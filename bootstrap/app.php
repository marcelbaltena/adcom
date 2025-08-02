<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Verwijder Inertia middleware als je geen Inertia gebruikt
        // Alleen de standaard Laravel middleware
        
        // Middleware aliases voor je permission systeem
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'project.access' => \App\Http\Middleware\CheckProjectAccess::class,
            'company.access' => \App\Http\Middleware\CheckCompanyAccess::class,
            'active.user' => \App\Http\Middleware\CheckActiveUser::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
        ]);

        // Middleware groups
        $middleware->group('admin', [
            'auth',
            'verified',
            'active.user',
            'role:admin',
            'log.activity'
        ]);

        $middleware->group('manager', [
            'auth',
            'verified',
            'active.user',
            'role:admin,beheerder,account_manager',
            'log.activity'
        ]);

        $middleware->group('project-member', [
            'auth',
            'verified',
            'active.user',
            'project.access'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();