<?php

namespace App\Http\Controllers\Admin\Article;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Article\Article;
use App\Http\Helpers\Classes\LFM;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    public function datatable(Request $request)
    {
        $search = $request->input('search');
        $dir = $request->input('dir', 'desc');
        $length = (int)$request->input('length', 10);
        $columnName = $request->input('column_name', 'id');

        $query = Article::query()->with('category');

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

    public function show(Article $Article)
    {
        $Article->setAppends(['main_image']);

        return ApiResponse::success(['article' => $Article]);
    }

    public function update(Request $request, Article $Article)
    {
        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:articles,slug,' . $Article->id],
            'short_description' => ['required', 'string', 'max:700'],
            'description'       => ['required', 'string'],
            'tags'              => ['nullable', 'string', 'max:1000'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'published_at'      => ['nullable', 'date'],
            'is_active'         => ['nullable', 'boolean'],
            'show_on_homepage'  => ['nullable', 'boolean'],
            'category_id'       => ['required', 'integer', 'exists:article_categories,id'],
        ]);

        $Article->title = $validated['title'];
        $Article->slug = $validated['slug'];
        $Article->short_description = $validated['short_description'];
        $Article->description = $validated['description'];
        $Article->tags = $validated['tags'] ?? null;
        $Article->published_at = $validated['published_at'] ?? null;
        $Article->is_active = $validated['is_active'] ?? true;
        $Article->show_on_homepage = $validated['show_on_homepage'] ?? false;

        $Article->category_id = $validated['category_id'];

        if ($request->hasFile('image'))
        {
            $file = LFM::fileUpload($validated['image'], 'article_img', 'user_expert');
            $Article->image_id = $file->id;
        }

        $Article->save();

        return ApiResponse::success($Article, 'وبلاگ با موفقیت ویرایش شد.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:articles,slug'],
            'short_description' => ['required', 'string', 'max:500'],
            'description'       => ['required', 'string'],
            'tags'              => ['nullable', 'string', 'max:1000'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'published_at'      => ['nullable', 'date'],
            'category_id'       => ['required', 'integer', 'exists:article_categories,id'],
            'is_active'         => ['nullable', 'boolean'],
            'show_on_homepage'  => ['nullable', 'boolean'],
        ]);

        $Article = new Article();
        $Article->title = $validated['title'];
        $Article->slug = $validated['slug'];
        $Article->short_description = $validated['short_description'];
        $Article->description = $validated['description'];
        $Article->tags = $validated['tags'] ?? null;
        $Article->published_at = $validated['published_at'] ?? null;
        $Article->is_active = $validated['is_active'] ?? true;
        $Article->show_on_homepage = $validated['show_on_homepage'] ?? false;
        $Article->category_id = $validated['category_id'];

        if ($request->hasFile('image'))
        {
            $file = LFM::fileUpload(
                $validated['image'],
                'city_img',
                'user_expert'
            );

            $Article->image_id = $file->id;
        }
        $Article->submitted_at = Carbon::now();
        $Article->submitted_by = auth()->guard('user_expert')->id();
        $Article->save();

        return ApiResponse::success($Article, 'وبلاگ با موفقیت ایجاد شد.');
    }

    public function delete(Article $Article)
    {
        $Article->delete();

        return ApiResponse::success(null, 'وبلاگ با موفقیت حذف شد.');
    }
}
