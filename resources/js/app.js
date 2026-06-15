import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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

window.Alpine = Alpine;

Alpine.start();
