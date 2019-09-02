<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
 

$pres=array();

$pres_loc = array();
$resActivity= mysql_query("SELECT DISTINCT sender FROM federation_chat WHERE type <> 'SPECIFIC' AND time >= ".($curTime-900));
if(mysql_affected_rows())
	while($ras_pres_loc = mysql_fetch_assoc($resActivity))
		$pres_loc[] = $ras_pres_loc['sender']; 
 
$resPgPresenti = mysql_query("SELECT pgID as ID,place_LittleLogo1 as assign_logo, pgUser,pgSpecie,pgLock as locked,pgSesso as user_sesso,pgLastAct, pgMostrina as rank_mostrina, pgGrado as user_grado, pgSezione as user_sezione,ambientType, placeID,placeName as ship_name,locName as place_name, locID as pgPlaceI FROM pg_users,pg_ranks,fed_ambient,pg_places WHERE pgLocation = placeID AND pgLastAct >=".($curTime-1800)." AND rankCode = prio AND pgRoom = locID AND pgAuthOMA <> 'BAN' ORDER BY rankerprio DESC");

while($p=mysql_fetch_assoc($resPgPresenti))
{
	if(!array_key_exists($p['placeID'], $pres))
		$pres[ $p['placeID'] ] = array();	

	$p['pgIC'] = (in_array($p['ID'],$pres_loc) && $p['ambientType'] != 'DEFAULT');

	$pres[ $p['placeID'] ][] = $p;
}

echo json_encode($pres);

?>


