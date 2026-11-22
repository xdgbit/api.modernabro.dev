<?php

namespace App\Providers;

use App\Models\Location\City;
use App\Models\SystemConfig;
use App\Models\User\UserExpert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Gate::before(function ($user, $ability) {
            if (
                $user instanceof UserExpert &&
                $user->hasRole('super_admin', 'user_expert')
            ) {
                return true;
            }

            return null;
        });

        Collection::macro('setAppends', function ($attributes) {
            return $this->map(function ($item) use ($attributes) {
                return $item->setAppends($attributes);
            });
        });

        Collection::macro('makeHidden', function ($attributes) {
            return $this->map(function ($item) use ($attributes) {
                return $item->makeHidden($attributes);
            });
        });

        view()->composer('frontend.layouts.master', function ($query) {
            $cities = City::query()
                ->where('is_active', true)
                ->where('show_on_home', true)
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                    'slug',
                    'image_id',
                ]);
            $SystemConfig = SystemConfig::query()->first();
            $query->with(['cities' => $cities, 'system_config' => $SystemConfig]);
        });
        Validator::extend('iran_mobile_number', function ($attribute, $value, $parameters) {
            if ((bool)preg_match('/^09\d{9}$/', $value))
            {
                return true;
            }

            return false;
        });
    }
}
