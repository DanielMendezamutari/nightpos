<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class NightposBackupDatabaseCommand extends Command
{
    protected $signature = 'nightpos:backup-database {--path= : Directorio destino (default: storage/backups)}';

    protected $description = 'Genera un volcado SQL de la base de datos NightPOS (requiere mysqldump en PATH)';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') !== 'mysql') {
            $this->error('Solo está soportado MySQL/MariaDB.');

            return self::FAILURE;
        }

        $dir = $this->option('path') ?: storage_path('backups');
        File::ensureDirectoryExists($dir);

        $filename = sprintf('nightpos-%s.sql', now()->format('Y-m-d_His'));
        $target = $dir.DIRECTORY_SEPARATOR.$filename;

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg((string) $config['host']),
            escapeshellarg((string) ($config['port'] ?? '3306')),
            escapeshellarg((string) $config['username']),
            escapeshellarg((string) $config['password']),
            escapeshellarg((string) $config['database']),
            escapeshellarg($target),
        );

        $result = null;
        system($command, $result);

        if ($result !== 0 || ! is_file($target)) {
            $this->error('No se pudo crear el backup. Verifique mysqldump y credenciales.');

            return self::FAILURE;
        }

        $this->info("Backup creado: {$target}");

        return self::SUCCESS;
    }
}
