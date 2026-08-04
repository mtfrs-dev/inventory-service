<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Subcategory\StoreSubcategoryRequest;
use App\Http\Requests\Subcategory\UpdateSubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Repositories\Contracts\SubcategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SubcategoryController extends ApiController
{
    public function __construct(private readonly SubcategoryRepositoryInterface $categories)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return SubcategoryResource::collection(
            $this->categories->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreSubcategoryRequest $request): JsonResponse
    {
        $item = $this->categories->create($request->validated());

        return SubcategoryResource::make($item)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $id): SubcategoryResource
    {
        return SubcategoryResource::make($this->categories->find($id));
    }

    public function update(UpdateSubcategoryRequest $request, string $id): SubcategoryResource
    {
        return SubcategoryResource::make($this->categories->update($id, $request->validated()));
    }

    public function destroy(string $id): Response
    {
        $this->categories->delete($id);

        return response()->noContent();
    }
}