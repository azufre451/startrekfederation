<?php
chdir('../');
include('includes/app_include.php');
 
 
$date = time();
$dateL = $date-3600;
$dateLE = $date-2678400;


$oneMonth = $date - (30*24*60*60);
$twoMonth = $date - (2*30*24*60*60);

$eightMonth = $date - (8*30*24*60*60);

$threeMonth = $date - (3*30*24*60*60);
$thrsixhours = $date - (36*60*60);

$twentyfourhours = $date - (24*60*60);

$ru=mysql_query("SELECT pgID FROM pg_users WHERE pgLastAct > $threeMonth AND pgPoints > 10 AND pgAuthOMA <> 'BAN' AND png = 0");
while($ra = mysql_fetch_assoc($ru))
{
	$PP = new PG($ra['pgID']);
	echo $PP->pgUser . '<br />';
	
	$PP->addPoints(24,'CHR18','Bonus Natale 2018','Bonus Natale 2018');
} 

@Database::tdbClose(); 
exit; 
 

?> 