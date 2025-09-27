<?php

function dep_extract()
{
    $path = dirname(dirname(__FILE__));

    $newfname = $path . "/dependencies_pro.zip";

    $zip = new ZipArchive;
    $res = $zip->open($newfname);
    if ($res === TRUE)
    {
        $zip->extractTo($path);
        $zip->close();
    }
    else
    {
        $return["status"] = "FAILED";
        $return["message"] = "Unable to extract dependencies.zip file!";
        echo json_encode($return);
        exit;
    }

    $return["status"] = "OK";
    echo json_encode($return);
    exit;
}

dep_extract();
