<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

/**
 * Standard SSE payload for order-related operational events.
 */
final class OrderOperationalEventPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function build(
        int $orderId,
        string $status,
        string $source,
        ?string $summary = null,
        array $refresh = ['orders'],
    ): array {
        $payload = [
            'order_id' => $orderId,
            'entity' => ['type' => 'order', 'id' => $orderId],
            'refresh' => $refresh,
            'status' => $status,
            'source' => $source,
        ];

        if ($summary !== null && $summary !== '') {
            $payload['summary'] = $summary;
        }

        return $payload;
    }
}
