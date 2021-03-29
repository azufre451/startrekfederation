<?php
session_start();

include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php");
PG::updatePresence($_SESSION['pgID']);
#$u=exec('/home/fvkpphtr/tools/miniconda3/bin/python slack_notifier.py pre-approval 3 '.$_SESSION['pgID']);


 
ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_DEPRECATED);

$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);


$prestigioLabels = array(
0=>	  array('name'=>'Inesistente', 												'desc' => 'Nessuno conosce questo personaggio, né sembra possibile trovare alcuna traccia del suo passaggio: nessuno sembra averlo visto, né aver mai sentito parlare di lui in alcun modo. Tanto il suo nome quanto la sua vita passata sono totalmente avvolti nell’ombra. Ogni ricerca su qualunque database potrebbe non produrre alcun risultato: costui sembra, a tutti gli effetti, non esistere.', 'long_desc' => "Nessuno conosce questo personaggio, né sembra possibile trovare alcuna traccia del suo passaggio: nessuno sembra averlo visto, né aver mai sentito parlare di lui in alcun modo. Tanto il suo nome quanto la sua vita passata sono totalmente avvolti nell’ombra. Ogni ricerca su qualunque database potrebbe non produrre alcun risultato: costui sembra, a tutti gli effetti, non esistere."),
1 =>  array('name'=>'Sconosciuto', 												'desc' => 'Questo personaggio è assolutamente sconosciuto. Non se ne sa molto, e solo un numero incredibilmente ristretto di persone sembrano aver sentito parlare di lui. Potrebbe essere estremamente difficile trovare dettagli sul suo nome o sulla sua vita passata: le informazioni si limiteranno ad una o due entry del tutto prive di valore.', 'long_desc' => "Questo personaggio è assolutamente sconosciuto. Non se ne sa molto, e solo un numero incredibilmente ristretto di persone sembrano aver sentito parlare di lui. Potrebbe essere estremamente difficile trovare dettagli sul suo nome o sulla sua vita passata: le informazioni si limiteranno ad una o due entry del tutto prive di valore."),
2 =>  array('name'=>'Anonimo Individuo',										'desc' => 'Questa personaggio è abbastanza anonimo agli occhi dei più. Si sa ben poco in merito al suo nome ed alle sue azioni, oppure quest’ultime non sono affatto degne di nota. Potrebbe ad esempio svolgere un lavoro umile, o vivere ai margini della società. È relativamente semplice rinvenire informazioni sul suo conto tramite una ricerca sui database, ma esse non sono né dettagliate né interessanti.', 'long_desc' => "Questa personaggio è abbastanza anonimo agli occhi dei più. Si sa ben poco in merito al suo nome ed alle sue azioni, oppure quest’ultime non sono affatto degne di nota. Potrebbe essere ad esempio un civile che svolge un lavoro umile, o qualcuno che vive ai margini della società. È relativamente semplice rinvenire informazioni sul suo conto tramite una ricerca sui database, ma esse non sono né dettagliate… né particolarmente interessanti."),
3 =>  array('name'=>'Persona Comune', 											'desc' => 'Questo personaggio è abbastanza comune: il classico cittadino “standard”. Conduce una vita nella media: è l\'uomo comune, mai stato “sotto i riflettori”. Non sembrano esserci in giro voci determinanti sul suo conto, ed una ricerca sui database potrebbe confermare questa assoluta parvenza di normalità.', 'long_desc' => "Questo personaggio è abbastanza comune: il classico cittadino “standard”. Conduce una vita nella media, e possiede una cerchia di contatti ed amicizie assolutamente normali per la sua posizione sociale. Potrebbe essere un membro della Flotta Stellare che non abbia mai fatto parlare di sé, oppure un qualunque Civile o cittadino che non sia mai stato “sotto i riflettori”: per questa ragione, non  sembrano esserci in giro voci particolarmente determinanti sul suo conto. Una ricerca sui database potrebbe confermare questa assoluta parvenza di normalità."),
4 =>  array('name'=>'Persona Notoria', 											'desc' => 'Questo personaggio è generalmente comune, ma potrebbe essere più che noto all’interno di alcuni gruppi sociali di dimensioni consistenti. Potrebbe ad esempio essere una persona a capo di una piccola Divisione, un Professore Associato all\'interno di una Università, una personalità minore della Politica, oppure un criminale abbastanza conosciuto nel “suo giro”. Può essere legittimo non saperne nulla in merito, così come può essere possibile avere informazioni dettagliate su di lui.', 'long_desc' => "Questo personaggio è comune per il grande pubblico, ma potrebbe essere decisamente noto all’interno di alcuni gruppi sociali di dimensioni consistenti. Potrebbe essere ad esempio un membro della Flotta Stellare o del Personale Civile a capo di una piccola divisione o di un dipartimento, titolare di una cattedra in una università, una personalità politica minore all’interno di un Governo, oppure un criminale abbastanza conosciuto nel “suo giro”. Può essere legittimo non saperne nulla in merito, così come può essere possibile avere informazioni dettagliate su di lui."),
5 =>  array('name'=>'Personalità di spicco', 									'desc' => 'Questo personaggio è abbastanza conosciuto. Le sue azioni potrebbero generalmente non essere note nel dettaglio, ma di contro è difficile non averlo mai sentito nominare nel corso della propria vita. Potrebbe essere un politico di lungo corso o emergente, una persona recentemente salita agli onori di cronaca per azioni particolari, un giornalista firmatario di articoli discussi, il Dirigente di qualche grande dipartimento, oppure un criminale noto per dei delitti particolarmente odiosi.', 'long_desc' => "Questo personaggio è abbastanza conosciuto. Le sue azioni potrebbero non essere note nel dettaglio a tutti, ma di contro è certamente difficile non averne mai sentito nominare il nome o il cognome nel corso della propria vita. Potrebbe essere un politico di lungo corso o recentemente emergente, un membro della Flotta Stellare che sia recentemente salito agli onori di cronaca per azioni particolarmente “chiacchierate” all’interno della propria unità, un Giornalista firmatario di articoli molto discussi, un talento nascente nella musica o nello spettacolo, il Dirigente di qualche grande dipartimento o divisione pubblica o privata, oppure un criminale noto per dei delitti particolarmente odiosi."),
6 =>  array('name'=>'Persona Nota', 											'desc' => 'Questo personaggio è decisamente conosciuto. Le sue pubbliche azioni sono più o meno note a tutti, così come la sua posizione nell’organizzazione di appartenenza. Tutti ne conoscono l\'identità: la sua presenza ed i suoi comportamenti non passano inosservati. Potrebbe essere un politico affermato con incarichi di spicco, una persona salita agli onori di cronaca per azioni straordinarie, un Direttore di qualche importante e nota struttura, oppure un criminale macchiatosi di una lunga serie di reati.', 'long_desc' => "Questo personaggio è decisamente conosciuto. Le sue pubbliche azioni sono bene o male note a tutti, così come la posizione da lui occupata nell’organizzazione di appartenenza. Tutti ne conoscono o ne hanno sentito nominare il nome o il cognome, e la sua presenza ed i suoi comportamenti difficilmente passano inosservati all’interno di ogni contesto sociale. Potrebbe essere un politico affermato che ricopre incarichi di spicco, un noto giornalista o una figura conosciuta nel mondo della musica o dello spettacolo, un Ufficiale Superiore all’interno della propria unità, una persona recentemente salita agli onori di cronaca per azioni assolutamente straordinarie, un Direttore di qualche importante e nota struttura pubblica o privata, oppure un criminale macchiatosi di una numerose serie di reati."),
7 =>  array('name'=>'Persona Famosa / Famigerata', 								'desc' => 'Questo personaggio è generalmente conosciuto. Le sue pubbliche azioni sono sotto gli occhi di tutti, così come i suoi trascorsi. Tutti ne conoscono l\'identità, insieme ai dettagli salienti della sua vita - finanche privata. La sua presenza in ogni contesto è evidente, le sue azioni non passano inosservate. Potrebbe essere un funzionario di Governo titolare di uno o più dicasteri, un Capitano noto per le sue gesta, un compositore o un attore dalla carriera ormai pluripremiata, uno scienziato la cui scoperta è sulla bocca di tutti, oppure il capo di una organizzazione criminale di buona vastità.', 'long_desc' => "Questo personaggio è generalmente conosciuto. Le sue pubbliche azioni sono sotto gli occhi di tutti, così come la posizione da lui occupata all’interno della sua organizzazione d’appartenenza. Tutti ne conoscono il nome ed il cognome, insieme a diversi dettagli della sua vita (finanche di quella privata). La sua presenza all’interno di ogni contesto è sotto gli occhi di tutti, ed ogni sua azione risulta essere quasi naturalmente “chiacchierata” all’interno della sua comunità d’appartenenza. Potrebbe essere un alto funzionario di un Governo titolare di uno o più dicasteri, un Capitano particolarmente noto per le sue gesta, un compositore o un attore dalla carriera ormai pluripremiata, uno scienziato la cui scoperta è sulla bocca di tutti per la sua enorme portata, oppure il capo di una organizzazione criminale di discreta vastità."),
8 =>  array('name'=>'Persona Celebre / Criminale Ricercato ', 					'desc' => 'Questo personaggio è generalmente conosciuto. Le sue pubbliche azioni sono sotto gli occhi di tutti, così come i suoi trascorsi. Tutti ne conoscono l\'identità, insieme praticamente ogni dettaglio della sua vita - pubblica o privata che sia. La sua presenza in ogni contesto è evidente, ogni sua azione non passa inosservata. Potrebbe essere un Capo di Governo, il comandante di una Forza Armata, un inventore geniale in grado di rivoluzionare drasticamente la sua società, un compositore, un attore o uno scienziato ormai entrato nella storia, oppure il capo di una organizzazione criminale di considerevole vastità.', 'long_desc' => "Questo personaggio è assolutamente conosciuto. Le sue pubbliche azioni sono sotto gli occhi di tutti, così come la posizione da lui occupata all’interno della sua organizzazione d’appartenenza. Tutti ne conoscono il nome ed il cognome, insieme praticamente ogni dettaglio della sua vita, pubblica o privata che sia. La sua presenza all’interno di ogni contesto è sotto gli occhi di tutti, ed ogni sua azione risulta essere naturalmente “chiacchierata” e discussa anche al di fuori della sua comunità d’appartenenza. Potrebbe essere un Capo di Governo, il comandante di una Forza Armata, un inventore geniale in grado di rivoluzionare drasticamente la sua società, un compositore, un attore o uno scienziato ormai entrato nella storia, oppure il capo di una organizzazione criminale di considerevole vastità."),
9 =>  array('name'=>'Eroe della Federazione / Signore del Crimine', 			'desc' => 'Questo personaggio è universalmente conosciuto. Le azioni che lo hanno portato a raggiungere tale livello di notorietà sono ormai entrate nei libri di storia, e dopo la sua morte non c’è dubbio che si continuerà ancora a parlare di lui. L’estensione della sua fama è praticamente planetaria, e non c’è alcun dubbio che sia conosciuto sia dalla sua civiltà d’appartenenza che dalle altre circostanti.', 'long_desc' => "Questo personaggio è universalmente conosciuto. Le azioni che lo hanno portato a raggiungere tale livello di notorietà sono ormai entrate nei libri di storia, e dopo la sua morte non c’è dubbio che si continuerà ancora a parlare di lui. L’estensione della sua fama è praticamente planetaria, e non c’è alcun dubbio che sia conosciuto sia dalla sua civiltà d’appartenenza che dalle altre circostanti."),
10 => array('name'=> 'Leggenda vivente / Efferato Criminale', 					'desc' => 'Questo personaggio è una Leggenda Vivente. In vita o in morte, non c’è anima viva che non abbia sentito parlare di lui, o non conosca le sue gesta: l’entità della sua fama è talmente palese da non aver bisogno di descrizioni - solo il suo nome è sufficiente a richiamare il mito.', 'long_desc' => "Questo personaggio è una Leggenda Vivente. In vita o in morte, non c’è anima viva che non abbia sentito parlare di lui, o non conosca le sue gesta: l’entità della sua fama è talmente palese da non aver bisogno di descrizioni - solo il suo nome è sufficiente a richiamare il mito."));


