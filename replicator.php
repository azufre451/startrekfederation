<?php
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");

$currentUser = new PG($_SESSION['pgID']);
$availableUCs = array('&#129347;','&#129367;','&#127836;','&#127863;','&#129384;','&#127831;','&#127827;','&#128031;','&#127856;','&#127863;','&#127861;','&#129371;','&#9749;','&#127831;','&#127864;','&#129472;','&#127861;','&#127837;');

if(isSet($_GET['term']) && isSet($_GET['lookSpecie']))
{ 
		$call = addslashes($_GET['term']);
		$lookSpecie = addslashes($_GET['lookSpecie']);
		$res = mysql_query("SELECT DISTINCT foodSpecie FROM fed_food WHERE active = 1 AND foodSpecie LIKE '%$call%'"); 
		$arec = array(); 
		while($reA = mysql_fetch_assoc($res)) $arec[] = $reA['foodSpecie'];
		echo json_encode($arec);
}

elseif(isSet($_GET['term']))
{ 
		$call = addslashes($_GET['term']);
		$res = mysql_query("SELECT foodName FROM fed_food WHERE active = 1 AND foodName LIKE '%$call%' OR foodSpecie LIKE '%$call%'"); 
		$arec = array();
		while($reA = mysql_fetch_assoc($res)) $arec[] = $reA['foodName'];
		echo json_encode($arec);
}

elseif(isSet($_GET['propose']))
{ 
		$prop_foodTitle = addslashes($_POST['prop_foodTitle']);
		$prop_foodDescript = addslashes($_POST['prop_foodDescript']);
		$prop_foodSpecie = addslashes($_POST['prop_foodSpecie']);
		$prop_foodImage = addslashes($_POST['prop_foodImage']);
		$prop_foodType = addslashes($_POST['prop_foodType']);

		$prop_foodIcon = addslashes($_POST['prop_foodUC']);
		$usr = $_SESSION['pgID'];
	
		mysql_query("INSERT INTO fed_food (foodName,foodImage,foodDescription,foodSpecie,active,presenter,foodType,iconUC) VALUES ('$prop_foodTitle','$prop_foodImage','$prop_foodDescript','$prop_foodSpecie',0,$usr,'$prop_foodType','$prop_foodIcon')");
		$p1 = new PG(1);
		$p2 = new PG(5);
		$toUser = $currentUser->pgUser;
		
		$cString = addslashes("L'utente $toUser a appena inserito in replicatore un cibo degli $prop_foodSpecie:<br /><br /><p style='text-align:center'><span style='font-weight:bold'>$prop_foodTitle</span><br /><img src='$prop_foodImage' style='border:1px solid #AAA; padding:5px; width:150px;' /> Accedi al tool admin replicatore per approvare o modificare.</p><br /> $prop_foodDescript");
		 
		$p1->sendNotification("Nuovo cibo Replicatore","$toUser ha appena inserito in replicatore un cibo degli $prop_foodSpecie",$_SESSION['pgID'],$prop_foodImage,'repliOpen');
		$p2->sendNotification("Nuovo cibo Replicatore","$toUser ha appena inserito in replicatore un cibo degli $prop_foodSpecie",$_SESSION['pgID'],$prop_foodImage,'repliOpen');
	 
		header("location:replicator.php?success=true&loc=".$_POST['loc']); 
}

elseif(isSet($_GET['ajax_checkInage']))
{
	$siz = getimagesize($_POST['imaUrl']);
	if ($siz[0] == '390' || $siz[1] == '230') echo json_encode(array('OK' => true));
	else echo json_encode(array());
	exit;
	
}

elseif(isSet($_GET['ajaxCall']))
{ 
		$call = $_GET['ajaxCall'];
		if (is_numeric($call)) $qu = "SELECT foodID,foodName, foodImage, foodDescription,iconUC FROM fed_food WHERE active = 1 AND foodID = $call";
		else $qu = "SELECT foodID,foodName, foodImage, foodDescription,iconUC FROM fed_food WHERE active = 1 AND foodName = '".addslashes($call)."'";
		
		$res = mysql_query($qu);
		$reA = mysql_fetch_assoc($res);
		echo json_encode($reA);
}

