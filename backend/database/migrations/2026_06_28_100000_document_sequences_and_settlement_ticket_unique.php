<?php

declare(strict_types=1);

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('period_key', 20);
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'branch_id', 'document_type', 'period_key'],
                'document_sequences_scope_unique',
            );
        });

        app(DocumentSequenceService::class)->syncSettlementPaymentSequencesFromExistingTickets();

        if (Schema::hasColumn('staff_settlements', 'ticket_number')) {
            Schema::table('staff_settlements', function (Blueprint $table): void {
                if ($this->indexExists('staff_settlements', 'staff_settlements_ticket_number_unique')) {
                    $table->dropUnique('staff_settlements_ticket_number_unique');
                }
            });

            Schema::table('staff_settlements', function (Blueprint $table): void {
                if (! $this->indexExists('staff_settlements', 'staff_settlements_ticket_scope_unique')) {
                    $table->unique(
                        ['tenant_id', 'branch_id', 'ticket_number'],
                        'staff_settlements_ticket_scope_unique',
                    );
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('staff_settlements', 'ticket_number')) {
            Schema::table('staff_settlements', function (Blueprint $table): void {
                if ($this->indexExists('staff_settlements', 'staff_settlements_ticket_scope_unique')) {
                    $table->dropUnique('staff_settlements_ticket_scope_unique');
                }
            });

            Schema::table('staff_settlements', function (Blueprint $table): void {
                if (! $this->indexExists('staff_settlements', 'staff_settlements_ticket_number_unique')) {
                    $table->unique('ticket_number', 'staff_settlements_ticket_number_unique');
                }
            });
        }

        Schema::dropIfExists('document_sequences');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = $connection->getDatabaseName();
        $result = $connection->select(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName],
        );

        return ((int) ($result[0]->c ?? 0)) > 0;
    }
};
