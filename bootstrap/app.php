<?php

use App\Exceptions\NoActiveShiftException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PaymentMismatchException;
use App\Http\Middleware\CheckRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
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
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->throttleApi();

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                abort(401, 'Unauthenticated.');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (OutOfStockException $e) {
            return $e->render();
        });

        $exceptions->render(function (NoActiveShiftException $e) {
            return $e->render();
        });

        $exceptions->render(function (PaymentMismatchException $e) {
            return $e->render();
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && $request->wantsJson()) {
                if ($e instanceof AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }

                if ($e instanceof AuthorizationException) {
                    return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
                }

                if ($e instanceof InvalidArgumentException) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }
            }
        });
    })->create();
