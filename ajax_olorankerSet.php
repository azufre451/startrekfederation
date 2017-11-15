<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
		$person= (htmlentities(addslashes(($_POST['person'])),ENT_COMPAT, 'UTF-8'));	
		$whatwrite= ($_POST['whatassign'] == 'norank') ? '' : (htmlentities(addslashes(($_POST['whatassign'])),ENT_COMPAT, 'UTF-8'));	
		
		//$user = new PG($_SESSION['pgID']);
		//$userN = $user->pgUser;
		if(PG::mapPermissions('O',PG::getOMA($_SESSION['pgID']))) 
		mysql_query("UPDATE pg_users SET pgMostrinaOlo = (SELECT ordinaryUniform FROM pg_ranks WHERE prio = '$whatwrite') WHERE pgID = $person");
		


?>						