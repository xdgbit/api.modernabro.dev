<?php

namespace App\Http\Controllers\Admin\Product;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function stats()
    {
        $total = Product::count();
        $active = Product::where('status', 'active')->count();
        $inactive = Product::where('status', 'inactive')->count();
        $outOfStock = Product::where('stock', '<=', 0)->count();
        $lowStock = Product::where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();
        $inventoryValue = (int)Product::query()
            ->selectRaw('COALESCE(SUM(price * stock), 0) AS inventory_value')
            ->value('inventory_value');
        $attentionQuery = Product::query()
            ->whereColumn('stock', '<=', 'low_stock_threshold');
        $attentionCount = (clone $attentionQuery)->count();
        $attentionProducts = (clone $attentionQuery)
            ->with('brand:id,title')
            ->orderBy('stock')
            ->limit(50)
            ->get()
            ->map(fn(Product $product) => [
                'id'                  => $product->id,
                'name'                => $product->name,
                'sku'                 => $product->sku,
                'brand'               => $product->brand?->title,
                'stock'               => $product->stock,
                'low_stock_threshold' => $product->low_stock_threshold,
            ])
            ->values();
        return ApiResponse::success([
            'total'              => $total,
            'active'             => $active,
            'inactive'           => $inactive,
            'out_of_stock'       => $outOfStock,
            'low_stock'          => $lowStock,
            'inventory_value'    => $inventoryValue,
            'attention_count'    => $attentionCount,
            'fa_attention_count' => ConvertNumbersEntoFa($attentionCount),
            'attention_products' => $attentionProducts,
        ]);
    }

    public function datatable(Request $request)
    {
        $search = trim((string)$request->input('search', ''));
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $length = max(1, (int)$request->input('length', 10));
        $columnName = $request->input('column_name', 'id');

        $allowedColumns = ['id', 'sku', 'name', 'price', 'sale_price', 'stock', 'status'];
        $columnName = in_array($columnName, $allowedColumns, true) ? $columnName : 'id';

        $query = Product::query()->with([
            'brand:id,title',
            'category:id,title',
        ]);

        if ($search !== '')
        {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $this->applyFilters($query, $request);

        $products = $query
            ->orderBy($columnName, $dir)
            ->paginate($length);

        $products->getCollection()->transform(
            fn(Product $product) => $this->transformRow($product)
        );

        return ApiResponse::success($products);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('sku'))
        {
            $query->where('sku', 'like', '%' . trim($request->sku) . '%');
        }

        if ($request->filled('name'))
        {
            $query->where('name', 'like', '%' . trim($request->name) . '%');
        }

        if ($request->filled('brand'))
        {
            $brand = trim($request->brand);

            $query->whereHas('brand', function ($query) use ($brand) {
                $query->where('title', 'like', "%{$brand}%");
            });
        }

        if ($request->filled('category'))
        {
            $category = trim($request->category);

            $query->whereHas('category', function ($query) use ($category) {
                $query->where('title', 'like', "%{$category}%");
            });
        }

        if ($request->filled('status'))
        {
            $status = $request->input('status');

            if (in_array($status, ['active', 'inactive'], true))
            {
                $query->where('status', $status);
            }
        }

        if ($request->filled('stock_status'))
        {
            match ($request->input('stock_status'))
            {
                'out_of_stock' => $query->where('stock', '<=', 0),

                'low_stock' => $query->where('stock', '>', 0)
                    ->whereColumn('stock', '<=', 'low_stock_threshold'),

                'in_stock' => $query->whereColumn('stock', '>', 'low_stock_threshold'),

                default => null,
            };
        }

        if ($request->filled('price_from'))
        {
            $query->where('price', '>=', (int)$request->price_from);
        }

        if ($request->filled('price_to'))
        {
            $query->where('price', '<=', (int)$request->price_to);
        }
    }

    public function jspanel(Request $request)
    {
        $productId = $request->input('product_id');

        $brands = Brand::query()
            ->select(['id', 'title'])
            ->orderBy('title')
            ->get();

        $categories = ProductCategory::query()
            ->select(['id', 'title'])
            ->orderBy('title')
            ->get();

        $product = null;

        if ($productId)
        {
            $product = Product::query()->find($productId);

            if (!$product)
            {
                return ApiResponse::error(
                    'محصول موردنظر پیدا نشد.',
                    null,
                    404
                );
            }
        }

        return ApiResponse::success([
            'brands'     => $brands,
            'categories' => $categories,
            'product'    => $product,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        $product = Product::create($validated);

        return ApiResponse::success(
            $product->fresh(['brand', 'category'])
        );
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);

        $product->update($validated);

        return ApiResponse::success(
            $product->fresh(['brand', 'category'])
        );
    }

    public function destroy(Product $product)
    {
        // محصولی که در سفارش استفاده شده، حذف نمی‌شود
        // (رابطه orderItems باید روی مدل Product تعریف شده باشد)
        if ($product->orderItems()->exists())
        {
            return ApiResponse::error(
                'این محصول در سفارش‌ها استفاده شده و قابل حذف نیست.',
                null,
                422
            );
        }

        $product->delete();

        return ApiResponse::success(null, 'محصول با موفقیت حذف شد.');
    }

    /**
     * به‌روزرسانی موجودی
     * action: increase | decrease | set
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'action'              => ['required', Rule::in(['increase', 'decrease', 'set'])],
            'quantity'            => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        return DB::transaction(function () use ($product, $validated) {
            // قفل ردیف برای جلوگیری از تغییر هم‌زمان موجودی
            $locked = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $newStock = match ($validated['action'])
            {
                'increase' => $locked->stock + $validated['quantity'],
                'decrease' => $locked->stock - $validated['quantity'],
                'set' => $validated['quantity'],
            };

            if ($newStock < 0)
            {
                return ApiResponse::error(
                    'موجودی نمی‌تواند منفی شود.',
                    null,
                    422
                );
            }

            $locked->update([
                'stock'               => $newStock,
                'low_stock_threshold' => $validated['low_stock_threshold']
                    ?? $locked->low_stock_threshold,
            ]);

            return ApiResponse::success($locked->fresh());
        });
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'brand_id'            => ['nullable', 'integer', 'exists:brands,id'],
            'category_id'         => ['nullable', 'integer', 'exists:product_categories,id'],
            'status'              => ['required', Rule::in(['active', 'inactive'])],
            'price'               => ['required', 'integer', 'min:0'],
            'sale_price'          => ['nullable', 'integer', 'min:0', 'lt:price'],
            'stock'               => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'description'         => ['nullable', 'string'],
            'star_rating'         => ['nullable', 'integer'],
        ]);
    }

    private function transformRow(Product $product): array
    {
        [$statusLabel, $statusClass, $statusIcon] = $product->status === 'active'
            ? ['فعال', 'bg-emerald-100 text-emerald-700', 'CheckCircle2']
            : ['غیرفعال', 'bg-slate-100 text-slate-600', 'CircleOff'];

        return [
            'id'                  => $product->id,
            'sku'                 => $product->sku,
            'name'                => $product->name,
            'brand'               => [
                'id'    => $product->brand?->id,
                'title' => $product->brand?->title,
            ],
            'category'            => [
                'id'    => $product->category?->id,
                'title' => $product->category?->title,
            ],
            'price'               => $product->price,
            'sale_price'          => $product->sale_price,
            'stock'               => $product->stock,
            'low_stock_threshold' => $product->low_stock_threshold,
            'status'              => $product->status,
            'status_label'        => $statusLabel,
            'status_class'        => $statusClass,
            'status_icon'         => $statusIcon,
        ];
    }
}