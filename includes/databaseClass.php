<?php class Database
{
	public static $db_Host; 
	public static $db_User; 
	public static $db_Pass; 
	public static $db_Name; 
	
	public static function tdbConnect()
	{
		mysql_connect(self::$db_Host, self::$db_User, self::$db_Pass);
		echo mysql_error();
		mysql_select_db(self::$db_Name);
	}
	
	public static function tdbClose()
	{
		mysql_close();
	}
	

}

class Ambient
{	
	public static function getAmbient($ambientID)
	{
		$res = mysql_query("SELECT locID,ambientType, locName,ambientLocation,ambientLevel_deck,descrizione,image,imageMap, locationable,ambientLight,ambientLightColor,ambientTemperature,ambientAudio FROM fed_ambient WHERE locID = '$ambientID'");
		
		$resa = mysql_fetch_array($res);
		return $resa;
	}
	
	public static function getAmbientName($ambientID)
	{
		$res = mysql_query("SELECT locName FROM fed_ambient WHERE locID = '$ambientID'");
		if(mysql_affected_rows()) 
		{$resa = mysql_fetch_array($res);
		return $resa['locName'];
		}
		else return false;
	}
	
	public static function getType($ambientID)
	{
		$res = mysql_query("SELECT ambientType FROM fed_ambient WHERE locID = '$ambientID'");
		if(mysql_affected_rows()) 
		{$resa = mysql_fetch_array($res);
		return $resa['ambientType'];
		}
		else return false;
	}
}


