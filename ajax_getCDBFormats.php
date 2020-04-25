<?php
session_start();
error_reporting(E_ALL);
ini_set('error_display',1);
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 




$term = addslashes($_POST['stringer']);
$format = $_POST['format'];
$rapType = addslashes($_POST['rapType']);


$etos=mysql_fetch_assoc(mysql_query("SELECT * FROM cdb_templates WHERE ref = '$rapType'"));

if(mysql_affected_rows())
{



	$templateBase = $etos['text'];




	$sel = explode(',',$term);
	$lisOuser="";
	foreach ($sel as $auth)
	{
		if($auth!='' && $auth!=' '){
			$lisOuser .= "'".trim($auth)."',";
		}

	} 
	$lisOuser = substr(trim($lisOuser),0,-1);
	 

	$colr = array('Comando e Strategia' => 'RED', 'Difesa e Sicurezza' => 'GREEN', 'Ingegneria e Operazioni' => 'YELLOW', 'Scientifica e Medica' => 'GREEN', 'Medica' => 'GREEN', 'Medicina Civile' => 'GREEN', 'Scienze' => 'GREEN', 'Navigazione' => 'BLUE');

	$actDate=timeHandler::timestampToGiulian($curTime);
	$curUser = new PG($_SESSION['pgID']);
	$curUser->getIncarichi();
	$pgSezione = $curUser->pgSezione;
	$curLocation = $curUser->getLocationOfUser();

	$curLocationName=$curLocation['placeName'];
	$curLocationID = $curLocation['placeID'];
	$curLocationNameU=strtoupper($curLocationName);

	$mySectionColor = (array_key_exists($pgSezione,$colr)) ?  $colr[$pgSezione] : 'GRAY';

	$usersString="";


	if(strpos($lisOuser,'[Ufficiali Superiori]') !== false)
	{
		$tUnion = "UNION (SELECT UCASE(pgUser) as pgUser, UCASE(pgNomeC) as pgNomeC, UCASE(pgNomeSuff) as pgNomeSuff, pgSezione,incIncarico,pgGrado,ordinaryUniform,placeName,rankerprio FROM pg_users LEFT JOIN pg_incarichi ON pg_users.pgID = pg_incarichi.pgID LEFT JOIN pg_places ON pgPlace = placeID LEFT JOIN pg_ranks ON prio = rankCode WHERE incActive = 1 AND incDipartimento LIKE '%Ufficiali in Comando%' AND incIncarico NOT LIKE '%Vice%' AND pgPlace = '$curLocationID')
			UNION
			(SELECT UCASE(pngSurname) as pgUser, UCASE(pngName) as pgNomeC, '' as pgNomeSuff, pngSezione,pngIncarico,rGrado,ordinaryUniform,placeName,rankerprio FROM png_incarichi,pg_places,pg_ranks WHERE prio = pngRank AND pngPlace = placeID AND pngDipartimento LIKE 'Ufficiali in Comando' AND pngIncarico NOT LIKE '%Vice%' AND pngPlace = '$curLocationID' GROUP BY pngSurname
			)"; 
	}
	else 
		$tUnion = '';

	if($lisOuser != '')
	{
		$res = mysql_query("(SELECT UCASE(pgUser) as pgUser, UCASE(pgNomeC) as pgNomeC, UCASE(pgNomeSuff) as pgNomeSuff, pgSezione,incIncarico,pgGrado,ordinaryUniform,placeName,rankerprio FROM pg_users LEFT JOIN pg_incarichi ON pg_users.pgID = pg_incarichi.pgID LEFT JOIN pg_places ON pgPlace = placeID LEFT JOIN pg_ranks ON prio = rankCode WHERE (incMain = 1 OR ISNULL(incMain)) AND pgUser IN ($lisOuser) )
			UNION (
			SELECT UCASE(pngSurname) as pgUser, UCASE(pngName) as pgNomeC, '' as pgNomeSuff, pngSezione,pngIncarico,rGrado,ordinaryUniform,placeName,rankerprio FROM png_incarichi,pg_places,pg_ranks WHERE prioritary = 1 AND prio = pngRank AND pngPlace = placeID AND pngSurname IN ($lisOuser) GROUP BY pngSurname
			) $tUnion

			ORDER BY rankerprio DESC"); 
		if(!mysql_error())
		{
			while($resA = mysql_fetch_array($res))
				{
					$pgUser = $resA['pgUser'];
					$pgNomeC = $resA['pgNomeC'];
					$pgNomeSuff = $resA['pgNomeSuff'];
					$pgIncarico = $resA['incIncarico']; 
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

	 

	$REPLACE=array(
	'{__NOME_UNITA__}' => $curLocationName,
	'{__NOME_UNITA_UPPERCASE__}' => $curLocationNameU,
	'{__DATA__}' => timeHandler::timestampToGiulian($curTime),
	'{__DATA_STELLARE__}' => $currentStarDate,
	'{__MYSECTIONCOLOR__}' => $mySectionColor,
	'{__GRADO__}' => $curUser->pgGrado,
	'{__GRADO__UPPERCASE}' => strtoupper($curUser->pgGrado),
	'{__SEZIONE__}' => $pgSezione,
	'{__COGNOME__}' => $curUser->pgUser,
	'{__NOME__}' => $curUser->pgNomeC,
	'{__SUFFISSO__}' => $curUser->pgNomeSuff,
	'{__DIPARTIMENTO__}' => 'DIPARTIMENTO '.strtoupper($curUser->pgDipartimento),
	'{__SEZIONE__UPPERCASE}' => strtoupper($pgSezione),
	'{__COGNOME__UPPERCASE}' => strtoupper($curUser->pgUser),
	'{__NOME__UPPERCASE}' => strtoupper($curUser->pgNomeC),
	'{__SUFFISSO__UPPERCASE}' => strtoupper($curUser->pgNomeSuff),
	'{__SUFFISSO__}' => $curUser->pgNomeSuff,
	'{__LOGO_UNITA__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/'.$curLocation['placeLogo'].'[/IMG]',
	'{__LOGO_COMANDO__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/nl_com.r.png[/IMG]',
	'{__LOGO_DIFESA__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/nl_sec.r.png[/IMG]',
	'{__LOGO_OPERAZIONI__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/logo_ops.png[/IMG]',
	'{__LOGO_INGEGNERIA__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/nl_ops.r.png[/IMG]',
	'{__LOGO_MEDICA__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/nl_med.r.png[/IMG]',
	'{__LOGO_SCIENTIFICA__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/nl_sci.r.png[/IMG]',
	'{__LOGO_NAVIGAZIONE__}' => '[IMG]https://oscar.stfederation.it/SigmaSys/logo/nl_nav.r.png[/IMG]',
	'{__LISTA_UTENTI__}' => $usersString);


	$outString= str_replace(array_keys($REPLACE),$REPLACE,$templateBase);
	
echo json_encode($outString);
}

?>