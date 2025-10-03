@props(['href'])

@php
    $isActive = Request::routeIs($href);

    // Tentukan class berdasarkan status aktif
    $baseClasses = 'flex items-center p-2 rounded-lg hover:bg-gray-100';
    $activeClasses = ' text-blue-700 bg-blue-100 shadow-sm font-semibold';
    $inactiveClasses = ' text-gray-900';

    $linkClasses = $baseClasses . ($isActive ? $activeClasses : $inactiveClasses);

    // Tentukan class icon berdasarkan status aktif
    $iconClasses = 'fa-lg ' . ($isActive ? 'text-blue-700' : 'text-blue-500');
@endphp

<li>
    <a href="{{ route($href) }}" class="{{ $linkClasses }}">
        {{-- Menggunakan $attributes untuk mendapatkan class icon --}}
        <i {{ $attributes->merge(['class' => $iconClasses]) }}></i>
        <span class="ml-3">{{ $slot }}</span>
    </a>
</li>
