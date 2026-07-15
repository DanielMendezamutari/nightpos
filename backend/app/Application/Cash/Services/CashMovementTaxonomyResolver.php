<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Domain\Cash\ValueObjects\CashMovementCategory;
use App\Domain\Cash\ValueObjects\CashMovementFamily;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;

final class CashMovementTaxonomyResolver
{
    /**
     * @return array{family: string, category: string, strategy: string}
     */
    public function resolveForNew(
        string $movementType,
        ?int $cashMovementReasonId = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $description = null,
    ): array {
        return $this->resolve(
            movementType: $movementType,
            cashMovementReasonId: $cashMovementReasonId,
            sourceType: $sourceType,
            sourceId: $sourceId,
            description: $description,
        );
    }

    /**
     * @return array{family: string, category: string, strategy: string}
     */
    public function resolveForExisting(CashMovementModel $movement): array
    {
        return $this->resolve(
            movementType: (string) $movement->movement_type,
            cashMovementReasonId: $movement->cash_movement_reason_id !== null ? (int) $movement->cash_movement_reason_id : null,
            sourceType: $movement->source_type,
            sourceId: $movement->source_id !== null ? (int) $movement->source_id : null,
            description: $movement->description,
        );
    }

    /**
     * @return array{family: string, category: string, strategy: string}
     */
    private function resolve(
        string $movementType,
        ?int $cashMovementReasonId,
        ?string $sourceType,
        ?int $sourceId,
        ?string $description,
    ): array {
        $normalizedType = strtoupper(trim($movementType));
        $normalizedSource = strtoupper(trim((string) $sourceType));

        if ($normalizedSource !== '') {
            $bySource = $this->resolveBySourceType($normalizedSource, $sourceId);
            if ($bySource !== null) {
                return $bySource;
            }
        }

        if ($cashMovementReasonId !== null) {
            $byReason = $this->resolveByReason($cashMovementReasonId, $normalizedType);
            if ($byReason !== null) {
                return $byReason;
            }
        }

        $byDescription = $this->resolveByDescription($normalizedType, $description);
        if ($byDescription !== null) {
            return $byDescription;
        }

        return $normalizedType === 'INCOME'
            ? [
                'family' => CashMovementFamily::MANUAL,
                'category' => CashMovementCategory::OTHER_INCOME,
                'strategy' => 'type_fallback',
            ]
            : [
                'family' => CashMovementFamily::EXPENSE,
                'category' => CashMovementCategory::OTHER_EXPENSE,
                'strategy' => 'type_fallback',
            ];
    }

    /**
     * @return array{family: string, category: string, strategy: string}|null
     */
    private function resolveBySourceType(string $sourceType, ?int $sourceId): ?array
    {
        if ($sourceType === 'STAFF_SETTLEMENT' && $sourceId !== null) {
            $settlementType = (string) (StaffSettlementModel::query()->whereKey($sourceId)->value('settlement_type') ?? '');

            return match (strtoupper($settlementType)) {
                'GIRL' => [
                    'family' => CashMovementFamily::SETTLEMENT,
                    'category' => CashMovementCategory::SETTLEMENT_GIRL_PAYMENT,
                    'strategy' => 'source_type',
                ],
                'WAITER' => [
                    'family' => CashMovementFamily::SETTLEMENT,
                    'category' => CashMovementCategory::SETTLEMENT_WAITER_PAYMENT,
                    'strategy' => 'source_type',
                ],
                'CLEANING' => [
                    'family' => CashMovementFamily::SETTLEMENT,
                    'category' => CashMovementCategory::SETTLEMENT_CLEANING_PAYMENT,
                    'strategy' => 'source_type',
                ],
                default => [
                    'family' => CashMovementFamily::EXPENSE,
                    'category' => CashMovementCategory::OTHER_EXPENSE,
                    'strategy' => 'source_type_fallback',
                ],
            };
        }

        if ($sourceType === 'SALE' && $sourceId !== null) {
            $orderId = SaleModel::query()->whereKey($sourceId)->value('order_id');

            return $orderId === null
                ? [
                    'family' => CashMovementFamily::SALE,
                    'category' => CashMovementCategory::DIRECT_SALE_COLLECTION,
                    'strategy' => 'source_type',
                ]
                : [
                    'family' => CashMovementFamily::SALE,
                    'category' => CashMovementCategory::SALE_COLLECTION,
                    'strategy' => 'source_type',
                ];
        }

        return match ($sourceType) {
            'BRACELET' => [
                'family' => CashMovementFamily::SALE,
                'category' => CashMovementCategory::BRACELET_COLLECTION,
                'strategy' => 'source_type',
            ],
            'ROOM_SERVICE' => [
                'family' => CashMovementFamily::SALE,
                'category' => CashMovementCategory::ROOM_SERVICE_COLLECTION,
                'strategy' => 'source_type',
            ],
            default => null,
        };
    }

