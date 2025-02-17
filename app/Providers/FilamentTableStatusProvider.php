<?php

namespace App\Providers;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;

class FilamentTableStatusProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        TextColumn::configureUsing(function (TextColumn $column) {
            if ($column->getName() === 'status') {
                $column
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Confirmed', 'Paid' => 'success',
                        'Canceled',  'Failed' => 'danger',
                        default => 'gray',
                    });
            }
            if ($column->getName() === 'role') {
                $column
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'primary',
                        'customer' => 'gray',
                    });
            }
        });
    }
}
