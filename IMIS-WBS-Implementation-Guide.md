# Inventory Management Information System (IMIS)
## Work Breakdown Structure & Implementation Guide

---

## Part 1 — Confirmed Baseline

Before the WBS, these are the locked decisions this plan is built on. Any change to these
after Day 3 is a scope change and will affect the schedule.

| Concern | Decision |
|---|---|
| Team | 2 senior developers, self-organizing |
| Timeline | 20 work days |
| Focus | Backend only (Laravel API) |
| Database | MySQL 8.0, ULID primary keys |
| No count field | One row = one item = one serial number |
| Status flow | Forward-only. RECEIVED → REJECTED (terminal, skip for now) |
| Approval workflow | None in Phase 1. Tables scaffolded for Phase 2 |
| Notifications | Phase 2 only |
| Auth | External system. Local user table as integration point |
| Images | JPG / JPEG / PNG · max 2MB · max 10 per item |
| File storage | Local disk (Laravel `public` or `local` disk). S3-compatible path structure from Day 1 |
| Reporting Phase 1 | Items per status per project — in-app JSON, Excel, PDF |
| Mobile | Future only. API designed for it but no client built |
| OCR Import | Phase 2 (~1–2 months post-Phase 1) |
| Docker | Self-hosted on-premise |

---

## Part 2 — Final Technology Stack

```
Runtime         PHP 8.3 + Laravel 11
Database        MySQL 8.0
Queue / Cache   Redis 7 + Laravel Horizon
File Storage    Local disk (dev) → MinIO-compatible path structure for prod
Auth            Laravel Sanctum (token-based, external team integrates later)
Roles           spatie/laravel-permission
Excel           maatwebsite/laravel-excel (import + export)
PDF             barryvdh/laravel-dompdf
Image           intervention/image-laravel (validation + thumbnail generation)
State Machine   Custom lightweight implementation (no package — flow is simple enough)
Dev tooling     Laravel Telescope (dev only)
Container       Docker + Docker Compose (app, mysql, redis, nginx)
```

**Why no `spatie/laravel-medialibrary` or `spatie/laravel-activitylog`:**
Both are excellent packages but add indirection that makes this codebase harder to understand
for the auth integration team later. With a clear image upload spec (10 files, 2MB, 3 types)
and full control over the audit log schema, custom implementations are leaner and faster to build.

---

## Part 3 — Work Breakdown Structure

