<?php
session_start();
error_reporting(E_ALL);
ini_set('error_display',1);
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("../includes/PHPTAL/PHPTAL.php"); 



$term = addslashes($_POST['stringer']);
$format = $_POST['format'];
$rapType = $_POST['rapType'];

$sel = explode(',',$term);
$lisOuser="";
foreach ($sel as $auth)
{
	if($auth!='') $lisOuser .= "'".trim($auth)."',";
} 
$lisOuser = substr(trim($lisOuser),0,-1);



$colr = array('Comando e Navigazione' => 'RED', 'Tattica e Sicurezza' => 'YELLOW', 'Ingegneria' => 'YELLOW', 'Scientifica' => 'GREEN', 'Medica' => 'GREEN', 'Medicina Civile' => 'GREEN', 'Scienze' => 'GREEN', 'Comando' => 'RED', 'Ricerca e Terapia' => 'WHITE', 'Navigazione' => 'BLUE', 'Tecnica' => 'GREEN', 'Strategica' => 'YELLOW');
$actDate=timeHandler::timestampToGiulian($curTime);
$curUser = new PG($_SESSION['pgID']);
$pgSezione = $curUser->pgSezione;
$curLocation = $curUser->getLocationOfUser();

$curLocationName=$curLocation['placeName'];
$curLocationNameU=strtoupper($curLocationName);

$mySectionColor = (array_key_exists($pgSezione,$colr)) ?  $colr[$pgSezione] : 'GRAY';

$usersString="";

if($lisOuser != '')
{
	$res = mysql_query("SELECT UCASE(pgUser) as pgUser, UCASE(pgNomeC) as pgNomeC, UCASE(pgNomeSuff) as pgNomeSuff, pgSezione,pgIncarico,pgGrado,ordinaryUniform,placeName,rankerprio FROM pg_users,pg_places,pg_ranks WHERE prio = rankCode AND pgAssign = placeID AND pgUser IN ($lisOuser) ORDER BY rankerprio DESC"); 
	if(!mysql_error()){
	while($resA = mysql_fetch_array($res))
	{
		$pgUser = $resA['pgUser'];
		$pgNomeC = $resA['pgNomeC'];
		$pgNomeSuff = $resA['pgNomeSuff'];
		$pgIncarico = explode('<br />',$resA['pgIncarico']);
		$pgIncarico = $pgIncarico[0];
		$pgGrado = strtoupper($resA['pgGrado']);
		$placeName = $resA['placeName'];
		$pgListSezione = $resA['pgSezione'];
		$pgListColor = (array_key_exists($pgListSezione,$colr)) ?  $colr[$pgListSezione] : 'GRAY'; 
		 
		$pgMostrina = $resA['ordinaryUniform'].'.png';
		if ($format == 1) $usersString .= "[IMG]TEMPLATES/img/ranks/".$pgMostrina."[/IMG] [SIZE=1][B][COLOR=".$pgListColor."]".$pgGrado." ".$pgUser.", ".$pgNomeC." ".$pgNomeSuff."[/COLOR][/B] - ".$pgIncarico." [COLOR=GRAY]".$placeName."[/COLOR][/SIZE]\n";
		
		elseif ($format == 2) $usersString .= "[IMG]TEMPLATES/img/ranks/".$pgMostrina."[/IMG] [SIZE=1][B]".$pgGrado." ".$pgUser.", ".$pgNomeC."[/B] ".$pgNomeSuff." [COLOR=".$pgListColor."]>[/COLOR] [COLOR=GRAY]".$pgIncarico."[/COLOR][/SIZE]\n";
		
		elseif ($format == 3) $usersString .= "[IMG]TEMPLATES/img/ranks/".$pgMostrina."[/IMG] [SIZE=1][COLOR=".$pgListColor."][B]".$pgGrado." ".$pgUser.", ".$pgNomeC." ".$pgNomeSuff."[/B][/COLOR][/SIZE]\n";
		
		elseif ($format == 4) $usersString .= "[IMG]TEMPLATES/img/ranks/".$pgMostrina."[/IMG] [SIZE=1][COLOR=".$pgListColor."][B]".$pgGrado." ".$pgUser."[/B][/COLOR][/SIZE]\n";

		elseif ($format == 5) $usersString .= "[IMG]TEMPLATES/img/ranks/".$pgMostrina."[/IMG] [SIZE=1][B][COLOR=".$pgListColor."]".$pgUser."[/COLOR][/B][/SIZE]\n";
		}
	}
}
 
	
	if($usersString=="")
	{
		$usersString = "[SIZE=1][COLOR=GRAY]TUTTO IL PERSONALE[/COLOR][/SIZE]";
	}
	
	if ($rapType=="00")
		$outString=$usersString;

	if ($rapType=="Q1") 
	{ 
	
$outString="[CENTER][COLOR=YELLOW][SIZE=1]-- $curLocationNameU --[/SIZE][/COLOR][/CENTER]

[COLOR=YELLOW][B]Data:[/B][/COLOR] $actDate
[COLOR=YELLOW][B]Data Stellare:[/B][/COLOR] $currentStarDate
[COLOR=YELLOW][B]Luogo:[/B][/COLOR] $curLocationName
[COLOR=YELLOW][B]Stesore Rapporto:[/B][/COLOR] [SIZE=1][B][COLOR=".$mySectionColor."]".strtoupper($curUser->pgGrado)." ".strtoupper($curUser->pgUser).", ".strtoupper($curUser->pgNomeC)." ".strtoupper($curUser->pgNomeSuff)."[/COLOR][/B][/SIZE]

[COLOR=YELLOW][B]Presenti:[/B][/COLOR]

$usersString

[COLOR=YELLOW][B]Eventi[/B][/COLOR]

Rapporto";
}
	
