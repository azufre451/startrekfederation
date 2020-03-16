<?php
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php'); 
include("includes/PHPTAL/PHPTAL.php");



$what = isSet($_GET['what']) ? $_GET['what'] :'';

if($what == 'mainInterface')
{
	mysql_query("UPDATE pg_users SET pgFirst = 1 WHERE pgFirst <> 2 AND pgID = ".$_SESSION['pgID']);
	header('Location:main.php');
	exit;
} 

elseif($what == 'sch') $template = new PHPTAL('TEMPLATES/tutorial_scheda.htm');
elseif($what == 'cdb') $template = new PHPTAL('TEMPLATES/tutorial_cdb.htm');
elseif($what == 'dbb') $template = new PHPTAL('TEMPLATES/tutorial_db.htm');

else
{
$template = new PHPTAL('TEMPLATES/tutorial_panel.htm');
$template->user = new PG($_SESSION['pgID']);

$res = mysql_query("SELECT * FROM pg_users_tutorial WHERE pgID = ".$_SESSION['pgID']);
if(mysql_affected_rows())
{
	$resA = mysql_fetch_assoc($res);  
	$template->testInitial = ($resA['main']=='1') ? true : false;
	$template->testScheda = ($resA['scheda']=='1') ? true : false;
	$template->testCDB = ($resA['cdb']=='1') ? true : false;
	$template->testDB = ($resA['db']=='1') ? true : false;
}

else{
	$template->testInitial = false;
	$template->testScheda = false;
	$template->testCDB = false;
	$template->testDB = false;
}



}


$template->gameOptions = $gameOptions;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

?>