<?php

use App\Models\User;

/*
 * SECURITY LAB — BRANCH 2: auth-gated IDOR. Login is enforced, but there is no
 * object-level authorization, so the IDOR still works between accounts.
 */

it('blocks the profile API for guests', function () {
    $victim = User::factory()->create();

    $this->getJson("/api/profile/{$victim->id}")->assertUnauthorized();
});

it('lets any logged-in user read any other user (IDOR) and leaks the salt', function () {
    $attacker = User::factory()->create();
    $victim = User::factory()->create(['email' => 'victim@example.com']);

    $response = $this->actingAs($attacker)->getJson("/api/profile/{$victim->id}");

    $response->assertOk()
        ->assertJsonPath('email', 'victim@example.com')
        ->assertJsonPath('salt', config('lab.salt'))
        ->assertJsonStructure(['id', 'email', 'password_reset_token', 'salt']);
});
