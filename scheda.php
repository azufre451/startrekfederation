<?php 
session_start();
if (!isSet($_SESSION['pgID'])) { header("Location:index.php?login=do"); exit;}
if (!$_SESSION['pgID']){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/prestige.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php");

PG::updatePresence($_SESSION['pgID']);

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_DEPRECATED);

$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);

if(isSet($_GET['pgID'])) $selectedUser = $vali->numberOnly($_GET['pgID']);
else $selectedUser = $_SESSION['pgID'];

mysql_query("SELECT 1 FROM pg_users WHERE pgID = $selectedUser");
if(!mysql_affected_rows()) $selectedUser = $_SESSION['pgID'];

$mode = (isSet($_GET['s'])) ? $_GET['s'] : '';

$selectedDUser = new PG($selectedUser,2);

if($mode == 'bg')
{ 
	$template = new PHPTAL('TEMPLATES/scheda_bkg.htm');
	$backgrounder = PG::getSomething($selectedUser,"BG");

	$revBackgrounder = PG::getSomething($selectedUser,"lastBG"); 

	
	if($selectedUser == $_SESSION['pgID'] || PG::mapPermissions('A',$currentUser->pgAuthOMA)){ $template->showIlSegreto = True;}

	if($backgrounder)
		{
			$template->showBackground = True;
			$template->background = str_replace('<embed','&lt;embed',CDB::bbcode($backgrounder));
		}


	if($revBackgrounder)
	{
		$template->revBackground = str_replace('<embed','&lt;embed',CDB::bbcode($revBackgrounder));
		if($selectedUser == $_SESSION['pgID'] || PG::mapPermissions('G',$currentUser->pgAuthOMA))
		{
			$template->showRevBackground = True;
		}
	}

} 

elseif($mode == 'bvadd')
{

	if($selectedUser == $_SESSION['pgID'] || $currentUser->pgAuthOMA == 'A')
	{

		$template = new PHPTAL('TEMPLATES/scheda_ruolino_points.htm');
		
		$resPoints = PG::getSomething($selectedUser,'upgradePoints');
		$resUPoints = $resPoints['pgUpgradePoints'];
		
		//if ($resUPoints <= 0) header("Location:scheda.php?s=bv&pgID=$selectedUser");
		
		$a = new abilDescriptor($selectedUser);


		$abil=array();
		$resQ = mysql_query("SELECT pg_abilita.abID,abName, abImage,abEImage,abDescription,abDiff, abClass, value FROM pg_abilita LEFT JOIN pg_abilita_levels ON pg_abilita_levels.abID = pg_abilita.abID AND pgID = $selectedUser ORDER BY abDiff ASC");

		while($resCats = mysql_fetch_assoc($resQ)){
			$abClass = $resCats['abClass'];
			if(!array_key_exists($abClass,$abil))
				$abil[$abClass] = array();

			if($resCats['value'] == NULL){
				$resCats['value'] = 0;
				$resCats['nullCat']=1;
			}


			$cL = array();
			$lastCost = '';
			for ($i=$resCats['value']+1; $i<=15; $i++)
			{
				$cost = $a->fromToAbil($resCats['value'],$i,$resCats['abID']);
				if ($resUPoints >= $cost)
					$cL[] = array($i,$cost);
				else{
					$lastCost = $cost;
					break;
				}
			} 
				
	  
	  		$dep=$a->explainDependencies($resCats['abID']);
	  		
	  		$totThr=0;
//	  		$myval= -3*($this->abilDict[$abID]['abDiff']);

	  		$depsArray=array();
	  		foreach ($dep as $depK=>$depV){
	  			$totThr += (float)$depV[2];
	  			$depsArray[] ='depFrom_'.$depV[0]['abID'];
	  			//echo $totThr;
	  		}

			$abil[$abClass][] = array(
				'abID' => $resCats['abID'],
				'abName' => $resCats['abName'],
				'abImage' => $resCats['abImage'],
				'abEImage' => $resCats['abEImage'],
				'abClass' => $resCats['abClass'],
				'abDiff' => $resCats['abDiff'],
				'abDescription' => $resCats['abDescription'],
				'value' => $resCats['value'],
				'dep' => $dep,
				'depString' => implode(' ',$depsArray),
				'thr' => (isSet($resCats['nullCat']) ? -3*($resCats['abDiff']) : $resCats['value']) + ceil($totThr),
				'exp' => $cL,
				'lastCost' => $lastCost,
				'levelperc' => ceil((float)($resCats['value'])/15*100)			
			);
		}
		//print_r($abil['COMB']); exit;
		ksort($abil);
		$template->abil = $abil;
		$template->resUPoints = $resUPoints;
		if(isSet($_GET['absel'])){
			
			$abilSet = abilDescriptor::getAbil(addslashes($_GET['absel']));
			$template->openedCategory = $abilSet['abClass'];
			$template->openedAbil = $abilSet['abID'];

		}
		$template->labeler = array('GEN'=>'Ab. Generali','COMB'=>'Ab. Combattimento','ATT'=>'Ab. Attitudinali','SPE'=>'Ab. Speciali','TEC'=>'Ab. Tecniche','SCI'=>'Ab. Scientifiche','ABIL'=>'Caratteristiche');
		$template->selectedUser = $selectedUser;
	} else
	{
		header('Location:404.htm');
		exit;
	}
}

elseif($mode == 'addPointCar'){
	
	$car = $_POST['dcar'];
	$dest = $_POST['target'];
	$selector=$vali->numberOnly($_POST['userSelector']);

	if($selectedUser == $_POST['userSelector'] || $currentUser->pgAuthOMA == 'A')
	{
		$a = new abilDescriptor($selector); 
		$a->performVariation(array(array($car,$dest)));
	}
	header("location:scheda.php?pgID=$selector&s=bvadd&absel=$car");
	exit;
}
elseif($mode == 'bv')
{
	$ptl= PG::getSomething($selectedUser,'upgradePoints');
	
	//if(($ptl['pgUpgradePoints']+$ptl['pgSocialPoints']+$ptl['pgSpecialistPoints'] > 0) && !isSet($_GET['escape']) && $selectedUser == $_SESSION['pgID']) header("Location:scheda.php?s=bvadd&pgID=$selectedUser");
	
	$template = new PHPTAL('TEMPLATES/scheda_ruolino.htm');
	
	$resQ = mysql_query("SELECT pg_abilita.abID,abName,abDescription, abImage, abClass, abDiff, value as level, abLevelDescription_1,abLevelDescription_2,abLevelDescription_3,abLevelDescription_4,abLevelDescription_5,abDepString FROM pg_abilita_levels, pg_abilita WHERE pgID = $selectedUser AND pg_abilita_levels.abID = pg_abilita.abID ORDER BY abDiff,abName");
	// $i=0;
	//echo mysql_error();
	// $k=0;



	$abil=array();

	while($resCats = mysql_fetch_array($resQ)){
		if(!array_key_exists($resCats['abClass'],$abil))
			$abil[$resCats['abClass']] = array();

		$deps=array();
		if($resCats['abDepString'] != '')
		{	
			$t = explode('__',$resCats['abDepString']);
		
			foreach($t as $atp){
				$etp=explode('#',$atp);
				$deps[] = $etp;
			}
		}

		$abil[$resCats['abClass']][$resCats['abID']] = array(
			'abID' => $resCats['abID'],
			'abName' => $resCats['abName'],
			'abImage' => $resCats['abImage'],
			'abClass' => $resCats['abClass'],
			'level' => $resCats['level'],
			'abDiff' => $resCats['abDiff'],
			'deps' => $deps,
			'thr' => '',
			'abDescription' => $resCats['abDescription'],
			'leveldesc' => ($resCats['level'] > 0) ? $resCats['abLevelDescription_'.ceil($resCats['level']/3)] : 'Abilità attivata',
			'levelperc' => ceil((float)($resCats['level'])/ ($resCats['abClass'] == 'ABIL' ? 15 : 20) *100)			
		); 
	}

	foreach($abil as $abClass => &$abilsInClass)
	{
		foreach($abilsInClass as $abID => &$ability)
		{

			$totThr=0;
			
			$connectedText='<p class="thrExplain"> Soglia dado: ';
			
			$depsText= (!empty($ability['deps'])) ? ' | Dipende da: ' : '';

			foreach($ability['deps'] as $depVal){
				//print_r($depVal);

				$connectedLevel=$abil['ABIL'][$depVal[0]]['level'];
				$depsText.= '<img src="TEMPLATES/img/interface/personnelInterface/abilita/'.$abil['ABIL'][$depVal[0]]['abImage'].'" /> ';

				$totThr+= (int)$connectedLevel *$depVal[1]/100;
			}
			
			$thrVal= $ability['level'] +ceil($totThr);
			
			$ability['thr'] = $connectedText . ' ' . $thrVal . $depsText . '</p>';

			$ability['levelThrperc'] = min(100, ceil((float)($thrVal)/($ability['abClass'] == 'ABIL' ? 15 : 20)   *100));
		}

			
	}

	//print_r($abil);exit;
	mysql_query("SELECT 1 FROM pg_users_bios WHERE pgID = $selectedUser AND valid = 2");

	if (mysql_affected_rows())
		$template->restrictEditCar = 1;


	ksort($abil);
	$template->abi = $abil;
	$template->labeler = array('GEN'=>'Ab. Generali','COMB'=>'Ab. Combattimento','ATT'=>'Ab. Attitudinali','SPE'=>'Ab. Speciali','TEC'=>'Ab. Tecniche','SCI'=>'Ab. Scientifiche','ABIL'=>'Caratteristiche');
 	$template->uniform = PG::getSomething($selectedUser,'uniform'); 
 	$template->pgSpecie = $selectedDUser->pgSpecie;




}

elseif($mode == 'ssto')
{
	$template = new PHPTAL('TEMPLATES/scheda_stato_servizio.htm');
	$template->thisYear = $thisYear+$bounceYear;

	$res = mysql_query("SELECT recID,timer,text,placeName,postLink,type,extra,image FROM pg_service_stories LEFT JOIN pg_places ON placeID = placer WHERE owner = $selectedUser ORDER BY timer DESC");
	
	$stories = array('SERVICE' => array(),'EXAM' => array());
	while($resA = mysql_fetch_array($res)){
		$resA['text'] = CDB::bbcode($resA['text']);
		
		if($resA['postLink'] != '')
		{
			if(strpos($resA['postLink'],'#') !== false)
			{
				$e = explode('#',$resA['postLink']);
				$resA['postLink'] = CDB::formatCDBLinkExternal($e,$mode='small');
			}
			else
				$resA['postLink'] = CDB::formatCDBLinkExternal(array('link',$resA['postLink']),$mode='small');
		}

		$stories[$resA['type']][] = $resA;
	}

	$res = mysql_query("SELECT * FROM pg_user_stories WHERE pgID = $selectedUser ORDER BY dater");
	$storiesRuol = array();


	
		while($resA = mysql_fetch_array($res))
		{
			if (!$resA['pgGroup']){ 
				$storiesRuol[$resA['storyID']] = $resA;
			}
			else
			{
				if(!array_key_exists('associatedHTML',$storiesRuol[$resA['pgGroup']] ))
				{
					$storiesRuol[$resA['pgGroup']]['associatedHTML'] = '';
					$storiesRuol[$resA['pgGroup']]['commonYear'] = '';


				}
				$storiesRuol[$resA['pgGroup']]['commonYear'] .= substr($resA['dater'],0,4).'#';
				$storiesRuol[$resA['pgGroup']]['associatedHTML'] .= '<p><img src="TEMPLATES/img/ranks/'.$resA['rankImage'].'.png" /> <span style="color:#CCC; font-size:10px; text-transform:uppercase">'.substr($resA['dater'],0,4).'</span>  <span style="color:#999; font-size:10px; text-transform:uppercase">'.$resA['what'].'</span> </p>';
			}
		}

	

	if($selectedUser == $_SESSION['pgID'] || $currentUser->pgAuthOMA == 'A')
	{

	$aggregations=array();
	$ranks=array();
	$curRankCode = PG::getSomething($selectedUser,'rankCode');
	

	$adminFilter = ($currentUser->pgAuthOMA == 'M') ? '' : 'WHERE masked = 0';

	$my = mysql_query("SELECT prio,Note,ordinaryUniform,aggregation FROM pg_ranks $adminFilter ORDER BY rankerprio DESC");
	
	while($myA = mysql_fetch_array($my)){

		if (!in_array($myA['aggregation'], $aggregations))
			$aggregations[] = $myA['aggregation'];

		if ($myA['prio'] == $curRankCode)
			{
				$curRankImage=$myA['ordinaryUniform'];
				$curRankNote=$myA['Note'];
			}
	}
	
	$template->aggregations = $aggregations;
	$template->monty = array('1' => 'GEN', '2' => 'FEB','3' => 'MAR','4' => 'APR','5' => 'MAG','6' => 'GIU','7' => 'LUG','8' => 'AGO','9' => 'SET','10' => 'OTT','11' => 'NOV','12' => 'DIC');

	$template->rankCode = $curRankCode;
	$template->ordinaryRank = $curRankImage;
	$template->ordinaryRankNote = $curRankNote;
	}
	 

	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places WHERE placeType <> 'Navetta' ORDER BY placeName");
	$resA = mysql_fetch_array($res);
	
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
		$locArray[$resLoc['placeID']] = $resLoc['placeName'];

	$template->locations = $locArray;
	$template->storiesRuol = $storiesRuol; 
	$template->stories = $stories;
	


	
} 

