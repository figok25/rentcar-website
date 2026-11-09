<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun admin')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Kata sandi')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Kosongkan jika tidak ingin mengganti kata sandi.'
                                : null),
                        Toggle::make('is_active')
                            ->label('Akun aktif')
                            ->helperText('Admin nonaktif tidak bisa masuk ke panel.')
                            ->default(true)
                            // Admin tidak boleh menonaktifkan akunnya sendiri.
                            ->disabled(fn (?Model $record): bool => $record?->getKey() === auth()->id())
                            ->dehydrated(),
                    ]),
            ]);
    }
}
