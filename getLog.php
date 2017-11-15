<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
$vali = new validator();  
$user=new PG($_SESSION['pgID']);
$userName = $user->pgUser;

$ambient = $vali->killchars(htmlentities(addslashes($_GET['amb'])));
$fromID = $vali->killchars(htmlentities(addslashes($_GET['lastID'])));
$htmlLiner = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<link rel="shortcut icon" href="favicon.ico" />
	
	<title>STAR TREK FEDERATION</title>
<script>
		function selectOccur(tex){}
		function deselectOccur(tex){}
</script>
<style>	
body
{	
	background-color:black;
	color:white;
	display: block;
    height:auto;
	font-family:Verdana, Helvetica, Arial;
}
img{border:0px;}
.chatAction,.subspaceCom,.commMessage,.chatQuotation,.chatQuotationAction,.chatUser{font-style:italic;}
.chatAction,.subspaceCom,.commMessage,.chatTag,.masterAction, .globalAction,.offAction,.auxAction, .tempMasterAction,.specificMasterAction,.oloMasterAction{font-weight:bold;}
.chatAction,.subspaceCom,.commMessage,.chatDirect{margin:0px;margin-top:4px;}
.chatAction,.subspaceCom,.commMessage{font-size:12px;}

.chatAction{color:#3188F3; line-height:16px;}
.subspaceCom,.commMessage{color:#ffefcc;}
.subspaceComPre,.commPreamble{font-size:11px;color:#e8a30e;}
.chatDirect{font-size:13px;color:#EEE;}
.chatInvisi{height:0px;}
.chatQuotation{color:#d7a436;}
.chatQuotationAction,.chatUser{color:#999;}

.chatTag{font-size:11px;color:#d7a436;}
.chatUser{margin-right:5px;}
.masterAction, .globalAction,.offAction,.auxAction,.specificMasterAction,.oloMasterAction
{
	padding:8px;
	border:1px solid;
	font-size:15px;
	margin:5px;
	text-align:center;
}
.globalAction  div , .masterAction  div, .offAction  div, .auxAction  div:first-of-type, .specificMasterAction  div, .oloMasterAction div {
float:left;
font-size: 10px;
margin-top:-8px;
margin-left:-8px;
padding: 3px 10px; 
border-bottom-width:1px;
border-right-width:1px;
border-bottom-style:solid;
border-right-style:solid;
}
.globalAction{border-color:#3188F3; color:#3188F3;}
.globalAction  div {background-color: #14335a; border-color:#3188F3; color:white;}

/*Master*/
.masterAction{border-color:red; color:red;}
.masterAction   div {background-color: #850000; border-color:red; color:white;} 

/*OFF*/
.auxAction{border-color:#b3b3b3; color:#b3b3b3;}
.auxAction  div:first-of-type {background-color: #333; border-color:#b3b3b3; color:white;} 
 
.offAction{border-color:#1db716; color:#1db716;}  
.offAction  div {background-color: #175a14; border-color: #1db716; color:white;}
 
.specificMasterAction{border-color:#c67729; color:#c67729;}  
.specificMasterAction  div {background-color: #8a5e09; border-color: #c67729; color:white;}
 
.oloMasterAction{border-color:#feff84; color:#feff84;} 
.oloMasterAction  div {background-color: #6e6f1f; border-color: #feff84; color:white;}

.imageAction{text-align:center; margin:5px;}
.imageAction img {border:0px; max-height:250px; border:1px solid #3188F3; padding:5px;}
.imaLer:hover{border:1px solid #ff9900;}
.imaLer{border:1px solid black;}
.blackOpacity img {vertical-align:middle;}
.chatUser
{
cursor:pointer;
}

.repliLine{width:630px; margin:auto; height:auto;}
.repliLeft, .repliRight{margin:0px; font-weight:normal; text-align:center;  font-size:15px; font-weight:bold; display: inline-block; vertical-align:middle;}
.repliLeft img{vertical-align:middle; float:left; width:150px; display: table-cell; vertical-align:middle;}
.repliRight span{font-style:italic; color:white; font-weight:normal; font-size:11px;}
.repliLeft{width:150px;}
.repliRight{width:455px; margin-left:5px;}

.officers{margin:0px;}

input, select, textarea, button
{
	color:#999;
	border:1px solid #999;
	background-color:black;
    font-family: Helvetica;
    font-size: 12px;
    padding: 1px;
}

textarea {font-size:12px;}
input:focus, select:focus, textarea:focus, button:focus
{
	color:white;
	border:1px solid white;
}

input:hover, select:hover, textarea:hover, button:hover
{
	color:white;
	border:1px solid white;
}

</style>	
</head>


<body style="background-color:black;">
<div style="float:left; width:40%; border:1px solid #333; margin-left:30px; padding:20px;"><p style="text-align:center;color:orange; font-weight:bold;">Presenti alla giocata:</p><br /><table><tr style="padding:20px; font-size:12px; text-align:center;"><td style="width:180px;">PG</td><td>Prima Azione</td><td>Ultima Azione</td><td>Azioni</td></tr>
';

if ($fromID > 0)
{
	$currentAmbient = Ambient::getAmbient($ambient);
	$currentLocation = PG::getLocation($currentAmbient['ambientLocation']);
	$currentLocationName = $currentLocation['placeName'];
		$placeLogo=$currentLocation['placeLogo'];
	
	$presents = mysql_query("SELECT DISTINCT pgUser,ordinaryUniform,pgGrado,pgSezione,MIN(time) as minner, MAX(time)  as maxer,COUNT(chat) as chatter FROM pg_users,federation_chat,pg_ranks WHERE sender=pgID AND prio=rankCode AND ambient = '$ambient' AND IDE >= $fromID AND type IN ('DIRECT','ACTION') GROUP BY pgUser,ordinaryUniform,pgGrado,pgSezione ORDER BY minner ASC");
	$userLister='';
	while($resa=mysql_fetch_array($presents))
	{
		$ima=$resa['ordinaryUniform'];
		$person = $resa['pgUser'];
		$minner = date('H:i:s',$resa['minner']);
		$maxer = date('H:i:s',$resa['maxer']);
		$chatter = $resa['chatter'];
		$title= $resa['pgGrado']." - ".$resa['pgSezione'];
		$htmlLiner .= "<tr class=\"chatUser officers\" style=\"font-size:12px;\"><td style=\"color:white;\"><img src=\"TEMPLATES/img/ranks/$ima.png\" title=\"$title\" /> $person</td><td>$minner</td><td>$maxer</td><td>$chatter</td></tr>";
		$userLister.="$person, ";
	}
	
	$locName=$currentAmbient['locName'];

	$htmlLiner.="</table></div><div style=\"float:right; text-align:center; width:30%; margin-left:30px;\"><div style=\"border:1px solid #666; padding:20px;\"><b>Codice Auto-Mostrine per CDB:</b> <i>Copia questa lista e incollala nel tool \"Avanzate\" del CDB per ottenere la lista dei presenti con mostrine e incarichi.</i><br /><br />
	
	<input value=\"$userLister\" onclick=\"javascript:this.select();\" style=\"width:97%\"/></div>
	
	<p style=\"font-family:Arial; font-weight:bold; font-size:22px;\"><img src=\"TEMPLATES/img/logo/$placeLogo\" height=\"100px\" align=\"left\">$locName<br />$currentLocationName</p>
	
	</div><div style=\"clear:both\" /><br /><hr /><div style=\"padding:20px; border:1px solid #666; margin-top:20px;\">
	";
	
	$chatLines = mysql_query("SELECT chat,time FROM federation_chat WHERE ambient = '$ambient' AND IDE >= $fromID AND type NOT IN ('APM','AUDIO','AUDIOE','SPECIFIC') ORDER BY time");
	
	
	while($chatLi = mysql_fetch_array($chatLines))
	{	
		if(!isSet($head))
		{
		$htmlLiner.="<p style=\"text-align:center; color:white; font-weight:bold;\">Inizio del log alle: <span style=\"color:#3188F3;\">".date('H:i:s',$chatLi['time'])."</span> del <span style=\"color:#3188F3;\">".date('d-m-Y',$chatLi['time'])."</span></p><br />";
		$head=true;
		}
		$htmlLiner.=$chatLi['chat'];
	}
	
	$htmlLiner.="</div></body></html>";
	
	$fileName = "temp/log_$userName.html";
	$fh = fopen($fileName, 'w');
	
	
	fwrite($fh, str_replace('TEMPLATES/img/','http://www.startrekfederation.it/TEMPLATES/img/',$htmlLiner));
	$size = filesize($fileName);//calcola dimensione del file 
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Cache-Control: private');
	header('Pragma: no-cache');
	header("Content-Transfer-Encoding: binary");
	header("Content-length: {$size}");
	header("Content-type: text/html");
	$tit = date('d-m-Y').' - '.$currentAmbient['locName'].' - '.date('H.i').'.html';
	header("Content-disposition: attachment; filename=\"{$tit}\"");
	readfile($fileName);
}

	

include('includes/app_declude.php');	


?>
