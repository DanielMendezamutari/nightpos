<?php

declare(strict_types=1);

namespace App\Shared\Application\DTOs;

/**
 * Standard application-layer result envelope (before HTTP serialization).
 */
final readonly class OperationResult extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|object|null  $data
     */
    public function __construct(
        public bool $success,
        public string $message,
        public array|object|null $data = null,
        public array $errors = [],
    ) {
    }

    public static function ok(string $message = 'Operación realizada correctamente.', mixed $data = null): self
    {
        return new self(true, $message, is_array($data) || is_object($data) || $data === null ? $data : ['value' => $data]);
    }

    public static function fail(string $message, array $errors = []): self
    {
        return new self(false, $message, null, $errors);
    }
}
