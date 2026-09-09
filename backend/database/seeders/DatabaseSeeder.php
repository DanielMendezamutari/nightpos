<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = TenantModel::firstOrCreate(
            ['slug' => 'casa-demo'],
            [
                'name' => 'Casa Ribersoft Demo',
                'company_brand' => 'Ribersoft',
                'is_active' => true,
            ]
        );

        $branch = BranchModel::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'CENTRO'],
            [
                'name' => 'Sucursal Central',
                'address' => 'Calle Murillo #450, Centro',
                'phone' => '67369293',
                'nit' => '1028475023',
                'municipality' => 'Santa Cruz',
                'is_active' => true,
            ]
        );

        $users = [
            [
                'name' => 'Administrador Ribersoft',
                'username' => 'admin',
                'email' => 'admin@ribersoft.com',
                'pin' => '2468',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ],
            [
                'name' => 'Cajero Principal',
                'username' => 'cajero',
                'email' => 'cajero@ribersoft.com',
                'pin' => '1234',
                'role' => 'cajero',
                'password' => Hash::make('cajero123'),
            ],
            [
                'name' => 'Mesero SalÃ³n',
                'username' => 'mesero',
                'email' => 'mesero@ribersoft.com',
                'pin' => '5678',
                'role' => 'mesero',
                'password' => Hash::make('mesero123'),
            ],
            [
                'name' => 'Chef de Cocina',
                'username' => 'cocina',
                'email' => 'cocina@ribersoft.com',
                'pin' => '4001',
                'role' => 'cocina',
                'password' => Hash::make('cocina123'),
            ],
            [
                'name' => 'Barman Turno',
                'username' => 'barman',
                'email' => 'barman@ribersoft.com',
                'pin' => '5001',
                'role' => 'barman',
                'password' => Hash::make('barman123'),
            ],
        ];

        foreach ($users as $userData) {
            UserModel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'username' => $userData['username']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                    'password' => $userData['password'],
                    'pin_hash' => Hash::make($userData['pin']),
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ]
            );
        }

        $this->call(SalonesMesasSeeder::class);
        $this->call(MenuSeeder::class);
    }
}