elseif($mode == 'addssto' || $mode == 'addexam' || $mode == 'editsstoDo')
{
	//5$user = $vali->numberOnly($_POST['userSelector']);
	$dateG = str_pad($vali->numberOnly($_POST['dataG']),2,'0',STR_PAD_LEFT);
	$dateM = str_pad($vali->numberOnly($_POST['dataM']),2,'0',STR_PAD_LEFT);
	$dateA = $vali->numberOnly($_POST['dataA']);
	$dateDef = $dateA.'-'.$dateM.'-'.$dateG;
	$what = addslashes(($_POST['what']));
	
	if($mode == 'addssto')
	{
		$cross = addslashes(($_POST['cross']));
		$placer = addslashes(($_POST['placer']));
		$query = "INSERT INTO pg_service_stories (owner,timer,text,placer,postLink,type) VALUES ($selectedUser,'$dateDef','$what','$placer','$cross','SERVICE')";
		$padTit = 'Update Stato di Servizio';
		$paddTex = "È stato aggiunto nella tua scheda PG un nuovo elemento allo stato di servizio";
	}
	elseif($mode == 'editsstoDo')
	{
		$cross = addslashes(($_POST['cross']));
		$placer = addslashes(($_POST['placer']));
		$recID = $vali->numberOnly($_POST['recordID']);

		$query = "UPDATE pg_service_stories SET timer = '$dateDef', text = '$what', placer = '$placer', postLink = '$cross' WHERE recID = '$recID' AND type = 'SERVICE'";
	}
	elseif($mode == 'addexam')
	{
		$esit = $vali->numberOnly($_POST['esit']);
		 
		$bvQuery = mysql_fetch_assoc(mysql_query("SELECT brevID,image FROM pg_brevetti WHERE descript = '$what'"));
		$brevID = $bvQuery['brevID'];
		$image= (mysql_affected_rows()) ?  $bvQuery['image']  : 'starfleet_brev.png';
		$esitL = ($esit <= 100) ? $esit : (($esit == 110) ? 'APPROVATO' : 'RESPINTO');
		
		$brevID= (mysql_affected_rows()) ?  $bvQuery['brevID']  : 'NULL';
		

		$query = "INSERT INTO pg_service_stories (owner,timer,text,placer,extra,type,image,brevlink) VALUES ($selectedUser,'$dateDef','$what',(SELECT pgAssign FROM pg_users WHERE pgID = $selectedUser),'$esit','EXAM','$image',$brevID)";
		
		$paddTex = "Esito caricato per: \"$what\"";
		$padTit = 'Nuovo Esame Registrato';

	}
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)){
		mysql_query($query);


		if (isSet($padTit))
			$selectedDUser->sendNotification($padTit,$paddTex,$_SESSION['pgID'],"https://oscar.stfederation.it/SigmaSys/logo/logoufp.png",'schedaSstoOpen');

	}
	

	header("Location:scheda.php?pgID=$selectedUser&s=ssto");
	exit;
}

else if ($mode == 'removessto')
{
	$w = $vali->numberOnly($_GET['sID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)){
		mysql_query("DELETE FROM pg_service_stories WHERE recID = $w");
	} 
	
	header("Location:scheda.php?pgID=$selectedUser&s=ssto");
	exit;
}

else if ($mode == 'editssto')
{
	$w = $vali->numberOnly($_GET['sID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)){
		
		$template = new PHPTAL('TEMPLATES/scheda_edit_stato_servizio.htm');
		$template->thisYear = $thisYear+$bounceYear;



		$rea=mysql_fetch_assoc(mysql_query("SELECT * FROM pg_service_stories WHERE recID = $w AND owner = $selectedUser LIMIT 1"));
		$rtp=explode('-',$rea['timer']);

		$rea['dYear'] = $rtp[0];
		$rea['dMonth'] = $rtp[1];
		$rea['dDay'] = $rtp[2];
		$template->serviceStory= $rea;

		$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places WHERE placeType <> 'Navetta' ORDER BY placeName");
		
	
		$locArray=array();
		while($resLoc = mysql_fetch_array($resLocations))
			$locArray[$resLoc['placeID']] = $resLoc['placeName'];

		$template->locations = $locArray;
		
	} 
}


elseif($mode == 'me')
{
	$template = new PHPTAL('TEMPLATES/scheda_medica.htm');
	$res = mysql_query("SELECT pgMedica.*,placeName FROM pgMedica,pg_places WHERE placeID = unita AND pgID = $selectedUser ORDER BY time DESC");
	
	$iconTypes = array('MED' => 'medlogo.png','PSI' => 'cnslogo.png','rMED' => 'romulan_ric_logo.png','rPSI' =>'romulan_ric_logo.png');
	
	$medics=array();
	
	while ($resA = mysql_fetch_array($res))
	{
		$timString = str_pad(substr($resA['tdate'],8,2),2,'0',STR_PAD_LEFT) . '/' . str_pad(substr($resA['tdate'],5,2),2,'0',STR_PAD_LEFT) . '/' . substr($resA['tdate'],0,4);
		$medics[] = array('medic' => $resA['medico'],'time' => $timString,'unita'=>$resA['placeName'], 'recid' => $resA['recID'],'logoName' => $iconTypes[$resA['type']],'type' => $resA['type'],'medAnamnesi'=>CDB::reduced_bbCode($resA['medAnamnesi']),'medVisiv'=>CDB::reduced_bbCode($resA['medVisiv']),'medStrument'=>CDB::reduced_bbCode($resA['medStrument']),'medDiagnosi'=>CDB::reduced_bbCode($resA['medDiagnosi']),'medTerapia'=>CDB::reduced_bbCode($resA['medTerapia']),'medDecorso'=>CDB::reduced_bbCode($resA['medDecorso']),'medCode'=>$resA['medCode']);
	}
 
	$statoSalute = PG::getSomething($selectedUser,"statoSalute");	
	$template->medics = $medics;
	$template->statoSalute = $statoSalute;
	
	$template->currentUserSignature = $currentUser->pgGrado.' '.$currentUser->pgUser;
	//$mEG = ;
	
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(15))){$template->showPsiEdit = true; $template->SHT = true; }
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(10))){$template->showMedEdit = true; $template->SHT = true;}
	
	$template->thisYear = $thisYear+$bounceYear;
 
 
}


else if ($mode == 'meAdd')
{
	//$id = addslashes($_GET['medicA']);
	$id = $vali->numberOnly($_GET['pgID']);
	 

	$medicName = isSet($_POST['medicName']) ?  addslashes($_POST['medicName']) : addslashes($currentUser->pgGrado.' '.$currentUser->pgUser);
	$medicCode = isSet($_POST['medicCode']) ? addslashes($_POST['medicCode']) : '';
	$medicAnamnesi = isSet($_POST['medicAnamnesi']) ?  addslashes($_POST['medicAnamnesi']) : '';
	$medicVisi = isSet($_POST['medicVisi']) ?  addslashes($_POST['medicVisi']) : '';
	$medicStrum = isSet($_POST['medicStrum']) ?  addslashes($_POST['medicStrum']) : '';
	$medicDiagnos = isSet($_POST['medicDiagnos']) ?  addslashes($_POST['medicDiagnos']) : '';
	$medicTerap = isSet($_POST['medicTerap']) ?  addslashes($_POST['medicTerap']) : '';
	$medicDecorso = isSet($_POST['medicDecorso']) ?  addslashes($_POST['medicDecorso']) : '';
	
	$dateG = str_pad($vali->numberOnly($_POST['dataG']),2,'0',STR_PAD_LEFT);
	$dateM = str_pad($vali->numberOnly($_POST['dataM']),2,'0',STR_PAD_LEFT);
	$dateA = $vali->numberOnly($_POST['dataA']);
	$dateDef = $dateA.'-'.$dateM.'-'.$dateG;
	
	
	$tType = $_POST['medType'];
	
	if($medicDiagnos != '') 
	{
	//if (PG::mapPermissions('SL',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(150))){$showPsi = true;}
	if (in_array($tType,array('MED','rMED')) && (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(10))))
		{
		mysql_query("INSERT INTO pgMedica(pgID, medico, time, unita, type, medAnamnesi, medVisiv, medStrument, medDiagnosi, medTerapia, medDecorso, medCode,tdate) VALUES ($id,'$medicName',$curTime,'".addslashes($currentUser->pgLocation)."','$tType','$medicAnamnesi','$medicVisi','$medicStrum','$medicDiagnos','$medicTerap','$medicDecorso','$medicCode','$dateDef')");
		//$selectedDUser->sendPadd('OFF: NUOVO REFERTO','Un nuovo referto medico è stato aggiunto alla tua scheda PG. Consulta la sezione "Scheda Medica" per vedere i dettagli.');
		#$text,$subtext,$text,$from = '518',$image='
		$selectedDUser->sendNotification("Nuovo referto medico","Un nuovo referto medico è stato aggiunto alla tua scheda PG",$_SESSION['pgID'],"https://oscar.stfederation.it/SigmaSys/logo/nl_med.r.png",'schedaMedOpen');

		if (!$selectedDUser->png) $currentUser->addPoints(1,'MEDIC','Inserimento Referto Medico','Inserimento Referto Medico '.$selectedDUser->pgUser);
 
		}
	
	else if (in_array($tType,array('PSI','rPSI')) && (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(15))))
		{
		mysql_query("INSERT INTO pgMedica(pgID, medico, time, unita, type, medAnamnesi, medDiagnosi, medTerapia,tdate) VALUES ($id,'$medicName',$curTime,'".addslashes($currentUser->pgLocation)."','$tType','$medicAnamnesi','$medicDiagnos','$medicTerap','$dateDef')");

		$selectedDUser->sendNotification("Nuovo referto medico","Un nuovo referto psicologico è stato aggiunto alla tua scheda PG",$_SESSION['pgID'],"https://oscar.stfederation.it/SigmaSys/logo/nl_med.r.png",'schedaMedOpen');

		if (!$selectedDUser->png) $currentUser->addPoints(2,'MEDIC','Inserimento Referto Psicologico','Inserimento Referto Psicologico '.$selectedDUser->pgUser);
		} 
	}
	header("Location:scheda.php?pgID=$id&s=me");
	exit;
}

elseif ($mode == 'meEdi')
{
	$template = new PHPTAL('TEMPLATES/scheda_medica_edit.htm');
	$rec = $vali->numberOnly($_GET['recID']);
	$resA = mysql_fetch_assoc(mysql_query("SELECT * FROM pgMedica WHERE recID = $rec"));
	
	$template->ediRecord = $resA;
	$template->thisYear = $thisYear+$bounceYear;
	if (in_array($resA['type'],array('MED','rMED'))) $template->mediRec = 'true';
}


