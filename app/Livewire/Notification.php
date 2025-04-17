<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Notification extends Component
{
    public $message = '';

    public $type = 'info';

    public $show = false;

    public $timeout = 3000;

    #[On('booking-failed')]
    public function showError($message): void
    {
        $this->message = $message;
        $this->type = 'error';
        $this->show = true;

        $this->js("setTimeout(() => { \$wire.set('show', false) }, {$this->timeout})");
    }

    #[On('booking-successful')]
    public function showSuccess($message): void
    {
        $this->message = $message;
        $this->type = 'success';
        $this->show = true;

        $this->js("setTimeout(() => { \$wire.set('show', false) }, {$this->timeout})");
    }

    #[On('time-or-date-invalid')]
    #[On('slot-unavailable')]
    public function showWarning($message): void
    {
        $this->message = $message;
        $this->type = 'warning';
        $this->show = true;

        $this->js("setTimeout(() => { \$wire.set('show', false) }, {$this->timeout})");
    }

    public function dismiss(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.notification');
    }
}
