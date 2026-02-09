<?php

if (!function_exists('money')) {
    function money($amount) {
        if ($amount === null || $amount === '') {
            return '';
        }
        
        return number_format(floatval($amount), 2, '.', ',');
    }
}