<?php
session_start();

function stripBBCode($text_to_search) {
 $pattern = '|[[\/\!]*?[^\[\]]*?]|';
 $replace = '';
 return preg_replace($pattern, $replace, $text_to_search);
}


if (!isSet($_SESSION['pgID'])){echo "Errore di Login. Ritorna alla homepage ed effettua il login correttamente!"; exit;}
    
include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php");
PG::updatePresence($_SESSION['pgID']);

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_DEPRECATED);

$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);
$mode = (isSet($_GET['s'])) ? $_GET['s'] : NULL;
 

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

 
if($mode == 'newP')
{
	$toString = array_filter(explode(',',$_POST['users']));

	$curID = $_SESSION['pgID'];
	
	$titolo = htmlentities($_POST['titolo'],ENT_COMPAT, 'UTF-8');
	$testo = htmlentities($_POST['testo'],ENT_COMPAT, 'UTF-8');
	$testo = $_POST['testo'];
	

	if(trim($titolo=="")) $titolo= "NESSUN OGGETTO";
	
	foreach($toString as $to)
	{
		
		if (empty($to) || $to == " " || $to == NULL) continue;
		
		$to = addslashes(trim($to)); 

		if (startsWith($to,"Gruppo"))
		{
			$idR = mysql_query("SELECT pgID FROM pg_groups_ppl,pg_groups WHERE pg_groups_ppl.groupID = pg_groups.groupID AND groupLabel = '$to'");

			$testo = '<p>Messaggio collettivo inviato a: <span style="color:#FC0; font-weight:bold;">'.$to.'</span></p>'.$testo;
				
			while($res = mysql_fetch_assoc($idR))
			{	
				$toP = new PG($vali->numberOnly($res['pgID']),2);
				$toP->sendPadd($titolo,$testo,$_SESSION['pgID']);
			}
		}

		elseif(strpos($to,'[Ufficiali Superiori]') !== false)
		{

			$curLocation = $currentUser->getLocationOfUser();
			$curLocationID = $curLocation['placeID'];
			$placeName = $curLocation['placeName'];
			$testo = '<p>Padd inviato agli Ufficiali Superiori della <span style="color:#FC0; font-weight:bold;">'.$placeName.'</span></p><br />'.$testo;

			$rus=mysql_query("SELECT pg_users.pgID FROM pg_users LEFT JOIN pg_incarichi ON pg_users.pgID = pg_incarichi.pgID LEFT JOIN pg_places ON pgPlace = placeID LEFT JOIN pg_ranks ON prio = rankCode WHERE incActive = 1 AND incDipartimento LIKE '%Ufficiali in Comando%' AND incIncarico NOT LIKE '%Vice%' AND pgPlace = '$curLocationID'");

			while($ris = mysql_fetch_assoc($rus)){
				$toP = new PG($ris['pgID'],2);
				$toP->sendPadd($titolo,$testo,$_SESSION['pgID']); 
			}
		}
		else
		{
			$idsA = mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$to'"));
			if (mysql_affected_rows())
			{	 
				$toP = new PG($vali->numberOnly($idsA['pgID']),2);

				$fm=0;

				if (isSet($_GET['guider'])) $paddType = 3;
				if (isSet($_POST['paddType']))
				{
					if($_POST['paddType'] == "4" && PG::mapPermissions('M',$currentUser->pgAuthOMA))
						$paddType=4;
					elseif($_POST['paddType'] == "1S" && PG::mapPermissions('SM',$currentUser->pgAuthOMA))
						{
							$fm=1;
							$paddType=1;
						}
					elseif($_POST['paddType'] == 1 || $_POST['paddType'] == 0 || $_POST['paddType'] == 3)
						$paddType=(int)($_POST['paddType']);
				}
				else $paddType=0;
				$toP->sendPadd($titolo,$testo,$_SESSION['pgID'],$paddType,$fm);
			}
		}
	} 
	header('Location:padd.php?ps=1'); exit;
}

