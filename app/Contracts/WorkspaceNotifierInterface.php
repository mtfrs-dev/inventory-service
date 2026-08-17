<?php

namespace App\Contracts;

interface WorkspaceNotifierInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function notifyItemsGenerated(array $payload): void;
}
