<?php
session_start();
if (!isSet($_SESSION['pgID'])) exit;

include('includes/validate_class.php');
include('includes/app_include.php');
if($_GET['action']=='deleteMany')
{
	$vali = new validator();
	foreach($_POST['padds'] as $ref)
	{
	$rife = $vali->numberOnly($ref);
	$result=mysql_query("SELECT paddFrom, paddTo FROM fed_pad WHERE padID= $rife");
	$resArr=mysql_fetch_array($result);
	if($resArr['paddTo'] == $_SESSION['pgID'])	mysql_query("UPDATE fed_pad SET paddDeletedTo= 1 WHERE padID = $rife");
	if($resArr['paddFrom'] == $_SESSION['pgID']) mysql_query("UPDATE fed_pad SET paddDeletedFrom= 1 WHERE padID = $rife"); 
	
	echo mysql_error();
	}
	if(!mysql_error())	echo json_encode(array('OK' => true));
}
?>