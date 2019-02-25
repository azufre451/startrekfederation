<?php session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php'); exit;}

include('includes/app_include.php');
include('includes/notifyClass.php');

$me=$_SESSION['pgID'];
$aar = array();



$res =mysql_fetch_assoc ( mysql_query('SELECT padID,paddTitle,pgAvatarSquare FROM fed_pad,pg_users WHERE paddFrom = pg_users.pgID AND  paddDeletedTo = 0 AND paddTo = '.($_SESSION['pgID']).' AND paddRead = 0 AND paddTitle NOT LIKE "::special::%" ORDER BY paddTime DESC LIMIT 1 ') ) ;

if(mysql_affected_rows()){ $aar['NP'] = 1; $aar['NPtitle'] = $res['paddTitle']; $aar['NPavatar'] = $res['pgAvatarSquare'];}
 
$notifications=NotificationEngine::getMyNotifications($_SESSION['pgID']);
if($notifications){ $aar['NPR'] = $notifications;}
 


$resNotify = mysql_query('SELECT paddText,extraField FROM fed_pad WHERE paddTo = '.($_SESSION['pgID']).' AND paddRead = 0 AND paddTitle LIKE "::special::%" LIMIT 1');
if(mysql_affected_rows()){$aal = mysql_fetch_array($resNotify); $etm = explode('::',$aal['paddText']); $aar['NOTIFY']['TEXT'] = ($etm[1]); $aar['NOTIFY']['TITLE'] = ($etm[0]);  $aar['NOTIFY']['IMG'] = $aal['extraField'];}

$res = mysql_query('SELECT IDE FROM fed_sussurri WHERE susTo = '.$_SESSION['pgID'].' AND reade = 0');
$aar['SU'] = (mysql_affected_rows()) ? true : false;


$res = mysql_query('SELECT placeAlert FROM pg_users,pg_places WHERE pgLocation = placeID AND pgID = '.($_SESSION['pgID']));

if(mysql_affected_rows())
{
	$aarA = mysql_fetch_array($res);
	$aar['AL'] = $aarA['placeAlert'];
}

echo json_encode($aar);
include('includes/app_declude.php');
//echo var_dump($aar);
?>