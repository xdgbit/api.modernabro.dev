<?php

namespace App\Http\Controllers\Admin\Product;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = [
        'id',
        'title',
        'slug',
        'sort_order',
        'children_count',
        'products_count',
    ];

    private const IMAGE_DISK = 'public';

    private const IMAGE_DIR = 'categories';

    public function stats()
    {
        $total = ProductCategory::count();

        $rootCount = ProductCategory::whereNull('parent_id')->count();

        $childCount = ProductCategory::whereNotNull('parent_id')->count();

        // دسته‌هایی که هیچ محصولی ندارند
        $emptyQuery = ProductCategory::query()
            ->withCount('products')
            ->having('products_count', '=', 0);

        $emptyCount = (clone $emptyQuery)->count();

        $emptyCategories = (clone $emptyQuery)
            ->with('parent:id,title')
            ->orderBy('title')
            ->limit(50)
            ->get()
            ->map(fn(ProductCategory $category) => [
                'id'     => $category->id,
                'title'  => $category->title,
                'parent' => $category->parent
                    ? ['id' => $category->parent->id, 'title' => $category->parent->title]
                    : null,
            ])
            ->values();

        return ApiResponse::success([
            'total'            => $total,
            'root_count'       => $rootCount,
            'child_count'      => $childCount,
            'empty_count'      => $emptyCount,
            'fa_empty_count'   => ConvertNumbersEntoFa($emptyCount),
            'empty_categories' => $emptyCategories,
        ]);
    }

    public function datatable(Request $request)
    {
        $search = trim((string)$request->input('search', ''));
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $length = min(max((int)$request->input('length', 10), 1), 100);

        $columnName = $request->input('column_name');
        $columnName = in_array($columnName, self::ALLOWED_SORT_COLUMNS, true)
            ? $columnName
            : 'id';

        $query = ProductCategory::query()
            ->with('parent:id,title')
            ->withCount(['children', 'products']);

        if ($search !== '')
        {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $this->applyFilters($query, $request);

        $categories = $query
            ->orderBy($columnName, $dir)
            ->paginate($length);

        $categories->getCollection()->transform(
            fn(ProductCategory $category) => $this->transformRow($category)
        );

        return ApiResponse::success($categories);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('title'))
        {
            $query->where('title', 'like', '%' . trim($request->title) . '%');
        }

        if ($request->filled('slug'))
        {
            $query->where('slug', 'like', '%' . trim($request->slug) . '%');
        }

        if ($request->filled('type'))
        {
            match ($request->input('type'))
            {
                'root'  => $query->whereNull('parent_id'),
                'child' => $query->whereNotNull('parent_id'),
                default => null,
            };
        }

        if ($request->filled('parent_id'))
        {
            $query->where('parent_id', (int)$request->parent_id);
        }
    }

    public function jspanel(Request $request)
    {
        $categoryId = $request->input('category_id');

        // فقط دسته‌های اصلی به عنوان والد انتخاب می‌شوند
        $parents = ProductCategory::query()
            ->whereNull('parent_id')
            ->select(['id', 'title'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $category = null;

        if ($categoryId)
        {
            $category = ProductCategory::query()->find($categoryId);

            if (!$category)
            {
                return ApiResponse::error('دسته‌بندی موردنظر پیدا نشد.', null, 404);
            }

            $this->withImageUrl($category);
        }

        return ApiResponse::success([
            'parents'  => $parents,
            'category' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        // در ایجاد، عکس قبلی وجود ندارد
        $validated['image'] = $this->resolveImagePath($request, null);

        $category = ProductCategory::create($validated);

        return ApiResponse::success(
            $this->withImageUrl($category->fresh('parent')),
            'دسته‌بندی با موفقیت ایجاد شد.'
        );
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $this->validateCategory($request, $productCategory);

        // دسته نمی‌تواند والد خودش یا زیرمجموعه‌اش باشد
        if (!empty($validated['parent_id']))
        {
            $parent = ProductCategory::find($validated['parent_id']);

            if ($parent?->parent_id !== null || $parent?->id === $productCategory->id)
            {
                return ApiResponse::error(
                    'دسته والد نامعتبر است. فقط دسته‌های اصلی می‌توانند والد باشند.',
                    null,
                    422
                );
            }

            // اگر این دسته خودش زیرمجموعه دارد، نمی‌تواند زیر دسته دیگری برود
            if ($productCategory->children()->exists())
            {
                return ApiResponse::error(
                    'این دسته زیرمجموعه دارد و نمی‌تواند زیر دسته دیگری قرار گیرد.',
                    null,
                    422
                );
            }
        }

        // فایل را فقط بعد از پاس شدن همه اعتبارسنجی‌ها ذخیره می‌کنیم
        $oldImage = $productCategory->image;

        $validated['image'] = $this->resolveImagePath($request, $oldImage);

        $productCategory->update($validated);

        // اگر عکس عوض یا حذف شد، فایل قبلی پاک می‌شود
        if ($oldImage !== $productCategory->image)
        {
            $this->deleteImage($oldImage);
        }

        return ApiResponse::success(
            $this->withImageUrl($productCategory->fresh('parent')),
            'دسته‌بندی با موفقیت ویرایش شد.'
        );
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->exists())
        {
            return ApiResponse::error(
                'این دسته‌بندی محصول دارد و قابل حذف نیست.',
                null,
                422
            );
        }

        if ($productCategory->children()->exists())
        {
            return ApiResponse::error(
                'این دسته‌بندی زیرمجموعه دارد و قابل حذف نیست.',
                null,
                422
            );
        }

        $oldImage = $productCategory->image;

        $productCategory->delete();

        $this->deleteImage($oldImage);

        return ApiResponse::success(null, 'دسته‌بندی با موفقیت حذف شد.');
    }

    private function validateCategory(Request $request, ?ProductCategory $category = null): array
    {
        $validated = $request->validate([
            'parent_id'    => ['nullable', 'integer', 'exists:product_categories,id'],
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_categories', 'slug')->ignore($category?->id),
            ],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        // این دو کلید ستون دیتابیس نیستند و جداگانه پردازش می‌شوند
        unset($validated['image'], $validated['remove_image']);

        // نامک خالی از عنوان ساخته می‌شود و یکتا می‌ماند
        $validated['slug'] = !empty($validated['slug'])
            ? $validated['slug']
            : $this->makeUniqueSlug($validated['title'], $category?->id);

        $validated['sort_order'] = $validated['sort_order'] ?? ($category?->sort_order ?? 0);

        return $validated;
    }

    /**
     * مسیر عکس نهایی را برمی‌گرداند:
     * - اگر فایل جدید آمده باشد، ذخیره و مسیر جدید برگردانده می‌شود.
     * - اگر remove_image فرستاده شده باشد، null برمی‌گردد.
     * - در غیر این صورت مسیر فعلی بدون تغییر می‌ماند.
     */
    private function resolveImagePath(Request $request, ?string $currentPath): ?string
    {
        if ($request->hasFile('image'))
        {
            return $request->file('image')->store(self::IMAGE_DIR, self::IMAGE_DISK);
        }

        if ($request->boolean('remove_image'))
        {
            return null;
        }

        return $currentPath;
    }

    /**
     * فقط فایل‌های داخل پوشه categories پاک می‌شوند تا فایل‌های دیگر آسیب نبینند.
     */
    private function deleteImage(?string $path): void
    {
        if (!$path || preg_match('#^https?://#', $path))
        {
            return;
        }

        // مسیرهای قدیمی ممکن است با /storage/ شروع شوند
        $relative = ltrim(preg_replace('#^/?storage/#', '', $path), '/');

        if (!str_starts_with($relative, self::IMAGE_DIR . '/'))
        {
            return;
        }

        Storage::disk(self::IMAGE_DISK)->delete($relative);
    }

    private function imageUrl(?string $path): ?string
    {
        if (!$path)
        {
            return null;
        }

        if (preg_match('#^https?://#', $path) || str_starts_with($path, '/storage/'))
        {
            return $path;
        }

        return Storage::disk(self::IMAGE_DISK)->url($path);
    }

    private function withImageUrl(ProductCategory $category): ProductCategory
    {
        $category->image_url = $this->imageUrl($category->image);

        return $category;
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'category';
        $slug = $base;
        $i = 2;

        while (
        ProductCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        )
        {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function transformRow(ProductCategory $category): array
    {
        return [
            'id'             => $category->id,
            'title'          => $category->title,
            'slug'           => $category->slug,
            'parent'         => $category->parent
                ? ['id' => $category->parent->id, 'title' => $category->parent->title]
                : null,
            'children_count' => $category->children_count,
            'products_count' => $category->products_count,
            'sort_order'     => $category->sort_order,
            'image_url'      => $this->imageUrl($category->image),
        ];
    }
}