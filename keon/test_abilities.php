<?php
chdir('../');
session_start();
include('includes/app_include.php');
include('includes/cdbClass.php');
include('includes/abilDescriptor.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 

if(isSet($_SESSION['pgID']))
{
    
    PG::updatePresence($_SESSION['pgID']);
    $currentUser = new PG($_SESSION['pgID']);
}
else{
	header('Location:../login.php');
	exit;
}
if(isSet($_GET['ajax']))
{

	$a=new abilDescriptor(5); //Namor
	$var=array();
	$cuAb=$a->getCars();
	$qq= array();

	foreach($_POST['payload'] as $abv)
	{

		$abil=addslashes($abv[0]);
		$abilV=(int)($abv[1]);		
		if(is_numeric($abil)){
			
			$var[] = array($abil,$abilV);
			$qq[] = "('$abil','$abilV','ABI')";
		}
		else{
			$abilC = $cuAb[$abil]+$abilV;
			$var[] = array($abil,$abilC);
			$qq[] = "('$abil','$abilV','CAR')";
		}

	}
	
	$cost=$a->calculateVariationCost($var); 
	echo json_encode(array('cost'=>$cost,'txt'=>implode(',',$qq)));
	exit;
}

elseif(isSet($_GET['abSearch']))
{

	$term = trim(addslashes($_GET['term']));

	$res = mysql_query("SELECT abName as value,abID,abImage,abClass,abDescription FROM pg_abilita WHERE abName LIKE '%$term%' ORDER BY abDiff ASC");
	$aar = array();
	while ($row = mysql_fetch_assoc($res)) {
	$aar[] = $row;
	}
	echo json_encode($aar);
	exit;
}


$template = new PHPTAL('keon/htm/test_abilities.htm');


$bonuses=array();
$info=array();
$resModQ=mysql_query("SELECT pg_abilita.abID,abName,abImage,abMod,reason,0 as special,species,type FROM pg_abilita_bonus,pg_abilita WHERE pg_abilita.abID = pg_abilita_bonus.abID ORDER BY type ASC, abMod ASC");
while($resMod=mysql_fetch_assoc($resModQ))
{
	if(!array_key_exists($resMod['species'],$bonuses))
		$bonuses[$resMod['species']] = array('ABI'=>array(), 'CAR'=>array());

	
	$bonuses[$resMod['species']][$resMod['type']][] = $resMod;

	$info[$resMod['abID']] = array('abName'=>$resMod['abName'], 'abImage'=>$resMod['abImage']);
}

$a=new abilDescriptor(5); //Namor

$cuAb=$a->getCars();
$speciesTO=array('Umana','Bajoriana','Caitiana','Deltana','Vulcaniana','Andoriana','Trill','Betazoide','Boliana','Xenita','Terosiana','Tellarita','Zaldan','Koyar');

$speciesTO=array();
$rus=mysql_query("SELECT DISTINCT species FROM pg_abilita_bonus");
while($ra = mysql_fetch_assoc($rus))
	$speciesTO[]=$ra['species'];

sort($speciesTO);

$speciesOut=array();
foreach ($speciesTO as $species)
{
	
	if (!array_key_exists($species, $speciesOut))
		$speciesOut[$species] = array('cost'=>0,'mods'=>array());

	$var=array();

	foreach( $bonuses[$species]['CAR'] as $car){
		$var[] = array($car['abID'],$cuAb[$car['abID']]+$car['abMod']);
		$tneg=($car['abMod'] < 0) ? 'style="color:red;"' : '';

		$speciesOut[$species]['mods'][] = array('reason'=> '<span style="color:#FC0">'.$car['abName'].'</span> - ' . $car['reason'], 'img'=> $info[$car['abID']]['abImage'], 'abName' => $info[$car['abID']]['abName'],'abMod' => $car['abMod']  );
	}

	
	foreach( $bonuses[$species]['ABI'] as $abi)
	{
		$var[] = array($abi['abID'],$abi['abMod']);
		$tneg=($abi['abMod'] < 0) ? 'style="color:red;"' : '';

		$speciesOut[$species]['mods'][] = array('reason'=> '<span style="color:#FC0">'.$abi['abName'].'</span> - ' . $abi['reason'], 'img'=> $info[$abi['abID']]['abImage'], 'abName' => $info[$abi['abID']]['abName'],'abMod' => $abi['abMod']  );
	}

	$cost=$a->calculateVariationCost($var); 
	if($species == 'Umana') $cost = ($cost+50) . '***';
	$speciesOut[$species]['cost'] = $cost;

}


$template->speciesOut = $speciesOut;
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