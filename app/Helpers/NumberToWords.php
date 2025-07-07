<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        '', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة',
        'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر',
        'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'
    ];

    private static $tens = [
        '', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'
    ];

    private static $hundreds = [
        '', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'
    ];

    private static $scales = [
        '', 'ألف', 'مليون', 'مليار', 'تريليون'
    ];

    public static function convert($number)
    {
        if ($number == 0) {
            return 'صفر';
        }

        if ($number < 0) {
            return 'سالب ' . self::convert(abs($number));
        }

        // Handle decimal part
        $parts = explode('.', number_format($number, 2, '.', ''));
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) $parts[1] : 0;

        $result = self::convertInteger($integerPart);

        if ($decimalPart > 0) {
            $result .= ' و ' . self::convertInteger($decimalPart);
            if ($decimalPart == 1) {
                $result .= ' فلس';
            } else {
                $result .= ' فلساً';
            }
        }

        return $result;
    }

    private static function convertInteger($number)
    {
        if ($number == 0) {
            return '';
        }

        if ($number < 20) {
            return self::$ones[$number];
        }

        if ($number < 100) {
            $tens = intval($number / 10);
            $ones = $number % 10;
            $result = self::$tens[$tens];
            if ($ones > 0) {
                $result .= ' و ' . self::$ones[$ones];
            }
            return $result;
        }

        if ($number < 1000) {
            $hundreds = intval($number / 100);
            $remainder = $number % 100;
            $result = self::$hundreds[$hundreds];
            if ($remainder > 0) {
                $result .= ' و ' . self::convertInteger($remainder);
            }
            return $result;
        }

        // Handle thousands, millions, etc.
        $scaleIndex = 0;
        $result = '';
        
        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk > 0) {
                $chunkText = self::convertInteger($chunk);
                if ($scaleIndex > 0) {
                    $chunkText .= ' ' . self::$scales[$scaleIndex];
                }
                if ($result) {
                    $result = $chunkText . ' و ' . $result;
                } else {
                    $result = $chunkText;
                }
            }
            $number = intval($number / 1000);
            $scaleIndex++;
        }

        return $result;
    }
}
