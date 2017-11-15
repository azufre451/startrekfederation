<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
$vali = new validator();

PG::updatePresence($_SESSION['pgID']);
$currentUser = new PG($_SESSION['pgID']); 
if ($currentUser->pgAuthOMA == 'BAN'){header("Location:404"); exit;}

$a = new abilDescriptor($currentUser->ID);

if ($_GET['action'] == 'getAbil'){

	$focus = $_POST['abID'];

	$out = array('AB'=>$a->abilDict[$focus],'DEP'=>$a->explainDependencies($focus),'STAT'=>$a->explaindice($focus));
	echo json_encode($out);
}


if ($_GET['action'] == 'rollDeliver'){



	$amb = addslashes($_POST['amb']);

	$t = json_decode($_POST['lister']);

	if (count($t) > 0){

	$otp='';
	foreach ($t as $esiter){

		$recID = $vali->numberOnly($esiter[0]);
		$modi = (is_numeric($esiter[1]) ? $esiter[1] : 0 );
		if( $modi > 6 || $modi < -6){exit;}
		$rea=mysql_fetch_assoc(mysql_query("SELECT sender,dicerOutcome,dicerAbil FROM federation_chat WHERE IDE = $recID" ));

		$b=new abilDescriptor($rea['sender']);

		$outcome = $b->reRollDice($rea['dicerAbil'],$rea['dicerOutcome'],$modi);
		$launched_abil = $b->abilDict[$rea['dicerAbil']];

		$otp .= '<div class="'.$outcome['outcome'].'"><p class="bar"></p>'.PG::getSomething($rea['sender'],'username').': <img src="TEMPLATES/img/interface/personnelInterface/abilita/'.$launched_abil['abImage'].'" title="'.$launched_abil['abName'].'"><br />Soglia: '.$outcome['threshold'].' <span class="bmal">'.$modi.'</span> <br />Dado: '.$outcome['v'].' <p class="label"></p></div>';
		
		mysql_query("UPDATE federation_chat SET dicerAbil = '' WHERE IDE = $recID");

		unset($b);

	}

	$tqr='<div style="position:relative;" class="masterAction">

	<div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Esito del lancio di un dado" /> Dado Abilità</div>
	
	<div class="diceOutcomeBox">'.$otp.'	

	</div>
	<div stlye="clear:both"></div>
	</div>'; 

	mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$tqr',".time().",'MASTER',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");


	}
	echo json_encode("OK");
}

if ($_GET['action'] == 'rollRecompute'){

	$focus = addslashes($_POST['abID']);
	$valor = $vali->numberOnly($_POST['valor']);
	$pgID = $vali->numberOnly($_POST['pgID']);

	$b = new abilDescriptor($pgID);
	
	$mod = (is_numeric($_POST['mod']) ? $_POST['mod'] : 0 );
	
	if( $mod > 6 || $mod < -6){exit;}

	$outcome = $b->reRollDice($focus,$valor,$mod);

	$locale = array('F' => 'Fallimento','FC' => 'Fallimento Critico', 'S' => 'Successo', 'SC' => 'Successo Critico');
	$outcome['outcome'] = $locale[$outcome['outcome']];
	echo json_encode($outcome);
} 



if ($_GET['action'] == 'roll'){

	$focus = addslashes($_POST['abID']);
	$amb = addslashes($_POST['amb']);
	$locale = array('F' => 'Fallimento','FC' => 'Fallimento Critico', 'S' => 'Successo', 'SC' => 'Successo Critico');
	$rnd = rand(1,20);
	$outcome = $a->rollDice($focus);

	$sessionOngoing = Ambient::getActiveSession($amb);

	if($sessionOngoing && $sessionOngoing['session']['sessionMaster'])
	{
		$userSpecific = $sessionOngoing['session']['openerID']; 

		$string = '<div style="position:relative;" class="specificMasterAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Esito del lancio di un dado" /> Dado Abilità</div>'.addslashes($currentUser->pgUser).' lancia un dado su '.$a->abilDict[$focus]['abName'].' | Esito: '.$outcome['v'] .'/20, soglia: '.$outcome['threshold'].'</div>'; 
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,specReceiver,privateAction,dicerOutcome,dicerAbil) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'DICERSPEC',$userSpecific,IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0),'".$outcome['v']."','".$a->abilDict[$focus]['abID']."')"); 
	}
	else{
		$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Esito del lancio di un dado" /> Dado Abilità</div> <img style="width:30px; vertical-align:middle;" src="TEMPLATES/img/interface/personnelInterface/abilita/'.$a->abilDict[$focus]['abImage'].'" title="'.$a->abilDict[$focus]['abName'].'" />  '.addslashes($currentUser->pgUser).' lancia un dado su '.$a->abilDict[$focus]['abName'].' | Esito: '.$locale[$outcome['outcome']].' ('.$outcome['v'] .'/20, soglia: '.$outcome['threshold'].') </div>'; 
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type,privateAction) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL',IF((SELECT chatPwd FROM fed_ambient WHERE locID = '$amb' AND chatPwd > 0) > 0,1,0))");
 	}
	
}

/*
print "<br />Umani (+53)<br />";

print $a->calculateVariationCost(array(
	array(38,0),
	array(31,1)
	)) +53; 


print "<br />vulcaniani<br />";

print $a->calculateVariationCost(array(
	array(60,2),
	array(59,1),
	array(61,1),
	array(56,0),
	array('WP',6),
	array('HT',6),
	));

print "<br />AND<br />";

print $a->calculateVariationCost(array(
	array(10,2),
	array(21,1),
	array(17,1),
	array(52,2),
	array(4,3),
	array('DX',6),
	array('HT',6)
	));

print "<br />trilli<br />";

print $a->calculateVariationCost(array(
	array(21,1),
	array(20,1),
	array(9,0),
	array(35,2),
	array('IQ',6),
	array('HT',7)
	));


print "<br />Betamerde<br />";

print $a->calculateVariationCost(array(
	array(61,2),
	array(20,0),
	array(60,2), 
	array('HT',4),
	array('WP',7)
	));

//echo var_dump($aar);
*/

?>