else if ($mode == 'read')
{ 
	$template = new  PHPTAL('TEMPLATES/padd_show.htm');
	$paddID = $vali->numberOnly($_GET['paddID']);
	$paddQ = mysql_query("SELECT padID, paddTitle, paddText, paddFrom, paddTo, paddTime, fromPGT.pgAvatarSquare, fromPGT.pgAuthOMA as pgATOMA,fromPGT.png as frompng,  toPGT.pgUser as ToPG, fromPGT.pgUser as FromPG,fromPGT.pgSpecie as pgSpecie,fromPGT.pgSesso as pgSesso, toPGT.pgID as ToPGID, fromPGT.pgID as FromPGID, paddType, ordinaryUniform FROM fed_pad, pg_users  AS fromPGT, pg_users AS toPGT, pg_ranks WHERE prio = fromPGT.rankCode AND (paddDeletedFrom <> 1 OR paddDeletedTo <> 1) AND padID =$paddID AND toPGT.pgID = paddTo AND fromPGT.pgID = paddFrom AND (paddTo = ".$_SESSION['pgID']." OR paddFrom = ".$_SESSION['pgID'].")");
	
	if(mysql_affected_rows())
	{
		$padd = mysql_fetch_assoc($paddQ); 
		$template->padd = $padd;
		$template->pcontent = cdb::bbcode($padd['paddText']);
		
		//$template->pcontent = nl2br($padd['paddText']);

		$template->phour = date('H',$padd['paddTime']);
		$template->pmin = date('i',$padd['paddTime']); 

		$template->pday = timeHandler::extrapolateDay($padd['paddTime']);
		$template->deletable = ($padd['paddTo'] == $_SESSION['pgID']) ? true : false;
		if($padd['paddTo'] == $_SESSION['pgID']) mysql_query("UPDATE fed_pad SET paddRead = 1 WHERE padID = $paddID");
	}
	else 
	{
		header("Location:padd.php");
		exit;
	}
}

else if($mode == 'delInPad')
{
	$ref = $vali->numberOnly($_GET['paddID']);
	mysql_query("UPDATE fed_pad SET paddDeletedTo = 1 WHERE padID = $ref AND paddTo = ".$_SESSION['pgID']);
	header('Location:padd.php?h=in'); 
	exit;
}

else if($mode == 'delOutPad')
{
	$ref = $vali->numberOnly($_GET['paddID']);
	mysql_query("UPDATE fed_pad SET paddDeletedFrom = 1 WHERE padID = $ref AND paddFrom = ".$_SESSION['pgID']);
	header('Location:padd.php?h=ou'); 
	exit;
}

else if($mode == 'delHer')
{
	$ref = $vali->numberOnly($_GET['newsID']);
	
	if(PG::mapPermissions('M',$currentUser->pgAuthOMA)) mysql_query("DELETE FROM fed_news WHERE newsID = $ref");
	
	header('Location:padd.php?s=tr');
	exit;
}

else if ($mode == 'notif'){

	$template = new  PHPTAL('TEMPLATES/padd_noti.htm');
	
}

