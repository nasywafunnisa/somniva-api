<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    // Untuk /api/*, jangan mengarahkan ke halaman login (tidak ada di API ini).
    $middleware->redirectGuestsTo(
        fn ($request) => $request->is('api/*') ? null : route('login')
    );
})
    ->withExceptions(function (Exceptions $exceptions): void {
    // Semua permintaan ke /api/* selalu dijawab JSON (401, 422, dll),
    // walaupun header Accept lupa dikirim.
    $exceptions->shouldRenderJsonWhen(
        fn ($request, $e) => $request->is('api/*') || $request->expectsJson()
    );
})->create();
