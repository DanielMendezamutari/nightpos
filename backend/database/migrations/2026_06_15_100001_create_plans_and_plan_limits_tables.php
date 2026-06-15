<?php

use App\Infrastructure\Persistence\Eloquent\Models\PlanLimitModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->decimal('yearly_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('limit_key', 50);
            $table->integer('limit_value');
            $table->timestamps();

            $table->unique(['plan_id', 'limit_key']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('status')->constrained('plans')->nullOnDelete();
        });

        $this->seedDefaultPlans();
        $this->assignDemoTenantPlan();
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
        });

        Schema::dropIfExists('plan_limits');
        Schema::dropIfExists('plans');
    }

    private function seedDefaultPlans(): void
    {
        $definitions = [
            [
                'name' => 'Free',
                'code' => 'FREE',
                'description' => 'Plan gratuito para prueba operativa.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'display_order' => 1,
                'limits' => [
                    'branches' => 1,
                    'users' => 5,
                    'cashiers' => 2,
                    'waiters' => 2,
                    'products' => 100,
                    'rooms' => 10,
                ],
            ],
            [
                'name' => 'Starter',
                'code' => 'STARTER',
                'description' => 'Plan inicial para locales pequeños.',
                'monthly_price' => 49,
                'yearly_price' => 490,
                'display_order' => 2,
                'limits' => [
                    'branches' => 3,
                    'users' => 20,
                    'cashiers' => 5,
                    'waiters' => 10,
                    'products' => 500,
                    'rooms' => 50,
                ],
            ],
            [
                'name' => 'Business',
                'code' => 'BUSINESS',
                'description' => 'Plan para operación multi-sucursal.',
                'monthly_price' => 149,
                'yearly_price' => 1490,
                'display_order' => 3,
                'limits' => [
                    'branches' => 10,
                    'users' => 100,
                    'cashiers' => 20,
                    'waiters' => 40,
                    'products' => 2000,
                    'rooms' => 200,
                ],
            ],
            [
                'name' => 'Enterprise',
                'code' => 'ENTERPRISE',
                'description' => 'Plan sin límites operativos.',
                'monthly_price' => 399,
                'yearly_price' => 3990,
                'display_order' => 4,
                'limits' => [
                    'branches' => -1,
                    'users' => -1,
                    'cashiers' => -1,
                    'waiters' => -1,
                    'products' => -1,
                    'rooms' => -1,
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $plan = PlanModel::query()->create([
                'name' => $definition['name'],
                'code' => $definition['code'],
                'description' => $definition['description'],
                'monthly_price' => $definition['monthly_price'],
                'yearly_price' => $definition['yearly_price'],
                'is_active' => true,
                'display_order' => $definition['display_order'],
            ]);

            foreach ($definition['limits'] as $key => $value) {
                PlanLimitModel::query()->create([
                    'plan_id' => $plan->id,
                    'limit_key' => $key,
                    'limit_value' => $value,
                ]);
            }
        }
    }

    private function assignDemoTenantPlan(): void
    {
        $businessId = PlanModel::query()->where('code', 'BUSINESS')->value('id');

        if ($businessId === null) {
            return;
        }

        TenantModel::query()
            ->where('slug', 'casa-demo')
            ->update([
                'plan_id' => $businessId,
                'plan_name' => 'BUSINESS',
            ]);
    }
};
