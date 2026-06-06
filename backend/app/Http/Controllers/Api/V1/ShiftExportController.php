<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Shift\UseCases\GetOfficialShiftSummaryUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ShiftExportController extends Controller
{
    public function __construct(
        private readonly GetOfficialShiftSummaryUseCase $summary,
    ) {
    }

    public function csv(int $id): StreamedResponse
    {
        $result = $this->summary->execute((object) ['shiftId' => $id]);
        $payload = $result->data ?? [];
        $shift = $payload['shift'] ?? [];
        $summary = $payload['summary'] ?? [];

        $rows = [
            ['Campo', 'Valor'],
            ['Turno', (string) ($shift['name'] ?? '')],
            ['Tipo', (string) ($shift['shift_type'] ?? '')],
            ['Fecha negocio', (string) ($shift['business_date'] ?? '')],
            ['Estado', (string) ($shift['status'] ?? '')],
            ['Ventas total', (string) ($summary['total_sales'] ?? '0')],
            ['Efectivo', (string) ($summary['total_cash'] ?? '0')],
            ['QR', (string) ($summary['total_qr'] ?? '0')],
            ['Tarjeta', (string) ($summary['total_card'] ?? '0')],
            ['Efectivo contado', (string) ($summary['counted_cash'] ?? '')],
            ['Diferencia', (string) ($summary['cash_difference'] ?? '')],
        ];

        return $this->streamCsv('turno-'.$id.'.csv', $rows);
    }

    /**
     * @param  list<array{0: string, 1: string}>  $rows
     */
    private function streamCsv(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
