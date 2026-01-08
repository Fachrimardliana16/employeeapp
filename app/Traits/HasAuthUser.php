<?php

namespace App\Traits;

use App\Models\User;

trait HasAuthUser
{
    /**
     * Get the authenticated user with proper type hinting
     */
    protected function getAuthUser(): User
    {
        /** @var User $user */
        $user = auth()->user();
        
        return $user;
    }
    
    /**
     * Get the authenticated user ID
     */
    protected function getAuthUserId(): int
    {
        return (int) auth()->id();
    }
}
