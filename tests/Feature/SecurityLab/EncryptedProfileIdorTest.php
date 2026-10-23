<?php

use App\Models\User;
use App\Support\LabCipher;

/*
 * SECURITY LAB — BRANCH 3: encrypted-id challenge. The salt-derived cipher is
 * the only thing standing between an attacker and the same IDOR as before.
 */

it('returns the salt hint when a plain numeric id is used', function () {
    $attacker = User::factory()->create();

    $this->actingAs($attacker)->getJson('/api/profile/1')
        ->assertStatus(422)
        ->assertJsonPath('message', 'kita butuh garam garam garam');
});

it('lets an attacker who knows the salt forge any identifier (IDOR)', function () {
    $attacker = User::factory()->create();
    $victim = User::factory()->create(['email' => 'victim@example.com']);

    // Attacker reproduces the identifier from the leaked salt + target id.
    $identifier = (new LabCipher(config('lab.salt')))->encryptId($victim->id);

    $this->actingAs($attacker)->getJson("/api/profile/{$identifier}")
        ->assertOk()
        ->assertJsonPath('email', 'victim@example.com')
        ->assertJsonStructure(['id', 'email', 'password_reset_token']);
});

it('rejects an identifier forged with the wrong salt', function () {
    $attacker = User::factory()->create();
    $victim = User::factory()->create();

    $wrong = (new LabCipher('not-the-real-salt'))->encryptId($victim->id);

    $this->actingAs($attacker)->getJson("/api/profile/{$wrong}")
        ->assertStatus(404);
});

it('round-trips an id through the cipher', function () {
    $cipher = new LabCipher('Y0uM16htN33dM3');

    expect($cipher->decryptId($cipher->encryptId(42)))->toBe('42');
});
