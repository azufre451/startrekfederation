<?php


function getmicrotime(){
list( $usec, $sec) = explode( " ", microtime());
return ( ( float)$usec + ( float)$sec);
} 

function cmp($a, $b) {


		if ( (preg_match('#^Capo Squad#i', $a['pgIncarico']) === 1) || (preg_match('#^Comandante#i', $a['pgIncarico']) === 1) || (preg_match('#^Responsabile#i', $a['pgIncarico']) === 1)) return -1;

		if ( (preg_match('#^Capo Squad#i', $b['pgIncarico']) === 1) || (preg_match('#^Comandante#i', $b['pgIncarico']) === 1) || (preg_match('#^Responsabile#i', $b['pgIncarico']) === 1)) return 1;

   		if ($a['rankerprio'] == $b['rankerprio']) {
   		 	if ($a['png'] != $b['png']) 

    			return ($a['png'] > $b['png']) ? 1 : -1;
    		else
        		return ($a['pgUser'] > $b['pgUser']) ? 1 : -1;
    	}
    	return ($a['rankerprio'] > $b['rankerprio']) ? -1 : 1;
		}

		function cmp2($a, $b) { 
   		 if ($a == $b) {
        	return 0;
    	}
    	
//    	if (strstr($a,'Comando')){ echo "KAVA ".$a.' '.$b.'$rw=1;}
//    	if (strstr($b,'Comando')){ echo "KAVA ".$a.' '.$b.'$rw=-1;}
//    	else $rw=($a > $b);
    	
    	if ( (preg_match('#comando#i', strtolower($a)) === 1)) $rw=-1;
    	elseif ( (preg_match('#comando#i', strtolower($b)) === 1)) $rw=1;
    	else $rw = (int)($a > $b);

    	return $rw;

		}

$start_time = getmicrotime();


$allSupportedSpecies=array('Andoriana','Bajoriana','Benzita','Betazoide','Boliana','Borg','Breen','Caitiana','Capellana','Cardassiana','Deltana','Denobulana','El-Auriana','Elaysiana','Ferengi','Fondatore','Gorn','Grazerita','Jem\'Hadar','Kelpiana','Klingon','Nausicaana','Ocampa','Orioniana','Risiana','Romulana','Sauriana','Sconosciuta','Talariana','Talassiana','Tellarita','Terosiana','Tholiana','Trill','Tzenkethi','Umana','Umana-Betazoide','Umana-Vulcaniana','Umana-Klingon','Vorta','Vulcaniana','Vulcaniana-Romulana','Xenita','Zakdorn','Zaldan');

session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/prestige.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
PG::updatePresence($_SESSION['pgID']);

$currentUser = new PG($_SESSION['pgID']);
$vali = new validator();


if(isSet($_GET['delAssign']))
{
	$equia = $vali->killchars($_GET['equia']);

	header('Location:crew.php?equi='.$equia);

}

