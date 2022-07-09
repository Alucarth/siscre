<?php

function ktranslate($keyword, $field = '', $use_default = '')
{
    $ci = &get_instance();
    
    if ( $field != '' )
    {
        $field = $ci->lang->line($field);
        $keyword = $ci->lang->line($keyword);
        
        $ret = str_replace("{field}", $field, $keyword);
    }
    
    $ret = $ci->lang->line($keyword);
    
    if ( $use_default != '' && $ret == '' )
    {
        // Create/append the word to 
        $filename = APPPATH . '/language/en/autogen_lang.php';
        
        if ( file_exists($filename) )
        {
            $content = '$lang["'.$keyword.'"] = "' . $use_default . '";' . "\r\n";
            file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
        }
        else
        {
            $content = '<?php ' . "\r\n";
            $content .= '$lang["'.$keyword.'"] = "' . $use_default . '";' . "\r\n";
            file_put_contents($filename, $content);
        }
        
        $ret = $use_default;
    }
    
    return $ret;
}

function ktranslate2($words = '', $field = '')
{
    $ci = &get_instance();
    
    $keyword = strip_tags($words);
    $keyword = preg_replace('/[^A-Za-z0-9. -]/', '', $keyword);
    $keyword = preg_replace('/\\s+/', '_', $keyword);
    $keyword = str_replace(".","_",$keyword);
    $keyword = "_" . strtolower(str_replace("-","",$keyword));
    
    if ( $field != '' )
    {
        $field = $ci->lang->line($field);
        $keyword = $ci->lang->line($keyword);
        
        $ret = str_replace("{field}", $field, $keyword);
    }
    
    $ret = $ci->lang->line($keyword);
    
    if ( $ret == '' )
    {
        // Create/append the word to 
        $filename = APPPATH . '/language/en/autogen_lang.php';
        
        if ( file_exists($filename) )
        {
            $content = '$lang["'.$keyword.'"] = "' . $words . '";' . "\r\n";
            file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
        }
        else
        {
            $content = '<?php ' . "\r\n";
            $content .= '$lang["'.$keyword.'"] = "' . $words . '";' . "\r\n";
            file_put_contents($filename, $content);
        }
        
        $ret = $words;
    }
    
    return $ret;
}