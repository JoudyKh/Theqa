<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckAbilities;
use App\Http\Middleware\TrackLastActiveUser;
use App\Http\Middleware\CheckBannedMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['api', 'banned'])
                ->prefix('api/v1')
                ->group(base_path('routes/v1/app/api.php'));
            Route::middleware(['api'])
                ->prefix('api/v1/admin')
                ->group(base_path('routes/v1/admin/api.php'));
            Route::withoutMiddleware(['lms-config'])
                ->group(base_path('routes/web.php'));

        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            SetLocale::class,
        ]);

        $middleware->alias([
            'banned' => CheckBannedMiddleware::class,
            'last.active' => TrackLastActiveUser::class,
            'ability' => CheckAbilities::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })->create();
