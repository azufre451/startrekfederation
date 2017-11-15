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
		$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si accendono </div>';

		$target=5;
	}
else
	{
		$string='<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>Le luci nella stanza si spengono </div>';

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

$k='<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta ad un giocatore per aver consultato il computer di bordo o premuto un tasto automatizzato (luci, replicatori, biolettini etc.)." /> Comando Utente</div>';


if($li == 1) $string = $k."Le luci si spengono e la stanza raggiunge gradualmente i $te &deg;C." .'</div>';
if($li == 2) $string = $k."Le luci ora sono basse e la stanza raggiunge gradualmente i $te &deg;C." .'</div>';
if($li == 3) $string = $k."Nella stanza le luci sono soffuse e la temperatura arriva a $te &deg;C." .'</div>';
if($li == 4) $string = $k."La stanza si porta ad un livello di lluminazione medio e vengono raggiunti i $te &deg;C." .'</div>';
if($li == 5) $string = $k."Le luci si accendono al massimo e la stanza raggiunge gradualmente i $te &deg;C." .'</div>';

mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$amb','$string',".time().",'NORMAL')");
}
?>