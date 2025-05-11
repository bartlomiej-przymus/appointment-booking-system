<?php

namespace App\Livewire;

use App\Models\Schedule as ScheduleModel;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
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
        if (auth()->check() && $this->hasSessionAppointmentData()) {
            $this->retrieveBookingInfo();
        } else {
            $this->slots = collect();
            $this->selectedDate = '';
            $this->selectedTime = '';
            $this->showTimeSlots = false;
            $this->schedule = $this->scheduleService
                ->getActiveSchedule();
            $this->date = CarbonImmutable::now()->startOfMonth();
            $this->refreshCalendar();
        }
    }

    private function hasSessionAppointmentData(): bool
    {
        return Session::has('appointment_calendar_date')
            && Session::has('appointment_selected_date')
            && Session::has('appointment_selected_time');
    }

    #[On('booking-successful')]
    #[On('booking-failed')]
    #[On('time-or-date-no-longer-valid')]
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

    public function canBook(): bool
    {
        return ! filled($this->selectedDate) || ! filled($this->selectedTime);
    }

    public function retrieveBookingInfo(): void
    {
        $savedCalendarDate = Session::pull('appointment_calendar_date');
        if ($savedCalendarDate) {
            $this->date = CarbonImmutable::parse($savedCalendarDate);
        } else {
            $this->date = CarbonImmutable::now()->startOfMonth();
        }

        $this->schedule = $this->scheduleService->getActiveSchedule();

        $this->refreshCalendar();

        $savedDate = Session::pull('appointment_selected_date');

        if (filled($savedDate) && $this->availableDates->has($savedDate)) {
            $this->selectedDate = $savedDate;
            $this->slots = $this->availableDates->get($savedDate, collect());
            $this->showTimeSlots = $this->slots->isNotEmpty();

            $savedTime = Session::pull('appointment_selected_time');

            if (filled($savedTime) && $this->slots->contains($savedTime)) {
                $this->selectedTime = $savedTime;
            } else {
                $this->dispatch('time-or-date-no-longer-valid', 'Selected booking slot is no longer available. Please choose different appointment time.');
            }
        }
    }

    public function setTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function bookAppointment(): void
    {
        $date = $this->selectedDate;
        $time = $this->selectedTime;

        if (empty($date) || empty($time)) {
            $this->dispatch('booking-failed', 'You need to select date and time to book a slot');

            return;
        }

        if (auth()->guest()) {
            $this->dispatch('authentication-required', message: 'Please log in to book an appointment.');

            Session::put('appointment_calendar_date', $this->date->format('Y-m-d'));
            Session::put('appointment_selected_date', $this->selectedDate);
            Session::put('appointment_selected_time', $this->selectedTime);

            $this->redirect('/login');

            return;
        }

        if (is_null($this->schedule)) {
            $this->dispatch('booking-failed', message: 'No active schedule available.');

            return;
        }

        $available = $this->scheduleService->isAppointmentAvailable($date, $time, $this->schedule->getKey());

        if (! $available) {
            $this->dispatch('booking-failed', 'Selected booking slot is no longer available. Please choose different appointment time.');
            $this->refreshCalendar();

            if ($this->showTimeSlots) {
                $this->slots = $this->availableDates->get($date) ?? collect();
            }

            return;
        }

        try {
            $booking = $this->scheduleService
                ->bookAppointment(
                    $date,
                    $time,
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
            $this->dispatch('booking-failed', message: 'Could not make a booking. Please try again.');
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
