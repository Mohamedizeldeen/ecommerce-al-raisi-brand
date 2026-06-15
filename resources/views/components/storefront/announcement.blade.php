@php
    $messages = [
        'Complimentary shipping on orders over OMR100.000',
        '10% off your first order — code WELCOME10',
        'Handcrafted in the Sultanate of Oman',
    ];
@endphp

<div class="overflow-hidden bg-ink text-white">
    <div class="flex w-max">
        @for ($track = 0; $track < 2; $track++)
            <div class="flex shrink-0 animate-marquee items-center gap-12 whitespace-nowrap py-2.5 pr-12 text-[10px] uppercase tracking-[0.28em]" @if ($track === 1) aria-hidden="true" @endif>
                @for ($i = 0; $i < 2; $i++)
                    @foreach ($messages as $message)
                        <span>{{ $message }}</span>
                        <span class="text-accent">&bull;</span>
                    @endforeach
                @endfor
            </div>
        @endfor
    </div>
</div>
