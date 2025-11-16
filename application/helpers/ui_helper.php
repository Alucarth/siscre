<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('person_photo_url')) {
  /**
   * Retorna una URL segura para la foto de persona.
   * - Si no existe o el nombre viene mal, retorna el placeholder.
   * - Normaliza extensión y mayúsculas/minúsculas.
   * - URL-encodea el nombre (espacios, tildes, etc.).
   */
  function person_photo_url($filename, $fallback = 'uploads/people/placeholder-80x80.png') {
    $filename = trim((string)$filename);
    $baseDir  = rtrim(FCPATH, '/').'/uploads/people/';
    $baseUrl  = rtrim(base_url(), '/').'/uploads/people/';

    // Sin nombre -> placeholder
    if ($filename === '') {
      return base_url($fallback);
    }

    // 1) Intento directo (tal cual)
    $direct = $baseDir.$filename;
    if (is_file($direct)) {
      return $baseUrl.rawurlencode(basename($filename));
    }

    // 2) Prueba variantes de extensión y case (foo.jpg/jpeg/png en may/min)
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $candidates = glob($baseDir.$stem.'.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    if (!empty($candidates)) {
      return $baseUrl.rawurlencode(basename($candidates[0]));
    }

    // 3) Por si hay espacios dobles/trim extraño, compacta espacios
    $compact = preg_replace('/\s+/', ' ', $filename);
    if ($compact !== $filename && is_file($baseDir.$compact)) {
      return $baseUrl.rawurlencode(basename($compact));
    }

    // 4) Nada funcionó -> placeholder
    return base_url($fallback);
  }
}
