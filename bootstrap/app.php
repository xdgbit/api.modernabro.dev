<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        /*
        |--------------------------------------------------------------------------
        | Global Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->append([
             \App\Http\Middleware\SetDebugForDeveloper::class,
            // \App\Http\Middleware\RedirectIndexPHP::class,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Middleware Groups
        |--------------------------------------------------------------------------
        */

        $middleware->web([
            // Middlewareهای اضافه گروه web
        ]);

       /* $middleware->api([
            \App\Http\Middleware\MaintenanceSystem::class,
        ]);*/


        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([

            /*
            |--------------------------------------------------------------------------
            | Laravel
            |--------------------------------------------------------------------------
            */
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,

            'auth.basic' =>
                \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,

            'cache.headers' =>
                \Illuminate\Http\Middleware\SetCacheHeaders::class,

            'can' =>
                \Illuminate\Auth\Middleware\Authorize::class,

            'guest' =>
                \App\Http\Middleware\RedirectIfAuthenticated::class,

            'password.confirm' =>
                \Illuminate\Auth\Middleware\RequirePassword::class,

            'signed' =>
                \Illuminate\Routing\Middleware\ValidateSignature::class,

            'throttle' =>
                \Illuminate\Routing\Middleware\ThrottleRequests::class,


            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            'verified_email' =>
                \App\Http\Middleware\Customer\RedirectIfNotVerifyEmail::class,

            'verified_mobile' =>
                \App\Http\Middleware\Customer\RedirectIfNotVerifyMobile::class,

            'just_unverified_email' =>
                \App\Http\Middleware\Customer\JustUnverifiedEmail::class,

            'must_authenticated' =>
                \App\Http\Middleware\Customer\RedirectIfNotAuthenticated::class,

            'check_agent' =>
                \App\Http\Middleware\Customer\RedirectIfChangeAgent::class,

            'check_auth_document' =>
                \App\Http\Middleware\Customer\CheckAuthDocument::class,

            'payment_status_system' =>
                \App\Http\Middleware\Customer\PaymentStatusSystem::class,

            'p2p_status_system' =>
                \App\Http\Middleware\Customer\P2PStatusSystem::class,

            'b2c_status_system' =>
                \App\Http\Middleware\Customer\B2CStatusSystem::class,

            'check_ban_state' =>
                \App\Http\Middleware\Customer\RedirectIfBaned::class,

            'wallet_update_deposit_limit' =>
                \App\Http\Middleware\Customer\RateLimitWalletUpdate::class,

            'check_lack_of_activity' =>
                \App\Http\Middleware\Customer\RedirectLockForDocument::class,

            'check_freeze_state' =>
                \App\Http\Middleware\Customer\ErrorIfFreeze::class,


            /*
            |--------------------------------------------------------------------------
            | Expert
            |--------------------------------------------------------------------------
            */

            'just_super_admin' =>
                \App\Http\Middleware\Expert\JustSuperAdmin::class,

            'expert_session_timeout' =>
                \App\Http\Middleware\CheckSessionTimeOutForExperts::class,

            'expert_secure_session' =>
                \App\Http\Middleware\Expert\SecureSession::class,


            /*
            |--------------------------------------------------------------------------
            | Front
            |--------------------------------------------------------------------------
            */

            'insert_visit' =>
                \App\Http\Middleware\Front\InsertPageVisit::class,

            'detect_google_search' =>
                \App\Http\Middleware\Front\DetectGoogleSearch::class,


            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            'restricts_country' =>
                \App\Http\Middleware\RestrictsCountry::class,

            'maintenance_system' =>
                \App\Http\Middleware\MaintenanceSystem::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $e->getMessage(),
                    $e->errors(),
                    422
                );
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('مورد درخواستی پیدا نشد.', null, 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('احراز هویت انجام نشده است.', null, 401);
            }
        });
    })

    ->create();