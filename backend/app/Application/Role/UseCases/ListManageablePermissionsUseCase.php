<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\Support\ManageablePermissionCatalog;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListManageablePermissionsUseCase implements UseCaseInterface
{
    public function execute(mixed $input = null): OperationResult
    {
        $assignable = ManageablePermissionCatalog::assignableSlugs();
        $existing = PermissionModel::query()
            ->whereIn('slug', $assignable)
            ->orderBy('slug')
            ->get()
            ->keyBy('slug');

        $groups = [];
        foreach (ManageablePermissionCatalog::groups() as $groupKey => $slugs) {
            $items = [];
            foreach ($slugs as $slug) {
                if (! isset($existing[$slug])) {
                    continue;
                }
                $items[] = [
                    'id' => $existing[$slug]->id,
                    'slug' => $slug,
                    'name' => $existing[$slug]->name,
                    'label' => ManageablePermissionCatalog::permissionLabel($slug),
                ];
            }
            if ($items !== []) {
                $groups[] = [
                    'key' => $groupKey,
                    'label' => ManageablePermissionCatalog::groupLabel($groupKey),
                    'permissions' => $items,
                ];
            }
        }

        return OperationResult::ok('Permisos administrables.', ['groups' => $groups]);
    }
}
