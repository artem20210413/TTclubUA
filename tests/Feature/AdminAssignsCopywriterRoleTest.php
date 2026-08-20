<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

function actingAdmin(): User
{
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    return $admin;
}

it('allows admin to assign the copywriter role to a user', function () {
    actingAdmin();
    Role::firstOrCreate(['name' => 'copywriter', 'guard_name' => 'web']);

    $target = User::factory()->create();

    $this->postJson("/api/user/{$target->id}/update", ['roles' => ['copywriter']])
        ->assertStatus(200);

    expect($target->fresh()->hasRole('copywriter'))->toBeTrue();
});

it('allows admin to assign the head-copywriter role to a user', function () {
    actingAdmin();
    Role::firstOrCreate(['name' => 'head-copywriter', 'guard_name' => 'web']);

    $target = User::factory()->create();

    $this->postJson("/api/user/{$target->id}/update", ['roles' => ['head-copywriter']])
        ->assertStatus(200);

    expect($target->fresh()->hasRole('head-copywriter'))->toBeTrue();
});

it('immediately revokes access after the copywriter role is removed', function () {
    actingAdmin();
    Role::firstOrCreate(['name' => 'copywriter', 'guard_name' => 'web']);

    $target = User::factory()->create();
    $target->assignRole('copywriter');

    $this->postJson("/api/user/{$target->id}/update", ['roles' => []])
        ->assertStatus(200);

    expect($target->fresh()->hasRole('copywriter'))->toBeFalse();
});
