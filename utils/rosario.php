<?php
chdir('/home/ND-47/public_html/');

include('includes/app_include.php');
 
 
$date = time();
$dateL = $date-3600;
$dateLE = $date-2678400;

$tenDays = $date - (10*24*60*60);

$oneMonth = $date - (30*24*60*60);
$twoMonth = $date - (2*30*24*60*60);
$sixMonth = $date - (6*30*24*60*60);

$eightMonth = $date - (8*30*24*60*60);

$threeMonth = $date - (3*30*24*60*60);
$thrsixhours = $date - (36*60*60);

$twentyfourhours = $date - (24*60*60);
//$ = $date - (24*60*60*20);
$twentydays = $date - (24*60*60*20);

$twoWeeks = $date - 1209600;
$oneWeek = $date - 604800;

$for1 = $date+604800;
$for2 = $date+604800+604800;


$SITA = "<p style=\"font-family:monospace\">-----------  Rapporto Pulizia  --------------<br />Esecuzione in data: ".date('d-m-y h:i:s',time()).'<br />-------------------------------------------<br /><br />';

//mysql_query("DELETE FROM federation_chat WHERE time < $eightMonth");

$SITA .= "> Cancello <span style=\"font-weight:bold;color:#096bd0\">".mysql_affected_rows()."</span> righe di chat più vecchie di OTTO mesi<br />";

mysql_query("DELETE FROM fed_sussurri WHERE time < $oneMonth AND susTo IN (0,6)");

$SITA .= "> Cancello <span style=\"font-weight:bold;color:#096bd0\">".mysql_affected_rows()."</span> sussurri pubblici più vecchi di UN mese<br />";

mysql_query("DELETE FROM fed_sussurri WHERE time < $twoMonth AND susTo NOT IN (0,6)");

$SITA .= "> Cancello <span style=\"font-weight:bold;color:#096bd0\">".mysql_affected_rows()."</span> sussurri privati più vecchi di DUE mesi<br />";


mysql_query("UPDATE pg_users SET pgDiario = 0 WHERE pgDiario IN (SELECT cdb_topics.topicID FROM cdb_topics WHERE cdb_topics.topicID NOT IN (SELECT cdb_posts.topicID FROM cdb_posts GROUP BY cdb_posts.topicID) AND SUBSTR(topicTitle,1,6) = 'DIARIO')");

if(mysql_affected_rows()) $SITA .= "> Elimino <span style=\"font-weight:bold;color:#096bd0\">".mysql_affected_rows()."</span> topic di diari creati e senza post<br />";