```
1.  PROJECT FOUNDATION
    1.1  Repository setup (Git, branches, .gitignore)
    1.2  Laravel 11 project initialization
    1.3  Docker Compose (dev environment: app, mysql, redis)
    1.4  Core package installation and configuration
    1.5  Coding conventions document (naming, commit format, PR rules)
    1.6  Base application scaffolding (exception handler, response helper, base controller)

2.  DATABASE LAYER
    2.1  Migrations — identity and organization tables
         2.1.1  users
         2.1.2  projects
         2.1.3  project_user  (pivot: project_leader ↔ project)
         2.1.4  categories
         2.1.5  subcategories
         2.1.6  work_units
    2.2  Migrations — inventory tables
         2.2.1  items
         2.2.2  item_pictures
    2.3  Migrations — operational tables
         2.3.1  item_activity_logs
         2.3.2  work_unit_assignment_rules
    2.4  Migrations — future scaffolds (empty, no logic attached)
         2.4.1  serial_import_batches
         2.4.2  serial_import_rows
         2.4.3  status_transition_requests
    2.5  Eloquent models + relationship definitions
    2.6  Model factories
    2.7  Seeders (roles, permissions, admin user, sample data)

3.  AUTHENTICATION & AUTHORIZATION
    3.1  User model aligned with external auth contract
    3.2  Sanctum installation and token endpoint
    3.3  Spatie permission setup (roles, permissions seeded)
    3.4  Role-based middleware registration
    3.5  Policies (Item, Category, Project, WorkUnit)
    3.6  Project–leader scoping (ensure project_leader only touches their project)

4.  PROJECT & CATEGORY MODULE
    4.1  Project CRUD (admin only)
         4.1.1  List + filter
         4.1.2  Create / Update / Delete
         4.1.3  Assign / remove project_leader
    4.2  Category CRUD (admin + project_leader)
         4.2.1  List by project
         4.2.2  Create / Update / Delete
    4.3  Subcategory CRUD (admin + project_leader)
         4.3.1  List by category
         4.3.2  Create / Update / Delete
    4.4  API Resources for all three

5.  WORK UNIT MODULE
    5.1  Work Unit CRUD (admin only)
         5.1.1  List + filter
         5.1.2  Create / Update / Delete (soft delete)
    5.2  Assignment Rule CRUD (admin only)
         5.2.1  List rules (ordered by priority)
         5.2.2  Create / Update / Delete / Toggle active
    5.3  API Resources for both

6.  ITEM MODULE
    6.1  Item CRUD (project_leader, scoped)
         6.1.1  List with filters (status, category, subcategory, work_unit, date range)
         6.1.2  Create (serial + name + description + category + optional subcategory)
         6.1.3  Update (non-status, non-assignment fields)
         6.1.4  Soft delete
         6.1.5  Restore
    6.2  Serial number handling
         6.2.1  Manual input validation (alphanumeric, unique)
         6.2.2  Duplicate detection with clear error message
    6.3  Image upload pipeline
         6.3.1  Upload endpoint (multipart, max 10 files per call per item)
         6.3.2  Validation (mime type, 2MB limit, count cap)
         6.3.3  Storage and thumbnail generation
         6.3.4  Delete single picture
         6.3.5  Reorder pictures
    6.4  Status state machine
         6.4.1  Transition map + guard conditions
         6.4.2  StatusTransitionService
         6.4.3  Transition endpoint (project_leader initiates, fires event)
         6.4.4  Allowed transitions endpoint (for frontend awareness)
    6.5  API Resources (Item list, Item detail with pictures and latest log)

7.  ASSIGNMENT MODULE
    7.1  Manual assignment (project_leader)
         7.1.1  Assign item to work_unit
         7.1.2  Reassign item to different work_unit
         7.1.3  Unassign
    7.2  Auto-assignment engine
         7.2.1  AssignmentResolverFactory
         7.2.2  CategoryMatchResolver
         7.2.3  CapacityBasedResolver
         7.2.4  Round-robin resolver (Redis-backed)
         7.2.5  AssignmentEngine (evaluates rules in priority order)
    7.3  Auto-assign endpoint (project_leader triggers, engine selects work_unit)

8.  ACTIVITY LOGGING
    8.1  ItemAction enum (all possible actions as typed constants)
    8.2  Event class definitions (ItemCreated, ItemStatusChanged, ItemAssigned, etc.)
    8.3  LogItemActivity listener (writes to item_activity_logs)
    8.4  Activity log query endpoint (paginated timeline per item)
    8.5  ActivityLogResource

9.  REPORTING MODULE
    9.1  Report query service (items per status per project)
         9.1.1  Aggregate query with Redis caching (5-min TTL)
         9.1.2  Filter by project, date range
    9.2  Dashboard data endpoint (JSON, chart-ready format)
    9.3  Excel export (maatwebsite/laravel-excel)
    9.4  PDF export (barryvdh/laravel-dompdf)

10. INFRASTRUCTURE & DEPLOYMENT
    10.1  Production Docker Compose (app, mysql, redis, nginx)
    10.2  Nginx configuration (reverse proxy, file size limits, timeouts)
    10.3  Laravel Horizon configuration and Dockerfile worker service
    10.4  Storage configuration (local disk with S3-compatible structure)
    10.5  .env.example with all required variables documented
    10.6  Deployment checklist and runbook

11. TESTING
    11.1  Test environment setup (SQLite in-memory or dedicated test DB)
    11.2  Auth and authorization tests
    11.3  Project, category, subcategory CRUD tests
    11.4  Work unit and assignment rule tests
    11.5  Item CRUD tests (including edge cases: duplicate serial, invalid category)
    11.6  Status transition tests (valid, invalid, unauthorized)
    11.7  Image upload tests (valid types, oversized, count cap exceeded)
    11.8  Assignment tests (manual + auto-engine rule matching)
    11.9  Reporting tests (data correctness, export file generation)
    11.10 Logging verification tests (every action produces correct log entry)

12. QA, DOCUMENTATION & HANDOVER
    12.1  API route documentation (Laravel Scribe or manual Markdown)
    12.2  .env variable documentation
    12.3  Docker setup and run instructions
    12.4  Known limitations and Phase 2 integration points documented
    12.5  Final deployment rehearsal
```

---

## Part 4 — Day-by-Day Developer Schedule

**Dev A** handles the complex domain logic: state machine, assignment engine, logging, infrastructure.
**Dev B** handles CRUD modules, image handling, reporting, testing.

Both review each other's code daily (async, not ceremonial standups).

