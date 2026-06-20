<x-layouts.storefront
    :title="$post->meta_title ?: $post->title"
    :description="$post->meta_description ?: $post->summary()"
    :image="$post->coverImageUrl()">

    <x-storefront.article
        :post="$post"
        :related="$related"
        index-route="blog.index"
        show-route="blog.show"
        :back-label="__('Back to Blog')"
        :more-label="__('More from the Blog')" />
</x-layouts.storefront>
