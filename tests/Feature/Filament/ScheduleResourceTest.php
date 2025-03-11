<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ScheduleResource;

it('can render page', function () {
    $this->get(ScheduleResource::getUrl())->assertSuccessful();
});
