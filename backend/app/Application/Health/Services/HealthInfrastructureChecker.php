<?php

declare(strict_types=1);

namespace App\Application\Health\Services;

use App\Application\Health\Support\HealthSeverity;
use Illuminate\Support\Facades\DB;

final class HealthInfrastructureChecker
{
    /**
     * @return list<array<string, mixed>>
     */
    public function run(): array
    {
        return [
            $this->checkAppDebug(),
            $this->checkAppEnv(),
            $this->checkDatabase(),
            $this->checkJwt(),
            $this->checkStorageLink(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkAppDebug(): array
    {
        $debug = (bool) config('app.debug');
        $env = (string) config('app.env');

        if (! $debug) {
            return $this->ok('INFRA.APP_DEBUG', 'Debug desactivado', ['app_debug' => false]);
        }

        $severity = $env === 'production' ? HealthSeverity::CRITICAL : HealthSeverity::WARNING;

        return $this->issue(
            'INFRA.APP_DEBUG',
            $env === 'production'
                ? 'APP_DEBUG=true en producción — riesgo de seguridad.'
                : 'APP_DEBUG=true — desactivar antes de producción.',
            $severity,
            ['app_debug' => true, 'app_env' => $env],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkAppEnv(): array
    {
        $env = (string) config('app.env');

        if ($env === 'production') {
            return $this->ok('INFRA.APP_ENV', 'Entorno production', ['app_env' => $env]);
        }

        return $this->issue(
            'INFRA.APP_ENV',
            "APP_ENV={$env} — usar production en hosting.",
            HealthSeverity::WARNING,
            ['app_env' => $env],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return $this->ok('INFRA.DB', 'Base de datos accesible', ['connection' => config('database.default')]);
        } catch (\Throwable $exception) {
            return $this->issue(
                'INFRA.DB',
                'No se pudo conectar a la base de datos.',
                HealthSeverity::CRITICAL,
                ['error' => $exception->getMessage()],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function checkJwt(): array
    {
        $secret = config('jwt.secret');
        $configured = is_string($secret) && trim($secret) !== '';

        if ($configured) {
            return $this->ok('INFRA.JWT', 'JWT configurado', []);
        }

        return $this->issue(
            'INFRA.JWT',
            'JWT secret no configurado — login imposible.',
            HealthSeverity::CRITICAL,
            [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkStorageLink(): array
    {
        $linkPath = public_path('storage');
        $linked = is_link($linkPath) && is_dir($linkPath);

        if ($linked) {
            return $this->ok('INFRA.STORAGE', 'Enlace public/storage activo', []);
        }

        return $this->issue(
            'INFRA.STORAGE',
            'public/storage no enlazado — logos y adjuntos pueden fallar.',
            HealthSeverity::WARNING,
            ['path' => $linkPath],
        );
    }

    /**
     * @param  array<string, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function ok(string $code, string $message, array $evidence): array
    {
        return [
            'code' => $code,
            'domain' => 'infrastructure',
            'severity' => HealthSeverity::OK,
            'message' => $message,
            'evidence' => $evidence,
        ];
    }

    /**
     * @param  array<string, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function issue(string $code, string $message, string $severity, array $evidence): array
    {
        return [
            'code' => $code,
            'domain' => 'infrastructure',
            'severity' => $severity,
            'message' => $message,
            'evidence' => $evidence,
        ];
    }
}
