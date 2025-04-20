<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Schedule as ScheduleModel;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Schedule extends Component
{
    public CarbonImmutable $date;

    public array $calendar = [];

    public bool $showTimeSlots = false;

    public Collection $slots;

    public string $selectedDate = '';

    public string $selectedTime = '';

    public Collection $availableDates;

    public ?ScheduleModel $schedule;

    private ScheduleService $scheduleService;

    public function boot(ScheduleService $scheduleService): void
    {
        $this->scheduleService = $scheduleService;
    }

    public function mount(): void
    {
        $this->slots = collect();
        $this->selectedDate = '';
        $this->selectedTime = '';
        $this->showTimeSlots = false;
        $this->schedule = $this->scheduleService
            ->getActiveSchedule();
        $this->date = CarbonImmutable::now()->startOfMonth();
        $this->refreshCalendar();
    }

    #[On('authentication-required')]
    #[On('time-or-date-invalid')]
    #[On('slot-unavailable')]
    #[On('booking-successful')]
    #[On('booking-failed')]
    public function reload(): void
    {
        $this->showTimeSlots = false;
        $this->selectedDate = '';
        $this->selectedTime = '';
        $this->refreshCalendar();
    }

    public function nextMonth(): void
    {
        $this->date = $this->date->addMonth();
        $this->reload();
    }

    public function prevMonth(): void
    {
        $this->date = $this->date->subMonth();
        $this->reload();
    }

    public function setDate(string $date): void
    {
        $this->selectedTime = '';

        if ($this->showTimeSlots && $this->selectedDate === $date) {
            $this->showTimeSlots = false;

            return;
        }

        $this->selectedDate = $date;
        $this->slots = $this->availableDates->get($date, collect());

        $this->showTimeSlots = $this->slots->isNotEmpty();
    }

    public function setTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function bookAppointment(): void
    {
        $date = $this->selectedDate;
        $time = $this->selectedTime;

        if (is_null(auth()->user())) {
            $this->dispatch('authentication-required', message: 'Please log in to book an appointment.');

            return;
        }

        if (! filled($date) || ! filled($time)) {
            $this->dispatch('time-or-date-invalid', 'You need to select date and time to book a slot');

            return;
        }

        if (is_null($this->schedule)) {
            $this->dispatch('booking-failed', message: 'No active schedule available.');

            return;
        }

        $isStillAvailable = (new Appointment)->isAvailable($date, $time, $this->schedule->getKey());

        if (! $isStillAvailable) {
            $this->dispatch('slot-unavailable', message: 'This slot was just booked by someone else.');
            $this->refreshCalendar();

            if ($this->showTimeSlots) {
                $this->slots = $this->availableDates->get($date) ?? collect();
            }

            return;
        }

        try {
            $user = auth()->user();

            $booking = $this->scheduleService
                ->bookAppointment(
                    $date,
                    $time,
                    $user,
                    $this->schedule,
                );
            $this->dispatch('booking-successful',
                message: 'Your appointment has been booked!',
                bookingId: $booking->id
            );
            $this->refreshCalendar();

            if ($this->showTimeSlots && $this->selectedDate === $date) {
                $this->slots = $this->availableDates->get($date) ?? collect();

                if ($this->slots->isEmpty()) {
                    $this->showTimeSlots = false;
                }
            }
        } catch (Exception $e) {
            logger()->error('Booking failed', ['exception' => $e->getMessage()]);
            $this->dispatch('booking-failed', message: 'Could not book this slot. Please try again.');
        }
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

    #[Computed]
    public function getAppointmentDuration(): int
    {
        if (! filled($this->selectedDate) || is_null($this->schedule)) {
            return 0;
        }

        return $this->scheduleService->getAppointmentDuration(
            $this->schedule,
            $this->selectedDate
        );
    }

    private function refreshCalendar(): void
    {
        $this->availableDates = $this->scheduleService->getAvailableDatesForMonth(
            Carbon::createFromImmutable($this->date)
        );
        $this->calendar = $this->generateCalendarMonth($this->date);
    }

    public function render(): View
    {
        return view('livewire.schedule');
    }
}