else if ($mode == 'meEdiE')
{
	//$id = addslashes($_GET['medicA']);
	$id = $vali->numberOnly($_POST['ediRecID']);
	$pgID = $vali->numberOnly($_POST['pgID']);
	
	$medicName = isSet($_POST['medicName']) ?  addslashes($_POST['medicName']) : $currentUser->pgGrado.' '.$currentUser->pgUser;
	$medicCode = isSet($_POST['medicCode']) ? addslashes($_POST['medicCode']) : '';
	$medicAnamnesi = isSet($_POST['medicAnamnesi']) ?  addslashes($_POST['medicAnamnesi']) : '';
	$medicVisi = isSet($_POST['medicVisi']) ?  addslashes($_POST['medicVisi']) : '';
	$medicStrum = isSet($_POST['medicStrum']) ?  addslashes($_POST['medicStrum']) : '';
	$medicDiagnos = isSet($_POST['medicDiagnos']) ?  addslashes($_POST['medicDiagnos']) : '';
	$medicTerap = isSet($_POST['medicTerap']) ?  addslashes($_POST['medicTerap']) : '';
	$medicDecorso = isSet($_POST['medicDecorso']) ?  addslashes($_POST['medicDecorso']) : '';
	
	$dateG = str_pad($vali->numberOnly($_POST['dataG']),2,'0',STR_PAD_LEFT);
	$dateM = str_pad($vali->numberOnly($_POST['dataM']),2,'0',STR_PAD_LEFT);
	$dateA = $vali->numberOnly($_POST['dataA']);
	$dateDef = $dateA.'-'.$dateM.'-'.$dateG;
	$dateTimer = mktime(0,0,0,$dateM,$dateG,$dateA-$bounceYear);
	
	if($medicDiagnos != '') 
	{
		$resL = mysql_fetch_assoc(mysql_query("SELECT type FROM pgMedica WHERE recID = $id"));
		$tType = $resL['type'];
		
	if (in_array($tType,array('MED','rMED')) && (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(10))))
		mysql_query("UPDATE pgMedica SET medico = '$medicName', time = $dateTimer, medAnamnesi ='$medicAnamnesi', medVisiv='$medicVisi', medStrument='$medicStrum', medDiagnosi ='$medicDiagnos', medTerapia = '$medicTerap', medDecorso='$medicDecorso', medCode = '$medicCode',tdate ='$dateDef' WHERE recID = $id");
	
	else if (in_array($tType,array('PSI','rPSI')) && (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(15))))
		mysql_query("UPDATE pgMedica SET medico = '$medicName', time = $dateTimer, medAnamnesi ='$medicAnamnesi', medDiagnosi ='$medicDiagnos', medTerapia = '$medicTerap', medCode = '$medicCode',tdate ='$dateDef' WHERE recID = $id");	
	}
	header("Location:scheda.php?pgID=$pgID&s=me");
	exit;
}

elseif( $mode == 'addTempAuth'){
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$pgID = $vali->numberOnly($_GET['pgID']);
		
		$dateFrom = mktime(0,0,0,str_pad($_POST['dataM'],2,'0',STR_PAD_LEFT),str_pad($_POST['dataG'],2,'0',STR_PAD_LEFT),$_POST['dataA']);
		$dateTo = mktime(0,0,0,str_pad($_POST['data2M'],2,'0',STR_PAD_LEFT),str_pad($_POST['data2G'],2,'0',STR_PAD_LEFT),$_POST['data2A']);
		$authType = addslashes($_POST['authType']);
		$textR = addslashes($_POST['textR']);

		

		mysql_query("INSERT INTO pg_users_temp_auths (pgID,authStart,authEnd,authType,text,owner) VALUES ($pgID,'$dateFrom','$dateTo','$authType','$textR','".$currentUser->ID."')");
		
	}
	header("Location:scheda.php?pgID=$pgID&s=master");
	exit;
}

elseif( $mode == 'delTempAuth'){
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$pgID = $vali->numberOnly($_GET['pgID']);
		$recID = $vali->numberOnly($_GET['autID']);
		mysql_query("DELETE FROM pg_users_temp_auths WHERE recID = '$recID' AND pgID = '$pgID'");
	}
	header("Location:scheda.php?pgID=$pgID&s=master");
	exit;
}

else if ($mode == 'meRem')
{
	$id = $vali->numberOnly($_GET['recID']);
	$user = $vali->numberOnly($_GET['pgID']);
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		mysql_query("DELETE FROM pgMedica WHERE recID = $id");
		
	header("Location:scheda.php?pgID=$user&s=me");
	exit;
}

elseif($mode == 'getStory')
{
	$rec = $vali->numberOnly($_POST['storyID']);

	$resA = mysql_fetch_assoc(mysql_query("SELECT storyID,wherer,what,dater,prio,YEAR(dater) as year, MONTH(dater) as month, DAY(dater) as day FROM pg_user_stories,pg_ranks WHERE ordinaryUniform = rankImage AND storyID = $rec ORDER BY prio DESC LIMIT 1" ));
	if(mysql_affected_rows())
	{
		echo json_encode($resA);
	}

	
	exit;
}

elseif($mode == 'oj')
{
	$template = new PHPTAL('TEMPLATES/scheda_oggetti.htm');
	
	$res = mysql_query("SELECT fed_objects.oID,fed_objects_ownership.recID,oName as title, oDesc as descript, oImage as image FROM fed_objects,fed_objects_ownership WHERE fed_objects.oID = fed_objects_ownership.oID AND owner =$selectedUser ORDER BY recID DESC"); 
	$objects = array();
	while($rea = mysql_fetch_array($res))
	$objects[] = $rea;
	
	$template->objects = $objects;
	$template->editable = ($selectedUser == $_SESSION['pgID'] || PG::mapPermissions('SM',$currentUser->pgAuthOMA) ) ? true : false;
	
}

elseif($mode == 'addObj')
{
	$user = $vali->numberOnly($_POST['userSelector']);
	$what = (htmlentities(addslashes(($_POST['what'])),ENT_COMPAT, 'UTF-8'));
	$image = (htmlentities(addslashes(($_POST['whatI'])),ENT_COMPAT, 'UTF-8'));
	$description = (htmlentities(addslashes(($_POST['whatD'])),ENT_COMPAT, 'UTF-8'));
	
	if ($selectedUser == $_SESSION['pgID'] || PG::mapPermissions('SM',$currentUser->pgAuthOMA)){
		mysql_query("INSERT INTO fed_objects (oName,oDesc,oImage,oType) VALUES ('$what','$description','$image','PERSONAL')");
		echo mysql_error();
		mysql_query("INSERT INTO fed_objects_ownership (oID,owner) VALUES ((SELECT oID FROM fed_objects WHERe oName = '$what' ORDER BY oID DESC LIMIT 1),$user)");
		echo mysql_error();
	}
	header("Location:scheda.php?pgID=$user&s=oj");
	exit;
}

elseif($mode == 'remObj')
{
	$rem = $vali->numberOnly($_POST['remover']); 
	if ($selectedUser == $_SESSION['pgID'] || PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$obj=mysql_fetch_assoc(mysql_query("SELECT oID FROM fed_objects_ownership WHERE recID = $rem"));
		$objID = $obj['oID'];

		mysql_query("DELETE FROM fed_objects_ownership WHERE recID = $rem AND owner = $selectedUser");
		mysql_query("SELECT * FROM fed_objects_ownership WHERE oID = $objID");
		mysql_query("DELETE FROM pg_current_dotazione WHERE ref = $objID AND owner = $selectedUser AND type = 'OBJECT'");
		
		if (!mysql_affected_rows())
		{
			mysql_query("DELETE FROM fed_objects WHERE oID = $objID AND oType = 'PERSONAL'");
		}
	}
	
	header("Location:scheda.php?pgID=$selectedUser&s=oj");
	exit;
}


elseif($mode == 'movObj')
{
	
	$rem = $vali->numberOnly($_POST['mover']);

	$toUser = addslashes($_POST['userB']);
	$toUserQ= mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$toUser'"));
	$toUserID = $toUserQ['pgID'];
	

	if ($selectedUser == $_SESSION['pgID'] || PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$resae = mysql_query("SELECT * FROM fed_objects_ownership,fed_objects WHERE fed_objects_ownership.oID = fed_objects.oID AND owner = $selectedUser AND fed_objects_ownership.recID = $rem");
		if (mysql_affected_rows())
		{
			$obj=mysql_fetch_assoc($resae);
			$objID=$obj['oID'];

			$imago = ($obj['oLittleImage'] != '') ? $obj['oLittleImage'] : $obj['oImage'];

			mysql_query("UPDATE fed_objects_ownership SET owner = $toUserID WHERE owner = $selectedUser AND recID = $rem AND oID = $objID");
			mysql_query("DELETE FROM pg_current_dotazione WHERE ref = $objID AND owner = $selectedUser AND type = 'OBJECT'");

			$tu = new PG($toUserID);
			$usera=$selectedDUser->pgUser;
			$ojName = $obj['oName']; 
			$tu->sendNotification("Oggetto ricevuto","$usera ti ha inviato: $ojName",$selectedUser,$imago,'schedaOpen');
		}
	}
	
	header("Location:scheda.php?pgID=$selectedUser&s=oj");
	exit;
}

elseif($mode == 'addStory')
{
	$ranker = $vali->numberOnly($_POST['rankCode']);
	$dateG = str_pad($vali->numberOnly($_POST['dataG']),2,'0',STR_PAD_LEFT);
	$dateM = str_pad($vali->numberOnly($_POST['dataM']),2,'0',STR_PAD_LEFT);
	$dateA = $vali->numberOnly($_POST['dataA']);

	$dateFG = str_pad($vali->numberOnly($_POST['dataFG']),2,'0',STR_PAD_LEFT);
	$dateFM = str_pad($vali->numberOnly($_POST['dataFM']),2,'0',STR_PAD_LEFT);
	$dateFA = $vali->numberOnly($_POST['dataFA']);
	
	$storyEdit = $vali->numberOnly($_POST['storyEdit']);

	$dateDef = $dateA.'-'.$dateM.'-'.$dateG;
	
	$what = htmlentities(addslashes(($_POST['what'])));
	$where = addslashes(($_POST['where']));
	
	if ($selectedUser == $_SESSION['pgID'] || $currentUser->pgAuthOMA == 'A')
	{

		if($storyEdit)
		{
			$tu =mysql_query("SELECT pgID FROM pg_user_stories WHERE storyID = $storyEdit");
			if(mysql_affected_rows())
			{
				$te=mysql_fetch_assoc($tu);
				if($te['pgID'] == $_SESSION['pgID'] || PG::mapPermissions('SM',$currentUser->pgAuthOMA))
				{
					mysql_query("DELETE FROM pg_user_stories WHERE pgGroup = $storyEdit");
					mysql_query("UPDATE pg_user_stories SET rankImage =  (SELECT ordinaryUniform FROM pg_ranks WHERE prio = $ranker), dater='$dateDef', what='$what',wherer='$where', rankNAme=(SELECT Note FROM pg_ranks WHERE prio = $ranker),timeStamp=$curTime WHERE storyID = $storyEdit");
				}
			}
		}
		
		else
		{
		mysql_query("INSERT INTO pg_user_stories (pgID,rankImage,dater,what,wherer,rankName,timeStamp) VALUES ($selectedUser,(SELECT ordinaryUniform FROM pg_ranks WHERE prio = $ranker),'$dateDef','$what','$where',(SELECT Note FROM pg_ranks WHERE prio = $ranker),$curTime)");
		}

		if (($dateFG+$dateFM+$dateFA >0) & ($dateFA > $dateA) )
		{
			$ru=mysql_fetch_assoc(mysql_query("SELECT storyID FROM pg_user_stories WHERE pgID = $selectedUser AND timeStamp = $curTime ORDER BY dater DESC LIMIT 1"));
			$pgGroupCD=$ru['storyID'];

			foreach(range($dateA+1, $dateFA, 1) as $p)
			{
				$dateDef = $p.'-'.$dateFM.'-'.$dateFG;
				mysql_query("INSERT INTO pg_user_stories (pgID,rankImage,dater,what,wherer,rankName,pgGroup) VALUES ($selectedUser,(SELECT ordinaryUniform FROM pg_ranks WHERE prio = $ranker),'$dateDef','$what','$where',(SELECT Note FROM pg_ranks WHERE prio = $ranker),$pgGroupCD)");
			}
		}
	}
	
	header("Location:scheda.php?pgID=$selectedUser&s=ssto&l=1");
	exit;
}	

