@props(['status'])
@php
    $colors = [
        'draft' => 'gray',
        'documents_pending' => 'yellow',
        'ready' => 'blue',
        'filed' => 'indigo',
        'cleared' => 'green',
        'held' => 'red',
    ];
    $color = $colors[$status] ?? 'gray';
    $label = ucwords(str_replace('_', ' ', $status));
@endphp
<span {{ $attributes->merge(['class' => "badge badge-$color"]) }}>{{ $label }}</span>
