<?php
session_start();
chdir('../');
include('includes/app_include.php');
 
exit;

$date = time();
$dateL = $date-3600;
$dateLE = $date-2678400;


$oneMonth = $date - (30*24*60*60);
$twoMonth = $date - (2*30*24*60*60);

$eightMonth = $date - (8*30*24*60*60);

$threeMonth = $date - (3*30*24*60*60);
$sixMonth = $date - (6*30*24*60*60);
$twMonth = $date - (365*24*60*60);
$thrsixhours = $date - (36*60*60);

$twentyfourhours = $date - (24*60*60);

$ru=mysql_query("SELECT pgID,pgUser,pgPoints, SUM(points) AS smp FROM pg_users,pg_users_pointStory WHERE owner = pgID AND pgLastAct > $twMonth AND png = 0 AND pgPoints > 10 AND pgAuthOMA <> 'BAN' AND timer > $sixMonth AND png = 0 GROUP BY pgID,pgUser HAVING smp < 50"); 
//while($ra = mysql_fetch_assoc($ru)){ echo $ra['pgUser']." ".$ra['pgPoints']." ".$ra['smp']."<br />";}

while($ra = mysql_fetch_assoc($ru))
{
	$PP = new PG($ra['pgID']);
	echo $PP->pgUser . '<br />';
	
	//$PP->addPoints(24,'CHR19','Bonus Natale 2018','Bonus Natale 2018');
	$PP->sendPadd('Buone Feste',"
Caro giocatore,

a nome di tutto lo staff di Star Trek Federation, ti auguro Buone Feste! Ti ringraziamo del tuo contributo come giocatore attivo, consci del fatto che una land è nulla senza i propri giocatori. 

Buon cenone, buoni pranzi, buone giocate festive se ne farai in questi giorni!

Un abbraccio,
Moreno, Jean e tutto lo staff STF

",1,1,0);
//W	$PP->sendNotification("Bonus Natale","Buon Natale! Hai ricevuto 24 FP bonus",$_SESSION['pgID'],"nick/SigmaSys/personal/obrind/tmp/small_christmas_stamp.jpg",'schedaOpen');
} 

@Database::tdbClose(); 
exit; 
 

?> 