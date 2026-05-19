<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")->required(),
            TextInput::make("email")->email()->required()->unique(ignoreRecord: true),
            Select::make("role")
                ->options([
                    "administrator" => "Administrator",
                    "petugas" => "Petugas",
                ])
                ->default("petugas")
                ->required(),
            TextInput::make("password")
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === "create")
                ->rule(Password::default())
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state)),
        ]);
    }
}
