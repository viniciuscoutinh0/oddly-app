@props(['position' => 1])

<div
    {{ $attributes->class([
        'flex-1 rounded-xl border border-dashed border-zinc-800',
        'order-2 min-h-52' => $position === 1,
        'order-1 min-h-44' => $position === 2,
        'order-3 min-h-36' => $position === 3,
    ]) }}>
</div>
