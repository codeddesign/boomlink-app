<?php
//Main config start


$host ="95.85.63.246";
$database_name = "site_analysis";
$database_user = "root";
$database_password = "c0deddes1gn";

//Main config End















$con = mysql_connect($host ,$database_user ,$database_password);
if (!$con)
{
    die('Could not connect: ' . mysql_error());
}
mysql_select_db($database_name, $con);
?>
