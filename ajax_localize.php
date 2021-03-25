<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
 
$mode=$_GET['s'];

$pres=array();
$pres_loc = array();

if ($mode == 'loc'){

	$resActivity= mysql_query("SELECT DISTINCT sender FROM federation_chat WHERE type <> 'SPECIFIC' AND time >= ".($curTime-900));
	if(mysql_affected_rows())
		while($ras_pres_loc = mysql_fetch_assoc($resActivity))
			$pres_loc[] = $ras_pres_loc['sender']; 
 
	$resPgPresenti = mysql_query("SELECT pgID as ID,place_LittleLogo1 as assign_logo, pgUser,pgSpecie,pgLock as locked,pgSesso as user_sesso,pgLastAct, pgMostrina as rank_mostrina, pgGrado as user_grado, pgSezione as user_sezione,ambientType, placeID,placeName as ship_name,locName as place_name, locID as pgPlaceI FROM pg_users,pg_ranks,fed_ambient,pg_places WHERE pgLocation = placeID AND pgLastAct >=".($curTime-1800)." AND rankCode = prio AND pgRoom = locID AND pgAuthOMA <> 'BAN' ORDER BY rankerprio DESC, pgUser ASC");

	while($p=mysql_fetch_assoc($resPgPresenti))
	{
		if(!array_key_exists($p['placeID'], $pres))
			$pres[ $p['placeID'] ] = array();	

		$p['pgIC'] = (in_array($p['ID'],$pres_loc) && $p['ambientType'] != 'DEFAULT');

		$pres[ $p['placeID'] ][] = $p;
	}

}
elseif ($mode == 'comm')
{
	$pres=array('places'=>array(),'people'=>array());

	$pgID=$_SESSION['pgID'];
	$usQ = mysql_query("SELECT pgLocation,pointerL,pgRoom FROM pg_places,pg_users WHERE pgLocation = placeID AND pgID = $pgID");
	$usLL = mysql_fetch_array($usQ);
	$locat = $usLL['pgLocation'];
	$pointerL = $usLL['pointerL'];
	$currentAmbient = $usLL['pgRoom'];
	

	if($_GET['stm'] == 'ppl'){

		$timeLimit = $curTime-1800;

		$resPgPresenti = mysql_query("
			SELECT 
				pgID as ID,
				place_LittleLogo1 as assign_logo,
				pgUser,pgSpecie,pgLock as locked,
				pgSesso as user_sesso,pgLastAct,
				pgMostrina as rank_mostrina,
				pgAvatarSquare as pgAvatar,
				pgGrado as user_grado,
				pgSezione as user_sezione,
				placeID,
				placeName as ship_name,
				locName as place_name,
				locID as pgPlaceI
			FROM pg_users,pg_ranks,fed_ambient,pg_places
			WHERE 
				pgID <> $pgID AND
				pgLocation = placeID AND
				ambientLocation = placeID AND
				rankCode = prio AND
				pgRoom = locID AND
				pgAuthOMA <> 'BAN' AND
				ambientType <> 'DEFAULT' AND
				pgLastAct >= $timeLimit AND
				(placeID = '$locat' OR attracco = '$locat' OR (pointerL = '$pointerL' AND pointerL <> ''))
			ORDER BY pgUser ASC");

		while($p=mysql_fetch_assoc($resPgPresenti))
		{
			if(!array_key_exists($p['placeID'], $pres['people']))
				$pres['people'][ $p['placeID'] ] = array('plName' => $p['ship_name'], 'ppl' => array() );	

			$pres['people'][ $p['placeID'] ]['ppl'][] = $p;
		}

	}
	elseif($_GET['stm'] == 'pla'){

		
		$resAmbient = mysql_query("SELECT locID,placeType, IF(attracco = '',placeID,attracco) as placeID, placeName as ship_name,attracco,locName,image,icon FROM pg_places,fed_ambient WHERE ambientLocation = placeID AND ambientType <> 'DEFAULT' AND (placeID = '$locat' OR attracco = '$locat' OR (pointerL = '$pointerL' AND pointerL <> '')) ORDER BY attracco,locName");



		while($p = mysql_fetch_assoc($resAmbient))
		{

			if($p['placeType'] == 'Pianeta')
				$p['image'] = $p['icon'];

			if($p['attracco'] != '')
				$p['locName'] = $p['locName'] . ' ('.$p['ship_name'].')';



			$p['myLocat'] = ( $p['locID'] == $currentAmbient) ? 1 : 0;

			if(!array_key_exists($p['placeID'], $pres['places']))
				$pres['places'][ $p['placeID'] ] = array('plName' => $p['ship_name'], 'places' => array() );	

			$pres['places'][$p['placeID']]['places'][] = $p;
		}
	}
}

echo json_encode($pres);

?>