```
DAY  │ DEV A                                      │ DEV B
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
1    │ WBS 1.1–1.3 · Git + Laravel init + Docker  │ WBS 1.4–1.6 · Packages + conventions +
     │ dev Compose (app/mysql/redis)               │ base scaffolding (response helper,
     │                                             │ exception handler, base controller)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
2    │ WBS 2.1–2.3 · All core migrations           │ WBS 2.4 · Future scaffold migrations +
     │ (users through item_activity_logs)           │ Spatie permission install + config
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
3    │ WBS 2.5 · All Eloquent models +             │ WBS 2.6–2.7 · Factories + seeders
     │ relationship definitions                    │ (roles, permissions, admin user, sample data)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
4    │ WBS 3.1–3.4 · User model, Sanctum,          │ WBS 3.5–3.6 · Policies + project-leader
     │ role middleware                              │ scoping middleware
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
5    │ WBS 4.1–4.2 · Project CRUD + Category       │ WBS 4.3–4.4 · Subcategory CRUD +
     │ CRUD + Form Requests                        │ all API Resources for modules 4
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
6    │ WBS 5.1–5.2 · Work Unit CRUD +              │ WBS 6.1–6.2 · Item CRUD (list, create,
     │ Assignment Rule CRUD (admin)                │ update, soft delete) + serial validation
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
7    │ WBS 6.4 · Status state machine              │ WBS 6.3 · Image upload pipeline (upload,
     │ (transition map + service + endpoint)       │ validate, store, thumbnail, delete, reorder)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
8    │ WBS 6.5 · Item API Resources (list +        │ WBS 6.5 · Item detail resource (with
     │ pagination + filter implementation)         │ pictures + log preview) + restore endpoint
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
9    │ WBS 8.1–8.3 · ItemAction enum + events +    │ WBS 8.4–8.5 · Activity log query endpoint
     │ LogItemActivity listener                    │ + ActivityLogResource + pagination
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
10   │ WBS 7.2 · Auto-assignment engine            │ WBS 7.1 + 7.3 · Manual assignment +
     │ (factory + all 3 resolvers + engine)        │ reassign + unassign + auto-trigger endpoint
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
11   │ WBS 9.1 · Report query service +            │ WBS 9.3 · Excel export
     │ Redis caching                               │ (maatwebsite, export class + download route)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
12   │ WBS 9.4 · PDF export (dompdf + Blade        │ WBS 9.2 · Dashboard JSON endpoint
     │ report template)                            │ (chart-ready format, filter params)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
13   │ WBS 5.3 + route polish · WorkUnit +         │ WBS 11.1–11.3 · Test env setup + auth
     │ Rule resources + route cleanup              │ tests + project/category/subcategory tests
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
14   │ WBS 11.5–11.6 · Item CRUD tests +           │ WBS 11.4 + 11.7 · WorkUnit + rule tests +
     │ status transition tests                     │ image upload tests
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
15   │ WBS 11.8 + 11.10 · Assignment tests +       │ WBS 11.9 · Reporting tests (query
     │ logging verification tests                  │ correctness + export file generation)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
16   │ Cross-review Dev B's code from Days 6–12    │ Cross-review Dev A's code from Days 7–10
     │ (items, images, reporting)                  │ (state machine, assignment, logging)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
17   │ WBS 10.1–10.3 · Production Docker Compose   │ Bug fixes from cross-review Day 16
     │ + Nginx config + Horizon worker             │
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
18   │ WBS 10.4–10.5 · Storage config +            │ WBS 12.1 · API documentation
     │ .env.example + deployment runbook           │ (Scribe or Markdown route map)
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
19   │ WBS 12.5 · Full deployment rehearsal on     │ WBS 12.2–12.4 · .env docs + Docker run
     │ staging Docker environment                  │ instructions + Phase 2 integration notes
─────┼────────────────────────────────────────────┼──────────────────────────────────────────────
20   │ Final bug fixes from rehearsal (Day 19)     │ Final review of test coverage + handover
     │ + tag release v1.0.0                        │ checklist sign-off
─────┴────────────────────────────────────────────┴──────────────────────────────────────────────
```

---

## Part 5 — Implementation Guides

---

### 5.1 Foundation (Day 1)

**Laravel initialization**
```bash
composer create-project laravel/laravel imis
cd imis
composer require spatie/laravel-permission maatwebsite/laravel-excel \
    barryvdh/laravel-dompdf intervention/image-laravel \
    laravel/sanctum laravel/horizon laravel/telescope
```

**Response helper — define this first, use it everywhere:**
```php
// app/Http/Helpers/ApiResponse.php
class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }
}
```

**Base controller:**
```php
// app/Http/Controllers/Controller.php
abstract class Controller
{
    // No helpers imported here. Use ApiResponse::success() statically.
    // Keep controllers thin: validate → call service → return resource.
}
```

---

### 5.2 Database Layer (Days 2–3)

**ULID usage — apply consistently:**
```php
// In every model
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Item extends Model
{
    use HasUlids; // Automatically sets $primaryKey = 'id' as ULID
}
```

**Migration conventions — use this pattern for every table:**
```php
Schema::create('items', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('category_id')->constrained()->restrictOnDelete();
    $table->foreignUlid('subcategory_id')->nullable()->constrained()->restrictOnDelete();
    $table->foreignUlid('work_unit_id')->nullable()->constrained()->nullOnDelete();
    $table->string('serial_number', 100)->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('status', 50)->default('planned')->index();
    $table->string('assignment_method', 20)->nullable(); // 'manual' | 'automatic'
    $table->timestamp('assigned_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

> **Why `string` for status instead of `enum`?** MySQL ENUM requires ALTER TABLE to add a value.
> With thousands of items, that risks a table lock. A `string` with app-level validation is
> safer and equally explicit. Enforce valid values in the FormRequest and StatusTransitionService.

**Future scaffold migrations — create the tables but leave them empty of logic:**
```php
// These exist solely so Phase 2 can add columns without creating new migrations
// that conflict with existing data. Do not attach models or seeders to these yet.

