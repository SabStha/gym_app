<?php

if (!function_exists('number_format_short')) {
    /**
     * Format a number to a short string (e.g., 1k, 1.5M).
     *
     * @param float|int $n
     * @return string
     */
    function number_format_short($n) {
        if ($n >= 1000) {
            return round(($n / 1000), 1) . 'k';
        }
        return (string) $n;
    }
}
