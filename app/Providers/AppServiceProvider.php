<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Carbon to use Gregorian calendar
        \Carbon\Carbon::setLocale('en');

        // Add custom Carbon macros for Arabic date formatting
        \Carbon\Carbon::macro('toArabicDateString', function () {
            /** @var \Carbon\Carbon $this */
            $months = [
                1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
            ];

            return $this->day . ' ' . $months[$this->month] . ' ' . $this->year;
        });

        \Carbon\Carbon::macro('toArabicDateTimeString', function () {
            /** @var \Carbon\Carbon $this */
            return $this->toArabicDateString() . ' - ' . $this->format('H:i');
        });


    }
}
