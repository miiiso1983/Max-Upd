<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class CarbonServiceProvider extends ServiceProvider
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
        // Force Carbon to use Gregorian calendar
        Carbon::setLocale('en');
        
        // Set default format for dates
        Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('Y-m-d H:i:s');
        });
        
        // Configure Carbon to use Gregorian calendar globally
        $this->configureGregorianCalendar();
    }

    /**
     * Configure Carbon to use Gregorian calendar
     */
    private function configureGregorianCalendar(): void
    {
        // Set default timezone
        Carbon::setTestNow(null);
        
        // Ensure all Carbon instances use Gregorian calendar
        Carbon::macro('toGregorianDateString', function () {
            return $this->format('Y-m-d');
        });
        
        Carbon::macro('toGregorianDateTimeString', function () {
            return $this->format('Y-m-d H:i:s');
        });
        
        Carbon::macro('toArabicDateString', function () {
            $months = [
                1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
            ];
            
            return $this->day . ' ' . $months[$this->month] . ' ' . $this->year;
        });
        
        Carbon::macro('toArabicDateTimeString', function () {
            return $this->toArabicDateString() . ' - ' . $this->format('H:i');
        });
    }
}
