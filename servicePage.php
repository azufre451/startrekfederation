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

elseif(isSet($_GET['getMyObj']))
{
	$objs=array('SERVICE'=>array(),'PERSONAL'=>array());
	$rel=mysql_query("SELECT fed_objects.oID,oName,oType FROM fed_objects,fed_objects_ownership WHERE fed_objects.oID=fed_objects_ownership.oID AND fed_objects.oID NOT IN (SELECT ref FROM pg_current_dotazione WHERE owner = ".$_SESSION['pgID'].") AND owner = ".$_SESSION['pgID']);
	echo mysql_error();
	while($ral = mysql_fetch_assoc($rel))
	{
		$objs[$ral['oType']][]=$ral;
	}

	echo json_encode($objs);
	exit;
}
elseif(isSet($_GET['remDot']))
{
	$iRem = $vali->numberOnly($_POST['iRem']); 
	$oID = $vali->numberOnly($_POST['oID']); 

	mysql_query("DELETE FROM pg_current_dotazione WHERE owner = ".$_SESSION['pgID']." AND ref = $oID AND recID = $iRem");
	if(mysql_affected_rows())
	{
		$rt=mysql_fetch_assoc(mysql_query("SELECT oID,oName,oType FROM fed_objects WHERE oID = $oID"));

		echo json_encode(array('STA' => 'OK','RT'=>$rt)); 
	}
	exit;
}

elseif(isSet($_GET['addDot']))
{
	$iAdd = $vali->numberOnly($_POST['iAdd']);
	mysql_query("SELECT 1 FROM fed_objects_ownership WHERE oID = $iAdd AND owner = ".$_SESSION['pgID']);
	if(mysql_affected_rows())
	{
		mysql_query("INSERT INTO pg_current_dotazione (owner,type,ref) VALUES (".$_SESSION['pgID'].",'OBJECT',$iAdd)");
		if(mysql_affected_rows())
		{
			$rt=mysql_fetch_assoc(mysql_query("SELECT recID,oID, oName, oImage,oLittleImage FROM fed_objects,pg_current_dotazione WHERE oID = ref AND owner = ".$_SESSION['pgID']." AND  fed_objects.oID = $iAdd"));
			$rt['image'] = ($rt['oLittleImage'] != '') ? $rt['oLittleImage'] : $rt['oImage'];
			echo json_encode(array('STA' => 'OK','RT'=>$rt)); 
		}
	}
	
	exit;
}

elseif(isSet($_GET['getDot']))
{

	if(isSet($_GET['me'])){
		$id = $_SESSION['pgID'];
	}
	else{
		$id = $_POST['pgID'];
		$currentUser = new PG ($id);
	}


	$currentUni = mysql_fetch_assoc(mysql_query("SELECT uniform,descript,isDress,pgPrestige FROM pg_uniforms,pg_users WHERE pgID = $id AND pgMostrina = mostrina"));
	 
	$currentUniform = $currentUni['uniform']; 
	$currentDescript = $currentUni['descript'];


	$aar = array(
		'DATA' => array(
			'ABITI' => array(),
			'OBJECT' => array(),
			'MEDAL' => array(),
		),
		'currentUniform' => $currentUniform,
		'currentDescript' => $currentDescript,
		'pgMostrina' => $currentUser->pgMostrina,
		'pgPrestigio' => $currentUni['pgPrestige'],

		'pgGrado' => $currentUser->pgGrado,
		'pgSesso' => $currentUser->pgSesso,
		'pgSpecie' => $currentUser->pgSpecie,
		'pgSezione' => $currentUser->pgSezione
	); 
	$ral=mysql_query("SELECT * FROM pg_current_dotazione LEFT JOIN fed_objects ON ref = oID WHERE owner = $id");
	while($rel = mysql_fetch_assoc($ral)){
		if ($rel['type'] == 'OBJECT') $rel['image'] = ($rel['oLittleImage'] != '') ? $rel['oLittleImage'] : $rel['oImage'];
		$rel['oName'] = (strlen($rel['oName']) > 40 ) ? substr($rel['oName'],0,37).'...' : $rel['oName'];
		$aar['DATA'][$rel['type']][] = $rel;
	}
 
	if($currentUni['isDress'])
	{
		$ral=mysql_query("SELECT medImage FROM pg_medals,pgDotazioni WHERE dotazioneIcon = medID AND pgID = $id ORDER BY medPrio ASC");
		while($rel = mysql_fetch_assoc($ral))
			$aar['DATA']['MEDAL'][] = $rel;
	
	}

	echo json_encode($aar); 
	exit;

}


elseif(isSet($_GET['setMos']))
{
	$emo = $_POST['emoSel'];
	$civDetail = addslashes($_POST['civDetail']);
	$civimage = addslashes($_POST['civimage']);


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
	else{ 
		$query ="UPDATE pg_users SET pgMostrina = 'CIV' WHERE pgID = $id";
		
		if ($civDetail != '' || $civimage != ''){
			mysql_query("DELETE FROM pg_current_dotazione WHERE type = 'ABITI' AND owner = $id");
			mysql_query("INSERT INTO pg_current_dotazione (owner,type,descr,image) VALUES($id,'ABITI','$civDetail','$civimage')");
			
			echo mysql_error();	
		}

	}
	
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