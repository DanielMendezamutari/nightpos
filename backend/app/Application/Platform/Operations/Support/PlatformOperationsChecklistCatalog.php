<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\Support;

final class PlatformOperationsChecklistCatalog
{
    /**
     * @return list<array{key: string, label: string}>
     */
    public static function defaults(): array
    {
        return [
            ['key' => 'domain', 'label' => 'Dominio configurado'],
            ['key' => 'ssl', 'label' => 'SSL activo'],
            ['key' => 'branch', 'label' => 'Sucursal creada'],
            ['key' => 'users', 'label' => 'Usuarios creados'],
            ['key' => 'cash_register', 'label' => 'Caja creada'],
            ['key' => 'products', 'label' => 'Productos cargados'],
            ['key' => 'payment_methods', 'label' => 'Métodos de pago'],
            ['key' => 'print_agent', 'label' => 'Agente impresión instalado'],
            ['key' => 'printer', 'label' => 'Impresora registrada'],
            ['key' => 'print_test', 'label' => 'Prueba impresión OK'],
            ['key' => 'first_sale', 'label' => 'Primera venta'],
            ['key' => 'first_close', 'label' => 'Primer cierre'],
            ['key' => 'cashier_training', 'label' => 'Capacitación cajera'],
            ['key' => 'waiter_training', 'label' => 'Capacitación garzón'],
        ];
    }
}
