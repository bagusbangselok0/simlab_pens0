@php
    $resolvedBreadcrumbs = \App\Support\BreadcrumbResolver::resolve($breadcrumbs ?? null, [
        'title' => $title ?? '',
        'user' => Auth::user(),
        'logbook' => $logbook ?? null,
    ]);
@endphp

@if (!empty($resolvedBreadcrumbs))
    <nav aria-label="Breadcrumb" class="simlab-breadcrumb-nav mt-2">
        <ol class="simlab-breadcrumb mb-0">
            @foreach ($resolvedBreadcrumbs as $item)
                @php
                    $isLast = $loop->last;
                    $isHome = !empty($item['is_home']);
                @endphp
                <li class="simlab-bc-item {{ $isLast ? 'is-current' : '' }}">
                    @if (!$loop->first)
                        <i class="bi bi-chevron-right simlab-bc-sep" aria-hidden="true"></i>
                    @endif

                    @if ($isHome && !empty($item['url']))
                        <a href="{{ $item['url'] }}" class="simlab-bc-link simlab-bc-home" title="Kembali ke Dashboard" aria-label="Dashboard">
                            <i class="bi bi-house-door-fill" aria-hidden="true"></i>
                            <span class="d-none d-sm-inline">{{ $item['label'] }}</span>
                        </a>
                    @elseif (!$isLast && !empty($item['url']))
                        <a href="{{ $item['url'] }}" class="simlab-bc-link" title="{{ $item['label'] }}">
                            <span class="simlab-bc-text">{{ $item['label'] }}</span>
                        </a>
                    @elseif (!$isLast)
                        <span class="simlab-bc-group">{{ $item['label'] }}</span>
                    @else
                        <span class="simlab-bc-current" aria-current="page" title="{{ $item['label'] }}">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
