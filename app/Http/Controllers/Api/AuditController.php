<?php

namespace App\Http\Controllers\Api;


// use App\Http\Resources\AuditLogResource;
// use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditController extends ApiController
{
    public function index(Request $request)
    {
        Gate::authorize('audit.view');
        return;
        // return AuditLogResource::collection(AuditLog::orderByDesc('created_at')->paginate(50));
    }
}
