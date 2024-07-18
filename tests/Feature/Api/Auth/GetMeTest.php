<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Permission;

use function Pest\Laravel\getJson;

/** @test Unauthenticated Auth - Get Me */
it('should unauthenticated users can get me', function () {
    getJson(route('auth.me'), [])
        ->assertJson(['message' => 'Unauthenticated.'])
        ->assertStatus(401);
});

/** @test Authenticated - Permissions - Get Me */
it('should return user with nur data', function () {
    $user = User::factory()->create();
    $access_token = $user->createToken('test_2e')->plainTextToken;
    getJson(route('auth.me'), [
        'Authorization' => "Bearer {$access_token}",
    ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'permissions' => [],
            ],
        ])
        ->assertOk();
});

/** @test Authenticated - And Permissions - Get Me */
it('should return user with nur data and our permissions', function () {
    $permissions = Permission::factory()->count(10)->create();
    $permissions = Permission::factory()->count(10)->create()->pluck('id')->toArray();
    $user = User::factory()->create();
    $access_token = $user->createToken('test_2e')->plainTextToken;
    $user->permissions()->attach($permissions);
    getJson(route('auth.me'), [
        'Authorization' => "Bearer {$access_token}",
    ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'permissions' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                    ],
                ],
            ],
        ])
        ->assertJsonCount(10, 'data.permissions')
        ->assertOk();
});
