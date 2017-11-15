<?php
include('includes/app_include.php');

$date = time();
$dateL = $date-3600;
$dateLE = $date-2678400;

$oneMonth = $date - 2592000;
$twoMonth = $date - 2592000 -2592000;
$threeMonth = $date - 2592000 - 2592000 -2592000;
$twoWeeks = $date - 1209600;
$oneWeek = $date - 604800;

$for1 = $date+604800;
$for2 = $date+604800+604800;

echo "Time: $date <br /> T1: $for1 <br /> T2: $for2";
 
mysql_query("DELETE FROM federation_chat WHERE time < $twoMonth");
echo mysql_error();
echo "cancellate ".mysql_affected_rows()." righe di chat<br />";

mysql_query("DELETE FROM fed_sussurri WHERE time < $oneMonth");
echo mysql_error();
echo "cancellate ".mysql_affected_rows()." righe di sussurri<br />";
mysql_query("DELETE FROM fed_pad WHERE paddTime < $threeMonth");
echo mysql_error();
echo "cancellati ".mysql_affected_rows()." padd<br />";


$res= mysql_query("SELECT * FROM `pg_users` WHERE pgLastAct < $threeMonth AND pgAuthOMA <> 'BAN'  AND png = 0");
echo "SELECT * FROM `pg_users` WHERE pgLastAct < $threeMonth AND pgLastAct <> 0";
$timeString =substr(time(),4,4).'_';
$list ="";
$aff=0;
while($ra = mysql_fetch_array($res))
{
	$to=$ra['pgID'];
	mysql_query("DELETE FROM pg_alloggi WHERE pgID = $to"); // Cancello l'alloggio associato
	mysql_query("DELETE FROM pgDotazioni WHERE pgID = $to");
	mysql_query("DELETE FROM pg_notes WHERE owner = $to");
	mysql_query("DELETE FROM pgMedica WHERE pgID = $to");
	mysql_query("DELETE FROM fed_pad WHERE paddFrom = $to");
	mysql_query("DELETE FROM pg_user_stories WHERE pgID = $to");
	mysql_query("DELETE FROM pg_brevetti_assign WHERE pgID = $to");
	mysql_query("DELETE FROM pg_brevetti_levels WHERE pgID = $to");
	mysql_query("DELETE FROM pg_service_stories WHERE owner = $to");
	mysql_query("UPDATE cdb_topics SET topicCat = 47 WHERE topicID IN (SELECT pgMedica FROM pg_users WHERE pgID = $to)");
	mysql_query("UPDATE pg_users SET pgOffAvatarN='',pgOffAvatarC='',pgUser = CONCAT('$timeString',pgUser), pgLocation = 'BAVO',pgAssign='BAVO',pgMedica=0, email = CONCAT('$timeString',email), pgRoom ='BAVO', pgAuthOMA='BAN' WHERE pgID = $to");
	$aff++;
	$list.=" ".$ra['pgUser'];
}
echo "cancellati ".$aff." pg <b>$list</b><br />";
$list ="";

$res= mysql_query("SELECT * FROM `pg_users` WHERE pgLastAct < $twoMonth AND pgAssign <> 'BAVO' AND pgAuthOMA <> 'BAN' AND png = 0");
echo "SELECT * FROM `pg_users` WHERE pgLastAct < $twoMonth";
$list ="";

while($ra = mysql_fetch_array($res))
{
	$to=$ra['pgID'];
	mysql_query("UPDATE pg_users SET pgLocation = 'BAVO',pgAssign='BAVO', pgRoom ='BAVO', pgAuthOMA='N' WHERE pgID = $to");
	mysql_query("DELETE FROM pg_alloggi WHERE pgID = $to");
	
	$pg = new PG($to);
	$pgName = $pg->pgUser;
	$mail = PG::getSomething($to,'email');
	
	mail($mail,"Star Trek Federation - Ti abbiamo perso di vista!","Ciao $pgName,\n\nSono passati due mesi dal tuo ultimo login in Star Trek: Federation. Per garantire uno sviluppo funzionale degli organigrammi di bordo, il tuo personaggio verrà spostato in altra locazione a partire da oggi. Ci auguriamo di rivederti presto fra noi, e ti assicuriamo che, in caso volessi tornare, il tuo PG sarà mantenuto attivo per altri 30 giorni. Al termine dei 30 giorni, il PG sarà eliminato dai nostri server.\n\n A presto\n\nIl team di Star Trek: Federation\n\nhttp://www.startrekfederation.it","From:staff@startrekfederation.it");
	
	echo "Mailed for Bavosization: $mail<br />";
	
	$aff++;
	$list.=" ".$ra['pgUser'];
}
echo "bavosizzati ".$aff." pg <b>$list</b><br />";
$list ="";

mysql_query("UPDATE pg_users SET pgPoints = (SELECT SUM(points) FROM pg_users_pointStory WHERE owner = pgID)");

echo "Dovrebbero ricevere il +1<br /><hr/>";
$res = mysql_query("SELECT pgUser, COUNT(*) as L FROM pg_users,cdb_posts WHERE (pgID = owner OR pgID = coOwner) AND pgID NOT IN (SELECT owner FROM pg_achievement_assign WHERE achi = 27)  GROUP BY pgUser HAVING COUNT(*) >= 100 ORDER BY L DESC");
while($rea = mysql_fetch_assoc($res))
echo $rea['pgUser']." - ".$rea['L']."<br />";

echo "Dovrebbero ricevere il 'zuppa pomodoro'<br /><hr/>";
$res = mysql_query("SELECT pgUser, COUNT(*) as L FROM pg_users,fed_food WHERE pgID = presenter AND active = 1 AND pgID NOT IN (SELECT owner FROM pg_achievement_assign WHERE achi = 51)  GROUP BY pgUser HAVING COUNT(*) >= 25 ORDER BY L DESC");
while($rea = mysql_fetch_assoc($res))
echo $rea['pgUser']." - ".$rea['L']."<br />";

echo "Dovrebbero ricevere il 'O soave fanciulla'<br /><hr/>";
$res = mysql_query("SELECT pgUser FROM pg_users,`pg_brevetti_levels`,pg_brevetti_sectors WHERE sector = sectID AND noLevels = 1 AND value = 15 AND pg_brevetti_levels.pgID = pg_users.pgID AND pg_users.pgID NOT IN (SELECT owner FROM pg_achievement_assign WHERE achi = 50)");
while($rea = mysql_fetch_assoc($res))
echo $rea['pgUser']."<br />";

echo "Dovrebbero ricevere l\'achi ruolino'<br /><hr/>";
$res = mysql_query("SELECT pgUser, COUNT(*) as L FROM pg_users,pg_user_stories WHERE pg_users.pgID = pg_user_stories.pgID AND pg_users.pgID AND png= 0 AND pgAuthOMA <> 'BAN' AND pg_users.pgID NOT IN (SELECT owner FROM pg_achievement_assign WHERE achi = 44) AND pgLastAct > $twoMonth GROUP BY pgUser HAVING COUNT(*) >= 5 ORDER BY L DESC");
while($rea = mysql_fetch_assoc($res))
echo $rea['pgUser']." - ".$rea['L']." storie di ruolino <br />";

mysql_query("UPDATE pg_users SET actionCSS='',parlatCSS='', otherCSS=''  WHERE actionCSS='12;#3188f3;#999999' AND parlatCSS='13;#eeeeee;#d7a436' AND otherCSS='13;15;12;#999999;#e8a30e;#ffefcc;11;#d7a436'");

?>