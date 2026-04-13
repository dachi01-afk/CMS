@props(['href'])

@php
    $isActive = Request::routeIs($href);

    $baseClasses = 'sidebar-link group relative flex items-center rounded-lg p-2 transition overflow-visible';
    $activeClasses = 'text-blue-700 bg-blue-100 shadow-sm font-semibold';
    $inactiveClasses = 'text-gray-900 hover:bg-gray-100';

    $linkClasses = $baseClasses . ' ' . ($isActive ? $activeClasses : $inactiveClasses);
    $iconClasses = 'sidebar-icon fa-lg shrink-0 ' . ($isActive ? 'text-blue-700' : 'text-blue-500');

    $url = Route::has($href) ? route($href) : '#';
@endphp

<li class="relative overflow-visible">
    <a href="{{ $url }}" class="{{ $linkClasses }}" data-sidebar-tooltip="{{ trim(strip_tags($slot)) }}">
        <i {{ $attributes->merge(['class' => $iconClasses]) }}></i>

        <span class="sidebar-label ml-3 truncate">
            {{ $slot }}
        </span>
    </a>
</li>
