<x-layouts.storefront :title="$category->name" :description="$category->description" :image="$products->first()?->primaryImageUrl()">
    <section class="border-b border-stone-soft">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 text-center">
            {{-- Breadcrumb: Shop / [parent] / current --}}
            <nav class="mb-4 text-xs uppercase tracking-[0.18em] text-stone-500">
                <a href="{{ route('shop.index') }}" class="hover:text-accent">{{ __('Shop') }}</a>
                @if ($category->parent)
                    <span class="mx-2">/</span>
                    <a href="{{ route('categories.show', $category->parent->slug) }}" class="hover:text-accent">{{ $category->parent->name }}</a>
                @endif
                <span class="mx-2">/</span>
                <span class="text-ink">{{ $category->name }}</span>
            </nav>

            <h1 class="text-4xl text-ink">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mx-auto mt-3 max-w-2xl text-stone-600">{{ $category->description }}</p>
            @endif

            {{-- Sub-categories as their own landing pages (SDM subcategory template) --}}
            @if ($children->isNotEmpty())
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach ($children as $child)
                        <a href="{{ route('categories.show', $child->slug) }}"
                            class="border border-stone-soft px-4 py-2 text-[11px] uppercase tracking-[0.18em] text-ink/80 transition hover:border-accent hover:text-accent">
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-14">
        <x-storefront.product-grid :products="$products" :facets="$facets" />
    </section>
</x-layouts.storefront>
