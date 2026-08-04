<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenRevokedException extends Exception
{
    public function __construct(string $message = 'The access token has been revoked.')
    {
        parent::__construct($message, 401);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'unauthorized',
            'message' => $this->getMessage(),
        ], 401);
    }
}