class PG
{
	public $ID;
	public $pgUser;
	public $pgNomeC;
	public $pgGrado;
	public $pgDataN;
	public $pgSezione;
	public $pgDipartimento;
	public $pgAuth;
	public $pgSpecie;
	public $pgSesso;
	public $pgIncarico;
	public $pgRoom;
	public $pgSeclar;
	public $pgAuthOMA;
	public $pgLastAct;
	public $pgNomeSuff;
	public $ONLINE;
	public $pgFixYear;
	public $pgMatricola;
	public $pgOffAvatar;
	public $audioEnable;
	public $audioEnvEnable;
	public $audioextEnable;
	public $customCSS;
	public function __construct($id,$adv=0)
	{
		$res = ($adv == 0) ? mysql_query("SELECT pgSesso,pgAssign,pgAvatar,pgFixYear,pgMatricola,pgMostrinaOlo,pgRoom,png,pgFirst,pgMostrina,pgLocation,pgNomeSuff,pgLock,pgStatoCiv,pgLastVisit,pgLastAct,pgUser,pgNomeC,pgDataN,pgLuoN,pgGrado,pgSezione,pgAuth,pgSeclar,pgIncarico,pgSpecie,pgAuthOMA,audioEnable,audioEnvEnable,pgDipartimento FROM pg_users WHERE pgID = $id") : mysql_query("SELECT pgSesso,pgAssign,pgAvatar,pgFixYear,pgMatricola,pgMostrinaOlo,pgRoom,png,pgFirst,pgMostrina,pgLocation,pgNomeSuff,pgLock,pgStatoCiv,pgLastVisit,pgLastAct,pgUser,pgNomeC,pgDataN,pgLuoN,pgGrado,pgSezione,pgAuth,pgSeclar,pgIncarico,pgSpecie,pgAuthOMA,audioEnable,audioEnvEnable,pgDipartimento, parlatCSS,actionCSS,otherCSS FROM pg_users WHERE pgID = $id");
		
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else return 0;
		
		$this->ID = $id;
		$this->pgUser = $re['pgUser'];
		$this->pgNomeC = $re['pgNomeC'];
		$this->pgDataN = $re['pgDataN'];
		$this->pgFixYear = $re['pgFixYear'];
		$this->pgLuoN  = $re['pgLuoN'];
		$this->pgGrado = $re['pgGrado'];
		$this->pgSezione = $re['pgSezione'];
		$this->pgDipartimento = $re['pgDipartimento'];
		$this->pgAuth = $re['pgAuth'];
		$this->pgSeclar = $re['pgSeclar'];
		$this->pgIncarico = $re['pgIncarico'];
		$this->pgAuthOMA = $re['pgAuthOMA'];
		$this->pgSpecie = $re['pgSpecie'];
		$this->pgSesso = $re['pgSesso'];
		$this->pgLastVisit = $re['pgLastVisit'];
		$this->pgLastAct = $re['pgLastAct'];
		$this->pgAssign = $re['pgAssign'];
		$this->pgAvatar = $re['pgAvatar'];
		$this->pgRoom = $re['pgRoom'];
		$this->pgStatoCiv = $re['pgStatoCiv'];
		$this->png = $re['png'];
		$this->pgFirst = $re['pgFirst'];
		$this->pgMatricola = $re['pgMatricola'];
		$this->audioEnable = $re['audioEnable'];
		$this->audioextEnable = ($re['audioEnable'] > 1) ? 1 : 0;
		$this->audioEnvEnable = ($re['audioEnvEnable']) ? 1 : 0;
		
		$this->ONLINE = ($this->pgLastAct < (time()-1800)) ? false : true;

		
		$this->pgMostrina = $re['pgMostrina'];
		$this->pgMostrinaOlo = $re['pgMostrinaOlo'];
		$this->pgLocation = $re['pgLocation'];
		$this->pgNomeSuff = $re['pgNomeSuff'];
		$this->pgLock = $re['pgLock'];
		
		if($adv == 1){
			if($re['actionCSS'] != '' && $re['parlatCSS'] != '')
			{
			$action = explode(';',$re['actionCSS']);
			$parlat = explode(';',$re['parlatCSS']);		
			$other = explode(';',$re['otherCSS']);		
			$actionSize = $action[0].'px';
			$actionColor = $action[1];
			$actionParlatColor = $action[2];
			
			$parlatSize = $parlat[0].'px';
			$parlatColor = $parlat[1];
			$parlatQuoteColor = $parlat[2];
			
			$nomePGSize = $other[0].'px';
			$masterSize = $other[1].'px';
			$commSize = $other[2].'px';
			$nomePGColor = $other[3];
			$commColor = $other[4];
			$commColorTex = $other[5];
			$tagSize = $other[6].'px';
			$tagColor = $other[7];
			
			$this->customCSS = ".chatAction{color:$actionColor; font-size:$actionSize;} .chatDirect{color:$parlatColor; font-size:$parlatSize;} .chatQuotation{color:$parlatQuoteColor;} .chatQuotationAction{color:$actionParlatColor} .chatUser{color:$nomePGColor; font-size:$nomePGSize} .masterAction, .globalAction,.offAction,.auxAction,.specificMasterAction,.oloMasterAction{font-size:$masterSize} .subspaceCom,.commMessage{font-size:$commSize; color:$commColorTex;} .subspaceComPre,.commPreamble{font-size:$commSize;color:$commColor;} .chatTag{font-size:$tagSize; color:$tagColor}";
			}
			else $this->customCSS = '';
		}
	}
	
	public function setPresenceInto($where)
	{
		mysql_query("UPDATE pg_users SET pgCoord = '10;10',pgRoom = '$where', pgLocation = '$where', pgLastAct = ".time()." WHERE pgID = ".$this->ID);
		$this->pgRoom = $where;
		$this->pgLocation = $where;
		
	}
	public function setPresenceIntoChat($where)
	{
		mysql_query("UPDATE pg_users SET pgRoom = '$where', pgLocation = (SELECT ambientLocation FROM fed_ambient WHERE locID = '$where'), pgLastAct = ".time()." WHERE pgID = ".$this->ID);
		$this->pgRoom = $where;
		$sor = mysql_query("(SELECT ambientLocation FROM fed_ambient WHERE locID = '$where')");
		$sorL = mysql_fetch_array($sor);
		$this->pgLocation = $sorL['ambientLocation'];
		
	}
	
	public static function updatePresence($id)
	{
	  mysql_query("UPDATE pg_users SET pgLastAct = ".time()." WHERE pgID = $id");
	}
	public static function getLocation($id)
	{
		$res = mysql_query("SELECT placeID,placeAlert, placeName, placeLogo, placeMap1, placeMap2, placeMap3, placeMapSupport1,placeMapSupport2,placeMapSupport3, catGDB, catDISP, catRAP, sector, placeType, warp, attracco, pointer,pointerL  FROM pg_places WHERE placeID = '$id'");
		
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else return 0;
		return $re;
	}
	
