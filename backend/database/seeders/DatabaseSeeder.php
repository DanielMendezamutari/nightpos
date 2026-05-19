<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $siteCasa22 = DB::table('sites')->insertGetId([
            'code' => 'CASA22',
            'name' => 'Casa 22',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $siteVip = DB::table('sites')->insertGetId([
            'code' => 'VIP',
            'name' => 'VIP',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            [
                'name' => 'Owner NightPOS',
                'email' => 'owner@nightpos.com',
                'pin_code' => '9000',
                'role' => 'owner',
                'site_id' => null,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@nightpos.com',
                'pin_code' => '9001',
                'role' => 'super_admin',
                'site_id' => null,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin Casa22',
                'email' => 'admin.casa22@nightpos.com',
                'pin_code' => '1001',
                'role' => 'admin',
                'site_id' => $siteCasa22,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cajera Casa22',
                'email' => 'cajera.casa22@nightpos.com',
                'pin_code' => '2001',
                'role' => 'cashier',
                'site_id' => $siteCasa22,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Garzon Casa22',
                'email' => 'garzon.casa22@nightpos.com',
                'pin_code' => '3001',
                'role' => 'waiter',
                'site_id' => $siteCasa22,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Encargada VIP',
                'email' => 'encargada.vip@nightpos.com',
                'pin_code' => '4001',
                'role' => 'manager',
                'site_id' => $siteVip,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->call(SpatieRoleSeeder::class);

        $branchUsers = DB::table('users')
            ->whereNotNull('site_id')
            ->get(['id', 'site_id', 'role']);
        foreach ($branchUsers as $u) {
            DB::table('user_site_accesses')->updateOrInsert(
                ['user_id' => $u->id, 'site_id' => $u->site_id],
                ['is_default' => true, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        $garzonId = DB::table('users')->where('email', 'garzon.casa22@nightpos.com')->value('id');
        $cajeraId = DB::table('users')->where('email', 'cajera.casa22@nightpos.com')->value('id');
        foreach ([$garzonId, $cajeraId] as $staffId) {
            if (! $staffId) {
                continue;
            }
            DB::table('user_site_accesses')->updateOrInsert(
                ['user_id' => $staffId, 'site_id' => $siteVip],
                ['is_default' => false, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        $roomCasa22Id = DB::table('site_rooms')->insertGetId([
            'site_id' => $siteCasa22,
            'code' => 'PISO1',
            'name' => 'Pista',
            'kind' => 'dance_floor',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $roomVipId = DB::table('site_rooms')->insertGetId([
            'site_id' => $siteVip,
            'code' => 'SALON',
            'name' => 'Salon VIP',
            'kind' => 'lounge',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tableCasaM1 = DB::table('site_tables')->insertGetId([
            'site_id' => $siteCasa22,
            'site_room_id' => $roomCasa22Id,
            'code' => 'M1',
            'seats' => 4,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tableCasaM2 = DB::table('site_tables')->insertGetId([
            'site_id' => $siteCasa22,
            'site_room_id' => $roomCasa22Id,
            'code' => 'M2',
            'seats' => 4,
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tableVipV1 = DB::table('site_tables')->insertGetId([
            'site_id' => $siteVip,
            'site_room_id' => $roomVipId,
            'code' => 'V1',
            'seats' => 4,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($garzonId) {
            foreach ([$tableCasaM1, $tableCasaM2] as $tableId) {
                DB::table('site_table_assignments')->updateOrInsert(
                    ['site_table_id' => $tableId],
                    [
                        'site_id' => $siteCasa22,
                        'waiter_user_id' => $garzonId,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
            DB::table('site_table_assignments')->updateOrInsert(
                ['site_table_id' => $tableVipV1],
                [
                    'site_id' => $siteVip,
                    'waiter_user_id' => $garzonId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        DB::table('users')->whereNotNull('site_id')->update(['active_site_id' => DB::raw('site_id')]);

        DB::table('saas_subscriptions')->insert([
            [
                'site_id' => $siteCasa22,
                'monthly_fee' => 700,
                'status' => 'active',
                'suspended_reason' => null,
                'last_paid_at' => now()->subDays(5),
                'next_due_at' => now()->addDays(25),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'site_id' => $siteVip,
                'monthly_fee' => 700,
                'status' => 'active',
                'suspended_reason' => null,
                'last_paid_at' => now()->subDays(10),
                'next_due_at' => now()->addDays(20),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('saas_discount_rules')->upsert([
            ['months_covered' => 3, 'discount_percent' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['months_covered' => 6, 'discount_percent' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['months_covered' => 12, 'discount_percent' => 20, 'created_at' => now(), 'updated_at' => now()],
        ], ['months_covered'], ['discount_percent', 'updated_at']);

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'global_lock'],
            [
                'is_locked' => false,
                'reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
