<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_prepend_string',"<pre>")  ;
ini_set('error_append_string',"</pre>")  ;

spl_autoload_register(function ($class_name) {
    include_once sprintf("classes/%s.php", $class_name);
});
$db = new MySQLCon("/config/database.php");
$discordAPI = new discordAPI("/config/discord.php", $db);




?>