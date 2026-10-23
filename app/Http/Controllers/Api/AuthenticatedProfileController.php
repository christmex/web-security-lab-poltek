<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * SECURITY LAB — BRANCH 2 (intentionally vulnerable).
 *
 * The endpoint now requires a logged-in session, but that is the ONLY check.
 * There is no object-level authorization, so IDOR remains: any authenticated
 * user can walk the id and read every other account (still dumping the
 * plaintext reset token). The response also leaks a `salt` value — the
 * breadcrumb needed to solve branch 3.
 *
 * DO NOT copy this into real code.
 */
class AuthenticatedProfileController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $user->makeVisible([
            'password',
            'password_reset_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'remember_token',
        ]);

        return response()->json([
            ...$user->toArray(),
            'salt' => config('lab.salt'),
        ]);
    }
}
