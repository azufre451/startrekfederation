<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php'); 

$whatter = array('1'=>'main','2'=>'scheda','3'=>'cdb','4'=>'db','5'=>'padd');
$what = $whatter[$_POST['what']]; 

$realK = isSet($_POST['realK']) ? true : false;

if($what == 'main')
	mysql_query("UPDATE pg_users SET pgFirst=0 WHERE pgID = ".$_SESSION['pgID']); 
	 
if($realK){
	
	mysql_query("SELECT 1 FROM pg_users_tutorial WHERE pgID = ".$_SESSION['pgID']);
	
	if (!mysql_affected_rows())	mysql_query("INSERT INTO pg_users_tutorial (pgID) VALUES (".$_SESSION['pgID'].")"); 
	
	mysql_query("UPDATE pg_users_tutorial SET $what = 1 WHERE pgID = ".$_SESSION['pgID']); 
	$dat=date('d.m.Y');
	mysql_query("UPDATE pg_users SET pgNote = CONCAT(pgNote,'\n$dat --> Completato Tutorial $what') WHERE pgID = ".$_SESSION['pgID']);
	
}

mysql_query("SELECT 1 FROM pg_users_tutorial WHERE main=1 AND scheda=1 AND cdb=1 AND db=1 AND pgID = ".$_SESSION['pgID']);
if (mysql_affected_rows())
{
	$achi = 52;
	$pgID = $_SESSION['pgID'];
	
	mysql_query("SELECT 1 FROM pg_achievement_assign WHERE owner = $pgID AND achi = 52");
	if(!mysql_affected_rows())
	{
		mysql_query("INSERT INTO pg_achievement_assign (owner,achi,timer) VALUES ($pgID,$achi,".time().")");
		$res = mysql_query("SELECT aText,aImage FROM pg_achievements WHERE aID = $achi");
		$resA = mysql_fetch_array($res);
		$Descri =$resA['aText'];
		$ima =$resA['aImage'];
		
		$cString = addslashes("Congratulazioni!!<br />Hai sbloccato un nuovo achievement!<br /><br /><p style='text-align:center'><img src='TEMPLATES/img/interface/personnelInterface/$ima' /><br /><span style='font-weight:bold'>$Descri</span></p><br />Il Team di Star Trek: Federation");
		$eString = addslashes("Hai un nuovo achievement!::$Descri"); 
		mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES (518,$pgID,'OFF: Nuovo Achievement!','$cString',".time().",0)");
		mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (518,$pgID,'::special::achiev','$eString',".time().",0,'TEMPLATES/img/interface/personnelInterface/$ima')"); 
		$currentUser = new PG($_SESSION['pgID']);
		$currentUser->addPoints(10,'TUTOR','Completamento Tutorial di Gioco','Completamento Tutorial di Gioco');
	}
}

echo json_encode(array('OK'));

?>