else if ($mode == 'removeStory')
{
	$w = $vali->numberOnly($_GET['sID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)){ mysql_query("DELETE FROM pg_user_stories WHERE (storyID = $w OR pgGroup = $w)"); }
	else mysql_query("DELETE FROM pg_user_stories WHERE (storyID = $w OR pgGroup = $w) AND pgID = ".$_SESSION['pgID']);
	header("Location:scheda.php?pgID=".$_SESSION['pgID']."&s=ssto&l=1");
	exit;
}

else if($mode == 'deleteNoteStaff'){

	$w = $vali->numberOnly($_GET['sID']);
	if ($currentUser->pgAuthOMA == 'A'){ 
		$etp=mysql_fetch_assoc(mysql_query("SELECT pgTo FROM pg_notestaff WHERE recID = '$w'"));
		$etpU=$etp['pgTo'];
		mysql_query("DELETE FROM pg_notestaff WHERE recID = '$w'");
		header("Location:scheda.php?pgID=".$etpU."&s=master#noteStaff");
	}
	exit;
}


elseif($mode == 'al')
{
	$template = new PHPTAL('TEMPLATES/scheda_alloggio.htm');
	
	$all = mysql_query("SELECT defaulta,locID,ambientLevel_deck, ambientNumber,placeName FROM pg_alloggi,fed_ambient,pg_places WHERE pg_alloggi.alloggio = fed_ambient.locID AND ambientLocation = placeID AND pgID = $selectedUser");
	$alloggi=array();
	while($allo = mysql_fetch_array($all)) $alloggi[] = $allo;
	
	$unita= PG::getSomething($selectedUser,"pgAlloggio");
	//$res = mysql_query("SELECT locName,`desc` FROM fed_ambient WHERE locID = '$unita'");
	
	//$resA=mysql_fetch_array($res);
	$template->alloggi = $alloggi;
	$template->desc = CDB::bbcode($unita['descrizione']);
	$template->locName = $unita['locName']; 
}

elseif($mode == 'resetCSS') { mysql_query("UPDATE pg_users SET parlatCSS = '', otherCSS = '' WHERE pgID = ".$_SESSION['pgID']); header("Location:scheda.php?pgID=$selectedUser&s=edit"); exit;}
elseif($mode == 'edit')
{
	if ($selectedUser == $_SESSION['pgID'] || $currentUser->pgAuthOMA == 'A')
	{
	$template = new PHPTAL('TEMPLATES/scheda_edit.htm');
	
	if(PG::getSomething($selectedUser,"lastBG"))
		$template->background = PG::getSomething($selectedUser,"lastBG");
	else $template->background = PG::getSomething($selectedUser,"BG");
	
	$template->prestavolto = PG::getSomething($selectedUser,"prestavolto");
	$allo = PG::getSomething($selectedUser,"pgAlloggio");
	$options = PG::getSomething($selectedUser,"optionsRec");
	$template->paddMail = $options['paddMail'];
	$template->email = $options['email'];
	
	$template->parlatCSS = ($options['parlatCSS'] != '') ? explode(';',$options['parlatCSS']) : array('13','#EEEEEE','#D7A436');
	//$template->actionCSS = ($options['actionCSS'] != '') ? explode(';',$options['actionCSS']) : array('12','#3188F3','#999');
	
	// Size User
	// Size Master
	// Size Comm
	// Colore User
	// Color Comm (User)
	// Color Comm Text
	
	
	$template->otherCSS = ($options['otherCSS'] != '') ? explode(';',$options['otherCSS']) : array('13','15','12','#999999','#e8a30e','#ffefcc','11','#d7a436','#FF0000','#FF0000','#c67729','#c67729');
	
	$template->audioEnable = $selectedDUser->audioEnable;
	$template->audioextEnable = $selectedDUser->audioextEnable;
	$template->audioEnvEnable = $selectedDUser->audioEnvEnable;
	$template->showAllo = (isSet($allo['descrizione'])) ? 'SI' : 'NO';
	$template->alloggio = $allo;
	} else
	{	
		header('Location:scheda.php');
		exit;
	}
}

elseif($mode == 'master')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)){Mailer::emergencyMailer("Tentativo di accesso a scheda master del pg $pgID",$currentUser); header('Location:scheda.php');} 
	
	$template = new PHPTAL('TEMPLATES/scheda_master.htm');
	$res = mysql_query("SELECT pgLock, pgSalute,rankCode,pgPrestige FROM pg_users WHERE pgID = $pgID");
	
	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places ORDER BY placeName");
	$resA = mysql_fetch_array($res);
	
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeID']] = $resLoc['placeName'];
	
	$ranks=array();
	$my = mysql_query("SELECT prio,Note,ordinaryUniform,aggregation FROM pg_ranks ORDER BY rankerprio DESC");
	while($myA = mysql_fetch_array($my))
	$ranks[$myA['aggregation']][$myA['prio']] = array('note' => $myA['Note'], 'ord' => $myA['ordinaryUniform']);
	//var_dump($ranks);exit;
	
	$all = mysql_query("SELECT defaulta,locID,ambientLevel_deck, ambientNumber,placeName FROM pg_alloggi,fed_ambient,pg_places WHERE pg_alloggi.alloggio = fed_ambient.locID AND ambientLocation = placeID AND pgID = $pgID");
	$alloggi=array();
	while($allo = mysql_fetch_array($all))
	{
		$alloggi[] = $allo;
	}
	
	if(PG::mapPermissions('MM',$currentUser->pgAuthOMA)){
	
		$res = mysql_query("SELECT png, email FROM pg_users WHERE pgID = $pgID");
		
		if(mysql_affected_rows()) $resB = mysql_fetch_array($res);
		else {header('Location:scheda.php'); exit;}
		
		$logQ = mysql_query("SELECT IP FROM connlog WHERE user = $pgID ORDER BY time DESC");
		
		$stringDoppi = "";
		$stringPNGDoppi = "";
		$stringPGPartial = "";
		$partiaLusers = array();
		$encountered = array();
		
		while($logQA = mysql_fetch_array($logQ))
		{
		
			if(!isSet($lastIP)) $lastIP = $logQA['IP'];
			if(in_array($logQA['IP'],$encountered)) continue;
			$encountered[] = $logQA['IP'];
			
			
			$logE = mysql_query("SELECT DISTINCT pgUser FROM pg_users,connlog WHERE pgID = user AND pgID <> $pgID AND (pgID <> 1762 AND pgID <> 1677) AND png=0  AND IP = '".$logQA['IP']."'");
			if(mysql_affected_rows()) $stringDoppi.= (PHP_EOL).' - '.$logQA['IP'];
			
			
			while ($logQE = mysql_fetch_array($logE))
			$stringDoppi.= ' '.$logQE['pgUser'].', ';
			

			$logE = mysql_query("SELECT DISTINCT pgUser FROM pg_users,connlog WHERE pgID = user AND pgID <> $pgID  AND png=1 AND IP = '".$logQA['IP']."'");
			if(mysql_affected_rows()) $stringPNGDoppi.= (PHP_EOL).' - '.$logQA['IP'];
			
			while ($logQE = mysql_fetch_array($logE))
			$stringPNGDoppi.= ' '.$logQE['pgUser'].', ';
			

		}

		$selectedDUser->getIncarichi();

		$allServiceObjects = array();
		
		$re=mysql_query("SELECT oID,oName FROM fed_objects WHERE oType = 'SERVICE'");
		while($res = mysql_fetch_assoc(($re)))
		{
			$allServiceObjects[] = $res;
		}


		
		$template->allServiceObjects = $allServiceObjects;
		
		$template->stringDoppi = $stringDoppi;
		$template->stringPNGDoppi = $stringPNGDoppi;
		$template->stringPGPartial = array(); //$stringPGPartial; 
		$template->ip =  $lastIP;
		$template->host =  gethostbyaddr($lastIP);
		$template->png = ($resB['png'] == 1) ? true : false;
		$template->email = $resB['email'];
		
	}


	$alloTypes = array();
	$rusA=mysql_query("SELECT alloName,placeName,typeID FROM pg_alloggi_types,pg_places WHERE location = placeID");
	while($rus = mysql_fetch_assoc($rusA)){
		if(!array_key_exists($rus['placeName'], $alloTypes))
			$alloTypes[$rus['placeName']] = array();
		$alloTypes[$rus['placeName']][] = $rus;
	}
	
	$template->alloTypes = $alloTypes;
	

	$atAdd = (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) ? 'AND secret = 0' : '';
	
	$resNote = mysql_query("SELECT pg_notestaff.*,pgUser FROM pg_notestaff,pg_users WHERE pgID = pgFrom AND pgTo = $pgID $atAdd ORDER BY recID DESC");
	$resNoteA = array();
	while ($til = mysql_fetch_assoc($resNote))
		$resNoteA[] = $til;


	//$resDoppi = mysql_query("SELECT pgUser,pgID,ordinaryUniform,pgPoints,pgType FROM pg_users,pg_ranks WHERE prio = rankCode AND pgID = $selectedUser");

	$resDoppi = mysql_query("SELECT pgUser,pgID,ordinaryUniform,pgPoints,pgType FROM pg_users,pg_ranks WHERE prio = rankCode AND mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID) ORDER BY rankerprio DESC");
	$resDoppiA = array();
	while ($til = mysql_fetch_assoc($resDoppi))
		$resDoppiA[] = $til;



	$res_extra=mysql_query("SELECT * FROM pg_extra_values WHERE pgID = $pgID");
	$res_extras=array();
	
	while($res_extra_val = mysql_fetch_assoc($res_extra))
	{
		$res_extras[$res_extra_val['key']]=$res_extra_val;
	}


	$res_auths=mysql_query("SELECT pg_users_temp_auths.*,pgUser,pg_users.pgID as signerPgID FROM pg_users_temp_auths,pg_users WHERE owner=pg_users.pgID AND pg_users_temp_auths.pgID = $pgID");
	$res_temp_auths=array();
	
	while($res_auths_val = mysql_fetch_assoc($res_auths))
	{
		$res_temp_auths[]=$res_auths_val;
	}

	$repli_food = mysql_query("SELECT foodName,timer, food as tr, ( SELECT COUNT(recID) FROM fed_food_replications WHERE food = tr AND user = $pgID AND YEAR(FROM_UNIXTIME(timer)) = $thisYear) as cnt FROM fed_food,fed_food_replications WHERE foodID = food AND user = $pgID  AND YEAR(FROM_UNIXTIME(timer)) = $thisYear ORDER BY timer DESC LIMIT 25");

	while($ftd = mysql_fetch_assoc($repli_food))
	{
		$replicated_foods[] = $ftd;
	}


	$repli_food_ov = mysql_fetch_assoc(mysql_query("SELECT COUNT(recID) as cnt FROM fed_food,fed_food_replications WHERE foodID = food AND user = $pgID AND YEAR(FROM_UNIXTIME(timer)) = $thisYear"));

	$template->ov_replications = $repli_food_ov['cnt'];

	$template->replicated_foods=$replicated_foods;
	
	$template->res_extras = $res_extras;
	$template->res_temp_auths = $res_temp_auths;

	$images = scandir('TEMPLATES/img/ruolini/lauree');
	$template->images=array_diff($images,array('.','..'));

	// $template->availBrevetti=$availBrevetti;
	
	$template->thisYear = $thisYear;
	$template->alloggi = $alloggi;
	$template->lock = $resA['pgLock'];
	$template->pgPrestige = $resA['pgPrestige'];
	$template->prestigioLabels = $prestigioLabels;
	$template->note = $resNoteA;
	$template->doppi = $resDoppiA;
	$template->ranks = $ranks;
	$template->rankCode = $resA['rankCode'];
	$template->saluteStatus = $resA['pgSalute'];
	$template->locations = $locArray;
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->bonusSM = 'show';
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->bonusA = 'show';
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->bonusM = 'show';
	if (PG::mapPermissions('SL',$currentUser->pgAuthOMA)) $template->bonusSL = 'show';
	
}

// elseif($mode=='kavanagh')
// { 
	// $namea = PG::getSomething($_GET['pgID'],'username');
	// mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",".$_GET['pgID'].",'::special::achiev','L\'ho sentita.::Evidentemente... la celebrità non è... tutto. Vero, signor $namea?',".time().",0,'http://miki.stfederation.it/minik.png')");
// }

