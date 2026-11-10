@props(['status'])
@php
    $styles = [
        'success' => 'bg-green-100 text-sign-green-dark',
        'warning' => 'bg-amber-100 text-sign-amber',
        'info' => 'bg-blue-100 text-sign-blue',
        'danger' => 'bg-red-100 text-sign-red',
    ];
    $dots = [
        'success' => 'bg-sign-green',
        'warning' => 'bg-amber-500',
        'info' => 'bg-sign-blue',
        'danger' => 'bg-sign-red',
    ];
    $color = $status->color();
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold', $styles[$color] ?? '']) }}>
    <span class="size-2 rounded-full {{ $dots[$color] ?? '' }}" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