if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)){header('Location:scheda.php'); exit;} 

$mode = (isSet($_GET['s'])) ? $_GET['s'] : '';

if($mode == 'closeSession'){

	$vali = new validator();
	$rec = $vali->numberOnly($_GET['ida']); 

	$ree  = mysql_fetch_assoc(mysql_query("SELECT sessionOwner,sessionLabel,sessionStart FROM federation_sessions WHERE sessionID = '$rec'"));
	$pig = new PG($ree['sessionOwner']);
	$seTitle = addslashes($ree['sessionLabel']);
	$seNow = addslashes( ((time()-(int)($ree['sessionStart']))/3600) );
	$pig->sendPadd('OFF: Chiusura Sessione',"Un admin ha chiuso la tua sessione: $seTitle dopo <b>$seNow ore</b> di inattività. Non sono stati assegnati punti per questa sessione.");
	
	mysql_query("UPDATE federation_sessions SET sessionEnd = $curTime, sessionStatus = 'CLOSED' WHERE sessionID = '$rec'");
	if(mysql_affected_rows())
		echo json_encode("OK");
	exit;
}
if($mode == 'deletePoints')
{
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;
	$vali = new validator();
	$rec = $vali->numberOnly($_POST['idR']); 
	
	$recIL = mysql_query("SELECT pgPoints, pgID, points FROM pg_users,pg_users_pointStory WHERE pgID = owner AND recID = $rec");
	if (mysql_affected_rows())
	{
		$reaL = mysql_fetch_assoc($recIL);
		$pgPoints = $reaL['pgPoints'];
		$points = $reaL['points'];
		$pgID = $reaL['pgID'];
		
		$braker= false;
		for($i = $pgPoints-$points; $i < $pgPoints; $i++)
		{
			if($i % 12 == 0) $braker = true;
		}
		
		if(!$braker)
		{
			mysql_query("DELETE FROM pg_users_pointStory WHERE recID = $rec");
			$ok = mysql_affected_rows();
			mysql_query("UPDATE pg_users SET pgPoints = pgPoints - $points WHERE pgID = $pgID");
			
			if($ok) echo json_encode(array('OK' => 'Ok'));	 
		}
		else echo json_encode(array('OK' => 'No'));	
	}
	exit;
	
}

