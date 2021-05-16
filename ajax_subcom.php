<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		$string= (htmlentities(stf_real_escape(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));
		if ($string == '' || $string == '+' || string == '-' || string == '@' || string == '#') exit;
	
		$ambientFrom= stf_real_escape($_POST['amb']);
		$to= stf_real_escape($_POST['to']);
			
		$sor = mysql_query("SELECT placeName FROM pg_places WHERE placeID = (SELECT ambientLocation FROM fed_ambient WHERE locID = '$ambientFrom')");
		$sorE = mysql_fetch_array($sor);
		$placeFrom = strtoupper($sorE['placeName']);
		
		$sor = mysql_query("SELECT placeName FROM pg_places WHERE placeID = '$to'");
		$sorE = mysql_fetch_array($sor);
		$placeTo = strtoupper($sorE['placeName']);
		
		$sor = mysql_query("SELECT placePlancia FROM pg_places WHERE placeID = '$to'");
		$sorE = mysql_fetch_array($sor);
		$ambientTo = $sorE['placePlancia'];
		
		
		$string = '<p class="subspaceCom">'.date('H:i')." <span class=\'subspaceComPre\'>$placeFrom a $placeTo</span> - $string</p>";
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientTo','$string',".time().",'MASTER')");
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientFrom','$string',".time().",'MASTER')");
		

?>						