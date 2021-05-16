<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}
 

function stringToColorCode($str) {
  $code = dechex(crc32($str)); 
  return  substr($code, 0, 6);
}

include('includes/app_include.php');
include('includes/validate_class.php');
mysql_query('SET NAMES utf8mb4');

		
		$string= str_replace("\xE2\x80\x8B", "", htmlentities(stf_real_escape(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));

		if (!is_numeric($_POST['chatTo'])) exit;
		else $chatTo = $_POST['chatTo'];
		
		$user = new PG($_SESSION['pgID']);
		if($user->pgAuthOMA == 'BAN') exit;
		
		$time = time();
	
		if(ltrim($string[0]) == '=' && PG::mapPermissions('MM',$user->pgAuthOMA))
		{
			$string[0] = '';
			$col = ($user->pgAuthOMA == 'A') ? 'auxAction' : 'auxActionMaster';
			$string = '<p class="'.$col.'">'.ucfirst(ltrim($string)).'</p>';
			mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",0,$time,'$string',0)");
			exit;
		}
		
		if ($string == 'VIDEO_FEDERATION_SPAM' && PG::mapPermissions('MM',$user->pgAuthOMA))
		{
			$string = "<p class=\"auxActionMaster\"><iframe width=\"550\" height=\"309\" src=\"//www.youtube.com/embed/84RRUr9pZRU\" frameborder=\"0\" allowfullscreen></iframe></p>";
			mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",'0',$time,'$string',0)");
			exit;
		}
			
		if ($string == 'CHAT_ZAP' && PG::mapPermissions('SM',$user->pgAuthOMA))
		{
			mysql_query('DELETE FROM fed_sussurri');
			$string = '<p class="auxAction">(TUTTE LE CHAT AZZERATE)</p>';
			mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",0,$time,'$string',0)");
			exit;
		}
		
		
		
		// else if(ltrim($string[0]) == '*' && PG::mapPermissions('A',$user->pgAuthOMA))
		// {
			// $string =  str_replace('*','',$string);
			// $string = '<p class="imageAction"><img src="'.$string.'" alt="'.$string.'"/></p>';
			// mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",0,$time,'$string',0)");
			// exit;
		// }
		
		
		$arrayIma = array('LOL','(bho)','(cry)','(bleah)','(uplook)','(love)','(sodout)','(edwards)');
		$aima = array('<img src="/TEMPLATES/img/interface/smileys/sm_lol.gif" alt="LOL" />','<img src="/TEMPLATES/img/interface/smileys/bho.gif" alt="bah" />','<img src="/TEMPLATES/img/interface/smileys/cry.gif" alt="CRY" />','<img src="/TEMPLATES/img/interface/smileys/puke.gif" alt="puke" />','<img src="/TEMPLATES/img/interface/smileys/uplook.gif" alt="bah" />','<img src="/TEMPLATES/img/interface/smileys/2heart.gif" alt="Love" />','<img src="/TEMPLATES/img/interface/smileys/soldout.gif" alt="sout" />','<img src="/TEMPLATES/img/interface/smileys/assimilaz.gif" alt="borg" />');
		$string = str_replace($arrayIma,$aima,$string);
		
			    
			    
		if($chatTo != 0 && $chatTo != 7)
		{
			
			$fromUser = stf_real_escape($user->pgUser);
			$toUser = stf_real_escape(PG::getSomething($chatTo,'username'));

			$Col=stringToColorCode($fromUser);

			$string = '<p class="susChat"><span class=\"susPrivate\">'.date('H:i',$time)."</span> <span class=\"susChatMPUserO\">$fromUser</span> <span style=\"color:#$Col\"; class=\"susChatMPSeparator\">--&gt;</span>  <span class=\"susChatMPUserO\">$toUser:</span> <span class=\"susPrivate\">$string</span></p>";
		
		}
		else 
		{		$fromUser = stf_real_escape($user->pgUser);

				if (!$user->png && PG::mapPermissions('A',$user->pgAuthOMA))
					$classer='susChatMPUserA'; 
				elseif (!$user->png && PG::mapPermissions('M',$user->pgAuthOMA))
					$classer='susChatMPUserM'; 
				elseif (!$user->png && PG::mapPermissions('G',$user->pgAuthOMA))
					$classer='susChatMPUserG'; 
				else
					$classer='susChatMPUser';

				$string = '<p class="susChat">'.date('H:i',$time)." <span class=\"$classer\" onclick=\"selectUser(\'$fromUser\');\">$fromUser:</span> $string</p>";
		}
		
		mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",'$chatTo',$time,'$string',0)");
		
		if(strpos(strtolower($string), 'telegram') !== false && PG::mapPermissions('G',$user->pgAuthOMA))
		{
		        $toUser = stf_real_escape(PG::getSomething($_SESSION['pgID'],'username'));
		    	$stringk = '<p class="susChat"><span class=\"susPrivate\">'.date('H:i',$time)."</span> <span class=\"susChatMPUserO\">Kavanagh</span> <span style=\"color:$Col\"; class=\"susChatMPSeparator\">--&gt;</span>  <span class=\"susChatMPUserO\">$toUser:</span> <span class=\"susPrivate\">L\'amministrazione ricorda allo staff che l\'utilizzo di strumenti esterni a STF per la gestione dei contatti con l\'utenza non è consigliata (ed è generalmente disincentivata).</span></p>";
		
		    mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES(394,'.$_SESSION['pgID'].",$time,'$stringk',0)"); 
		}
		     
		PG::updatePresence($_SESSION['pgID']);
		

?>						