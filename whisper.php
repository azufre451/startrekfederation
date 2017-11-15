<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php");
mysql_query('UPDATE fed_sussurri SET reade = 1 WHERE reade=0 AND susTo = '.$_SESSION['pgID']);

if(isSet($_GET['justFocus'])) exit;

$vali = new validator();  
PG::updatePresence($_SESSION['pgID']);


if(isSet($_GET['recruitment']))
{
$template = new PHPTAL('TEMPLATES/whisper_recruit.htm');

$chatNumber = mysql_query('SELECT COUNT(IDE) as CT FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 0) OR susTo = 7 OR susTo = '.$_SESSION['pgID']);
$chatNumberL = mysql_fetch_array($chatNumber);
$chatNumberCounter = ($chatNumberL['CT'] > 35) ? ($chatNumberL['CT']-35) : 0;

$chatLines = mysql_query('SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 0) OR susTo = 7 OR susTo = '.$_SESSION['pgID']." ORDER BY time LIMIT $chatNumberCounter,35");
$htmlLiner=''; $MAX = 0;
while($chatLi = mysql_fetch_array($chatLines))
{
	$htmlLiner.=$chatLi['chat'];
	if($chatLi['IDE'] > $MAX) $MAX = $chatLi['IDE'];
}

$template->htmlLiner = $htmlLiner;
$template->maxVIS = $MAX;
}

else 
{
$template = new PHPTAL('TEMPLATES/whisper.htm');

$chatNumber = mysql_query('SELECT COUNT(IDE) as CT FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 7) OR susTo = 0 OR susTo = '.$_SESSION['pgID']);
$chatNumberL = mysql_fetch_array($chatNumber);
$chatNumberCounter = ($chatNumberL['CT'] > 35) ? ($chatNumberL['CT']-35) : 0;

$chatLines = mysql_query('SELECT IDE,chat,time,susFrom,susTo FROM fed_sussurri WHERE (susFrom = '.$_SESSION['pgID'].' AND susTo <> 7) OR susTo = 0 OR susTo = '.$_SESSION['pgID']." ORDER BY time LIMIT $chatNumberCounter,35");
$htmlLiner=''; $MAX = 0;
while($chatLi = mysql_fetch_array($chatLines))
{
	$htmlLiner.=$chatLi['chat'];
	if($chatLi['IDE'] > $MAX) $MAX = $chatLi['IDE'];
}

$template->htmlLiner = $htmlLiner;
$template->maxVIS = $MAX;
}


	$resPgPresenti = mysql_query('SELECT pgID, pgAvatar, pgUser,pgAuthOMA FROM pg_users WHERE pgID <> '.$_SESSION['pgID'].' AND pgLastAct >= '.($curTime-1800).' ORDER BY pgUser ASC');
	$pgArray=array('S' => array(), 'N' => array());
	
	while($resPG = mysql_fetch_array($resPgPresenti))
		{
			if (PG::mapPermissions('A',$resPG['pgAuthOMA']))
			{	
				$ptcl = '[A]';
				$atcl = 'Admin';
				$kp='S';
			}

			else if (PG::mapPermissions('M',$resPG['pgAuthOMA']))
			{	
				$ptcl = '[M]';
				$atcl = 'Master';
				$kp='S';

			}

			elseif (PG::mapPermissions('G',$resPG['pgAuthOMA']))
			{
				$ptcl = '[G]';
				$atcl = 'Guida';
				$kp='S';
			}

			else
				{
					$ptcl='';
					$atcl = '';
					$kp='N';
				}
						
			$pgArray[$kp][$resPG['pgID']] = array('label' => $ptcl.' '.$resPG['pgUser'],'role' => $atcl);
		}

	$template->people = $pgArray;
	$template->coPeople = count($pgArray)+2;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}

include('includes/app_declude.php');	
?>
