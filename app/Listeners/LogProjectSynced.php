<?php

namespace App\Listeners;

use App\Events\ProjectSynced;
use Illuminate\Support\Facades\Log;

class LogProjectSynced
{
    public function handle(ProjectSynced $event): void
    {
        Log::info('Project synchronized from external service.', [
            'external_id' => $event->project->external_id,
            'code'        => $event->project->code,
        ]);
    }
}
