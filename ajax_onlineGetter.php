<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');

$curPGID=$_SESSION['pgID'];
 
$resPgPresenti = mysql_query("SELECT pgID, pgAvatar, pgUser,pgSpecie,pgLock,pgSesso,pgLastAct, pgMostrina,pgAuthOMA FROM pg_users,pg_ranks WHERE pgLastAct >= ".($curTime-1800)." AND rankCode = prio AND pgAuthOMA <> 'BAN' AND pgID <> '$curPGID' ORDER BY pgUser");

$aar = array();

 
while($pgPres = mysql_fetch_assoc($resPgPresenti)){	$aar[] = array('pgID'=> $pgPres['pgID'],'label'=> $pgPres['pgUser'],'role'=> $pgPres['pgAuthOMA'],'pgMostrina'=> $pgPres['pgMostrina']);}

$aar['PGP'] = $aar;
echo json_encode($aar);
?>