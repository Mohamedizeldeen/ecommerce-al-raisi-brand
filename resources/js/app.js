import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const setCookie = (name, value, days = 365) => {
    document.cookie = `${name}=${value}; path=/; max-age=${days * 24 * 60 * 60}; samesite=lax`;
};
const hasCookie = (name) => document.cookie.split('; ').some((c) => c.startsWith(name + '='));

// #3 Cookie-consent banner.
Alpine.data('cookieConsent', () => ({
    show: false,
    init() {
        if (hasCookie('cookie_consent')) return;
        // Avoid stacking with the welcome offer: wait until it's been seen
        // (welcome_seen) before showing. Fallback reveal after 15s.
        const start = Date.now();
        const reveal = () => {
            if (hasCookie('welcome_seen') || Date.now() - start > 15000) {
                this.show = true;
            } else {
                setTimeout(reveal, 600);
            }
        };
        reveal();
    },
    accept() {
        setCookie('cookie_consent', 'accepted');
        this.show = false;
    },
    decline() {
        setCookie('cookie_consent', 'declined');
        this.show = false;
    },
}));

// #6 First-order welcome offer (10% off) — shown once per visitor.
Alpine.data('welcomeOffer', (code = 'WELCOME10') => ({
    show: false,
    code,
    copied: false,
    init() {
        if (hasCookie('welcome_seen')) return;
        setTimeout(() => { this.show = true; }, 2500);
    },
    markSeen() {
        setCookie('welcome_seen', '1');
    },
    dismiss() {
        this.markSeen();
        this.show = false;
    },
    copy() {
        navigator.clipboard?.writeText(this.code);
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 1600);
    },
}));

Alpine.store('toast', {
    items: [],
    push(message, type = 'success') {
        if (! message) return;
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type });
        setTimeout(() => this.remove(id), 3800);
    },
    remove(id) {
        this.items = this.items.filter((t) => t.id !== id);
    },
});

Alpine.store('cart', {
    count: window.__cartCount ?? 0,
    open: false,
    loading: false,
    html: '',

    async refresh() {
        const res = await fetch('/cart/drawer', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        this.html = data.html;
        this.count = data.count;
    },

    async openDrawer() {
        this.open = true;
        await this.refresh();
    },

    close() {
        this.open = false;
    },

    async add(variantId, qty = 1) {
        if (! variantId) {
            Alpine.store('toast').push('Please select your options.', 'error');
            return;
        }

        this.loading = true;

        try {
            const res = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ variant_id: variantId, quantity: qty }),
            });

            const data = await res.json();

            if (res.ok && data.ok) {
                this.count = data.count;
                Alpine.store('toast').push(data.message ?? 'Added to your bag.');
                await this.openDrawer();
            } else {
                Alpine.store('toast').push(data.message ?? 'Could not add to your bag.', 'error');
            }
        } catch (e) {
            Alpine.store('toast').push('Something went wrong. Please try again.', 'error');
        } finally {
            this.loading = false;
        }
    },
});

const escapeHtml = (s) =>
    s.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

// Storefront AI assistant — chats with customers about products & support.
Alpine.data('chatAssistant', () => ({
    open: false,
    sent: false,        // has the customer sent at least one message?
    loading: false,
    input: '',
    messages: [],       // [{ role: 'user' | 'assistant', content: string }]
    suggestions: [
        'هل لديكم أوشحة حرير؟',
        'ما هي سياسة الإرجاع؟',
        'كيف أختار مقاسي؟',
    ],

    toggle() {
        this.open = ! this.open;
        if (this.open) this.$nextTick(() => this.focusInput());
    },

    focusInput() {
        this.$refs.input?.focus();
    },

    suggest(text) {
        this.input = text;
        this.send();
    },

    // Render an assistant message: escape, then linkify URLs, **bold** and line breaks.
    render(content) {
        return escapeHtml(content)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" class="underline hover:text-accent" target="_blank" rel="noopener">$1</a>')
            .replace(/\n/g, '<br>');
    },

    async send() {
        const text = this.input.trim();
        if (! text || this.loading) return;

        // History = the conversation so far (before this new message).
        const history = this.messages.map((m) => ({ role: m.role, content: m.content }));

        this.messages.push({ role: 'user', content: text });
        this.input = '';
        this.sent = true;
        this.loading = true;
        this.scrollDown();

        try {
            const res = await fetch('/assistant/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message: text, history }),
            });

            const data = await res.json().catch(() => ({}));

            this.messages.push({
                role: 'assistant',
                content: data.reply || 'عذرًا، لم أتمكن من الرد الآن. / Sorry, I could not reply just now.',
            });
        } catch (e) {
            this.messages.push({
                role: 'assistant',
                content: 'عذرًا، حدث خطأ في الاتصال. / Sorry, a connection error occurred.',
            });
        } finally {
            this.loading = false;
            this.scrollDown();
        }
    },

    scrollDown() {
        this.$nextTick(() => {
            const log = this.$refs.log;
            if (log) log.scrollTop = log.scrollHeight;
        });
    },
}));

window.Alpine = Alpine;

Alpine.start();
