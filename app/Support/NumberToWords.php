<?php

namespace App\Support;

class NumberToWords
{
    /**
     * Convert number to Indonesian words (Terbilang).
     */
    public static function convert($number): string
    {
        $number = abs((int)$number);
        $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $result = "";

        if ($number < 12) {
            $result = " " . $words[$number];
        } elseif ($number < 20) {
            $result = self::convert($number - 10) . " Belas";
        } elseif ($number < 100) {
            $result = self::convert((int)($number / 10)) . " Puluh" . self::convert($number % 10);
        } elseif ($number < 200) {
            $result = " Seratus" . self::convert($number - 100);
        } elseif ($number < 1000) {
            $result = self::convert((int)($number / 100)) . " Ratus" . self::convert($number % 100);
        } elseif ($number < 2000) {
            $result = " Seribu" . self::convert($number - 1000);
        } elseif ($number < 1000000) {
            $result = self::convert((int)($number / 1000)) . " Ribu" . self::convert($number % 1000);
        } elseif ($number < 1000000000) {
            $result = self::convert((int)($number / 1000000)) . " Juta" . self::convert($number % 1000000);
        } elseif ($number < 1000000000000) {
            $result = self::convert((int)($number / 1000000000)) . " Miliar" . self::convert($number % 1000000000);
        }

        return trim($result);
    }
}
