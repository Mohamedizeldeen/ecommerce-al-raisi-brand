<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', 'admin@amalalraisi.com')->first();
    $this->staff = User::factory()->create();
    $this->staff->forceFill(['is_admin' => true, 'role' => 'staff'])->save();
});

it('lets staff into the panel, orders, contact and reviews', function () {
    actingAs($this->staff)->get('/admin')->assertSuccessful();
    actingAs($this->staff)->get('/admin/orders')->assertSuccessful();
    actingAs($this->staff)->get('/admin/contact-messages')->assertSuccessful();
    actingAs($this->staff)->get('/admin/reviews')->assertSuccessful();
});

it('blocks staff from admin-only resources', function () {
    actingAs($this->staff)->get('/admin/products')->assertForbidden();
    actingAs($this->staff)->get('/admin/coupons')->assertForbidden();
    actingAs($this->staff)->get('/admin/settings')->assertForbidden();
    actingAs($this->staff)->get('/admin/newsletter-subscribers')->assertForbidden();
});

it('still lets a full admin into everything', function () {
    actingAs($this->admin)->get('/admin/products')->assertSuccessful();
    actingAs($this->admin)->get('/admin/settings')->assertSuccessful();
});

it('exposes the role helpers correctly', function () {
    expect($this->admin->isAdmin())->toBeTrue()
        ->and($this->admin->isStaff())->toBeFalse()
        ->and($this->staff->isStaff())->toBeTrue()
        ->and($this->staff->isAdmin())->toBeFalse();
});

it('grants and revokes panel access with the set-role command', function () {
    $user = User::factory()->create(['email' => 'newstaff@example.com']);

    artisan('user:set-role', ['email' => 'newstaff@example.com', 'role' => 'staff'])->assertExitCode(0);
    expect($user->fresh()->is_admin)->toBeTrue()
        ->and($user->fresh()->isStaff())->toBeTrue();

    artisan('user:set-role', ['email' => 'newstaff@example.com', 'role' => 'none'])->assertExitCode(0);
    expect($user->fresh()->is_admin)->toBeFalse();
});
