{{--
    Skeleton Loader Component
    Usage: <x-skeleton class="h-6 w-32" />
           <x-skeleton class="h-40 w-full" rounded="lg" />
--}}
@props(['rounded' => 'md'])

<div {{ $attributes->merge(['class' => "siperlo-skeleton rounded-{$rounded}"]) }}
     aria-hidden="true">
</div>
