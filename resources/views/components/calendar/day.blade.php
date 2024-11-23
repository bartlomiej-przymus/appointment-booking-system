@props([
    'header' => false,
    'weekend' => false,
    'withinMonth' => true,
    'today' => false,
    'available' => false,
    'selected' => false,
])

<button {{ $attributes }} @class([
    'font-light', 'h-16', 'flex', 'justify-center', 'items-center', 'aspect-[1/1]',
    'cursor-default' => ! $available,
    'border' => ! $header,
    'border-accentColor' => $today,
    'border-gray-200' => ! $today,
    'hover:bg-green-200' => $withinMonth && $available,
    'text-gray-300' => ! $withinMonth,
    'text-red-600' => $weekend && $withinMonth,
    'text-red-300' => $weekend && ! $withinMonth,
    'bg-green-100' => $available,
    'bg-green-500' => $selected,
])>
    {{ $slot }}
</button>
