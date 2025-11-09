<?php

namespace Tests\Unit;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;

class JwtTestHelper
{
    /**
     * Generate a valid JWT token for testing
     */
    public static function generateToken($userId, $expiresInMinutes = 60): string
    {
        $privateKey = config('services.crypt.private');
        
        $payload = [
            'key' => $userId,
            'exp' => now()->addMinutes($expiresInMinutes)->timestamp,
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * Generate an expired JWT token
     */
    public static function generateExpiredToken($userId): string
    {
        $privateKey = config('services.crypt.private');
        
        $payload = [
            'key' => $userId,
            'exp' => now()->subMinutes(10)->timestamp,
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * Generate a refresh token (valid for 30 days)
     */
    public static function generateRefreshToken($userId, $expiresInDays = 30): string
    {
        $privateKey = config('services.crypt.private');
        
        $payload = [
            'key' => $userId,
            'exp' => now()->addDays($expiresInDays)->timestamp,
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * Generate a token with invalid signature
     */
    public static function generateInvalidSignatureToken($userId): string
    {
        return "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJrZXkiOiIxIiwiZXhwIjoxOTI2MzQwMjAwfQ.invalid_signature_here";
    }

    /**
     * Generate a malformed token
     */
    public static function generateMalformedToken(): string
    {
        return "this.is.not.a.valid.jwt.token.at.all";
    }
}

