<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Report\GenerateReportRequest;
use App\Http\Resources\ReportResource;
use App\Services\Report\ReportGenerationService;
use Illuminate\Support\Facades\Gate;

class ReportController extends ApiController
{
    public function __construct(private readonly ReportGenerationService $reports)
    {
    }

    public function generate(GenerateReportRequest $request)
    {
        $report = $this->reports->generate(
            $request->validated('type'),
            $request->validated('from'),
            $request->validated('to'),
        );

        return (new ReportResource($report))->response()->setStatusCode(201);
    }

    public function index()
    {
        Gate::authorize('report.view');

        return ['data' => $this->reports->recent()];
    }
}
