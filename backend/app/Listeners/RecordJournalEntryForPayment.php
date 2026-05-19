<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentRegistered;
use App\Support\AccountingChartBootstrap;
use Illuminate\Support\Facades\DB;

final class RecordJournalEntryForPayment
{
    public function handle(PaymentRegistered $event): void
    {
        $exists = DB::table('journal_entries')
            ->where('reference_type', 'payment')
            ->where('reference_id', $event->paymentId)
            ->exists();

        if ($exists) {
            return;
        }

        $accounts = AccountingChartBootstrap::ensureDefaultAccountsForSite($event->siteId);

        DB::transaction(function () use ($event, $accounts): void {
            $memo = 'Cobro POS pago #'.$event->paymentId.' ('.$event->method.')';

            $entryId = DB::table('journal_entries')->insertGetId([
                'site_id' => $event->siteId,
                'entry_date' => now()->toDateString(),
                'reference_type' => 'payment',
                'reference_id' => $event->paymentId,
                'memo' => $memo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $now = now();
            DB::table('journal_lines')->insert([
                [
                    'journal_entry_id' => $entryId,
                    'account_id' => $accounts['cash'],
                    'debit' => $event->amount,
                    'credit' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'journal_entry_id' => $entryId,
                    'account_id' => $accounts['revenue'],
                    'debit' => 0,
                    'credit' => $event->amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        });
    }
}
