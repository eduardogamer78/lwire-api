<?php

use App\Models\User;
use function Pest\Laravel\postJson;

/** @test Auth - Logout */
it('should user can logout', function () {
    $user = User::factory()->create();
    $access_token = $user->createToken('test_2e')->plainTextToken;

    postJson(route('auth.logout'), [], [
        'Authorization' => "Bearer {$access_token}",
    ])
    ->assertStatus(204);
});

/** @test Unauthenticated Auth - Logout */
it('should user unauthenticated logout', function () {
    $user = User::factory()->create();
    $access_token = $user->createToken('test_2e')->plainTextToken;

    postJson(route('auth.logout'), [], [])
    ->assertJson(['message' => 'Unauthenticated.'])
    ->assertStatus(401);
});

