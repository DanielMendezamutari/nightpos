<?php

declare(strict_types=1);

namespace App\Application\Role\Support;

/**
 * Permisos y roles que el administrador local puede gestionar.
 */
final class ManageablePermissionCatalog
{
    public const ROLE_PERMISSION_ADMIN_SLUG = 'roles.permissions.update';

    /** Roles de sistema que no se pueden eliminar. */
    public const PROTECTED_ROLE_SLUGS = ['tenant_owner'];

    /** Slug reservado global; nunca gestionable por tenant admin. */
    public const GLOBAL_SUPER_ADMIN_SLUG = 'super_admin';

    /**
     * Slugs de permisos asignables por admin local (whitelist).
     *
     * @return list<string>
     */
    public static function assignableSlugs(): array
    {
        return array_values(array_unique(array_merge(
            ...array_values(self::groups()),
        )));
    }

    /**
     * @return array<string, list<string>>
     */
    public static function groups(): array
    {
        return [
            'operation' => [
                'orders.access',
                'orders.create',
                'orders.add_items',
                'orders.send_to_bar',
                'orders.update_items',
                'orders.cancel_item',
                'orders.update_header',
                'orders.cancel',
            ],
            'cash' => [
                'cash.access',
                'sales.list',
                'sales.charge',
                'sales.direct_create',
                'shift_console.access',
            ],
            'services' => [
                'bracelets.access',
                'bracelets.create',
                'room_services.access',
                'room_services.create',
                'room_services.finish',
                'room_services.check',
                'room_services.cleaning_view',
                'shows.access',
                'shows.create',
            ],
            'rooms' => [
                'rooms.access',
                'rooms.create',
                'rooms.update',
                'rooms.clean',
                'rooms.maintenance',
            ],
            'cleaning' => [
                'cleaning.dashboard',
                'cleaning.room_services',
                'cleaning.check',
                'cleaning.finish',
                'cleaning.mark_clean',
                'cleaning.earnings.view',
            ],
            'settlements' => [
                'settlements.access',
                'settlements.generate',
                'settlements.pay',
                'settlements.history',
                'settlements.pending_sources',
                'settlements.fines.manage',
            ],
            'reports' => [
                'reports.access',
            ],
            'catalog' => [
                'products.list',
                'products.create',
                'products.update',
                'products.quick_create',
                'product-categories.list',
                'product-categories.create',
                'product_prices.quick_create',
                'show_types.access',
                'show_types.create',
                'show_types.update',
            ],
            'staff' => [
                'admin.users.list',
                'admin.users.create',
                'admin.users.update',
                'staff.quick_create_girl',
                'staff.quick_create_waiter',
                'roles.access',
                'roles.create',
                'roles.update',
                'roles.delete',
                'roles.permissions.update',
                'permissions.access',
            ],
            'shifts' => [
                'shifts.access',
                'shifts.open',
                'shifts.close',
                'shifts.list',
            ],
            'modes' => [
                'waiter.dashboard',
                'waiter.orders',
                'waiter.my_tables',
                'girl.dashboard',
                'girl.earnings.view',
            ],
            'settings' => [
                'settings.cash_reasons',
                'settings.cash_reasons.manage',
                'settings.payment_methods',
                'settings.payment_methods.manage',
                'settings.service_areas',
                'settings.service_areas.manage',
                'settings.service_tables',
                'settings.service_tables.manage',
                'settings.waiter_assignments',
                'settings.waiter_assignments.manage',
                'settings.room_types',
                'settings.room_types.manage',
                'notifications.access',
                'notifications.read',
                'audits.list',
                'admin.cash_sessions.list',
                'admin.cash_sessions.view',
                'admin.cash_sessions.force_close',
            ],
        ];
    }

    public static function groupLabel(string $group): string
    {
        return match ($group) {
            'operation' => 'Operación',
            'cash' => 'Caja',
            'services' => 'Servicios',
            'rooms' => 'Habitaciones',
            'cleaning' => 'Limpieza',
            'settlements' => 'Liquidaciones',
            'reports' => 'Reportes',
            'catalog' => 'Catálogo',
            'staff' => 'Personal',
            'shifts' => 'Turnos',
            'modes' => 'Modos operativos',
            'settings' => 'Configuración y auditoría',
            default => $group,
        };
    }