elseif(isSet($_POST['fCall1']))
{
		$call1 = $_POST['fCall1']; //cibi-bevande: (menuAll1 menuCibi menuBevande)
		$call2 = $_POST['fCall2']; // liste: (menuAll2 menuLasts menuTop)
		$call3 = $_POST['fCall3']; // order: (menuaz menuspecie)
		
		$wAdd = '';
		$jAdd = '';
		$oAdd = "ORDER BY foodName";
		$gAdd = '';
		$qAdd = '';
		$dClause = '';
		if($call1 == 'menuCibi') $wAdd .= "AND foodType='A'";
		elseif($call1 == 'menuBevande') $wAdd .= "AND foodType='B'";
		
		if($call2 == 'menuLasts'){
			$jAdd.= "JOIN fed_food_replications ON foodID = food";
			$wAdd .= "AND user = ".$_SESSION['pgID']." ";
			$oAdd = "ORDER BY timer DESC LIMIT 10";
			$dClause = 'DISTINCT';
		}
		elseif($call2 == 'menuTop'){
			$jAdd.= "JOIN fed_food_replications ON foodID = food";
			$gAdd = "GROUP BY foodID,foodName, foodSpecie, foodType,iconUC";
			$oAdd = "ORDER BY LCO DESC LIMIT 10";
			$qAdd = ', COUNT(*) as LCO';
		}
		//elseif($call2 == 'menuTop') $jAdd.= "AND foodID IN (SELECT food FROM fed_food_replications WHERE 1 GROUP BY food ORDER BY COUNT(*) DESC LIMIT 10)";
		
		$foods = array();
		$res = mysql_query("SELECT $dClause foodID,foodName, foodSpecie, foodType,iconUC $qAdd FROM fed_food $jAdd WHERE active = 1 $wAdd $gAdd $oAdd");
		//echo "SELECT $dClause foodID,foodName, foodSpecie, foodType FROM fed_food $jAdd WHERE active = 1 $wAdd $gAdd $oAdd";exit;
		while ($reA = mysql_fetch_assoc($res))
		
		{
			if (!isSet($foods[$reA['foodSpecie']])) $foods[$reA['foodSpecie']] = array();
			$foods[$reA['foodSpecie']][] = $reA;
		}
		ksort($foods);
		echo json_encode($foods);
}
elseif(isSet($_GET['admin']))
{
	if(!PG::mapPermissions("SM",$currentUser->pgAuthOMA)) exit;
	
	$template = new PHPTAL('TEMPLATES/replicator_admin.htm');
	$res = mysql_query("SELECT foodID,foodName, foodSpecie,foodDescription, foodType,iconUC, foodImage, presenter, pgUser FROM fed_food,pg_users WHERE pgID = presenter AND active = 0");
	$food=array();
	while($rea = mysql_fetch_array($res))
	{	
		$sizS='';
		if($rea['foodImage'] != '')
		{	
			$siz = getimagesize($rea['foodImage']);
			$sizS = $siz[0].'x'.$siz[1];
		}
		
		$food[]=array('foodID' => $rea['foodID'],'foodName' => $rea['foodName'],'foodSpecie' => $rea['foodSpecie'],'foodDescription' => $rea['foodDescription'],'foodType' => $rea['foodType'],'foodImage' => $rea['foodImage'],'pgUser' => $rea['pgUser'],'foodImaSize'=>$sizS, 'iconUC' => $rea['iconUC']);
	}
	$template->food =$food;
	try
	{
		echo $template->execute();
	}	catch (Exception $e){echo $e;}
}

