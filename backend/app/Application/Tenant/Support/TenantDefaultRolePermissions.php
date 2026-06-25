<?php

declare(strict_types=1);

namespace App\Application\Tenant\Support;

/**
 * Permisos por rol operativo al provisionar un tenant nuevo (wizard SaaS).
 */
final class TenantDefaultRolePermissions
{
    public static function tenantOwner(): array
    {
        return [
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
            'product-categories.list',
            'product-categories.create',
            'shifts.access',
            'shifts.open',
            'shifts.close',
            'shifts.list',
            'settlements.access',
            'settlements.generate',
            'settlements.pay',
            'settlements.history',
            'settlements.pending_sources',
            'settlements.fines.manage',
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
            'product_prices.quick_create',
        ];
    }

    public static function cashier(): array
    {
        return [
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
            'shifts.access',
            'shifts.close',
            'shift_console.access',
            'settlements.access',
            'settlements.generate',
            'settlements.pay',
            'settlements.history',
            'settlements.pending_sources',
            'settlements.fines.manage',
            'bracelets.access',
            'bracelets.create',
            'room_services.access',
            'room_services.create',
            'shows.access',
            'shows.create',
            'room_services.finish',
            'room_services.check',
            'room_services.cleaning_view',
            'notifications.access',
            'notifications.read',
            'rooms.access',
            'staff.quick_create_girl',
            'staff.quick_create_waiter',
            'show_types.access',
            'show_types.create',
            'settings.cash_reasons',
            'settings.payment_methods',
            'settings.service_areas',
            'settings.room_types',
        ];
    }

    public static function waiter(): array
    {
        return [
            'waiter.dashboard',
            'waiter.orders',
            'waiter.my_tables',
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
        ];
    }

    public static function cleaning(): array
    {
        return [
            'cleaning.dashboard',
            'cleaning.room_services',
            'cleaning.check',
            'cleaning.finish',
            'cleaning.mark_clean',
            'cleaning.earnings.view',
            'notifications.access',
        ];
    }

    public static function girl(): array
    {
        return [
            'girl.dashboard',
            'girl.earnings.view',
            'notifications.access',
            'notifications.read',
        ];
    }
}
