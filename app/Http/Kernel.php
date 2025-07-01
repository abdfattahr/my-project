<?php

namespace App\Http;

// استيراد الفئات المفقودة
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // الميدل وير العامة (تُطبق على كل الطلبات)
        /** @suppress P1009 */
        TrustProxies::class, // السطر 11
        \Illuminate\Http\Middleware\HandleCors::class,
        PreventRequestsDuringMaintenance::class, // السطر 13
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        TrimStrings::class, // السطر 15
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class, // السطر 21
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            VerifyCsrfToken::class, // السطر 25
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

       'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
       ],
        // تأكد من أن auth:api أو auth:sanctum غير موجود هنا
    ];

    protected $routeMiddleware = [
        'auth' => Authenticate::class, // السطر 37
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => RedirectIfAuthenticated::class, // السطر 41
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

}