Schema::create('status_transition_requests', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('item_id')->constrained()->cascadeOnDelete();
    $table->foreignUlid('requested_by')->constrained('users');
    $table->foreignUlid('reviewed_by')->nullable()->constrained('users');
    $table->string('from_status', 50);
    $table->string('to_status', 50);
    $table->string('review_status', 20)->default('pending'); // pending|approved|rejected
    $table->text('reviewer_note')->nullable();
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamps();
});
```

**Model relationships — define all, even ones not used in Phase 1:**
```php
// app/Models/Item.php
class Item extends Model
{
    use HasUlids, SoftDeletes;

    protected $casts = ['assigned_at' => 'datetime'];

    public function category(): BelongsTo    { return $this->belongsTo(Category::class); }
    public function subcategory(): BelongsTo { return $this->belongsTo(Subcategory::class); }
    public function workUnit(): BelongsTo    { return $this->belongsTo(WorkUnit::class); }
    public function pictures(): HasMany      { return $this->hasMany(ItemPicture::class)->orderBy('sort_order'); }
    public function activityLogs(): HasMany  { return $this->hasMany(ItemActivityLog::class)->latest(); }

    // Convenience: get the owning project through category
    public function project(): HasOneThrough
    {
        return $this->hasOneThrough(Project::class, Category::class, 'id', 'id', 'category_id', 'project_id');
    }
}
```

---

### 5.3 Authentication & Authorization (Day 4)

**The user model is an integration point, not owned by this system:**
```php
// app/Models/User.php
// Do not put business logic here. The external auth team will call our login endpoint
// and receive a Sanctum token. This model is a local mirror of the external identity.

class User extends Authenticatable
{
    use HasUlids, HasApiTokens, HasRoles; // HasRoles from spatie/permission

    protected $fillable = ['id', 'name', 'email', 'external_id'];
    // external_id: the ID from the external auth system (for future sync)
}
```

**Sanctum token login — keep this minimal and documented:**
```php
// POST /api/auth/login
// This endpoint will be replaced by the external auth team's SSO flow.
// Until then, it allows local credential-based login for development.
public function login(LoginRequest $request): JsonResponse
{
    if (!Auth::attempt($request->only('email', 'password'))) {
        return ApiResponse::error('Invalid credentials', 401);
    }
    $token = Auth::user()->createToken('imis-token')->plainTextToken;
    return ApiResponse::success(['token' => $token]);
}
```

**Roles and permissions — seed these, do not hard-code in controllers:**
```php
// database/seeders/RolesAndPermissionsSeeder.php

$permissions = [
    'projects.manage',          // admin
    'categories.manage',        // admin + project_leader (scoped)
    'subcategories.manage',     // admin + project_leader (scoped)
    'work_units.manage',        // admin
    'assignment_rules.manage',  // admin
    'items.create',             // project_leader
    'items.update',             // project_leader
    'items.delete',             // project_leader
    'items.view',               // all roles
    'items.transition',         // project_leader
    'items.assign',             // project_leader
    'reports.view',             // all roles
    'reports.export',           // project_manager + admin
];

Role::findOrCreate('admin')->syncPermissions(Permission::all());
Role::findOrCreate('project_leader')->syncPermissions([
    'categories.manage', 'subcategories.manage',
    'items.create', 'items.update', 'items.delete', 'items.view',
    'items.transition', 'items.assign', 'reports.view',
]);
Role::findOrCreate('project_manager')->syncPermissions([
    'items.view', 'reports.view', 'reports.export',
]);
```

**Project scoping — project_leader must only access their own project's items:**
```php
// app/Http/Middleware/ScopeToProject.php
// Applied to all routes that project_leaders can reach.

public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if ($user->hasRole('project_leader')) {
        // Attach the user's project IDs to the request for downstream use
        $request->merge([
            '_allowed_project_ids' => $user->projects()->pluck('projects.id')->toArray(),
        ]);
    }

    return $next($request);
}

// Then in ItemRepository::listForUser():
if ($user->hasRole('project_leader')) {
    $query->whereHas('category', fn($q) =>
        $q->whereIn('project_id', $request->_allowed_project_ids)
    );
}
```

---

### 5.4 Item Status State Machine (Day 7)

Build this as a **service class with a constant transition map**. No package needed.

```php
// app/Enums/ItemStatus.php
enum ItemStatus: string
{
    case Planned      = 'planned';
    case Procured     = 'procured';
    case Received     = 'received';
    case Accepted     = 'accepted';
    case Rejected     = 'rejected';   // Terminal — no further transitions
    case Staged       = 'staged';
    case Deployed     = 'deployed';
    case Installed    = 'installed';
    case Commissioned = 'commissioned';
    case Dispatched   = 'dispatched';
    case Handovered   = 'handovered'; // Terminal

    public function label(): string
    {
        return match($this) {
            self::Planned      => 'Planned',
            self::Procured     => 'Procured',
            self::Received     => 'Received',
            self::Accepted     => 'Accepted',
            self::Rejected     => 'Rejected',
            self::Staged       => 'Staged',
            self::Deployed     => 'Deployed',
            self::Installed    => 'Installed',
            self::Commissioned => 'Commissioned',
            self::Dispatched   => 'Dispatched',
            self::Handovered   => 'Handovered',
        };
    }
}
```

```php
// app/Services/StatusTransitionService.php

