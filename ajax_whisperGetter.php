<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');

//if($_SESSION['pgID'] == '1005') session_destroy();

$last = addslashes($_POST['lastID']);
$vinculum = addslashes($_POST['vinculum']);
$focused = isSet($_POST['focused']) ? addslashes($_POST['focused']) : 0;

$aar = array();

$curPGID=$_SESSION['pgID'];
 
$resPgPresenti = mysql_query("SELECT pgID, pgUser, pgMostrina,pgAuthOMA,pgLock,png FROM pg_users,pg_ranks WHERE pgLastAct >= ".($curTime-1800)." AND rankCode = prio AND pgAuthOMA <> 'BAN' AND pgID <> '$curPGID' ORDER BY pgUser");
  
while($pgPres = mysql_fetch_assoc($resPgPresenti)){

	if (!$pgPres['png'] && $pgPres['pgLock'])
		$role='L';
	elseif (!$pgPres['png'] && PG::mapPermissions('A',$pgPres['pgAuthOMA']))
				$role = 'A';
	else if (!$pgPres['png'] && PG::mapPermissions('M',$pgPres['pgAuthOMA']))
				$role = 'M';
	elseif (!$pgPres['png'] && PG::mapPermissions('G',$pgPres['pgAuthOMA']))
				$role = 'G';
	else
		$role='';

	$aar[] = array('pgID'=> $pgPres['pgID'],'label'=> $pgPres['pgUser'],'role'=> $role,'pgMostrina'=> $pgPres['pgMostrina']);
}




$aar['PGP'] = $aar;

if ((int)$vinculum == 0 || (int)$vinculum == 7)
{
	$chatLines = mysql_query("SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE IDE > $last AND ((susFrom = ".$_SESSION['pgID']." AND susTo NOT IN (0,7)) OR susTo = $vinculum OR susTo = '$curPGID') ORDER BY time ASC");
	if($focused) mysql_query('UPDATE fed_sussurri SET reade = 1 WHERE reade=0 AND susTo = '.$curPGID);
	$htmlLiner='';
	$MAX = 0;
	while($chatLi = mysql_fetch_assoc($chatLines)){
	     
    if ($chatLi['susTo'] == "7" || $chatLi['susTo'] == "0" ){
       
        if(strpos(strtolower($chatLi['chat']), '@') !== false)
        { 
            $me=PG::getSomething($_SESSION['pgID'],'username');
            
            if(strpos(strtolower($chatLi['chat']), "@".strtolower($me)) !== false){
                
                $chatLi['chat']= str_replace("@".$me,"<span class=\"tagMatch\">".$me."</span>",str_replace("@".strtolower($me),"<span class=\"tagMatch\">".$me."</span>",$chatLi['chat']));
            }
		}
    } 
	    
	    $htmlLiner.=$chatLi['chat'];
		if ($chatLi['IDE'] > $MAX) 	$MAX = $chatLi['IDE'];
	}
	$aar['CH'] = $htmlLiner;
	$aar['LCH'] = $MAX;

}
echo json_encode($aar);
include('includes/app_declude.php');
?>