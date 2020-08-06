<?php
session_start();
if (!isSet($_SESSION['pgID']))  header("Location:index.php?login=do");

include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/savedSessionsClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
$vali = new validator();  

$user=new PG($_SESSION['pgID']);
$userName = $user->pgUser;
$userID = $user->ID;



if (isSet($_GET['getAllPlayerLogs']))
{
	$kind = (isSet($_POST['masterLog'])) ? 'MASTER' : 'PLAY';
	$logLimit = $vali->numberOnly($_POST['logLimit']);


	if (!in_array($logLimit,array(0,1,2,3,6,12)))
	{
		echo "Valore non valido"; exit;
	}
	if (($logLimit == 0 || $logLimit == 12) && !$user->checkTempAuth('ELOG'))
	{
		echo '<body style="background-color:black; color: white;"><p style="font-family:Helvetica; font-size:15px;">Autorizzazioni all\'operazione non sufficienti. Devi contattare un Global Master per farti autorizzare al download di grandi volumi di log</p></body>'; exit;
	}



	$targetuserID=$vali->numberOnly($_GET['getAllPlayerLogs']);
	
	if ( $targetuserID == $_SESSION['pgID'] || PG::mapPermissions("A",$user->pgAuthOMA))
	{

		$targetuser=new PG($targetuserID);



		$zip = new ZipArchive;

		$zipName = "../stf-data/temp/log_complete_".$_SESSION['pgID'].".zip";
		if(file_exists($zipName)) 
			unlink($zipName);
		
		$timeLimit = ($logLimit != 0) ? $curTime - (24*60*60*30*$logLimit) : $logLimit;
		
		$re= mysql_query("SELECT DISTINCT federation_sessions_participation.sessionID FROM federation_sessions_participation,federation_sessions WHERE federation_sessions.sessionID = federation_sessions_participation.sessionID AND  sessionEnd > $timeLimit AND pgID = ".$targetuser->ID." AND kind = '".$kind."' ORDER BY sessionID");

		if (mysql_affected_rows())
		{


			$res = $zip->open($zipName, ZipArchive::CREATE);

			while($resession = mysql_fetch_assoc($re))
			{
				$sessionToAdd = new Session($resession['sessionID']);

		 		if ($sessionToAdd->sessionIniTime > 0 && $sessionToAdd->sessionStopTime > 0 )
		 		{
					if ($res === TRUE) {
						$locationName = ($sessionToAdd->locName != '' ) ? $sessionToAdd->locName : $sessionToAdd->locID;
						$formattedFileName = date('Y',$sessionToAdd->sessionIniTime).'_'.date('m',$sessionToAdd->sessionIniTime).'_'.date('d',$sessionToAdd->sessionIniTime).'_'.str_replace(':','_',$sessionToAdd->locName).'.html';

				    $zip->addFromString($formattedFileName, $sessionToAdd->getText(0,PG::mapPermissions("SM",$user->pgAuthOMA)));
					}
				}
			}
			$zip->close();

			$size = filesize($zipName);//calcola dimensione del file 
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Cache-Control: private');
			header('Pragma: no-cache');
			header("Content-Transfer-Encoding: binary");
			header("Content-length: {$size}");
			header("Content-type: application/zip");
			$tit = 'logs_'.str_replace('\'','',$targetuser->pgUser).'.zip';
			header("Content-disposition: attachment; filename=\"{$tit}\"");
			readfile($zipName);
		}
		else{
			echo '<body style="background-color:black; color:white;"><div id="indi_mainContainer">
			<p style="text-align:center; font-size:20px; margin:20px; font-family:Helvetica;"><img src="https://oscar.stfederation.it/SigmaSys/promo_stf/little_logo.png" /><br /><br />Sembra tu non abbia log da generare...<br /><br /><img src="https://oscar.stfederation.it/SigmaSys/PNG/Kavanagh_001.png" /></p>
			<p style="text-align:center; font-size:20px; margin:20px; font-family:Helvetica;">Dovresti giocare per generarne qualcuno...</p>
			</div>';
			exit;
		}
	}
	exit;
}

else 
	{

	$sessionID = $vali->numberOnly(($_GET['session']));

	if ($sessionID > 0)
	{

		$sesser = new Session($sessionID);
		if(isSet($_GET['toFile']))
			$sesser->getText(1,PG::mapPermissions("SM",$user->pgAuthOMA));
		else
			echo $sesser->getText(0,PG::mapPermissions("SM",$user->pgAuthOMA),1);

	}
}

include('includes/app_declude.php');	


?>

