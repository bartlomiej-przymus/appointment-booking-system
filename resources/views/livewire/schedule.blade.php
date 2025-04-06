<section class="mt-10 mx-auto flex flex-row max-w-7xl border border-gray-400 shadow-md rounded">
    <div class="w-1/3 px-6 py-8 border-r border-gray-400 flex flex-col gap-3">
        @if($schedule)
            <div class="text-gray-500 text-xl">
                {{ $schedule->user->name }}
            </div>
            <div class="text-gray-800 text-3xl">
                {{ $schedule->name }}
            </div>
        @else
            <div class="text-gray-500 text-xl">
{{--                TODO: make no active schedule fully booked in a month text customizable--}}
                Fully booked text / no active schedule
            </div>
        @endif
        @if($selectedDate)
            <div class="flex flex-row items-center gap-2 text-md text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div class="text-gray-400">
                    {{ $this->getAppointmentDuration() }} min.
                </div>
            </div>
            <div class="flex flex-row items-center gap-2 text-md text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <div class="text-gray-400">
                    {{ now()->parse($selectedDate)->format('l dS \o\f F Y') }}
                </div>
            </div>
        @endif
    </div>
    <div class="w-2/3 px-6 py-8 flex flex-col gap-3">
        <div class="text-gray-800 text-xl">
            Select Date & Time
        </div>
        <div class="flex flex-row">
            <div class="flex flex-col w-3/4">
                <nav class="flex flex-row gap-3 items-center justify-center h-8">
                    <button class="p-2 rounded-full hover:bg-red-200 text-gray-500 flex flex-row items-center" wire:click="prevMonth">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <div class="font-light text-l text-center px-4 w-1/4">
                        <h1>{{ $calendar['month'] }} - {{ $calendar['year'] }}</h1>
                    </div>
                    <button class="p-2 rounded-full hover:bg-red-200 text-gray-500 flex flex-row items-center" wire:click="nextMonth">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </nav>
                <div class="grid grid-cols-7 gap-3 font-light">
                    <div class="h-14 flex justify-center items-center aspect-[1/1]">
                        Mon
                    </div>
                    <div class="h-14 flex justify-center items-center aspect-[1/1]">
                        Tue
                    </div>
                    <div class="h-14 flex justify-center items-center aspect-[1/1]">
                        Wed
                    </div>
                    <div class="h-14 flex justify-center items-center aspect-[1/1]">
                        Thu
                    </div>
                    <div class="h-14 flex justify-center items-center aspect-[1/1]">
                        Fri
                    </div>
                    <div class="h-14 flex justify-center items-center aspect-[1/1] text-red-600">
                        Sat
                    </div>
                    <div class="h-14 flex justify-center items-center aspect-[1/1] text-red-600">
                        Sun
                    </div>

                    @foreach($calendar['weeks'] as $week)
                        @foreach($week as $day)
                            @if($day['available'])
                                <x-calendar.day
                                    wire:click="showSlots('{{$day['date']}}')"
                                    within-month="{{$day['withinMonth']}}"
                                    weekend="{{$day['weekend']}}"
                                    today="{{$day['today']}}"
                                    available="{{$day['available']}}"
                                    selected="{{ $selectedDate === $day['date'] }}"
                                >
                                    {{ $day['day'] }}
                                </x-calendar.day>
                            @else
                                <x-calendar.day
                                    within-month="{{$day['withinMonth']}}"
                                    weekend="{{$day['weekend']}}"
                                    today="{{$day['today']}}"
                                    disabled
                                >
                                    {{ $day['day'] }}
                                </x-calendar.day>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
            <aside class="w-1/4 pt-8 text-center flex flex-col gap-3 pl-4">
                @if($showTimeSlots)
                    @foreach($slots as $slot)
                        <div class="font-light h-14 flex justify-center items-center border border-gray-200 hover:bg-gray-100">
                            {{$slot}}
                        </div>
                    @endforeach
                @endif
            </aside>
        </div>
    </div>
</section>
