<?php

$dbuser = 'Mac';
$dbpass = 'root';
$dbhost = 'localhost';
$dbname='srinagarhc';

try {
    $connection = new PDO("pgsql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
?>