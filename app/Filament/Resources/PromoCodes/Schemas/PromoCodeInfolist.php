<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PromoCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label('Kode Promo'),

                TextEntry::make('discount_type')
                    ->label('Tipe Diskon'),

                TextEntry::make('discount')
                    ->label('Diskon'),

                TextEntry::make('valid_until')
                    ->label('Berlaku Hingga')
                    ->dateTime(),

                TextEntry::make('usage_limit')
                    ->label('Limit Penggunaan')
                    ->formatStateUsing(fn ($state) => $state === null ? 'Unlimited' : $state),

                TextEntry::make('used_count')
                    ->label('Telah Digunakan'),

                TextEntry::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Aktif' : 'Tidak Aktif'),

                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime(),
            ]);
    }
}