class StatusTransitionService
{
    // Define the allowed transition map as a constant.
    // Key = current status. Value = array of allowed next statuses.
    private const TRANSITIONS = [
        'planned'      => ['procured'],
        'procured'     => ['received'],
        'received'     => ['accepted', 'rejected'],
        'accepted'     => ['staged'],
        'rejected'     => [],              // Terminal
        'staged'       => ['deployed'],
        'deployed'     => ['installed'],
        'installed'    => ['commissioned'],
        'commissioned' => ['dispatched'],
        'dispatched'   => ['handovered'],
        'handovered'   => [],              // Terminal
    ];

    public function canTransition(Item $item, string $toStatus): bool
    {
        $allowed = self::TRANSITIONS[$item->status] ?? [];
        return in_array($toStatus, $allowed, true);
    }

    public function allowedTransitions(Item $item): array
    {
        return self::TRANSITIONS[$item->status] ?? [];
    }

    public function transition(Item $item, string $toStatus, User $actor): void
    {
        if (!$this->canTransition($item, $toStatus)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition '{$item->serial_number}' from '{$item->status}' to '{$toStatus}'."
            );
        }

        $fromStatus = $item->status;

        $item->update(['status' => $toStatus]);

        event(new ItemStatusChanged($item, $fromStatus, $toStatus, $actor));
    }
}
```

**Controller for status transition (thin — delegates fully):**
```php
// POST /api/items/{item}/transition
public function transition(TransitionStatusRequest $request, Item $item): JsonResponse
{
    $this->authorize('transition', $item);

    $this->statusService->transition($item, $request->validated('to_status'), $request->user());

    return ApiResponse::success(
        new ItemResource($item->fresh()),
        "Item transitioned to {$item->status}."
    );
}
```

```php
// app/Http/Requests/TransitionStatusRequest.php
public function rules(): array
{
    return [
        'to_status' => ['required', 'string', Rule::in(array_column(ItemStatus::cases(), 'value'))],
    ];
}
```

---

### 5.5 Image Upload Pipeline (Day 7)

Do not use Spatie Media Library here. The rules are simple enough for a clean custom implementation.

```php
// app/Services/PictureService.php

