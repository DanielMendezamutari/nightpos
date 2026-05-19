<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Application\UseCases;

use App\Support\ShiftCashTotals;
use Illuminate\Support\Facades\DB;

final class CloseShiftUseCase
{
    public function execute(int $shiftTurnId, int $closingCash): array
    {
        return DB::transaction(function () use ($shiftTurnId, $closingCash): array {
            $built = ShiftCashTotals::build($shiftTurnId);
            $shift = $built['shift'];

            $cashTotal = $built['cash_from_sales'];
            $qrTotal = $built['payment_totals']['qr'];
            $cardTotal = $built['payment_totals']['card'];
            $expectedCash = $built['expected_cash'];
            $difference = $closingCash - $expectedCash;

            DB::table('shift_turns')
                ->where('id', $shiftTurnId)
                ->update([
                    'closing_cash' => $closingCash,
                    'closed_at' => now(),
                    'status' => 'closed',
                    'updated_at' => now(),
                ]);

            return [
                'shift_turn_id' => $shiftTurnId,
                'totals' => [
                    'cash' => $cashTotal,
                    'qr' => $qrTotal,
                    'card' => $cardTotal,
                ],
                'drawer_in' => $built['drawer_in'],
                'drawer_out' => $built['drawer_out'],
                'opening_cash' => (int) $shift->opening_cash,
                'expected_cash' => $expectedCash,
                'closing_cash' => $closingCash,
                'difference' => $difference,
                'informe_cierre' => [
                    'titulo' => 'Informe de liquidación (ERP)',
                    'pdf_api_path' => '/shifts/'.$shiftTurnId.'/pdf',
                ],
            ];
        });
    }
}
