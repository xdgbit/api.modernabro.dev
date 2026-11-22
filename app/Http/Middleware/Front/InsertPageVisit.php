<?php

namespace App\Http\Middleware\Front;

use App\Models\Blog\BlogArticle;
use App\Models\PageVisit;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsertPageVisit
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if ($request->method() === 'GET')
        {
            $route_name = $request->route()->getName();
            $ip = client_ip();
            $user_customer_id = auth()->guard('user_customer')->check() ? auth()->guard('user_customer')->id() : null;
            $user_expert_id = auth()->guard('user_expert')->check() ? auth()->guard('user_expert')->id() : null;
            $page_visit = new PageVisit();
            $page_visit->user_customer_id = $user_customer_id;
            $page_visit->user_expert_id = $user_expert_id;
            $page_visit->route_name = $route_name;
            if ($route_name === 'frontend.articles.details')
            {
                $slug = $request->route('article_slug');
                $blog_article = BlogArticle::where('slug', $slug)->first();
                $page_visit->target_type = 'article';
                if ($blog_article)
                {
                    $page_visit->target_id = $blog_article->id;
                }
            }
            $page_visit->url = $request->url();
            $page_visit->ip = $ip;
            $page_visit->save();
        }

        return $next($request);
    }
}
