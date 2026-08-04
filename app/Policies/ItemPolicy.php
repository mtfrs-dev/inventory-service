<?php

namespace App\Policies;

use App\Auth\AuthenticatedUser;
use App\Models\Item;

class ItemPolicy
{
    public function view(AuthenticatedUser $user, ?Item $item = null): bool
    {
        return $user->hasPermission('item.view');
    }

    public function create(AuthenticatedUser $user): bool
    {
        return $user->hasPermission('item.create');
    }

    public function update(AuthenticatedUser $user, Item $item): bool
    {
        return $user->hasPermission('item.update');
    }

    public function delete(AuthenticatedUser $user, Item $item): bool
    {
        return $user->hasPermission('item.delete');
    }
}
