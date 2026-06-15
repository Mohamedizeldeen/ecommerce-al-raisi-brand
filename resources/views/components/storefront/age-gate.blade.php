@unless (request()->cookie('age_verified'))
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-ink/95 px-4">
        <div class="max-w-md bg-white p-10 text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ config('app.name') }}</p>
            <h2 class="mt-4 font-serif text-3xl text-ink">Welcome</h2>
            <p class="mt-4 text-stone-600">You must be 18 years or older to enter this site.</p>
            <p class="mt-2 text-sm text-stone-500">Are you 18 years old or older?</p>
            <div class="mt-8 flex justify-center gap-4">
                <form method="POST" action="{{ route('age.verify') }}">
                    @csrf
                    <button type="submit" class="bg-ink px-8 py-3 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">Yes, I am</button>
                </form>
                <a href="https://www.google.com" class="border border-stone-soft px-8 py-3 text-xs uppercase tracking-[0.2em] text-stone-500 transition hover:border-ink">No</a>
            </div>
        </div>
    </div>
@endunless
