<?php

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class ApiException extends Exception
{
    protected int $statusCode   = 500;
    protected array $errors     = [];

    public function __construct(string $message = '', array $errors = [])
    {
        parent::__construct($message ?: $this->message);
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        $payload = ['success' => false, 'message' => $this->getMessage()];

        if (!empty($this->errors)) {
            $payload['errors'] = $this->errors;
        }

        return response()->json($payload, $this->statusCode);
    }
}