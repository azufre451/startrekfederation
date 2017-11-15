<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		
		$vali = new validator();  
		$user = $vali->numberOnly($_SESSION['pgID']);

		if($_GET['A'] == 'a') mysql_query("DELETE FROM federation_chat WHERE type ='SPECIFIC' AND sender = $user");
		else if($_GET['A'] == 'b') mysql_query("DELETE FROM fed_pad WHERE paddTo = $user AND paddTitle LIKE '::special::%'");
		echo mysql_error();
		

?>						