class PictureService
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png'];
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2MB
    private const MAX_COUNT = 10;
    private const THUMB_WIDTH = 400;

    public function upload(Item $item, UploadedFile $file): ItemPicture
    {
        if ($item->pictures()->count() >= self::MAX_COUNT) {
            throw new PictureLimitExceededException("Item already has " . self::MAX_COUNT . " pictures.");
        }

        // Store original
        $directory = "items/{$item->id}";
        $filename  = (string) Str::ulid() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs($directory, $filename, 'local');

        // Generate thumbnail using Intervention Image
        $thumbFilename = 'thumb_' . $filename;
        $thumbPath     = storage_path("app/{$directory}/{$thumbFilename}");
        Image::read($file->getRealPath())
             ->scale(width: self::THUMB_WIDTH)
             ->save($thumbPath);

        // Determine next sort order
        $nextOrder = ($item->pictures()->max('sort_order') ?? -1) + 1;

        return $item->pictures()->create([
            'disk'       => 'local',
            'file_path'  => $path,
            'thumb_path' => "{$directory}/{$thumbFilename}",
            'file_name'  => $file->getClientOriginalName(),
            'file_size'  => $file->getSize(),
            'mime_type'  => $file->getMimeType(),
            'sort_order' => $nextOrder,
        ]);
    }

    public function delete(ItemPicture $picture): void
    {
        Storage::disk($picture->disk)->delete($picture->file_path);
        Storage::disk($picture->disk)->delete($picture->thumb_path);
        $picture->delete();
    }

    public function reorder(Item $item, array $orderedIds): void
    {
        // orderedIds: array of picture IDs in desired order
        foreach ($orderedIds as $index => $id) {
            $item->pictures()->where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
```

**Validation lives in the Form Request, not the service:**
```php
// app/Http/Requests/UploadPictureRequest.php
public function rules(): array
{
    return [
        'pictures'   => ['required', 'array', 'max:10'],
        'pictures.*' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
    ];
}
```

**Important — add to `php.ini` or Docker environment:**
```
upload_max_filesize = 20M     # Headroom for 10 × 2MB files in one request
post_max_size       = 25M
```

---

### 5.6 Activity Logging (Day 9)

The log is immutable. No updates, no deletes, no soft deletes. Every write goes through `ActivityLogService`.

```php
// app/Enums/ItemAction.php
enum ItemAction: string
{
    case Created           = 'created';
    case Updated           = 'updated';
    case Deleted           = 'deleted';
    case Restored          = 'restored';
    case StatusChanged     = 'status_changed';
    case Assigned          = 'assigned';
    case Reassigned        = 'reassigned';
    case Unassigned        = 'unassigned';
    case AssignmentFailed  = 'auto_assignment_failed';
    case PictureAdded      = 'picture_added';
    case PictureDeleted    = 'picture_deleted';
    case PicturesReordered = 'pictures_reordered';
    case SerialUpdated     = 'serial_updated';
}
```

```php
// app/Listeners/LogItemActivity.php
// This listener is registered for every item-related event.

class LogItemActivity
{
    public function handle(object $event): void
    {
        $payload = match(true) {
            $event instanceof ItemStatusChanged => [
                'action'     => ItemAction::StatusChanged->value,
                'old_values' => ['status' => $event->fromStatus],
                'new_values' => ['status' => $event->toStatus],
                'metadata'   => null,
            ],
            $event instanceof ItemAssigned => [
                'action'     => ItemAction::Assigned->value,
                'old_values' => ['work_unit_id' => $event->previousWorkUnitId],
                'new_values' => ['work_unit_id' => $event->workUnit->id, 'work_unit_name' => $event->workUnit->name],
                'metadata'   => ['method' => $event->method],
            ],
            $event instanceof ItemCreated => [
                'action'     => ItemAction::Created->value,
                'old_values' => null,
                'new_values' => ['serial_number' => $event->item->serial_number, 'name' => $event->item->name],
                'metadata'   => null,
            ],
            default => null,
        };

        if ($payload === null) return;

        ItemActivityLog::create([
            'id'         => (string) Str::ulid(),
            'item_id'    => $event->item->id,
            'user_id'    => $event->actor?->id,
            'action'     => $payload['action'],
            'old_values' => $payload['old_values'],
            'new_values' => $payload['new_values'],
            'metadata'   => $payload['metadata'],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
```

**Wire all events to this single listener in `EventServiceProvider`:**
```php
protected $listen = [
    ItemCreated::class       => [LogItemActivity::class],
    ItemStatusChanged::class => [LogItemActivity::class],
    ItemAssigned::class      => [LogItemActivity::class],
    ItemUnassigned::class    => [LogItemActivity::class],
    // Add more as needed — the listener handles all via match()
];
```

---

### 5.7 Assignment Engine (Day 10)

```php
// app/Assignment/Contracts/AssignmentResolverContract.php
interface AssignmentResolverContract
{
    // Returns a WorkUnit if this resolver can satisfy the rule, null otherwise.
    public function resolve(Item $item, WorkUnitAssignmentRule $rule): ?WorkUnit;
}
```

```php
// app/Assignment/Resolvers/CategoryMatchResolver.php
// parameters JSON schema: { "category_ids": ["ulid1", "ulid2"] }

class CategoryMatchResolver implements AssignmentResolverContract
{
    public function resolve(Item $item, WorkUnitAssignmentRule $rule): ?WorkUnit
    {
        $allowedCategories = $rule->parameters['category_ids'] ?? [];

        if (!in_array($item->category_id, $allowedCategories, true)) {
            return null; // This rule does not apply to this item
        }

        return $rule->workUnit; // The rule's associated work unit is the answer
    }
}
```

```php
// app/Assignment/Resolvers/CapacityBasedResolver.php
// parameters JSON schema: { "max_load_percentage": 80 }
// Assigns to the rule's work_unit only if it has capacity headroom.

class CapacityBasedResolver implements AssignmentResolverContract
{
    public function resolve(Item $item, WorkUnitAssignmentRule $rule): ?WorkUnit
    {
        $workUnit = $rule->workUnit;

        if ($workUnit->capacity === null) {
            return $workUnit; // No capacity limit, always eligible
        }

        $currentLoad    = $workUnit->items()->count();
        $maxLoad        = $rule->parameters['max_load_percentage'] / 100 * $workUnit->capacity;

        return $currentLoad < $maxLoad ? $workUnit : null;
    }
}
```

```php
// app/Assignment/Resolvers/RoundRobinResolver.php
// parameters JSON schema: { "group": "zone_a" }
// Rotates through all work_units that have an active round_robin rule with the same group.

class RoundRobinResolver implements AssignmentResolverContract
{
    public function __construct(private readonly Repository $cache) {}

    public function resolve(Item $item, WorkUnitAssignmentRule $rule): ?WorkUnit
    {
        $group = $rule->parameters['group'] ?? 'default';

        // Get all work units in this round-robin group
        $workUnits = WorkUnitAssignmentRule::where('rule_type', 'round_robin')
            ->where('is_active', true)
            ->whereJsonContains('parameters->group', $group)
            ->with('workUnit')
            ->get()
            ->pluck('workUnit')
            ->filter(fn($wu) => $wu->is_active);

        if ($workUnits->isEmpty()) return null;

        $cacheKey   = "rr_group:{$group}:last_index";
        $lastIndex  = (int) $this->cache->get($cacheKey, -1);
        $nextIndex  = ($lastIndex + 1) % $workUnits->count();

        $this->cache->put($cacheKey, $nextIndex, now()->addDay());

        return $workUnits->values()->get($nextIndex);
    }
}
```

```php
// app/Assignment/AssignmentEngine.php

class AssignmentEngine
{
    public function __construct(private readonly AssignmentResolverFactory $factory) {}

    public function evaluate(Item $item): ?WorkUnit
    {
        // Rules are ordered by priority ASC (lower = higher priority)
        $rules = WorkUnitAssignmentRule::where('is_active', true)
                     ->orderBy('priority')
                     ->with('workUnit')
                     ->get();

        foreach ($rules as $rule) {
            $resolver = $this->factory->make($rule->rule_type);
            $workUnit = $resolver->resolve($item, $rule);

            if ($workUnit !== null) {
                return $workUnit;
            }
        }

        return null; // No rule matched
    }
}
```

```php
// app/Assignment/AssignmentResolverFactory.php

class AssignmentResolverFactory
{
    public function make(string $ruleType): AssignmentResolverContract
    {
        return match($ruleType) {
            'category_match'    => app(CategoryMatchResolver::class),
            'capacity_based'    => app(CapacityBasedResolver::class),
            'round_robin'       => app(RoundRobinResolver::class),
            default             => throw new \InvalidArgumentException("Unknown rule type: {$ruleType}"),
        };
    }
}
```

---

### 5.8 Reporting (Days 11–12)

```php
// app/Services/ReportService.php

class ReportService
{
    public function itemsPerStatusPerProject(array $filters = []): Collection
    {
        $cacheKey = 'report:items_per_status:' . md5(serialize($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            return DB::table('items')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->join('projects', 'categories.project_id', '=', 'projects.id')
                ->select(
                    'projects.id as project_id',
                    'projects.name as project_name',
                    'items.status',
                    DB::raw('COUNT(*) as total')
                )
                ->when($filters['project_id'] ?? null, fn($q, $v) => $q->where('projects.id', $v))
                ->when($filters['date_from'] ?? null, fn($q, $v) => $q->where('items.created_at', '>=', $v))
                ->when($filters['date_to'] ?? null,   fn($q, $v) => $q->where('items.created_at', '<=', $v))
                ->whereNull('items.deleted_at')
                ->groupBy('projects.id', 'projects.name', 'items.status')
                ->orderBy('projects.name')
                ->orderBy('items.status')
                ->get();
        });
    }

    // Transform flat query result into chart-ready nested structure
    public function formatForChart(Collection $data): array
    {
        return $data->groupBy('project_name')->map(fn($rows, $name) => [
            'project'  => $name,
            'statuses' => $rows->pluck('total', 'status')->toArray(),
        ])->values()->toArray();
    }
}
```

**Excel export:**
```php
// app/Exports/ItemsPerStatusExport.php
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;

class ItemsPerStatusExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $data) {}

    public function collection(): Collection { return $this->data; }

    public function headings(): array
    {
        return ['Project', 'Status', 'Total Items'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],  // Bold header row
        ];
    }
}

// Controller:
// GET /api/reports/items-per-status/export/excel
public function exportExcel(ReportFilterRequest $request): BinaryFileResponse
{
    $data = $this->reportService->itemsPerStatusPerProject($request->validated());
    return Excel::download(new ItemsPerStatusExport($data), 'items-per-status.xlsx');
}
```

**PDF export using a Blade template:**
```php
// resources/views/reports/items_per_status.blade.php
// Simple table layout — dompdf renders standard HTML

// Controller:
// GET /api/reports/items-per-status/export/pdf
public function exportPdf(ReportFilterRequest $request): Response
{
    $data = $this->reportService->itemsPerStatusPerProject($request->validated());
    $pdf  = Pdf::loadView('reports.items_per_status', ['data' => $data])
               ->setPaper('a4', 'landscape');

    return $pdf->download('items-per-status.pdf');
}
```

---

### 5.9 Docker Configuration (Day 17)

```yaml
# docker-compose.yml (production)
version: '3.9'

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    depends_on:
      - mysql
      - redis
    environment:
      - APP_ENV=production

  horizon:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan horizon
    depends_on:
      - redis
      - mysql
    restart: unless-stopped

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: imis
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

volumes:
  mysql_data:
  redis_data:
```

**Nginx config — critical for file uploads:**
```nginx
# docker/nginx/default.conf
server {
    listen 80;
    root /var/www/html/public;
    index index.php;

    client_max_body_size 25M;   # Must match PHP upload_max_filesize headroom

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;   # For large Excel exports
    }
}
```

---

## Part 6 — API Route Map

```php
// routes/api.php

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Projects (admin)
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::post('projects/{project}/assign-leader', [ProjectController::class, 'assignLeader']);
        Route::apiResource('work-units', WorkUnitController::class);
        Route::apiResource('assignment-rules', AssignmentRuleController::class);
    });

    // Categories & Subcategories (admin + project_leader scoped)
    Route::middleware(['role:admin|project_leader', 'scope.project'])->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('subcategories', SubcategoryController::class);
    });

    // Items (project_leader scoped, project_manager read-only)
    Route::middleware('scope.project')->group(function () {
        Route::apiResource('items', ItemController::class);
        Route::post('items/{item}/restore', [ItemController::class, 'restore']);
        Route::get('items/{item}/transitions', [ItemStatusController::class, 'allowed']);
        Route::post('items/{item}/transition', [ItemStatusController::class, 'transition']);
        Route::post('items/{item}/assign', [ItemAssignmentController::class, 'assign']);
        Route::post('items/{item}/auto-assign', [ItemAssignmentController::class, 'autoAssign']);
        Route::delete('items/{item}/unassign', [ItemAssignmentController::class, 'unassign']);
        Route::post('items/{item}/pictures', [ItemPictureController::class, 'upload']);
        Route::delete('items/{item}/pictures/{picture}', [ItemPictureController::class, 'destroy']);
        Route::patch('items/{item}/pictures/reorder', [ItemPictureController::class, 'reorder']);
        Route::get('items/{item}/logs', [ItemActivityLogController::class, 'index']);
    });

    // Reporting (all roles)
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/items-per-status', [ReportController::class, 'itemsPerStatus']);
        Route::middleware('permission:reports.export')->group(function () {
            Route::get('reports/items-per-status/export/excel', [ReportController::class, 'exportExcel']);
            Route::get('reports/items-per-status/export/pdf', [ReportController::class, 'exportPdf']);
        });
    });
});
```

---

## Part 7 — Risk Register

| # | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R1 | Auth integration team delivers external SSO late, blocking Phase 2 | High | Medium | Local Sanctum auth works standalone. SSO is a drop-in replacement — no IMIS code changes required. |
| R2 | Assignment engine edge case: no rule matches an item | Medium | High | Log the failure explicitly (`auto_assignment_failed` action). Return 422 with clear message. Project leader falls back to manual assignment. |
| R3 | Image upload fails silently if storage path missing | Medium | High | Add a startup health check that verifies `storage/app/items` is writable. Fail fast with a meaningful error. |
| R4 | Reporting query is slow on thousands of items | Medium | Medium | Redis cache (5 min TTL) applied from Day 1. Add composite index on `(items.status, categories.project_id)` on Day 2. |
| R5 | Status transition called concurrently on same item | Low | High | Wrap `StatusTransitionService::transition()` in a DB transaction with a `SELECT ... FOR UPDATE` on the item row. |
| R6 | PDF export crashes on large datasets | Low | Medium | Add row limit (e.g., 500 items max per export). Paginated export as Phase 2 enhancement. |
| R7 | ULID type mismatch with external auth user IDs | Medium | High | Keep `users.id` as ULID but store `external_id` as `VARCHAR(255)` for any format the external system uses. |
| R8 | 20-day deadline slipped by scope creep | High | High | Freeze scope after Day 3. Any new requests go into a Phase 1.1 backlog. |

---

## Part 8 — Phase 2 Integration Points (documented for handover)

These are the exact touch points Phase 2 developers need to know:

```
Serial import (.xlsx)
→ Serial Import Batch table is already migrated
→ SerialNumberImport class stub to be created in app/Imports/
→ maatwebsite/laravel-excel is already installed
→ Queue: use the 'imports' queue (to be configured in Horizon)

