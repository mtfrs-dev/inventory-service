<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InvalidTokenException extends Exception
{
    public function __construct(string $message = 'The access token is invalid.', ?Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'unauthorized',
            'message' => $this->getMessage(),
        ], 401);
    }
}
