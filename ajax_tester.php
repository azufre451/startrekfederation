<?php
session_start();
//if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 //exit;
   $starttime = microtime();
 


include('includes/app_include.php');
echo "BANAN";
$mtime = microtime();
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   echo "This page was created in ".$totaltime." seconds"; 
?>