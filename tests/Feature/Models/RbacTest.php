<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('user can have roles and roles can have permissions', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'manager']);
    $permission = Permission::factory()->create(['name' => 'manage-orders']);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role);

    expect($user->hasRole('manager'))->toBeTrue();
    expect($user->hasPermission('manage-orders'))->toBeTrue();
});

test('newly registered customer automatically gets user role', function () {
    // Створюємо ролі в базі через сідер
    $this->seed(\Database\Seeders\RoleSeeder::class);

    // Тестуємо Volt-компонент реєстрації
    Volt::test('pages.auth.register')
        ->set('name', 'Test Buyer')
        ->set('email', 'buyer@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register') // Викликаємо анонімну функцію $register
        ->assertHasNoErrors()
        ->assertRedirect('/dashboard');

    // Перевіряємо, чи з'явився користувач у БД і чи отримав він роль
    $user = User::where('email', 'buyer@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->hasRole('user'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
});

test('user profile calculates correct gravatar url', function () {
    $user = User::factory()->create(['email' => 'test@pavell.net']);
    $hash = md5('test@pavell.net');

    expect($user->avatar_url)->toContain("https://www.gravatar.com/avatar/{$hash}");
});