elseif($mode=='edit_extras')
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)){header('Location:scheda.php'); exit;}

	foreach($_POST as $key=>$element)
	{
		$key_to_add=addslashes($key); 
		$val_to_add = $vali->numberOnly($element);

		mysql_query("SELECT 1 FROM pg_extra_values WHERE pg_extra_values.key = '$key_to_add' AND pgID = ".$_GET['pgID']);
		if(mysql_affected_rows())
		{
			mysql_query("UPDATE pg_extra_values SET value = $val_to_add WHERE pg_extra_values.key = '$key_to_add' AND pgID = ".$_GET['pgID']);
		} 
	}
	header('Location:scheda.php?s=master&pgID='.$_GET['pgID']);
	exit;
}
elseif($mode=='kavanagh')
{ 
	//$selectedDUser->preload_objects();


		
	$namea = PG::getSomething($_GET['pgID'],'username');
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",".$_GET['pgID'].",'::special::achiev','L\'ho sentita...::Chi siete? Dove andate? Un asciugamano!',".time().",0,'https://oscar.stfederation.it/SigmaSys/PNG/Kavanagh_001.png')");
	header('Location:scheda.php?pgID='.$_GET['pgID']);
	exit;
}

elseif($mode=='logout')
{
	if ((int)$_GET['pgID'] > 5)
	{
		mysql_query("UPDATE pg_users SET pgLastVisit = ".time().", pgLastAct = ".(time()-1801)." WHERE pgID = ".$_GET['pgID']);
	}
	header('Location:scheda.php?pgID='.$_GET['pgID']);
	exit;
}


elseif($mode=='matto')
{ 
	$namea = PG::getSomething($_GET['pgID'],'username');
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",".$_GET['pgID'].",'::special::achiev','Ehi tu...::Non fare il matto, dai...',".time().",0,'https://oscar.stfederation.it/SigmaSys/personal/hopkins/v22/giphy-downsized.gif')");
	header('Location:scheda.php?pgID='.$_GET['pgID']);
	exit;
}

// }
elseif($mode=='reminder') 
{ 
	$namea = PG::getSomething($_GET['pgID'],'username');
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",".$_GET['pgID'].",'::special::achiev','È ora di azionare!::',".time().",0,'https://oscar.stfederation.it/SigmaSys/PNG/Kavanagh_001.png')");
	header('Location:scheda.php?pgID='.$_GET['pgID']);
	exit;
}


elseif ($mode == 'ajax_getstats'){
	echo json_encode($selectedDUser->getStatsRecord());
	include('includes/app_declude.php');
	exit;
}
elseif ($mode == 'ajax_getactivityrecord'){
	echo json_encode($selectedDUser->getPlayRecord(28,'master'));
	include('includes/app_declude.php');
	exit;
}

elseif($mode == 'admin')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)){Mailer::emergencyMailer("Tentativo di accesso a scheda admin del pg $pgID",$currentUser); header('Location:scheda.php');}
	$template = new PHPTAL('TEMPLATES/scheda_admin.htm');
	$res = mysql_query("SELECT png, email,pgSSF,pgUpgradePoints,pgSpecialistPoints,pgPrestige,pgLastURI,pgType,mainPG FROM pg_users WHERE pgID = $pgID");
	
	if(mysql_affected_rows()) $resA = mysql_fetch_array($res);
	else {header('Location:scheda.php'); exit;}
		
	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places WHERE 1 ORDER BY placeName");
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeID']] = $resLoc['placeName'];
	
	$res = mysql_query("SELECT medID,medName,medgroup FROM pg_medals WHERE 1 ORDER BY medPrio ASC");
	$nasArray=array();
	while($resD = mysql_fetch_array($res))
	{
		if (!array_key_exists($resD['medgroup'], $nasArray))
			$nasArray[$resD['medgroup']] = array($resD);
		else			
			$nasArray[$resD['medgroup']][] = $resD;
	}
	ksort($nasArray);
	
	$lasts = array();
	
	$e= mysql_query("SELECT chat,time FROM  federation_chat WHERE  sender = $pgID ORDER BY time DESC LIMIT 1");
	while ($d = mysql_fetch_array($e))
	$lasts['chat'] = array(date("d M H:i:s",$d['time']),$d['chat'],'Ultima Chat');
	 
	$e= mysql_query("SELECT * FROM  fed_sussurri WHERE  susFrom = $pgID ORDER BY time DESC LIMIT 1");
	while ($d = mysql_fetch_array($e))
	$lasts['sussurro'] = array(date("d M H:i:s",$d['time']),$d['chat'],'Ultimo Sussurro'); 
	
	$e= mysql_query("SELECT * FROM  connlog WHERE  user = $pgID ORDER BY time DESC LIMIT 1");
	while ($d = mysql_fetch_array($e))
	$lasts['conn'] = array(date("d M H:i:s",$d['time']),$d['IP'],'Ultimo Login'); 
	
	$e= mysql_query("SELECT * FROM  cdb_posts WHERE  owner = $pgID OR coOwner = $pgID ORDER BY time DESC LIMIT 1");
	while ($d = mysql_fetch_array($e))
	$lasts['posts'] = array(date("d M H:i:s",$d['time']),$d['title'],'Ultimo Post');
	
	$e= mysql_query("SELECT * FROM  fed_pad WHERE  paddFrom = $pgID ORDER BY paddTime DESC LIMIT 1");
	while ($d = mysql_fetch_array($e))
	$lasts['padds'] = array(date("d M H:i:s",$d['paddTime']),$d['paddTitle'],'Ultimo Padd Inviato'); 
	
	$e= mysql_query("SELECT COUNT(*) as C FROM  cdb_posts WHERE owner = $pgID");
	while ($d = mysql_fetch_array($e))
	$lasts['posts'] = array($d['C'],'-','Numero Posts'); 
	
	$e= mysql_query("SELECT AVG(LENGTH(chat)) as C FROM  federation_chat WHERE sender = $pgID AND type IN ('DIRECT','ACTION')");
	while ($d = mysql_fetch_array($e))
	$lasts['padd'] = array($d['C']*0.65,'-','Lunghezza Azioni (AVG)'); 
	
	$lasts['pgLastURI'] = array(date('d M H:i:s',$selectedDUser->pgLastAct),$resA['pgLastURI'],'LAST URL');

	$mainPG=$resA['mainPG'];
	if(!$mainPG) $mainPG=$selectedUser;

        $mainPGRecQ=mysql_query("SELECT pgUser, ordinaryUniform, pgID, pgSesso, pgSpecie,mainPG FROM pg_users,pg_ranks WHERE rankCode = prio AND pgID = $mainPG LIMIT 1" );
	
	if (mysql_affected_rows())
		$mainPGRec=mysql_fetch_assoc($mainPGRecQ);
	else
                $mainPGRec=array();

	//$e= mysql_query("SELECT pgUser,points,cause,causeE,causeM,timer FROM pg_users_pointStory,pg_users WHERE pgID = assigner AND owner = $pgID AND cause LIKE '%DISP%' ORDER BY timer DESC LIMIT 50");	
	//$pstory = array();
	//while ($d = mysql_fetch_array($e))
	//$pstory[] = $d;	
	
	$template->thisYear = $thisYear+$bounceYear;
	$template->lasts = $lasts;
	//$template->pstory = $pstory;
	$template->mainPGRec=$mainPGRec;
	$template->nastrini = $nasArray;
	$template->pgUpgradePoints = $resA['pgUpgradePoints'];
	$template->pgSpecialistPoints = $resA['pgSpecialistPoints']; 
	$template->locations = $locArray;
	$template->png = ($resA['png'] == 1) ? true : false;
	$template->email = $resA['email'];
	$template->pgSSF = $resA['pgSSF']; 
	$template->pgType = $resA['pgType'];
	$template->isMasCapableEnable = PG::isMasCapable($pgID);

	$carObj = new abilDescriptor($pgID);
	$template->caratt = $carObj->getCars();

	
}

elseif ($mode == 'editS')
{
	$ediID = $_POST['ediID'];

	$alloID = PG::getSomething($ediID,"pgAlloggioRealID");
	$ediName = addslashes($_POST['ediNome']);
	$ediSuff = addslashes($_POST['ediSuff']);
	$ediLuoN =addslashes($_POST['ediLuoN']);
	$ediDataN = addslashes($_POST['ediDataN']);
	$ediAvatar = addslashes($_POST['ediAvatar']);
	$ediAvatarSquare = addslashes($_POST['ediAvatarSquare']);
	
	$ediFis = addslashes($_POST['ediFis']);
	$ediBack = addslashes($_POST['ediBack']);
	$ediCarat = addslashes($_POST['ediCarat']);
	$ediFamil = addslashes($_POST['ediFamil']);
	$ediVarie = addslashes($_POST['ediVarie']);
	$ediIlSegreto = addslashes($_POST['ediIlSegreto']);
	
	
	$ediAllo = isSet($_POST['ediAllo']) ? addslashes($_POST['ediAllo']) : '';
	$ediStaCiv = addslashes($_POST['ediStaCiv']);
	$pgOffAvatarN = addslashes($_POST['pgOffAvatarN']);
	$pgOffAvatarC = addslashes($_POST['pgOffAvatarC']);
	$audioEnableSet = (isSet($_POST['audioextEnableSet']) && isSet($_POST['audioEnableSet'])) ? 2 : (isSet($_POST['audioEnableSet']) ? 1 : 0);
	$paddMail = isSet($_POST['ediMailVali']) ? 1 : 0;
	$audioEnvEnableSet = isSet($_POST['audioEnvEnableSet']) ? 1 : 0;
	
	//CustomCSS
	$parlatCSS = $vali->numberOnly($_POST['parlatCSSFontSize']).';'.addslashes($_POST['parlatCSSFontColor']).';'.addslashes($_POST['parlatCSSFontColorEscape']);
	#$actionCSS = $vali->numberOnly($_POST['actionCSSFontSize']).';'.addslashes($_POST['actionCSSFontColor']).';'.addslashes($_POST['actionCSSFontColorEscape']);
	//$actionCSS = '';

	$otherCSS = $vali->numberOnly($_POST['otherCSSSizeUser']).';'.$vali->numberOnly($_POST['otherCSSSizeMaster']).';'.$vali->numberOnly($_POST['otherCSSSizeComm']).';'.addslashes($_POST['otherCSSColorUser']).';'.addslashes($_POST['otherCSSColorCommUser']).';'.addslashes($_POST['otherCSSColorCommText']).';'.$vali->numberOnly($_POST['otherCSSSizeTag']).';'.addslashes($_POST['otherCSSColorTag']).';'.addslashes($_POST['otherCSSColorMaster']).';'.addslashes($_POST['otherCSSColorMasterBorder']).';'.addslashes($_POST['otherCSSColorSpecMaster']).';'.addslashes($_POST['otherCSSColorSpecMasterBorder']);
	
 
	mysql_query("UPDATE pg_users SET pgNomeC = '$ediName', paddMail=$paddMail,audioEnvEnable = $audioEnvEnableSet, audioEnable = $audioEnableSet, pgNomeSuff = '$ediSuff', pgLuoN = '$ediLuoN', pgDataN = '$ediDataN', pgAvatar = '$ediAvatar', pgAvatarSquare = '$ediAvatarSquare' ,pgOffAvatarN = '$pgOffAvatarN',pgOffAvatarC = '$pgOffAvatarC', pgStatoCiv = '$ediStaCiv', parlatCSS = '$parlatCSS', otherCSS = '$otherCSS' WHERE pgID = $ediID");
	

	
	$ral=mysql_fetch_assoc(mysql_query("SELECT png FROM pg_users WHERE pgID = $ediID"));
	if ($ral['png']){
		$validity=2;
		mysql_query("DELETE FROM pg_users_bios WHERE valid = 2 AND pgID = '$ediID'");

	}
	else{
		$validBG = PG::getSomething($ediID,"BG");
		if ($validBG != NULL && $validBG['pgBiometrics'] == stripslashes($ediFis) && $validBG['pgIlSegreto'] == stripslashes($ediIlSegreto) && $validBG['pgBackground'] == stripslashes($ediBack) && $validBG['pgCarattere'] == stripslashes($ediCarat) && $validBG['pgFamily'] == stripslashes($ediFamil) && $validBG['pgVarie'] == stripslashes($ediVarie))
		{
			$validity=2;	
			mysql_query("DELETE FROM pg_users_bios WHERE valid = 2 AND pgID = '$ediID'");

		}
		else
		{
			mysql_query("DELETE FROM pg_users_bios WHERE valid <= '1' AND pgID = '$ediID'");
			if ($validBG != NULL)
				$validity=1;
			else 
				$validity=0;
		}
	}
	

	mysql_query("INSERT INTO pg_users_bios (pgID,pgBiometrics,pgIlSegreto,pgBackground,pgCarattere,pgFamily,pgVarie,valid,tim,edituser) VALUES ($ediID,'$ediFis','$ediIlSegreto','$ediBack','$ediCarat','$ediFamil','$ediVarie',$validity,$curTime,".$currentUser->ID.")");


	if(isSet($_POST['ediAllo'])) mysql_query("UPDATE fed_ambient SET descrizione = '$ediAllo' WHERE locID = '$alloID'");
	
	

	//echo "UPDATE pg_users SET pgNomeC = '$ediName', pgNomeSuff = '$ediSuff', pgLuoN = '$ediLuoN', pgDataN = '$ediDataN', pgAvatar = '$ediAvatar', pgAuth ='$ediAuth', pgScheda = '$ediBack'<br />".mysql_error();
	//exit;
	header("Location:scheda.php?pgID=$ediID");
	exit;
}

