<?php
include('includes/conf.php');
include('includes/databaseClass.php');

ini_set("display_errors", 1);
setlocale(LC_ALL, 'it_IT');
date_default_timezone_set('Europe/Rome');

error_reporting(E_ALL ^ E_DEPRECATED);

Database::tdbConnect($db_Host,$db_User, $db_Pass,$db_Name);
Database::query('SET NAMES utf8');

$gameName = "v. 1.8.683 - Triumphal Zebrafish";
$gameVersion = "1.8.683 - Triumphal Zebrafish";

<<<<<<< HEAD
$gameOptions = array('numericVersion' => "1.8.69");
=======
$gameOptions = array('numericVersion' => "1.8.683");
>>>>>>> 42432be33886cb0062e7cdc68f0d5f25b3715381

$debug=true;
$tips=true;

$thisYear = 2020;
$bounceYear = 379;

$gameServiceInfo = 0;

$curTime = time();
$lapConstant = ((date("Y",$curTime) % 4) == 0) ? 0.00003162315 : 0.00003170979;
$currentDate = utf8_encode(ucfirst(strftime('%A %e %B %Y',mktime(date("H"),date("i"),date("s"),date("n",$curTime),date("j",$curTime),($bounceYear+date("Y",$curTime))))));
$currentStarDate = number_format((((date("Y",$curTime)+$bounceYear)-2323) + (date("z",$curTime)/365.2425))*1000 + ((date("H",$curTime)*3600 + date("i",$curTime)*60 + date("s",$curTime)) * $lapConstant),2,".","");

?>
