<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Promo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                Select::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percentage' => 'Persentase (%)',
                        'fixed' => 'Jumlah Tetap (Rp)',
                    ])
                    ->required(),

                TextInput::make('discount')
                    ->label('Diskon')
                    ->numeric()
                    ->required(),

                DateTimePicker::make('valid_until')
                    ->label('Berlaku Hingga')
                    ->required(),

                TextInput::make('usage_limit')
                    ->label('Limit Penggunaan')
                    ->helperText('Kosongkan untuk penggunaan tanpa batas.')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
