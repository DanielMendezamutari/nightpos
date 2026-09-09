<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = TenantModel::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Restaurante Central Ribersoft',
            'slug' => 'casa-demo',
            'company_brand' => 'Ribersoft',
            'is_active' => true,
        ]);

        $branch = BranchModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'code' => 'CENTRO',
            'name' => 'Sucursal Centro (Casa Matriz)',
            'address' => 'Av. Principal #123, Santa Cruz',
            'phone' => '70012345',
            'nit' => '1020304050',
            'municipality' => 'Santa Cruz de la Sierra',
            'settings' => [
                'currency' => 'BOB',
                'mesas_visibilidad' => 2, // TodosVenTodo (desde ConfigToptech)
                'tipos_negocio' => 'Restaurante',
            ],
            'is_active' => true,
        ]);

        // 1. SuperAdmin Ribersoft
        UserModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => null,
            'name' => 'Super Administrador Ribersoft',
            'username' => 'superadmin',
            'email' => 'admin@ribersoft.com',
            'password' => Hash::make('Admin123!'),
            'pin_hash' => Hash::make('0001'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Administrador / Gerente Restaurante
        UserModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Gerente de Restaurante',
            'username' => 'admin.demo',
            'email' => 'gerencia@ribersoft.com',
            'password' => Hash::make('AdminDemo123!'),
            'pin_hash' => Hash::make('2468'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 3. Cajero (Caja y Facturación SIAT)
        UserModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Cajero Principal',
            'username' => 'cajero.demo',
            'email' => 'caja@ribersoft.com',
            'password' => Hash::make('Caja123!'),
            'pin_hash' => Hash::make('1234'),
            'role' => 'cajero',
            'is_active' => true,
        ]);

        // 4. Mesero / Garzón (Salón y Comandas)
        UserModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Mesero Salón',
            'username' => 'mesero.demo',
            'email' => 'mesero@ribersoft.com',
            'password' => Hash::make('Mesero123!'),
            'pin_hash' => Hash::make('5678'),
            'role' => 'mesero',
            'is_active' => true,
        ]);

        // 5. Cocinero / Chef (KDS Cocina)
        UserModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Chef de Cocina',
            'username' => 'cocina.demo',
            'email' => 'cocina@ribersoft.com',
            'password' => Hash::make('Cocina123!'),
            'pin_hash' => Hash::make('4001'),
            'role' => 'cocina',
            'is_active' => true,
        ]);

        // 6. Barman (Barra de Bebidas)
        UserModel::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Barman Barra',
            'username' => 'barman.demo',
            'email' => 'barra@ribersoft.com',
            'password' => Hash::make('Bar123!'),
            'pin_hash' => Hash::make('5001'),
            'role' => 'barman',
            'is_active' => true,
        ]);
    }
}
