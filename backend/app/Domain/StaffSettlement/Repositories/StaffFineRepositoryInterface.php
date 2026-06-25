<?php



declare(strict_types=1);



namespace App\Domain\StaffSettlement\Repositories;



interface StaffFineRepositoryInterface

{

    /**

     * @param  array<string, mixed>  $filters

     * @return array<int, array<string, mixed>>

     */

    public function list(int $tenantId, int $branchId, array $filters): array;



    /**

     * @param  array<string, mixed>  $data

     * @return array<string, mixed>

     */

    public function create(array $data): array;



    public function findById(int $id, int $tenantId, int $branchId): ?array;



    /**

     * @return array<string, mixed>

     */

    public function cancel(int $id, int $tenantId, int $branchId, int $cancelledByUserId, string $cancellationReason): array;

}


