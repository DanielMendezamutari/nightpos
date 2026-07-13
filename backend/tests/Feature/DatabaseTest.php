<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    public function test_it_can_create_a_user(): void
    {
        $this->app['config']->set('database.default', 'mysql');
        $tenant = \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->create(['name' => 'Test Tenant', 'slug' => 'test-tenant-3']);
        $branch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->create(['tenant_id' => $tenant->id, 'name' => 'Test Branch', 'code' => 'test-branch-3']);

        $user = UserModel::query()->create([
            'name' => 'Test User',
            'email' => 'test3@test.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'username' => 'testuser3'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test3@test.com',
        ]);

        $user->delete();
        $branch->delete();
        $tenant->delete();
    }
}
