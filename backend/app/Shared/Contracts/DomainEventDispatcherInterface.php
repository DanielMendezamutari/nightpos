<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Port for publishing domain events after aggregate changes.
 */
interface DomainEventDispatcherInterface
{
    /**
     * @param  object  $event  Domain event instance
     */
    public function dispatch(object $event): void;

    /**
     * @param  list<object>  $events
     */
    public function dispatchAll(array $events): void;
}
