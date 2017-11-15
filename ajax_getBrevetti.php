<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
 		
		
	$term = addslashes($_GET['term']);
	$res = mysql_query("SELECT image,descript FROM pg_brevetti,pg_brevetti_sectors WHERE sector = sectID AND (descript LIKE '%$term%' OR sectName LIKE '%$term%')");   
	$arr = array();
	while($rea=mysql_fetch_array($res)) $arr[]=$rea['descript'];
	
	echo json_encode($arr);
?>						