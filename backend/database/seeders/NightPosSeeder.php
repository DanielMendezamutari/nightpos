<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderStatusHistoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomTypeCatalogModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowTypeModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\Concerns\SeedsNightPosFoundation;
use Illuminate\Database\Seeder;

final class NightPosSeeder extends Seeder
{
    use SeedsNightPosFoundation;

    /**
     * PINs demo (cada uno único en el sistema).
     *
     * | Usuario      | PIN  |
     * |--------------|------|
     * | superadmin   | 0001 |
     * | admin.demo   | 2468 |
     * | cajero.demo  | 1234 |
     * | garzon.demo  | 5678 | Modo garzón — comandas demo W-DEMO-* precargadas |
     * | garzon2.demo | 5688 | Segundo garzón (comanda ajena para pruebas) |
     * | chica.centro | 9012 |
     * | chica2.demo  | 9022 |
     * | chica3.demo  | 9032 |
     * | limpieza.demo| 3333 |
     */
    public function run(): void
    {
        $this->seedNightPosFoundation();
        $this->seedOperationalDemoData();
    }

    private function seedOperationalDemoData(): void
    {
        $tenant = TenantModel::query()->where('slug', 'casa-demo')->firstOrFail();

        $branch = BranchModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('code', 'CENTRO')
            ->firstOrFail();

        $cashier = UserModel::query()->where('username', 'cajero.demo')->firstOrFail();

        $waiter = UserModel::query()->where('username', 'garzon.demo')->firstOrFail();

        $waiter2 = UserModel::query()->where('username', 'garzon2.demo')->firstOrFail();

        $girl = UserModel::query()->where('username', 'chica.centro')->firstOrFail();

        $demoRooms = [
            ['code' => 'P1', 'name' => 'Pieza 1', 'room_type' => 'STANDARD', 'suggested_price' => 80.00, 'default_duration_minutes' => 60],
            ['code' => 'P2', 'name' => 'Pieza 2', 'room_type' => 'STANDARD', 'suggested_price' => 80.00, 'default_duration_minutes' => 60],
            ['code' => 'P3', 'name' => 'Pieza 3', 'room_type' => 'STANDARD', 'suggested_price' => 80.00, 'default_duration_minutes' => 60],
            ['code' => 'P4', 'name' => 'Pieza 4', 'room_type' => 'STANDARD', 'suggested_price' => 80.00, 'default_duration_minutes' => 60],
            ['code' => 'VIP1', 'name' => 'VIP 1', 'room_type' => 'VIP', 'suggested_price' => 150.00, 'default_duration_minutes' => 90],
            ['code' => 'VIP2', 'name' => 'VIP 2', 'room_type' => 'VIP', 'suggested_price' => 150.00, 'default_duration_minutes' => 90],
        ];

        foreach ($demoRooms as $room) {
            RoomModel::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'code' => $room['code'],
                'name' => $room['name'],
                'room_type' => $room['room_type'],
                'status' => 'AVAILABLE',
                'default_duration_minutes' => $room['default_duration_minutes'],
                'suggested_price' => $room['suggested_price'],
            ]);
        }

        $categoryBebidas = ProductCategoryModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Bebidas',
            'status' => 'active',
        ]);

        $categoryTragos = ProductCategoryModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Tragos',
            'status' => 'active',
        ]);

        $categoryCocteles = ProductCategoryModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Cócteles',
            'status' => 'active',
        ]);

        $demoProducts = [
            ['name' => 'Cerveza Paceña', 'category_id' => $categoryBebidas->id, 'solo' => 25, 'companion' => 80, 'girl' => 40, 'house' => 40],
            ['name' => 'Cerveza importada', 'category_id' => $categoryBebidas->id, 'solo' => 35, 'companion' => 95, 'girl' => 48, 'house' => 47],
            ['name' => 'Agua mineral', 'category_id' => $categoryBebidas->id, 'solo' => 15, 'companion' => null, 'girl' => null, 'house' => null],
            ['name' => 'Coca-Cola', 'category_id' => $categoryBebidas->id, 'solo' => 18, 'companion' => null, 'girl' => null, 'house' => null],
            ['name' => 'Gaseosa limón', 'category_id' => $categoryBebidas->id, 'solo' => 18, 'companion' => null, 'girl' => null, 'house' => null],
            ['name' => 'Red Bull', 'category_id' => $categoryBebidas->id, 'solo' => 30, 'companion' => null, 'girl' => null, 'house' => null],
            ['name' => 'Whisky copa', 'category_id' => $categoryTragos->id, 'solo' => 45, 'companion' => 120, 'girl' => 60, 'house' => 60],
            ['name' => 'Ron medida', 'category_id' => $categoryTragos->id, 'solo' => 35, 'companion' => 90, 'girl' => 45, 'house' => 45],
            ['name' => 'Piscola', 'category_id' => $categoryTragos->id, 'solo' => 40, 'companion' => 100, 'girl' => 50, 'house' => 50],
            ['name' => 'Gin Tonic', 'category_id' => $categoryTragos->id, 'solo' => 42, 'companion' => 110, 'girl' => 55, 'house' => 55],
            ['name' => 'Champagne copa', 'category_id' => $categoryTragos->id, 'solo' => 80, 'companion' => 200, 'girl' => 100, 'house' => 100],
            ['name' => 'Mojito', 'category_id' => $categoryCocteles->id, 'solo' => 38, 'companion' => 105, 'girl' => 52, 'house' => 53],
            ['name' => 'Piña colada', 'category_id' => $categoryCocteles->id, 'solo' => 40, 'companion' => 108, 'girl' => 54, 'house' => 54],
        ];

        $productsByName = [];

        foreach ($demoProducts as $row) {
            $product = ProductModel::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => null,
                'category_id' => $row['category_id'],
                'name' => $row['name'],
                'product_type' => 'beverage',
                'unit' => 'unit',
                'track_inventory' => false,
                'status' => 'active',
            ]);

            $productsByName[$row['name']] = $product;

            ProductPriceModel::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'sale_mode' => 'SOLO_CLIENTE',
                'price' => $row['solo'],
                'currency' => 'BOB',
                'status' => 'active',
            ]);

            if ($row['companion'] !== null) {
                ProductPriceModel::query()->create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'sale_mode' => 'CON_ACOMPANANTE',
                    'price' => $row['companion'],
                    'girl_amount' => $row['girl'],
                    'house_amount' => $row['house'],
                    'currency' => 'BOB',
                    'status' => 'active',
                ]);
            }
        }

        foreach (['Show Privado Demo', 'Show Escenario Demo', 'Baile Especial Demo'] as $showName) {
            ShowTypeModel::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'name' => $showName,
                'suggested_price' => 200,
                'status' => 'active',
            ]);
        }

        foreach ([
            ['type' => 'INCOME', 'name' => 'Ingreso manual'],
            ['type' => 'INCOME', 'name' => 'Ajuste positivo'],
            ['type' => 'INCOME', 'name' => 'Otro ingreso'],
            ['type' => 'EXPENSE', 'name' => 'Compra insumos'],
            ['type' => 'EXPENSE', 'name' => 'Pago taxi'],
            ['type' => 'EXPENSE', 'name' => 'Limpieza'],
            ['type' => 'EXPENSE', 'name' => 'Pago personal'],
            ['type' => 'EXPENSE', 'name' => 'Pago cajera'],
            ['type' => 'EXPENSE', 'name' => 'Adelanto personal'],
            ['type' => 'EXPENSE', 'name' => 'Ajuste negativo'],
            ['type' => 'EXPENSE', 'name' => 'Otro egreso'],
            ['type' => 'BOTH', 'name' => 'Corrección caja'],
            ['type' => 'EXPENSE', 'name' => 'Compra hielo'],
            ['type' => 'EXPENSE', 'name' => 'Compra comida'],
            ['type' => 'EXPENSE', 'name' => 'Multa'],
        ] as $reason) {
            CashMovementReasonModel::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'type' => $reason['type'],
                    'name' => $reason['name'],
                ],
                ['status' => 'active'],
            );
        }

        foreach ([
            ['code' => 'STANDARD', 'name' => 'Estándar', 'minutes' => 60, 'price' => 80],
            ['code' => 'VIP', 'name' => 'VIP', 'minutes' => 90, 'price' => 150],
            ['code' => 'SUITE', 'name' => 'Suite', 'minutes' => 120, 'price' => 250],
        ] as $rt) {
            RoomTypeCatalogModel::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $rt['code']],
                [
                    'name' => $rt['name'],
                    'default_duration_minutes' => $rt['minutes'],
                    'suggested_price' => $rt['price'],
                    'status' => 'active',
                ],
            );
        }

        $serviceAreasByCode = [];

        foreach ([
            ['code' => 'M01', 'name' => 'Mesa 1', 'area_type' => 'TABLE'],
            ['code' => 'M02', 'name' => 'Mesa 2', 'area_type' => 'TABLE'],
            ['code' => 'VIP', 'name' => 'VIP', 'area_type' => 'VIP'],
            ['code' => 'BAR', 'name' => 'Barra', 'area_type' => 'BAR'],
        ] as $area) {
            $serviceAreasByCode[$area['code']] = ServiceAreaModel::query()->firstOrCreate(
                ['branch_id' => $branch->id, 'code' => $area['code']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $area['name'],
                    'area_type' => $area['area_type'],
                    'status' => 'active',
                ],
            );
        }

        $this->seedWaiterDemo(
            tenant: $tenant,
            branch: $branch,
            waiter: $waiter,
            waiter2: $waiter2,
            girl: $girl,
            openedBy: $cashier,
            serviceAreasByCode: $serviceAreasByCode,
            productsByName: $productsByName,
        );

        RoomModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'code' => 'LX',
            'name' => 'En limpieza demo',
            'room_type' => 'STANDARD',
            'status' => 'CLEANING',
            'default_duration_minutes' => 60,
            'suggested_price' => 80,
        ]);
    }

    /**
     * Turno abierto + comandas demo para probar modo garzón (KPI, listados, bebidas SOLO/CON_ACOMPANANTE).
     *
     * @param  array<string, ServiceAreaModel>  $serviceAreasByCode
     * @param  array<string, ProductModel>  $productsByName
     */
    private function seedWaiterDemo(
        TenantModel $tenant,
        BranchModel $branch,
        UserModel $waiter,
        UserModel $waiter2,
        UserModel $girl,
        UserModel $openedBy,
        array $serviceAreasByCode,
        array $productsByName,
    ): void {
        $shift = OfficialShiftModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Turno Demo Garzón',
            'shift_type' => 'DAY',
            'business_date' => now()->toDateString(),
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->endOfDay(),
            'status' => 'OPEN',
            'opened_by_user_id' => $openedBy->id,
            'opened_at' => now(),
            'notes' => 'Seed demo — pruebas modo garzón móvil',
        ]);

        $this->createWaiterDemoOrder(
            $tenant,
            $branch,
            $shift,
            $productsByName,
            'W-DEMO-01',
            'Mesa 1',
            $serviceAreasByCode['M01']->id,
            $waiter,
            $waiter,
            'OPEN',
            [
                ['product' => 'Cerveza Paceña', 'sale_mode' => 'SOLO_CLIENTE', 'qty' => 2, 'unit' => 25, 'item_status' => 'PENDING'],
            ],
        );

        $this->createWaiterDemoOrder(
            $tenant,
            $branch,
            $shift,
            $productsByName,
            'W-DEMO-02',
            'VIP',
            $serviceAreasByCode['VIP']->id,
            $waiter,
            $waiter,
            'OPEN',
            [],
            'Sin ítems — probar agregar bebida',
        );

        $this->createWaiterDemoOrder(
            $tenant,
            $branch,
            $shift,
            $productsByName,
            'W-DEMO-03',
            'Barra',
            $serviceAreasByCode['BAR']->id,
            $waiter,
            $waiter,
            'SENT_TO_BAR',
            [
                ['product' => 'Whisky copa', 'sale_mode' => 'SOLO_CLIENTE', 'qty' => 1, 'unit' => 45, 'item_status' => 'SENT'],
                [
                    'product' => 'Ron medida',
                    'sale_mode' => 'CON_ACOMPANANTE',
                    'qty' => 1,
                    'unit' => 90,
                    'girl_amount' => 45,
                    'house_amount' => 45,
                    'girl_user_id' => $girl->id,
                    'item_status' => 'SENT',
                ],
            ],
            null,
            now()->subMinutes(12),
        );

        $this->createWaiterDemoOrder(
            $tenant,
            $branch,
            $shift,
            $productsByName,
            'W-DEMO-04',
            'Mesa 2',
            $serviceAreasByCode['M02']->id,
            $waiter,
            $waiter,
            'READY',
            [
                ['product' => 'Agua mineral', 'sale_mode' => 'SOLO_CLIENTE', 'qty' => 2, 'unit' => 15, 'item_status' => 'SENT'],
                ['product' => 'Coca-Cola', 'sale_mode' => 'SOLO_CLIENTE', 'qty' => 1, 'unit' => 18, 'item_status' => 'SENT'],
            ],
            null,
            now()->subMinutes(25),
        );

        $this->createWaiterDemoOrder(
            $tenant,
            $branch,
            $shift,
            $productsByName,
            'W-DEMO-05',
            'Mesa preparación',
            null,
            $waiter,
            $waiter,
            'IN_PREPARATION',
            [
                ['product' => 'Mojito', 'sale_mode' => 'SOLO_CLIENTE', 'qty' => 1, 'unit' => 38, 'item_status' => 'SENT'],
            ],
            null,
            now()->subMinutes(8),
        );

        $this->createWaiterDemoOrder(
            $tenant,
            $branch,
            $shift,
            $productsByName,
            'W-DEMO-G2',
            'Mesa otro garzón',
            $serviceAreasByCode['M01']->id,
            $waiter2,
            $waiter2,
            'OPEN',
            [
                ['product' => 'Piscola', 'sale_mode' => 'SOLO_CLIENTE', 'qty' => 1, 'unit' => 40, 'item_status' => 'PENDING'],
            ],
        );
    }

    /**
     * @param  array<string, ProductModel>  $productsByName
     * @param  list<array{
     *     product: string,
     *     sale_mode: string,
     *     qty: int,
     *     unit: float|int,
     *     item_status: string,
     *     girl_amount?: float|int|null,
     *     house_amount?: float|int|null,
     *     girl_user_id?: int|null
     * }>  $items
     */
    private function createWaiterDemoOrder(
        TenantModel $tenant,
        BranchModel $branch,
        OfficialShiftModel $shift,
        array $productsByName,
        string $orderNumber,
        string $tableLabel,
        ?int $serviceAreaId,
        UserModel $waiter,
        UserModel $openedBy,
        string $status,
        array $items,
        ?string $notes = null,
        ?\DateTimeInterface $sentToBarAt = null,
    ): void {
        $subtotal = 0.0;

        foreach ($items as $row) {
            $subtotal += (float) $row['unit'] * (int) $row['qty'];
        }

        $order = OrderModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'official_shift_id' => $shift->id,
            'order_number' => $orderNumber,
            'status' => $status,
            'table_label' => $tableLabel,
            'service_area_id' => $serviceAreaId,
            'waiter_user_id' => $waiter->id,
            'opened_by_user_id' => $openedBy->id,
            'notes' => $notes,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'currency' => 'BOB',
            'sent_to_bar_at' => $sentToBarAt,
        ]);

        OrderStatusHistoryModel::query()->create([
            'order_id' => $order->id,
            'status' => 'OPEN',
            'changed_by_user_id' => $openedBy->id,
            'created_at' => now()->subHour(),
        ]);

        if ($status !== 'OPEN') {
            OrderStatusHistoryModel::query()->create([
                'order_id' => $order->id,
                'status' => $status,
                'changed_by_user_id' => $openedBy->id,
                'created_at' => now(),
            ]);
        }

        foreach ($items as $row) {
            $product = $productsByName[$row['product']] ?? null;

            if ($product === null) {
                continue;
            }

            $lineTotal = (float) $row['unit'] * (int) $row['qty'];

            OrderItemModel::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sale_mode' => $row['sale_mode'],
                'quantity' => $row['qty'],
                'unit_price' => $row['unit'],
                'line_total' => $lineTotal,
                'girl_amount' => $row['girl_amount'] ?? null,
                'house_amount' => $row['house_amount'] ?? null,
                'girl_user_id' => $row['girl_user_id'] ?? null,
                'item_status' => $row['item_status'],
            ]);
        }
    }
}
