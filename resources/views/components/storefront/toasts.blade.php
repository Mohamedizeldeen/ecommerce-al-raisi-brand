<div class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4 sm:items-end sm:px-6">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex items-center gap-4 border-l-2 bg-ink px-5 py-3 text-sm text-white shadow-xl"
            :class="toast.type === 'error' ? 'border-red-400' : 'border-accent'">
            <span x-text="toast.message"></span>
            <button @click="$store.toast.remove(toast.id)" class="text-white/50 transition hover:text-white" aria-label="Dismiss">&times;</button>
        </div>
    </template>
</div>
