<?php

use App\Models\User;

use function Pest\Laravel\postJson;

/** @test Auth - Login */
it('should auth user', function () {
    $user = User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => 'password',
        'device' => 'e2e_test',
    ];

    postJson(route('auth.login'), $data)
       ->assertJsonStructure(['access_token'])
       ->assertStatus(201);
});

/** @test Password */
it('should fail auth - with wrong password', function () {
    $user = User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => '123123123',
        'device' => 'e2e_test',
    ];

    postJson(route('auth.login'), $data)->assertStatus(422);
});

/** @test Email */
it('should fail auth - with wrong email', function () {
    $user = User::factory()->create();
    $data = [
        'email' => 'faker@email.com',
        'password' => 'password',
        'device' => 'e2e_test',
    ];

    postJson(route('auth.login'), $data)->assertStatus(422);
});

/** @test Validation - Email - Password - Device-Name */
describe('validation', function () {

    it('should require email', function () {
        postJson(route('auth.login'), [
            'password' => 'password',
            'device' => 'e2e_test',
        ])
        ->assertJsonValidationErrors([
            'email' => trans('validation.required', ['attribute' => 'email']),
        ])->assertStatus(422);
    });

    it('should require password', function () {
        $user = User::factory()->create();
        postJson(route('auth.login'), [
            'email' => $user->email,
            'device' => 'e2e_test',
        ])
        ->assertJsonValidationErrors([
            'password' => trans('validation.required', ['attribute' => 'password']),
        ])->assertStatus(422);
    });

    it('should require device name', function () {
        $user = User::factory()->create();
        postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertJsonValidationErrors([
            'device' => trans('validation.required', ['attribute' => 'device']),
        ])->assertStatus(422);
    });

});