if($mode == 'setMyPresence')
{
	$ptpVal='';
	$me=$currentUser->ID;
	mysql_query( "DELETE FROM pg_users_presence WHERE pgID = '$me'");

	foreach(range(0, 6, 1) as $p)
	{
		$ptp=addslashes($_POST['ppres_'.$p]); 
		
		mysql_query( "INSERT INTO pg_users_presence(pgID,day,value) VALUES ('$me','$p','$ptp')" );
		
	}


	header('Location:multitool.php?s=mypresence');
	exit;
}
if($mode == "achiAssignBackground")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

	$vat =  $vali->numberOnly($_GET['what']);
	$pgID = $vali->numberOnly($_GET['pgID']);

	$tp = new PG($pgID);
	
	mysql_query("INSERT INTO pg_achievement_assign (owner,achi,timer) VALUES ($pgID,'$vat',".time().")");
	
	$res = mysql_query("SELECT aText,aImage FROM pg_achievements WHERE aID = '$vat'");
	if(mysql_error()) {echo mysql_error();exit;}
	$resA = mysql_fetch_array($res);
	$Descri =$resA['aText'];
	$ima =$resA['aImage'];
	
	$cString = addslashes("Congratulazioni!!<br />Hai sbloccato un nuovo achievement!<br /><br /><p style='text-align:center'><img src='TEMPLATES/img/interface/personnelInterface/$ima' /><br /><span style='font-weight:bold'>$Descri</span></p><br />Il Team di Star Trek: Federation");
	$eString = addslashes("Hai un nuovo achievement!::$Descri");
	
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES (".$_SESSION['pgID'].",$pgID,'OFF: Nuovo Achievement!','$cString',".time().",0)");
	if(mysql_error()) {echo mysql_error();exit;}
	mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (".$_SESSION['pgID'].",$pgID,'::special::achiev','$eString',".time().",0,'TEMPLATES/img/interface/personnelInterface/$ima')");
	if(mysql_error()) {echo mysql_error();exit;}

	header("Location:multitool.php?viewApprovals=true");

}



if($mode == "remindBackground")
{

	if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)) exit;

	$pgID = $vali->numberOnly($_GET['pgID']);


	$tp = new PG($pgID);
	$usera = $tp->pgUser;
	$tp->sendPadd('Attesa per approvazione BG',"Ciao $usera<br /><br />Ti comunichiamo che abbiamo visionato i dati relativi alla registrazione del PG ed il Background.<br /><br />Non abbiamo potuto procedere alla valutazione del BG, che è ancora privo di alcuni elementi molto importanti. Per questo ti chiederei di provvedere a completare le parti mancanti del Background non appena avrai un momento di tempo.<br /><br />
Di seguito trovi alcune risorse utili per scrivere il BG. Lo staff è a tua disposizione qualora avessi delle domande sulla compilazione!<br /><br />
&raquo; <a href=\"javascript:dbOpenToTopic(242)\" class=\"interfaceLink\"> Lauree, Medaglie e Stato di Servizio </a>
&raquo; <a href=\"javascript:dbOpenToTopic(241)\" class=\"interfaceLink\"> Il Background del PG </a>

	 Sentiti libero di contattare le Guide per informazioni sulla compilazione del BG

	 Intanto buon gioco, <br /><br /><br />Lo staff",$_SESSION['pgID']);

	mysql_query("UPDATE pg_users_bios SET lastReminder = ".time().", supervision = ".$_SESSION['pgID']." WHERE pgID = $pgID");

	header("Location:multitool.php?viewLasts=true");

}
if($mode == "preapproveBackground")
{
	if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)) exit;
	if ($currentUser->pgAuthOMA == 'M') exit;

	$pgID = $vali->numberOnly($_GET['pgID']);
	$targetUser = new PG($pgID);

	mysql_query("UPDATE pg_users_bios SET valid = 1 WHERE pgID = $pgID");

	$real= mysql_query("SELECT pgID FROM pg_users WHERE pgAuthOMA IN ('SM','A')");
	$usera = $currentUser->pgUser;
	$userTarget = $targetUser->pgUser;
	$userTargetID = $targetUser->ID;
	
	$targetUser->sendNotification("Preapprovazione BG: ".$userTarget,"$usera ha pre-approvato il background che ora è in attesa di approvazione Admin!",$_SESSION['pgID'],"TEMPLATES/img/interface/index/blevinrevin_02.png",'schedaOpen');

	while($rea = mysql_fetch_assoc($real))
	{
		$tp = new PG($rea['pgID']);
		

		$tp->sendNotification("GreenLight Guide: ".$userTarget,"$usera ha pre-approvato il background che è in attesa di approvazione",$_SESSION['pgID'],"TEMPLATES/img/interface/index/blevinrevin_02.png",'masterShadow');

	}

	exec('/home/fvkpphtr/tools/miniconda3/bin/python /home/fvkpphtr/public_html/utils/slack_notifier.py pre-approval '.$pgID.' '.$currentUser->ID);

	header("Location:multitool.php?viewApprovals=true");

}
if($mode == "approveBackground")
{
	
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

	$pgID = $vali->numberOnly($_GET['pgID']);


	$lar = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as TLP FROM pg_users_bios WHERE pgID = $pgID AND valid = 2"));

	mysql_query("UPDATE pg_users_bios SET pgID = 6, valid = 0 WHERE pgID = $pgID AND valid = 2");
	mysql_query("UPDATE pg_users_bios SET valid = 2 WHERE pgID = $pgID");

	$tp = new PG($pgID);
	$usera = $tp->pgUser;
	
	

	if((int)$lar['TLP'] == 0)
		{
			$tp->sendPadd('Approvazione BG',"Ciao $usera<br />Ti comunichiamo che abbiamo visionato i dati relativi alla registrazione del PG ed il Background. Tutto risulta in ordine ed il BG e' ora approvato! Ricorda che le eventuali aggiunte e modifiche (comunque sempre incoraggiate) dovranno essere approvate: se modificherai la scheda rimarrà sempre visibile (agli altri) il BG approvato, fino ad approvazione di quello nuovo.<br /><br /> Ricorda anche che da ora non è più possibile chiedere il reset delle abilità / caratteristiche: qualora volessi modificarle per l'ultima volta, ti invitiamo a chiedere immediatamente allo Staff!<br /><br />Ti auguriamo buon gioco in land,<br />Lo staff",'518');

			$tp->addNote('Approvazione BG e scheda',$currentUser->ID);
			exec('/home/fvkpphtr/tools/miniconda3/bin/python /home/fvkpphtr/public_html/utils/slack_notifier.py approval '.$pgID.' '.$currentUser->ID);
		}
	else{
		$tp->sendNotification("Approvazione Revisione BG","Il tuo BG è stato revisionato e approvato",$_SESSION['pgID'],'/TEMPLATES/img/interface/fed_tick.png','schedaOpen');
	}

	
	header("Location:multitool.php?viewApprovals=true");

}

if($mode == "blockBulk")
{
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	$res = mysql_query("UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)");
	
	echo json_encode(array('stat'=>true)); exit;
}

if($mode == "reassignBulk")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$placeLocReassign = $_POST['placeAssign'];
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	$res = mysql_query("UPDATE pg_users SET pgLocation='$placeLocReassign',pgRoom = '$placeLocReassign' WHERE pgUser IN ($lisOuser)");
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
	echo json_encode(array('stat'=>true)); exit;
}

if($mode == 'addsstoBulk')
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;

	$sel=explode(',',$_POST['listof']);
	$lisOuser="";


	$cross = addslashes(($_POST['cross']));
	$placer = addslashes(($_POST['placer']));
	$dateG = str_pad($vali->numberOnly($_POST['dataG']),2,'0',STR_PAD_LEFT);
	$dateM = str_pad($vali->numberOnly($_POST['dataM']),2,'0',STR_PAD_LEFT);
	$dateA = $vali->numberOnly($_POST['dataA']);
	$dateDef = $dateA.'-'.$dateM.'-'.$dateG;
	$what = addslashes(($_POST['what']));

	$padTit = 'Update Stato di Servizio';
	$paddTex = "È stato aggiunto nella tua scheda PG un nuovo elemento allo stato di servizio";

	foreach ($sel as $auth)
		if(trim($auth)!='')
		{	
			$tUser = trim(addslashes($auth));
			$rea = mysql_fetch_array(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$tUser'"));
			$pgID = $vali->numberOnly($rea['pgID']);
			$selectedDUser = new PG($pgID);
			mysql_query("INSERT INTO pg_service_stories (owner,timer,text,placer,postLink,type) VALUES ($pgID,'$dateDef','$what','$placer','$cross','SERVICE')");
			$selectedDUser->sendNotification($padTit,$paddTex, $currentUser->ID ,"https://oscar.stfederation.it/SigmaSys/logo/logoufp.png",'schedaSstoOpen');
		}

	echo json_encode(array('stat'=>true)); exit;
}

