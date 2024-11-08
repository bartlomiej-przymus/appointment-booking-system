<?php

namespace App\Livewire;

use Livewire\Component;

class Day extends Component
{
    public $weekend = false;

    public $withinMonth = true;

    public $today;

    public $available = false;

    public function mount($day)
    {
        dd($day);
        $this->today = $day['today'];
        $this->weekend = $day['weekend'];
        $this->withinMonth = $day['withinMonth'];
    }

    public function render()
    {
        return view('livewire.day');
    }
}
