<?php

namespace App\Exceptions\Api;

class IncompleteProjectDataException extends BusinessRuleException
{
    /**
     * @param  array<int, string>  $missingFields
     */
    public function __construct(array $missingFields)
    {
        parent::__construct(
            'Data proyek belum lengkap di layanan PM (field kosong: '.implode(', ', $missingFields).'). '
            .'Lengkapi data proyek terlebih dahulu sebelum generate item.'
        );
    }
}
