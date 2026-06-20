<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AtelierController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\PressController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ThawaniWebhookController;
use App\Http\Controllers\VirtualTryOnController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{collection:slug}', [CollectionController::class, 'show'])->name('collections.show');

Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/atelier', [AtelierController::class, 'index'])->name('atelier');

// Editorial — Blog articles and Press releases (admin-managed via Filament).
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/press', [PressController::class, 'index'])->name('press.index');
Route::get('/press/{post:slug}', [PressController::class, 'show'])->name('press.show');

Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Virtual try-on (Gemini image model) — heavily throttled (image generation is costly).
Route::post('/products/{product:slug}/try-on', [VirtualTryOnController::class, 'store'])
    ->middleware('throttle:6,10')
    ->name('products.try-on');

Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:30,1')->name('search');

Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart.index');
    Route::get('/cart/drawer', 'drawer')->name('cart.drawer');
    Route::post('/cart/add', 'add')->name('cart.add');
    Route::patch('/cart/items/{item}', 'update')->name('cart.update');
    Route::delete('/cart/items/{item}', 'remove')->name('cart.remove');
    Route::post('/cart/coupon', 'applyCoupon')->middleware('throttle:10,1')->name('cart.coupon');
});

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

Route::post('/thawani/webhook', ThawaniWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('thawani.webhook');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,10');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order:order_number}', [AccountController::class, 'show'])->name('orders.show');
});

// Static content pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/size-guide', 'pages.size-guide')->name('size-guide');
Route::view('/pages/privacy-policy', 'pages.privacy')->name('pages.privacy');
Route::view('/pages/shipping-returns', 'pages.shipping')->name('pages.shipping');
Route::view('/pages/terms', 'pages.terms')->name('pages.terms');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit']);

Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Language + currency preference (region switcher).
Route::post('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');

// Storefront AI assistant (Gemini, grounded in store data only).
Route::post('/assistant/chat', [AssistantController::class, 'chat'])
    ->middleware('throttle:20,1')
    ->name('assistant.chat');

Route::post('/age-verify', function () {
    return back()->withCookie(cookie('age_verified', '1', 60 * 24 * 365));
})->name('age.verify');

Route::get('/sitemap.xml', SitemapController::class);

Route::get('/order/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