elseif(isSet($_GET['approval']))
{ 
	$validate = new validator();
	if(!PG::mapPermissions("SM",$currentUser->pgAuthOMA)) exit;
	$mode = $_GET['s'];
	$foodID = $validate->numberOnly($_GET['foodID']);
	$getRecord = mysql_fetch_assoc(mysql_query("SELECT * FROM fed_food WHERE foodID = $foodID"));
	
	if($mode == 'approveFully')
	{
		if(!strpos(trim($getRecord['foodImage']), 'oscar.stfederation.it/imaReplicatore'))
		{
			$fileName = substr(time(),4,4).basename($getRecord['foodImage']);
			copy(trim($getRecord['foodImage']), "oscar/imaReplicatore/$fileName");
			$imaUrl = "https://oscar.stfederation.it/imaReplicatore/$fileName";
			mysql_query("UPDATE fed_food SET active = 1, foodImage = '$imaUrl' WHERE foodID = $foodID");
			if(mysql_affected_rows() && strpos(trim($getRecord['foodImage']), 'oscar.stfederation.it/SigmaSys/'))
			{
				$handle = fopen("LOG_to_delete_report.txt", "a");
				fwrite($handle,date("d-m-Y H:i:s",time()).' '."Caricamento file ".$getRecord['foodImage']." in Sigma. Cancellare!\r\n");
				fclose($handle);
			}
		}
		else {
			mysql_query("UPDATE fed_food SET active = 1 WHERE foodID = $foodID");
			$imaUrl=$getRecord['foodImage'];
		}
		

		$to = $getRecord['presenter'];
		$title=$getRecord['foodName'];
		$curUser = $currentUser->pgUser; 
		$approvedUser = new PG($to);
		$approvedUser->addPoints(2,'FOOD','Proposta cibo Replicatore','Proposta cibo Replicatore',$assigner=518);
		$approvedUser->sendNotification("Proposta replicatore approvata","$curUser ha approvato la tua proposta per $title . Grazie!",$_SESSION['pgID'],$imaUrl,'repliOpen');

		
		header('location:replicator.php?admin=true'); exit;
	}
	
	if($mode == 'delete')
	{	
		$imago=$getRecord['foodImage'];
		$namao=$getRecord['foodName'];
		$reason=$_POST['reason'];
		$descriptio=$getRecord['foodDescription'];

		$to = $getRecord['presenter'];
		
		$curUser = $currentUser->pgUser; 
		$approvedUser = new PG($to);
		
		$approvedUser->sendPadd("Proposta replicatore respinta","$curUser ha rigettato la tua proposta per $namao: <hr /> <p style=\"text-align:center\"><img src=\"$imago\" style=\"border:1px solid #FFCC00; max-width:250px;\"></img></p> <table><tr><td style=\"font-weight:bold;\">URL:</td><td style=\"font-weight:bold;\">$imago</td></tr><tr> <td style=\"font-weight:bold;\">Descrizione</td><td>$descriptio</td> </tr> <tr><td style=\"font-weight:bold;\">Ragione</td><td>$reason</td></tr></table>",$_SESSION['pgID'],$currentUser->pgAvatarSquare);
		
		mysql_query("DELETE FROM fed_food WHERE foodID = $foodID"); 
		echo json_encode(array('OK'));

	}
 
	if($mode == 'revise')
	{
		$template = new PHPTAL('TEMPLATES/replicator_admin_edit.htm');
		$template->availableUCs = $availableUCs;
		$template->getRecord = $getRecord;
		
		try{echo $template->execute();}	catch (Exception $e){echo $e;}
	}
	
	if($mode == 'confirmRevise')
	{ 
		$prop_foodTitle = addslashes($_POST['editName']);
		$prop_foodDescript = addslashes($_POST['editDescr']);
		$prop_foodSpecie = addslashes($_POST['editSpecie']);
		$prop_foodImage = addslashes($_POST['editImage']);
		$prop_foodType = addslashes($_POST['editTipo']);
		$prop_foodIcon = addslashes($_POST['prop_foodUC']);

		
		mysql_query("UPDATE fed_food SET foodName='$prop_foodTitle',foodImage='$prop_foodImage',foodDescription='$prop_foodDescript',foodSpecie='$prop_foodSpecie',foodType='$prop_foodType',iconUC='$prop_foodIcon' WHERE foodID = $foodID");
 
		header("location:replicator.php?admin=true");
	}
	
}
else
{
$template = new PHPTAL('TEMPLATES/replicator.htm');
$foods = array();



$res = mysql_query("SELECT foodID,foodName, foodSpecie, foodType,iconUC FROM fed_food WHERE active = 1 ORDER BY foodName");
while ($reA = mysql_fetch_assoc($res))
{
	if (!isSet($foods[$reA['foodSpecie']])) $foods[$reA['foodSpecie']] = array();
	$foods[$reA['foodSpecie']][] = $reA;
}
 
ksort($foods); 

$template->foods = $foods;
$template->availableUCs = $availableUCs;

if (isSet($_GET['success'])) $template->success = true;
if(PG::mapPermissions("SM",$currentUser->pgAuthOMA)) $template->isAdmin = true;
if (isSet($_GET['loc'])) $template->placeLoc = $_GET['loc'];
if (isSet($_POST['loc'])) $template->placeLoc = $_POST['loc'];
if (isSet($_GET['foodID'])) $template->customFood = $_GET['foodID'];
 

	try
	{
		echo $template->execute();
	}	catch (Exception $e){echo $e;}
}	

include('includes/app_declude.php');
	?>