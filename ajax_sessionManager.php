<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
		
		$ambientID= addslashes($_POST['amb']);
		
 		$user = new PG($_SESSION['pgID']); 
		$action = $_GET['action'];
		
		if ($action == 'check-ambient')
		{
			$sectionsOngoing = Ambient::getActiveSession($ambientID);
			if($sectionsOngoing) $sectionsOngoing['allAVG'] = round(Ambient::getActiveSessionAVG($ambientID));
			
			
			echo json_encode($sectionsOngoing);
		}
		
		if ($action == 'check-private')
		{
			$pvtOngoing = Ambient::getAmbientPrivate($ambientID); 
			echo json_encode($pvtOngoing);
		}
		
		/*if ($action == 'open-private'){
			if(!Ambient::getActiveSession($ambientID))
			{	echo "START";
				echo PG::mapPermissions('SM',$user->pgAuthOMA);
				if(PG::mapPermissions('SM',$user->pgAuthOMA) || $ambientID == PG::getSomething($user->ID,'pgAlloggioRealID'))			
					Ambient::openPrivate($ambientID,$user->ID,$_POST['lister']); 
			}
		}
		if ($action == 'close-private'){
			if(PG::mapPermissions('SM',$user->pgAuthOMA) || $ambientID == PG::getSomething($user->ID,'pgAlloggioRealID'))
			Ambient::closePrivate($ambientID);
			
		}*/

		if ($action == 'open-new')
		{
			Ambient::closeSession($ambientID); 
			$master = ((int)($_POST['master'] && PG::mapPermissions('M',$user->pgAuthOMA)))  ? 1 : 0; 
			$label = addslashes($_POST['label']);
			
			
			if($master)
			{
				$vali = new validator();
				$timer = $vali->numberOnly($_POST['maxturner']);

			}
			else $timer = 8;


			$listerOne = ($master && isSet($_POST['lister'])) ? $_POST['lister'] : '';
			$pvtIndex = ($listerOne != '') ? 1 : 0;
			Ambient::openSession($ambientID,$user->ID,$label,$master,$pvtIndex,$timer); 
			
			if($pvtIndex){
				Ambient::openPrivate($ambientID,$user->ID,$listerOne); 
			}
			
		}

		if ($action == 'close-active')
			
		{
			
			$sectionsOngoing = Ambient::getActiveSession($ambientID);
			$avig = Ambient::getActiveSessionAVG($ambientID);
			if($avig < 800) $avig = 800;
			$allacts = Ambient::getAllActions($ambientID);
			
			if ($sectionsOngoing['session']['sessionMaster'] && !PG::mapPermissions('JM',$user->pgAuthOMA)){ return;}
			 
			 
			$person = array(); 
			$ltime = 0;
			foreach($allacts as $var)
			{	  

				if ( (int)($sectionsOngoing['session']['sessionIntervalTime']) > 0 && ($ltime != 0 && $var['time'] > $ltime + ((int)($sectionsOngoing['session']['sessionIntervalTime'])*60))){
					
					echo "Action ".$var['IDE']."for person".$var['sender'].' was deleted T';

					$ltime = $var['time']; continue;
				}

				$ppl = (string)$var['sender'];
				
				if(!array_key_exists($ppl,$person))	$person[$ppl] = array();
				
				
				$ltime = $var['time'];

				if($var['type'] == 'DIRECT')
					$person[$ppl][] = $var['realLen'];
			}
			$pointarray=array();
			
			foreach($person as $playerID => $player)
			{	

				foreach ($player as $action)
				{
					

					if(!array_key_exists($playerID,$pointarray))	$pointarray[$playerID] = 0.0;
					if($action > 500)
					{
						if($action >= 500 && $action <= $avig*1.3)
						{
							$pointarray[$playerID] += $action / (($avig*1.3) - 500) - (500 / (($avig*1.3) - 500));
							
							//echo 'Action('.$playerID.',' . $action . ') : ' . ($action / (($avig*1.3) - 500) - (500 / (($avig*1.3) - 500))) . 'pt MS: '.($avig*1.3).';;;';
						}
						else if ($action > $avig*1.3) { $pointarray[$playerID]+=1; }
					}
				}
			} 

			if($sectionsOngoing['session']['sessionMaster']){
				
				$totalPta=0;	
				
				$owner = (string)($sectionsOngoing['session']['sessionOwner']);
				
				if(!array_key_exists($owner,$pointarray)){$pointarray[$owner] = 0.0;}
				
				foreach($pointarray as $personPoints){$totalPta += $personPoints;}
				$avgpointarray = (float)$totalPta / count($pointarray);
				
				if ((count($pointarray)-1) >= 3 && (count($pointarray)-1) <=5) $coeff = (count($pointarray)-1) * 10;
				elseif ((count($pointarray)-1) >= 6) $coeff = 50;
				else $coeff = 0;

				$totalPta = $avgpointarray + ($avgpointarray * ($coeff / 100));
				
				$avgpointarray_s = round($avgpointarray,2);
				$coeff_s = (string)(count($pointarray)-1).' (+'.$coeff." %)";
				$totalPta_s = round($totalPta,2);
				
				$tpl_s = round($pointarray[$owner],2);
				$pointarray[$owner] += $totalPta;
				
				$endpointarray = round($pointarray[$owner]);
				 
				
				$master_expl = addslashes("Per la sessione appena conclusa sono stati assegnati i seguenti punti per le attività di gioco e mastering:
				
				> Media dei punti assegnati in giocata: $avgpointarray_s FP / giocatore,
				> Giocatori coinvolti: $coeff_s
				> Punti per le azioni on-game: $tpl_s FP
				> Totale Bonus Mastering: $totalPta_s FP
				> ---------------------------------------
				> Totale FP assegnati: $endpointarray FP
				> (i punti sono arrotondati all'intero più vicino)"); 
				
				$sl = $sectionsOngoing['session']['sessionOwner']; 
				mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES (518,$sl,'RIEPILOGO PUNTI SESSIONE','<p style=\"font-size:13px; margin-left:15px; margin-right:15px;\">$master_expl</p><p style=\" color:#AAA; font-style:italic;\">Questo è un messaggio automatico.</p>',$curTime,0)");
				echo mysql_error(); 
			}
			
			
			foreach($pointarray as $playerID => $playerResult)
			{
				echo $playerID."--".$playerResult.'>>'.round($playerResult);
				$pta = round($playerResult);
				$sessionLabel = addslashes($sectionsOngoing['session']['sessionLabel']);
				if ($pta > -1){
					$pgg = new PG($playerID);  
					$pgg->addPoints($pta,'QS',"Log:".$sectionsOngoing['session']['sessionID'],"Punti per sessione di gioco $sessionLabel",$_SESSION['pgID']);
					 
					
					unset($pgg);	
				}
			}
			
			
			
			Ambient::closeSession($ambientID);
			Ambient::closePrivate($ambientID);
		 
		}
 

/*

Almeo 3 azioni per dare il bonus 
U(X) = (max_points / log_10(max_length - base_length)) * log_10 (X - (base_length-1)) se base_length < X < max_length 
0 altrimenti 
 
base_length = 450 
max_length = 1100 
max_points = 0.8 
 

*/
?>						