else if (isSet($_GET['amendPNG']))
{
	$mode = $_GET['amendPNG'];
	$equi = $_GET['equia'];
	if(!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	if ($mode == 'add')
	{
		$pngIncarico = addslashes($_POST['pgIncarico']);
		$pngSezione = addslashes($_POST['pgSezione']);
		$pngDivisione = addslashes($_POST['pgDivisione']);
		$pngDipartimento = addslashes($_POST['pgDipartimento']);
		$pngIncGroup = addslashes($_POST['incGroup']);
		$pngPlace = addslashes($_POST['assegnazione']);
		$pngID = $vali->numberOnly($_POST['pngID']);

		$pre=mysql_fetch_assoc(mysql_query(("SELECT pngName,pngSurname,pngRank,pngSesso,pngSpecie FROM png_incarichi WHERE pngID = $pngID")));
		if(mysql_affected_rows())

			mysql_query("INSERT INTO png_incarichi (pngName,pngSurname,pngRank,pngIncarico,pngSezione,pngDivisione,pngDipartimento,pngIncGroup,pngPlace,pngSesso,pngSpecie) VALUES ('".addslashes($pre['pngName'])."','".addslashes($pre['pngSurname'])."',".addslashes($pre['pngRank']).",'$pngIncarico','$pngSezione','$pngDivisione','$pngDipartimento','$pngIncGroup','$pngPlace','".addslashes($pre['pngSesso'])."','".addslashes($pre['pngSpecie'])."') ");
		
		header('Location:crew.php?editAssign='.$pngID.'&equia='.$equi);
		exit;
	}
	if ($mode == 'edit')
	{
		if(!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
		$pngIncarico = addslashes($_POST['pgIncarico']);
		$pngSezione = addslashes($_POST['pgSezione']);
		$pngDivisione = addslashes($_POST['pgDivisione']);
		$pngDipartimento = addslashes($_POST['pgDipartimento']);
		$pngIncGroup = addslashes($_POST['incGroup']);
		$pngPlace = addslashes($_POST['assegnazione']);
		$pngID = $vali->numberOnly($_POST['pngID']);

		mysql_query("UPDATE png_incarichi SET pngIncarico = '$pngIncarico', pngSezione='$pngSezione', pngDivisione='$pngDivisione', pngDipartimento='$pngDipartimento',pngIncGroup='$pngIncGroup',pngPlace='$pngPlace' WHERE pngID = '$pngID'");
		header("Location:crew.php?editAssign=$pngID&equia=$equi");
		exit;
	}

	elseif ($mode == 'editPerson')
	{
		$pngSurname=addslashes($_POST['pgSurname']);
		$pngName=addslashes($_POST['pgName']);
		$pngSpecie=addslashes($_POST['pgSpecie']);
		$pngSesso=addslashes($_POST['pgSesso']);
		$rankCode=$vali->numberOnly($_POST['rankCode']);
		$pngID = $vali->numberOnly($_POST['pngID']);

		$pre=mysql_fetch_assoc(mysql_query("SELECT pngName,pngSurname FROM png_incarichi WHERE pngID = $pngID"));
		if(mysql_affected_rows())
		{
			mysql_query("UPDATE png_incarichi SET pngName = '$pngName', pngSurname='$pngSurname', pngSpecie='$pngSpecie', pngSesso='$pngSesso',pngRank='$rankCode' WHERE pngName = '".addslashes($pre['pngName'])."' AND pngSurname = '".addslashes($pre['pngSurname'])."'");
		}
		header("Location:crew.php?editAssign=$pngID&equia=$equi");
		exit;
	}

	elseif($mode == 'deleteIncarico')
	{
		$ida = $vali->numberOnly($_GET['pngID']);
		mysql_query("DELETE FROM png_incarichi WHERE pngID = $ida");
	}


	elseif($mode == 'deletePNG')
	{
		$ida = $vali->numberOnly($_GET['pngID']);
		$pre=mysql_fetch_assoc(mysql_query("SELECT pngName,pngSurname FROM png_incarichi WHERE pngID = $ida"));
		
		mysql_query("DELETE FROM png_incarichi WHERE pngName = '".addslashes($pre['pngName'])."' AND pngSurname = '".addslashes($pre['pngSurname'])."'");
	}

	elseif($mode == 'togglePriority')
	{
		$ida = $vali->numberOnly($_GET['pngID']);
				
		mysql_query("UPDATE png_incarichi SET prioritary = !prioritary WHERE pngID = '$ida'");
		header("Location:crew.php?editAssign=$ida&equia=$equi");
		exit;
		

	}
 
	header('Location:crew.php?equi='.$equi);
	exit;


} 
elseif(isSet($_GET['editAssign']))
{

	$template = new PHPTAL('TEMPLATES/cdb_organigramma_edit.htm');
	$equia = $vali->killchars($_GET['equia']);
	$ida = $vali->numberOnly($_GET['editAssign']);


	$crew = mysql_query("SELECT placeID, placeMotto, placeName, placeLogo, place_littleLogo1, placeClass, catGDB, catDISP, catRAP, pgGrado,pgUser,pgNomeC,ordinaryUniform FROM pg_places LEFT JOIN (pg_users JOIN pg_ranks ON rankCode = prio) ON pgID = placeCommander WHERE placeID = '$equia'");
	
		while($reissA = mysql_fetch_array($crew))
		$place = array('placeName' => $reissA['placeName'], 'placeMotto' => $reissA['placeMotto'], 'placeClass' => $reissA['placeClass'], 'placeLittle' => $reissA['place_littleLogo1'],'placeLogo' => $reissA['placeLogo'], 'placeID' => $reissA['placeID'], 'catRAP' => $reissA['catRAP'], 'catGDB' => $reissA['catGDB'], 'catDISP' => $reissA['catDISP'],'commander' => ($reissA['pgGrado'] != NULL) ? $reissA['pgGrado'].' '.$reissA['pgNomeC'].' '.$reissA['pgUser'] : '','uniform' => ($reissA['pgGrado'] != NULL) ? $reissA['ordinaryUniform'] : '');
		$template->place=$place;

	$rus = mysql_fetch_assoc(mysql_query("SELECT * FROM png_incarichi,pg_ranks WHERE prio = pngRank AND pngID = $ida"));

	$pngName = addslashes($rus['pngName']);
	$pngSurname = addslashes($rus['pngSurname']);
	$rusOthers = mysql_query("SELECT ordinaryUniform,placeName,png_incarichi.* FROM png_incarichi,pg_ranks,pg_places WHERE pngPlace=placeID AND prio = pngRank AND pngName = '$pngName' AND pngSurname = '$pngSurname'");
	
	if (mysql_affected_rows())
	{
		$otherIncarichi=array();
		while($rusOtherR = mysql_fetch_assoc($rusOthers))
			$otherIncarichi[] = $rusOtherR;
		$template->otherIncarichi = $otherIncarichi;
	}

	$template->thisIncarico = $rus;
	$template->equi = $equia;

}

elseif(isSet($_GET['equi']))
{		

		$equi = addslashes($vali->killchars($_GET['equi']));

		$rtp = mysql_fetch_assoc(mysql_query("SELECT hasCrew FROM pg_places WHERE placeID = '$equi'"));
		if($rtp['hasCrew'] > 1)
			$template = new PHPTAL('TEMPLATES/cdb_organigramma_N.htm');
		else
			$template = new PHPTAL('TEMPLATES/cdb_organigramma_uniq_N.htm');

		

		$ccols = array('Comando e Strategia' => 'ccolRed','Difesa e Sicurezza' => 'ccolGre','Ingegneria e Operazioni' => 'ccolYelo','Navigazione' => 'ccolBlue','Scientifica e Medica' => 'ccolTeal');

		$cclogs = array('Comando e Strategia' => 'nl_com.png','Difesa e Sicurezza' => 'nl_sec.png','Ingegneria e Operazioni' => 'nl_ops.png','Navigazione' => 'nl_nav.png','Scientifica e Medica' => 'nl_sci.png','Comando Civile' => 'logo_tycho.png');

		$clab = array('Comando e Strategia' => 'COM / STR','Difesa e Sicurezza' => 'DIF / SIC','Ingegneria e Operazioni' => 'OPS / ING','Navigazione' => 'NAV','Scientifica e Medica' => 'SCI / MED','Comando Civile' => 'COMANDO','Personale Civile' => 'Civili');

		$crew = mysql_query("(SELECT recID,pg_users.pgID,pg_users.pgPrestige as pgPrestige,pgNomeC,pgNomeSuff, pgSpecie, pgSesso, pgUser, pgGrado,pgLastAct, pgSezione, pgIncarico,pgLock, ordinaryUniform, png, pg_incarichi.incSezione, pg_incarichi.incDivisione, pg_incarichi.incDipartimento,pg_incarichi.incGroup,pg_incarichi.incIncarico, rankerprio FROM pg_users,pg_ranks,pg_incarichi WHERE pg_users.pgID = pg_incarichi.pgID AND prio = rankCode AND pgLock=0 AND pg_incarichi.pgPlace = '$equi' AND (pgAuthOMA <> 'BAN' OR png=1) AND incActive=1 ORDER BY rankerprio DESC, pgUser ASC) UNION (SELECT pngID as recID,0 as pgID,-1 as pgPrestige,pngName as pgNomeC,'' as pgNomeSuff, pngSpecie as pgSpecie, pngSesso as pgSesso, pngSurname as pgUser, Rgrado as pgGrado, '0' as pgLastAct, Rsezione as pgSezione, pngIncarico as pgIncarico, '0' as pgLock, ordinaryUniform, '1' as png, pngSezione as incSezione, pngDivisione as incDivisione, pngDipartimento as incDipartimento, pngIncGroup as incGroup, pngIncarico as incIncarico,rankerprio FROM pg_ranks,png_incarichi WHERE prio = pngRank AND pngPlace = '$equi' ORDER BY rankerprio DESC, pngSurname ASC)");
		 
		//SELECT 0 as pgID,pngName as pgNomeC, pngSpecie as pgSpecie, pngSesso as pgSesso, pngSurname as pgUser, 'T' as pgGrado, 'TA' as pgSezione, pngIncarico as pgIncarico, '0' as pgLock, ordinaryUniform, 1 as png, pg_incarichi.* FROM pg_users,pg_ranks,pg_incarichi WHERE pg_users.pgID = pg_incarichi.pgID AND prio = rankCode AND pgLock=0 AND pgAssign = '$equi' ORDER BY rankerprio DESC, pgUser ASC
 
	 		 
		$personale = array();
		while($rCrew = mysql_fetch_assoc($crew)){


			//$rCrew['incSezione'] = str_replace('Personale Civile','Civ',$rCrew['incSezione']);

			if($rtp['hasCrew'] == 1)
				{
					$rCrew['incDivisione'] ='-';
					$rCrew['incDipartimento'] ='-';
					$rCrew['incGroup'] ='-';
				}
				$r_incSezione = trim($rCrew['incSezione'] );
				$r_incDivisione = trim(strtoupper( $rCrew['incDivisione'] ));
				$r_incDipartimento = trim(strtoupper( $rCrew['incDipartimento'] ));
				$r_incGroup = trim(strtoupper( $rCrew['incGroup'] ));
				
			


			if (!array_key_exists($r_incSezione,$personale))
				$personale[$r_incSezione] = array();

			if (!array_key_exists($r_incDivisione,$personale[$r_incSezione]))
				$personale[$r_incSezione][$r_incDivisione] = array();

			if (!array_key_exists($r_incDipartimento,$personale[$r_incSezione][$r_incDivisione]))
				$personale[$r_incSezione][$r_incDivisione][$r_incDipartimento] = array();

			if (!array_key_exists($r_incGroup,$personale[$r_incSezione][$r_incDivisione][$r_incDipartimento]))
				$personale[$r_incSezione][$r_incDivisione][$r_incDipartimento][$r_incGroup] = array();

			$personale[$r_incSezione][$r_incDivisione][$r_incDipartimento][$r_incGroup][] = $rCrew; 
		}


		
		foreach ($personale as $sez=>$k)
			foreach ($k as $div=>$j)
			{
				uksort($personale, 'cmp2');  
				uksort($personale[$sez][$div], 'cmp2');  
				foreach ($j as $dip=>$dipgroup)
				{
					#uasort($personale[$sez][$div][$dipgroup], 'cmp');  
					ksort($personale[$sez][$div][$dip]);  

					foreach ($dipgroup as $dipgru=>$per)
					{
						
						uasort($personale[$sez][$div][$dip][$dipgru], 'cmp');  
					}
				}
			} 
		

		$template->personale = $personale;
		$template->equi = $equi;
		$template->ccol = $ccols;
		$template->clab = $clab; 
		$template->cclogos = $cclogs;
		$crew = mysql_query("SELECT placeID, placeMotto, placeName, placeLogo, place_littleLogo1, placeClass, catGDB, catDISP, catRAP, pgGrado,pgUser,pgNomeC,ordinaryUniform, placeType, placePopulation, placeAlignment FROM pg_places LEFT JOIN (pg_users JOIN pg_ranks ON rankCode = prio) ON pgID = placeCommander WHERE placeID = '$equi'");
	


		while($reissA = mysql_fetch_array($crew))
		$place = array('placeName' => $reissA['placeName'], 'placeMotto' => $reissA['placeMotto'], 'placeClass' => $reissA['placeClass'], 'placeLittle' => $reissA['place_littleLogo1'],'placeLogo' => $reissA['placeLogo'], 'placeID' => $reissA['placeID'], 'catRAP' => $reissA['catRAP'], 'catGDB' => $reissA['catGDB'], 'catDISP' => $reissA['catDISP'],'commander' => ($reissA['pgGrado'] != NULL) ? $reissA['pgGrado'].' '.$reissA['pgNomeC'].' '.$reissA['pgUser'] : '','uniform' => ($reissA['pgGrado'] != NULL) ? $reissA['ordinaryUniform'] : '', 'placeType' => $reissA['placeType'],'placePopulation' => $reissA['placePopulation'],'placeAlignment' => $reissA['placeAlignment']);
		$template->place=$place;
		
		
}

else if(isSet($_GET['prest']))
{
		$template = new PHPTAL('TEMPLATES/cdb_prestavolto.htm');
		$reiss = mysql_query("SELECT pgID,pgOffAvatarC,pgOffAvatarN,pgUser FROM pg_users WHERE pgOffAvatarC <> '' AND pgOffAvatarN <> '' AND pgAuthOMA <> 'BAN' ORDER BY pgOffAvatarC");
		$pg = array();
		
		while($reissA = mysql_fetch_array($reiss))
		$pg[] = $reissA;
		
		$template->pg = $pg;
}
else if (isSet($_GET['createPNG']))
{

	$pgName = addslashes($_POST['pgName']);
	$pgSurname = addslashes($_POST['pgSurname']);
	$pgSpecie = htmlentities(addslashes(($_POST['pgSpecie'])),ENT_COMPAT, 'UTF-8');
	$pgSesso = htmlentities(addslashes(($_POST['pgSesso'])),ENT_COMPAT, 'UTF-8');
	$pgIncarico = addslashes($_POST['pgIncarico']);
	$pgSezione = addslashes($_POST['pgSezione']);
	$pgDivisione = addslashes($_POST['pgDivisione']);
	$pgDipartimento = addslashes($_POST['pgDipartimento']);
	$pgIncGroup = addslashes($_POST['incGroup']);
	
	$pngRankCode = $vali->numberOnly($_POST['rankCode']);
	$assegnazione = addslashes($_POST['assegnazione']);

	$equi = addslashes($_GET['equia']);

	if(!isSet($_POST['creaScheda']))
	{
		if(!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;

		mysql_query("INSERT INTO png_incarichi (pngName,pngSurname,pngRank,pngIncarico,pngSezione,pngDivisione,pngDipartimento,pngIncGroup,pngPlace,pngSesso,pngSpecie) VALUES ('$pgName','$pgSurname',$pngRankCode,'$pgIncarico','$pgSezione','$pgDivisione','$pgDipartimento','$pgIncGroup','$assegnazione','$pgSesso','$pgSpecie') ");
	}
	else{
		
		if(!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

		$passy = addslashes($_POST['passScheda']);

		$emai= 'png@stfederation.it';
		
		$pgPassword1 = md5($passy);
		
		$a1 = array('ALFA','BETA','GAMMA','DELTA','ETA','EPSILON','ZETA','ETA','THETA','IOTA','KAPPA','LAMBDA','MI','NI','XI','OMICRON','PI','RHO','SIGMA','TAU','YPSILON','PHI','CHI','PSI','OMEGA');
		$pgAuth= $a1[rand(0,24)].' '.$a1[rand(0,24)].' '.rand(0,10).' '.rand(0,10);
		
		if ($pgName =='' || $emai == '' || $pgSpecie == '' || $pgSesso == '') 
		{	
			header('Location:index.php?error=insertion_error');
			exit;
		}
	
		
		$currentUser = new PG($_SESSION['pgID']);
		$re1=mysql_query("SELECT 1 FROM pg_users WHERE pgUser = '$pgSurname'");

		if (mysql_affected_rows()){header("Location:index.php?error=96"); exit;}
		
		mysql_query("INSERT INTO pg_users(pgUser,pgNomeC, pgPass, pgAuth, pgLocation, pgRoom, pgAuthOMA, pgSpecie, pgSesso, rankCode, email,pgLock,pgFirst,png, pgMatricola) VALUES ('$pgSurname','$pgName','$pgPassword1','$pgAuth','$equi','$equi','N','$pgSpecie','$pgSesso',$pngRankCode,'$emai',0,0,1,'".createRandomMatricola()."')");

		$rek=mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$pgSurname'"));

		$rekU = $rek['pgID'];
		PG::setMostrina($rekU,$pngRankCode);

		$nPN = new PG($rekU);
		$nPN->addNote("Password: $passy",$_SESSION['pgID']);

		mysql_query("INSERT INTO pg_incarichi (pgID,incIncarico,incSezione,incDivisione,incDipartimento,pgPlace,incMain) VALUES('$rekU','$pgIncarico','$pgSezione','$pgDivisione','$pgDipartimento','$equi','1')");

		mysql_query("INSERT INTO pg_users_bios (pgID) VALUES ($rekU)");

		mysql_query("INSERT INTO connlog (user,time,ip) VALUES ($rekU,$curTime,'".$_SERVER['REMOTE_ADDR']."')");

	}

	header('Location:crew.php?equi='.$equi);
}


else 
{
		$template = new PHPTAL('TEMPLATES/cdb_assign.htm');
		$reiss = mysql_query("SELECT placeID, placeMotto, placeName, placeLogo, place_littleLogo1, placeClass, catGDB, catDISP, catRAP,pgGrado,pgUser,pgNomeC,ordinaryUniform,placeType,placePopulation,placeAlignment FROM pg_places LEFT JOIN (pg_users JOIN pg_ranks ON rankCode = prio) ON pgID = placeCommander WHERE hasCrew > 0 ORDER BY placeType, ordering");
		$places = array();
		
		while($reissA = mysql_fetch_array($reiss))
		{
			
			$reissA["placeNameE"] = str_replace("'","",$reissA['placeName']);
			$reissA["placeLittle"] = $reissA['place_littleLogo1'];
			$reissA["commander"] = ($reissA['pgGrado'] != NULL) ? $reissA['pgGrado'].' '.str_replace("'","\'",$reissA['pgNomeC']).' '.str_replace("'","\'",$reissA['pgUser']) : '';
			$reissA["uniform"] = ($reissA['pgGrado'] != NULL) ? $reissA['ordinaryUniform'] : '';
			$places[] = $reissA;
		}
		
		$template->places = $places;
		
}

	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places");
 
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeID']] = $resLoc['placeName'];
	
	
	$ranks=array();
	$my = mysql_query("SELECT prio,Note,ordinaryUniform,aggregation FROM pg_ranks WHERE aggregation IN ('Civili ','Ristorazione','Flotta Civile','Corpo Diplomatico','Ingegneria Civile','Stampa','Medicina Civile','Musicisti','Federazione Unita dei Pianeti','Scienze','Teatro e Recitazione','Danza','Intrattenimento e Animazione','Klingon ','Politica - Altri','Forze di Difesa ','Flotta Stellare') ORDER BY rankerprio DESC");
	while($myA = mysql_fetch_array($my))
	$ranks[$myA['aggregation']][$myA['prio']] = array('note' => $myA['Note'], 'ord' => $myA['ordinaryUniform']);


	$sects=array();
	$my = mysql_query("SELECT DISTINCT CONCAT(aggregation,CONCAT(' - ',Rsezione)) as ktl, Rsezione FROM pg_ranks WHERE aggregation  IN ('Civili ','Ristorazione','Flotta Civile','Corpo Diplomatico','Ingegneria Civile','Stampa','Medicina Civile','Musicisti','Federazione Unita dei Pianeti','Scienze','Teatro e Recitazione','Danza','Intrattenimento e Animazione','Klingon ','Politica - Altri','Forze di Difesa ','Flotta Stellare') ORDER BY aggregation");
	while($myA = mysql_fetch_array($my))
		$sects[$myA['Rsezione']] = $myA['ktl'];


$template->flavour = 'uniq';

$template->ranks = $ranks;
$template->sects = $sects;
$template->locations=$locArray;
$template->user = $currentUser;
$template->prestigioLabels = $prestigioLabels;
$template->userSM = (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) ? 'yes' : 'no' ;
$template->userM = (PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? 'yes' : 'no' ;

$template->userSL = (PG::mapPermissions('SL',$currentUser->pgAuthOMA)) ? true : false;
 $template->currentDate = $currentDate;
 $template->currentStarDate = $currentStarDate;
 $template->gameOptions = $gameOptions;
 $template->allSupportedSpecies=$allSupportedSpecies;
// $template->gameName = $gameName;
// $template->gameVersion = $gameVersion;
// $template->debug = $debug;
// $template->gameServiceInfo = $gameServiceInfo;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	

function createRandomMatricola() {
	$chars = "abcdefghijkmnopqrstuvwxyz";
    $nums = "023456789";
    srand((double)microtime()*1000000);
   
    $i = 0;
    $let = '' ;
    $num1 = '' ;
    $num2 = '' ;

    while ($i < 2) {
        $num = rand() % 25;
        $tmp = substr($chars, $num, 1);
        $let = $let . $tmp;
        $i++;
    }
	 $i = 0;
	 while ($i < 3) {
        $num = rand() % 9;
        $tmp = substr($nums, $num, 1);
        $num1 = $num1 . $tmp;
        $i++;
    }
	$i = 0;
	 while ($i < 3) {
        $num = rand() % 9;
        $tmp = substr($nums, $num, 1);
        $num2 = $num2 . $tmp;
        $i++;
    }
	return strtoupper($let.'-'.$num1.'-'.$num2);
}

include('includes/app_declude.php');

?>