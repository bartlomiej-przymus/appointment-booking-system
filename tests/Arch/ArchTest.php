<?php

arch('app architecture')
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump']);
