<?php

use App\Http\Middleware\JWTAuthenticate;
use App\Http\Middleware\JWTRefreshToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.jwt' => JWTAuthenticate::class,
            'jwt.refresh' => JWTRefreshToken::class,
            'throttle' => ThrottleRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $code = 500;
                $message = $e->getMessage();
                $data = null;

                if ($e instanceof TokenExpiredException) {
                    $code = 401;
                    $message = 'Token has expired';
                } elseif ($e instanceof TokenInvalidException) {
                    $code = 401;
                    $message = 'Token is invalid';
                } elseif ($e instanceof JWTException) {
                    $code = 401;
                    $message = 'Token is missing';
                } elseif ($e instanceof AuthenticationException) {
                    $code = 401;
                    $message = 'Unauthenticated';
                } elseif ($e instanceof ValidationException) {
                    $code = 422;
                    $message = 'Validation Error';
                    $data = $e->errors();
                } elseif ($e instanceof ModelNotFoundException) {
                    $code = 404;
                    $message = 'Resource not found';
                } elseif ($e instanceof NotFoundHttpException) {
                    $code = 404;
                    $message = 'Endpoint not found';
                } else {
                    $code = $e instanceof HttpException ? $e->getStatusCode() : 500;
                    $message = $code == 500 ? 'Server Error' : $message;
                    if (config('app.debug')) {
                        $data = [
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ];
                    }
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'data' => $data,
                ], $code);
            }
        });
    })->create();