	public function getLocationOfUser()
	{
		$res = mysql_query("SELECT placeID,placeAlert, placeName, placeLogo, placeMap1, placeMap2, placeMap3, placeMapSupport1,placeMapSupport2,placeMapSupport3, catGDB, catDISP, catRAP, sector, placeType, warp, attracco, pointer,pointerL  FROM pg_places WHERE placeID = '".$this->pgLocation."'");
		
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else return 0;
		return $re;
	}
	
	public function getLimitrofi()
	{
		$targetPlace = $this->pgLocation; 
		
		$res = mysql_query("SELECT placeName,placeID,place_littleLogo1, placeMap1 FROM pg_places WHERE placeType <> 'Accademia' AND attracco = '' AND warp = 0 AND pointerL = (SELECT pointerL FROM pg_places WHERE placeID = '$targetPlace') ORDER BY placeType");
		
		$re = array();
		if(mysql_affected_rows()) while($rea = mysql_fetch_array($res)) $re[] = $rea; 
		else return 0;
		return $re;
	}
	
	public function hasBrevetto($arr)
	{
		$tr= '(';
		foreach ($arr as $elem)
			$tr .= $elem.', ';
		$tr = substr($tr,0,-2).')';
		mysql_query("SELECT 1 FROM pg_brevetti_assign WHERE owner = ".$this->ID." AND brev IN $tr");
		return mysql_affected_rows();
	}
	
