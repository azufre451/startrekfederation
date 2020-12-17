<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
$term = addslashes($_POST['term']);

 $res= mysql_query("SELECT * FROM pg_ranks WHERE aggregation = '$term' ORDER BY rankerprio ASC");
 //echo "SELECT * FROM pg_ranks WHERE aggregation = '$term' ORDER BY rankerprio ASC";
 echo mysql_error();

$aar = array();

$sects=array();
$ranks=array();
while ($row = mysql_fetch_array($res)){
	$rank=$row['Rgrado'];
	$sect=$row['Rsezione'];
	
	if(!in_array($sect, $sects))
		$sects[]=$sect;

	if(!in_array($rank, $ranks))
		$ranks[]=$rank;

	if(!array_key_exists($rank, $aar))
		$aar[$rank]= array();

	if(!array_key_exists($sect, $aar[$rank]))
		$aar[$rank][$sect]= array();
	
	$aar[$rank][$sect][] = array('prio'=> $row['prio'], 'image'=>$row['ordinaryUniform'],'rank'=>$rank,'section'=>$sect,'note'=>$row['Note']);
}


$finArray=array();
foreach($ranks as $rk){
	$rower=array();
	foreach($sects as $sk){
		if (array_key_exists($rk, $aar))
			if (array_key_exists($sk, $aar[$rk]))
				$rower[] = $aar[$rk][$sk];
			else
				$rower[] = 0;
		else
			$rower[] = 0;
	}
	$finArray[] = $rower;
}


//print_r($finArray);

$data=array('aggregator'=> $term, 'cols' => $sects,'data'=>$finArray);
echo json_encode($data);
include('includes/app_declude.php');

?> 