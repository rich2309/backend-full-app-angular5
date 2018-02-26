<?php
$servername = "localhost";
$bdd = "curso_angular4";
$username = "admin_default";
$password = "harpuchas_23";

try {
    $db_connection = new PDO("mysql:host=$servername;dbname=$bdd", $username, $password,array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
?>