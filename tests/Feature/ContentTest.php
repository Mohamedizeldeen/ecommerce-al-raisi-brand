<?php

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the static content pages', function () {
    foreach (['/about', '/size-guide', '/contact', '/pages/privacy-policy', '/pages/shipping-returns', '/pages/terms'] as $path) {
        get($path)->assertOk();
    }
});

it('subscribes an email to the newsletter', function () {
    post('/newsletter', ['email' => 'sub@example.com'])->assertSessionHas('success');

    expect(NewsletterSubscriber::where('email', 'sub@example.com')->exists())->toBeTrue();
});

it('stores a contact message but ignores honeypot bots', function () {
    post('/contact', ['name' => 'Aisha', 'email' => 'aisha@example.com', 'message' => 'Hello, I have a question.'])
        ->assertSessionHas('success');
    expect(ContactMessage::count())->toBe(1);

    post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'spam', 'website' => 'http://spam'])
        ->assertSessionHas('success');
    expect(ContactMessage::count())->toBe(1);
});

it('serves an XML sitemap', function () {
    get('/sitemap.xml')->assertOk()->assertSee('<urlset', false);
});

it('shows the 18+ age gate and sets a cookie when accepted', function () {
    get('/')->assertSee('Are you 18 years old or older?');

    post('/age-verify')->assertRedirect()->assertCookie('age_verified');
});
