@props([
    'name',
    'alt' => null,
    'mobile' => false,
])

@php
    $fallbackName = 'sidebar-settings';

    $resolvedName = $name;

    $iconSourcePath = base_path('packages/medsdn/Admin/src/Resources/assets/images/streamline-plump-color/' . $resolvedName . '.svg');

    if (! file_exists($iconSourcePath)) {
        $resolvedName = $fallbackName;
    }

    $dataAttribute = $mobile ? 'data-mobile-shell-icon' : 'data-shell-icon';
@endphp

<img
    src="{{ bagisto_asset('images/streamline-plump-color/' . $resolvedName . '.svg', 'admin') }}"
    alt="{{ $alt ?? str($resolvedName)->replace('-', ' ')->title() }}"
    {{ $attributes->merge([
        'class' => 'h-6 w-6',
        $dataAttribute => $resolvedName,
    ]) }}
/>