else if ($mode == 'sh' || ($mode == 'shM' && PG::mapPermissions('M',$currentUser->pgAuthOMA)))
{
	$template = ($mode == 'sh') ? new  PHPTAL('TEMPLATES/padd_status.htm') : new  PHPTAL('TEMPLATES/padd_status_edit.htm');
	
	$placeQ = mysql_query('SELECT placeID,status,placeAlert,placeType,warp, placeMotto, placeName, placeLogo,note, place_littleLogo1, placeClass, attracco, placeSubType FROM pg_places  WHERE placeID = \''.$currentUser->pgLocation.'\'');

	if(mysql_affected_rows())
	{
		$place = mysql_fetch_array($placeQ);
		
		if($place['attracco'] != NULL)
		{
			$template->attraccata = PG::getLocationName($place['attracco']);
			$template->stat = 'attraccata';	
		}
		else if($place['warp'] != 0)
			$template->stat = 'curvatura';
		else 
		{
			$template->stat = 'posizionata';
			/*SE NAVETTA RECUPERO LE LIMITROFE PER L'ATTRACCO*/
			if ($place['placeType'] == 'Navetta' && $mode == 'shM') 
			{
				$targetPlace = $currentUser->pgLocation; 
				$res = mysql_query("SELECT placeName,placeID,place_littleLogo1, placeMap1 FROM pg_places WHERE placeType IN('Nave','Stazione') AND warp = '0' AND attracco = '' AND pointerL = (SELECT pointerL FROM pg_places WHERE placeID = '$targetPlace') ORDER BY placeType");
				
				$limitrofi=array();
				while($resa = mysql_fetch_array($res))
					$limitrofi[$resa['placeID']] = $resa['placeName'];
				if(count($limitrofi) != 0) $template->limitrofi = $limitrofi;
			}	
		}
		
		
		$template->place=$place;
		$tr = explode(',',$place['status']);
		foreach ($tr as $element) $statuses[] = $element;
		$template->statuses = $statuses;
		$template->note = nl2br($place['note']);
	
		if($place['placeType'] == 'Nave' && $place['placeSubType'] == 'ROMULAN') $template->labels = array('SCAFO','INT. STRUTTURALE','SCUDI DEFLETTORI', 'PHASER e SILURI', 'OCCULTAMENTO','MOTORI R.C.S.','MOTORI I.P.S.','MOTORI WARP','COMUNICAZIONI','SENSORI','SUPPORTO VITALE','TELETRASPORTO');
		else if($place['placeType'] == 'Nave' && $place['placeSubType'] != 'ROMULAN') $template->labels = array('SCAFO','INT. STRUTTURALE','SCUDI DEFLETTORI', 'PHASER e SILURI', 'DEFLETTORE','MOTORI R.C.S.','MOTORI I.P.S.','MOTORI WARP','COMUNICAZIONI','SENSORI','SUPPORTO VITALE','TELETRASPORTO');
		else if($place['placeType'] == 'Pianeta') $template->labels =  array('SENSORI','INFRASTRUTTURE','TELETRASPORTO', 'SISTEMI DIFESA', 'CONT. ATMOSFERICO','RETI ENERGETICHE','RETE DATI','CAPACITA\'','COMUNICAZIONI','RIS. EMERGENZA','SIST. AMBIENTALI','SVILUPPO');
		else if($place['placeType'] == 'Navetta') $template->labels = array('SCAFO','CORAZZA ABLATIVA','SCUDI DEFLETTORI', 'BANCHI PHASER', 'LANCIASILURI','MOTORI R.C.S.','MOTORI I.P.S.','MOTORI WARP','COMUNICAZIONI','SENSORI','SUPPORTO VITALE','TELETRASPORTO');
		else if($place['placeType'] == 'Stazione') $template->labels = array('SCAFO','CORAZZA ABLATIVA','SCUDI DEFLETTORI', 'BANCHI PHASER', 'LANCIASILURI','MOTORI R.C.S.','RETE EPS','ATTRACCHI','COMUNICAZIONI','SENSORI','SUPPORTO VITALE','TELETRASPORTO');
		else if($place['placeType'] == 'Accademia') $template->labels =  array('SENSORI','INFRASTRUTTURE','TELETRASPORTO', 'SISTEMI DIFESA', 'CONT. ATMOSFERICO','RETI ENERGETICHE','RETE DATI','CAPACIA\'','COMUNICAZIONI','RIS. EMERGENZA','SIST. AMBIENTALI','SVILUPPO');
	
		//if 
		$template->modify = (PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? true : false;
	} 
	else {header('Location:padd.php'); exit;}

	/*PATTERN
	
	SCAFO  			-						- SCAFO
	CORAZZA ABLATIVA- 						- CORAZZA ABLATIVA
	SCUDI DEFLETTORI- SCUDO PLANETARIO 	    - SCUDI DEFLETTORI  
	BANCHI PHASER   - PIATTAFORME DI DIFESA - BANCHI PHASER
	LANCIASILURI    - LANCIAMISSILI 		- LANCIASILURI
	MOTORI R.C.S.   -					    - MOTORI RCS
	MOTORI I.P.S.   -						- 
	MOTORI WARP     -						- 
	COMUNICAZIONI   - COMUNICAZIONI 		- COMUNICAZIONI
	SENSORI 		- SENSORI				- SENSORI
	SUPPORTO VITALE - SUPPORTO AMBIENTALE   - SUPPORTO VITALE
	
	*/
}

else if($mode == 'seR')
{
	$template = new  PHPTAL('TEMPLATES/padd_send_custom.htm');
	
	$template->to = $_GET['to'];
	$template->sub = ($_GET['sub'] != "") ? (strstr($_GET['sub'],'Re: ') ? $_GET['sub'] : 'Re: '.addslashes($_GET['sub']) ) : addslashes($_GET['sub']) ;
	
	$template->prevType = isSet($_GET['prevType']) ? $_GET['prevType'] : 0;
 }

else if($mode == 'ds')
{
	$template = new  PHPTAL('TEMPLATES/duties_index.htm');
	
	$res = mysql_query(PG::getSomething($_SESSION['pgID'],'DutiesAvailQuery'));
	
	$availDuties = array();
	while($resA = mysql_fetch_array($res))
	{
			$availDuties[] = $resA;
	}
	
	$template->availDuties = $availDuties;
}

else if($mode == 'tr' || $mode == 'ta')
{
	if($mode=='tr') 
	{
		$template = new  PHPTAL('TEMPLATES/padd_tribune.htm');
		$limit = "LIMIT 10";
	}
	else
	{
		$template = new PHPTAL('TEMPLATES/padd_tribune_archivio.htm');
		$limit = "";
	}
	
	$particle = ($currentUser->pgSpecie == 'Romulana') ? 'ROM' : 'FED';
	
	setlocale(LC_TIME, 'it_IT');
	$template->datae = strftime('%e').' '.ucfirst(strftime('%B')).' '.(date('Y')+379);
	$news = mysql_query("SELECT * FROM fed_news WHERE aggregator = '$particle' ORDER BY newsTime DESC $limit");
	$newsArr=array();
	while($newsA = mysql_fetch_array($news))
	$newsArr[] = array('ID' => $newsA['newsID'],'title' => $newsA['newsTitle'], 'subtitle' => $newsA['newsSubTitle'], 'text' => CDB::bbcode($newsA['newsText']), 'time' => (strftime('%e', $newsA['newsTime']).' '.ucfirst(strftime('%B', $newsA['newsTime'])).' '.(date('Y', $newsA['newsTime'])+379)));
	
	$template->masterable = (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(19) )) ? true : false;
	$template->adminable = (PG::mapPermissions('A',$currentUser->pgAuthOMA)) ? true : false;
	$template->news = $newsArr;
}

