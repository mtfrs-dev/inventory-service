<?php

namespace App\Exceptions;

use RuntimeException;

class CategoryItemCountEmptyException extends RuntimeException
{
    public function __construct(string $projectCode, string $projectYear)
    {
        parent::__construct("Beberapa kategori dalam proyek \"{$projectCode}-{$projectYear}\" memiliki total item 0 atau NULL. Generate data tidak dapat dilanjutkan.");
    }
}
