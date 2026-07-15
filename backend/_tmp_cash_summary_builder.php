<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\Cash\Contracts\SummaryBuilders\CashSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;

$sessionId = (int) ($argv[1] ?? 36);
$builder = app(CashSummaryBuilder::class);
$summary = $builder->build(new CashSessionId($sessionId));

echo json_encode([
    'session_id' => $sessionId,
    'cash_income_sales' => $summary->cash_income_sales->amount,
    'cash_income_sales_normal' => $summary->cash_income_sales_normal->amount,
    'cash_income_sales_room_services' => $summary->cash_income_sales_room_services->amount,
    'cash_income_sales_shows' => $summary->cash_income_sales_shows->amount,
    'cash_income_sales_other' => $summary->cash_income_sales_other->amount,
    'cash_expense_settlements' => $summary->cash_expense_settlements->amount,
    'expected_cash' => $summary->expected_cash->amount,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