	public function sendPadd($subject,$text,$from = '518')
	{
		$myID = $this->ID;
		$curTime = time();
		mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead) VALUES ($from,$myID,'$subject','<p style=\"font-size:13px; margin-left:15px; margin-right:15px;\">$text</p><p style=\" color:#AAA; font-style:italic;\">Questo è un messaggio automatico.</p>',$curTime,0)");
	}
	
	public function addPoints($p,$causa,$causaTitle,$reason,$assigner=518)
	{
		$me = $this->ID;
		$curTime = time();
		
		$causaTitle = addslashes($causaTitle);
		$reason = addslashes($reason);
		$causa = addslashes($causa);
		
		$pointsPre = $pointsPre = PG::getSomething($me,'totalPoints');
		mysql_query("UPDATE pg_users SET pgPoints = pgPoints+$p WHERE pgID = $me");
		mysql_query("INSERT INTO pg_users_pointStory(owner,points,cause,causeM,timer,assigner,causeE) VALUES ($me,$p,'$causa','$causaTitle',$curTime,$assigner,'$reason')"); 
		
		for($i = 0; $i < $p; $i++)
		{	if(($pointsPre+$i) % 200 == 0)
			{
				mysql_query("UPDATE pg_users SET pgUpgradePoints = pgUpgradePoints+2,pgSpecialistPoints=pgSpecialistPoints+1, pgSocialPoints = pgSocialPoints+1 WHERE pgID = $me"); 
				
				$cString = addslashes("Congratulazioni!!<br />Hai ottenuto 4 Upgrade Points!<br /><br /><p style='text-align:center'><span style='font-weight:bold'>Puoi usarli per aumentare le tue caratteristiche nella Scheda PG!</span></p><br />Il Team di Star Trek: Federation");
				$eString = addslashes("Upgrade Points!::Hai ottenuto quattro UP!"); 
				
				mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (518,$me,'OFF: Upgrade Points!','$cString',$curTime,0,''),(518,$me,'::special::achiev','$eString',$curTime,0,'TEMPLATES/img/interface/personnelInterface/starIcon.png')");
			}
			elseif(($pointsPre+$i) % 100 == 0)
			{
				mysql_query("UPDATE pg_users SET pgUpgradePoints = pgUpgradePoints+2, pgSocialPoints = pgSocialPoints+1 WHERE pgID = $me");
				
				$cString = addslashes("Congratulazioni!!<br />Hai ottenuto 3 Upgrade Points!<br /><br /><p style='text-align:center'><span style='font-weight:bold'>Puoi usarli per aumentare le tue caratteristiche nella Scheda PG!</span></p><br />Il Team di Star Trek: Federation");
				$eString = addslashes("Upgrade Points!::Hai ottenuto tre UP!"); 
				
				mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField) VALUES (518,$me,'OFF: Upgrade Points!','$cString',$curTime,0,''),(518,$me,'::special::achiev','$eString',$curTime,0,'TEMPLATES/img/interface/personnelInterface/starIcon.png')");
			}
		}
	}
	
	public static function getHangar($id)
	{
		$res = mysql_query("SELECT placeHangar FROM pg_places WHERE placeID = '$id'");
		
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else return 0;
		return $re['placeHangar'];
	}
	
	public static function getLocationName($id)
	{
		$res = mysql_query("SELECT placeName FROM pg_places WHERE placeID = '$id'");
		
		if(mysql_affected_rows()){
			$re = mysql_fetch_array($res); 
			return $re['placeName'];
		}
		else return 0;
		
	}
	
	 
	
	public static function setMostrina($pgID,$grado)
	{
		mysql_query("UPDATE pg_users SET rankCode = $grado, pgMostrina = (SELECT ordinaryUniform FROM pg_ranks WHERE prio = $grado), pgGrado = (SELECT Rgrado FROM pg_ranks WHERE prio = $grado), pgSezione = (SELECT Rsezione FROM pg_ranks WHERE prio = $grado), pgSeclar = (SELECT Rseclar FROM pg_ranks WHERE prio = $grado) WHERE pgID =$pgID");
	}
	
	public static function setMostrinaL($pgL,$grado)
	{
		mysql_query("UPDATE pg_users SET rankCode = $grado, pgMostrina = (SELECT ordinaryUniform FROM pg_ranks WHERE prio = $grado), pgGrado = (SELECT Rgrado FROM pg_ranks WHERE prio = $grado), pgSezione = (SELECT Rsezione FROM pg_ranks WHERE prio = $grado), pgSeclar = (SELECT Rseclar FROM pg_ranks WHERE prio = $grado) WHERE pgUser ='$pgL'");
		echo mysql_error();
	}
	
	public static function sendModerationL($pgL,$moder)
	{
		$string = '<p class="offAction" title="Moderazione">'."[Moderazione (questo avviso lo vedi solo tu)] ".ucfirst(ltrim($moder)).'</p>';
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES((SELECT pgID FROM pg_users WHERE pgUser = '$pgL'),(SELECT pgRoom FROM pg_users WHERE pgUser = '$pgL'),'$string',".time().",'SPECIFIC')");
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",(SELECT pgRoom FROM pg_users WHERE pgID = '".$_SESSION['pgID']."'),'$string',".time().",'SPECIFIC')");
	}

	
		public static function setIncarico($pgID,$testo)
	{
		mysql_query("UPDATE pg_users SET pgIncarico = '$testo' WHERE pgID ='$pgID'");
	}
	
	public static function getLocationAlert($id)
	{
		$res = mysql_query("SELECT placeAlert FROM pg_places WHERE placeID = '$id'");
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else return 0;
		return $re['placeAlert'];
	}
	
	
	public static function getOMA($id)
	{
		$res = mysql_query("SELECT pgAuthOMA FROM pg_users WHERE pgID = $id");
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else $re = NULL;
		return $re['pgAuthOMA'];
	}
	
	public static function isMasCapable($id)
	{
		$res = mysql_query("SELECT 1 FROM pg_users WHERE pgID = $id AND isMasCapable = 1");
		if(mysql_affected_rows())return true;
		else return false;
	}
	
	public static function getSomething($id,$var)
	{
		if($var == "BG")
		{
			$res = mysql_query("SELECT * FROM pg_users_bios WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re;
		}
		elseif($var == 'optionsRec')
		{
			$res = mysql_query("SELECT paddMail,email,actionCSS,parlatCSS,otherCSS FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res);  
			else $re = NULL;
			return $re;
		}
		
		elseif($var == 'prestavolto')
		{ 
			$res = mysql_query("SELECT TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(iscriDate), NOW()) as iscriDiff,iscriDate ,pgOffAvatarN,pgOffAvatarC FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res);  
			else $re = NULL;
			return $re;
		}
		
		elseif($var == 'totalPoints')
		{
			$res = mysql_query("SELECT pgPoints FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res);  
			else $re = NULL;
			return $re['pgPoints'];
		}
		elseif($var == 'upgradePoints')
		{
			$res = mysql_query("SELECT pgUpgradePoints,pgSocialPoints,pgSpecialistPoints FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res);  
			else $re = NULL; 
		return $re;
		
		}
		
		elseif($var == 'pgPoints')
		{
			$res = mysql_query("SELECT pgUser,points,cause,causeE,causeM,timer FROM pg_users_pointStory,pg_users WHERE pgID = assigner AND owner = $id ORDER BY timer DESC LIMIT 50");
			
			$re=array();
			if(mysql_affected_rows())
			{	
				while($ress=mysql_fetch_array($res)) 
				$re[] = $ress;  
			}
			else $re = NULL;
			
			return $re;
		}
		
		elseif($var == 'ordinaryRank')
		{
			$res = mysql_query("SELECT ordinaryUniform FROM pg_ranks WHERE prio = (SELECT rankCode FROM pg_users WHERE pgID = $id)");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re['ordinaryUniform'];
		}
		
		elseif($var == "DutiesAvailQuery")
		{
			$res = mysql_query("(SELECT duty_duties.dutyID FROM duty_duties, duty_sections_assigns, pg_users WHERE pgID = 1 AND pgDutyArea = dutySection AND duty_duties.dutyID = duty_sections_assigns.dutyID AND duty_duties.dutyID NOT IN (SELECT duty_duties.dutyID FROM duty_duties, duty_sections_except WHERE duty_sections_except.pgID = $id AND duty_duties.dutyID = duty_sections_except.dutyID AND definer = 'DENY'))
			UNION
			(SELECT duty_duties.dutyID FROM duty_duties, duty_sections_except WHERE duty_sections_except.pgID = 1 AND duty_duties.dutyID = duty_sections_except.dutyID AND definer = 'ALLOW')");
			
			
			$ini = 'SELECT * FROM duty_duties WHERE dutyID IN (';
			while($resa = mysql_fetch_array($res))
			{
				$ini.= $resa['dutyID'].',';
			}
			$ini = substr($ini,0,-1).')';
			
			return $ini;
		}
		
		else if($var == "statoSalute")
		{
			$res = mysql_query("SELECT pgSalute,pgMedica FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return array("salute" => $re['pgSalute'],"medica" => $re['pgMedica']);
		}
		
		else if($var == "room")
		{
			$res = mysql_query("SELECT pgRoom FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re['pgRoom'];
		}
		
		else if($var == "email")
		{
			$res = mysql_query("SELECT email FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re['email'];
		}
		
		else if($var == "username")
		{
			$res = mysql_query("SELECT pgUser FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re['pgUser'];
		}
		
		else if($var == "pgAlloggioID")
		{
			$res = mysql_query("SELECT locName FROM fed_ambient,pg_users WHERE locID = pgAlloggio AND pgID = $id");
			$resA=mysql_fetch_array($res);
			return $resA['locName'];
		}
		
		else if($var == "pgAlloggioRealID")
		{
			//echo "SELECT locID FROM fed_ambient,pg_users WHERE locID = pgAlloggio AND pgID = $id";exit;
			$res = mysql_query("SELECT alloggio FROM pg_alloggi WHERE pgID = $id AND defaulta=1");
			$resA=mysql_fetch_array($res);
			return $resA['alloggio'];
		}
		
		else if($var == "pgAlloggio")
		{
			$res = mysql_query("SELECT locID,locName,descrizione FROM fed_ambient WHERE locID = (SELECT alloggio FROM pg_alloggi WHERE pgID = $id AND defaulta = 1)");
			$resA=mysql_fetch_array($res);
			return $resA;
		}	
		
		else if($var == "pgUnit")
		{
			$res = mysql_query("SELECT placeName FROM pg_places,pg_users WHERE placeID = pgAssign AND pgID = ".$id);
			$resA=mysql_fetch_array($res);
			return $resA['placeName'];
		}
	}
	
	public static function isOnline($id)
	{
			$now = time();
			$res = mysql_query("SELECT lastAct, lastVisit FROM pg_users WHERE pgID = $id");
			$resA=mysql_fetch_array($res);
			if ($resA['pglastAct'] < time()-1800) return 0;
			else return 1;
	}
	
	public static function isMaster($id)
	{
		return (self::getOMA($id) == "M" || self::getOMA($id) == "SM");
	}
	
	public static function isAdmin($id)
	{
		return (self::getOMA($id) == "A");
	}
	
	public static function mapPermissions($requested,$actual)
	{	// O M SM A
		if($actual == 'BAN') return false;
		
		if ($actual == "A" || $requested == "N") return true;
		
		if ($actual == "SM" && $requested == "M") return true;
		if ($actual == "SM" && $requested == "MM") return true;
		
		if ($actual == "SM" && $requested == "JM") return true;
		if ($actual == "M" && $requested == "JM") return true;
		if ($actual == "MM" && $requested == "JM") return true;
		
		if ($actual == "SM" && $requested == "SL") return true;
		if ($actual == "MM" && $requested == "SL") return true;
		if ($actual == "M" && $requested == "SL") return true;
	
		if ($actual == "SM" && $requested == "O") return true;
		if ($actual == "MM" && $requested == "O") return true;
		if ($actual == "M" && $requested == "O") return true;
		if ($actual == "JM" && $requested == "O") return true;
	
		if ($actual == $requested) return true;
		
		return false;
	}
	
	public static function roleName($red)
	{	// O M SM A
		if($red == 'BAN') return "Utente Bannato";
		elseif ($red == "A") return "Amministratore";
		elseif ($red == "SM") return "Master Globale";
		elseif ($red == "MM") return "Moderatore";
		elseif ($red == "M") return "Master Ordinario";
		elseif ($red == "JM") return "Junior Master";
		elseif ($red == "O") return "Entertainer";
		elseif ($red == "N") return "Giocatore";
		return false;
	}
	
	public static function returnMapsStringFORDB($actual)
	{	// O M SM A
			
		if ($actual == "A") return "'A','MM','SM','M','SL','JM','N'";
		elseif ($actual == "SM") return "'SM','MM','SL','M','JM','N'";
		elseif ($actual == "MM") return "'MM','JM','SL','N'";
		elseif ($actual == "M") return "'M','JM','SL','N'";
		elseif ($actual == "JM") return "'JM','N'";
		elseif ($actual == "O") return "'O','N'";
		elseif ($actual == "N") return "'N'";
		
		return "()"; 
	}
	
}

class timeHandler
{
	public static function timestampToGiulian($time)
	{
		return date("d/m",$time)."/".(date("Y",$time)+368)." ".self::extrapolateHour($time);
	} 
	
	public static function extrapolateDay($time)
	{
	return date("d/m",$time)."/".(date("Y",$time)+368);
	}
	
	public static function extrapolateDayHour($time)
	{
	return date("d/m",$time)."/".(date("Y",$time)+368)." ".self::extrapolateHour($time,false);
	}
	
	public static function extrapolateHour($time,$secs=true)
	{
		return ($secs) ? date("H:i:s",$time) : date("H:i",$time);
	}
	
	public static function getOnline($var)
	{
		if($var == NULL)
		$my = mysql_query("SELECT COUNT(*) AS conto FROM pg_users WHERE pgLastAct >= ".(time()-1800));
		
		else $my = mysql_query("SELECT COUNT(*) AS conto FROM pg_users WHERE pgLocation = '$var' AND pgLastAct >= ".(time()-1800));
		
		$ra = mysql_fetch_array($my);
		return $ra['conto'];
	}
}

class Mailer
{
	public static function emergencyMailer($text,$refU){
	$string = "TimeStamp: ".date('d/m/Y ore H:i:s',time())."\n TENTATIVO FALLITO DI VIOLAZIONE\n\n".$text."\n\n"."REFERENZE: User:".$refU->pgUser." IP:".$_SERVER['REMOTE_ADDR'];
	mail("moreno@startrekfederation.it","[FED] Violazione di Sicurezza",$string,"From:emergency@startrekfederation.it");
	}
	
	public static function notificationMail($text,$refU){
	$string = "TimeStamp: ".date('d/m/Y ore H:i:s',time())."\n MODIFICA CRITICA ESEGUITA\n\n".$text."\n\n"."REFERENZE: User:".$refU->pgUser;
	mail("moreno@startrekfederation.it","[FED] Notifica",$string,"From:notify@startrekfederation.it");
	}
}
?>