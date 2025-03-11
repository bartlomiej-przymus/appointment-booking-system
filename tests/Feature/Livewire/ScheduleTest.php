<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Schedule;
use Livewire;

it('can render schedule', function () {
    Livewire::test(Schedule::class)
        ->assertStatus(200);
});
