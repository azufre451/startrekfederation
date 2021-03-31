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
			if($sectionsOngoing) $sectionsOngoing['allAVG'] = round(Ambient::getActiveSessionMedian($ambientID));
			
			
			echo json_encode($sectionsOngoing);
		}
		
		if ($action == 'check-private')
		{
			$pvtOngoing = Ambient::getAmbientPrivate($ambientID); 
			echo json_encode($pvtOngoing);
		}
		

		if ($action == 'open-new')
		{
			if($user->pgLock || $user->pgAuthOMA == 'BAN'){ echo json_encode(array('sta'=>'ok')); exit;}
			
			Ambient::closeSession($ambientID); 
			$master = ((int)($_POST['master'] && PG::mapPermissions('M',$user->pgAuthOMA)))  ? 1 : 0; 
			$label = addslashes($_POST['label']);
			
			$vali = new validator();
			
			//if($master)
			//{
			$timer = $vali->numberOnly($_POST['maxturner']);
			$charrer = (isSet($_POST['maxchar']) && (int)$_POST['maxchar'] >= 800) ? $vali->numberOnly($_POST['maxchar']) : 0;

			//}
			//else{
			//	//$timer = 8;
			//	$timer = $vali->numberOnly($_POST['maxturner']);
			//	$charrer=0;
			//}


			$listerOne = ($master && isSet($_POST['lister'])) ? $_POST['lister'] : '';
			$pvtIndex = ($listerOne != '') ? 1 : 0;
			Ambient::openSession($ambientID,$user->ID,$label,$master,$pvtIndex,$timer,$charrer); 
			
			if($pvtIndex){
				Ambient::openPrivate($ambientID,$user->ID,$listerOne); 
			}
			
		}

		if ($action == 'close-active')
			
		{
			if($user->pgLock || $user->pgAuthOMA == 'BAN'){ echo json_encode(array('sta'=>'ok')); exit;}
			$log="SESSION_ID\tACTION_ID\tUSER\tDATE\tTYPE\tMESSAGE\tV1\tV2\n";

			$sectionsOngoing = Ambient::getActiveSession($ambientID);
			//$avig = Ambient::getActiveSessionAVG($ambientID);
			
			$avig = Ambient::getActiveSessionMedian($ambientID);

   
			$offTargetACTS=0;
			$allACTS=0;
			if($avig < 600) $avig = 600;

			$allacts = Ambient::getAllActions($ambientID);
			
			if ($sectionsOngoing['session']['sessionMaster'] && !PG::mapPermissions('M',$user->pgAuthOMA)){ return;}
			 
			
			$person = array(); 
			$ltime = 0;
			$penalties = array();
			$off_offset=0;
			$playerLabels = array();
			
			foreach($allacts as $var)
			{	  
				if ($var['type'] == 'OFF' && !$off_offset)
				{
					continue;
				}
				if ($var['type'] == 'DIRECT')
					$allACTS+=1;

				$off_offset=1;

				//echo $var['IDE'] . ") last-time: ".$ltime . ' this time: '. $var['time'] . ' span: ' . (((int)($sectionsOngoing['session']['sessionIntervalTime'])*60)) . ' thr: ' . ($ltime + ((int)($sectionsOngoing['session']['sessionIntervalTime'])*60)) . '

				if (!array_key_exists($var['sender'], $playerLabels)){
						$playerLabels[$var['sender']] = PG::getSomething($var['sender'],'username');
					}



				if ( (int)($sectionsOngoing['session']['sessionIntervalTime']) > 0 && ($ltime != 0 && $var['time'] > $ltime + ((int)($sectionsOngoing['session']['sessionIntervalTime'])*60))){
					
					
					$offTargetACTS+=1;

					if (!array_key_exists($var['sender'], $penalties))
						$penalties[$var['sender']] = 0;


					
					$upperBound = $ltime + (int)($sectionsOngoing['session']['sessionIntervalTime'])*60;
					$scarto = $var['time'] - $upperBound;

					if ($scarto > 120)
						$penalPlay = 1;
					elseif ($scarto > 100)
						$penalPlay = 0.75;
					elseif ($scarto > 50)
						$penalPlay = 0.5;
					else
						$penalPlay = 0.25;
 


					$penalties[$var['sender']] += $penalPlay;
					$log.=$sectionsOngoing['session']['sessionID'] . "\t".$var['IDE']."\t".$playerLabels[$var['sender']]."\t".date('H:i:s',$var['time'])."\t". $var['type'] . "\tOVERTIME\t".$scarto . "\t".$penalPlay."\n";
 

					
					

					$ltime = $var['time'];
				}

				$ppl = (string)$var['sender'];
				
				if(!array_key_exists($ppl,$person))	$person[$ppl] = array();
				
				$ltime = $var['time'];

				if($var['type'] == 'DIRECT' || $var['type'] == 'OFF') 
				{
					//echo "Action ".$var['IDE']."for person".$var['sender'].' will be counted '.$var['realLen'];
					$log.=$sectionsOngoing['session']['sessionID'] . "\t".$var['IDE']."\t".$playerLabels[$var['sender']]."\t".date('H:i:s',$var['time'])."\t". $var['type'] . "\tCOUNT\t".$var['realLen'] . "\t\n";

					$person[$ppl][] = $var['realLen'];
				}
			}
			$pointarray=array();


		
			foreach($person as $playerID => $player)
			{	

				

				foreach ($player as $action)
				{
					if(!array_key_exists($playerID,$pointarray))	$pointarray[$playerID] = 0.0;
					if($action > 400)
					{
						if($action >= 400 && $action <= $avig*1.2)
						{
							$pointarray[$playerID] += $action / (($avig*1.2) - 400) - (400 / (($avig*1.2) - 400));
						}
						else if ($action > $avig*1.2) { $pointarray[$playerID]+=1; }
					}
				}
			} 
 

			if($sectionsOngoing['session']['sessionMaster']){
				
				$totalPta=0;	
				
				$owner = (string)($sectionsOngoing['session']['sessionOwner']);
				
				
				foreach($pointarray as $personPoints){
					//$playPenalty = array_key_exists($playerID,$penalties) ? $penalties[$playerID] : 0;
					$totalPta += $personPoints;
				}

				$tpl_s = round($pointarray[$owner],2);

				if ( $tpl_s > 0 && $tpl_s < 3 ) $rte = 1;
				else $rte=0;

				$avgpointarray = (float)$totalPta / (count($pointarray)-$rte);

				if(!array_key_exists($owner,$pointarray)){$pointarray[$owner] = 0.0;}

				if ((count($pointarray)-1) >= 3 && (count($pointarray)-1) <=5) $coeff = (count($pointarray)-1) * 10;
				elseif ((count($pointarray)-1) >= 6) $coeff = 50;
				else $coeff = 0;

				$totalPta = $avgpointarray + ($avgpointarray * ($coeff / 100));
				
				$avgpointarray_s = round($avgpointarray,2);
				$coeff_s = (string)(count($pointarray)-1).' (+'.$coeff." %)";
				$totalPta_s = round($totalPta,2);
				
				$pointarray[$owner] += $totalPta;
				
				
				$sl = $sectionsOngoing['session']['sessionOwner']; 

				//$organizingUser = PG($sl);
				
				$masterPenalty = array_key_exists($sl,$penalties) ? round($penalties[$sl],2) : 0;
				$endpointarray = round($pointarray[$owner] - $masterPenalty);
				
				$master_expl = addslashes("Per la sessione appena conclusa sono stati assegnati i seguenti punti per le attività di gioco e mastering:
				
				> Media dei punti assegnati in giocata (senza penalità): $avgpointarray_s FP / giocatore,
				> Numero di azioni fuori-tempo: $offTargetACTS,
				> Giocatori coinvolti: $coeff_s
				> Punti per le azioni on-game: $tpl_s FP
				> Bonus Mastering: $totalPta_s FP
				> Penalità maturata     : -$masterPenalty FP
				> ---------------------------------------
				> Totale FP assegnati: $endpointarray FP
				> (i punti sono arrotondati all'intero più vicino)"); 
				
				mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES (518,$sl,'RIEPILOGO PUNTI SESSIONE','<p style=\"font-size:13px; margin-left:15px; margin-right:15px;\">$master_expl</p><p style=\" color:#AAA; font-style:italic;\">Questo è un messaggio automatico.</p>',$curTime,0)");
				mysql_query("INSERT INTO federation_sessions_participation (pgID,sessionID,kind) VALUES(".$sectionsOngoing['session']['sessionOwner'].",".$sectionsOngoing['session']['sessionID'].",'MASTER')");
				echo mysql_error();
			}
			   
			foreach($pointarray as $playerID => $playerResult)
			{
				$playPenalty = array_key_exists($playerID,$penalties) ? $penalties[$playerID] : 0;
 

				$pta =  max(0,round($playerResult - $playPenalty));
				$sessionLabel = addslashes($sectionsOngoing['session']['sessionLabel']);
				$log.=$sectionsOngoing['session']['sessionID'] . "\t\t".$playerLabels[$playerID]."\t\t\t\tPOINTS_TOTAL\t".round($playerResult,2). " - ".round($playPenalty,2)."\t".$pta."\t\n";

				if ($pta > -1){
					$pgg = new PG($playerID); 
					if ($pta == 0 && $allACTS >= 4 && count($person[$playerID]) >= 4)
					{
						$pta=1;
						$log.=$sectionsOngoing['session']['sessionID'] . "\t\t".$playerLabels[$playerID]."\t\t\t\tPOINTS_TOTAL_ADJ\t+1\t - ".round($playPenalty,2)."\t".$pta."\t\n";
					}
					 
					$pgg->addPoints($pta,'QS',"Log:".$sectionsOngoing['session']['sessionID'],"Punti per sessione di gioco $sessionLabel",$_SESSION['pgID']);
					mysql_query("INSERT INTO federation_sessions_participation (pgID,sessionID,kind) VALUES($playerID,".$sectionsOngoing['session']['sessionID'].",'PLAY')");
					
					unset($pgg);	
				}
			}
			

			$fp = fopen('../stf-data/sessions_logs.txt', 'a');

	
			fwrite($fp, $log);
			fclose($fp);

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