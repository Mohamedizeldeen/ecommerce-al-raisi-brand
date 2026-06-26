<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', 'admin@amalalraisi.com')->first();
    $this->staff = User::factory()->create();
    $this->staff->forceFill(['is_admin' => true, 'role' => 'staff'])->save();
});

it('exposes the users screen to admins only', function () {
    actingAs($this->admin)->get('/admin/users')->assertSuccessful();
    actingAs($this->staff)->get('/admin/users')->assertForbidden();
});

it('lets an admin assign a role and grant panel access from the screen', function () {
    $user = User::factory()->create(['email' => 'promote@example.com']);

    actingAs($this->admin);

    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->fillForm(['is_admin' => true, 'role' => 'staff'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->is_admin)->toBeTrue()
        ->and($user->fresh()->isStaff())->toBeTrue();
});
