<?php
chdir('../');
session_start();
include('includes/app_include.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 



$template = new PHPTAL('keon/htm/allmedals.htm');

if(isSet($_SESSION['pgID']))
{
    
    PG::updatePresence($_SESSION['pgID']);
    $currentUser = new PG($_SESSION['pgID']);
    $template->user = $currentUser;
    $template->currentDate = $currentDate;
    $template->currentStarDate = $currentStarDate;
}

$template->gameOptions = $gameOptions;

$aa= mysql_query("SELECT * FROM pg_medals ORDER BY medPrio DESC");



$allAchi = array();
while ($as = mysql_fetch_array($aa))
{
    $allAchi[] = $as;
}

$template->allMedal = $allAchi;

try 
{
		echo $template->execute();
}
catch (Exception $e){
echo $e;
}

?>