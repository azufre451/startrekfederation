<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
		$vali = new validator();
		
		$pres=  $vali->numberOnly($_POST['prest']);
		
		mysql_query("UPDATE pg_users SET audioEnvEnable = $pres WHERE pgID = ".$_SESSION['pgID']);


?>						