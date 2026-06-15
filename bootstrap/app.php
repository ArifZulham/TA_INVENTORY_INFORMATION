<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use OpenAI\Exceptions\RateLimitException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum SPA auth — izinkan cookie session untuk request API dari frontend
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Tangani rate limit OpenAI — kembalikan pesan yang mudah dipahami user
        $exceptions->render(function (RateLimitException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Kuota OpenAI API habis untuk sementara. Tunggu 1–2 menit lalu coba lagi, atau periksa billing di platform.openai.com.',
                    'error' => 'openai_rate_limit',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }
        });
    })->create();
