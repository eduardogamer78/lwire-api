<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Permission;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware([AclPermission::class]);
    $this->user = User::factory()->create();
    $this->access_token = $this->user->createToken('test_e2e')->plainTextToken;
});

/** @test List permissions of user */
it('should return all permissions of user', function () {
    getJson(route('users.permissions', $this->user->id), [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description',
                ],
            ],
        ]);
});

/** @test List permissions of user with permissions */
it('should return all permissions of user with permissions', function () {
    Permission::factory()->count(10)->create();
    $permissions = Permission::factory()->count(10)->create();
    $this->user->permissions()->sync($permissions->pluck('id')->toArray());
    getJson(route('users.permissions', $this->user->id), [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description',
                ],
            ],
        ])->assertJsonCount(10, 'data');
});

/** @test List sync permissions of user */
it('should sync permissions of user', function () {
    assertDatabaseCount('permissions', 0);
    $arrayPermissions = Permission::factory()->count(10)->create()->pluck('id')->toArray();
    postJson(route('users.permissions.sync', $this->user->id), [
        'permissions' => $arrayPermissions,
    ], [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertStatus(200);
    assertDatabaseCount('permissions', 10);
});

/** @test List permissions validated */
it('should validated permissions', function () {
    postJson(route('users.permissions.sync', $this->user->id), [
        'permissions' => ['fake_permission'],
    ], [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertStatus(422);
});
