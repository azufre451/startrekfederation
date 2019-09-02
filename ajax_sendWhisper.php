<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

function stringToColorCode($str) {

	$arr=array("#e6194b","#3cb44b","#ffe119","#0082c8","#f58231","#911eb4","#46f0f0","#f032e6","#d2f53c","#fabebe","#008080","#e6beff","#aa6e28","#fffac8","#800000","#aaffc3","#808000","#ffd8b1","#000080","#808080","#000000");

  $code = md5($str);
  $cnt=0;
  echo $code;
  echo "<br />";
  for($i = 0; $i < count($code); $i++)
    {
    	echo $code[$i] + ' ' + ord($code[$i]);
        $cnt += ord($code[$i]);
    }
  echo $cnt % count($arr);

  return $arr[($cnt+1) % count($arr)];
}



include('includes/app_include.php');
include('includes/validate_class.php');

		
		$string= str_replace("\xE2\x80\x8B", "", htmlentities(addslashes(($_POST['chatLine'])),ENT_COMPAT, 'UTF-8'));

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
			
			$fromUser = addslashes($user->pgUser);
			$toUser = addslashes(PG::getSomething($chatTo,'username'));

			$Col=stringToColorCode($fromUser);

			$string = '<p class="susChat"><span class=\"susPrivate\">'.date('H:i',$time)."</span> <span class=\"susChatMPUserO\">$fromUser</span> <span style=\"color:$Col\"; class=\"susChatMPSeparator\">--&gt;</span>  <span class=\"susChatMPUserO\">$toUser:</span> <span class=\"susPrivate\">$string</span></p>";
		
		}
		else 
		{		$fromUser = addslashes($user->pgUser);

				$classer = (PG::mapPermissions('A',$user->pgAuthOMA)) ? 'susChatMPUserA' : ((PG::mapPermissions('M',$user->pgAuthOMA)) ? 'susChatMPUserM' : ((PG::mapPermissions('G',$user->pgAuthOMA)) ? 'susChatMPUserG' : 'susChatMPUser'));
				$string = '<p class="susChat">'.date('H:i',$time)." <span class=\"$classer\" onclick=\"selectUser(\'$fromUser\');\">$fromUser:</span> $string</p>";
		}
		
		mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES('.$_SESSION['pgID'].",'$chatTo',$time,'$string',0)");
		
		if(strpos(strtolower($string), 'telegram') !== false && PG::mapPermissions('G',$user->pgAuthOMA))
		{
		        $toUser = addslashes(PG::getSomething($_SESSION['pgID'],'username'));
		    	$stringk = '<p class="susChat"><span class=\"susPrivate\">'.date('H:i',$time)."</span> <span class=\"susChatMPUserO\">Kavanagh</span> <span style=\"color:$Col\"; class=\"susChatMPSeparator\">--&gt;</span>  <span class=\"susChatMPUserO\">$toUser:</span> <span class=\"susPrivate\">L\'amministrazione ricorda allo staff che l\'utilizzo di strumenti esterni a STF per la gestione dei contatti con l\'utenza non è consigliata (ed è generalmente disincentivata).</span></p>";
		
		    mysql_query('INSERT INTO fed_sussurri (susFrom,susTo,time,chat,reade) VALUES(394,'.$_SESSION['pgID'].",$time,'$stringk',0)"); 
		}
		   


		     
		PG::updatePresence($_SESSION['pgID']);
		

?>						