if($mode == "getProfilingPadds")
{

	if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)) exit;
	$ider = addslashes($_POST['ider']);

	$padder=array();

	

	$res = mysql_query("SELECT padID, paddTitle, paddText, paddFrom, paddTo, paddTime, fromPGT.pgAvatarSquare , toPGT.pgUser as ToPG, fromPGT.pgUser as FromPG,fromPGT.pgSpecie as pgSpecie,fromPGT.pgSesso as pgSesso, toPGT.pgID as ToPGID, fromPGT.pgID as FromPGID, ordinaryUniform FROM fed_pad, pg_users  AS fromPGT, pg_users AS toPGT, pg_ranks WHERE prio = fromPGT.rankCode AND toPGT.pgID = paddTo AND fromPGT.pgID = paddFrom AND (paddTo = $ider OR paddFrom = $ider) AND paddType = 3 ORDER BY paddTime DESC");
	while($atpl = mysql_fetch_assoc($res))
	{

		
		$atpl['pcontent'] = cdb::bbcode($atpl['paddText']);
		$atpl['phour'] = date('H',$atpl['paddTime']);
		$atpl['pmin'] = date('i',$atpl['paddTime']);
		$atpl['pday'] = timeHandler::extrapolateDay($atpl['paddTime']); 

		$padder[] = $atpl;

	}
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
	echo json_encode(array('padder'=>$padder)); exit;
}

if($mode == "setSeclar")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$seclar = $vali->numberOnly($_POST['seclar']);
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	$res = mysql_query("UPDATE pg_users SET pgSeclar=$seclar WHERE pgUser IN ($lisOuser)");

	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}


if($mode == "addMedals")
{
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$medal = $vali->numberOnly($_POST['medal']);
	$timer = $vali->numberOnly($_POST['timer']);

	$lisOuser="";

	foreach ($sel as $auth)
		if(trim($auth)!='')
		{	
			$tUser = trim(addslashes($auth));
			$rea = mysql_fetch_array(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$tUser'"));
			$pgID = $vali->numberOnly($rea['pgID']);
			$selectedDUser = new PG($pgID);
			$selectedDUser->addMedal($medal,$timer);
			$selectedDUser->addNote("Aggiunta medaglia $medal",$currentUser->ID);
		}
			
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}



if($mode == "setPrestige")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$points = $vali->numberOnly($_POST['prestigeLevel']);
	$reason = addslashes($_POST['reason']);
	
	$lisOuser="";


	foreach ($sel as $auth)
	{
		$auth = addslashes(trim($auth));
		if($auth!='')
		{
			$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
			$pgID=$myco['pgID'];	
			$pigo = new PG($pgID);
			
			 $curLevel = mysql_fetch_assoc(mysql_query("SELECT pgPrestige FROM pg_users WHERE pgID = $pgID"));
			$differential = $points-(int)$curLevel['pgPrestige'];
			if ($reason != "")
				mysql_query("INSERT INTO pg_prestige_stories (owner,time,reason,variation) VALUES($pgID,$curTime,'$reason',$differential)");	
			

			mysql_query("UPDATE pg_users SET pgPrestige = $points WHERE pgID = $pgID");
			
			
			$pigo->addNote("Prestigio modificato ($points)",$currentUser->ID);	
			$pigo->sendPadd('OFF: Notorietà',"Il tuo livello di notorietà è cambiato in [COLOR=YELLOW][B]".$prestigioLabels[$points]['name']."[/COLOR][/B] per la seguente ragione: [B]\"$reason\"[/B].

			[COLOR=YELLOW][B]".$prestigioLabels[$points]['name']."[/COLOR][/B]:
			[I]".$prestigioLabels[$points]['long_desc']."[/I] ",$currentUser->ID);
		}
	}

	 

	echo json_encode(array('stat'=>true)); exit;
}

if($mode == "addServiceObj")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$oID = $vali->numberOnly($_POST['obID']);
	 
	$lisOuser="";


	foreach ($sel as $auth)
	{
		$auth = addslashes(trim($auth));
		if($auth!='')
		{
			$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
			$pgID=$myco['pgID'];	
			
			mysql_query("SELECT 1 FROM fed_objects_ownership WHERE owner = $pgID AND oID = $oID");
			if(!mysql_affected_rows())
			{
				mysql_query("INSERT INTO fed_objects_ownership (owner,oID) VALUES ($pgID, $oID)");
			}
		}
	}
	echo json_encode(array('stat'=>true)); exit;
}
if($mode == "removeServiceObj")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$oID = $vali->numberOnly($_POST['obID']);
	 
	$lisOuser="";
	foreach ($sel as $auth)
	{
		$auth = addslashes(trim($auth));
		if($auth!='')
		{
			$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
			$pgID=$myco['pgID'];	
 			mysql_query("DELETE FROM fed_objects_ownership WHERE owner = $pgID AND oID = $oID");
			mysql_query("DELETE FROM pg_current_dotazione WHERE ref = $oID AND owner = $pgID AND type = 'OBJECT'");
		}
	}
	echo json_encode(array('stat'=>true)); exit;
}


if($mode == 'delete' || $mode == 'bavosize')
{
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;

	//if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;

	$sel=explode(',',$_POST['listof']);
	

	foreach ($sel as $auth)
		{
			$auth = addslashes(trim($auth));
			if($auth!='')
			{
				$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
				$pgID=$myco['pgID'];	
				$pigo = new PG($pgID);
				
				if($mode == 'bavosize'){
					$pigo->bavosize();
					$pigo->addNote("Bavosizzato",$currentUser->ID);
				}
				elseif($mode == 'delete') $pigo->delete();
			}
		}
	 
	echo json_encode(array('stat'=>true)); exit;


}

if($mode == 'group')
{
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;

	$sel=explode(',',$_POST['listof']);
	
	$last=NULL;
	$affectedList=array();
	$minIscriDate=$curTime;

	foreach ($sel as $auth)
		{
			$auth = addslashes(trim($auth));
			if($auth!='')
			{
				$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID,iscriDate FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
				$minIscriDate = min($myco['iscriDate'],$minIscriDate);
				$affectedList[] = $myco['pgID'];
			}
		}
	 
	$affectedListStr=implode(",",$affectedList);
	$mainPGID=end($affectedList);

	mysql_query("UPDATE pg_users SET mainPG = '$mainPGID', pgType = 'DOPPIO' WHERE pgID IN ($affectedListStr) AND pgID <> '$mainPGID'");
	mysql_query("UPDATE pg_users SET mainPG = '$mainPGID', pgType = 'MAIN', iscriDate='$minIscriDate' WHERE pgID = '$mainPGID'");


 	if(!mysql_error())
		echo json_encode(array('stat'=>true));

	exit;

}


