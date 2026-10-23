<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LabCipher;
use Illuminate\Http\JsonResponse;

/**
 * SECURITY LAB — BRANCH 3 (challenge).
 *
 * The URL no longer takes a raw id. The identifier is the user id encrypted
 * with the leaked salt (see LabCipher). The IDOR is "fixed" only by obscurity:
 * a student who recovered the salt in branch 2 can encrypt any id themselves
 * and still walk every account.
 *
 * Passing a plain numeric id returns a hint instead of data.
 */
class EncryptedProfileController extends Controller
{
    public function show(string $identifier, LabCipher $cipher): JsonResponse
    {
        // Someone is still trying the old numeric id -> nudge them toward the salt.
        if (ctype_digit($identifier)) {
            return response()->json([
                'message' => 'kita butuh garam garam garam',
                'hint' => 'id di URL sudah dienkripsi. Enkripsi id target pakai salt (ingat branch 2): key = sha256(salt), lalu AES-256-CBC, hasilnya base64url.',
            ], 422);
        }

        $id = $cipher->decryptId($identifier);

        if ($id === null || ! ctype_digit($id)) {
            return response()->json([
                'message' => 'identifier tidak valid',
                'hint' => 'garam kamu salah, atau cara enkripsinya belum pas.',
            ], 404);
        }

        $user = User::findOrFail($id);

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
