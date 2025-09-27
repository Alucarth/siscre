<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <title>Install | K-Loans</title>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.form.min.js"></script>
        <script src="js/main.js"></script>
        <link rel="stylesheet" rev="stylesheet" href="css/install.css?v=<?=time();?>" />
    </head>
    <body>
        
        <?php $db_config_path = dirname( dirname(__FILE__) ) . '/application/config/database.php'; ?>

        <center><h1>K-Loans - Install</h1></center>
        <?php if (is_writable($db_config_path)): ?>

            <p class="error" style="display:none;"></p>

            <form id="install_form" method="post" action="process.php">
                <fieldset>
                    <legend>Database settings</legend>                    
                    <label for="hostname">Hostname <span style="font-size: 10px;">(Include port number if you are not using the default port 3306)</span></label><input type="text" id="hostname" value="localhost" class="input_text" name="hostname" />
                    <label for="username">Username</label><input type="text" id="username" class="input_text" name="username" />
                    <label for="password">Password</label><input type="password" id="password" class="input_text" name="password" />
                    <label for="database">Database Name</label><input type="text" id="database" class="input_text" name="database" />
                    <input type="submit" value="Install" id="submit" />
                </fieldset>
            </form>
            
            <div id="div_install_dependencies">
                <h3>This may take some time, Please wait...</h3>
                <p id="loading">
                    Downloading Dependencies...
                </p>
            </div>

        <?php else: ?>
            <p class="error">Please make the application/config/database.php file writable. <strong>Example</strong>:<br /><br /><code>chmod 777 application/config/database.php</code></p>
        <?php endif; ?>

    </body>
</html>
