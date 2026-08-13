<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->withCount('products')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResource
    {
        $category = Category::create($request->validated());

        AuditLog::record('category_created', $category);

        return new CategoryResource($category);
    }

    public function show(Category $category): JsonResource
    {
        return new CategoryResource($category->loadCount('products'));
    }

    public function update(StoreCategoryRequest $request, Category $category): JsonResource
    {
        $category->update($request->validated());

        AuditLog::record('category_updated', $category);

        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        AuditLog::record('category_deleted', $category);

        return response()->json(['message' => 'Category deleted.']);
    }
}
