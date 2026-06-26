<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns health json without authentication', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonStructure(['time', 'version', 'db']);
});
