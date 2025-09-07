<?php

error_reporting(0); //Setting this to E_ALL showed that that cause of not redirecting were few blank lines added in some php files.
//
// Only load the classes in case the user submitted the form
if ($_POST)
{

    // Load the classes and create the new objects
    require_once('includes/core_class.php');
    require_once('includes/database_class.php');

    $core = new Core();
    $database = new Database();
    
    // Validate the post data
    if ($core->validate_post($_POST) == true)
    {
        // First create the database, then create tables, then write config file
        if ($database->create_database($_POST) == false)
        {
            $message = $core->show_message('error', "The database could not be created, please verify your settings.");
        }
        else if ($database->create_tables($_POST) == false)
        {
            $message = $core->show_message('error', "The database tables could not be created, please verify your settings.");
        }
        else if ($core->write_config($_POST) == false)
        {
            $message = $core->show_message('error', "The database configuration file could not be written, please chmod application/config/database.php file to 777");
        }

        // If no errors, redirect to registration page
        if (!isset($message))
        {
            $redir = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
            $redir .= "://" . $_SERVER['HTTP_HOST'];
            $redir .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
            $redir = str_replace('install/', '', $redir);

            $return["href"] = $redir . 'index.php/welcome';
            $return["status"] = "OK";
            echo json_encode($return);
            exit;
        }
        else
        {
            $return["message"] = $message;
            $return["status"] = "FAILED";
            echo json_encode($return);
            exit;
        }
    }
    else
    {
        $message = $core->show_message('error', 'Not all fields have been filled in correctly. The host, username, password, and database name are required.');

        $return["message"] = $message;
        $return["status"] = "FAILED";
        echo json_encode($return);
        exit;
    }
}