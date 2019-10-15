<?php
chdir('../');
include('includes/app_include.php');

$to=addslashes($_GET['pgID']);



$res=mysql_query("INSERT INTO pg_extra_values (pgID,pg_extra_values.key,value) VALUES ('$to','Melial_Points',1000)");

exit; 

?>

