<?php
exit;
chdir('../');
session_start();
include('includes/app_include.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 


$u=mysql_query("SELECT * FROM pg_abilita_levels,pg_abilita WHERE pg_abilita.abID = pg_abilita_levels.abID AND pg_abilita_levels.abID IN (5,6,20,56)");
echo mysql_error(); 
while ($e=mysql_fetch_assoc($u)){
	$user = new PG($e['pgID']);
	$ab=new abilDescriptor(5);


	$reimburse= $ab->calculateVariationCost(array(array( $e['abID'], $e['value']))); 
	echo $user->ID.' '.$user->pgUser . ' ' . $e['abName'] . ' diff' . $e['abDiff']. ' is at: '. $e['value']. ':: +' . $reimburse .' FP <br />' ;

	mysql_query("UPDATE pg_users SET pgUpgradePoints = pgUpgradePoints+$reimburse WHERE pgID = ".$user->ID);
	mysql_query("DELETE FROM pg_abilita_levels WHERE abID = ".$e['abID']." AND pgID = ".$user->ID);
	$ssTring="Rimborso FP abilità ".$e['abName'];
	$subtext="Ti sono stati rimborsati ".$reimburse.' FP';
	
 	$user->sendNotification($ssTring,$subtext,'1','https://miki.stfederation.it/SigmaSys/personal/obrind/Ostevik/e15.png','schedaOpen');

}