elseif ($mode == 'lock')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SL',$currentUser->pgAuthOMA))
	{	mysql_query("UPDATE pg_users SET pgLock = !pgLock WHERE pgID = $pgID");
		$pgIDU = PG::getSomething($pgID,'username');
		Mailer::notificationMail("Il PG $pgIDU e' stato bloccato o sbloccato",$currentUser);
	}
	header("Location:scheda.php?pgID=$pgID&s=master");
	exit;
}

elseif ($mode == 'curse')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{	mysql_query("UPDATE pg_users SET pgSSF = !pgSSF WHERE pgID = $pgID");
		$pgIDU = PG::getSomething($pgID,'username');
		Mailer::notificationMail("Il PG $pgIDU e' stato maledetto o smaledetto",$currentUser);
	}
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}

elseif ($mode == 'ban' || $mode == 'sban')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$ma = ($mode == 'ban') ? 'BAN' : 'N';
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{	mysql_query("UPDATE pg_users SET pgAuthOMA = '$ma' WHERE pgID = $pgID");
		$pgIDU = PG::getSomething($pgID,'username');
		Mailer::notificationMail("Il PG $pgIDU e' stato bannato o sbannato",$currentUser);
	}
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}

elseif ($mode == 'png')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	
		if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET png = !png WHERE pgID = $pgID");
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}


elseif ($mode == 'setSalute')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$sal = addslashes($_POST['medStatus']);
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET pgSalute = '$sal' WHERE pgID = $pgID");
	if(!mysql_error()) 
	{	
		$toPG = new PG($pgID);
		$toPG->sendNotification("Nuovo stato di Salute",$_POST['medStatus'],$_SESSION['pgID'],"https://oscar.stfederation.it/SigmaSys/logo/nl_med.r.png",'schedaMedOpen');

	}

	header("Location:scheda.php?pgID=$pgID&s=me");
	exit;
}

elseif ($mode == 'document')
{

	$pgID = $vali->numberOnly($_GET['pgID']);
	$pgNew = new PG($pgID);

	$pgNew->sendWelcomePadd();
	
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'deleteAllo')
{
	$alloLocation = addslashes($_GET['loc']);
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SL',$currentUser->pgAuthOMA))
	{
		mysql_query("DELETE FROM pg_alloggi WHERE alloggio = '$alloLocation' AND pgID = $pgID");
		mysql_query("DELETE FROM fed_ambient WHERE locID = '$alloLocation' AND locID NOT IN (SELECT alloggio FROM pg_alloggi WHERE alloggio = '$alloLocation')");
	}
	header("Location:scheda.php?pgID=$pgID&s=master");
	exit;
}
elseif ($mode == 'setDefAllo')
{
	$alloLocation = addslashes($_GET['loc']);
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SL',$currentUser->pgAuthOMA))
	{
		mysql_query("UPDATE pg_alloggi SET defaulta = 0 WHERE pgID = '$pgID'");
		mysql_query("UPDATE pg_alloggi SET defaulta = 1 WHERE alloggio = '$alloLocation'");
	}
	header("Location:scheda.php?pgID=$pgID&s=master");
	exit;
}

elseif ($mode == 'creAllo')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$alloLocation = addslashes($_POST['alloLocation']);
	$alloDeck = addslashes($_POST['alloDeck']);
	$num = $vali->numberOnly($_POST['alloNum']);
	$alloType = $vali->numberOnly($_POST['alloType']);

	
	$locID = "ALL_".time();
	$real=mysql_query("SELECT 1 FROM pg_alloggi WHERE pgID = $pgID");
	$defa = (mysql_affected_rows()) ? 0 : 1;
	
	
	if (PG::mapPermissions('SL',$currentUser->pgAuthOMA))
	{
		$c = mysql_query("SELECT locID FROM fed_ambient WHERE ambientLocation='$alloLocation' AND ambientNumber = $num AND ambientLevel_deck = $alloDeck");
		

		if(!mysql_affected_rows()){

			if ($alloType > 0){
				

				$ras=mysql_query("SELECT * FROM pg_alloggi_types WHERE typeID = $alloType");
				if (mysql_affected_rows())
				{
					$rasA = mysql_fetch_assoc($ras);
					if ($rasA['ploidy'] > 1)
						$alloName = "Alloggio $num - Ponte $alloDeck";
					else
						$alloName = addslashes("Alloggio  $num - ".PG::getSomething($pgID,'username'));

					$alloDescript=addslashes($rasA['alloDescript']);
					$alloImage='TEMPLATES/img/ambients/alloggi/' . addslashes($rasA['alloImage']);

					mysql_query("INSERT INTO fed_ambient (locID,locName,ambientLocation,ambientLevel_deck,ambientType,ambientNumber,image,locationable,descrizione) VALUES ('$locID','$alloName', '$alloLocation', '$alloDeck','ALLOGGIO',$num,'$alloImage',0,'$alloDescript')");
					
				}
			}


		} 
		else {
			$lo = mysql_fetch_array($c); 
			$locID = $lo['locID'];
			$newName = "Alloggio $num - Ponte $alloDeck";
			mysql_query("UPDATE fed_ambient SET locName = '$newName',locationable=0 WHERE locID = '$locID'");
			}

		mysql_query("SELECT 1 FROM pg_alloggi WHERE pgID = $pgID AND alloggio = '$locID'");
		if(!mysql_affected_rows()) mysql_query("INSERT INTO pg_alloggi (pgID, alloggio,defaulta) VALUES ($pgID, '$locID',$defa)");
	}
	
	header("Location:scheda.php?pgID=$pgID&s=master");
	exit;
}

elseif ($mode == 'delIncarico'){

	$recID = $vali->numberOnly($_GET['recID']);
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		mysql_query("DELETE FROM pg_incarichi WHERE recID = '$recID'"); 
	header("Location:scheda.php?pgID=$pgID&s=master#setIncarichi");
	exit;

}

elseif ($mode == 'togglePrincIncarico'){

	$recID = $vali->numberOnly($_GET['recID']);
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_incarichi SET incMain = !incMain WHERE recID = '$recID'");
	header("Location:scheda.php?pgID=$pgID&s=master#setIncarichi");
	exit;
}

elseif ($mode == 'toggleHighIncarico'){

	$recID = $vali->numberOnly($_GET['recID']);
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_incarichi SET incHigh = !incHigh WHERE recID = '$recID'");
	header("Location:scheda.php?pgID=$pgID&s=master#setIncarichi");
	exit;
}

elseif ($mode == 'toggle_bavosize'){

	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET pgBavo = !pgBavo WHERE pgID = '$pgID'"); 
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;


}


elseif ($mode == 'toggleActIncarico'){

	$recID = $vali->numberOnly($_GET['recID']);
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_incarichi SET incActive = !incActive WHERE recID = '$recID'");
		mysql_query("UPDATE pg_incarichi SET incMain = incActive WHERE recID = '$recID' AND incActive=0");
	header("Location:scheda.php?pgID=$pgID&s=master#setIncarichi");
	exit;
}

elseif ($mode == 'setIncarico')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$incMain = (isSet($_POST['incMain'])) ? 1 : 0; 
	$assegnazione = addslashes($_POST['assegnazione']);
	$incIncarico = addslashes($_POST['incIncarico']);
	$incDivisione = addslashes($_POST['incDivisione']);
	$incSezione = addslashes($_POST['incSezione']);
	
	$incDipartimento = addslashes($_POST['incDipartimento']);
	$incGroup = addslashes($_POST['incGroup']);

	 
	$dateDef = $bounceYear+date('Y',$curTime).'-'.date('n',$curTime).'-'.date('j',$curTime);

	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{	
		mysql_query("UPDATE pg_users SET pgAssign = '$assegnazione' WHERE pgID ='$pgID'");
		if($incMain)
			mysql_query("UPDATE pg_incarichi SET incMain = 0 WHERE pgID = '$pgID'");
		
		mysql_query("INSERT INTO pg_incarichi (pgID,incIncarico,incSezione,incDivisione,incDipartimento,incGroup,pgPlace,incMain) VALUES('$pgID','$incIncarico','$incSezione','$incDivisione','$incDipartimento','$incGroup','$assegnazione','$incMain')");

		mysql_query("INSERT INTO pg_user_stories (pgID,rankImage,dater,what,wherer,rankName) VALUES ('$pgID',(SELECT ordinaryUniform FROM pg_ranks,pg_users WHERE prio = rankCode AND pgID = '$pgID'),'$dateDef','$incIncarico',(SELECT placeName FROM pg_places WHERE placeID = '$assegnazione'),(SELECT Note FROM pg_ranks,pg_users WHERE prio = rankCode AND pgID = '$pgID'))");
 
		
	}
	header("Location:scheda.php?pgID=$pgID&s=master#setIncarichi");
	exit;
}

elseif ($mode == 'spesex')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$sesso = addslashes($_POST['sesso']);
	$specie = addslashes($_POST['specie']);
	
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET pgSpecie = '$specie', pgSesso = '$sesso' WHERE pgID ='$pgID'");
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'addDotazione')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$tipologia = addslashes($_POST['tipologia']);
	$ima = addslashes($_POST['image']);
	$text = addslashes($_POST['testo']);
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		mysql_query("INSERT INTO pgDotazioni (pgID,dotazioneIcon,doatazioneType,dotazioneAlt) VALUES ($pgID,'$ima','$tipologia','$text')");
	header("Location:scheda.php?pgID=$pgID&s=bv");
	exit;
}

elseif ($mode == 'remDotazione')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$ider = $vali->numberOnly($_GET['ider']);
	$item = $_GET['type'];
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		
		if($item == 'medal' && PG::mapPermissions('A',$currentUser->pgAuthOMA))
			mysql_query("DELETE FROM pgDotazioni WHERE doatazioneType = 'MEDAL' AND pgID = $pgID AND recID = $ider");
		elseif($item == 'degree')
			mysql_query("DELETE FROM pgDotazioni WHERE doatazioneType = 'LAUR' AND pgID = $pgID AND recID = $ider");
		elseif($item == 'nota')
			mysql_query("DELETE FROM pgDotazioni WHERE doatazioneType = 'NOTA' AND pgID = $pgID AND recID = $ider");

	}
		
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'invaliMail')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	
	$t = '_'.substr(time(),6);
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	mysql_query("UPDATE pg_users SET email = CONCAT('$t',email) WHERE pgID = $pgID");
		
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}

elseif ($mode == 'editCar')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
		$a = new abilDescriptor($pgID);
		$a->superSet(
			array(
				array('IQ',$vali->numberOnly($_POST['edi_IQ'])),
				array('DX',$vali->numberOnly($_POST['edi_DX'])),
				array('HT',$vali->numberOnly($_POST['edi_HT'])),
				array('PE',$vali->numberOnly($_POST['edi_PE'])),
				array('WP',$vali->numberOnly($_POST['edi_WP']))
			)
		);
	}
		
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}


elseif ($mode == 'resetandeditCar')
{
	$pgID = $vali->numberOnly($_GET['pgID']);



	$initial=array(
				'IQ' => $vali->numberOnly($_POST['edi_IQ']),
				'DX' => $vali->numberOnly($_POST['edi_DX']),
				'HT' => $vali->numberOnly($_POST['edi_HT']),
				'PE' => $vali->numberOnly($_POST['edi_PE']),
				'WP' => $vali->numberOnly($_POST['edi_WP'])
			);
 

	if ( PG::mapPermissions('A',$currentUser->pgAuthOMA) )
	{
		$a = new abilDescriptor($pgID);
		$a->resetAndRestore($initial);
		header("Location:scheda.php?pgID=$pgID&s=admin");
		exit;
	}
	elseif( ($currentUser->ID == $pgID) && (array_sum($initial) == 23) )
	{
			mysql_query("SELECT 1 FROM pg_users_bios WHERE pgID = $pgID AND valid = 2");
			if(!mysql_affected_rows())
			{
				$a = new abilDescriptor($pgID);
				$a->resetAndRestore($initial);
				header("Location:scheda.php?pgID=$pgID&s=bv");
				exit;
			}
	}
}




