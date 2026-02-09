<?php

function dep_download()
{
    // Download dependencies first
    $url = "https://softreliance.com/dependencies_pro.zip";
    $path = dirname( dirname(__FILE__) );
    
    $newfname = $path . "/" . basename($url);
    $file = fopen($url, 'rb');
    if ($file)
    {
        $newf = fopen($newfname, 'wb');
        if ($newf)
        {
            while (!feof($file))
            {
                fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
            }
        }
    }
    else
    {
        $return["status"] = "FAILED";
        $return["message"] = "unable to download";
        echo json_encode($return);
        exit;
    }
    
    if ($file)
    {
        fclose($file);
    }
    if ($newf)
    {
        fclose($newf);
    }
    
    $return["status"] = "OK";
    echo json_encode($return);
    exit;
}

dep_download();