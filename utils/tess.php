<?php
 
session_start();
include('includes/app_include.php');
include('includes/validate_class.php');

$res = mysql_query("SELECT * FROM pg_medals ORDER BY medPrio");
while ($resA = mysql_fetch_assoc($res))
{
	$n = $resA['medName'];
	$des = $resA['medDescript'];
	$ima = $resA['medImage'];
	
	echo "<div style='border:1px dashed #614901; margin-top:15px; width:85%; padding:5px; margin:auto; color:white;'><p class='line1'><img src='TEMPLATES/img/ruolini/medaglie/$ima' style='vertical-align:middle;' /> <span style='font-weight:bold; font-size:1.1em; margin-left:5px;'>$n</span></p><p style='margin-left:75px;'>$des</p></div>"; 
} 
exit;

mysql_query("UPDATE pg_users SET pgPoints = (SELECT SUM(points) FROM pg_users_pointStory WHERE owner = pgID) WHERE png=0;");
echo mysql_error();
echo "OK";
exit;


$tla = array();
$res = mysql_query("SELECT * FROM federation_sessions WHERE 1");
while ($resA = mysql_fetch_assoc($res))
{
	// sessione
	$ambient = $resA['sessionPlace'];
	$ini = $resA['sessionStart'];
	$end = $resA['sessionEnd'];
	$label = $resA['sessionLabel'];
	$master = $resA['sessionMaster'];
	$owner = $resA['sessionOwner'];
		
	echo "<b><center>$label</center></b><br /><hr/>";
	
	$acts = mysql_query("SELECT * FROM federation_chat WHERE ambient = '$ambient' AND time BETWEEN $ini AND $end AND type = 'DIRECT'");
	$allacts = array();
	while ($actss = mysql_fetch_assoc($acts))
	{
		$allacts[] = $actss;
	}
	
	$resAVG=mysql_query("SELECT AVG(realLen) AS averageLen FROM federation_chat WHERE ambient = '$ambient' AND time BETWEEN $ini AND $end AND type = 'DIRECT'"); 
	$resAVGL=mysql_fetch_assoc($resAVG); 
	$avig = $resAVGL['averageLen'];
	if($avig < 800) $avig = 800;
 
 
$person = array(); 
foreach($allacts as $var)
{
	$ppl = (string)$var['sender'];
	
	if(!array_key_exists($ppl,$person))	$person[$ppl] = array();
	
	$person[$ppl][] = $var['realLen'];
}

$pointarray=array();


foreach($person as $playerID => $player)
{
	foreach ($player as $action)
	{
		if(!array_key_exists($playerID,$pointarray))	$pointarray[$playerID] = 0.0;
		if($action > 500)
		{
			if($action >= 500 && $action <= $avig*1.3)
			{
				$pointarray[$playerID] += $action / (($avig*1.3) - 500) - (500 / (($avig*1.3) - 500));
			}
			else if ($action > $avig*1.3) { $pointarray[$playerID]+=1;}
		}
	}
}

if($master){
	
	$totalPta=0;
	$master_is_player=-1;
	
	
	$owner = (string)($owner);
	
	if(!array_key_exists($owner,$pointarray)){$pointarray[$owner] = 0.0; $master_is_player=-1;}
	
	
	foreach($pointarray as $personPoints){$totalPta += $personPoints;}
	
	$avgpointarray = (float)$totalPta / count($pointarray);
	$coeff = (count($pointarray)+$master_is_player) * 10;
	$totalPta = $avgpointarray + ($avgpointarray * ($coeff / 100));
	
	$avgpointarray_s = round($avgpointarray,2);
	$coeff_s = (string)(count($pointarray)+$master_is_player).' (+'.$coeff." %)";
	$totalPta_s = round($totalPta,2);
	 
	
	$tpl_s = round($pointarray[$owner],2);
	
	$pointarray[$owner] += $totalPta;
	
	$endpointarray = round($pointarray[$owner]);
	 
	
	$master_expl = addslashes("Per la sessione appena conclusa sono stati assegnati i seguenti punti per le attività di gioco e mastering:
	
	> Media dei punti assegnati in giocata: $avgpointarray_s FP / giocatore,
	> Giocatori coinvolti (oltre a te): $coeff_s
	> Punti per le azioni on-game: $tpl_s FP
	> Totale Bonus Mastering: $totalPta_s FP
	> ---------------------------------------
	> Totale FP assegnati: $endpointarray FP
	> (i punti sono arrotondati all'intero più vicino)"); 
	
	$sl = $owner; 
	$al = new PG($sl);
	
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES (518,$sl,'RIEPILOGO PUNTI SESSIONE','<p style=\"font-size:13px; margin-left:15px; margin-right:15px;\">$master_expl</p><p style=\" color:#AAA; font-style:italic;\">Questo è un messaggio automatico.</p>',$curTime,0)");
	echo "<hr/>### MASTER (".$owner.", ".$al->pgUser.") ###<br/>".$master_expl."<br />###<br />";
	
	echo mysql_error(); 
	
}



foreach($pointarray as $playerID => $playerResult)
{
	$pta = round($playerResult);
	$sessionLabel = addslashes($label);
	
	if ($pta > 0){
		$pgg = new PG($playerID);  
		if($pgg->pgUser != '')
		{
		$pgg->addPoints($pta,'QS',"Punti per sessione di gioco $sessionLabel","Punti per sessione di gioco: $sessionLabel");
		echo ">> Assegno $pta punti a ".$pgg->pgUser."<br />";
	
		if(!array_key_exists($pgg->pgUser,$tla)) $tla[$pgg->pgUser] = 0;
		$tla[$pgg->pgUser] += $pta ;
		
		} 
		unset($pgg);	
	}
}


 
	
}

var_dump($tla);

?>