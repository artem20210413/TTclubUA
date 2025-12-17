<?php

namespace App\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\UserResolver as UserResolverContract;

class UserResolver implements UserResolverContract
{
    /**
     * {@inheritdoc}
     */
    public static function resolve(): ?Authenticatable
    {
        return Auth::user();
    }
}
