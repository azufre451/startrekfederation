<html>
<head>
	<style>
		body{ color:white; background-color: black; font-size:14px; font-family:Helvetica; }
		td{border:1px solid #AAA;}
	</style>
</head>
<?php
chdir('../');
include('includes/app_include.php');
include('includes/abilDescriptor.php');


/*Bonus*/

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
$speciesTO=array('Umana','Bajoriana','Caitiana','Vulcaniana','Andoriana','Trill','Betazoide','Boliana','Xenita','Terosiana','Tellarita','Zaldan','Koyar');
sort($speciesTO);
foreach ($speciesTO as $species)
{
	
	echo "<div style='width:300px; height:500px; float:left; margin-left:10px;'><table style='width:100%; border:1px solid black;'><tr><td colspan=2><b>".$species."</b></td><td /></tr>";

	$var=array();

	foreach( $bonuses[$species]['CAR'] as $car){
		$var[] = array($car['abID'],$cuAb[$car['abID']]+$car['abMod']);
		$tneg=($car['abMod'] < 0) ? 'style="color:red;"' : '';
		echo '<tr><td><img title="'.$car['reason'].'" src="../TEMPLATES/img/interface/personnelInterface/abilita/' . $info[$car['abID']]['abImage'] . '" style="width:40px;" /></td><td '.$tneg.'>' . $car['abMod'] . '</td><td>'.$info[$car['abID']]['abName'].'</td></tr>';
	}

	
	foreach( $bonuses[$species]['ABI'] as $abi)
	{
		$var[] = array($abi['abID'],$abi['abMod']);
		$tneg=($abi['abMod'] < 0) ? 'style="color:red;"' : '';
		echo '<tr><td><img title="'.$abi['reason'].'" src="../TEMPLATES/img/interface/personnelInterface/abilita/' . $info[$abi['abID']]['abImage'] . '" style="width:40px;" /></td><td '.$tneg.'>' . $abi['abMod'] . '</td><td>'.$info[$abi['abID']]['abName'].'</td></tr>';
	}

	$cost=$a->calculateVariationCost($var);
	//print_r($var);
	if($species == 'Umana') $cost = ($cost+50) . '***';
	echo "<tr><td colspan=2><b>".$species."</b></td><td>".$cost."</td></tr></table></div>";
	
}

exit;

 include('includes/app_declude.php');
?>

</html>