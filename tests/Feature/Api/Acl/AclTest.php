<?php

use App\Models\User;
use App\Models\Permission;
use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->access_token = $this->user->createToken('test_e2e')->plainTextToken;
});

/** @test List return 403 */
test('should return 403', function () {
    getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertStatus(200);
});

/** @test List return users.index */
test('should return users.index', function () {
    $permission = Permission::factory()->create(['name' => 'users.index']);
    $this->user->permissions()->attach($permission);
    getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertStatus(200);
});

/** @test List return permissions.index */
test('should return permissions.index', function () {
    $permission = Permission::factory()->create(['name' => 'permissions.index']);
    $this->user->permissions()->attach($permission);
    getJson(route('permissions.index'), [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertStatus(200);
});
