<?php

namespace App\Listeners;

use App\Events\ProjectDeleted;
use Illuminate\Support\Facades\Log;

class LogProjectDeleted
{
    public function handle(ProjectDeleted $event): void
    {
        Log::info('Project deleted via external service sync.', [
            'external_id' => $event->project->external_id,
            'code'        => $event->project->code,
        ]);
    }
}
