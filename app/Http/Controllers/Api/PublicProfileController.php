<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * SECURITY LAB — BRANCH 1 (intentionally vulnerable).
 *
 * Vulnerability: Insecure Direct Object Reference (IDOR) + sensitive data
 * exposure. The endpoint is public (no authentication) and returns a user by
 * the raw id from the URL, dumping every column — including the plaintext
 * password-reset token. Walking the id (/1, /2, /3, ...) exposes every account.
 *
 * DO NOT copy this into real code.
 */
class PublicProfileController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Leak everything, including normally-hidden secrets.
        $user->makeVisible([
            'password',
            'password_reset_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'remember_token',
        ]);

        return response()->json($user->toArray());
    }
}
