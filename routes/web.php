<?php

use App\Livewire\Counter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

//Route::get('/counter', Counter::class);