if($mode == 'switch')
{ 
	include_once('includes/abilDescriptor.php');
	
	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;

	$sel=explode(',',$_POST['listof']);
	//echo count($sel);
	if (count($sel) >= 2)

	{	
		$last=NULL;
		$affectedList=array();
		$minIscriDate=$curTime;

		$OLDPGU=addslashes(trim($sel[0]));
		$NEWPGU=addslashes(trim($sel[1]));

		$mycoOLD =  mysql_fetch_assoc(mysql_query("SELECT pgID,pgPoints,pgSpecialistPoints,iscriDate FROM pg_users WHERE pgUser = '$OLDPGU' LIMIT 1"));
		$mycoNEW =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$NEWPGU' LIMIT 1"));
		 
		$mycoOLDIDD = $mycoOLD['pgID'];
		$mycoOLDPOINTS = $mycoOLD['pgPoints'];
		$mycoOLDISCRIDATE = $mycoOLD['iscriDate'];		
		$mycoOLDSPEC = $mycoOLD['pgSpecialistPoints']; 
		$mycoNEWIDD = $mycoNEW['pgID'];

		
					mysql_query("UPDATE pg_users SET mainPG = $mycoNEWIDD WHERE mainPG = $mycoOLDIDD;");
					mysql_query("UPDATE pg_users SET mainPG = $mycoNEWIDD, pgType='OLD' WHERE pgID = $mycoOLDIDD;");
					mysql_query("UPDATE pg_users SET mainPG = $mycoNEWIDD, pgType='MAIN' WHERE pgID = $mycoNEWIDD;");

					mysql_query("DELETE FROM pg_users_pointStory WHERE owner = $mycoNEWIDD;");
					mysql_query("UPDATE pg_users_pointStory SET owner = $mycoNEWIDD, causeE = CONCAT(causeE,CONCAT(' *',(SELECT pgUser FROM pg_users WHERE pgID = $mycoOLDIDD))) WHERE owner = $mycoOLDIDD;");

					//mysql_query("CREATE TABLE temp1 AS SELECT iscriDate FROM pg_users WHERE pgID = $mycoOLDIDD;");
					//mysql_query("UPDATE pg_users SET iscriDate = (SELECT iscriDate FROM temp1 WHERE 1 LIMIT 1),pgPoints = (SELECT SUM(points) FROM pg_users_pointStory WHERE owner = $mycoNEWIDD), pgSpecialistPoints = (SELECT pgSpecialistPoints FROM pg_users_pointStory WHERE owner = $mycoOLDIDD) WHERE pgID = $mycoNEWIDD;");

					mysql_query("UPDATE pg_users SET pgSpecialistPoints = 0,pgPoints=0 WHERE pgID = $mycoOLDIDD;");
					mysql_query("UPDATE pg_users SET pgSpecialistPoints = $mycoOLDSPEC, iscriDate='$mycoOLDISCRIDATE',pgPoints='$mycoOLDPOINTS'  WHERE pgID = $mycoNEWIDD;");

					//mysql_query("DROP TABLE temp1;");

					mysql_query("INSERT INTO pg_notestaff(pgFrom, pgTo, what, timeCode) VALUES (518,$mycoOLDIDD, CONCAT('Cambio pg da: ',CONCAT((SELECT pgUser FROM pg_users WHERE pgID = $mycoOLDIDD),CONCAT(' a: ',(SELECT pgUser FROM pg_users WHERE pgID = $mycoNEWIDD)))), UNIX_TIMESTAMP());");

					mysql_query("INSERT INTO pg_notestaff(pgFrom, pgTo, what, timeCode) VALUES (518,$mycoNEWIDD, CONCAT('Cambio pg da: ',CONCAT((SELECT pgUser FROM pg_users WHERE pgID = $mycoOLDIDD),CONCAT(' a: ',(SELECT pgUser FROM pg_users WHERE pgID = $mycoNEWIDD)))), UNIX_TIMESTAMP());");

					mysql_query("UPDATE fed_food_replications SET user = $mycoNEWIDD WHERE user = $mycoOLDIDD;");
					mysql_query("UPDATE fed_food SET presenter = $mycoNEWIDD WHERE presenter = $mycoOLDIDD;");
					
		$oldPGObj = new PG($mycoOLDIDD);
		$oldPGAbil = new abilDescriptor($mycoOLDIDD);
		$oldPGAbil->resetAndRestore(array('IQ' => 5,'DX' => 5,'HT' => 5,'PE' => 4,'WP' => 4));
		$oldPGAbil->superImposeRace($oldPGObj->pgSpecie);
		$oldPGObj->sendNotification("Cambio PG","La procedura di cambio PG da $OLDPGU a $NEWPGU è stata completata!",$_SESSION['pgID'],"TEMPLATES/img/interface/index/blevinrevin_02.png",'schedaOpen');

		$newPGObj = new PG($mycoNEWIDD);
		$newPGAbil = new abilDescriptor($mycoNEWIDD);
		$newPGAbil->resetAndRestore(array('IQ' => 5,'DX' => 5,'HT' => 5,'PE' => 4,'WP' => 4));
		$newPGAbil->superImposeRace($newPGObj->pgSpecie);
		$newPGObj->sendNotification("Cambio PG","La procedura di cambio PG da $OLDPGU a $NEWPGU è stata completata!",$_SESSION['pgID'],"TEMPLATES/img/interface/index/blevinrevin_02.png",'schedaOpen');

		$currentUser->sendNotification("Cambio PG","La procedura di cambio PG da $OLDPGU a $NEWPGU è stata completata!",$_SESSION['pgID'],"TEMPLATES/img/interface/index/blevinrevin_02.png",'schedaOpen');

	 	if(!mysql_error())
			echo json_encode(array('stat'=>true));
	}
	exit;

}


if($mode == "setIncarico")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$assegnazione = addslashes($_POST['assegnazione']);
	$incarico = addslashes($_POST['incarico']);
	$dipartimento = addslashes($_POST['dipartimento']);
	$divisione = addslashes($_POST['divisione']);


	$lisOuser="";
	foreach ($sel as $auth)
		{
			$auth = addslashes(trim($auth));
			if($auth!='')
			{	
				$auth=addslashes($auth);
				$adNote = 'Piallatura Incarico e Assegnazione: '.$incarico;
				$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
				$pgID=$myco['pgID'];
				$pigo = new PG($pgID);

				mysql_query("DELETE FROM pg_incarichi WHERE pgID = $pgID");				
				mysql_query("INSERT INTO pg_incarichi (pgID,incIncarico,incSezione,incDivisione,incDipartimento,pgPlace,incMain) VALUES((SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1),'$incarico',$pgID,'$divisione','$dipartimento','$assegnazione','1')");
				$pigo->addNote($adNote,$currentUser->ID);
			}
		}
	 
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}


if($mode == "addNote")
{
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']); 
	
	$lisOuser="";

	foreach ($sel as $auth)
	{
		$auth = addslashes(trim($auth));
		if($auth!=''){
			$myco =  mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$auth' LIMIT 1"));
			$pgID=$myco['pgID'];
			$pigo = new PG($pgID);
			$pigo->addNote($_POST['note'],$currentUser->ID);
		} 
	}
}