elseif ($mode == 'resetAbilTotal')
{ 
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
		$a = new abilDescriptor($selectedDUser->ID);
		$selectedDUser->addNote('Reset Totale Abilità',$currentUser->ID);

		$a->reset();
	}
	header("Location:scheda.php?pgID=".$selectedDUser->ID."&s=admin");
	exit;
}

elseif ($mode == 'resetAbilToRace')
{
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
		$pg = $selectedDUser;
		$pgID = $pg->ID;

		$a = new abilDescriptor($pg->ID);

		$a->superImposeRace($pg->pgSpecie);
		$selectedDUser->addNote("Caricamento Profilo di Razza: ".$pg->pgSpecie,$currentUser->ID);
		

	}
		
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}

elseif ($mode == 'refirst')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	
	$t = '_'.substr(time(),6);
	mysql_query("UPDATE pg_users SET pgFirst = 2 WHERE pgID = $pgID");
		
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}

elseif ($mode == 'setAutoma')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$aut = addslashes($_POST['aut']);
	
	if($aut == 'A'){ $selectedDUser->sendPadd('OFF: BECCATO!',"Allarmi! Allarmi! Un admin verrà avvisato della violazione!"); exit;}

	if($aut == 'O') $selectedDUser->sendPadd('OFF: Entertainer',"Ti è stato assegnato il ruolo di Entertainer / Olomaster, e hai ora la possibilità di inserire esiti in tutti gli ambienti del tipo Sala Ologrammi, utilizzando il comando:
	
	[COLOR=RED]-[/COLOR] Esito (per inviare un esito testuale)
	[COLOR=RED]*[/COLOR] URL immagine (per inviare un'immagine)
	
	Ulteriori informazioni sono disponibili qui:
	&raquo;  [POST]13688[/POST]

	Un grande in bocca al lupo!
	Lo Staff");
	
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA)){
		mysql_query("UPDATE pg_users SET pgAuthOMA = '$aut' WHERE pgID ='$pgID'");
		$pgIDU = PG::getSomething($pgID,'username');
		Mailer::notificationMail("Il PG $pgIDU e' stato passato a: $aut",$currentUser);
		}
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'setLocation')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$loc = addslashes($_POST['location']);
	
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET pgLocation = '$loc', pgRoom = '$loc' WHERE pgID ='$pgID'");
	header("Location:scheda.php?pgID=$pgID&s=admin");
	exit;
}

elseif ($mode == 'setUsername')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$user = addslashes($_POST['username']);
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
		{
			mysql_query("UPDATE pg_users SET pgUser = '$user' WHERE pgID ='$pgID'");
			$selectedDUser->addNote("Modifica Username in $user",$currentUser->ID);
		}

	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'setSeclar')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$setSeclar = $_POST['setSeclar'];
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		{
			mysql_query("UPDATE pg_users SET pgSeclar = $setSeclar WHERE pgID ='$pgID'");
				$pgIDU = PG::getSomething($pgID,'username');
				Mailer::notificationMail("Il PG $pgIDU e' stato seclarizzato a $setSeclar",$currentUser);
				$selectedDUser->addNote("Assegnazione di SECLAR $setSeclar",$currentUser->ID);
		}
	header("Location:scheda.php?pgID=$pgID");
	exit;
}
elseif ($mode == 'setGrado')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$grado = addslashes($_POST['grado']);
	$sezione = addslashes($_POST['sezione']);
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		mysql_query("UPDATE pg_users SET pgGrado = '$grado', pgSezione = '$sezione' WHERE pgID ='$pgID'");
		//Mailer::notificationMail("Il PG $pgID e' stato passato a $grado - $sezione",$currentUser);
	}
		
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'setRanker')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$grado = $vali->numberOnly($_POST['rankCode']);
	
	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	PG::setMostrina($pgID,$grado);
	$pgIDU = PG::getSomething($pgID,'username');
	Mailer::notificationMail("Il PG $pgIDU e' stato promosso o degradato a $grado",$currentUser);
	$selectedDUser->addNote("Modifica del RankCode ($grado)",$currentUser->ID);
	
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'setNastrini')
{
$pgID = $vali->numberOnly($_GET['pgID']);
$nastrini = $vali->numberOnly($_POST['nastrini']);
$dataA = $vali->numberOnly($_POST['dataA']);

	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{	
		$selectedDUser->addMedal($nastrini,$dataA);
		$selectedDUser->addNote("Aggiunta medaglia $nastrini",$currentUser->ID);
	}
		
	header("Location:scheda.php?pgID=$pgID");
	exit;
}

elseif ($mode == 'addNote')
{ 
	$note = $vali->killChars($_POST['note']);
//$note = str_replace('FOL/','TEMPLATES/img/ruolini/medaglie/',$nastrini);

	if (PG::mapPermissions('SL',$currentUser->pgAuthOMA))
		$selectedDUser->addNote($note,$currentUser->ID);

	header("Location:scheda.php?pgID=".$selectedDUser->ID."&s=master");
	exit;
}

/*elseif ($mode == 'toggleMasCap')
{
$pgID = $vali->numberOnly($_GET['pgID']);
//$note = str_replace('FOL/','TEMPLATES/img/ruolini/medaglie/',$nastrini);

	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
		mysql_query("UPDATE pg_users SET isMasCapable = !isMasCapable WHERE pgID = $pgID");
	header("Location:scheda.php?pgID=$pgID&s=admin");
}*/

elseif ($mode == 'setUPPoints')
{
$pgID = $vali->numberOnly($_GET['pgID']);
$points = $vali->numberOnly($_POST['points']);
if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
		mysql_query("UPDATE pg_users SET pgUpgradePoints = $points WHERE pgID = $pgID");
		$selectedDUser->addNote("Assegnazione di $points UP",$currentUser->ID);	
	}
header("Location:scheda.php?pgID=$pgID&s=admin");
}

elseif ($mode == 'setSpecialistPoints')
{
$pgID = $vali->numberOnly($_GET['pgID']);
$points = $vali->numberOnly($_POST['points']);
if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
		mysql_query("UPDATE pg_users SET pgSpecialistPoints = $points WHERE pgID = $pgID");
		$selectedDUser->addNote("Assegnazione di $points Lucky Points",$currentUser->ID);	
	}
header("Location:scheda.php?pgID=$pgID&s=admin");
exit;
}

elseif ($mode == 'setMainPG')
{
$pgID = $vali->numberOnly($_GET['pgID']);
$targetPG = addslashes($_POST['targetPGID']);
$pgIDRole = addslashes($_POST['pgIDRole']);
//$propagate = $vali->numberOnly($_POST['propagate']);


if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
{
	$rea=mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$targetPG' LIMIT 1");
	if (mysql_affected_rows())
	{
		$resPG=mysql_fetch_assoc($rea);
		$targetPGID = $resPG['pgID'];
		mysql_query("UPDATE pg_users SET mainPG = $targetPGID, pgType = '$pgIDRole' WHERE pgID = $pgID");
	}
}
		
	
header("Location:scheda.php?pgID=$pgID&s=admin");
exit;
}



elseif ($mode == 'setPrestige')
{
$pgID = $vali->numberOnly($_GET['pgID']);
$points = $vali->numberOnly($_POST['points']);
$reason = addslashes($_POST['reason']);

if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$curLevel = mysql_fetch_assoc(mysql_query("SELECT pgPrestige FROM pg_users WHERE pgID = $pgID"));
		$differential = $points-(int)$curLevel['pgPrestige'];
		
		if($reason != "")
			mysql_query("INSERT INTO pg_prestige_stories (owner,time,reason,variation) VALUES($pgID,$curTime,'$reason',$differential)");	

		mysql_query("UPDATE pg_users SET pgPrestige = $points WHERE pgID = $pgID");
		$selectedDUser->addNote("Prestigio modificato ($points)",$currentUser->ID);	
		$selectedDUser->sendPadd('OFF: Notorietà',"Il tuo livello di notorietà è cambiato in [COLOR=YELLOW][B]".$prestigioLabels[$points]['name']."[/COLOR][/B] per la seguente ragione: [B]\"$reason\"[/B].

			[COLOR=YELLOW][B]".$prestigioLabels[$points]['name']."[/COLOR][/B]:
			[I]".$prestigioLabels[$points]['long_desc']."[/I] ",$_SESSION['pgID']);
	}
header("Location:scheda.php?pgID=$pgID&s=master");
}

elseif ($mode == 'addServiceObject')
{ 
$oID = $vali->numberOnly($_POST['oID']);

if(PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES ($oID,$selectedUser)");

header("Location:scheda.php?pgID=$selectedUser&s=master");
exit;
}


else if($mode== 'assignAchi')
{
$pgID = $vali->numberOnly($_GET['pgID']);
$achi = $vali->numberOnly($_GET['achi']);

$ra = mysql_query("SELECT 1 FROM pg_achievement_assign WHERE owner = $pgID AND achi = $achi");

if(PG::mapPermissions('A',$currentUser->pgAuthOMA))
{

	if(!mysql_affected_rows())
	{
	mysql_query("INSERT INTO pg_achievement_assign (owner,achi,timer) VALUES ($pgID,$achi,".time().")");
	
	$res = mysql_query("SELECT aText,aImage FROM pg_achievements WHERE aID = $achi");
	$resA = mysql_fetch_array($res);
	$Descri =$resA['aText'];
	$ima =$resA['aImage'];
	
	$cString = addslashes("Congratulazioni!!<br />Hai sbloccato un nuovo achievement!<br /><br /><p style='text-align:center'><img src='TEMPLATES/img/interface/personnelInterface/$ima' /><br /><span style='font-weight:bold'>$Descri</span></p><br />Il Team di Star Trek: Federation");
	$eString = addslashes("Hai un nuovo achievement!::$Descri");
	
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,paddType) VALUES (".$_SESSION['pgID'].",$pgID,'OFF: Nuovo Achievement!','$cString',".time().",0,1)");
 
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",$pgID,'::special::achiev','$eString',".time().",0,'TEMPLATES/img/interface/personnelInterface/$ima')");
	
	}
	else{
		#WOLO
		mysql_query("DELETE FROM pg_achievement_assign WHERE achi = $achi AND owner IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID))");
	}
}
	header("Location:scheda.php?pgID=$pgID&sOff=off");
	exit;
}

elseif ($mode == 'addPoints')
{
	$pgID = $vali->numberOnly($_GET['pgID']);
	$code = $vali->killChars($_POST['addPoints']);
	$pointDetail = addslashes($_POST['pointDetail']);
	//$note = str_replace('FOL/','TEMPLATES/img/ruolini/medaglie/',$nastrini);
	$p=0;$l="A";
	//if($code == "a1"){$p=1;$little="Q1";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a2"){$p=2;$little="Q2";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "f2"){$p=2;$little="F2";$mex = "Punti Minishot";$l="SL";}
	//if($code == "a3"){$p=3;$little="Q3";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a4"){$p=4;$little="Q4";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a5"){$p=5;$little="Q5";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a6"){$p=6;$little="Q6";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a7"){$p=7;$little="Q7";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a8"){$p=8;$little="Q8";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a9"){$p=9;$little="Q9";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "a10"){$p=10;$little="Q10";$mex = "Partecipazione Giocata";$l="SL";}
	//if($code == "b5"){$p=5;$little="B1";$mex = "Mastering One Shot";$l="SL";}
	//
	//elseif($code == "r2"){$p=2;$little="R";$mex = "Stesura Rapporto";$l="SL";}

	if($code == "kz1"){$p=20;$little="DISP";$mex = "Piccola Integrazione";$l="SM";}
	elseif($code == "kz2"){$p=50;$little="DISP+";$mex = "Dispensa Completa";$l="SM";}
	elseif($code == "aa11")
	{
		$p=(int)$_POST['points'];
		if($p > 0){$little="Q00"; $mex = 'Punteggio Admin'; $l="A"; $p=$vali->numberOnly($_POST['points']);}
		if($p <= 0){$little="QDEC"; $mex = 'Decurtazione di Punteggio'; $l="A"; $p=0-$vali->numberOnly($_POST['points']);}
	}

	if (PG::mapPermissions($l,$currentUser->pgAuthOMA)) // && $_SESSION['pgID'] != $pgID)
	{
		$pointsPre = PG::getSomething($pgID,'totalPoints');
		
		if($little == 'Q00'){$selectedDUser->sendPadd('OFF: FP',"Ti sono stati assegnati $p punti extra con la seguente motivazione: \"$pointDetail\".",$_SESSION['pgID']);}
		if($little == 'QDEC'){$selectedDUser->sendPadd('OFF: FP',"Ti sono stati decurtati ".abs($p)." punti con la seguente motivazione: \"$pointDetail\".",$_SESSION['pgID']);}
		
		$selectedDUser->addPoints($p,$little,$mex,$pointDetail,$currentUser->ID);
	}
	header("Location:scheda.php?pgID=$pgID&sOff=off");
	exit;
}

