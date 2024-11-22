<?php

namespace App\Livewire;

use App\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Schedule extends Component
{
    public CarbonImmutable $date;

    public $calendar;

    #[Locked]
    public $availableDates;

    public function mount(): void
    {
        $this->date = CarbonImmutable::create(
            Carbon::now()->year,
            Carbon::now()->month
        );
        $this->availableDates = (new ScheduleService)
            ->getAvailableDatesForMonth(
                Carbon::createFromImmutable($this->date)
            );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function nextMonth(): void
    {
        $this->date = $this->date->addMonth();
        $this->availableDates = (new ScheduleService)
            ->getAvailableDatesForMonth(
                Carbon::createFromImmutable($this->date)
            );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function prevMonth(): void
    {
        $this->date = $this->date->subMonth();
        $this->availableDates = (new ScheduleService)
            ->getAvailableDatesForMonth(
                Carbon::createFromImmutable($this->date)
            );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function generateCalendarMonth(CarbonImmutable $date): array
    {
        $startOfMonth = $date->startOfMonth();
        $endOfMonth = $date->endOfMonth();
        $startOfWeek = $startOfMonth->startOfWeek(CarbonInterface::MONDAY);
        $endOfWeek = $endOfMonth->endOfWeek(CarbonInterface::SUNDAY);

        return [
            'year' => $date->year,
            'month' => $date->format('F'),
            'weeks' => collect($startOfWeek->toPeriod($endOfWeek)->toArray())
                ->map(fn ($date) => [
                    'date' => $date,
                    'day' => $date->day,
                    'weekend' => $date->isWeekend(),
                    'withinMonth' => $date->between($startOfMonth, $endOfMonth),
                    'today' => $date->isToday(),
                    'available' => rand(0, 1) === 1,
                ])
                ->chunk(7),
        ];
    }

    public static function render()
    {
        return view('livewire.schedule');
    }
}
