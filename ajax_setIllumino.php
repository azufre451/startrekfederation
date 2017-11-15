<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
$vali = new validator();
$amb = addslashes($_POST['ambient']);
$userN = PG::getSomething($_SESSION['pgID'],"username");
	$user = new PG($_SESSION['pgID']);
	if($user->pgLock || $user->pgAuthOMA == 'BAN') exit;
if(isSet($_GET['toggle'])) {
$res=mysql_query("SELECT ambientLight FROM fed_ambient WHERE locID = '$amb'");
$rea = mysql_fetch_array($res); 
if($rea['ambientLight']==1)
	{ 
		$string = '<p class="auxAction" title="Inviata da: '.$userN.'">Le luci nella stanza si accendono</p>';
		$target=5;
	}
else
	{
		$string = '<p class="auxAction" title="Inviata da: '.$userN.'">Le luci nella stanza si spengono</p>';
		$target=1;
	}
	
	mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL')");
	$res = mysql_query("UPDATE fed_ambient SET ambientLight = '$target' WHERE locID='$amb'");
}

else if(isSet($_GET['lightSet']))
{
$color = addslashes($_POST['color']);
$res = mysql_query("UPDATE fed_ambient SET ambientLightColor='$color' WHERE locID='$amb'");

}
else{
$li = $vali->numberOnly($_POST['light']);
$te = $vali->numberOnly($_POST['temperature']);
$color = addslashes($_POST['color']);
if($te > 40) $te = 40;
$res = mysql_query("UPDATE fed_ambient SET ambientLightColor='$color', ambientLight = '$li', ambientTemperature = '$te' WHERE locID='$amb'");

if($li == 1) $string = "<p class=\"auxAction\">Le luci si spengono e la stanza raggiunge gradualmente i $te &deg;C." .'</p>';
if($li == 2) $string = "<p class=\"auxAction\">Le luci ora sono basse e la stanza raggiunge gradualmente i $te &deg;C." .'</p>';
if($li == 3) $string = "<p class=\"auxAction\">Nella stanza le luci sono soffuse e la temperatura arriva a $te &deg;C." .'</p>';
if($li == 4) $string = "<p class=\"auxAction\">La stanza si porta ad un livello di lluminazione medio e vengono raggiunti i $te &deg;C." .'</p>';
if($li == 5) $string = "<p class=\"auxAction\">Le luci si accendono al massimo e la stanza raggiunge gradualmente i $te &deg;C." .'</p>';

mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL')");
}
?>