Approval workflow
→ status_transition_requests table is already migrated
→ StatusTransitionService::transition() fires ItemStatusChanged event
→ Insert approval check before the event in Phase 2

Notifications (email + WhatsApp)
→ Events are already defined and fired
→ Add a new Listener to each event in EventServiceProvider
→ WhatsApp provider: configure in .env (Fonnte/Twilio/etc.)

External auth SSO
→ User::external_id column is already in the users table
→ Replace POST /api/auth/login with an OAuth2 redirect flow
→ Sanctum token issuance remains unchanged

OCR Microservice
→ Communicate via HTTP: POST /ocr/process with multipart image
→ Expected response: { rows: [ { serial_number, name, description, category_code } ] }
→ Feed response into the existing import batch pipeline
```

---

## Part 9 — Definition of Done (Phase 1)

Phase 1 is complete when all of the following are true:

- [ ] All migrations run cleanly on a fresh database with `php artisan migrate`
- [ ] All seeders complete without error with `php artisan db:seed`
- [ ] Every API endpoint returns the correct HTTP status code for success, validation error, and unauthorized access
- [ ] Status transitions enforce the defined map — invalid transitions return 422
- [ ] Image upload rejects files over 2MB, wrong formats, and items that already have 10 pictures
- [ ] Auto-assignment logs a failure action if no rule matches
- [ ] Every item operation (create, update, delete, transition, assign, picture) produces a row in `item_activity_logs`
- [ ] Reporting returns correct counts verified against seeded data
- [ ] Excel and PDF download endpoints return valid, downloadable files
- [ ] All feature tests pass with `php artisan test`
- [ ] Docker Compose brings up the full stack with a single `docker compose up -d`
- [ ] API documentation lists all routes, parameters, and example responses
- [ ] Phase 2 integration points are documented and handed over
```
