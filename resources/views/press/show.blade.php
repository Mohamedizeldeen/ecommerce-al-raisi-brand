<x-layouts.storefront
    :title="$post->meta_title ?: $post->title"
    :description="$post->meta_description ?: $post->summary()"
    :image="$post->coverImageUrl()">

    <x-storefront.article
        :post="$post"
        :related="$related"
        index-route="press.index"
        show-route="press.show"
        :back-label="__('Back to Press')"
        :more-label="__('More Press')" />
</x-layouts.storefront>
