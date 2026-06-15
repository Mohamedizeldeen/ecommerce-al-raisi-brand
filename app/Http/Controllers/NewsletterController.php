<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
            ['subscribed_at' => now()],
        );

        $percent = (int) Setting::get('newsletter_discount_percent', 10);

        return back()->with('success', "Thank you for subscribing! Use code WELCOME{$percent} for {$percent}% off your first order.");
    }
}