mysql_query("CREATE TABLE tmp2 AS 
SELECT cdb_topics.topicID FROM cdb_topics WHERE cdb_topics.topicID NOT IN (SELECT cdb_posts.topicID FROM cdb_posts GROUP BY cdb_posts.topicID) AND SUBSTR(topicTitle,1,6) = 'DIARIO';");
mysql_query("DELETE FROM cdb_topics WHERE topicID IN (SELECT topicID FROM tmp2);");

mysql_query("DROP TABLE tmp2;");

mysql_query("DELETE FROM fed_pad WHERE paddFrom = 518 AND paddTime < $oneMonth");

$SITA .= "> Cancello <span style=\"font-weight:bold;color:#096bd0\">".mysql_affected_rows()."</span> padd automatici più vecchi di UN mese<br />";


$res = mysql_query("SELECT federation_sessions.*,pgUser FROM federation_sessions,pg_users WHERE pgID = sessionOwner AND sessionStatus = 'ONGOING' AND sessionStart < $thrsixhours");

$SITALIST = "";
$tolist = 0; 

while($ra = mysql_fetch_array($res))
{
	$tolist+=1;
	mysql_query("UPDATE federation_sessions SET sessionStatus='CLOSED' WHERE sessionID = ".$ra['sessionID']);
	$pig = new PG($ra['sessionOwner']);
	$seTitle = addslashes($ra['sessionLabel']);
	$seNow = addslashes( ((time()-(int)($ra['sessionStart']))/3600) );
	$pig->sendPadd('OFF: Chiusura Sessione',"Una procedura automatica ha chiuso la tua sessione: $seTitle dopo <b>$seNow ore</b> di inattività. Non sono stati assegnati punti per questa sessione.");

	$SITALIST .= '<span style="font-weight:bold;" class="interfaceLink" href="#">'.$ra['sessionLabel'].'</span> ('.$ra['sessionPlace'].') di <a onclick="javascript:schedaPOpen('.$ra['sessionOwner'].');" style="font-weight:bold;" class="interfaceLink" href="#">'.$ra['pgUser'].'</a><br />';
}

if ($tolist)
	$SITA .= "> Ho chiuso <span style=\"font-weight:bold;color:#096bd0\">$tolist</span> sessioni aperte in decomposizione:<p style=\"margin:15px;\">$SITALIST<br />";


// BAVOSIZZAZIONI


$res= mysql_query("(SELECT pg_users.pgID FROM pg_users JOIN federation_chat ON sender = pgID JOIN pg_incarichi ON pg_incarichi.pgID = pg_users.pgID WHERE incHigh = 1 AND pgAuthOMA NOT IN ('BAN','A','M','G','SM') AND png = 0 AND pgBavo=0 GROUP BY pg_users.pgID HAVING MAX(time) < $oneMonth) UNION (SELECT pg_users.pgID FROM pg_users JOIN federation_chat ON sender = pgID JOIN pg_incarichi ON pg_incarichi.pgID = pg_users.pgID WHERE incHigh = 0 AND pgAuthOMA NOT IN ('BAN','A','M','G','SM') AND png = 0 AND pgBavo=0 GROUP BY pg_users.pgID HAVING MAX(time) < $threeMonth)");
$SITAbavo = "";
$tabavosize = 0; 

while($ra = mysql_fetch_array($res))
{
	$tbavo = new PG($ra['pgID']);
	$tbavo->bavosize();
	$tabavosize+=1;
	$SITAbavo .= '<a onclick="javascript:schedaPOpen('.$tbavo->ID.');" style="font-weight:bold;" class="interfaceLink" href="#">'.$tbavo->pgUser.'</a> (inattivo dal '.date('d-m-y',$tbavo->pgLastAct).')<br />';
}

if ($tabavosize)
	$SITA .= "> Bavosizzo <span style=\"font-weight:bold;color:#096bd0\">$tabavosize</span> PG inattivi (non giocanti in ON) da un po':<p style=\"margin:15px;\">$SITAbavo<br /><br />";




$res= mysql_query("SELECT pgID FROM pg_users, federation_chat WHERE sender = pgID AND pgAuthOMA <> 'BAN' AND png = 0 AND pgBavo=1 GROUP BY pgID HAVING MAX(time) < $sixMonth");
$SITAcanc = "";
$tadelete = 0; 

while($ra = mysql_fetch_array($res))
{
	$tdelete = new PG($ra['pgID']);
	$tdelete->delete();
	$tadelete+=1;
	$SITAcanc .= '<a onclick="javascript:schedaPOpen('.$tdelete->ID.');" style="font-weight:bold;" class="interfaceLink" href="#">'.$tdelete->pgUser.'</a> (inattivo dal '.date('d-m-y',$tdelete->pgLastAct).')<br />';
}

if ($tadelete)
	$SITA .= "> Cancello questi <span style=\"font-weight:bold;color:#096bd0\">$tadelete</span> PG inattivi e bavosizzati da più di SEI mesi:<p style=\"margin:15px;\">$SITAcanc<br />.";


$res= mysql_query("SELECT pgID FROM pg_users WHERE pgAuthOMA <> 'BAN' AND png = 0 AND pgLock=1 AND pgLastAct <= $oneWeek");
$SITAcanc = "";
$tedelete = 0; 

while($ra = mysql_fetch_array($res))
{
	$tdelete = new PG($ra['pgID']);
	$tdelete->delete();
	$tedelete+=1;
	$SITAcanc .= '<a onclick="javascript:schedaPOpen('.$tdelete->ID.');" style="font-weight:bold;" class="interfaceLink" href="#">'.$tdelete->pgUser.'</a> (inattivo dal '.date('d-m-y',$tdelete->pgLastAct).')<br />';
}

if ($tedelete)
	$SITA .= "> Cancello questi <span style=\"font-weight:bold;color:#096bd0\">$tedelete</span> PG iscritti bloccati da più di sette giorni:<p style=\"margin:15px;\">$SITAcanc<br />.";








$res= mysql_query("SELECT pgID FROM pg_users WHERE pgAuthOMA <> 'BAN' AND png = 0 AND pgPoints < 30 AND pgLastAct < $twentydays");
$INACTIVECANC = "";
$tadadelete = 0; 

while($ra = mysql_fetch_array($res))
{
	$inactive_delete = new PG($ra['pgID']);
	$inactive_delete->delete();
	$tadadelete+=1;
	$INACTIVECANC .= '<a onclick="javascript:schedaPOpen('.$inactive_delete->ID.');" style="font-weight:bold;" class="interfaceLink" href="#">'.$inactive_delete->pgUser.'</a> (inattivo dal '.date('d-m-y',$inactive_delete->pgLastAct).')<br />';
}

if ($tadadelete)
	$SITA .= "> Cancello questi <span style=\"font-weight:bold;color:#096bd0\">$tadelete</span> PG inattivi e che non hanno giocato negli ultimi VENTI giorni: <p style=\"margin:15px;\">$INACTIVECANC<br />.";


$res = mysql_query("SELECT pgID,pgUser, COUNT(*) as L FROM pg_users,cdb_posts WHERE (pgID = owner OR pgID = coOwner) AND pgID NOT IN (SELECT owner FROM pg_achievement_assign WHERE achi = 27) AND pgLastAct > $oneMonth AND pgBavo=0 GROUP BY pgUser HAVING COUNT(*) >= 100 ORDER BY L DESC");

$SITALIST = "";
$tolist = 0;  

$ra1 = mysql_query("SELECT * FROM federation_sessions WHERE sessionStart > $twentyfourhours");
$session24 = mysql_affected_rows();

$ra2a = mysql_fetch_assoc(mysql_query("SELECT AVG(realLen) as avigo,COUNT(IDE) as contigo,COUNT(DISTINCT sender) as PGcontigo FROM federation_chat WHERE time > $twentyfourhours AND type = 'DIRECT'"));
$average24 = round($ra2a['avigo'],2);
$count24 = $ra2a['contigo'];
$pg24 = $ra2a['PGcontigo'];

$ra2b = mysql_fetch_assoc(mysql_query("SELECT COUNT(IDE) as contigo FROM fed_sussurri WHERE time > $twentyfourhours"));
 
$whispercount24 = $ra2b['contigo']; 

$ra2c = mysql_fetch_assoc(mysql_query("SELECT COUNT(IDE) as contigo FROM fed_sussurri WHERE time > $twentyfourhours AND susTo NOT IN (1,7)"));
 
$whispercount24pvt = $ra2c['contigo']; 
 

$ra2 = mysql_query("SELECT * FROM federation_chat WHERE time > $twentyfourhours AND type = 'DIRECT'");
$ary = array();
while($act = mysql_fetch_assoc($ra2)){

	if(!array_key_exists($act['sender'],$ary))
		$ary[$act['sender']] = 0;

	$ary[$act['sender']] +=1; 
} 
arsort($ary);
$bestPG='';
$i=0;
foreach($ary as $pg => $rec)
	{
		$i+=1;
		$tla = new PG($pg);
		$bestPG.='<a onclick="javascript:schedaPOpen('.$tla->ID.');" style="font-weight:bold;" class="interfaceLink" href="#">'.$tla->pgUser.'</a> ('.$rec.' azioni)<br/>';
		if($i >= 5) break;
	}


$ra2 = mysql_query("SELECT * FROM federation_chat WHERE time > $twentyfourhours AND type = 'DIRECT'");
$ary = array();
while($act = mysql_fetch_assoc($ra2)){

	if(!array_key_exists($act['sender'],$ary))
		$ary[$act['sender']] = array();

	$ary[$act['sender']][] = (float)$act['realLen']; 
} 

foreach($ary as $pg => $rec)
	$ary[$pg] = round(array_sum($ary[$pg])/count($ary[$pg]),2);

arsort($ary);
$bestPGQ='';
$i=0;
foreach($ary as $pg => $rec)
	{
		$i+=1;
		$tla = new PG($pg);
		$bestPGQ.='<a onclick="javascript:schedaPOpen('.$tla->ID.');" style="font-weight:bold;" class="interfaceLink" href="#">'.$tla->pgUser.'</a> ('.$rec.' chr)<br/>';
		if($i >= 5) break;
	}


$VITA = "<p style=\"font-family:monospace\">-----------  Rapporto Procrastinante  --------------<br />Favellato in data: ".date('d-m-y h:i:s',time()).'<br />-------------------------------------------<br /><br />';
$VITA .= "<br/>Nelle passanti 24 cicli di accomandita del bestiame:<br/>";

$VITA .= "> Iniziassero <span style=\"font-weight:bold;color:#096bd0\">$session24</span> disamine di giuoco<br />";
$VITA .= "> Hanno favellato <span style=\"font-weight:bold;color:#096bd0\">$pg24</span> gentigiocanti<br />";
$VITA .= "> La prolissitudine dei prescenti ragguaglianti è di: <span style=\"font-weight:bold;color:#096bd0\">$average24</span><br />";
$VITA .= "> <span style=\"font-weight:bold;color:#096bd0\">$count24</span> panzanitudini sono state affidate all'archivio degli Antichi<br />";
$VITA .= "> <span style=\"font-weight:bold;color:#096bd0\">$whispercount24</span> panzane private sono state inviate<br />&nbsp;&nbsp;&nbsp;(di cui <span style=\"font-weight:bold;color:#096bd0\">$whispercount24pvt</span> Invacchiti tra vacca e vacca) <br />";
$VITA .= "> I 5 blablatori più elucubranti sono stati: <p style=\"margin:10px;\">$bestPG</p><br />";
$VITA .= "> I 5 blablatori più meritescenti: <p style=\"margin:10px;\">$bestPGQ</p>";

$VITA .= "<p>La sacralità del nostro antico Villaggio preconizza anche il nostro grande obiettivo<br />";
$VITA .= " Giocatore che simma, la chat non insozzare <br /> Trama che traballa, admin non giocare <br /> Banks che esulta, master non accordare <br /> Background che olezza, guida non approvare!</p>";

  

$moreno = new PG('1'); 
$jean = new PG('3'); 

$moreno->sendPadd('Rapporto pulizia',$SITA,702);
$moreno->sendPadd('Rapporto qualità',$VITA,1580);

$jean->sendPadd('Rapporto pulizia',$SITA,702);
$jean->sendPadd('Rapporto qualità',$VITA,1580);

}

@Database::tdbClose(); 
exit; 
 

?> 