if($mode == 'timeline')
{
	$template = new PHPTAL('TEMPLATES/multitool_timeline.html');
	$YTP=array();
	$VTE=array();
	$minAssign=array();
	$metadata=array();
	$minmax=mysql_query("SELECT UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(T.wherer),'\'',''),'.',''),',',''),'-','_'),' ','_')) as whererL, MIN(YEAR(T.dater)) as minmin, MAX(YEAR(T.dater)) as maxmax FROM (SELECT wherer, dater FROM pg_user_stories UNION SELECT placeName as wherer, '2398-01-01' as dater FROM pg_incarichi,pg_places WHERE placeID = pgPlace) as T WHERE YEAR(dater) > 2340 GROUP BY whererL");
	$minmaxArray=array();
	while ($tm=mysql_fetch_assoc($minmax))
		$minmaxArray[$tm['whererL']] = array('min'=> $tm['minmin'],'max'=>$tm['maxmax']);
 	 
	$ppl=mysql_query("

		(SELECT pgUser,ordinaryUniform,pg_users.pgID, UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(wherer),'\'',''),'.',''),',',''),'-','_'),' ','_')) as wherer, YEAR(dater) as year,pgSesso,pgSpecie FROM pg_users,pg_user_stories,pg_ranks WHERE YEAR(dater) > 2340 AND pg_user_stories.pgID = pg_users.pgID AND rankCode = prio ORDER BY dater)
		UNION
		(SELECT pgUser,ordinaryUniform,pg_users.pgID, UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(placeName),'\'',''),'.',''),',',''),'-','_'),' ','_')) as wherer, '2398' as year,pgSesso,pgSpecie FROM pg_users,pg_ranks,pg_incarichi,pg_places WHERE placeID = pgPlace AND incActive = 1 AND  rankCode = prio AND pg_incarichi.pgID = pg_users.pgID)
		");
	while($res = mysql_fetch_assoc($ppl))
	{
		if(!array_key_exists($res['wherer'],$YTP))
			$YTP[$res['wherer']] = array();

		if(!array_key_exists($res['pgUser'],$YTP[$res['wherer']]))
		{

			$YTP[$res['wherer']][$res['pgUser']] = array();
			#echo $res['wherer'] . "<br />";
			$minDate= $minmaxArray[$res['wherer']]['min'];
			$maxDate= $minmaxArray[$res['wherer']]['max'];

			foreach(range($minDate, $maxDate, 1) as $p)
			{
				$YTP[$res['wherer']][$res['pgUser']][$p] = 0;
			}
		}

		$YTP[$res['wherer']][$res['pgUser']][$res['year']] = 2;
		

		if(!array_key_exists($res['pgUser'],$VTE))
			$VTE[$res['pgUser']]=array();
		if(!array_key_exists($res['year'],$VTE[$res['pgUser']]))
			$VTE[$res['pgUser']][$res['year']]=array();
		

		$VTE[$res['pgUser']][$res['year']] [] = $res['wherer'];


		if(!array_key_exists($res['pgUser'],$minAssign))
			$minAssign[$res['pgUser']]=$res['year'];

		if( $res['year'] < $minAssign[$res['pgUser']])
			$minAssign[$res['pgUser']] = $res['year'];

		$metadata[$res['pgUser']] = array('pgUser'=>$res['pgUser'],'pgID'=>$res['pgID'],'ordinaryUniform'=>$res['ordinaryUniform'],'pgSesso'=>$res['pgSesso'],'pgSpecie'=>$res['pgSpecie']);
		
	}
	

	foreach ($YTP as $ship=>$people)
	{
		if (count($people)>=5)
		{
			foreach($people as $pgID=>$years)
			{
				//if ($pgID != 'Rokan') continue;
				
				foreach($years as $year=>$tval)
				{
					if ($tval == 0 & $year >= $minAssign[$pgID])
					{
						//echo "Buco per".' '.$pgID.' su '.$ship.' nel '.$year.' (MA: '.$minAssign[$pgID].')<br />';
						$l=0;
						$m=0;
						//print_r($VTE[$pgID]);
						
						$yearP=$year;
						while($l==0 & $yearP >= $minAssign[$pgID])
						{
						//echo "TEST Y" . $yearP .'
						//';
						if (array_key_exists($pgID,$VTE) & array_key_exists($yearP,$VTE[$pgID]))
						{
							//echo "Test anno " . $yearP . '(CNT '.count($VTE[$pgID][$yearP]).'<br/>
							//';
				
							//print_r($VTE[$pgID][$yearP]);
							if (count($VTE[$pgID][$yearP]))
							{
								foreach($VTE[$pgID][$yearP] as $shipTo)
								{
									if($shipTo == $ship)
									{
										$l=1;
										//echo "Trovata assegnazione su stessa nave: risolto
										//0";
										
									}
									else{
										$m=1;
										//echo "Trovata assegnazione su DIVERSA nave: risolto
										//1";
									}
								}
							}
						}
							$yearP = $yearP-1;
							if($l==1 & $m == 0)
								$YTP[$ship][$pgID][$year] = 1;
							else
								$YTP[$ship][$pgID][$year] = 0;

						}

						

					}
				}
			}
		}
	}
	


	$template->ppl= $YTP;
	$template->ppl_metadata = $metadata;
	$template->minmaxArray =$minmaxArray;
		try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
	exit;
}

