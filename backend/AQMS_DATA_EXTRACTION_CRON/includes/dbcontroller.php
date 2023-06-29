<?php
$DB_host = "localhost";
$DB_user = "newuser";
$DB_pass = "password";
$DB_name = " production_monitoring";
try
{
 $DB_con = new PDO("mysql:host={$DB_host};dbname={$DB_name}",$DB_user,$DB_pass);
 $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
 $e->getMessage();
}
?>