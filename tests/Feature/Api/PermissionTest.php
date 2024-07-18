<?php

use App\Models\User;
use App\Models\Permission;

use Illuminate\Support\Str;

use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;
use App\Http\Middleware\AclPermission;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    withoutMiddleware([AclPermission::class]);
    $this->permission = Permission::factory()->create();
    $this->user = User::factory()->create();
    $this->access_token = $this->user->createToken('test_e2e')->plainTextToken;
});

/** @test List permissions - permissions */
it('should return 200 - empty database', function () {
    getJson(route('permissions.index'), [
        'Authorization' => "Bearer " . $this->access_token,
    ])
      ->assertJsonStructure([
        'data' => [
            '*' => [
               'id', 'name', 'description',
            ]
        ],
      ])->assertStatus(200);
});

/** @test List permissions - pagination */
it('should return permissions page 2', function () {
    Permission::factory()->count(22)->create();
    $response = getJson(
        route('permissions.index') . '?page=2',
        [
          'Authorization' => 'Bearer ' . $this->access_token
        ]
    )
    ->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertStatus(200);

    expect(count($response['data']))->toBe(8);
    expect($response['meta']['total'])->toBe(23);
});

/** @test List Total per page */
it('should return with total per page', function () {
    Permission::factory()->count(16)->create();
    $response = getJson(
        route('permissions.index') . '?total_per_page=4',
        [
          'Authorization' => 'Bearer ' . $this->access_token
        ]
    )
    ->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertStatus(200);

    expect(count($response['data']))->toBe(4);
    expect($response['meta']['total'])->toBe(17);
    expect($response['meta']['per_page'])->toBe(4);
});

/** @test List filters */
it('should return with filters', function () {
    Permission::factory()->count(10)->create();
    Permission::factory()->create(['name' => 'custom_permission_name']);
    $response = getJson(
        route('permissions.index') . '?filter=custom_permission_name',
        [
          'Authorization' => 'Bearer ' . $this->access_token
        ]
    )
    ->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'description']
        ],
        'meta' => ['total', 'current_page', 'from', 'last_page', 'links' => [], 'path', 'per_page', 'to']
    ])->assertStatus(200);

    expect(count($response['data']))->toBe(1);
    expect($response['meta']['total'])->toBe(1);
});

/** @test List permissions - Store */
it('should store new permission', function () {
    $response = postJson(
        route('permissions.store'),
        [
            'name' => 'user.example',
            'description' => 'asc',
        ],
        [
          'Authorization' => 'Bearer ' . $this->access_token
        ],
    )
    ->assertCreated();
    assertDatabaseHas('permissions', [
        'id' => $response['data']['id'],
        'name' => 'user.example',
        'description' => 'asc',
    ]);
});

describe('validations', function () {

    /** @test Validation - Permissions store */
    it('should validate create new permission', function () {
        postJson(route('permissions.store'), [], [
            'Authorization' => 'Bearer ' . $this->access_token
        ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
            'description' => trans('validation.required', ['attribute' => 'description']),
        ]);
    });

    /** @test Validation - Permissions Update */
    it('should validate update permission', function () {
        putJson(route('permissions.update', $this->permission->id), [], [
            'Authorization' => 'Bearer ' . $this->access_token
        ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
            'description' => trans('validation.required', ['attribute' => 'description'])
        ]);
    });

    /** @test Validation - Permissions - Min 3 chars */
    it('should validated name/description 3 chars - min', function () {
        putJson(route('permissions.update', $this->permission->id), [
            'name' => 'ex',
            'description' => 'sm',
        ], [
            'Authorization' => 'Bearer ' . $this->access_token
        ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'name' => trans('validation.min.string', ['attribute' => 'name', 'min' => 3]),
            'description' => trans('validation.min.string', ['attribute' => 'description', 'min' => 3]),
        ]);
    });

    /** @test Validation - Name/Description - Max 255 Chars */
    it('should validated name/description 255 chars max', function () {
        putJson(route('permissions.update', $this->permission->id), [
            'name' => Str::random(500),
            'description' => 'list all users',
        ], [
            'Authorization' => 'Bearer ' . $this->access_token
        ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'name' => trans('validation.max.string', ['attribute' => 'name', 'max' => 255]),
        ]);
    });
});

/** @test Show Permission */
it('should return permission', function () {
    $response = getJson(route('permissions.show', $this->permission->id), [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertJsonStructure([
        'data' => ['id', 'name', 'description']
    ])->assertStatus(200);
});

/** @test Show Permission/404 */
it('should return 404 permission not found', function () {
    $response = getJson(route('permissions.show', 'fake-permission'), [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertNotFound();
});

/** @test Update Permission */
it('should update permission', function () {
    putJson(route('permissions.update', $this->permission->id), [
        'name' => 'john.updated',
        'description' => 'updated',
    ], [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertStatus(200);

    assertDatabaseHas('permissions', [
        'id' => $this->permission->id,
        'name' => 'john.updated',
        'description' => 'updated',
    ]);
});

/** @test Update 404 Permission Error */
it('should update 404 permission with error', function () {
    putJson(route('permissions.update', 'faker-permission'), [
        'name' => 'john.updated',
        'description' => 'updated',
    ], [
        'Authorization' => 'Bearer ' . $this->access_token
    ])->assertStatus(404);
});

/** @test Delete Permission */
it('should delete permission', function () {
    deleteJson(route('permissions.destroy', $this->permission->id), [], [
        'Authorization' => 'Bearer ' . $this->access_token
    ])
    ->assertNoContent();
    assertDatabaseMissing('permissions', [
        'id' => $this->permission->id
    ]);
});

/** @test Delete 404 Permission not found */
it('should delete 404 permission not found', function () {
    deleteJson(route('permissions.destroy', 'fake_id'), [], [
        'Authorization' => 'Bearer ' . $this->access_token
    ])
    ->assertNotFound();
});
