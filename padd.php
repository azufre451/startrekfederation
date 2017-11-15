<?php
session_start();

if (!isSet($_SESSION['pgID'])){echo "Errore di Login. Ritorna alla homepage ed effettua il login correttamente!"; exit;}
    
include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
PG::updatePresence($_SESSION['pgID']);

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_DEPRECATED);

$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);
$mode = (isSet($_GET['s'])) ? $_GET['s'] : NULL;
 
 
if($mode == 'newP')
{
	if($_POST['users']=='ALL_ACTIVE')
	{
		
		$curID = $_SESSION['pgID'];
		$titolo = addslashes($_POST['titolo']);
		$testo = htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8');
		
		$oneMonth = $curTime - 2505600;
		if(trim($titolo=="")) $titolo= "NESSUN OGGETTO";
		
		$idR = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLock=0 AND png=0 AND pgLastAct >= $oneMonth");
		while($res = mysql_fetch_assoc($idR))
		{ 
			$idA = $res['pgID']; 
			mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES ($curID, $idA, '$titolo', '$testo',$curTime,0)");
		} 
	}
	else
	{
		$toString = explode(',',$_POST['users']);
		$curID = $_SESSION['pgID'];
		$titolo = addslashes($_POST['titolo']);
		$testo = htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8');
		$testoL = nl2br(htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8'));
		
		if(trim($titolo=="")) $titolo= "NESSUN OGGETTO";
		
		foreach($toString as $to)
		{
			$to = addslashes(trim($to)); 
			if ($to==NULL) continue;
			$idR = mysql_query("SELECT pgID,paddMail,email FROM pg_users WHERE pgUser = '$to'");
			$idsA = mysql_fetch_array($idR);
			
			$idA = $idsA['pgID'];
			
			mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES ($curID, $idA, '$titolo', '$testo',$curTime,0)");
			
			if($idsA['paddMail'])
			{
				$sendTo = $idsA['email'];
				$senderName = PG::getSomething($curID,'username');
				$receiverName = PG::getSomething($idA,'username');
				$subject = "[STF] $senderName >> ".$titolo;
				
				$message = "<div style=\"text-align:center;\"><img src=\"http://miki.startrekfederation.it/SigmaSys/logo/little_logo.png\" /></div><p>$senderName ti ha inviato un dpadd<br /><b>Testo:</b> $testoL<br /><br />Accedi a <a href=\"http://www.startrekfederation.it\" target=\"_blank\">Star Trek: Federation</a> per consultare il padd!";
				
				
				$header = "From: $senderName <messaggistica@startrekfederation.it>\n";
				$header .= "MIME-Version: 1.0\n";
				$header .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
				$header .= "Content-Transfer-Encoding: 7bit\n\n";
		
				mail($sendTo, $subject, $message, $header);

			}
		} 
	}
	header('Location:padd.php?ps=1'); 
}

else if ($mode == 'read')
{ 
	$template = new  PHPTAL('TEMPLATES/padd_show.htm');
	$paddID = $vali->numberOnly($_GET['paddID']);
	$paddQ = mysql_query("SELECT padID, paddTitle, paddText, paddFrom, paddTo, paddTime, fromPGT.pgAvatarSquare , toPGT.pgUser as ToPG, fromPGT.pgUser as FromPG,fromPGT.pgSpecie as pgSpecie,fromPGT.pgSesso as pgSesso, toPGT.pgID as ToPGID, fromPGT.pgID as FromPGID, ordinaryUniform FROM fed_pad, pg_users  AS fromPGT, pg_users AS toPGT, pg_ranks WHERE prio = fromPGT.rankCode AND (paddDeletedFrom <> 1 OR paddDeletedTo <> 1) AND padID =$paddID AND toPGT.pgID = paddTo AND fromPGT.pgID = paddFrom AND (paddTo = ".$_SESSION['pgID']." OR paddFrom = ".$_SESSION['pgID'].")");
	
	if(mysql_affected_rows())
	{
		$padd = mysql_fetch_array($paddQ);
		$template->padd = $padd;
		//$template->pcontent = str_replace($bbCode,$htmlCode,$padd['paddText']);
		$template->pcontent = nl2br($padd['paddText']);
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
}

else if($mode == 'delOutPad')
{
	$ref = $vali->numberOnly($_GET['paddID']);
	mysql_query("UPDATE fed_pad SET paddDeletedFrom = 1 WHERE padID = $ref AND paddFrom = ".$_SESSION['pgID']);
	header('Location:padd.php?h=ou'); 
}

else if($mode == 'delHer')
{
	$ref = $vali->numberOnly($_GET['newsID']);
	
	if(PG::mapPermissions('M',$currentUser->pgAuthOMA)) mysql_query("DELETE FROM fed_news WHERE newsID = $ref");
	
	header('Location:padd.php?s=tr'); 
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
	$template->sub = ($_GET['sub'] != "") ? 'Re: '.addslashes($_GET['sub']) : addslashes($_GET['sub']) ;
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
	$template->datae = strftime('%e').' '.ucfirst(strftime('%B')).' '.(date('Y')+377);
	$news = mysql_query("SELECT * FROM fed_news WHERE aggregator = '$particle' ORDER BY newsTime DESC $limit");
	$newsArr=array();
	while($newsA = mysql_fetch_array($news))
	$newsArr[] = array('ID' => $newsA['newsID'],'title' => $newsA['newsTitle'], 'subtitle' => $newsA['newsSubTitle'], 'text' => str_replace($bbCode,$htmlCode,$newsA['newsText']), 'time' => (strftime('%e', $newsA['newsTime']).' '.ucfirst(strftime('%B', $newsA['newsTime'])).' '.(date('Y', $newsA['newsTime'])+377)));
	
	$template->masterable = (PG::mapPermissions('M',$currentUser->pgAuthOMA)) ? true : false;
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
}

else if ($mode == 'newNote')
{
	$title = ($_POST['titolo'] == '') ? 'Nessun Titolo' : addslashes($_POST['titolo']);
	$testo= (htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8'));
	mysql_query("INSERT INTO pg_notes (owner,title,text) VALUES (".$currentUser->ID.",'$title','$testo')");
	header('Location:padd.php?s=no');
}

else if ($mode == 'ediNote')
{
	$title = addslashes($_POST['titolo']);
	$testo= (htmlentities(addslashes(($_POST['testo'])),ENT_COMPAT, 'UTF-8'));
	$id= $vali->numberOnly($_POST['ID']);
	mysql_query("UPDATE pg_notes SET title = '$title', text = '$testo' WHERE noteID = $id AND owner =".$currentUser->ID);
	header('Location:padd.php?s=readNote&ID='.$id);
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
		$template->text = str_replace($bbCode,$htmlCode,$text);
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
	header('Location:padd.php?s=no');
}

else if($mode == 'newH')
{
	$titolo = addslashes($_POST['titolo']);
	$sottotitolo = addslashes($_POST['sottotitolo']);
	$testo = addslashes($_POST['testo']);
	$categ =  isSet($_POST['aleXX']) ? addslashes($_POST['aleXX']) : 'FED';
	$tolink = ($categ != "FED" && $categ != "ROM") ? addslashes($_POST['sottotitolo']) : '';
	
	$particle = ($categ == 'ROM') ? "pgSpecie == 'Roumulana'" : "pgSpecie != 'Roumulana'";
	$particle2 = ($categ == 'ROM') ? "Giornale Romulano" : "Federation Tribune";
	
	if(trim($titolo)!="" && trim($testo)!="" && (PG::mapPermissions('M',$currentUser->pgAuthOMA)))
	{
		mysql_query("INSERT INTO fed_news (toLink,newsTitle, newsSubTitle, newsText, newsTime, aggregator) VALUES ('$tolink','$titolo', '$sottotitolo', '$testo',$curTime,'$categ')");
		if($categ == 'ROM' || $categ == 'FED')
		{
		$curID = $_SESSION['pgID'];
		$oneMonth = $curTime - 2505600; 
		$idR = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLock=0 AND png=0 AND $particle AND pgLastAct >= $oneMonth");
		while($res = mysql_fetch_assoc($idR))
		{ 
			$idA = $res['pgID']; 
			mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (518, $idA, 'NUOVA NEWS', 'E\' stata inserita una nuova News nel $particle2.<br /><p style=\"text-align:center;\"><span style=\"font-size:20px;\">$titolo</span><br />$sottotitolo</p><br /><br />Accedi al Dpadd, sezione $particle2 per leggerla!',$curTime,0)");
		} 
		}
	}
		
	header('Location:padd.php?s=tr'); 
}

else if($mode == 'readTribune')
{
	$id = $vali->numberOnly($_GET['newsID']);
	setlocale(LC_TIME, 'it_IT');
	$template = new PHPTAL('TEMPLATES/padd_tribune_read.htm');
	$template->datae = strftime('%e').' '.ucfirst(strftime('%B')).' '.(date('Y')+377);
	$news = mysql_query("SELECT * FROM fed_news WHERE newsID = $id");
	if(mysql_affected_rows()) $newsA = mysql_fetch_array($news);
	
	 $template->ID = $newsA['newsID'];
	 $template->title = $newsA['newsTitle'];
	 $template->subtitle = $newsA['newsSubTitle'];
	 $template->text = str_replace($bbCode,$htmlCode,$newsA['newsText']);
	 $template->time = (strftime('%e', $newsA['newsTime']).' '.ucfirst(strftime('%B', $newsA['newsTime'])).' '.(date('Y', $newsA['newsTime'])+368));
	 
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
		
		$res = mysql_query("SELECT padID,paddTitle,paddFrom,paddText,paddTime,paddRead, pgUser FROM fed_pad,pg_users WHERE paddTitle NOT LIKE '::special::%' AND paddFrom=pgID AND paddTo = ".$_SESSION['pgID']." AND paddDeletedTo <> 1 ORDER BY paddRead ASC, paddTime DESC");
		$incumingArray = array(0 => array(),1 => array());
			
		while($resA = mysql_fetch_array($res))
		$incumingArray[$resA['paddRead']][] = array(
		'ID' => $resA['padID'],
		'from' => $resA['pgUser'],
		'time' => timeHandler::timestampToGiulian($resA['paddTime']),
		'title' => $resA['paddTitle'],
		'paddText' => substr($resA['paddText'],0,150)
		);
		
		$res = mysql_query("SELECT padID, paddTitle,paddTo,paddText,paddTime,paddRead, pgUser FROM fed_pad,pg_users WHERE paddTo=pgID AND paddFrom = ".$_SESSION['pgID']." AND paddDeletedFrom <> 1 ORDER BY paddRead ASC, paddTime DESC");
		$outcumingArray = array(0 => array(),1 => array());
		while($resA = mysql_fetch_array($res))
		$outcumingArray[$resA['paddRead']][] = array(
		'ID' => $resA['padID'],
		'to' => $resA['pgUser'],
		'time' => timeHandler::timestampToGiulian($resA['paddTime']),
		'title' => $resA['paddTitle'],
		'paddText' => $resA['paddText']
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

$aggregat = ($currentUser->pgSpecie == 'Romulana')? 'ROM' : 'FED';
$tribuneLastNews = mysql_fetch_array(mysql_query("SELECT newsTime FROM fed_news WHERE aggregator = '$aggregat' ORDER BY newsTime DESC LIMIT 1"));

$template->tribuneNews = ($tribuneLastNews['newsTime'] > (time()-86400)) ? true : false;
$template->user = $currentUser;
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