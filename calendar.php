<?php 
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 

function toStardate($curTime)
{
$lapConstant = ((date("Y",$curTime) % 4) == 0) ? 0.00003162315 : 0.00003170979; 
return number_format((((date("Y",$curTime)+368)-2323) + (date("z",$curTime)/365.2425))*1000 + ((date("H",$curTime)*3600 + date("i",$curTime)*60 + date("s",$curTime)) * $lapConstant),2,".","");
}


PG::updatePresence($_SESSION['pgID']);

$currentUser = new PG($_SESSION['pgID']);

$vali = new validator();

if(isSet($_POST['event']))
{
	$event = (htmlentities(addslashes(($_POST['event'])),ENT_COMPAT, 'UTF-8'));
	$preDate = explode("/",$_POST['calDate']);
	$preDateM = explode(":",$_POST['calDateM']);
	$date = mktime($vali->numberOnly($preDateM[0]),$vali->numberOnly($preDateM[1]),0,$vali->numberOnly($preDate[1]),$vali->numberOnly($preDate[0]),$vali->numberOnly($preDate[2]));
	$place = (htmlentities(addslashes(($_POST['place'])),ENT_COMPAT, 'UTF-8'));
	$user = $_SESSION['pgID'];
	
	$res=mysql_query("SELECT MAX(evID) as Max FROM calendar_events");
	if(mysql_affected_rows()) $rae = mysql_fetch_array($res);
	$ider=$rae['Max']+1;
	
	mysql_query("INSERT INTO calendar_events(evID,event,date,sender,place) VALUES($ider,'$event',$date,$user,'$place')");
	
	
	if(isSet($_POST['cate']) && mysql_affected_rows())
	foreach($_POST['cate'] as $val)
		mysql_query("INSERT INTO calendar_labels_assign(labelCode,eventID) VALUES ('$val',$ider)");
	
	
	
	header("Location:calendar.php");exit;
}

else if(isSet($_GET['removeEvent']))
{
	if(PG::mapPermissions("SM",$currentUser->pgAuthOMA)) $check='';
	else $check = "AND sender = ".$_SESSION['pgID'];
	
	$rea = $vali->numberOnly($_GET['removeEvent']);
	
	mysql_query("DELETE FROM calendar_events WHERE evID = $rea $check");

	if(mysql_affected_rows()) mysql_query("DELETE FROM calendar_labels_assign WHERE eventID = $rea");
	
	header("Location:calendar.php");exit;
	
}

else
{

	$template = new PHPTAL('TEMPLATES/cdb_calendar.htm');

	$fr = (isSet($_GET['backwards'])) ? ($curTime - 864000) : mktime(0,0,0,date('n'),date('j'),date('Y'));
	$to = (isSet($_GET['backwards'])) ? ($fr + 864000 + 864000) : $fr+864000;
	$res = mysql_query("SELECT evID,event,date,sender,place,pgUser,placeName  FROM calendar_events,pg_places,pg_users WHERE pgID = sender AND placeID = place AND date BETWEEN $fr AND $to ORDER BY date");
$calEvents=array();
	while($rea = mysql_fetch_array($res))
	{

	//$rea['dateF'] = date("d/m",$rea['date'])."/".(date("Y",$rea['date'])+368);
	$rea['dateF'] = utf8_encode(ucfirst(strftime('%A %e %B %Y',mktime(0,0,0,date("n",$rea['date']),date("j",$rea['date']),(368+date("Y",$rea['date']))))));	
	
	$date = date('z',$rea['date']);
	$rese = mysql_query("SELECT label,class,iorder FROM calendar_labels_assign,calendar_labels WHERE labelCode = label AND eventID = ".$rea['evID']." ORDER BY iorder ASC");

	$categories = array();
	while ($reseR = mysql_fetch_array($rese))
		$categories[] = $reseR;
		
	$calEvents[$date][] = array('Tevent' =>$rea,'cats' => $categories);
	//echo "<pre>".var_dump($categories)."</pre>";exit;
	}

	$rese = mysql_query("SELECT label FROM calendar_labels ORDER BY label ASC");
	$categoriesAvail=array();
	while ($rea=mysql_fetch_array($rese))
	$categoriesAvail[] = $rea;

	$rese = mysql_query("SELECT placeID,placeName FROM pg_places ORDER BY placeName ASC");
	$places=array();
	while ($rea=mysql_fetch_array($rese))
	$places[] = $rea;




	ksort($calEvents);
	$template->user = $currentUser;
	$template->calEvents = $calEvents;
	$template->places = $places;
	$template->categoriesAvail = $categoriesAvail;


	$template->SM = (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) ? 'yes' : 'no'; 
	$template->M = (PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? 'yes' : 'no'; 
	
	$template->currentDate = $currentDate;
	$template->currentStarDate = $currentStarDate;
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
}	
include('includes/app_declude.php');

?>