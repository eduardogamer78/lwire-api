<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware([AclPermission::class]);
    $this->user = User::factory()->create();
    $this->access_token = $this->user->createToken('test_e2e')->plainTextToken;
});

/** @test List users - permissions */
it('should return 200 - empty database', function () {
    getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'email',
                    'permissions' => [],
                ],
            ],
        ])->assertStatus(200);
});

/** @test List many users */
it('should return 200 - with many users', function () {
    User::factory()->count(20)->create();
    $response = getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'email',
                    'permissions' => [],
                ],
            ],
            'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to'],
        ])->assertStatus(200);

    expect(count($response['data']))->toBe(15);
    expect($response['meta']['total'])->toBe(21);
});

/** @test List users - pagination */
it('should return users page 2', function () {
    User::factory()->count(22)->create();
    $response = getJson(
        route('users.index') . '?page=2',
        [
            'Authorization' => 'Bearer ' . $this->access_token,
        ]
    )
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'permissions' => []],
            ],
            'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to'],
        ])->assertStatus(200);

    expect(count($response['data']))->toBe(8);
    expect($response['meta']['total'])->toBe(23);
});

/** @test List Total per page */
it('should return with total per page', function () {
    User::factory()->count(16)->create();
    $response = getJson(
        route('users.index') . '?total_per_page=4',
        [
            'Authorization' => 'Bearer ' . $this->access_token,
        ]
    )
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'permissions' => []],
            ],
            'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to'],
        ])->assertStatus(200);

    expect(count($response['data']))->toBe(4);
    expect($response['meta']['total'])->toBe(17);
    expect($response['meta']['per_page'])->toBe(4);
});

/** @test List filters */
it('should return with filters', function () {
    User::factory()->count(10)->create();
    User::factory()->count(10)->create(['name' => 'custom_user_name']);
    $response = getJson(
        route('users.index') . '?filter=custom_user_name',
        [
            'Authorization' => 'Bearer ' . $this->access_token,
        ]
    )
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'permissions' => []],
            ],
            'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to'],
        ])->assertStatus(200);

    expect(count($response['data']))->toBe(10);
    expect($response['meta']['total'])->toBe(10);
});

/** @test List users - Store */
it('should store new user', function () {
    $response = postJson(
        route('users.store'),
        [
            'name' => 'john doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ],
        [
            'Authorization' => 'Bearer ' . $this->access_token,
        ],
    )
        ->assertCreated();
    assertDatabaseHas('users', [
        'id' => $response['data']['id'],
        'name' => 'john doe',
        'email' => 'john@example.com',
    ]);
});

describe('validations', function () {

    /** @test Validation - Users store */
    it('should validate create new user', function () {
        postJson(route('users.store'), [], [
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'name' => trans('validation.required', ['attribute' => 'name']),
                'email' => trans('validation.required', ['attribute' => 'email']),
                'password' => trans('validation.required', ['attribute' => 'password']),
            ]);
    });

    /** @test Validation - Users Update */
    it('should validate update user', function () {
        putJson(route('users.update', $this->user->id), [], [
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'name' => trans('validation.required', ['attribute' => 'name']),
            ]);
    });

    /** @test Validation - Password - Min */
    it('should validated password min', function () {
        putJson(route('users.update', $this->user->id), [
            'name' => 'john doe',
            'password' => '123',
        ], [
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'password' => trans('validation.min.string', ['attribute' => 'password', 'min' => 6]),
            ]);
    });

    /** @test Validation - Password - Max */
    it('should validated password max', function () {
        putJson(route('users.update', $this->user->id), [
            'name' => 'john doe',
            'password' => Str::random(21),
        ], [
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'password' => trans('validation.max.string', ['attribute' => 'password', 'max' => 20]),
            ]);
    });
});

/** @test Show User */
it('should return user', function () {
    $response = getJson(route('users.show', $this->user->id), [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertJsonStructure([
        'data' => ['id', 'name', 'email', 'permissions' => []],
    ])->assertStatus(200);
});

/** @test Show User/404 */
it('should return 404 user not found', function () {
    $response = getJson(route('users.show', 'fake-user'), [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertNotFound();
});

/** @test Update User */
it('should update user', function () {
    putJson(route('users.update', $this->user->id), [
        'name' => 'john doe updated',
    ], [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertStatus(200);

    assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'john doe updated',
    ]);
});

/** @test Update 404 User Error */
it('should update 404 user with error', function () {
    putJson(route('users.update', 'faker-user'), [
        'name' => 'john doe updated',
    ], [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])->assertStatus(404);
});

/** @test Delete User */
it('should delete user', function () {
    deleteJson(route('users.destroy', $this->user->id), [], [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])
        ->assertNoContent();
    assertDatabaseMissing('users', [
        'id' => $this->user->id,
    ]);
});

/** @test Delete 404 User not found */
it('should delete 404 user not found', function () {
    deleteJson(route('users.destroy', 'fake_id'), [], [
        'Authorization' => 'Bearer ' . $this->access_token,
    ])
        ->assertNotFound();
});
