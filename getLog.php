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



if (isSet($_GET['getAllPlayerLogs']))
{
	$kind = (isSet($_GET['master'])) ? 'MASTER' : 'PLAY';

	$targetuserID=$vali->numberOnly($_GET['getAllPlayerLogs']);
	
	if ( $targetuserID == $_SESSION['pgID'] || PG::mapPermissions("A",$user->pgAuthOMA))
	{

		$targetuser=new PG($targetuserID);



		$zip = new ZipArchive;

		$zipName = "temp/log_complete_".$_SESSION['pgID'].".zip";
		if(file_exists($zipName)) 
			unlink($zipName);
		
		$re= mysql_query("SELECT DISTINCT sessionID FROM federation_sessions_participation WHERE pgID = ".$targetuser->ID." AND kind = '".$kind."' ORDER BY sessionID");

		if (mysql_affected_rows())
		{


			$res = $zip->open($zipName, ZipArchive::CREATE);

			while($resession = mysql_fetch_assoc($re))
			{
				$sessionToAdd = new Session($resession['sessionID']);

		 		if ($sessionToAdd->sessionIniTime > 0 && $sessionToAdd->sessionStopTime > 0 )
		 		{
					if ($res === TRUE) {
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
			echo '<div id="indi_mainContainer">
			<p style="text-align:center; font-size:20px; margin:20px; font-family:Helvetica;"><img src="http://miki.startrekfederation.it/SigmaSys/logo/little_logo.png" /><br /><br />Sembra tu non abbia log da generare...<br /><br /><img src="http://miki.startrekfederation.it/SigmaSys/PNG/Kavanagh_001.png" /></p>
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

