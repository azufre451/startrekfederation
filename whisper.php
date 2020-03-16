<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
mysql_query('UPDATE fed_sussurri SET reade = 1 WHERE reade=0 AND susTo = '.$_SESSION['pgID']);

$currentUser = new PG($_SESSION['pgID']);

if(isSet($_GET['justFocus'])) exit;

$vali = new validator();  
PG::updatePresence($_SESSION['pgID']);


$template = new PHPTAL('TEMPLATES/whisper_N.htm');



if(isSet($_GET['recruitment']))
{ 

	$chatNumber = mysql_query('SELECT COUNT(IDE) as CT FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 0) OR susTo = 7 OR susTo = '.$_SESSION['pgID']);
	$chatNumberL = mysql_fetch_array($chatNumber);
	$chatNumberCounter = ($chatNumberL['CT'] > 35) ? ($chatNumberL['CT']-35) : 0;

	$chatLines = mysql_query('SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 0) OR susTo = 7 OR susTo = '.$_SESSION['pgID']." ORDER BY time LIMIT $chatNumberCounter,35");
	$template->publiGetterChannel = '7';
}

else 
{

	$chatNumber = mysql_query('SELECT COUNT(IDE) as CT FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 7) OR susTo = 0 OR susTo = '.$_SESSION['pgID']);
	$chatNumberL = mysql_fetch_array($chatNumber);
	$chatNumberCounter = ($chatNumberL['CT'] > 35) ? ($chatNumberL['CT']-35) : 0;
	$chatLines = mysql_query('SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 7) OR susTo = 0 OR susTo = '.$_SESSION['pgID']." ORDER BY time LIMIT $chatNumberCounter,35");
	$template->publiGetterChannel = '0';
}

$htmlLiner=''; $MAX = 0;
while($chatLi = mysql_fetch_array($chatLines))
{
    
    if ($chatLi['susTo'] == "0" || $chatLi['susTo'] == "7" ){
       
        if(strpos(strtolower($chatLi['chat']), '@') !== false)
        { 
            $me=PG::getSomething($_SESSION['pgID'],'username');
            
            if(strpos(strtolower($chatLi['chat']), "@".strtolower($me)) !== false){
                
                $chatLi['chat']= str_replace("@".$me,"<span class=\"tagMatch\">".$me."</span>",str_replace("@".strtolower($me),"<span class=\"tagMatch\">".$me."</span>",$chatLi['chat']));
            }
		}
    }
    
	$htmlLiner.=$chatLi['chat'];
	if($chatLi['IDE'] > $MAX) $MAX = $chatLi['IDE'];
}

$template->htmlLiner = $htmlLiner;
$template->maxVIS = $MAX;

	$resPgPresenti = mysql_query('SELECT pgID, pgAvatar, pgUser,pgLock,pgMostrina,pgAuthOMA FROM pg_users WHERE pgID <> '.$_SESSION['pgID'].' AND pgLastAct >= '.($curTime-1800).' ORDER BY pgUser ASC');
	$pgArray=array();
	
	while($resPG = mysql_fetch_array($resPgPresenti))
		{
			
			if ($resPG['pgLock'])
				$atcl = 'L';

			elseif (PG::mapPermissions('A',$resPG['pgAuthOMA']))
				$atcl = 'A';
			

			else if (PG::mapPermissions('M',$resPG['pgAuthOMA']))
				$atcl = 'M';

			elseif (PG::mapPermissions('G',$resPG['pgAuthOMA']))
				$atcl = 'G';

			else
				$atcl = '';
						
			$pgArray[$resPG['pgID']] = array('label' => $resPG['pgUser'],'role' => $atcl,'pgMostrina'=>$resPG['pgMostrina']);
		}



	if (PG::mapPermissions('G',$currentUser->pgAuthOMA)){ 

		mysql_query("SELECT 1 FROM pg_users_presence WHERE pgID = ".$currentUser->ID." AND value <> 0");
		if (mysql_affected_rows() <= 5) $template->presenceForce = true;
	}


	$template->people = $pgArray;
	$template->coPeople = count($pgArray)+2;
	$template->gameOptions = $gameOptions;
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
