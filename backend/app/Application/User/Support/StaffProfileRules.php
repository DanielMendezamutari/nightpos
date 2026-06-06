<?php



declare(strict_types=1);



namespace App\Application\User\Support;



use App\Domain\User\Exceptions\UserDomainException;



final class StaffProfileRules

{

    /**

     * @return array{

     *     waiter_commission_percent: ?string,

     *     can_receive_girl_commissions: bool,

     *     cleaning_base_amount: ?string,

     *     cleaning_room_amount: ?string

     * }

     */

    public static function normalize(

        ?string $staffRole,

        ?string $waiterCommissionPercent,

        ?bool $canReceiveGirlCommissions,

        ?string $cleaningBaseAmount = null,

        ?string $cleaningRoomAmount = null,

    ): array {

        if ($staffRole === null) {

            return self::empty();

        }



        return match ($staffRole) {

            'WAITER' => array_merge(self::forWaiter($waiterCommissionPercent), self::noCleaningPay()),

            'GIRL' => array_merge(self::forGirl($canReceiveGirlCommissions), self::noCleaningPay()),

            'CASHIER' => array_merge(self::forCashier(), self::noCleaningPay()),

            'CLEANING' => array_merge(self::forCleaning($cleaningBaseAmount, $cleaningRoomAmount), [

                'waiter_commission_percent' => null,

                'can_receive_girl_commissions' => false,

            ]),

            'MANAGER', 'INVENTORY', 'REPORTS' => array_merge([

                'waiter_commission_percent' => null,

                'can_receive_girl_commissions' => (bool) $canReceiveGirlCommissions,

            ], self::noCleaningPay()),

            default => throw UserDomainException::invalidStaffRole(),

        };

    }



    /**

     * @return array{waiter_commission_percent: ?string, can_receive_girl_commissions: bool, cleaning_base_amount: ?string, cleaning_room_amount: ?string}

     */

    public static function empty(): array

    {

        return [

            'waiter_commission_percent' => null,

            'can_receive_girl_commissions' => false,

            'cleaning_base_amount' => null,

            'cleaning_room_amount' => null,

        ];

    }



    /**

     * @return array{waiter_commission_percent: string, can_receive_girl_commissions: bool}

     */

    private static function forWaiter(?string $percent): array

    {

        if ($percent === null || $percent === '') {

            throw UserDomainException::waiterCommissionRequired();

        }



        return [

            'waiter_commission_percent' => $percent,

            'can_receive_girl_commissions' => false,

        ];

    }



    /**

     * @return array{waiter_commission_percent: null, can_receive_girl_commissions: bool}

     */

    private static function forGirl(?bool $canReceive): array

    {

        return [

            'waiter_commission_percent' => null,

            'can_receive_girl_commissions' => $canReceive ?? true,

        ];

    }



    /**

     * @return array{waiter_commission_percent: null, can_receive_girl_commissions: bool}

     */

    private static function forCashier(): array

    {

        return [

            'waiter_commission_percent' => null,

            'can_receive_girl_commissions' => false,

        ];

    }



    /**

     * @return array{cleaning_base_amount: string, cleaning_room_amount: string}

     */

    private static function forCleaning(?string $baseAmount, ?string $roomAmount): array

    {

        $base = self::resolveCleaningAmount($baseAmount, 'nightpos.cleaning.default_base_amount');

        $room = self::resolveCleaningAmount($roomAmount, 'nightpos.cleaning.default_room_amount');



        if ((float) $base < 0 || (float) $room < 0) {

            throw UserDomainException::invalidCleaningAmounts();

        }



        return [

            'cleaning_base_amount' => $base,

            'cleaning_room_amount' => $room,

        ];

    }



    /**

     * @return array{cleaning_base_amount: null, cleaning_room_amount: null}

     */

    private static function noCleaningPay(): array

    {

        return [

            'cleaning_base_amount' => null,

            'cleaning_room_amount' => null,

        ];

    }



    private static function resolveCleaningAmount(?string $value, string $configKey): string

    {

        if ($value !== null && $value !== '') {

            return number_format((float) $value, 2, '.', '');

        }



        return number_format((float) config($configKey, 0), 2, '.', '');

    }

}

