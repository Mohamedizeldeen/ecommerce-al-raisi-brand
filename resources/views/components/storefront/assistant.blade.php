@if (config('assistant.enabled', true))
    <div x-data="chatAssistant" x-cloak
        class="fixed bottom-5 right-5 z-[100] flex flex-col items-end sm:bottom-6 sm:right-6">

        {{-- Chat panel --}}
        <div x-show="open" x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
            x-transition:enter-start="translate-y-4 opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transition duration-200 ease-in"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-4 opacity-0"
            @keydown.escape.window="open = false"
            role="dialog" aria-modal="true" aria-label="{{ __('Store assistant chat') }}"
            class="mb-3 flex h-[70vh] max-h-[560px] w-[calc(100vw-2.5rem)] max-w-sm origin-bottom-right flex-col overflow-hidden rounded-2xl border border-stone-soft bg-white shadow-2xl">

            {{-- Header --}}
            <header class="flex items-center justify-between gap-3 bg-ink px-4 py-3 text-white">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-accent/90">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 10.5h8M8 14h5m-9 5.5 3.2-2.4A2 2 0 0 1 10.4 16.5H17a3 3 0 0 0 3-3v-5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v11Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-serif text-lg leading-none">{{ config('app.name') }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.25em] text-white/50">مساعد المتجر · {{ __('Assistant') }}</p>
                    </div>
                </div>
                <button @click="open = false" aria-label="{{ __('Close') }}" class="text-white/70 transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            {{-- Message log --}}
            <div x-ref="log" aria-live="polite" class="flex-1 space-y-3 overflow-y-auto bg-sand/40 px-4 py-4 text-sm leading-relaxed">
                {{-- Greeting (UI only) --}}
                <div class="flex">
                    <div dir="auto"
                        class="max-w-[85%] break-words rounded-2xl rounded-tl-sm border border-stone-soft bg-white px-3.5 py-2.5 text-ink shadow-sm">
                        مرحبًا 👋 أنا مساعد {{ config('app.name') }}. أقدر أساعدك في المنتجات، المقاسات، الشحن والإرجاع.
                        <span class="mt-1 block text-stone-400">{{ __('Hi! Ask me about our products, sizing, shipping & returns.') }}</span>
                    </div>
                </div>

                {{-- Suggestion chips (before first message) --}}
                <div x-show="! sent" class="flex flex-wrap gap-2 pt-1">
                    <template x-for="s in suggestions" :key="s">
                        <button type="button" @click="suggest(s)" x-text="s" dir="auto"
                            class="rounded-full border border-stone-soft bg-white px-3 py-1.5 text-xs text-ink transition hover:border-accent hover:text-accent"></button>
                    </template>
                </div>

                {{-- Conversation --}}
                <template x-for="(m, i) in messages" :key="i">
                    <div class="flex min-w-0" :class="m.role === 'user' ? 'justify-end' : ''">
                        <div dir="auto" class="max-w-[85%] min-w-0 [overflow-wrap:anywhere] rounded-2xl px-3.5 py-2.5 shadow-sm"
                            :class="m.role === 'user'
                                ? 'rounded-tr-sm bg-ink text-white'
                                : 'rounded-tl-sm border border-stone-soft bg-white text-ink'">
                            <span x-show="m.role === 'assistant'" class="block [&_a]:break-all" x-html="render(m.content)"></span>
                            <span x-show="m.role === 'user'" x-text="m.content"></span>
                        </div>
                    </div>
                </template>

                {{-- Typing indicator --}}
                <div x-show="loading" class="flex">
                    <div class="rounded-2xl rounded-tl-sm border border-stone-soft bg-white px-4 py-3 shadow-sm">
                        <span class="flex items-center gap-1">
                            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-stone-400 [animation-delay:-0.3s]"></span>
                            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-stone-400 [animation-delay:-0.15s]"></span>
                            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-stone-400"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <form @submit.prevent="send()" class="flex items-center gap-2 border-t border-stone-soft bg-white px-3 py-3">
                <input x-ref="input" x-model="input" type="text" :disabled="loading" dir="auto"
                    placeholder="اكتب رسالتك…  /  Type a message…" autocomplete="off"
                    class="min-w-0 flex-1 rounded-full border border-stone-soft bg-sand/50 px-4 py-2 text-sm text-ink placeholder-stone-400 focus:border-accent focus:outline-none">
                <button type="submit" :disabled="loading || ! input.trim()" aria-label="{{ __('Send') }}"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-accent text-white transition hover:bg-accent-dark disabled:cursor-not-allowed disabled:opacity-40">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6" />
                    </svg>
                </button>
            </form>
        </div>

        {{-- Launcher --}}
        <button x-ref="launcher" @click="toggle()"
            x-effect="if (! open && sent) $nextTick(() => $refs.launcher?.focus())"
            :aria-expanded="open" aria-label="{{ __('Chat with us') }}"
            class="grid h-14 w-14 place-items-center rounded-full bg-ink text-white shadow-xl transition hover:bg-accent">
            <svg x-show="! open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 10.5h8M8 14h5m-9 5.5 3.2-2.4A2 2 0 0 1 10.4 16.5H17a3 3 0 0 0 3-3v-5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v11Z" />
            </svg>
            <svg x-show="open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif
