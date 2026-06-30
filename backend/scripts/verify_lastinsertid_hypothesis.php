<?php

/**
 * Verifica hipótesis: PDO lastInsertId() devuelve id AUTO_INCREMENT, no last_value.
 * Ejecutar: php scripts/verify_lastinsertid_hypothesis.php
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Hipótesis: lastInsertId() vs last_value en INSERT upsert ===\n\n";

DB::beginTransaction();
try {
    // Tabla temporal de prueba con misma estructura relevante
    DB::statement('CREATE TEMPORARY TABLE IF NOT EXISTS _seq_test (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        scope_key VARCHAR(20) NOT NULL,
        last_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
        UNIQUE KEY (scope_key)
    )');

    DB::insert(
        'INSERT INTO _seq_test (scope_key, last_value) VALUES (?, LAST_INSERT_ID(1))
         ON DUPLICATE KEY UPDATE last_value = LAST_INSERT_ID(last_value + 1)',
        ['test-a'],
    );

    $pdoLastInsertId = (int) DB::getPdo()->lastInsertId();
    $mysqlLastInsertId = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS v')->v;
    $row = DB::selectOne('SELECT id, last_value FROM _seq_test WHERE scope_key = ?', ['test-a']);

    echo "Primer INSERT (fila nueva):\n";
    echo "  row.id (AUTO_INCREMENT)     = {$row->id}\n";
    echo "  row.last_value en BD        = {$row->last_value}\n";
    echo "  PDO::lastInsertId()         = {$pdoLastInsertId}\n";
    echo "  SELECT LAST_INSERT_ID()     = {$mysqlLastInsertId}\n\n";

    DB::insert(
        'INSERT INTO _seq_test (scope_key, last_value) VALUES (?, LAST_INSERT_ID(1))
         ON DUPLICATE KEY UPDATE last_value = LAST_INSERT_ID(last_value + 1)',
        ['test-a'],
    );

    $pdoLastInsertId2 = (int) DB::getPdo()->lastInsertId();
    $mysqlLastInsertId2 = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS v')->v;
    $row2 = DB::selectOne('SELECT id, last_value FROM _seq_test WHERE scope_key = ?', ['test-a']);

    echo "Segundo INSERT (ON DUPLICATE UPDATE):\n";
    echo "  row.last_value en BD        = {$row2->last_value}\n";
    echo "  PDO::lastInsertId()         = {$pdoLastInsertId2}\n";
    echo "  SELECT LAST_INSERT_ID()     = {$mysqlLastInsertId2}\n";

    DB::rollBack();
} catch (Throwable $e) {
    DB::rollBack();
    echo 'ERROR: '.$e->getMessage()."\n";
}

echo "\n=== Con AUTO_INCREMENT id=2 (como tenant 2 en prod) ===\n\n";

DB::beginTransaction();
try {
    DB::statement('CREATE TEMPORARY TABLE IF NOT EXISTS _seq_test3 (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        scope_key VARCHAR(20) NOT NULL,
        last_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
        UNIQUE KEY (scope_key)
    )');

    DB::insert('INSERT INTO _seq_test3 (scope_key, last_value) VALUES (?, 99)', ['seed-tenant3']);
    $seedRow = DB::selectOne('SELECT id FROM _seq_test3 WHERE scope_key = ?', ['seed-tenant3']);
    echo "  fila seed id (tenant 3 equivalente) = {$seedRow->id}\n";

    DB::insert(
        'INSERT INTO _seq_test3 (scope_key, last_value) VALUES (?, LAST_INSERT_ID(1))
         ON DUPLICATE KEY UPDATE last_value = LAST_INSERT_ID(last_value + 1)',
        ['tenant2-first-pay'],
    );

    $pdo = (int) DB::getPdo()->lastInsertId();
    $sel = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS v')->v;
    $row = DB::selectOne('SELECT id, last_value FROM _seq_test3 WHERE scope_key = ?', ['tenant2-first-pay']);

    echo "  fila nueva id (AUTO_INCREMENT) = {$row->id}\n";
    echo "  last_value en BD             = {$row->last_value}\n";
    echo "  PDO::lastInsertId()          = {$pdo}\n";
    echo "  SELECT LAST_INSERT_ID()      = {$sel}\n";
    echo "  ticket que generaría         = 1-2026-".str_pad((string) $pdo, 6, '0', STR_PAD_LEFT)."\n";

    DB::rollBack();
} catch (Throwable $e) {
    DB::rollBack();
    echo 'ERROR: '.$e->getMessage()."\n";
}
