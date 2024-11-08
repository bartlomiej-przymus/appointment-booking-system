<div class="font-sans">
    @dump($calendar)
    <div class="mx-auto w-fit">
        <div class="flex">
            <button class="py-4 border border-gray-400 w-1/4" wire:click="prevMonth">Prev Month</button>
            <div class="font-light text-l text-center border border-gray-100 w-1/2">
                <h1>{{ $calendar['year'] }}</h1>
                <h1>{{ $calendar['month'] }}</h1>
            </div>
            <button class="py-4 border border-gray-400 w-1/4" wire:click="nextMonth">Next Month</button>
        </div>
        <div class="mt-1 font-sans text-lg grid">
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
                        <livewire:day :key="$loop->parent->index . $loop->index" :day="$day" />
                        <x-calendar.day
                            within-month="{{$day['withinMonth']}}"
                            weekend="{{$day['weekend']}}"
                            today="{{$day['today']}}"
                            available="{{$day['available']}}"
                        >
                            {{ $day['day'] }}
                        </x-calendar.day>
                    @endforeach
                @endforeach
            </div>
            {{--        <div class="grid grid-cols-1 align-top w-fit">--}}
            {{--            <div class="font-light h-16 flex justify-center items-center border border-slate-800 aspect-[2/1] hover:bg-slate-100">Time</div>--}}
            {{--            <div class="font-light h-16 flex justify-center items-center border border-slate-800 aspect-[2/1] hover:bg-slate-100">8:00</div>--}}
            {{--        </div>--}}
        </div>
    </div>
</div>
