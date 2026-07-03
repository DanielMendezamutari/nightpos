<?php

declare(strict_types=1);

namespace App\Application\Health\Services;

use App\Application\Health\Support\HealthOperationalStatus;
use App\Application\Health\Support\HealthSeverity;

final class HealthScoreCalculator
{
    /**
     * @param  list<array{severity: string}>  $checks
     */
    public function computeScore(array $checks): int
    {
        $score = 100;

        foreach ($checks as $check) {
            $severity = (string) ($check['severity'] ?? HealthSeverity::OK);

            if ($severity === HealthSeverity::OK) {
                continue;
            }

            $score -= match ($severity) {
                HealthSeverity::CRITICAL => 15,
                HealthSeverity::WARNING => 8,
                HealthSeverity::INFO => 3,
                default => 0,
            };
        }

        return max(0, min(100, $score));
    }

    /**
     * @param  list<array{severity: string}>  $checks
     * @return list<array{severity: string}>
     */
    public function alertsFromChecks(array $checks): array
    {
        return array_values(array_filter(
            $checks,
            static fn (array $check): bool => ($check['severity'] ?? HealthSeverity::OK) !== HealthSeverity::OK,
        ));
    }

    /**
     * @param  list<array{severity: string}>  $checks
     * @return array{score: int, status: string, alerts: list<array<string, mixed>>}
     */
    public function summarize(array $checks): array
    {
        $score = $this->computeScore($checks);

        return [
            'score' => $score,
            'status' => HealthOperationalStatus::fromScore($score),
            'alerts' => $this->alertsFromChecks($checks),
        ];
    }
}