else 
{ 

	$template = new PHPTAL('TEMPLATES/scheda.htm');
	$pgPoints = PG::getSomething($selectedUser,'pgPoints');
	$pgPointsSaldo = PG::getSomething($selectedUser,'totalPoints');
	$prestavolto = PG::getSomething($selectedUser,"prestavolto");

	$selectedDUser->getIncarichi();


	$band = ($prestavolto['iscriDiff'] >= '48') ? 'b0sw.png' : (($prestavolto['iscriDiff'] >= '36') ? 'b0s.png' : (($prestavolto['iscriDiff'] >= '24') ? 'b0k.png' : (($prestavolto['iscriDiff'] >= '12') ? 'b02.png' : (($prestavolto['iscriDiff'] >= '6') ? 'b003.png' : (($prestavolto['iscriDiff'] >= '3') ? 'b002.png' : 'b01.png')))));
	$subText = ($prestavolto['iscriDiff'] >= '48') ? 'Platinum' : (($prestavolto['iscriDiff'] >= '36') ? 'Gold' : (($prestavolto['iscriDiff'] >= '24') ? 'Silver' : (($prestavolto['iscriDiff'] >= '12') ? 'Bronze' : (($prestavolto['iscriDiff'] >= '6') ? 'Copper' : (($prestavolto['iscriDiff'] >= '3') ? 'Iron' : '')))));

	$resAchi = mysql_query("SELECT owner,aID,aImage,aText,aHidden,category,timer as rt,assignable  FROM pg_achievements LEFT JOIN pg_achievement_assign ON aID = achi AND owner IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $selectedUser)) ORDER BY owner DESC,timer DESC,aHidden ASC,aImage ASC");


	$achi=array('Attivo nel...'=>array(),'PG & Scheda'=>array(),'Community'=>array(),'Contenuti'=>array(),'Segreti'=>array(),'Non assegnabili'=>array());


	while($reseAchi = mysql_fetch_assoc($resAchi))
	{

		if (!$reseAchi['assignable'] && !isSet($reseAchi['owner']) ){
			$reseAchi['category'] = 'Non assegnabili';

		}

		if(!array_key_exists($reseAchi['category'], $achi))
			$achi[$reseAchi['category']] = array();

		if(isSet($reseAchi['owner'])) $text = $reseAchi['aText'];

		else if(PG::mapPermissions('A',$currentUser->pgAuthOMA) && $reseAchi['aHidden']) $text = "** SEGRETO ** [admin:".$reseAchi['aText']."]";
		else if(PG::mapPermissions('A',$currentUser->pgAuthOMA) && !$reseAchi['aHidden']) $text = $reseAchi['aText'];

		else $text=($reseAchi['aHidden']) ? '** Segreto ** Sii il primo ad ottenerlo!' : $reseAchi['aText'];

		if (!array_key_exists($reseAchi['aID'], $achi[$reseAchi['category']] ) || ( array_key_exists($reseAchi['aID'], $achi[$reseAchi['category']]) && ($reseAchi['rt'] < $achi[$reseAchi['category']][$reseAchi['aID']]['rt']) ))
			$achi[$reseAchi['category']][$reseAchi['aID']] = array('owned'=>isSet($reseAchi['owner']),'ID'=>$reseAchi['aID'],'text'=>$text,'image'=>(isSet($reseAchi['owner'])) ? 'TEMPLATES/img/interface/personnelInterface/'.$reseAchi['aImage'] : 'TEMPLATES/img/interface/personnelInterface/bloccato_n.png','adminImage'=> (PG::mapPermissions("A",$currentUser->pgAuthOMA)) ? 'TEMPLATES/img/interface/personnelInterface/'.$reseAchi['aImage'] : '','rt'=>$reseAchi['rt'],'timer' => date("d/m/y",$reseAchi['rt']));
	}



	function cmp($a, $b) { if (!$a['owned'] && !$b['owned']) return -1; else return ($a['rt'] < $b['rt']);	}
	function cmp2($a, $b) { if ($a == 'Non assegnabili') return -1; else return ($a < $b);	}
	foreach ($achi as $achiCat => $achiRecord)
		uasort($achi[$achiCat], 'cmp'); 


	//uksort($achi,'cmp2');
	$template->uniform = PG::getSomething($selectedUser,'uniform');


/* Lauree, Note, */

	$res = mysql_query("

		(SELECT medImage as icon,medName as alt1,medDescript as alt2,recID,doatazioneType, medPrio as orderf, dotazioneAlt as yearD FROM pgDotazioni LEFT JOIN pg_medals ON dotazioneIcon = medID WHERE doatazioneType='MEDAL' AND pgID = $selectedUser ORDER BY medPrio ASC)

		UNION
		(SELECT dotazioneIcon as icon, dotazioneAlt as alt1, '-' as alt2, recID,doatazioneType, recID as orderf, '-' as yearD  FROM pgDotazioni WHERE doatazioneType IN ('LAUR','NOTA') AND pgID = $selectedUser )

		UNION 

		(SELECT image as icon, text as alt1, '' as alt2, recID, 'BREV' as doatazioneType, timer as orderf,  YEAR(timer) as yearD FROM pg_service_stories WHERE type='EXAM' AND image <> 'starfleet_brev.png' AND owner = $selectedUser)");

		$commendations['LAUR']=array(); 
		$commendations['NOTA']=array(); 
		$commendations['MEDAL']=array();
		$commendations['BREV']=array();  

		function sorterf($a, $b) {if($a['orderf'] == $b['orderf']) {return 0;} return ($a['orderf'] < $b['orderf']) ? -1 : 1;}
		function sorterfr($a, $b) {if($a['orderf'] == $b['orderf']) {return 0;} return ($a['orderf'] > $b['orderf']) ? -1 : 1;}


		while ($resA = mysql_fetch_array($res))
		{
			if($resA['doatazioneType'] == 'MEDAL')
				$resA['alt'] = $resA['alt1'].' <span class="yearDSpan">'.$resA['yearD'].'</span><hr/> '.$resA['alt2'];
			elseif($resA['doatazioneType'] == 'BREV')
				$resA['alt'] = $resA['alt1'] . ' <span class="yearDSpan">'.$resA['yearD'].'</span>'; 
			else
				$resA['alt'] = $resA['alt1'];

			$commendations[$resA['doatazioneType']][] = $resA;
		}

	 	uasort($commendations['MEDAL'],'sorterf');
	 	uasort($commendations['LAUR'],'sorterf');
	 	uasort($commendations['NOTA'],'sorterf');
	 	uasort($commendations['BREV'],'sorterf');


		$template->commendations = $commendations; 

		if(!$selectedDUser->png){
		$timLimit = $curTime - 7776000; 
		$res = mysql_query("SELECT 1 FROM pgMedica WHERE pgID = $selectedUser AND time > $timLimit AND type IN ('rMED','MED')");
		if(!mysql_affected_rows())
		$template->visitNeed = true;
		
		$res = mysql_query("SELECT 1 FROM pgMedica WHERE pgID = $selectedUser AND time > $timLimit AND type IN ('rPSI','PSI')");
		if(!mysql_affected_rows())
		$template->visitPsiNeed = true; 

		}

	/* Prestigio */
	$prestigioEntries= 'Notorietà: <span style="font-weight:bold; font-size:13px; color:#FFCC00; font-variant:small-caps;">'.$prestigioLabels[$prestavolto['pgPrestige']]['name'].'</span><hr/>'.$prestigioLabels[$prestavolto['pgPrestige']]['desc'].'<hr/>';
	$res = mysql_query("SELECT * FROM pg_prestige_stories WHERE owner = $selectedUser ORDER BY time DESC");
	while ($resE = mysql_fetch_assoc($res))
		{	
			if($resE['variation'] > 0){ $variationColor = 'green'; $prefix='+';}
			else{$variationColor = 'red'; $prefix='';}

			$prestigioEntries .= timeHandler::extrapolateDay($resE['time']) . ' - ' . $resE['reason'] . '(<span style="color:'.$variationColor.'" >'.$prefix.$resE['variation'].'</span>) <br />';
			//$prestigioEntries[] =$resE;
		}

	 
	$template->extendedRole = PG::roleName($selectedDUser->pgAuthOMA);
	$template->OFFSubi = (isSet($_GET['sOff'])) ? true : false;
	$template->achi = $achi;
	$template->subText = $subText;
	$template->pgPointsSaldo = $pgPointsSaldo;
	$template->pointss = $pgPoints;
	$template->bandaUser = $band;
	$template->prestavolto = $prestavolto;
	$template->prestige = array('level'=>$prestavolto['pgPrestige'],'name'=>$prestigioLabels[$prestavolto['pgPrestige']]['name'],'alt'=>$prestigioEntries);
}

$template->userData = $selectedDUser;
$rea = mysql_query("SELECT pg_assigner FROM pg_places WHERE placeID = '".$selectedDUser->pgAssign."'");
$reaa = mysql_fetch_array($rea);
$template->unit = $reaa['pg_assigner'];

//var_dump(PG::getSomething($selectedDUser->ID,'pgPoints'));exit;
$template->masterPanel = (PG::mapPermissions("SL",$currentUser->pgAuthOMA)) ? true : false;
$template->omasterPanel = (PG::mapPermissions("M",$currentUser->pgAuthOMA)) ? true : false;
$template->moderativePanel = (PG::mapPermissions("MM",$currentUser->pgAuthOMA)) ? true : false;
$template->smasterPanel = (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) ? true : false;
$template->adminPanel = (PG::mapPermissions("A",$currentUser->pgAuthOMA)) ? true : false;

$template->adminOstePanel = ($currentUser->pgUser =='Rezaei' || $currentUser->pgUser == 'Kyleakeen') ? true : false;
$template->editable = ($selectedUser == $_SESSION['pgID'] || $currentUser->pgAuthOMA == 'A') ? true : false;
$template->editableSM = ($selectedUser == $_SESSION['pgID'] || $currentUser->pgAuthOMA == 'SM') ? true : false;

$selectedOMA = $selectedDUser->pgAuthOMA; 

$template->isBan = $selectedOMA == 'BAN';
$template->isOlo = (PG::mapPermissions("O",$selectedOMA)) ? true : false;
$template->isMasCapable = (!PG::mapPermissions('SL',$selectedOMA) && PG::isMasCapable($selectedDUser->ID));
$template->isJMaster = (PG::mapPermissions('JM',$selectedOMA) && PG::isMasCapable($selectedDUser->ID));
$template->isMaster = (PG::mapPermissions("M",$selectedOMA)) ? true : false;
$template->isMMaster = (PG::mapPermissions("MM",$selectedOMA)) ? true : false;
$template->isSuperMaster = (PG::mapPermissions("SM",$selectedOMA)) ? true : false;
$template->isLorenzo = false;
$template->isGuide = (PG::mapPermissions("G",$selectedOMA)) ? true : false;
$template->isAdmin = (PG::mapPermissions("A",$selectedOMA)) ? true : false;

$template->selectedUser = $selectedUser;
$template->gameOptions = $gameOptions;
// $template->gameName = $gameName;
// $template->gameVersion = $gameVersion;
// $template->debug = $debug;
// $template->gameServiceInfo = $gameServiceInfo;
$template->dateFormat = "d/m/Y H:i:s";

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
include('includes/app_declude.php');	
?>

