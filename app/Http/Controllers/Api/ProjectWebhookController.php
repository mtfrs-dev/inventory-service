<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\ProjectDeletedRequest;
use App\Http\Requests\Webhook\ProjectUpsertedRequest;
use App\Services\Project\ProjectSyncService;
use Illuminate\Http\JsonResponse;

class ProjectWebhookController extends Controller
{
    public function __construct(private readonly ProjectSyncService $sync) {}

    public function upserted(ProjectUpsertedRequest $request): JsonResponse
    {
        $this->sync->upsert($request->validated());
        return response()->json(['status' => 'acknowledged']);
    }

    public function deleted(ProjectDeletedRequest $request): JsonResponse
    {
        $this->sync->delete($request->validated()['id']);
        return response()->json(['status' => 'acknowledged']);
    }
}