if($mode== "stor")
{
	$template = new PHPTAL('TEMPLATES/scheda_storicoPunti.htm');
	
	$arra=array();
	$res = mysql_query("SELECT recID,p1.pgUser as PB, p2.pgUser as PA,points,cause,causeE,causeM,timer FROM pg_users_pointStory,pg_users as p1, pg_users as p2 WHERE p1.pgID = assigner AND p2.pgID = owner ORDER BY timer DESC LIMIT 500");
	
	$causes = array();
	
	while($rea=mysql_fetch_array($res))
	{
		$arra[]=$rea;
		if (!in_array($rea['causeE'],$causes))
			$causes[$rea['causeE']] = '#' . strtoupper(dechex(rand(0,16777215)));	
	}
	
	$template->raa=$arra;
	$template->causes=$causes;
	$template->gameOptions = $gameOptions;
	
	if(PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->isAdmin=true;
	
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
	exit;
}

if($mode == "setMostrina")
{
	if (!PG::mapPermissions('SM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$mostrina = $vali->numberOnly($_POST['mostrina']);
	foreach ($sel as $auth)
		if($auth!=''){	
						$tUser = trim(addslashes($auth));
						PG::setMostrinaL($tUser,$mostrina);
						Mailer::notificationMail("Il PG $sel e' stato promosso o degradato a $mostrina",$currentUser);
		}
	echo json_encode(array('stat'=>true)); exit;	
}

/*if($mode == "sendModeration")
{
	if (!PG::mapPermissions('MM',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$moder = addslashes($_POST['moder']);
	foreach ($sel as $auth)
		if($auth!='')
		{	
						$tUser = trim(addslashes($auth));
						PG::sendModerationL($tUser,$moder);
		}
	echo json_encode(array('stat'=>true)); exit;	
}*/

if($mode == "setSalute")
{
	if (!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$sal = htmlentities(addslashes(($_POST['salute'])));
	
	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	//echo "UPDATE pg_users SET pgSalute = '$sal' WHERE pgUser IN ($lisOuser)";
	mysql_query("UPDATE pg_users SET pgSalute = '$sal' WHERE pgUser IN ($lisOuser)");
	
	if(!mysql_error()) 
	{
	foreach ($sel as $auth)
		if($auth!=''){	
						$tUser = trim(addslashes($auth));
						mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (".$_SESSION['pgID'].",(SELECT pgID FROM pg_users WHERE pgUser = '$tUser'), 'Modifica Salute', 'Ciao!\nIl tuo stato di salute e\' stato modificato in: $sal. Questo messaggio e\' stato generato automaticamente. Buon gioco!',".time().",0)");
		}
	}
	
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "addPoints")
{	
	if (!PG::mapPermissions('SL',$currentUser->pgAuthOMA)) exit;
	$sel=explode(',',$_POST['listof']);
	$code = $vali->killChars($_POST['code']);
	$detail = addslashes($_POST['detail']);
	
	$p=0;$l="A";
if($code == "a1"){$p=1;$little="Q1";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a2"){$p=2;$little="Q2";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a3"){$p=3;$little="Q3";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "f2"){$p=2;$little="F2";$mex = "Punti Minishot";$l="SL";}
elseif($code == "a4"){$p=4;$little="Q4";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a5"){$p=5;$little="Q5";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a6"){$p=6;$little="Q6";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a7"){$p=7;$little="Q7";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a8"){$p=8;$little="Q8";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a9"){$p=9;$little="Q9";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "a10"){$p=10;$little="Q10";$mex = "Partecipazione Giocata";$l="SL";}
elseif($code == "b5"){$p=5;$little="B1";$mex = "Mastering One Shot";$l="SL";}
elseif($code == "r2"){$p=2;$little="R";$mex = "Stesura Rapporto";$l="SL";}

elseif($code == "kz1"){$p=20;$little="DISP";$mex = "Piccola Integrazione";$l="SM";}
elseif($code == "kz2"){$p=50;$little="DISP+";$mex = "Dispensa Completa";$l="SM";}
elseif($code == "aa11"){$p=$vali->numberOnly($_POST['points']);$little="Q00";$mex = 'Punteggio Admin';$l="SM";}

	$lisOuser="";
	foreach ($sel as $auth)
		if($auth!='') $lisOuser .= "'".trim(addslashes($auth))."',";
	
	$lisOuser = substr(trim($lisOuser),0,-1);
	
	if (PG::mapPermissions($l,$currentUser->pgAuthOMA))
	{
		
		foreach ($sel as $auth)
			if(trim($auth)!=''){	
						$tUser = trim(addslashes($auth));
						if($tUser != $currentUser->pgUser || $currentUser->pgAuthOMA != 'A')
						{
							$rea = mysql_fetch_array(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$tUser'"));
							$pgID = $vali->numberOnly($rea['pgID']);
							$selectedDUser = new PG($pgID);
							$selectedDUser->addPoints($p,$little,$mex,$detail,$currentUser->ID);
						}
			}
		
	}
	
	echo json_encode(array('stat'=>true)); exit;
	
	//echo "UPDATE pg_users SET pgLock=1 WHERE pgUser IN ($lisOuser)"; exit;
}

if($mode == "insertionForm")
{
	$template = new PHPTAL('TEMPLATES/shadow_scheda_master_insert.htm');
}

if($mode == "setUnlock")
{
	if (!PG::mapPermissions('G',$currentUser->pgAuthOMA)) exit;
	$vali=new validator();

	$apg = $vali->numberOnly($_GET['pgID']);
	mysql_query("UPDATE pg_users SET pgLock = 0 WHERE pgID = $apg");

	header("Location:multitool.php?viewLasts=true");
}

if ($mode == 'ajax_getactivityrecord'){

	$vali=new validator();
	if (isSet($_POST['pgID'])){
		$apg = new PG($vali->numberOnly($_POST['pgID']));
		echo json_encode($apg->getPlayRecord());
	}
	include('includes/app_declude.php');
	exit;
}

if ($mode == 'ajax_deleteChatLine'){


	if (!PG::mapPermissions('A',$currentUser->pgAuthOMA)) exit;

	$ml=mysql_fetch_assoc(mysql_query("SELECT IDE FROM federation_chat ORDER BY IDE DESC LIMIT 1"));
	$maxi = (int)($ml['IDE'])-2000;

	$vali=new validator();
	if (isSet($_POST['IDE'])){
		$ideToDelete = $vali->numberOnly($_POST['IDE']);
		mysql_query("DELETE FROM federation_chat WHERE IDE = $ideToDelete AND IDE >= $maxi");
		echo json_encode('ok');
	}
	include('includes/app_declude.php');
	exit;
}

if($mode == "diffpg")
{
	include('includes/Finediff.php');

	$rea=mysql_fetch_assoc(mysql_query("SELECT * FROM pg_users_bios WHERE pgID = 1 AND valid = 2"));
	$lines1 = (mysql_affected_rows()) ? $rea['pgBackground'] : '';
	$rea=mysql_fetch_assoc(mysql_query("SELECT * FROM pg_users_bios WHERE pgID = 1 AND valid < 2 ORDER BY recID DESC LIMIT 1"));
	$lines2 = (mysql_affected_rows()) ? $rea['pgBackground'] : '';

	echo $lines1;
	echo $lines2;


	echo "<hr />";
	
	echo FineDiff::getDiffOpcodes($lines1, $lines2 /* , default granularity is set to character */);


	exit;
}

else 
{	
	$template = new PHPTAL('TEMPLATES/multitool.html');

$template->prestigioLabels = $prestigioLabels;

	$oneMonthAgo=time()-2592000;
	$twoMonthAgo=time()-2592000-2592000-2592000;
	$resLocations = mysql_query("SELECT placeID,placeName FROM pg_places");

	
	$locArray=array();
	while($resLoc = mysql_fetch_array($resLocations))
	$locArray[$resLoc['placeID']] = $resLoc['placeName'];
	
	$ranks=array();
	$my = mysql_query("SELECT prio,Note,ordinaryUniform,aggregation FROM pg_ranks ORDER BY rankerprio DESC");
	while($myA = mysql_fetch_array($my))
	$ranks[$myA['aggregation']][$myA['prio']] = array('note' => $myA['Note'], 'ord' => $myA['ordinaryUniform']);
	//var_dump($ranks);exit;

	$images = scandir('TEMPLATES/img/ruolini/');
	$template->images=array_diff($images,array('.','..'));

	if (isSet($_GET['viewApprovals'])) $template->viewApprovals = 1;
	if (isSet($_GET['viewLasts'])) $template->viewLasts = 1;
  
	$template->ranks = $ranks;
	$template->locations = $locArray;


	$allServiceObjects = array();
	$re=mysql_query("SELECT oID,oName FROM fed_objects WHERE oType = 'SERVICE'");
	while($res = mysql_fetch_assoc(($re)))
		$allServiceObjects[] = $res;
	
	$template->allServiceObjects = $allServiceObjects;
	


	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->bonusSM = 'show';
	if (PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->bonusM = 'show';
	if (PG::mapPermissions('MM',$currentUser->pgAuthOMA)) $template->bonusMM = 'show';
	if (PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->bonusA = 'show';



	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{

		$r=array();

		$rea = mysql_query("SELECT pg_users_bios.*,pg_users.pgUser,pg_users.pgBavo,pg_users.pgLock,ordinaryUniform,iscriDate FROM pg_users_bios,pg_users,pg_ranks WHERE pg_users.pgID = pg_users_bios.pgID AND prio=rankCode AND pgLastAct > $oneMonthAgo AND pgRoom <> 'BAVO' AND pgBavo = 0 AND pgLock = 0 AND png=0 AND valid = 1 ORDER BY pgLastACT DESC");

		echo mysql_error();

		while($re = mysql_fetch_assoc(($rea)))
		{
			
			$curBG = PG::getSomething($re['pgID'],'BG'); 

			if($curBG){
				$re['approdiff'] = array(
					'pgBiometrics' => htmlDiff($curBG['pgBiometrics'],$re['pgBiometrics']),
					'pgBackground' => htmlDiff($curBG['pgBackground'],$re['pgBackground']),
					'pgCarattere' => htmlDiff($curBG['pgCarattere'],$re['pgCarattere']),
					'pgFamily' => htmlDiff($curBG['pgFamily'],$re['pgFamily']),
					'pgVarie' => htmlDiff($curBG['pgVarie'],$re['pgVarie']),
					'pgIlSegreto' => htmlDiff($curBG['pgIlSegreto'],$re['pgIlSegreto']));

				$c1 = 'REVISIONE';
			}
			else{
				$re['approdiff'] = array(
					'pgBiometrics' => htmlDiff("",$re['pgBiometrics']),
					'pgBackground' => htmlDiff("",$re['pgBackground']),
					'pgCarattere' => htmlDiff("",$re['pgCarattere']),
					'pgFamily' => htmlDiff("",$re['pgFamily']),
					'pgVarie' => htmlDiff("",$re['pgVarie']),
					'pgIlSegreto' => htmlDiff("",$re['pgIlSegreto']));

				if ($re['pgLock'] || $re['pgBavo'])
					$c1 = 'BLOCCATI_BANNATI_VERMI_BAVOSI';
				 elseif ($re['iscriDate'] > (time()-(604800*2)))
					$c1 = 'QUESTE_2_SETTIMANE';
				elseif ($re['iscriDate'] > (time()-(604800*4)))
					$c1 = 'QUESTO_MESE';

				else $c1 = 'MATUSALEMME';
			}


			$r[$c1][] = $re;
		}
	 
		$template->pendingBGS = $r;



	}

	if (PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
		$rea = mysql_query("SELECT federation_sessions.*,pg_users.pgUser,pg_users.pgID,locName FROM federation_sessions,pg_users,fed_ambient WHERE pgID = sessionOwner AND sessionPlace = locID ORDER BY sessionStatus, sessionID DESC LIMIT 150");
		$sessions=array();
		while($rel = mysql_fetch_assoc($rea)) $sessions[] = $rel;

		$template->sessions = $sessions;


		/**/
		$images = glob('TEMPLATES/img/maps/*.jpg');
		$shipIMG=mysql_query("SELECT placeMap1, placeMap2, placeMap3 FROM pg_places WHERE placeType IN ('Nave','Navetta','Stazione')");
		$excludeShips=array();
		while($res=mysql_fetch_assoc($shipIMG))
		{
			$excludeShips[] = $res['placeMap1'];
			$excludeShips[] = $res['placeMap2'];
			$excludeShips[] = $res['placeMap3'];
		}

		foreach($images as $kv=>$planetImage)
			if (in_array(basename($planetImage), $excludeShips))
				unset($images[$kv]);

		$template->availableMaps=$images;

		$images = glob('TEMPLATES/img/logo/*.{jpg,png}',GLOB_BRACE);
		$logos = array('assignLogos'=>array(), 'logos'=>array(),'inclogos' => array());
		
		foreach($images as $logoPath){
			$logoImage=basename($logoPath);
			if (strpos($logoImage,"assign_logo") === 0)
				$logos['assignLogos'][] = $logoImage;
			elseif (strpos($logoImage,"logo_") === 0)
				$logos['logos'][] = $logoImage;
			elseif (strpos($logoImage,"r_logo") === 0)
				$logos['inclogos'][] = $logoImage;
			}
		$template->logos=$logos;
	}

	if (PG::mapPermissions('A',$currentUser->pgAuthOMA))
	{
		$rea = mysql_query("SELECT federation_chat.*,pgUser,ordinaryUniform,pgID FROM federation_chat,pg_users,pg_ranks WHERE type <> '' AND rankCode = prio AND type <> 'SERVICE' AND pgID = sender ORDER BY IDE DESC LIMIT 150");
		$lastchats=array();
		while($rel = mysql_fetch_assoc($rea)) $lastchats[] = $rel;

		$template->lastchats = $lastchats;
	}

	$lastPGS =  mysql_query("SELECT pg_users.pgID,pgUser,ordinaryUniform,pgLock,(SELECT 1 FROM pg_alloggi WHERE pg_alloggi.pgID = pg_users.pgID LIMIT 1) as pgAlloggio,(SELECT 1 FROM pg_incarichi WHERE pg_incarichi.pgID = pg_users.pgID LIMIT 1) as pgIncarico, valid as pgBackground, supervision, lastReminder, (SELECT COUNT(*) FROM fed_pad WHERE (paddTo = pg_users.pgID OR paddFrom = pg_users.pgID) AND paddType = 3) as commpadd, (SELECT FROM_UNIXTIME(timeCode,'%d/%m/%Y %k:%i') FROM pg_notestaff WHERE pgTo = pg_users.pgID AND what LIKE 'AB-Reset:%' ORDER BY timeCode DESC LIMIT 1) as lastCarEdit FROM pg_users,pg_ranks,pg_users_bios WHERE pg_users_bios.pgID = pg_users.pgID AND rankCode = prio AND pgBavo =0 AND png=0 and pgAuthOMA <> 'BAN' AND ( (pgLastAct > $oneMonthAgo AND valid < 1) OR iscriDate > $oneMonthAgo) ORDER BY iscriDate DESC");

	 
	while($resa = mysql_fetch_assoc($lastPGS))
	{
		if ($resa['lastReminder'])
			$resa['supervision'] = PG::getSomething($resa['supervision'],'username');
		$resLastPGS[] = $resa;	
	}


	$template->resLastPGS = $resLastPGS;


	//STORYLINE
	$valedictsAssignee=array();
	$valedicts = mysql_query("SELECT dotazioneAlt,pgUser,ordinaryUniform,medName,medImage,pg_users.pgID as pgID,pgSesso,pgSpecie FROM pgDotazioni,pg_medals,pg_users,pg_ranks WHERE pg_users.pgID = 
pgDotazioni.pgID AND prio = rankCode AND medID = dotazioneIcon AND medID IN (27,72,75,76) AND doatazioneType = 'MEDAL' ORDER BY dotazioneAlt");
	while($res = mysql_fetch_assoc($valedicts))
		{
			if (!array_key_exists($res['medName'], $valedictsAssignee))
				$valedictsAssignee[$res['medName']] = array();
			$valedictsAssignee[$res['medName']][] = $res;
		}

	$template->valedictsAssignee = $valedictsAssignee;


	$presentiTW=array();
	$presentimeta=array();
	$presePeople=mysql_query("SELECT pg_users_presence.pgID,pgUser,ordinaryUniform,pgAuthOMA,value,day FROM pg_users,pg_users_presence,pg_ranks WHERE rankCode = prio AND pg_users.pgID = pg_users_presence.pgID AND pgAuthOMA IN ('G','M','SM','A') ORDER BY pgAuthOMA");
	
	while($person = mysql_fetch_assoc($presePeople))
	{
		if(!array_key_exists($person['pgID'], $presentiTW))
			{
				$presentiTW[$person['pgID']] = array();
				foreach(range(0,6,1) as $p)
					$presentiTW[$person['pgID']][$p] = 0;
			}


			$presentiTW[$person['pgID']][$person['day']] = $person['value'];
			$presentimeta[$person['pgID']] = $person; 
	}
 

$cand= date('w',$curTime) -1;
$candT= ($cand != -1) ? $cand : 6;

$DDAYMAP = array(0=>'LUN', 1=>'MAR', 2=>'MER', 3=>'GIO', 4=>'VEN', 5=>'SAB', 6=>'DOM');


$DDAYMAP[$candT] .= ' '.date('d');

if($candT < 6)
{
	foreach(range($candT+1,6,1) as $p)
	{
		$DDAYMAP[$p] .= ' '.date('d',$curTime+(24*60*60* ($p-$candT) ));
	}	
}

if($candT >0)
{
	foreach(range($candT-1,0,-1) as $p)
	{	 
		$DDAYMAP[$p] .= ' '.date('d',$curTime-(24*60*60* ($candT-$p) ));
	}

}



$res = mysql_query("SELECT medID,medName FROM pg_medals WHERE 1 ORDER BY medPrio ASC");
	$nasArray=array();
	while($resD = mysql_fetch_array($res))
		$nasArray[] = $resD;


$template->thisYear = $thisYear+$bounceYear;
$template->nastrini = $nasArray;
$template->DDAYMAP=$DDAYMAP;
$template->gameOptions = $gameOptions;
$template->presentiTW = $presentiTW;
$template->presentimeta = $presentimeta;
$template->me=$currentUser->ID;
$template->meRole=$currentUser->pgAuthOMA;
if($mode == 'mypresence') 
	$template->presenzaStaff = true;

}
  
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
include('includes/app_declude.php');	



function diff($old, $new){
    $matrix = array();
    $maxlen = 0;
    foreach($old as $oindex => $ovalue){
        $nkeys = array_keys($new, $ovalue);
        foreach($nkeys as $nindex){
            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
            if($matrix[$oindex][$nindex] > $maxlen){
                $maxlen = $matrix[$oindex][$nindex];
                $omax = $oindex + 1 - $maxlen;
                $nmax = $nindex + 1 - $maxlen;
            }
        }   
    }
    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    return array_merge(
        diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
        array_slice($new, $nmax, $maxlen),
        diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}
function htmlDiff($old, $new){
    $ret = '';
    $diff = diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
    foreach($diff as $k){
        if(is_array($k))
            $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
                (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
        else $ret .= $k . ' ';
    }
    return $ret;
}
?>
