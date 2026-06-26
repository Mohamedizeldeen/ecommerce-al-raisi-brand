<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->orderByDesc('id')->get();

        return view('account.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $address = Auth::user()->addresses()->create($data);

        // First address (or one explicitly flagged) becomes the default.
        if (($data['is_default'] ?? false) || Auth::user()->addresses()->count() === 1) {
            $this->makeDefault($address);
        }

        return back()->with('success', __('Address saved.'));
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($address);

        $address->update($this->validated($request));

        if ($request->boolean('is_default')) {
            $this->makeDefault($address);
        }

        return back()->with('success', __('Address updated.'));
    }

    public function destroy(Address $address)
    {
        $this->authorizeAddress($address);
        $address->delete();

        return back()->with('success', __('Address removed.'));
    }

    public function setDefault(Address $address)
    {
        $this->authorizeAddress($address);
        $this->makeDefault($address);

        return back()->with('success', __('Default address updated.'));
    }

    private function makeDefault(Address $address): void
    {
        Auth::user()->addresses()->whereKeyNot($address->getKey())->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    private function authorizeAddress(Address $address): void
    {
        abort_unless($address->user_id === Auth::id(), 403);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}
