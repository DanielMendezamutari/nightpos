<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashRegisterModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

trait SeedsNightPosFoundation
{
    use SeedsNightPosPinCredentials;

    public function seedNightPosFoundation(): void
    {
        $tenant = TenantModel::query()->create([
            'name' => 'Casa Demo NightPOS',
            'slug' => 'casa-demo',
            'status' => 'active',
            'plan_name' => 'pro',
            'subscription_starts_at' => now()->subMonth(),
            'subscription_ends_at' => now()->addYear(),
        ]);

        $branch = BranchModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Sucursal Centro',
            'code' => 'CENTRO',
            'address' => 'Av. Principal 100',
            'status' => 'active',
        ]);

        $permissions = collect([
            ['name' => 'Acceso caja', 'slug' => 'cash.access'],
            ['name' => 'Acceso comandas', 'slug' => 'orders.access'],
            ['name' => 'Listar ventas', 'slug' => 'sales.list'],
            ['name' => 'Cobrar comandas', 'slug' => 'sales.charge'],
            ['name' => 'Venta directa desde caja', 'slug' => 'sales.direct_create'],
            ['name' => 'Acceso reportes', 'slug' => 'reports.access'],
            ['name' => 'Listar empresas', 'slug' => 'admin.tenants.list'],
            ['name' => 'Crear empresas', 'slug' => 'admin.tenants.create'],
            ['name' => 'Listar sucursales admin', 'slug' => 'admin.branches.list'],
            ['name' => 'Crear sucursales admin', 'slug' => 'admin.branches.create'],
            ['name' => 'Listar usuarios admin', 'slug' => 'admin.users.list'],
            ['name' => 'Crear usuarios admin', 'slug' => 'admin.users.create'],
            ['name' => 'Actualizar usuarios admin', 'slug' => 'admin.users.update'],
            ['name' => 'Listar productos', 'slug' => 'products.list'],
            ['name' => 'Crear productos', 'slug' => 'products.create'],
            ['name' => 'Actualizar productos', 'slug' => 'products.update'],
            ['name' => 'Alta rápida producto', 'slug' => 'products.quick_create'],
            ['name' => 'Listar categorías producto', 'slug' => 'product-categories.list'],
            ['name' => 'Crear categorías producto', 'slug' => 'product-categories.create'],
            ['name' => 'Alta rápida precio producto', 'slug' => 'product_prices.quick_create'],
            ['name' => 'Acceso turnos', 'slug' => 'shifts.access'],
            ['name' => 'Abrir turno', 'slug' => 'shifts.open'],
            ['name' => 'Cerrar turno', 'slug' => 'shifts.close'],
            ['name' => 'Listar turnos', 'slug' => 'shifts.list'],
            ['name' => 'Acceso liquidaciones', 'slug' => 'settlements.access'],
            ['name' => 'Generar liquidaciones', 'slug' => 'settlements.generate'],
            ['name' => 'Pagar liquidaciones', 'slug' => 'settlements.pay'],
            ['name' => 'Historial liquidaciones', 'slug' => 'settlements.history'],
            ['name' => 'Fuentes pendientes liquidación', 'slug' => 'settlements.pending_sources'],
            ['name' => 'Ver manillas', 'slug' => 'bracelets.access'],
            ['name' => 'Registrar manillas', 'slug' => 'bracelets.create'],
            ['name' => 'Ver piezas', 'slug' => 'room_services.access'],
            ['name' => 'Registrar piezas', 'slug' => 'room_services.create'],
            ['name' => 'Finalizar piezas', 'slug' => 'room_services.finish'],
            ['name' => 'Revisar piezas', 'slug' => 'room_services.check'],
            ['name' => 'Vista limpieza piezas', 'slug' => 'room_services.cleaning_view'],
            ['name' => 'Ver shows', 'slug' => 'shows.access'],
            ['name' => 'Registrar shows', 'slug' => 'shows.create'],
            ['name' => 'Acceso notificaciones', 'slug' => 'notifications.access'],
            ['name' => 'Leer notificaciones', 'slug' => 'notifications.read'],
            ['name' => 'Acceso habitaciones', 'slug' => 'rooms.access'],
            ['name' => 'Crear habitaciones', 'slug' => 'rooms.create'],
            ['name' => 'Actualizar habitaciones', 'slug' => 'rooms.update'],
            ['name' => 'Limpieza habitaciones', 'slug' => 'rooms.clean'],
            ['name' => 'Mantenimiento habitaciones', 'slug' => 'rooms.maintenance'],
            ['name' => 'Alta rápida de chica', 'slug' => 'staff.quick_create_girl'],
            ['name' => 'Alta rápida garzón', 'slug' => 'staff.quick_create_waiter'],
            ['name' => 'Acceso tipos de show', 'slug' => 'show_types.access'],
            ['name' => 'Crear tipos de show', 'slug' => 'show_types.create'],
            ['name' => 'Actualizar tipos de show', 'slug' => 'show_types.update'],
            ['name' => 'Setup plataforma SaaS', 'slug' => 'platform.setup'],
            ['name' => 'Consola de turno', 'slug' => 'shift_console.access'],
            ['name' => 'Motivos de caja', 'slug' => 'settings.cash_reasons'],
            ['name' => 'Gestionar motivos de caja', 'slug' => 'settings.cash_reasons.manage'],
            ['name' => 'Métodos de pago config', 'slug' => 'settings.payment_methods'],
            ['name' => 'Gestionar métodos de pago', 'slug' => 'settings.payment_methods.manage'],
            ['name' => 'Ambientes / mesas', 'slug' => 'settings.service_areas'],
            ['name' => 'Gestionar ambientes', 'slug' => 'settings.service_areas.manage'],
            ['name' => 'Tipos de habitación', 'slug' => 'settings.room_types'],
            ['name' => 'Gestionar tipos habitación', 'slug' => 'settings.room_types.manage'],
            ['name' => 'Checklist primera noche', 'slug' => 'settings.checklist'],
            ['name' => 'Cargar datos operativos iniciales', 'slug' => 'settings.bootstrap'],
            ['name' => 'Ver bitácora de auditoría', 'slug' => 'audits.list'],
            ['name' => 'Listar sesiones de caja admin', 'slug' => 'admin.cash_sessions.list'],
            ['name' => 'Ver detalle sesión de caja admin', 'slug' => 'admin.cash_sessions.view'],
            ['name' => 'Resumen fiscalización de cajas', 'slug' => 'admin.cash_sessions.summary'],
            ['name' => 'Modo garzón — inicio', 'slug' => 'waiter.dashboard'],
            ['name' => 'Modo garzón — comandas', 'slug' => 'waiter.orders'],
            ['name' => 'Crear comanda', 'slug' => 'orders.create'],
            ['name' => 'Agregar ítems comanda', 'slug' => 'orders.add_items'],
            ['name' => 'Enviar comanda a barra', 'slug' => 'orders.send_to_bar'],
            ['name' => 'Editar ítems comanda', 'slug' => 'orders.update_items'],
            ['name' => 'Cancelar línea comanda', 'slug' => 'orders.cancel_item'],
            ['name' => 'Editar cabecera comanda', 'slug' => 'orders.update_header'],
            ['name' => 'Cancelar comanda', 'slug' => 'orders.cancel'],
            ['name' => 'Modo limpieza — inicio', 'slug' => 'cleaning.dashboard'],
            ['name' => 'Modo limpieza — piezas', 'slug' => 'cleaning.room_services'],
            ['name' => 'Modo limpieza — revisar pieza', 'slug' => 'cleaning.check'],
            ['name' => 'Modo limpieza — finalizar pieza', 'slug' => 'cleaning.finish'],
            ['name' => 'Modo limpieza — marcar limpia', 'slug' => 'cleaning.mark_clean'],
            ['name' => 'Modo limpieza — ver ingresos', 'slug' => 'cleaning.earnings.view'],
            ['name' => 'Modo chica — inicio', 'slug' => 'girl.dashboard'],
            ['name' => 'Modo chica — ver ingresos', 'slug' => 'girl.earnings.view'],
            ['name' => 'Acceso a roles y permisos', 'slug' => 'roles.access'],
            ['name' => 'Crear roles locales', 'slug' => 'roles.create'],
            ['name' => 'Editar roles locales', 'slug' => 'roles.update'],
            ['name' => 'Eliminar roles locales', 'slug' => 'roles.delete'],
            ['name' => 'Actualizar permisos de roles', 'slug' => 'roles.permissions.update'],
            ['name' => 'Ver catálogo de permisos', 'slug' => 'permissions.access'],
        ])->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $roleSuper = RoleModel::query()->create([
            'tenant_id' => null,
            'name' => 'Super Admin SaaS',
            'slug' => 'super_admin',
        ]);

        $roleOwner = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Administrador',
            'slug' => 'tenant_owner',
        ]);

        $roleCashier = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Cajero',
            'slug' => 'cashier',
        ]);

        $roleCashierSenior = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Cajera Senior',
            'slug' => 'cashier_senior',
        ]);

        $roleWaiter = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Garzón',
            'slug' => 'waiter',
        ]);

        $roleCleaning = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Limpieza',
            'slug' => 'cleaning',
        ]);

        $roleGirl = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Chica',
            'slug' => 'girl',
        ]);

        $roleSuper->permissions()->sync($permissions->pluck('id'));

        $roleOwner->permissions()->sync(
            $permissions->whereIn('slug', [
                'cash.access',
                'orders.access',
                'orders.create',
                'orders.add_items',
                'orders.send_to_bar',
                'orders.update_items',
                'orders.cancel_item',
                'orders.update_header',
                'orders.cancel',
                'sales.list',
                'sales.charge',
                'sales.direct_create',
                'reports.access',
                'admin.branches.list',
                'admin.branches.create',
                'admin.users.list',
                'admin.users.create',
                'admin.users.update',
                'products.list',
                'products.create',
                'products.update',
                'products.quick_create',
                'product-categories.list',
                'product-categories.create',
                'product_prices.quick_create',
                'shifts.access',
                'shifts.open',
                'shifts.close',
                'shifts.list',
                'settlements.access',
                'settlements.generate',
                'settlements.pay',
                'settlements.history',
                'settlements.pending_sources',
                'bracelets.access',
                'bracelets.create',
                'room_services.access',
                'room_services.create',
                'room_services.finish',
                'room_services.check',
                'room_services.cleaning_view',
                'shows.access',
                'shows.create',
                'notifications.access',
                'notifications.read',
                'rooms.access',
                'rooms.create',
                'rooms.update',
                'rooms.clean',
                'rooms.maintenance',
                'staff.quick_create_girl',
                'staff.quick_create_waiter',
                'show_types.access',
                'show_types.create',
                'show_types.update',
                'shift_console.access',
                'settings.cash_reasons',
                'settings.cash_reasons.manage',
                'settings.payment_methods',
                'settings.payment_methods.manage',
                'settings.service_areas',
                'settings.service_areas.manage',
                'settings.room_types',
                'settings.room_types.manage',
                'settings.checklist',
                'settings.bootstrap',
                'audits.list',
                'admin.cash_sessions.list',
                'admin.cash_sessions.view',
                'admin.cash_sessions.summary',
                'roles.access',
                'roles.create',
                'roles.update',
                'roles.delete',
                'roles.permissions.update',
                'permissions.access',
            ])->pluck('id')
        );

        $roleCashier->permissions()->sync($permissions->whereIn('slug', [
            'cash.access',
            'orders.access',
            'orders.create',
            'orders.add_items',
            'orders.send_to_bar',
            'orders.update_items',
            'orders.cancel_item',
            'orders.update_header',
            'orders.cancel',
            'sales.list',
            'sales.charge',
            'sales.direct_create',
            'products.list',
            'product-categories.list',
            'products.quick_create',
            'shifts.access',
            'shifts.close',
            'settlements.access',
            'settlements.generate',
            'settlements.pay',
            'settlements.history',
            'settlements.pending_sources',
            'bracelets.access',
            'bracelets.create',
            'room_services.access',
            'room_services.create',
            'room_services.finish',
            'room_services.check',
            'room_services.cleaning_view',
            'shows.access',
            'shows.create',
            'notifications.access',
            'notifications.read',
            'rooms.access',
            'staff.quick_create_girl',
            'staff.quick_create_waiter',
            'show_types.access',
            'show_types.create',
            'shift_console.access',
            'settings.cash_reasons',
            'settings.payment_methods',
            'settings.service_areas',
            'settings.room_types',
        ])->pluck('id'));

        $roleCashierSenior->permissions()->sync($permissions->whereIn('slug', [
            'cash.access',
            'orders.access',
            'orders.create',
            'orders.add_items',
            'orders.send_to_bar',
            'orders.update_items',
            'orders.cancel_item',
            'orders.update_header',
            'orders.cancel',
            'sales.list',
            'sales.charge',
            'sales.direct_create',
            'products.list',
            'product-categories.list',
            'products.quick_create',
            'product_prices.quick_create',
            'shifts.access',
            'shifts.close',
            'settlements.access',
            'settlements.generate',
            'settlements.pay',
            'settlements.history',
            'settlements.pending_sources',
            'bracelets.access',
            'bracelets.create',
            'room_services.access',
            'room_services.create',
            'room_services.finish',
            'room_services.check',
            'room_services.cleaning_view',
            'shows.access',
            'shows.create',
            'notifications.access',
            'notifications.read',
            'rooms.access',
            'rooms.create',
            'staff.quick_create_girl',
            'staff.quick_create_waiter',
            'show_types.access',
            'show_types.create',
            'shift_console.access',
            'settings.cash_reasons',
            'settings.payment_methods',
            'settings.service_areas',
            'settings.room_types',
            'admin.cash_sessions.list',
            'admin.cash_sessions.view',
            'admin.cash_sessions.summary',
        ])->pluck('id'));

        $roleWaiter->permissions()->sync($permissions->whereIn('slug', [
            'waiter.dashboard',
            'waiter.orders',
            'orders.access',
            'orders.create',
            'orders.add_items',
            'orders.send_to_bar',
            'products.list',
            'product-categories.list',
            'settings.service_areas',
            'staff.quick_create_girl',
            'notifications.access',
            'notifications.read',
            'settlements.access',
        ])->pluck('id'));

        $roleCleaning->permissions()->sync($permissions->whereIn('slug', [
            'cleaning.dashboard',
            'cleaning.room_services',
            'cleaning.check',
            'cleaning.finish',
            'cleaning.mark_clean',
            'cleaning.earnings.view',
            'notifications.access',
        ])->pluck('id'));

        $roleGirl->permissions()->sync($permissions->whereIn('slug', [
            'girl.dashboard',
            'girl.earnings.view',
            'notifications.access',
            'notifications.read',
            'settlements.access',
        ])->pluck('id'));

        UserModel::query()->updateOrCreate(
            ['username' => 'superadmin'],
            array_merge([
                'tenant_id' => null,
                'branch_id' => null,
                'role_id' => $roleSuper->id,
                'name' => 'Super Admin',
                'email' => 'super@nightpos.test',
                'password' => 'SuperAdmin123!',
                'status' => 'active',
            ], $this->pinCredentials('0001'))
        );

        $owner = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleOwner->id,
            'name' => 'Admin Demo',
            'username' => 'admin.demo',
            'email' => 'admin@nightpos.test',
            'password' => 'AdminDemo123!',
            'status' => 'active',
        ], $this->pinCredentials('2468')));

        $cashier = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleCashier->id,
            'name' => 'Cajero Demo',
            'username' => 'cajero.demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('1234')));

        $waiter = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleWaiter->id,
            'name' => 'Garzón Demo',
            'username' => 'garzon.demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('5678')));

        $waiter2 = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleWaiter->id,
            'name' => 'Garzón Demo 2',
            'username' => 'garzon2.demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('5688')));

        $girl = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleGirl->id,
            'name' => 'Chica Centro',
            'username' => 'chica.centro',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('9012')));

        $girl2 = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleGirl->id,
            'name' => 'Chica Demo 2',
            'username' => 'chica2.demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('9022')));

        $girl3 = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleGirl->id,
            'name' => 'Chica Demo 3',
            'username' => 'chica3.demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('9032')));

        $cleaning = UserModel::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'role_id' => $roleCleaning->id,
            'name' => 'Limpieza Demo',
            'username' => 'limpieza.demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], $this->pinCredentials('3333')));

        StaffProfileModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'user_id' => $cashier->id,
            'staff_role' => 'CASHIER',
            'waiter_commission_percent' => null,
            'can_receive_girl_commissions' => false,
            'status' => 'active',
        ]);

        StaffProfileModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'user_id' => $waiter->id,
            'staff_role' => 'WAITER',
            'waiter_commission_percent' => 5.00,
            'can_receive_girl_commissions' => false,
            'status' => 'active',
        ]);

        StaffProfileModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'user_id' => $waiter2->id,
            'staff_role' => 'WAITER',
            'waiter_commission_percent' => 5.00,
            'can_receive_girl_commissions' => false,
            'status' => 'active',
        ]);

        foreach ([$girl, $girl2, $girl3] as $girlUser) {
            StaffProfileModel::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'user_id' => $girlUser->id,
                'staff_role' => 'GIRL',
                'waiter_commission_percent' => null,
                'can_receive_girl_commissions' => true,
                'status' => 'active',
            ]);
        }

        StaffProfileModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'user_id' => $cleaning->id,
            'staff_role' => 'CLEANING',
            'waiter_commission_percent' => null,
            'can_receive_girl_commissions' => false,
            'cleaning_base_amount' => 30.00,
            'cleaning_room_amount' => 10.00,
            'status' => 'active',
        ]);

        foreach ([$owner, $cashier, $waiter, $waiter2, $girl, $girl2, $girl3, $cleaning] as $user) {
            UserBranchAccessModel::query()->create([
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
            ]);
        }

        CashRegisterModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Caja Principal',
            'code' => 'CAJA-01',
            'status' => 'active',
        ]);

        foreach ([
            ['code' => 'CASH', 'name' => 'Efectivo', 'type' => 'CASH'],
            ['code' => 'QR', 'name' => 'QR / Transferencia', 'type' => 'QR'],
            ['code' => 'CARD', 'name' => 'Tarjeta', 'type' => 'CARD'],
        ] as $pm) {
            PaymentMethodModel::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $pm['code']],
                [
                    'name' => $pm['name'],
                    'type' => $pm['type'],
                    'enabled' => true,
                    'requires_reference' => $pm['type'] === 'QR',
                ],
            );
        }
    }
}
