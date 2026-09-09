<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
class AuthApiTest extends TestCase {
use RefreshDatabase;
protected function setUp(): void { parent::setUp(); $this->seed(DatabaseSeeder::class); }
public function test_can_login_with_pin(): void { $response = $this->postJson('/api/v1/auth/login-pin', ['pin' => '1234', 'tenant_slug' => 'casa-demo', 'branch_code' => 'CENTRO']); $response->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['token', 'token_type', 'user']]); }
public function test_rejects_wrong_pin(): void { $response = $this->postJson('/api/v1/auth/login-pin', ['pin' => '9999', 'tenant_slug' => 'casa-demo', 'branch_code' => 'CENTRO']); $response->assertStatus(401)->assertJsonPath('success', false); }
public function test_can_login_with_password(): void { $response = $this->postJson('/api/v1/auth/login-password', ['username' => 'admin', 'password' => 'admin123', 'tenant_slug' => 'casa-demo']); $response->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['token']]); }
public function test_can_get_authenticated_user(): void { $login = $this->postJson('/api/v1/auth/login-pin', ['pin' => '1234', 'tenant_slug' => 'casa-demo', 'branch_code' => 'CENTRO']); $token = $login->json('data.token'); $response = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/v1/auth/me'); $response->assertOk()->assertJsonPath('data.user.role', 'cajero'); }
}