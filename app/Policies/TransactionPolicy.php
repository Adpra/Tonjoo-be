<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\TransactionHeader;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransactionHeader $transactionHeader): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransactionHeader $transactionHeader): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransactionHeader $transactionHeader): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TransactionHeader $transactionHeader): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TransactionHeader $transactionHeader): bool
    {
        return false;
    }
}
