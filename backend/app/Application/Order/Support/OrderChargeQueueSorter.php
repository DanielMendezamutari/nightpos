<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

final class OrderChargeQueueSorter
{
    /** @var array<string, int> */
    private const STATUS_PRIORITY = [
        'SENT_TO_BAR' => 0,
        'IN_PREPARATION' => 1,
        'READY' => 2,
        'OPEN' => 3,
    ];

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function sort(array $rows): array
    {
        usort($rows, static function (array $a, array $b): int {
            $waitingA = (int) ($a['waiting_minutes'] ?? 0);
            $waitingB = (int) ($b['waiting_minutes'] ?? 0);

            if ($waitingA !== $waitingB) {
                return $waitingB <=> $waitingA;
            }

            $priorityA = self::STATUS_PRIORITY[(string) ($a['status'] ?? '')] ?? 99;
            $priorityB = self::STATUS_PRIORITY[(string) ($b['status'] ?? '')] ?? 99;

            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }

            $openedA = strtotime((string) ($a['opened_at'] ?? '')) ?: PHP_INT_MAX;
            $openedB = strtotime((string) ($b['opened_at'] ?? '')) ?: PHP_INT_MAX;

            if ($openedA !== $openedB) {
                return $openedA <=> $openedB;
            }

            return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
        });

        return $rows;
    }
}
