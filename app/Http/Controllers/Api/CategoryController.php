<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends ApiController
{
    public function __construct(private readonly CategoryRepositoryInterface $categories)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            $this->categories->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $item = $this->categories->create($request->validated());

        return CategoryResource::make($item)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $id): CategoryResource
    {
        return CategoryResource::make($this->categories->find($id));
    }

    public function update(UpdateCategoryRequest $request, string $id): CategoryResource
    {
        return CategoryResource::make($this->categories->update($id, $request->validated()));
    }

    public function destroy(string $id): Response
    {
        $this->categories->delete($id);

        return response()->noContent();
    }
}