<?php
include('includes/app_include.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW

$res = mysql_query("SELECT * FROM db_pathways WHERE pathID > 29 ORDER BY identifier");

$template = new PHPTAL('TEMPLATES/test2.htm');
$things= array();
while($rea = mysql_fetch_array($res))
{
$things[] = $rea;
}

$template->things = $things;


	try 
	{
		echo $template->execute();
	}
	catch (Exception $e){
	echo $e;
	}
?>						