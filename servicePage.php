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
	 $re = mysql_fetch_array(mysql_query("SELECT uniform,pgSesso,descript FROM pg_uniforms,pg_users WHERE pgID = ".$_SESSION['pgID']." AND pgMostrina = mostrina"));
	 
	 $template->uniform = $re['uniform'].strtolower($re['pgSesso']); 
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
	else if ($emo == "DES") $sqlt ="desertUniform";
	else if ($emo == "FAT") $sqlt ="faticaUniform";
	
	if(isSet($sqlt)){
		$rpl = mysql_fetch_assoc(mysql_query("SELECT $sqlt FROM pg_ranks WHERE prio = '$parameter'"));
		$query = $rpl[$sqlt];
	} 
	
	elseif ($emo == "EVA") $query ="EVA";
	else if ($emo == "POL") $query ="POL";
	else if ($emo == "NBC") $query ="NBC";
	else if ($emo == "VOL") $query ="VOL";
	else if ($emo == "CAM") $query ="CAM";
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
	else if ($emo == "POL") $query ="UPDATE pg_users SET pgMostrina = 'POL' WHERE pgID = $id";
	else if ($emo == "FAT") $query ="UPDATE pg_users SET pgMostrina = (SELECT faticaUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "EVA") $query ="UPDATE pg_users SET pgMostrina = 'EVA' WHERE pgID = $id";
	else if ($emo == "NBC") $query ="UPDATE pg_users SET pgMostrina = 'NBC' WHERE pgID = $id";
	else if ($emo == "VOL") $query ="UPDATE pg_users SET pgMostrina = 'VOL' WHERE pgID = $id";
	else if ($emo == "CAM") $query ="UPDATE pg_users SET pgMostrina = 'CAM' WHERE pgID = $id";
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