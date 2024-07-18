<?php

declare(strict_types=1);
use function Pest\Laravel\getJson;

it('should return status code 200', fn () => getJson('/')
  ->assertStatus(200));