else if($mode == 'no')
{
	$template = new  PHPTAL('TEMPLATES/padd_notes.htm');
	$noteR = mysql_query("SELECT * FROM pg_notes WHERE owner = ".$currentUser->ID);

	$notes = array();
	while($note = mysql_fetch_array($noteR))
		$notes[$note['noteID']] = $note['title'];
	$template->notes = $notes;
}

else if ($mode == 'delNote')
{
	$ID = $vali->numberOnly($_GET['ID']);
	mysql_query("DELETE FROM pg_notes WHERE noteID = $ID AND owner =".$currentUser->ID);
	header('Location:padd.php?s=no');
	exit;
}

else if ($mode == 'newNote')
{
	$title = ($_POST['titolo'] == '') ? 'Nessun Titolo' : addslashes($_POST['titolo']);
	$testo= (htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8'));
	mysql_query("INSERT INTO pg_notes (owner,title,text) VALUES (".$currentUser->ID.",'$title','$testo')");
	header('Location:padd.php?s=no');
	exit;
}

else if ($mode == 'ediNote')
{
	$title = addslashes($_POST['titolo']);
	$testo= (htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8'));
	$id= $vali->numberOnly($_POST['ID']);
	mysql_query("UPDATE pg_notes SET title = '$title', text = '$testo' WHERE noteID = $id AND owner =".$currentUser->ID);
	header('Location:padd.php?s=readNote&ID='.$id);
	exit;
}


else if ($mode == 'readNote')
{
	$ID = $vali->numberOnly($_GET['ID']);
	$template = new  PHPTAL('TEMPLATES/padd_notes_read.htm');
	$ra = mysql_query("SELECT * FROM pg_notes WHERE noteID = $ID AND owner = ".$currentUser->ID);
	if(mysql_affected_rows())
	{	
		$res = mysql_fetch_array($ra);
		$template->ID = $res['noteID'];
		$template->title = $res['title'];
		$text = $res['text'];
		$template->text = CDB::bbcode($text);
		$template->textP = $text;
	}
}

else if ($mode == 'savePad')
{
	$ID = $vali->numberOnly($_GET['paddID']);
	$raInfo = mysql_query("SELECT paddTitle,paddText FROM fed_pad WHERE padID = $ID");
	$raInfoS = mysql_fetch_array($raInfo);
	$ratitle = "[PADD SALVATO] ".addslashes($raInfoS['paddTitle']);
	$raText = addslashes($raInfoS['paddText']);
		
	$ra = mysql_query("INSERT INTO pg_notes (owner,title,text) VALUES (".$currentUser->ID.",'$ratitle','$raText')");
	header('Location:padd.php?s=no');exit;
}

else if($mode == 'newH')
{
	$titolo = addslashes($_POST['titolo']);
	$sottotitolo = addslashes($_POST['sottotitolo']);
	$testo = addslashes($_POST['testo']);
	$categ =  isSet($_POST['aleXX']) ? addslashes($_POST['aleXX']) : 'FED';
	$tolink = ($categ != "FED") ? addslashes($_POST['sottotitolo']) : '';
	
	if( trim($titolo)!="" && trim($testo)!="" && (PG::mapPermissions('M',$currentUser->pgAuthOMA) || $currentUser->hasBrevetto(array(19) ) ))
	{
		mysql_query("INSERT INTO fed_news (toLink,newsTitle, newsSubTitle, newsText, newsTime, aggregator) VALUES ('$tolink','$titolo', '$sottotitolo', '$testo',$curTime,'$categ')");
		$NI=mysql_fetch_assoc(mysql_query("SELECT newsID FROM fed_news ORDER BY newsID DESC LIMIT 1"));
		$newsID = $NI['newsID'];
		if($categ == 'FED')
		{
			$curID = $_SESSION['pgID'];
			$oneMonth = $curTime - 2505600; 
			$idR = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLock=0 AND png=0 AND pgLastAct >= $oneMonth");
			while($res = mysql_fetch_assoc($idR))
			{ 
				$idA = $res['pgID']; 
				mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (1610, $idA, 'Tychonian Eagle: $titolo', 'E\' stata inserita una nuova News nel Tychonian Eagle<br /><p style=\"text-align:center;\"><span style=\"font-size:20px;\">$titolo</span><br />$sottotitolo</p><br /><br /> <br /><a href=\"padd.php?s=readTribune&newsID=$newsID\" class=\"interfaceLinkBlue\">Clicca qui</a> per leggerla!',$curTime,0)");
			} 

			$string = addslashes("<p class=\"auxActionMaster\">Nuova News inserita nel Tychonian Eagle<br /><a class=\"interfaceLinkBlue\" href=\"javascript:void(0);\" onclick=\"window.open ('padd.php?s=readTribune&newsID=$newsID','padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width=655,height=403');\">$titolo</a></p>");
			mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",0,$curTime,'$string',0)");

		}
	}
		
	header('Location:padd.php?s=tr'); 
	exit;
}

else if($mode == 'readTribune')
{
	$id = $vali->numberOnly($_GET['newsID']);
	setlocale(LC_TIME, 'it_IT');
	$template = new PHPTAL('TEMPLATES/padd_tribune_read.htm');
	$template->datae = strftime('%e').' '.ucfirst(strftime('%B')).' '.(date('Y')+379);
	$news = mysql_query("SELECT * FROM fed_news WHERE newsID = $id");
	if(mysql_affected_rows()) $newsA = mysql_fetch_array($news);
	
	 $template->ID = $newsA['newsID'];
	 $template->title = $newsA['newsTitle'];
	 $template->subtitle = $newsA['newsSubTitle'];
	 $template->text = CDB::bbcode($newsA['newsText']);
	 $template->time = (strftime('%e', $newsA['newsTime']).' '.ucfirst(strftime('%B', $newsA['newsTime'])).' '.(date('Y', $newsA['newsTime'])+379));
	 
	$template->masterable = (PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? true : false;
	
}

else if($mode == 'not')
{
	$template = new  PHPTAL('TEMPLATES/padd_notify.htm');
	$name = addslashes($currentUser->pgUser);
	
	$res = mysql_query("SELECT ID,title,content,owner,topicColorExt,topicType,cdb_topics.topicID,topicTitle,time FROM cdb_topics,cdb_posts WHERE cdb_topics.topicID = cdb_posts.topicID AND owner <> ".$_SESSION['pgID']." AND content LIKE '%$name%' ORDER BY time DESC LIMIT 20");
	
	$relativePosts=array();
	while($rea=mysql_fetch_array($res))
	{
		$relativePosts[] = array(
		'ID' => $rea['ID'],
		'titleL' => $rea['title'],
		'content' => $rea['content'],
		'owner' => $rea['owner'],
		'topicColorExt' => $rea['topicColorExt'],
		'topicType' => $rea['topicType'],
		'topicID' => $rea['topicID'],
		'topicTitleL' => $rea['topicTitle'],
		'topicTitle' => (strlen($rea['topicTitle']) > 30) ? substr($rea['topicTitle'],0,30).'...' : $rea['topicTitle'],
		'time' => $rea['time']
		);
	}
	$template->notifications = $relativePosts;
} 

else {
	$template = new  PHPTAL('TEMPLATES/padd.htm'); 
	
		$template->submode = isSet($_GET['h']) ? $_GET['h'] : 'in' ;
		
		$res = mysql_query("SELECT padID,paddTitle,paddFrom,paddText,paddTime,paddRead,paddType, pgUser FROM fed_pad,pg_users WHERE paddTitle NOT LIKE '::special::%' AND paddFrom=pgID AND paddTo = ".$_SESSION['pgID']." AND paddDeletedTo <> 1 ORDER BY paddRead ASC, paddTime DESC");
		$incumingArray = array(0 => array(),1 => array());
			
		while($resA = mysql_fetch_array($res))
		$incumingArray[$resA['paddRead']][] = array(
		'ID' => $resA['padID'],
		'from' => $resA['pgUser'],
		'time' => timeHandler::timestampToGiulian($resA['paddTime']),
		'title' => $resA['paddTitle'],
		'paddType' => $resA['paddType'],
		'paddText' => substr($resA['paddText'],0,150).'...'
		
		);
		
		$res = mysql_query("SELECT padID, paddTitle,paddTo,paddText,paddTime,paddRead,paddType, pgUser FROM fed_pad,pg_users WHERE paddTo=pgID AND paddFrom = ".$_SESSION['pgID']." AND paddDeletedFrom <> 1 ORDER BY paddRead ASC, paddTime DESC");
		$outcumingArray = array(0 => array(),1 => array());
		while($resA = mysql_fetch_array($res))
		$outcumingArray[$resA['paddRead']][] = array(
		'ID' => $resA['padID'],
		'to' => $resA['pgUser'],
		'time' => timeHandler::timestampToGiulian($resA['paddTime']),
		'title' => $resA['paddTitle'],
		'paddType' => $resA['paddType'],

		'paddText' => substr($resA['paddText'],0,150).'...'
		
		);
		$template->incoming = $incumingArray;
		$template->outgoing = $outcumingArray;
		

		if (isSet($_GET['ps'])) $template->paddSent = true;
		
		/*SELECT paddTitle,paddFrom,paddTo,paddText,paddTime,paddRead, P1.pgUser as paddFromUser, P2.pgUser as paddToUser FROM fed_pad,pg_users as P1, pg_users as P2 WHERE paddFrom=P1.pgID AND paddTo = P2.pgID AND paddDeleted <> 1*/	
	
	// $template = new  PHPTAL('TEMPLATES/padd_main.htm');
	
	// $res = mysql_query("SELECT COUNT(*) as conto FROM fed_pad WHERE paddRead = 0 AND paddTitle NOT LIKE '::special::%' AND paddTo = ".$_SESSION['pgID']." AND paddDeletedTo <> 1");
	// $rea=mysql_fetch_array($res);
	// $template->paddNumber = $rea['conto'];
	// $template->currentDate = $currentDate;
	// $template->currentStarDate = $currentStarDate;
		
}
if (PG::mapPermissions('G',$currentUser->pgAuthOMA)) $template->isGuide = true;
if (PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->isMaster = true;
if (PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->isAdmin = true;
$aggregat = ($currentUser->pgSpecie == 'Romulana')? 'ROM' : 'FED';
$tribuneLastNews = mysql_fetch_array(mysql_query("SELECT newsTime FROM fed_news WHERE aggregator = '$aggregat' ORDER BY newsTime DESC LIMIT 1"));

$template->tribuneNews = ($tribuneLastNews['newsTime'] > (time()-86400)) ? true : false;
$template->user = $currentUser;
// $template->gameName = $gameName;
// $template->gameVersion = $gameVersion;
// $template->debug = $debug;
// $template->gameServiceInfo = $gameServiceInfo;
$template->dateFormat = "d/m/Y H:i:s";
$template->gameOptions = $gameOptions;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
include('includes/app_declude.php');

?>