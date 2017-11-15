<?php
session_start();
error_reporting(E_ALL);
ini_set('error_display',1);
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php'); 
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); 

$vali = new validator();

$a = array	(		'HT' =>
						array('8'=>array(),'9'=>array(),'10'=>array(),'11'=>array(),'12'=>array(),'13'=>array(),'14'=>array(),'15'=>array(),'16'=>array(),'17'=>array(),'18'=>array(),'19'=>array(),'20'=>array() ),
				'DX' => array() 
			);
			
$a['DX']['8']['9'] = 4;
$a['DX']['9']['10'] = 5;
$a['DX']['10']['11'] = 7;
$a['DX']['11']['12'] = 9;
$a['DX']['12']['13'] = 12;
$a['DX']['13']['14'] = 15;
$a['DX']['14']['15'] = 19;
$a['DX']['15']['16'] = 24;
$a['DX']['16']['17'] = 30;
$a['DX']['17']['18'] = 38;
$a['DX']['18']['19'] = 48; 
$a['DX']['19']['20'] = 60; 


$action = $_GET['action'];

if($action == 'get-step'){ 
	$to = $vali->numberOnly($_POST['to']);
	$what = strtoupper(addslashes($_POST['what']));
	
	$res = array('prev' =>  getCost($what,$to,$to-1,$a),'cost' =>  getCost($what,$to-1,$to,$a),'next' =>  getCost($what,$to,$to+1,$a));
	echo json_encode($res);
} 

function getCost($what,$from,$to,$matrix)
{
	if($from == $to) return 0;
	else if($from > $to) return -getCost($what,$to,$from,$matrix);
	else if (!isSet( $matrix[$what][(string)($from)][(string)($to)])) return NULL;
	else return $matrix[$what][(string)($from)][(string)($to)];
}
 

?>