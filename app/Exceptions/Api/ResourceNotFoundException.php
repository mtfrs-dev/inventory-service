<?php

namespace App\Exceptions\Api;

class ResourceNotFoundException extends ApiException
{
    protected int $statusCode = 404;
    protected $message = 'The requested resource was not found.';
}