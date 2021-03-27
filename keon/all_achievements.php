<?php
chdir('../');
session_start();
include('includes/app_include.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 



$template = new PHPTAL('keon/htm/allachi.htm');

if(isSet($_SESSION['pgID']))
{
    
    PG::updatePresence($_SESSION['pgID']);
    $currentUser = new PG($_SESSION['pgID']);
    $template->user = $currentUser;
    $template->currentDate = $currentDate;
    $template->currentStarDate = $currentStarDate;
}

$template->gameOptions = $gameOptions;

$aa= mysql_query("SELECT * FROM pg_achievements ORDER BY aHidden, aID");



$allAchi = array();
while ($as = mysql_fetch_array($aa))
{
    if($as['aHidden']){
        $as['aImage'] = 'bloccato_n.png';
        $as['aText'] = '***Segreto***';}
    $allAchi[] = $as;
}

$template->allAchi = $allAchi;

try 
{
		echo $template->execute();
}
catch (Exception $e){
echo $e;
}

?>