    /**
     * @return array{family: string, category: string, strategy: string}|null
     */
    private function resolveByReason(int $reasonId, string $movementType): ?array
    {
        $reason = CashMovementReasonModel::query()->find($reasonId);

        if ($reason === null) {
            return null;
        }

        if ($reason->default_movement_family !== null && $reason->default_movement_category !== null) {
            return [
                'family' => (string) $reason->default_movement_family,
                'category' => (string) $reason->default_movement_category,
                'strategy' => 'reason_default',
            ];
        }

        $reasonName = mb_strtolower(trim((string) $reason->name));

        if ($movementType === 'INCOME') {
            if (in_array($reasonName, ['otro ingreso', 'otros ingresos', 'otros'], true)) {
                return [
                    'family' => CashMovementFamily::MANUAL,
                    'category' => CashMovementCategory::MANUAL_INCOME,
                    'strategy' => 'reason_name_fallback',
                ];
            }

            return [
                'family' => CashMovementFamily::MANUAL,
                'category' => CashMovementCategory::OTHER_INCOME,
                'strategy' => 'reason_type_fallback',
            ];
        }

        if ($reasonName === 'pago chicas') {
            return [
                'family' => CashMovementFamily::SETTLEMENT,
                'category' => CashMovementCategory::SETTLEMENT_GIRL_PAYMENT,
                'strategy' => 'reason_name_fallback',
            ];
        }

        if ($reasonName === 'pago limpieza') {
            return [
                'family' => CashMovementFamily::SETTLEMENT,
                'category' => CashMovementCategory::SETTLEMENT_CLEANING_PAYMENT,
                'strategy' => 'reason_name_fallback',
            ];
        }

        if ($this->looksLikePurchase($reasonName)) {
            return [
                'family' => CashMovementFamily::EXPENSE,
                'category' => CashMovementCategory::PURCHASE,
                'strategy' => 'reason_name_fallback',
            ];
        }

        if (in_array($reasonName, [
            'cena',
            'taxi cena',
            'taxi chica',
            'rotacion personal',
            'pago de almuerzos',
            'comision de taxi',
            'gastos mantenimiento',
            'pago cajera',
            'adelanto personal',
        ], true)) {
            return [
                'family' => CashMovementFamily::EXPENSE,
                'category' => CashMovementCategory::OPERATING_EXPENSE,
                'strategy' => 'reason_name_fallback',
            ];
        }

        return [
            'family' => CashMovementFamily::EXPENSE,
            'category' => CashMovementCategory::OTHER_EXPENSE,
            'strategy' => 'reason_type_fallback',
        ];
    }

    /**
     * @return array{family: string, category: string, strategy: string}|null
     */
    private function resolveByDescription(string $movementType, ?string $description): ?array
    {
        $normalized = mb_strtolower(trim((string) $description));
        if ($normalized === '') {
            return null;
        }

        if ($movementType === 'INCOME') {
            if (str_starts_with($normalized, 'cobro comanda')) {
                return [
                    'family' => CashMovementFamily::SALE,
                    'category' => CashMovementCategory::SALE_COLLECTION,
                    'strategy' => 'description_fallback',
                ];
            }

            if (str_starts_with($normalized, 'venta directa')) {
                return [
                    'family' => CashMovementFamily::SALE,
                    'category' => CashMovementCategory::DIRECT_SALE_COLLECTION,
                    'strategy' => 'description_fallback',
                ];
            }

            if (str_starts_with($normalized, 'manilla')) {
                return [
                    'family' => CashMovementFamily::SALE,
                    'category' => CashMovementCategory::BRACELET_COLLECTION,
                    'strategy' => 'description_fallback',
                ];
            }

            if (str_starts_with($normalized, 'pieza')) {
                return [
                    'family' => CashMovementFamily::SALE,
                    'category' => CashMovementCategory::ROOM_SERVICE_COLLECTION,
                    'strategy' => 'description_fallback',
                ];
            }

            return [
                'family' => CashMovementFamily::MANUAL,
                'category' => CashMovementCategory::OTHER_INCOME,
                'strategy' => 'description_fallback',
            ];
        }

        if (str_contains($normalized, 'pago chicas')) {
            return [
                'family' => CashMovementFamily::SETTLEMENT,
                'category' => CashMovementCategory::SETTLEMENT_GIRL_PAYMENT,
                'strategy' => 'description_fallback',
            ];
        }

        if (str_contains($normalized, 'garz')) {
            return [
                'family' => CashMovementFamily::SETTLEMENT,
                'category' => CashMovementCategory::SETTLEMENT_WAITER_PAYMENT,
                'strategy' => 'description_fallback',
            ];
        }

        if (str_contains($normalized, 'pago limpieza')) {
            return [
                'family' => CashMovementFamily::SETTLEMENT,
                'category' => CashMovementCategory::SETTLEMENT_CLEANING_PAYMENT,
                'strategy' => 'description_fallback',
            ];
        }

        if ($this->looksLikePurchase($normalized)) {
            return [
                'family' => CashMovementFamily::EXPENSE,
                'category' => CashMovementCategory::PURCHASE,
                'strategy' => 'description_fallback',
            ];
        }

        return [
            'family' => CashMovementFamily::EXPENSE,
            'category' => CashMovementCategory::OTHER_EXPENSE,
            'strategy' => 'description_fallback',
        ];
    }

    private function looksLikePurchase(string $text): bool
    {
        $keywords = [
            'pedido',
            'compra',
            'punto frio',
            'corona',
            'casa belen',
            'hielo',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}