<?php

use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

if (!function_exists('getAuthToken')) {
    /**
     * Test helper for JWT authentication
     */
    function getAuthToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }
} 