elseif ($rapType=="Q2"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE COMANDO E NAVIGAZIONE $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/starfleet.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO __Ufficio__ -[/SIZE][/CENTER][/COLOR]


[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q2r1" || $rapType=="Q2r2" || $rapType=="Q2r3" || $rapType=="Q2r4" || $rapType=="Q2r5")
{

	if($rapType=="Q2r1"){ $office = 'COMANDO'; $logol = 'romulan_com_logo.png';}
	if($rapType=="Q2r2"){ $office = 'NAVIGAZIONE'; $logol = 'romulan_nav_logo.png';}
	if($rapType=="Q2r3"){ $office = 'STRATEGICA'; $logol = 'romulan_tat_logo.png';}
	if($rapType=="Q2r4"){ $office = 'TECNICA'; $logol = 'romulan_tec_logo.png';}
	if($rapType=="Q2r5"){ $office = 'RICERCA E TERAPIA'; $logol = 'romulan_ric_logo.png';}
	$logoI = '[IMG]http://miki.startrekfederation.it/SigmaSys/logo/'.$logol.'[/IMG]';
	
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE $office $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".($curLocation['placeLogo'])."[/IMG] $logoI [/CENTER] 

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO __Ufficio__ -[/SIZE][/CENTER][/COLOR]
 
[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q16"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE COMANDO E NAVIGAZIONE $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/starfleet.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL CAPITANO -[/SIZE][/CENTER][/COLOR]


[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q16r"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE COMANDO $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/little_".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/romulan_com_logo.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL RIOV -[/SIZE][/CENTER][/COLOR]


[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q16b"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE COMANDO E NAVIGAZIONE $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/starfleet.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL PRIMO UFFICIALE -[/SIZE][/CENTER][/COLOR]


[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q16br"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE COMANDO $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/little_".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/romulan_com_logo.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL PRIMO UFFICIALE -[/SIZE][/CENTER][/COLOR]


[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q3"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE COMANDO E NAVIGAZIONE $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/comm.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- DIPARTIMENTO COMUNICAZIONI -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q4"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE TATTICA E SICUREZZA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_tattica.png[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/security.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO __Ufficio__ -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
} 

elseif ($rapType=="Q5"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE TATTICA E SICUREZZA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_tattica.png[/IMG] [/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- DIPARTIMENTO TATTICO -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q15"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE TATTICA E SICUREZZA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_tattica.png[/IMG] [/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL CTO -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q6"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE TATTICA E SICUREZZA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/security.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- DIPARTIMENTO SICUREZZA -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q7"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE INGEGNERIA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_ops.png[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG] [IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_eng.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO: __Ufficio__ -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q8"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE INGEGNERIA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_ops.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- DIPARTIMENTO OPERAZIONI -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q14"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE INGEGNERIA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/logo_eng.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL CAPO INGEGNERE -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q9"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE SCIENTIFICA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/scientific.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO __Ufficio__ -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q13"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE SCIENTIFICA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/scientific.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO CSO -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q10"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE MEDICA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/medical.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO __Ufficio__ -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q17"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE MEDICA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/medical.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL CMO -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q18"){
$outString = "[CENTER][COLOR=YELLOW][SIZE=1]- SEZIONE MEDICA $curLocationNameU -[/SIZE][/COLOR][/CENTER]

[CENTER][IMG]http://miki.startrekfederation.it/SigmaSys/logo/".$curLocation['placeLogo']."[/IMG]  [IMG]http://miki.startrekfederation.it/SigmaSys/logo/medical.png[/IMG][/CENTER]

[COLOR=BLUE][CENTER][SIZE=1]- UFFICIO DEL CONSIGLIERE DI BORDO -[/SIZE][/CENTER][/COLOR]

[SIZE=1][COLOR=BLUE]ALL'ATTENZIONE DI:[/COLOR][/SIZE]
$usersString

[SIZE=1][COLOR=RED]OGGETTO: [/COLOR] [U] __Oggetto__ [/U][/SIZE]

<blockquote> __Disposizione__ </blockquote>";
}

elseif ($rapType=="Q11"){
$outString = "<blockquote><blockquote>
[B][COLOR=BLUE]DA:[/COLOR] __Partenza__
[COLOR=BLUE]A:[/COLOR] __Arrivo__ [/B]

[COLOR=YELLOW]Vascello:[/COLOR] [COLOR=BLUE][SIZE=1] $curLocationNameU [/SIZE][/COLOR]
[COLOR=YELLOW]Piano di Volo:[/COLOR] Rotta diretta per la destinazione

[COLOR=YELLOW][B]STD[/B][/COLOR] - [COLOR=GRAY][I]Scheduled time of Departure[/I][/COLOR]: __Data_e_Ora_Previsti_della_partenza__
[COLOR=RED][B]ATD[/B][/COLOR] - [COLOR=GRAY][I]Actual time of Departure[/I][/COLOR]: __Data_e_Ora_Effettivi_della_partenza__
[COLOR=BLUE][B]DLA[/B][/COLOR] - [COLOR=GRAY][I]Delay[/I][/COLOR]: __Ritardo__

[COLOR=YELLOW][B]ETA[/B][/COLOR] - [COLOR=GRAY][I]Scheduled time of Arrival[/I][/COLOR]: __Data_e_Ora_Previsti_per_arrivo__
[COLOR=YELLOW][B]EFT[/B][/COLOR] - [COLOR=GRAY][I]Estimated Flight Time[/I][/COLOR]: __Tempo_di_volo__
</blockquote></blockquote>";
}
 
elseif ($rapType=="Q12"){
$outString = "<blockquote><blockquote>
[B][COLOR=BLUE]DA:[/COLOR] __Partenza__  
[COLOR=BLUE]A:[/COLOR] __Arrivo__ [/B]

[COLOR=YELLOW]Vascello:[/COLOR] [COLOR=BLUE][SIZE=1] $curLocationNameU [/SIZE][/COLOR]
[COLOR=YELLOW]Piano di Volo:[/COLOR] Attracco
 
[COLOR=YELLOW][B]STA[/B][/COLOR] - [COLOR=GRAY][I]Scheduled time of Arrival[/I][/COLOR]: __Data_e_Ora_Prevista_arrivo__
[COLOR=RED][B]ATA[/B][/COLOR] - [COLOR=GRAY][I]Actual time of Arrival[/I][/COLOR]: __Data_e_Ora_Effettivi_arrivo__
[COLOR=BLUE][B]DLA[/B][/COLOR] - [COLOR=GRAY][I]Delay[/I][/COLOR]: __Ritardo__
</blockquote></blockquote>";
}


echo json_encode($outString);

?>