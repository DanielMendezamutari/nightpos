<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;

$showId = (int) ($argv[1] ?? 1);
$show = ShowModel::query()->findOrFail($showId);

echo json_encode($show->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
