<?php
include('includes/conf.php');
include('includes/databaseClass.php');

ini_set("display_errors", 1);
setlocale(LC_ALL, 'it_IT');
date_default_timezone_set('Europe/Rome');
//echo date("d-m-y h:i:s") . " -- " . time();
//
//echo date_default_timezone_get();

error_reporting(E_ALL ^ E_DEPRECATED);
  
Database::tdbConnect($db_Host,$db_User, $db_Pass,$db_Name);
Database::query('SET NAMES utf8');
//$formats = array(".jpg",".png",".gif",".bmp");
//$baseSite = "http://genesis.digital-destiny.org/SigmaSys/";

$gameName = "v. 1.8.6 - Triumphal Zebrafish";
$gameVersion = "1.8.6 - Triumphal Zebrafish";
//1.8 Rampant Unicorn

$gameOptions = array('numericVersion' => "1.8.65");

$debug=true;
$tips=true;

$thisYear = 2020;
$bounceYear = 379;


$gameServiceInfo = 0;

$curTime = time();		
$lapConstant = ((date("Y",$curTime) % 4) == 0) ? 0.00003162315 : 0.00003170979; 
//$currentDate = date("d/m",$curTime)."/".(date("Y",$curTime)+368);
$currentDate = utf8_encode(ucfirst(strftime('%A %e %B %Y',mktime(date("H"),date("i"),date("s"),date("n",$curTime),date("j",$curTime),($bounceYear+date("Y",$curTime))))));	

//<span class="calendarActDate" tal:content="php:utf8_encode(ucfirst(strftime('%A %e %B %Y',eventE[0]['Tevent']['date'])))" /> 
$currentStarDate = number_format((((date("Y",$curTime)+$bounceYear)-2323) + (date("z",$curTime)/365.2425))*1000 + ((date("H",$curTime)*3600 + date("i",$curTime)*60 + date("s",$curTime)) * $lapConstant),2,".","");

?>