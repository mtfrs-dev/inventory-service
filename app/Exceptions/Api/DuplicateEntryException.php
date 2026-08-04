<?php

namespace App\Exceptions\Api;

class DuplicateEntryException extends ApiException
{
    protected int $statusCode = 409;
    protected $message = 'This record already exists.';
}