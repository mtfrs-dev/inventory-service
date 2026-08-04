<?php

namespace App\Exceptions\Api;

class BusinessRuleException extends ApiException
{
    protected int $statusCode = 422;
    protected $message = 'This action violates a business rule.';
}