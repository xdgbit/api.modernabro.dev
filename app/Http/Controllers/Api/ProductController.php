<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductIndexRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product\Product;
use App\Repositories\Product\ProductRepositoryInterface;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    )
    {
    }

    public function index(ProductIndexRequest $request)
    {
        $products = $this->products->paginate(
            $request->validated(),
            $request->integer('per_page', 15)
        );

        return ApiResponse::success(ProductResource::collection($products));
    }

    public function show(Product $product)
    {
        $product->load(['brand:id,title,slug', 'category:id,title,slug']);

        return ApiResponse::success(new ProductResource($product));
    }
}