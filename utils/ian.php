<?php
chdir('/home2/xstfedee/public_html/');
include('includes/app_include.php');

mysql_query( "DELETE FROM pg_users_presence WHERE 1");
$res=mysql_query("SELECT pgID FROM pg_users WHERE pgAuthOMA IN ('G','M','SM','A') AND png =0 AND pgID <> 5");
while($resA = mysql_fetch_assoc($res))
{
	mysql_query("INSERT INTO pg_users_presence(pgID,day,value) VALUES(".$resA['pgID'].",1,0)");

	$tp = new PG($resA['pgID']);
	$tp->sendNotification("Presenza Staff","Il multitool ti ricorda di aggiornare la scheda presenza staff!",1,'TEMPLATES/img/interface/index/blevinrevin_02.png','masterShadow');
}
@Database::tdbClose(); 
exit; 

?>

