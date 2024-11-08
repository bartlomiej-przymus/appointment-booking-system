
{{--'header' => false,--}}
{{--'weekend' => false,--}}
{{--'withinMonth' => true,--}}
{{--'today' => false,--}}
{{--'available' => false,--}}

<div @class([
    'font-light', 'h-16', 'flex', 'justify-center', 'items-center', 'aspect-[1/1]',
    'border' => ! $header,
    'border-accentColor' => $today,
    'border-gray-200' => ! $today,
    'hover:bg-indigo-200' => $withinMonth && $available,
    'text-gray-300' => ! $withinMonth,
    'text-red-600' => $weekend,
    'bg-indigo-100' => $available,
])>
    {{ $slot }}
</div>
