<?php
class Pdf {

    public function __construct()
    {
        $CI = & get_instance();
        log_message('debug', 'mPDF wrapper (Pdf) loaded');
    }

    public function load($params = NULL)
    {
        // Carga el mPDF legacy
        include_once APPPATH . 'third_party/mpdf/mpdf.php';

        // Soporta string viejo y/o array nuevo
        if ($params === NULL) {
            // valores por defecto “seguros” (A5 vertical, márgenes pequeños)
            $params = ['en-GB-x','A5','','',8,8,8,8,0,0,'P'];
        } elseif (is_string($params)) {
            $tmp = explode(',', $params);
            $params = [];
            foreach ($tmp as $row) {
                $params[] = trim(str_replace('"','', $row));
            }
        }

        // Asegura 11 parámetros
        $params = array_pad($params, 11, '');
        return new mPDF(
            $params[0], $params[1], $params[2], $params[3],
            $params[4], $params[5], $params[6], $params[7],
            $params[8], $params[9], $params[10]
        );
    }
}

