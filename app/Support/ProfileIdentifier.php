<?php

namespace App\Support;

/**
 * SECURITY LAB — BRANCH 3 helper.
 *
 * Turns a user id into the opaque URL identifier using the leaked salt:
 *
 *   identifier = md5(salt . id)
 *
 * It is a one-way hash, so the server cannot "decrypt" it — instead it hashes
 * every user's id with the salt and looks for a match (see the controller).
 * A student who recovered the salt (branch 2) can reproduce the hash for any id
 * with a single md5 call in any tool.
 */
class ProfileIdentifier
{
    public static function for(int|string $id, ?string $salt = null): string
    {
        return md5(($salt ?? config('lab.salt')).$id);
    }
}
