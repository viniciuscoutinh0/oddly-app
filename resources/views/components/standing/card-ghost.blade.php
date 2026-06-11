@props(['position' => 1])

<div
    {{ $attributes->class([
            'order-1' => $position === 2,
            'order-3' => $position === 3,
        ])->merge(['class' => 'border border-zinc-800 rounded-xl border-dashed']) }}>
</div>
