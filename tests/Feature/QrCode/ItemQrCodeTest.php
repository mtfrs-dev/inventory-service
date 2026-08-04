<?php

namespace Tests\Feature\QrCode;

use App\Actions\Item\GenerateItemQrCodeAction;
use App\Jobs\GenerateItemQrCodeJob;
use App\Jobs\GenerateItemQrCodesJob;
use App\Models\Item;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private function createStatus(): Status
    {
        return Status::firstOrCreate(
            ['code' => 'AVAILABLE'],
            ['label' => 'Available', 'description' => 'Ready for use'],
        );
    }

    private function createItem(array $attributes = []): Item
    {
        $status  = $this->createStatus();
        $project = Project::factory()->create();

        return Item::factory()->create(array_merge([
            'project_id'        => $project->id,
            'current_status_id' => $status->id,
        ], $attributes));
    }

    // --- Observer ---

    public function test_qr_code_job_is_dispatched_when_item_is_created(): void
    {
        Queue::fake();

        $this->createItem();

        Queue::assertPushed(GenerateItemQrCodeJob::class);
    }

    // --- Action ---

    public function test_action_generates_png_file_and_updates_qr_code_column(): void
    {
        Storage::fake('public');
        Queue::fake();

        $item = $this->createItem(['qr_code' => null]);

        (new GenerateItemQrCodeAction())->handle($item);

        $expectedPath = "qr-codes/{$item->id}.png";
        Storage::disk('public')->assertExists($expectedPath);
        $this->assertSame($expectedPath, $item->fresh()->qr_code);
    }

    // --- GET /api/v1/items/{item}/qr-code ---

    public function test_qr_code_endpoint_returns_url_when_qr_exists(): void
    {
        Queue::fake();

        $item = $this->createItem(['qr_code' => "qr-codes/{$this->randomUuid()}.png"]);

        $response = $this->withoutMiddleware()
            ->getJson("/api/v1/items/{$item->id}/qr-code");

        $response->assertOk()
            ->assertJsonStructure(['success', 'message', 'data' => ['qr_code_url']])
            ->assertJsonPath('data.qr_code_url', asset('storage/'.$item->qr_code));
    }

    public function test_qr_code_endpoint_returns_404_when_qr_not_yet_generated(): void
    {
        Queue::fake();

        $item = $this->createItem(['qr_code' => null]);

        $response = $this->withoutMiddleware()
            ->getJson("/api/v1/items/{$item->id}/qr-code");

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // --- POST /api/v1/items/jobs/generate-qr-codes ---

    public function test_generate_qr_codes_endpoint_dispatches_backfill_job(): void
    {
        Queue::fake();

        $response = $this->withoutMiddleware()
            ->postJson('/api/v1/items/jobs/generate-qr-codes');

        $response->assertOk()
            ->assertJsonPath('success', true);

        Queue::assertPushed(GenerateItemQrCodesJob::class);
    }

    // --- GenerateItemQrCodesJob (backfill) ---

    public function test_backfill_job_dispatches_only_for_items_without_qr_code(): void
    {
        Queue::fake();

        $this->createItem(['qr_code' => 'qr-codes/existing.png']);
        $itemWithoutQr = $this->createItem(['qr_code' => null]);

        Queue::fake(); // reset — clear the observer-dispatched jobs from item creation

        (new GenerateItemQrCodesJob())->handle();

        Queue::assertPushed(GenerateItemQrCodeJob::class, 1);
        Queue::assertPushed(
            GenerateItemQrCodeJob::class,
            fn ($job) => $job->item->id === $itemWithoutQr->id,
        );
    }

    private function randomUuid(): string
    {
        return \Illuminate\Support\Str::uuid()->toString();
    }
}
