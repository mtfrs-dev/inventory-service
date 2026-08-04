<?php

namespace App\Exceptions;

use RuntimeException;

class SpectechMissingException extends RuntimeException
{
    public function __construct(string $projectCode, string $projectYear)
    {
        parent::__construct("Proyek \"{$projectCode}-{$projectYear}\" belum memiliki data spektek. Kategori tidak dapat ditentukan, dan generate data tidak dapat dilanjutkan.");
    }
}
