<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['interest'] = [
    // 365 por defecto; puedes cambiar a 360 si el cliente lo pide
    'day_count' => 365,
    // redondeo diario o al final del mes (usa 'per_day' o 'end_of_month')
    'rounding'  => 'per_day',
];
