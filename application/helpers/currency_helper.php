<?php

/** GARRISON MODIFIED 4/20/2013 * */
function to_currency($number, $use_comma = false, $dec = 2)
{
    $CI = & get_instance();
    
    $dec_separator = '.';
    $thousand_separator = '';
    
    if (is_plugin_active('currency_formatter'))
    {
        $dec = $CI->config->item("currency_num_decimal") !== '' ? $CI->config->item("currency_num_decimal") : $dec;
        $dec_separator = $CI->config->item("currency_decimal_separator") !== '' ? $CI->config->item("currency_decimal_separator") : $dec_separator;
        $thousand_separator = $CI->config->item("currency_thousand_separator") !== '' ? $CI->config->item("currency_thousand_separator") : $thousand_separator;
    }
    
    if (!$use_comma)
    {
        $currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
        if ($number >= 0)
        {
            if ($CI->config->item('currency_side') !== 'currency_side')
                return $currency_symbol . number_format($number, $dec, $dec_separator, $thousand_separator);
            else
                return number_format($number, $dec, $dec_separator, $thousand_separator) . $currency_symbol;
        }
        else
        {
            if ($CI->config->item('currency_side') !== 'currency_side')
                return '-' . $currency_symbol . number_format(abs($number), $dec, $dec_separator, $thousand_separator);
            else
                return '-' . number_format(abs($number), $dec, $dec_separator, $thousand_separator) . $currency_symbol;
        }
    }
    else
    {
        $currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
        if ($number >= 0)
        {
            if ($CI->config->item('currency_side') !== 'currency_side')
                return $currency_symbol . number_format($number, $dec, $dec_separator, $thousand_separator);
            else
                return number_format($number, $dec, $dec_separator, $thousand_separator) . $currency_symbol;
        }
        else
        {
            if ($CI->config->item('currency_side') !== 'currency_side')
                return '-' . $currency_symbol . number_format(abs($number), $dec, $dec_separator, $thousand_separator);
            else
                return '-' . number_format(abs($number), $dec, $dec_separator, $thousand_separator) . $currency_symbol;
        }
    }
}

/** END MODIFIED * */
function to_currency_no_money($number)
{
    return number_format($number, 2, '.', '');
}

?>