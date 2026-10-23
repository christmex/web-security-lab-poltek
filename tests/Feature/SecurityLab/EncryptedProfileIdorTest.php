<?php

use App\Models\User;
use App\Support\ProfileIdentifier;

/*
 * SECURITY LAB — BRANCH 3: hashed-id challenge. identifier = md5(salt . '+' . id).
 * The salt is the only thing between an attacker and the same IDOR as before.
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
    $identifier = md5(config('lab.salt').'+'.$victim->id);

    $this->actingAs($attacker)->getJson("/api/profile/{$identifier}")
        ->assertOk()
        ->assertJsonPath('email', 'victim@example.com')
        ->assertJsonStructure(['id', 'email', 'password_reset_token']);
});

it('rejects an identifier built with the wrong salt', function () {
    $attacker = User::factory()->create();
    $victim = User::factory()->create();

    $wrong = md5('not-the-real-salt'.'+'.$victim->id);

    $this->actingAs($attacker)->getJson("/api/profile/{$wrong}")
        ->assertStatus(404);
});

it('redirects the example hint identifier to the rickroll', function () {
    $attacker = User::factory()->create();

    $this->actingAs($attacker)
        ->get('/api/profile/75170fc230cd88f32e475ff4087f81d9')
        ->assertRedirect('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
});

it('helper matches a raw md5 of salt and id', function () {
    expect(ProfileIdentifier::for(2, 'Y0uM16htN33dM3'))
        ->toBe(md5('Y0uM16htN33dM3+2'));
});
