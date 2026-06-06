<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\Support;

final class RoomServiceAmountCalculator
{
    /**
     * @return array{gross_girl_amount: string, girl_amount: string, house_amount: string, cleaning_amount: string}
     */
    public static function split(float $totalAmount, float $girlPercent, float $cleaningAmount = 0.0): array
    {
        $grossGirlAmount = round($totalAmount * $girlPercent / 100, 2);
        $cleaning = round(min($cleaningAmount, $grossGirlAmount), 2);
        $netGirlAmount = round($grossGirlAmount - $cleaning, 2);
        $houseAmount = round($totalAmount - $grossGirlAmount, 2);

        return [
            'gross_girl_amount' => number_format($grossGirlAmount, 2, '.', ''),
            'girl_amount' => number_format($netGirlAmount, 2, '.', ''),
            'house_amount' => number_format($houseAmount, 2, '.', ''),
            'cleaning_amount' => number_format($cleaning, 2, '.', ''),
        ];
    }
}
