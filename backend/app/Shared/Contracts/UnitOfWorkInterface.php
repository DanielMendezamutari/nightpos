<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Transaction boundary port (implemented in Infrastructure).
 */
interface UnitOfWorkInterface
{
    public function begin(): void;

    public function commit(): void;

    public function rollBack(): void;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function transaction(callable $callback): mixed;
}
