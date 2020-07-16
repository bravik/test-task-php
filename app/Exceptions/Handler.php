<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    public function __construct()
    {
        $this->dontReport = [
            AuthorizationException::class,
            HttpException::class,
            ModelNotFoundException::class,
            ValidationException::class
        ];
    }

    /**
     * Render an exception into formatted Json Http response
     *
     * @param  Request  $request
     * @param Exception $e
     * @return JsonResponse
     */
    public function render($request, Exception $e): JsonResponse
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        $content = [
            'message' => $e->getMessage(),
        ];

        if (env("APP_DEBUG")) {
            $content['trace'] = $e->getTraceAsString();
        }

        if ($e instanceof EntityValidationException) {
            $status = $e->getCode();
            $content['errors'] = $e->getErrors();
        }

        return new JsonResponse($content, $status);
    }
}
