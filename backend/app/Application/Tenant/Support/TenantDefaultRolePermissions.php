<?php

declare(strict_types=1);

namespace App\Application\Tenant\Support;

/**
 * Fuente única de permisos default por rol operativo (wizard SaaS + demo alineado).
 */
final class TenantDefaultRolePermissions
{
    /** @return list<string> */
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
            'shift_console.access',
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
            'settings.checklist',
            'settings.bootstrap',
            'settings.printers',
            'settings.printers.manage',
            'printing.reprint',
            'audits.list',
            'admin.cash_sessions.list',
            'admin.cash_sessions.view',
            'admin.cash_sessions.summary',
            'admin.cash_sessions.force_close',
            'roles.access',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.permissions.update',
            'permissions.access',
        ];
    }

    /** @return list<string> */
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
            'products.quick_create',
            'shifts.access',
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
            'staff.quick_create_girl',
            'staff.quick_create_waiter',
            'show_types.access',
            'show_types.create',
            'shift_console.access',
            'settings.cash_reasons',
            'settings.payment_methods',
            'settings.service_areas',
            'settings.service_tables',
            'settings.room_types',
            'settings.printers',
            'printing.reprint',
        ];
    }

    /** @return list<string> */
    public static function cashierSenior(): array
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
            'products.quick_create',
            'product_prices.quick_create',
            'shifts.access',
            'shifts.close',
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
            'staff.quick_create_girl',
            'staff.quick_create_waiter',
            'show_types.access',
            'show_types.create',
            'shift_console.access',
            'settings.cash_reasons',
            'settings.cash_reasons.manage',
            'settings.payment_methods',
            'settings.service_areas',
            'settings.service_tables',
            'settings.service_tables.manage',
            'settings.waiter_assignments',
            'settings.waiter_assignments.manage',
            'settings.room_types',
            'settings.printers',
            'settings.printers.manage',
            'printing.reprint',
            'admin.cash_sessions.list',
            'admin.cash_sessions.view',
            'admin.cash_sessions.summary',
            'admin.cash_sessions.force_close',
        ];
    }

    /** @return list<string> */
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
            'printing.reprint',
            'products.list',
            'product-categories.list',
            'settings.service_areas',
            'staff.quick_create_girl',
            'notifications.access',
            'notifications.read',
            'settlements.access',
        ];
    }

    /** @return list<string> */
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

    /** @return list<string> */
    public static function girl(): array
    {
        return [
            'girl.dashboard',
            'girl.earnings.view',
            'notifications.access',
            'notifications.read',
            'settlements.access',
        ];
    }
}
