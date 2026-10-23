<?php

namespace App\Support;

/**
 * SECURITY LAB — BRANCH 3 helper.
 *
 * Turns a user id into the opaque URL identifier using the leaked salt:
 *
 *   identifier = md5(salt . '+' . id)      e.g. md5("Y0uM16htN33dM3+1")
 *
 * The literal '+' separator matches the in-response hint ("garam+1 adalah
 * koenci"), so a student hashes exactly what the hint spells out. It is a
 * one-way hash, so the server cannot "decrypt" it — instead it hashes every
 * user's id with the salt and looks for a match (see the controller).
 */
class ProfileIdentifier
{
    public static function for(int|string $id, ?string $salt = null): string
    {
        return md5(($salt ?? config('lab.salt')).'+'.$id);
    }
}
