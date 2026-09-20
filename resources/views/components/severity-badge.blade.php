@props(['severity'])
@php
    $colors = [
        'low' => 'gray',
        'medium' => 'yellow',
        'high' => 'red',
        'critical' => 'red',
    ];
    $color = $colors[$severity] ?? 'gray';
@endphp
<span {{ $attributes->merge(['class' => "badge badge-$color"]) }}>{{ ucfirst($severity) }}</span>
