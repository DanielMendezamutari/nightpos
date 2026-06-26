<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('sales', ['tenant_id', 'paid_at'], 'sales_tenant_paid_at_idx');
        $this->addIndex('orders', ['tenant_id', 'updated_at'], 'orders_tenant_updated_idx');
        $this->addIndex('orders', ['tenant_id', 'created_at'], 'orders_tenant_created_idx');
        $this->addIndex('cash_sessions', ['tenant_id', 'status', 'opened_at'], 'cash_sess_tenant_st_open_idx');
        $this->addIndex('official_shifts', ['tenant_id', 'status', 'opened_at'], 'shifts_tenant_st_open_idx');
        $this->addIndex('print_devices', ['tenant_id', 'enabled', 'last_seen_at'], 'print_dev_tenant_seen_idx');
        $this->addIndex('audit_logs', ['tenant_id', 'created_at'], 'audit_tenant_created_idx');
        $this->addIndex('operational_events', ['tenant_id', 'created_at'], 'ops_evt_tenant_created_idx');
    }

    public function down(): void
    {
        $this->dropIndex('sales', 'sales_tenant_paid_at_idx');
        $this->dropIndex('orders', 'orders_tenant_updated_idx');
        $this->dropIndex('orders', 'orders_tenant_created_idx');
        $this->dropIndex('cash_sessions', 'cash_sess_tenant_st_open_idx');
        $this->dropIndex('official_shifts', 'shifts_tenant_st_open_idx');
        $this->dropIndex('print_devices', 'print_dev_tenant_seen_idx');
        $this->dropIndex('audit_logs', 'audit_tenant_created_idx');
        $this->dropIndex('operational_events', 'ops_evt_tenant_created_idx');
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndex(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName): void {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndex(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName): void {
            $blueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $indexName],
        );

        return $result !== [];
    }
};
