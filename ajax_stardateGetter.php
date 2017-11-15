<?php
session_start();
error_reporting(E_ALL);
ini_set('error_display',1);
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php'); 

$curTime = mktime($_POST['hou'],0,$_POST['min'],$_POST['mon'],$_POST['day'],$_POST['yea']);		

$lapConstant = ((date("Y",$curTime) % 4) == 0) ? 0.00003162315 : 0.00003170979; 
//$currentDate = date("d/m",$curTime)."/".(date("Y",$curTime)+368);
$currentStarDate = number_format((((date("Y",$curTime))-2323) + (date("z",$curTime)/365.2425))*1000 + ((date("H",$curTime)*3600 + date("i",$curTime)*60 + date("s",$curTime)) * $lapConstant),2,".","");

 echo json_encode($currentStarDate);

?>