<?php

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the shop hub with evergreen categories', function () {
    get(route('shop.index'))
        ->assertOk()
        ->assertSee('Kaftans')
        ->assertSee('Evening &amp; Occasion Dresses', false);
});

it('serves an evergreen category landing at /collections/{slug}', function () {
    get('/collections/kaftans')
        ->assertOk()
        ->assertSee('Kaftans');
});

it('renders an attribute sub-landing and links it from the parent family', function () {
    // "Embroidered Dresses" is seeded as a child of Evening Dresses.
    get('/collections/embroidered-dresses')
        ->assertOk()
        ->assertSee('Embroidered Dresses');

    get('/collections/evening-dresses')
        ->assertOk()
        ->assertSee('/collections/embroidered-dresses', false);
});

it('renders the occasions hub and a specific occasion page', function () {
    get(route('occasions.index'))->assertOk()->assertSee('Wedding Guest');

    $occasion = Tag::where('slug', 'wedding-guest')->firstOrFail();
    get(route('occasions.show', $occasion))->assertOk()->assertSee('Wedding Guest');
});

it('only shows products carrying the occasion tag on the occasion page', function () {
    $occasion = Tag::where('slug', 'wedding-guest')->firstOrFail();
    $expected = $occasion->products()->published()->count();

    expect($expected)->toBeGreaterThan(0);
});

it('renders the lookbooks hub and a lookbook page', function () {
    get(route('lookbooks.index'))->assertOk()->assertSee('Lookbooks');
    get('/lookbooks/echoes-of-time')->assertOk()->assertSee('Echoes of Time');
});

it('301-redirects legacy category URLs to /collections/{slug}', function () {
    get('/category/kaftans')->assertStatus(301)->assertRedirect('/collections/kaftans');
});

it('301-redirects bare /collections to the shop hub', function () {
    get('/collections')->assertStatus(301)->assertRedirect('/shop');
});

it('301-redirects a legacy editorial collection slug to its lookbook', function () {
    get('/collections/echoes-of-time')
        ->assertStatus(301)
        ->assertRedirect(route('lookbooks.show', 'echoes-of-time'));
});

it('404s an unknown /collections slug', function () {
    get('/collections/not-a-real-thing')->assertNotFound();
});