    public static function permissionLabel(string $slug): string
    {
        return self::labels()[$slug] ?? $slug;
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'orders.access' => 'Acceso a comandas',
            'orders.create' => 'Crear comanda',
            'orders.add_items' => 'Agregar productos a comanda',
            'orders.send_to_bar' => 'Enviar comanda a barra',
            'orders.update_items' => 'Editar ítems de comanda',
            'orders.cancel_item' => 'Cancelar línea de comanda',
            'orders.update_header' => 'Editar cabecera de comanda',
            'orders.cancel' => 'Cancelar comanda',
            'cash.access' => 'Acceso a caja',
            'sales.list' => 'Listar ventas',
            'sales.charge' => 'Cobrar comandas',
            'sales.direct_create' => 'Venta directa',
            'shift_console.access' => 'Consola de turno',
            'bracelets.access' => 'Ver manillas',
            'bracelets.create' => 'Registrar manillas',
            'room_services.access' => 'Ver piezas',
            'room_services.create' => 'Registrar piezas',
            'room_services.finish' => 'Finalizar pieza',
            'room_services.check' => 'Revisar pieza',
            'room_services.cleaning_view' => 'Vista limpieza de piezas',
            'shows.access' => 'Ver shows',
            'shows.create' => 'Registrar shows',
            'rooms.access' => 'Acceso a habitaciones',
            'rooms.create' => 'Crear habitaciones',
            'rooms.update' => 'Actualizar habitaciones',
            'rooms.clean' => 'Marcar habitación en limpieza',
            'rooms.maintenance' => 'Mantenimiento de habitaciones',
            'cleaning.dashboard' => 'Panel de limpieza',
            'cleaning.room_services' => 'Piezas (modo limpieza)',
            'cleaning.check' => 'Revisar pieza (limpieza)',
            'cleaning.finish' => 'Finalizar pieza (limpieza)',
            'cleaning.mark_clean' => 'Marcar habitación limpia',
            'cleaning.earnings.view' => 'Ver ingresos de limpieza',
            'settlements.access' => 'Acceso a liquidaciones',
            'settlements.generate' => 'Generar liquidaciones',
            'settlements.pay' => 'Pagar liquidaciones',
            'settlements.history' => 'Historial de liquidaciones',
            'settlements.pending_sources' => 'Fuentes pendientes de liquidación',
            'settlements.fines.manage' => 'Gestionar multas de liquidación',
            'reports.access' => 'Acceso a reportes',
            'products.list' => 'Listar productos',
            'products.create' => 'Crear productos',
            'products.update' => 'Actualizar productos',
            'products.quick_create' => 'Alta rápida de producto',
            'product-categories.list' => 'Listar categorías',
            'product-categories.create' => 'Crear categorías',
            'product_prices.quick_create' => 'Alta rápida de precio',
            'show_types.access' => 'Ver tipos de show',
            'show_types.create' => 'Crear tipos de show',
            'show_types.update' => 'Actualizar tipos de show',
            'admin.users.list' => 'Listar usuarios',
            'admin.users.create' => 'Crear usuarios',
            'admin.users.update' => 'Actualizar usuarios',
            'staff.quick_create_girl' => 'Alta rápida de chica',
            'staff.quick_create_waiter' => 'Alta rápida de garzón',
            'roles.access' => 'Acceso a roles y permisos',
            'roles.create' => 'Crear roles locales',
            'roles.update' => 'Editar roles locales',
            'roles.delete' => 'Eliminar roles locales',
            'roles.permissions.update' => 'Actualizar permisos de roles',
            'permissions.access' => 'Ver catálogo de permisos',
            'shifts.access' => 'Acceso a turnos',
            'shifts.open' => 'Abrir turno',
            'shifts.close' => 'Cerrar turno',
            'shifts.list' => 'Listar turnos',
            'waiter.dashboard' => 'Modo garzón — inicio',
            'waiter.orders' => 'Modo garzón — comandas',
            'waiter.my_tables' => 'Modo garzón — mis mesas',
            'girl.dashboard' => 'Modo chica — inicio',
            'girl.earnings.view' => 'Modo chica — ver ingresos',
            'settings.cash_reasons' => 'Ver motivos de caja',
            'settings.cash_reasons.manage' => 'Gestionar motivos de caja',
            'settings.payment_methods' => 'Ver métodos de pago',
            'settings.payment_methods.manage' => 'Gestionar métodos de pago',
            'settings.service_areas' => 'Ver ambientes / mesas',
            'settings.service_areas.manage' => 'Gestionar ambientes',
            'settings.service_tables' => 'Ver mesas de servicio',
            'settings.service_tables.manage' => 'Gestionar mesas',
            'settings.waiter_assignments' => 'Ver asignación mesas garzones',
            'settings.waiter_assignments.manage' => 'Gestionar asignación mesas',
            'settings.room_types' => 'Ver tipos de habitación',
            'settings.room_types.manage' => 'Gestionar tipos de habitación',
            'notifications.access' => 'Acceso a notificaciones',
            'notifications.read' => 'Leer notificaciones',
            'audits.list' => 'Ver bitácora de auditoría',
            'admin.cash_sessions.list' => 'Listar sesiones de caja',
            'admin.cash_sessions.view' => 'Ver detalle de sesión de caja',
            'admin.cash_sessions.force_close' => 'Cierre administrativo de caja',
        ];
    }

    public static function isAssignable(string $slug): bool
    {
        return in_array($slug, self::assignableSlugs(), true);
    }

    public static function isProtectedRoleSlug(string $slug): bool
    {
        return in_array($slug, self::PROTECTED_ROLE_SLUGS, true)
            || $slug === self::GLOBAL_SUPER_ADMIN_SLUG;
    }
}
