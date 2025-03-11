<?php

namespace App\Livewire;

use App\Models\Schedule as ScheduleModel;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Schedule extends Component
{
    public CarbonImmutable $date;

    public array $calendar = [];

    public bool $showTimeSlots = false;

    public Collection $slots;

    public string $selectedDate = '';

    #[Locked]
    public Collection $availableDates;

    public ?ScheduleModel $schedule;

    public function __construct(private $scheduleService = new ScheduleService) {}

    public function mount(): void
    {
        $this->schedule = $this->scheduleService
            ->getActiveSchedule();

        $this->date = CarbonImmutable::create(
            Carbon::now()->year,
            Carbon::now()->month
        );
        $this->availableDates = $this->scheduleService
            ->getAvailableDatesForMonth(
                Carbon::createFromImmutable($this->date)
            );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function nextMonth(): void
    {
        $this->showTimeSlots = false;
        $this->date = $this->date->addMonth();
        $this->availableDates = (new ScheduleService)
            ->getAvailableDatesForMonth(
                Carbon::createFromImmutable($this->date)
            );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function prevMonth(): void
    {
        $this->showTimeSlots = false;
        $this->date = $this->date->subMonth();
        $this->availableDates = (new ScheduleService)
            ->getAvailableDatesForMonth(
                Carbon::createFromImmutable($this->date)
            );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function showSlots(string $date): void
    {
        if ($this->showTimeSlots && $this->selectedDate === $date) {
            $this->showTimeSlots = false;

            return;
        }

        $this->selectedDate = $date;
        $this->slots = $this->availableDates->get($date);
        $this->showTimeSlots = true;
    }

    public function bookSlot(string $date, string $timeSlot): void {}

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
                    'date' => $date->toDateString(),
                    'day' => $date->day,
                    'weekend' => $date->isWeekend(),
                    'withinMonth' => $date->between($startOfMonth, $endOfMonth),
                    'today' => $date->isToday(),
                    'available' => $this->availableDates->keys()->contains($date->format('Y-m-d')),
                ])
                ->chunk(7),
        ];
    }

    public static function render()
    {
        return view('livewire.schedule');
    }
}
