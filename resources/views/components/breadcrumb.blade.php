@props([
    // Array of ['label' => string, 'route' => string|null, 'url' => string|null]
    'items' => [],
])

@if (! empty($items))
    <nav aria-label="Breadcrumb" class="mt-1">
        <ol class="flex flex-wrap items-center gap-1 text-xs text-muted-ink">
            @foreach ($items as $index => $item)
                @php
                    $isLast = $index === count($items) - 1;
                    $href = $item['url'] ?? ($item['route'] ?? null ? route($item['route']) : null);
                @endphp
                <li class="inline-flex items-center gap-1">
                    @if (! $isLast && $href)
                        <a href="{{ $href }}" class="font-semibold text-muted-ink transition hover:text-campus-green focus-visible:text-campus-green">{{ $item['label'] }}</a>
                    @else
                        <span @if ($isLast) aria-current="page" class="text-ink" @endif>{{ $item['label'] }}</span>
                    @endif
                    @unless ($isLast)
                        <span aria-hidden="true" class="text-border-line">›</span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </nav>
@endif
