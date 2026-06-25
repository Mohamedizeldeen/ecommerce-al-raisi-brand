<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', 'admin@amalalraisi.com')->first();
    $this->customer = User::where('email', 'customer@example.com')->first();
});

it('forbids non-admins from the panel', function () {
    actingAs($this->customer)->get('/admin')->assertForbidden();
});

it('loads every admin resource list page for an admin', function () {
    $paths = [
        '/admin',
        '/admin/products',
        '/admin/categories',
        '/admin/collections',
        '/admin/coupons',
        '/admin/orders',
        '/admin/newsletter-subscribers',
        '/admin/contact-messages',
        '/admin/settings',
    ];

    foreach ($paths as $path) {
        actingAs($this->admin)->get($path)->assertSuccessful();
    }
});

it('loads the product edit page with its variants relation manager', function () {
    $product = Product::has('variants')->first();

    actingAs($this->admin)
        ->get('/admin/products/'.$product->getRouteKey().'/edit')
        ->assertSuccessful();
});

it('keeps the advertised welcome coupon in sync when the newsletter discount is saved', function () {
    actingAs($this->admin);

    Livewire::test(ManageSettings::class)
        ->fillForm([
            'newsletter_discount_percent' => 15,
            'free_shipping_threshold_omr' => 100,
            'shipping_flat_omr' => 2,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $coupon = Coupon::where('code', 'WELCOME15')->first();

    expect($coupon)->not->toBeNull()
        ->and($coupon->is_active)->toBeTrue()
        ->and((int) $coupon->value)->toBe(15);
});
