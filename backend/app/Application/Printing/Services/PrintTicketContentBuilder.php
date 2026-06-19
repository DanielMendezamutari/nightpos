<?php

declare(strict_types=1);

namespace App\Application\Printing\Services;

use App\Shared\Domain\Enums\PrintJobType;

final class PrintTicketContentBuilder
{
    private const SALE_MODE_LABELS = [
        'SOLO_CLIENTE' => 'Solo',
        'CON_ACOMPANANTE' => 'Acomp',
        'MIXED' => 'Mixto',
    ];

    /**
     * @param  array<string, mixed>  $order  Order from OrderPresentationService
     */
    public function buildOrderCommand(array $order, ?string $waiterName, ?string $serviceAreaName, int $paperWidthMm = 80): string
    {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];

        $lines[] = $this->center('NIGHTPOS', $width);
        $lines[] = $this->center('COMANDA BAR', $width);
        $lines[] = str_repeat('=', $width);

        $orderNumber = (string) ($order['order_number'] ?? '—');
        $lines[] = $this->row('Comanda', $orderNumber, $width);

        $tableLabel = (string) ($order['table_label'] ?? '');
        $location = $tableLabel !== '' ? $tableLabel : ($serviceAreaName ?? '—');
        $lines[] = $this->row('Mesa/Amb', $location, $width);

        if ($waiterName !== null && $waiterName !== '') {
            $lines[] = $this->row('Garzon', $waiterName, $width);
        }

        $timestamp = $order['sent_to_bar_at'] ?? $order['opened_at'] ?? now()->toIso8601String();
        $lines[] = $this->row('Fecha', $this->formatDateTime($timestamp), $width);

        $lines[] = str_repeat('-', $width);

        foreach ($this->visibleItems($order) as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $name = (string) ($item['product_name'] ?? 'Producto');
            $mode = self::SALE_MODE_LABELS[$item['sale_mode'] ?? ''] ?? ($item['sale_mode'] ?? '');
            $lines[] = sprintf('%dx %s', $qty, $this->truncate($name, $width - 4));

            if ($mode !== '') {
                $lines[] = '   '.$mode;
            }

            if (($item['sale_mode'] ?? '') === 'CON_ACOMPANANTE' && ! ($item['requires_allocation'] ?? false)) {
                $girlName = $item['girl_name'] ?? null;
                if ($girlName) {
                    $lines[] = '   Chica: '.$girlName;
                }
            }

            if ($item['requires_allocation'] ?? false) {
                $allocated = (int) ($item['allocated_bracelet_units'] ?? 0);
                $required = (int) ($item['required_bracelet_units'] ?? 0);
                $lines[] = "   Manillas: {$allocated}/{$required}";

                foreach ($item['allocations'] ?? [] as $alloc) {
                    $girl = (string) ($alloc['girl_name'] ?? '—');
                    $units = (int) ($alloc['units'] ?? 0);
                    $lines[] = "   {$girl} x{$units}";
                }
            }

            if (! empty($item['notes'])) {
                $lines[] = '   Nota: '.$this->truncate((string) $item['notes'], $width - 9);
            }
        }

        $lines[] = str_repeat('-', $width);

        $total = $order['total'] ?? '0.00';
        $currency = $order['currency'] ?? 'BOB';
        $lines[] = $this->row('TOTAL', "{$total} {$currency}", $width);

        if (! empty($order['notes'])) {
            $lines[] = str_repeat('-', $width);
            $lines[] = 'Notas: '.$this->truncate((string) $order['notes'], $width - 7);
        }

        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    public function buildForType(PrintJobType $type, array $payload, int $paperWidthMm = 80): string
    {
        return match ($type) {
            PrintJobType::OrderCommand => $this->buildOrderCommand(
                $payload['order'] ?? [],
                $payload['waiter_name'] ?? null,
                $payload['service_area_name'] ?? null,
                $paperWidthMm,
            ),
            default => "NightPOS — {$type->value}\n",
        };
    }

    /**
     * @param  array<string, mixed>  $order
     * @return list<array<string, mixed>>
     */
    private function visibleItems(array $order): array
    {
        return array_values(array_filter(
            $order['items'] ?? [],
            static fn (array $item) => ($item['item_status'] ?? '') !== 'CANCELLED',
        ));
    }

    private function center(string $text, int $width): string
    {
        $len = strlen($text);
        if ($len >= $width) {
            return substr($text, 0, $width);
        }

        $pad = (int) floor(($width - $len) / 2);

        return str_repeat(' ', $pad).$text;
    }

    private function row(string $label, string $value, int $width): string
    {
        $label = $this->truncate($label, 12);
        $space = max(1, $width - strlen($label) - strlen($value));

        return $label.str_repeat(' ', $space).$value;
    }

    private function truncate(string $text, int $max): string
    {
        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, max(0, $max - 1)).'…';
    }

    /**
     * @return list<string>
     */
    private function wrap(string $text, int $width): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;
            if (strlen($candidate) <= $width) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines === [] ? [''] : $lines;
    }

    private function formatDateTime(string $value): string
    {
        try {
            return (new \DateTimeImmutable($value))->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }
}
