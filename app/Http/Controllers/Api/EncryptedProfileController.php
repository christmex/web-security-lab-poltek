<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ProfileIdentifier;
use Illuminate\Http\JsonResponse;

/**
 * SECURITY LAB — BRANCH 3 (challenge).
 *
 * The URL no longer takes a raw id. The identifier is md5(salt . id). The IDOR
 * is "fixed" only by obscurity: a student who recovered the salt in branch 2
 * can hash any id themselves and still walk every account.
 *
 * Passing a plain numeric id returns a hint instead of data.
 */
class EncryptedProfileController extends Controller
{
    public function show(string $identifier): JsonResponse
    {
        // Someone is still trying the old numeric id -> nudge them toward the salt.
        if (ctype_digit($identifier) && strlen($identifier) <= 10) {
            return response()->json([
                'message' => 'kita butuh garam garam garam',
                'hint' => 'semua cabang saling terhubung, terkadang kita butuh garam, garam+1 adalah koenci',
            ], 422);
        }

        // One-way hash, so match by hashing every user's id with the salt.
        $identifier = strtolower($identifier);
        $userId = User::pluck('id')->first(
            fn ($id) => ProfileIdentifier::for($id) === $identifier
        );

        if ($userId === null) {
            return response()->json([
                'message' => 'identifier tidak valid',
                'hint' => 'garam kamu salah, atau cara hash-nya belum pas.',
            ], 404);
        }

        $user = User::find($userId);

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
