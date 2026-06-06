<?php

declare(strict_types=1);

namespace App\Domain\Printing\Repositories;

use App\Shared\Contracts\RepositoryInterface;

/**
 * Persistence port for the Printing bounded context.
 * Implementation belongs in Infrastructure (Phase 4+).
 */
interface PrintJobRepositoryInterface extends RepositoryInterface
{
}
