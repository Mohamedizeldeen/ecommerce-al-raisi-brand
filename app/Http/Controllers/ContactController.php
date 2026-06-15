<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        // Honeypot: bots fill hidden fields. Pretend success and drop silently.
        if ($request->filled('website')) {
            return back()->with('success', 'Thank you for your message.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if (config('services.hcaptcha.secret') && ! $this->verifyHcaptcha($request->input('h-captcha-response'))) {
            return back()->withInput()->with('error', 'Captcha verification failed. Please try again.');
        }

        ContactMessage::create($data);

        return back()->with('success', 'Thank you — we will be in touch shortly.');
    }

    private function verifyHcaptcha(?string $token): bool
    {
        if (! $token) {
            return false;
        }

        $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret' => config('services.hcaptcha.secret'),
            'response' => $token,
        ]);

        return (bool) $response->json('success', false);
    }
}
