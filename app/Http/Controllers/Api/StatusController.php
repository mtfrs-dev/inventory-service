<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Status\StoreStatusRequest;
use App\Http\Requests\Status\UpdateStatusRequest;
use App\Http\Resources\StatusResource;
use App\Repositories\Contracts\StatusRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StatusController extends ApiController
{
    public function __construct(private readonly StatusRepositoryInterface $statuses)
    {
    }
    
    public function index(): AnonymousResourceCollection
    {
        return StatusResource::collection($this->statuses->all());
    }

    public function store(StoreStatusRequest $request): JsonResponse
    {
        $status = $this->statuses->create($request->validated());

        return StatusResource::make($status)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $id): StatusResource
    {
        return StatusResource::make($this->statuses->find($id));
    }

    public function update(UpdateStatusRequest $request, string $id): StatusResource
    {
        return StatusResource::make($this->statuses->update($id, $request->validated()));
    }

    public function destroy(string $id): Response
    {
        $this->statuses->delete($id);

        return response()->noContent();
    }
}