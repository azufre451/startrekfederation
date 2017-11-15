<?php
include('includes/conf.php');
include('includes/databaseClass.php');

ini_set("display_errors", 1);
setlocale(LC_ALL, 'it_IT');
error_reporting(E_ALL ^ E_DEPRECATED);

	Database::$db_Host = $db_Host;
	Database::$db_User = $db_User;
    Database::$db_Pass = $db_Pass;
    Database::$db_Name = $db_Name;
	
Database::tdbConnect();
mysql_query('SET NAMES utf8');
//$formats = array(".jpg",".png",".gif",".bmp");
//$baseSite = "http://genesis.digital-destiny.org/SigmaSys/";

$gameName = "v. 0.9.9 - Appetizing Amethyst";
$gameVersion = "0.9.9 - Appetizing Amethyst";
$debug=false;
$tips=true;


$gameServiceInfo = "
	Star Trek: Federation<br /> 
		Codename Appetizing Amethyst<br />
		BETA<br />";

$curTime = time();		
$lapConstant = ((date("Y",$curTime) % 4) == 0) ? 0.00003162315 : 0.00003170979; 
//$currentDate = date("d/m",$curTime)."/".(date("Y",$curTime)+368);
$currentDate = utf8_encode(ucfirst(strftime('%A %e %B %Y',mktime(date("H"),date("i"),date("s"),date("n",$curTime),date("j",$curTime),(368+date("Y",$curTime))))));	

//<span class="calendarActDate" tal:content="php:utf8_encode(ucfirst(strftime('%A %e %B %Y',eventE[0]['Tevent']['date'])))" /> 
$currentStarDate = number_format((((date("Y",$curTime)+368)-2323) + (date("z",$curTime)/365.2425))*1000 + ((date("H",$curTime)*3600 + date("i",$curTime)*60 + date("s",$curTime)) * $lapConstant),2,".","");

$thisYear = 2014;
$bounceYear = 368;

//$assignTOSHIP='USS2';

//57342.238375874 
$bbCode = array(
"[B]","[/B]",
"[I]","[/I]",
"[U]","[/U]",
"[CENTER]","[/CENTER]",
"[LEFT]","[/LEFT]",
"[RIGHT]","[/RIGHT]",
"[COLOR=RED]","[COLOR=BLUE]",
"[COLOR=YELLOW]","[COLOR=WHITE]",
"[COLOR=GREEN]","[COLOR=GRAY]",
"[SIZE=1]","[SIZE=2]",
"[SIZE=3]","[/SIZE]","[/COLOR]","\n","[IMG]","[/IMG]",'[URL]','[/URL]','<script','</script>','<adminOsteScript14215','</adminOsteScript14215>');


$htmlCode = array(
"<b>","</b>",
"<i>","</i>",
"<u>","</u>",
"<div style=\"text-align:center;\" align=\"center\">","</div>",
"<p style=\"text-align:left\">","</p>",
"<p style=\"text-align:right\">","</p>",
"<span class=\"cdbPostRed\">","<span class=\"cdbPostBlue\">",
"<span class=\"cdbPostYellow\">","<span class=\"cdbPostWhite\">",
"<span class=\"cdbPostGreen\">","<span class=\"cdbPostGray\">",
"<span class=\"cdbPostLittleSize\">","<span class=\"cdbPostNormalSize\">",
"<span class=\"cdbPostBigSize\">","</span>","</span>","<br />","<img src=\"","\"/>","<a target=\"_blank\" class=\"interfaceLink\" href=\"","\">LINK</a>",'script','script','<script','</script>');

function reduced_bbCode($str){ 

$bbCode = array("[I]","[/I]","[U]","[/U]","\n");
$htmlCode = array("<i>","</i>","<u>","</u>","<br />");

return str_replace($bbCode,$htmlCode,htmlspecialchars($str));
}

?>