<?php
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
PG::updatePresence($_SESSION['pgID']);

$currentUser = new PG($_SESSION['pgID']);

$vali = new validator();

if(isSet($_GET['setOFF']))
{
	$id = $vali->numberOnly($_GET['setOFF']);
	if(PG::mapPermissions('O',$currentUser->pgAuthOMA))
	mysql_query("UPDATE pg_imbarchi SET pgOFF = 1 WHERE pgID = $id AND pgOFF = 0");
	
	if(PG::mapPermissions('M',$currentUser->pgAuthOMA))
	mysql_query("UPDATE pg_users SET pgLock = 0 WHERE pgID = $id");
	header('Location:people.php');
}
if(isSet($_GET['setON']))
{
	$id = $vali->numberOnly($_GET['setON']);
	if(PG::mapPermissions('O',$currentUser->pgAuthOMA))
	mysql_query("UPDATE pg_imbarchi SET pgON = 1 WHERE pgID = $id AND pgON = 0 AND pgOFF = 1");
	header('Location:people.php');
}
if(isSet($_GET['setVisita']))
{
	$id = $vali->numberOnly($_GET['setVisita']);
	if(PG::mapPermissions('O',$currentUser->pgAuthOMA))
	mysql_query("UPDATE pg_imbarchi SET pgVisitaMedica = 1 WHERE pgID = $id AND pgVisitaMedica = 0");
	header('Location:people.php');
}

if(isSet($_GET['adminCheck']))
{
	$id = $vali->numberOnly($_GET['adminCheck']);
	$toName = PG::getSomething($id,'username');
	$fromName = PG::getSomething($_SESSION['pgID'],'username');
	if(PG::mapPermissions('SM',$currentUser->pgAuthOMA))
	{
	mysql_query("UPDATE pg_imbarchi SET bcheck= 1 WHERE pgID = $id AND bcheck= 0");
	mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (".$_SESSION['pgID'].", $id, 'Background Check', 'Ciao $toName!\n\n Ti comunico che ho visionato i dati relativi alla registrazione del PG, ed il Background. Tutto risulta in ordine ed il BG e\' ora approvato! Buon gioco!!\n\n - $fromName',".time().",0)");
	}
	header('Location:people.php');
}


		$template = new PHPTAL('TEMPLATES/cdb_people.htm');
		//$equi = $vali->killchars($_GET['equi']);
		

		
//		$personale = array('CIV' => array(),'MIL' =>array());
		$crew = mysql_query("SELECT pg_users.pgID,(SELECT 1 FROM pg_alloggi WHERE pg_alloggi.pgID = pg_users.pgID LIMIT 1) as pgAlloggio, pgSpecie,rankCode,pgAssign,placeName, pgSesso, pgUser, pgGrado, pgSezione,pgLock,  ordinaryUniform as pgMostrina,pgOFF,pgON,pgTrekNote,bcheck,pgVisitaMedica,dateInsert, pgMedica FROM pg_imbarchi,pg_users,pg_ranks,pg_places WHERE pgLock=0 AND pg_users.pgID = pg_imbarchi.pgID AND pgAssign <> 'BAVO' AND pgAssign = placeID AND prio = rankCode AND toAppear = 1 AND pgLastAct <> 0 AND png = 0 ORDER BY bcheck ASC, dateInsert DESC LIMIT 35");
		while ($crewA = mysql_fetch_array($crew))
		{
		$personale[$crewA['placeName']][] = $crewA;
		}
		
		$template->personale = $personale;


$template->user = $currentUser;
if(PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->SM = 'ys';
$template->OM = (PG::mapPermissions('O',$currentUser->pgAuthOMA)) ? 'yes' : 'no'; 
 $template->currentDate = $currentDate;
 $template->currentStarDate = $currentStarDate;
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
	
include('includes/app_declude.php');

?>