<?php

namespace App\Filament\Resources\UserResource;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class UserForm
{
    public static function make(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->string()
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->required(),
                    ]),
            ]);
    }
}
