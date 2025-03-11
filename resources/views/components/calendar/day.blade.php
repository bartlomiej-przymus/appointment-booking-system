@props([
    'header' => false,
    'weekend' => false,
    'withinMonth' => true,
    'today' => false,
    'available' => false,
    'selected' => false,
])

<button {{ $attributes }} @class([
    'font-light', 'h-14', 'flex', 'justify-center', 'items-center', 'aspect-[1/1]', 'rounded-full',
    'cursor-default' => ! $available,
    'border' => ! $header,
    'border-gray-200' => ! $today,
    'border-accentColor' => $today,
    'hover:bg-red-200' => $withinMonth && $available,
    'text-gray-300' => ! $withinMonth,
    'text-red-600' => $weekend && $withinMonth,
    'text-red-300' => $weekend && ! $withinMonth,
    'bg-red-100' => $available,
    'bg-red-500' => $selected,
])>
    {{ $slot }}
</button>
