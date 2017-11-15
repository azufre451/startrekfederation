<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
 

if ($_GET['mod']=='gam') $template = new PHPTAL('TEMPLATES/index_landing_game.htm');
if ($_GET['mod']=='trk1') $template = new PHPTAL('TEMPLATES/index_landing1.html');
if ($_GET['mod']=='trk2') $template = new PHPTAL('TEMPLATES/index_landing2.html');

if ($_GET['mod']=='trk') $template = new PHPTAL('TEMPLATES/index_landing_trekker.htm');
  
$template->ida = $_GET['flavour'];
 
//echo "WALALO:".mktime(23,59,59,1,14,2013); 
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
?>