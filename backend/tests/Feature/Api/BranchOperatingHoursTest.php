<?php

use App\Models\Site;
use App\Models\SiteWorkShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSite(): Site
{
    return Site::create([
        'code' => 'H'.uniqid(),
        'name' => 'Sucursal horario',
        'is_active' => true,
    ]);
}

/** @return list<array{weekday: int, shifts: list<array{label: ?string, opens_at: string, closes_at: string, crosses_midnight: bool}>}> */
function emptyWeekdays(): array
{
    $out = [];
    for ($w = 1; $w <= 7; $w++) {
        $out[] = ['weekday' => $w, 'shifts' => []];
    }

    return $out;
}

/** Tres turnos de 8 h que cubren 24 h */
function threeEightHourShiftsWeek(): array
{
    $shiftTemplate = [
        ['label' => 'T1', 'opens_at' => '00:00', 'closes_at' => '08:00', 'crosses_midnight' => false],
        ['label' => 'T2', 'opens_at' => '08:00', 'closes_at' => '16:00', 'crosses_midnight' => false],
        ['label' => 'T3', 'opens_at' => '16:00', 'closes_at' => '00:00', 'crosses_midnight' => true],
    ];
    $out = [];
    for ($w = 1; $w <= 7; $w++) {
        $out[] = ['weekday' => $w, 'shifts' => $shiftTemplate];
    }

    return $out;
}

it('returns seven weekdays with empty shifts when nothing stored', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $response = $this->getJson('/api/branch/operating-hours');

    $response->assertOk();
    expect($response->json('data.weekdays'))->toHaveCount(7);
    expect($response->json('data.weekdays.0.weekday'))->toBe(1);
    expect($response->json('data.weekdays.0.shifts'))->toBe([]);
});

it('syncs three eight-hour shifts per day', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $response = $this->putJson('/api/branch/operating-hours', ['weekdays' => threeEightHourShiftsWeek()]);

    $response->assertOk();
    expect($response->json('data.weekdays.0.shifts'))->toHaveCount(3);
    expect($response->json('data.weekdays.0.shifts.2.crosses_midnight'))->toBeTrue();

    $this->assertDatabaseHas('site_work_shifts', [
        'site_id' => $site->id,
        'weekday' => 1,
        'slot_index' => 3,
        'opens_at' => '16:00',
        'closes_at' => '00:00',
        'crosses_midnight' => true,
    ]);
});

it('syncs two twelve hour shifts including overnight 21:00 to 09:00', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $weekdays = [];
    for ($w = 1; $w <= 7; $w++) {
        $weekdays[] = [
            'weekday' => $w,
            'shifts' => [
                ['label' => 'Dia', 'opens_at' => '09:00', 'closes_at' => '21:00', 'crosses_midnight' => false],
                ['label' => 'Noche', 'opens_at' => '21:00', 'closes_at' => '09:00', 'crosses_midnight' => true],
            ],
        ];
    }

    $this->putJson('/api/branch/operating-hours', ['weekdays' => $weekdays])->assertOk();

    $this->assertDatabaseHas('site_work_shifts', [
        'site_id' => $site->id,
        'slot_index' => 2,
        'opens_at' => '21:00',
        'closes_at' => '09:00',
        'crosses_midnight' => true,
    ]);
});

it('allows twenty four hour shift 21:00 to 21:00 next day', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $weekdays = [];
    for ($w = 1; $w <= 7; $w++) {
        $weekdays[] = [
            'weekday' => $w,
            'shifts' => [
                ['label' => '24h', 'opens_at' => '21:00', 'closes_at' => '21:00', 'crosses_midnight' => true],
            ],
        ];
    }

    $this->putJson('/api/branch/operating-hours', ['weekdays' => $weekdays])->assertOk();
});

it('rejects same calendar day shift when close is not after open without midnight cross', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $weekdays = [];
    for ($w = 1; $w <= 7; $w++) {
        $weekdays[] = [
            'weekday' => $w,
            'shifts' => [
                ['label' => 'X', 'opens_at' => '10:00', 'closes_at' => '09:00', 'crosses_midnight' => false],
            ],
        ];
    }

    $this->putJson('/api/branch/operating-hours', ['weekdays' => $weekdays])->assertStatus(422);
});

it('allows next-day end when opens 09:00 closes 21:00 with crosses_midnight', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $weekdays = [];
    for ($w = 1; $w <= 7; $w++) {
        $weekdays[] = [
            'weekday' => $w,
            'shifts' => [
                ['label' => 'Largo', 'opens_at' => '09:00', 'closes_at' => '21:00', 'crosses_midnight' => true],
            ],
        ];
    }

    $this->putJson('/api/branch/operating-hours', ['weekdays' => $weekdays])->assertOk();
});

it('requires seven weekdays', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $this->putJson('/api/branch/operating-hours', [
        'weekdays' => array_slice(emptyWeekdays(), 0, 6),
    ])->assertStatus(422);
});

it('replaces shifts on second sync', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $this->putJson('/api/branch/operating-hours', ['weekdays' => threeEightHourShiftsWeek()])->assertOk();
    expect(SiteWorkShift::query()->where('site_id', $site->id)->count())->toBe(21);

    $this->putJson('/api/branch/operating-hours', ['weekdays' => emptyWeekdays()])->assertOk();
    expect(SiteWorkShift::query()->where('site_id', $site->id)->count())->toBe(0);
});

it('accepts time with seconds and normalizes to hh:mm', function (): void {
    $site = makeSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $weekdays = [];
    for ($w = 1; $w <= 7; $w++) {
        $weekdays[] = [
            'weekday' => $w,
            'shifts' => [
                ['label' => 'T', 'opens_at' => '09:30:00', 'closes_at' => '21:45:59', 'crosses_midnight' => false],
            ],
        ];
    }

    $this->putJson('/api/branch/operating-hours', ['weekdays' => $weekdays])->assertOk();

    $this->assertDatabaseHas('site_work_shifts', [
        'site_id' => $site->id,
        'opens_at' => '09:30',
        'closes_at' => '21:45',
    ]);
});

it('forbids waiter from operating hours', function (): void {
    $site = makeSite();
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $site->id]);
    $this->actingAs($waiter);

    $this->getJson('/api/branch/operating-hours')->assertForbidden();
});
