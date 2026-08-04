<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceTrustException extends Exception
{
    public function __construct(string $message = 'Service trust validation failed.')
    {
        parent::__construct($message, 403);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'forbidden',
            'message' => $this->getMessage(),
        ], 403);
    }
}
