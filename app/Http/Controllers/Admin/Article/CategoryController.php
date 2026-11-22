<?php

namespace App\Http\Controllers\Admin\Article;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article\ArticleCategory;

class CategoryController extends Controller
{
    public function datatable(Request $request)
    {
        $search = $request->input('search');
        $dir = $request->input('dir', 'desc');
        $length = (int)$request->input('length', 10);
        $columnName = $request->input('column_name', 'id');

        $query = ArticleCategory::query();

        if ($search)
        {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $allowedColumns = [
            'id',
            'title',
            'slug',
            'is_active',
            'show_on_home',
            'created_at',
            'published_at',
        ];

        if (!in_array($columnName, $allowedColumns, true))
        {
            $columnName = 'id';
        }

        $dir = $dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($columnName, $dir);
        $articles = $query->paginate($length);
        $articles->getCollection()
            ->transform(function ($article) {
                return [
                    'id'                  => $article->id,
                    'title'               => $article->title,
                    'slug'                => $article->slug,
                    'main_image'          => $article->main_image,
                    'is_active'           => $article->is_active,
                    'show_on_home'        => $article->show_on_home,
                    'category_title'      => $article->category_title,
                    'submitter_full_name' => $article->submitter_full_name,
                    'jalali_submitted_at' => $article->jalali_submitted_at,
                    'jalali_published_at' => $article->jalali_published_at,
                ];
            });

        return ApiResponse::success($articles);
    }

    public function show(ArticleCategory $articleCategory)
    {
        return ApiResponse::success(['category' => $articleCategory]);
    }

    public function list()
    {
        $categories = ArticleCategory::query()->select('id', 'title')->get();

        return ApiResponse::success(['categories' => $categories]);
    }

    public function update(Request $request, ArticleCategory $articleCategory)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:Articles,slug,' . $articleCategory->id],
            'description' => ['required', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);
        $articleCategory->slug = $validated['slug'];
        $articleCategory->title = $validated['title'];
        $articleCategory->description = $validated['description'];
        $articleCategory->is_active = $validated['is_active'] ?? true;
        $articleCategory->save();

        return ApiResponse::success($articleCategory, 'دسته بندی مطلب با موفقیت ویرایش شد.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:Articles,slug'],
            'description' => ['required', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $articleCategory = new ArticleCategory();
        $articleCategory->title = $validated['title'];
        $articleCategory->slug = $validated['slug'];
        $articleCategory->description = $validated['description'];
        $articleCategory->is_active = $validated['is_active'] ?? true;
        $articleCategory->save();

        return ApiResponse::success($articleCategory, 'دسته بندی مطلب با موفقیت ایجاد شد.');
    }

    public function delete(ArticleCategory $articleCategory)
    {
        $articleCategory->delete();

        return ApiResponse::success(null, 'دسته بندی مطلب با موفقیت حذف شد.');
    }
}
