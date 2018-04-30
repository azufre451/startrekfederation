<?php
include('includes/app_include.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW

include('includes/bbcode.php');

$res = mysql_query("SELECT * FROM db_pathways WHERE pathID > 29 ORDER BY pathID");

$template = new PHPTAL('TEMPLATES/test2.htm');
$things= array();
while($rea = mysql_fetch_array($res))
{
$rea['text'] = str_replace($bbCode,$htmlCode,$rea['text']);
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