<?php
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php");

PG::updatePresence($_SESSION['pgID']);
$currentUser = new PG($_SESSION['pgID']);
if ($currentUser->pgAuthOMA == 'BAN'){header("Location:http://images1.wikia.nocookie.net/__cb20111112213451/naruto/images/f/f0/Sasuke.jpeg"); exit;}
$vali = new validator();

if(isSet($_GET['wordrobe']))
{
	 $template = new PHPTAL('TEMPLATES/service_wordrobe.htm');
	 $re = mysql_fetch_array(mysql_query("SELECT uniform,descript FROM pg_uniforms,pg_users WHERE pgID = ".$_SESSION['pgID']." AND pgMostrina = mostrina"));
	 
	 $template->uniform = $re['uniform']; 
	 $template->descript = $re['descript'];
	 $template->user = $currentUser;
} 

elseif(isSet($_GET['prevMos']))
{
	$emo = $_POST['emoSel'];
	$id = $_SESSION['pgID'];
	
	$myA = mysql_fetch_assoc(mysql_query("SELECT rankCode FROM pg_users WHERE pgID = $id"));
	$parameter = $myA['rankCode'];
	
	if ($emo == "SER") 		$sqlt ="ordinaryUniform";
	else if ($emo == "HIG") $sqlt ="dressUniform";
	else if ($emo == "TAT") $sqlt ="tacticalUniform";
	else if ($emo == "COA") $sqlt ="cappottoUniform";
	else if ($emo == "JKT") $sqlt ="ordinaryUniformNoJackect";
	else if ($emo == "DES") $sqlt ="desertUniform";
	else if ($emo == "POL") $sqlt ="polarUniform";
	else if ($emo == "LAB") $sqlt ="camiceUniform";
	
	if(isSet($sqlt)){
		$rpl = mysql_fetch_assoc(mysql_query("SELECT $sqlt FROM pg_ranks WHERE prio = '$parameter'"));
		$query = $rpl[$sqlt];
	} 
	
	else if ($emo == "EVA") $query ="nfEVA";
	else if ($emo == "FLY") $query ="nfFLY";
	else if ($emo == "MED") $query ="nfMED";
	else if ($emo == "ENG") $query ="nfENG";
	else if ($emo == "CIV") $query ="CIV"; 
	 
	
	$myA = mysql_fetch_array(mysql_query("SELECT uniform,descript FROM pg_uniforms WHERE mostrina = '$query'")); 
	
	$uniform = $myA['uniform'];
	$descript = $myA['descript'];
	$mostrina = $query;
	$pgSesso = strtolower($currentUser->pgSesso);
	
	$aar = array(); 
	$aar['DLT'] = $mostrina;
	$aar['DLU'] = $uniform;
	$aar['DLS'] = $pgSesso;
	$aar['TTT'] = $descript;
	echo json_encode($aar); 
	exit;
}

elseif(isSet($_GET['setMos']))
{
	$emo = $_POST['emoSel'];
	$id = $_SESSION['pgID'];
	
	$myA = mysql_fetch_assoc(mysql_query("SELECT rankCode FROM pg_users WHERE pgID = $id"));
	$parameter = $myA['rankCode'];
	
	if ($emo == "SER") $query ="UPDATE pg_users SET pgMostrina = (SELECT ordinaryUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "HIG") $query ="UPDATE pg_users SET pgMostrina = (SELECT dressUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "TAT") $query ="UPDATE pg_users SET pgMostrina = (SELECT tacticalUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "DES") $query ="UPDATE pg_users SET pgMostrina = (SELECT desertUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "POL") $query ="UPDATE pg_users SET pgMostrina = (SELECT polarUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "COA") $query ="UPDATE pg_users SET pgMostrina = (SELECT cappottoUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "LAB") $query ="UPDATE pg_users SET pgMostrina = (SELECT camiceUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "JKT") $query ="UPDATE pg_users SET pgMostrina = (SELECT ordinaryUniformNoJackect FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	
	else if ($emo == "EVA") $query ="UPDATE pg_users SET pgMostrina = 'nfEVA' WHERE pgID = $id";
	else if ($emo == "FLY") $query ="UPDATE pg_users SET pgMostrina = 'nfFLY' WHERE pgID = $id";
	else if ($emo == "ENG") $query ="UPDATE pg_users SET pgMostrina = 'nfENG' WHERE pgID = $id";
	else if ($emo == "MED") $query ="UPDATE pg_users SET pgMostrina = 'nfMED' WHERE pgID = $id";
	else $query ="UPDATE pg_users SET pgMostrina = 'CIV' WHERE pgID = $id";
	
	mysql_query($query);
	 
	
	$myA = mysql_fetch_array(mysql_query("SELECT pgMostrina FROM pg_uniforms,pg_users WHERE pgID = ".$_SESSION['pgID']." AND pgMostrina = mostrina"));
	$mostrina = $myA['pgMostrina'];
	
	$aar = array(); 
	$aar['DLT'] = $mostrina;
	echo json_encode($aar); 
	exit;
}

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

?>