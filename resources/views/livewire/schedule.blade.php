<div class="font-sans">
    <div class="mx-auto w-fit mt-20">
        <div class="flex">
            <button class="py-4 border border-gray-400 w-1/4" wire:click="prevMonth">Prev Month</button>
            <div class="font-light text-l text-center border border-gray-100 w-1/2">
                <h1>{{ $calendar['year'] }}</h1>
                <h1>{{ $calendar['month'] }}</h1>
            </div>
            <button class="py-4 border border-gray-400 w-1/4" wire:click="nextMonth">Next Month</button>
        </div>
        <div class="mt-1 font-sans text-lg flex">
            <div class="grid grid-cols-7 w-fit gap-1 font-light">
                <div class="h-16 flex justify-center items-center aspect-[1/1]">
                    Mon
                </div>
                <div class="h-16 flex justify-center items-center aspect-[1/1]">
                    Tue
                </div>
                <div class="h-16 flex justify-center items-center aspect-[1/1]">
                    Wed
                </div>
                <div class="h-16 flex justify-center items-center aspect-[1/1]">
                    Thu
                </div>
                <div class="h-16 flex justify-center items-center aspect-[1/1]">
                    Fri
                </div>
                <div class="h-16 flex justify-center items-center aspect-[1/1] text-red-600">
                    Sat
                </div>
                <div class="h-16 flex justify-center items-center aspect-[1/1] text-red-600">
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
            @if($showTimeSlots)
            <div class="grid grid-cols-1 w-fit gap-1 content-start ml-1">
                <div class="font-light h-16 flex justify-center items-center aspect-[2/1">Available Time</div>
                @foreach($slots as $slot)
                <div class="font-light h-16 flex justify-center items-center border border-gray-200 aspect-[2/1] hover:bg-gray-100">
                    {{$slot}}
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
