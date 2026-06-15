<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;

final class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant
    {
        $model = TenantModel::query()->find($id);

        return $model ? $this->map($model) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $model = TenantModel::query()->where('slug', $slug)->first();

        return $model ? $this->map($model) : null;
    }

    public function listAll(): array
    {
        return TenantModel::query()
            ->orderBy('name')
            ->get()
            ->map(fn (TenantModel $model) => $this->map($model))
            ->all();
    }

    public function create(
        string $name,
        string $slug,
        string $status,
        ?int $planId,
        ?string $planName,
        ?\DateTimeImmutable $subscriptionStartsAt,
        ?\DateTimeImmutable $subscriptionEndsAt,
    ): Tenant {
        $model = TenantModel::query()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
            'plan_id' => $planId,
            'plan_name' => $planName,
            'subscription_starts_at' => $subscriptionStartsAt?->format('Y-m-d H:i:s'),
            'subscription_ends_at' => $subscriptionEndsAt?->format('Y-m-d H:i:s'),
        ]);

        return $this->map($model);
    }

    public function update(
        int $id,
        string $name,
        string $slug,
        string $status,
        ?int $planId,
        ?string $planName,
        ?\DateTimeImmutable $subscriptionStartsAt,
        ?\DateTimeImmutable $subscriptionEndsAt,
    ): Tenant {
        $model = TenantModel::query()->findOrFail($id);

        $model->update([
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
            'plan_id' => $planId,
            'plan_name' => $planName,
            'subscription_starts_at' => $subscriptionStartsAt?->format('Y-m-d H:i:s'),
            'subscription_ends_at' => $subscriptionEndsAt?->format('Y-m-d H:i:s'),
        ]);

        return $this->map($model->fresh());
    }

    public function slugExists(string $slug, ?int $exceptTenantId = null): bool
    {
        $query = TenantModel::query()->where('slug', $slug);

        if ($exceptTenantId !== null) {
            $query->where('id', '!=', $exceptTenantId);
        }

        return $query->exists();
    }

    private function map(TenantModel $model): Tenant
    {
        return new Tenant(
            id: (int) $model->id,
            name: $model->name,
            slug: $model->slug,
            status: $model->status,
            planId: $model->plan_id !== null ? (int) $model->plan_id : null,
            planName: $model->plan_name,
            subscriptionStartsAt: $model->subscription_starts_at?->toDateTimeImmutable(),
            subscriptionEndsAt: $model->subscription_ends_at?->toDateTimeImmutable(),
        );
    }
}
