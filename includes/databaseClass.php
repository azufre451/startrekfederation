<?php 
function mysql_query($q){return Database::query($q);}
function mysql_fetch_array($q){return mysqli_fetch_array($q);}
function mysql_fetch_assoc($q){return mysqli_fetch_assoc($q);}
function mysql_error(){return mysqli_error(Database::$link);}
function mysql_affected_rows(){return mysqli_affected_rows(Database::$link);}

class Database
{ 
	public static $link;

	public static function tdbConnect($db_Host,$db_User, $db_Pass,$db_Name)
	{
		self::$link=mysqli_connect($db_Host,$db_User, $db_Pass,$db_Name);
	}
	
	public static function tdbClose()
	{
		mysqli_close(self::$link);
	}

	public static function query($query)
	{ 
		$QR = mysqli_query(self::$link,$query);
		if(mysqli_error(self::$link))
			echo mysqli_error(self::$link);
		return $QR;
	}
}

class Ambient 
{	
	public static function getAmbient($ambientID)
	{
		$res = mysql_query("SELECT locID,ambientType, locName,ambientLocation,ambientLevel_deck,descrizione,image,icon,imageMap, locationable,ambientLight,ambientLightColor,ambientTemperature,ambientAudio,chatPwd,planetSub, IF(sessionStatus = 'ONGOING', sessionOwner, NULL) as sessionOwner FROM fed_ambient LEFT JOIN federation_sessions ON sessionPlace = locID WHERE locID = '$ambientID' ORDER BY sessionStart DESC LIMIT 1");


		$resa = mysql_fetch_array($res);
		return $resa;
	}
	
	public static function getAmbientPrivate($ambientID)
	{
		$res = mysql_query("SELECT pg_users.pgID,pgUser,chatPwd,ordinaryUniform FROM fed_ambient,fed_ambient_auth,pg_users,pg_ranks WHERE fed_ambient.locID = fed_ambient_auth.locID AND pg_users.pgID = fed_ambient_auth.pgID AND prio=rankCode AND fed_ambient.locID = '$ambientID' AND chatPwd > 0"); 
		
		if (!mysql_affected_rows()) return 0;
		
		$ara = array();
		while($resa = mysql_fetch_assoc($res)){
			$ara[] = array('id' =>$resa['pgID'],'user' =>$resa['pgUser'],  'rankimage' => $resa['ordinaryUniform'], 'owner' => ($resa['chatPwd'] == $resa['pgID'])?1:0);
		}
		return $ara;
	}
	 
	public static function getLastSessions($limit,$filter='UL')
	{

		if ($filter == 'UL')
			$uQ = "SELECT federation_sessions.*,pg_users.pgUser,pg_users.pgID,ordinaryUniform,locName,placeLogo FROM federation_sessions,pg_users,fed_ambient,pg_ranks,pg_places WHERE placeID = ambientLocation AND rankCode = prio AND pgID = sessionOwner AND sessionPlace = locID AND sessionStatus = 'CLOSED' AND sessionPrivate = 0 ORDER BY sessionStatus, sessionID DESC LIMIT $limit";
		else
			$uQ = "SELECT federation_sessions.*,pg_users.pgUser,pg_users.pgID,locName FROM federation_sessions,pg_users,fed_ambient WHERE pgID = sessionOwner AND sessionPlace = locID ORDER BY sessionID DESC LIMIT $limit";

		$rea = mysql_query($uQ);

		$sessions=array();
		while($rel = mysql_fetch_assoc($rea)){
			$dater = date('d-m-y',$rel['sessionStart']);
			$sessions[$dater][] = $rel;
		}

		return $sessions;
	}
	
	public static function getActiveSession($ambientID){
		$res = mysql_query("SELECT federation_sessions.*, pgUser,pg_users.pgID as openerID FROM federation_sessions,pg_users WHERE pgID = sessionOwner AND sessionPlace = '$ambientID' AND sessionStatus = 'ONGOING'");
		if(mysql_affected_rows())
		{
			$resa = mysql_fetch_array($res);  
			$sessionInfo= array('sessionID' => $resa['sessionID'],'sessionLabel' => $resa['sessionLabel'],'pgUser' => $resa['pgUser'],'openerID'=>$resa['openerID'],'sessionMaster' => $resa['sessionMaster'],'sessionStart' => strftime('%e %B %H:%M',$resa['sessionStart']),'sessionLength' => (int)((time()-$resa['sessionStart'])/60), 'sessionOwner' => $resa['sessionOwner'],'sessionIntervalTime' => $resa['sessionIntervalTime']);
			
			$iniTime=$resa['sessionStart']; $imaTime=time(); 
			
			$resTime=mysql_query("SELECT pgID,pgUser,ordinaryUniform,pgSpecie,pgSesso,COUNT(realLen),SUM(realLen),AVG(realLen) AS averageLen FROM federation_chat,pg_users,pg_ranks WHERE rankCode = prio AND sender = pgID AND ambient = '$ambientID' AND time BETWEEN $iniTime AND $imaTime AND type = 'DIRECT' GROUP BY pgUser,ordinaryUniform,pgSpecie,pgSesso ORDER BY  averageLen DESC "); 
			$resPPL = array();
			while($resTimeL=mysql_fetch_assoc($resTime)){ 
				$resPPL[$resTimeL['pgUser']] = $resTimeL;
			}
			
			return array('session'=>$sessionInfo,'people'=>$resPPL);
		}
		else return 0; 
	}	
	
	public static function getActiveSessionAVG($ambientID){
		$res = mysql_query("SELECT federation_sessions.*, pgUser FROM federation_sessions,pg_users WHERE pgID = sessionOwner AND sessionPlace = '$ambientID' AND sessionStatus = 'ONGOING'");
		if(mysql_affected_rows())
		{ 
			$resa = mysql_fetch_array($res); 
			
			$iniTime=$resa['sessionStart']; $imaTime=time(); 
			
			$resAVG=mysql_query("SELECT AVG(realLen) AS averageLen FROM federation_chat WHERE ambient = '$ambientID' AND time BETWEEN $iniTime AND $imaTime AND type = 'DIRECT'"); 
			$resAVGL=mysql_fetch_assoc($resAVG); 
			
			return $resAVGL['averageLen'];
		}
		else return 0; 
	}

	public static function getActiveSessionMedian($ambientID){
		$res = mysql_query("SELECT federation_sessions.*, pgUser FROM federation_sessions,pg_users WHERE pgID = sessionOwner AND sessionPlace = '$ambientID' AND sessionStatus = 'ONGOING'");
		if(mysql_affected_rows())
		{ 
			$resa = mysql_fetch_array($res); 
			
			$iniTime=$resa['sessionStart']; $imaTime=time(); 
			
			$resAVG=mysql_query("SELECT realLen FROM federation_chat WHERE ambient = '$ambientID' AND time BETWEEN $iniTime AND $imaTime AND type = 'DIRECT'"); 
			$utpl=array();
			while($resAVGL=mysql_fetch_assoc($resAVG))
			{
				$utpl[] = $resAVGL['realLen']; 
			}			
			

			sort($utpl);
			$count = count($utpl);
			$median=0;
			if ($count > 0)
				$median = ($count%2) ? $utpl[($count-1)/2] : ($utpl[$count/2-1]+$utpl[($count/2)])/2;
			
			return $median;
		}
		else return 0; 
	}

	
	public static function getAllActions($ambientID){
		$res = mysql_query("SELECT federation_sessions.*, pgUser FROM federation_sessions,pg_users WHERE pgID = sessionOwner AND sessionPlace = '$ambientID' AND sessionStatus = 'ONGOING'");
		if(mysql_affected_rows())
		{  
			$resa = mysql_fetch_array($res); 
			
			$iniTime=$resa['sessionStart']; $imaTime=time(); 
			
			$resAct=mysql_query("SELECT realLen,IDE, sender,type,time FROM federation_chat WHERE ambient = '$ambientID' AND time BETWEEN $iniTime AND $imaTime AND type IN('DIRECT','MASTER','OFF') ORDER BY time"); 
			$resActions = array();
			
			while($resActL=mysql_fetch_assoc($resAct)){
				$resActions[] = $resActL;
			}
			return $resActions;
		}
		else return 0; 
	}
	
	
	
	public static function openPrivate($ambientID,$owner,$lister){
		
		$kilo=false;
		$splitted = explode(',',(trim($lister)));
		foreach ($splitted as $elemet)
		{	
			$kilo = true; 
			$to = addslashes(trim($elemet)); 
			if ($to==NULL) continue;
			$idR = mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$to'"));
			$idA = $idR['pgID'];
			
			if(mysql_affected_rows()){mysql_query("INSERT INTO fed_ambient_auth(pgID,locID) VALUES($idA,'$ambientID')");}
			
		}
		if ($kilo){
			mysql_query("UPDATE fed_ambient SET chatPwd = $owner WHERE locID = '$ambientID'");  
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(518,'$ambientID','<script>location.reload();</script>',".time().",'SERVICE')");
		}
		else{
			mysql_query("DELETE FROM fed_ambient_auth WHERE locID = '$ambientID'");
		}


		
		
	}
	public static function closePrivate($ambientID)
	{
		mysql_query("DELETE FROM fed_ambient_auth WHERE locID = '$ambientID'");
		mysql_query("UPDATE fed_ambient SET chatPwd = 0 WHERE locID = '$ambientID'");  
		
	}
	
	public static function openSession($ambientID,$owner,$label,$master,$private=0,$timer=8,$maxchar=0,$descript=''){
		$curTime = time(); 
		$col= ($master) ? 'masterAction' : 'auxAction';
		$timerParticle = ($master) ? 'm' : '';

		$sessionTimerA = "<img style=\"vertical-align:middle; width:25px;\" title=\"Dovrai azionare entro questo tempo massimo, altrimenti non ti saranno assegnati gli FP\" src=\"TEMPLATES/img/interface/sessions/timing_".$timer.$timerParticle.".jpg\" />";

		$sessionCharrerA = ($maxchar != 0) ? "<img style=\"vertical-align:middle; width:25px; margin-left:5px;\" title=\"Limite caratteri: $maxchar\" src=\"TEMPLATES/img/interface/sessions/chr_limit.jpg\" /> " : ''; 

		$res = mysql_query("INSERT INTO federation_sessions(sessionPlace,sessionStart,sessionStatus,sessionOwner,sessionLabel,sessionDescript,sessionMaster,sessionPrivate,	sessionIntervalTime,sessionMaxChars) VALUES ('$ambientID',$curTime,'ONGOING',$owner,'$label','$descript',$master,$private,$timer,$maxchar)"); 
		//echo "INSERT INTO federation_sessions(sessionPlace,sessionStart,sessionStatus,sessionOwner,sessionLabel,sessionMaster,sessionPrivate,	//sessionIntervalTime,sessionMaxChars) VALUES ('$ambientID',$curTime,'ONGOING',$owner,'$label',$master,$private,$timer,$maxchar)";

		if(mysql_affected_rows()){ 

			self::resetOloRankOf($ambientID);
			
			$string = '<div style="position:relative;" class="'.$col.'"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta del tool sessioni" /> Sessione Avviata </div> &Egrave; stata avviata una nuova sessione: '.$label . ' | ' .  $sessionTimerA.$sessionCharrerA.'</div>';   
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientID','$string',".time().",'OFF')");
			
			return 1;
		}
		else return 0;
	}


	public static function resetOloRankOf($ambientID){
		$at= mysql_fetch_assoc(mysql_query("SELECT ambientType FROM fed_ambient WHERE locID = '$ambientID'"))['ambientType'];

		if ($at == 'SALA_OLO')
			mysql_query("UPDATE pg_users SET pgMostrinaOlo = '' WHERE pgRoom = '$ambientID'");
	}
	
	public static function closeSession($ambientID){
		$curTime = time();
		$res = mysql_query("UPDATE federation_sessions SET sessionEnd = $curTime, sessionStatus = 'CLOSED' WHERE sessionPlace = '$ambientID' AND sessionStatus = 'ONGOING'"); 
		if(mysql_affected_rows()){
			
			self::resetOloRankOf($ambientID);

			$string = '<div style="position:relative;" class="auxAction"><div class="blackOpacity"><img src="TEMPLATES/img/interface/personnelInterface/info.png" title="Azione automatica di risposta del tool sessioni" /> Sessione Conclusa</div>&Egrave; stata chiusa la sessione attiva.</div>';   
			mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",'$ambientID','$string',".time().",'OFF')");
			return 1;
		}
		else return 0;
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
	public $pgBavo;
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
		
		$res = ($adv == 0) ? mysql_query("SELECT pgSesso,pgAssign,pgAvatar,pgAvatarSquare,pgFixYear,pgMatricola,pgMostrinaOlo,pgRoom,png,pgFirst,pgMostrina,pgLocation,pgNomeSuff,pgLock,pgStatoCiv,pgLastVisit,pgLastAct,pgUser,pgNomeC,pgDataN,pgLuoN,pgGrado,pgSezione,pgAuth,pgSeclar,pgSpecie,pgAuthOMA,audioEnable,audioEnvEnable,pgBavo FROM pg_users WHERE pgID = $id") : mysql_query("SELECT pgSesso,pgAssign,pgAvatar,pgAvatarSquare,pgFixYear,pgMatricola,pgMostrinaOlo,pgRoom,png,pgFirst,pgMostrina,pgLocation,pgNomeSuff,pgLock,pgStatoCiv,pgLastVisit,pgLastAct,pgUser,pgNomeC,pgDataN,pgLuoN,pgGrado,pgSezione,pgAuth,pgSeclar,pgSpecie,pgAuthOMA,audioEnable,audioEnvEnable,pgBavo, parlatCSS,otherCSS,paddMail,email FROM pg_users WHERE pgID = $id");
		

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
		$this->pgAuth = $re['pgAuth'];
		$this->pgSeclar = $re['pgSeclar'];
		$this->pgAuthOMA = $re['pgAuthOMA'];
		$this->pgSpecie = $re['pgSpecie'];
		$this->pgSesso = $re['pgSesso'];
		$this->pgLastVisit = $re['pgLastVisit'];
		$this->pgLastAct = $re['pgLastAct'];
		$this->pgAssign = $re['pgAssign'];
		$this->pgAvatar = $re['pgAvatar'];
		$this->pgAvatarSquare = $re['pgAvatarSquare'];
		$this->pgRoom = $re['pgRoom'];
		$this->pgStatoCiv = $re['pgStatoCiv'];
		$this->png = $re['png'];
		$this->pgFirst = $re['pgFirst'];
		$this->pgMatricola = $re['pgMatricola'];
		$this->audioEnable = $re['audioEnable'];
		$this->audioextEnable = ($re['audioEnable'] > 1) ? 1 : 0;
		$this->audioEnvEnable = ($re['audioEnvEnable']) ? 1 : 0;
		$this->pgBavo = ($re['pgBavo']) ? 1 : 0;
		
		$this->ONLINE = ($this->pgLastAct < (time()-1800)) ? false : true;

		$this->pgMostrina = $re['pgMostrina'];
		$this->pgMostrinaOlo = $re['pgMostrinaOlo'];
		$this->pgLocation = $re['pgLocation'];
		$this->pgNomeSuff = $re['pgNomeSuff'];
		$this->pgLock = $re['pgLock'];
		
		if ($adv == 2){$this->paddMail = ($re['paddMail'] > 0) ? 1 : 0; $this->email = $re['email'];}
		

		if($adv == 1){
			if($re['parlatCSS'] != '')
			{
			//$action = explode(';',$re['actionCSS']);
			$parlat = explode(';',$re['parlatCSS']);


			$other = explode(';',$re['otherCSS']);		
		

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
			$masterColor = $other[8];
			$masterBorderColor = $other[9];
			$masterSpecColor = $other[10];
			$masterSpecBorderColor = $other[11];
			$masterColorQuoter = $other[12];
			$masterSpecColorQuoter = $other[13];
			
			$this->customCSS = ".chatDirect{color:$parlatColor; font-size:$parlatSize;} .chatQuotation{color:$parlatQuoteColor;} .chatUser{color:$nomePGColor; font-size:$nomePGSize} .masterAction, .globalAction,.offAction,.auxAction,.specificMasterAction,.oloMasterAction{font-size:$masterSize} .subspaceCom,.commMessage{font-size:$commSize; color:$commColorTex;} .subspaceComPre,.commPreamble{font-size:$commSize;color:$commColor;} .chatTag{font-size:$tagSize; color:$tagColor} .masterAction {color:$masterColor; border-color:$masterBorderColor;} .specificMasterAction {color:$masterSpecColor; border-color:$masterSpecBorderColor;} .masterAction .chatQuotation{color:$masterColorQuoter;} .specificMasterAction .chatQuotation{color:$masterSpecColorQuoter;}";
			
			}
			else $this->customCSS = '';

		}
	}
	
	public function getIncarichi(){
		$inca=array();
		$myID = $this->ID;
		$myAssign = $this->pgAssign;


		$rek=mysql_query("SELECT recID,incDipartimento,incDivisione,incSezione,incIncarico,incGroup,incMain,incActive,incHigh,pgPlace,placeName FROM pg_incarichi,pg_places WHERE placeID = pgPlace AND pgID = $myID ORDER BY incMain DESC");
		while($rekA = mysql_fetch_assoc($rek))
			$inca[] = $rekA;

		$incaF = array();
		for($k=0; $k < count($inca); $k++)
		{
			if($inca[$k]['incActive'])
				$incaF[]=$inca[$k];
		}

		if(count($incaF) >= 1)
		{
			$this->pgIncarico = $incaF[0]['incIncarico'];
			$this->pgDipartimento = $incaF[0]['incDipartimento'];
			$this->pgAssign = $incaF[0]['pgPlace'];
			$this->pgIncarichi = $inca;
			
			if(count($incaF) > 1){
				$this->metapgIncarico = 'Altri incarichi:<hr/><ul>';
				for($k=1; $k < count($incaF); $k++)
					$this->metapgIncarico .= '<li>'.$incaF[$k]['incIncarico'].' - '.$incaF[$k]['placeName'].'</li>';
				$this->metapgIncarico .= '</ul>';
			}
		}
		else{
			$this->pgIncarico = '';
			$this->pgDipartimento = '';
			$this->pgAssign = '';
			$this->pgIncarichi = array();
		}
	}

	public function setPresenceInto($where)
	{
		mysql_query("UPDATE pg_users SET pgCoord = '10;10',pgRoom = '$where', pgLocation = '$where', pgLastAct = ".time().",pgLastURI = '".$_SERVER['REQUEST_URI']."' WHERE pgID = ".$this->ID);
		$this->pgRoom = $where;
		$this->pgLocation = $where;
		
	}
	public function setPresenceIntoChat($where)
	{
		mysql_query("UPDATE pg_users SET pgRoom = '$where', pgLocation = (SELECT ambientLocation FROM fed_ambient WHERE locID = '$where'), pgLastAct = ".time().", pgLastURI = '".$_SERVER['REQUEST_URI']."' WHERE pgID = ".$this->ID);
		$this->pgRoom = $where;
		$sor = mysql_query("(SELECT ambientLocation FROM fed_ambient WHERE locID = '$where')");
		$sorL = mysql_fetch_array($sor);
		$this->pgLocation = $sorL['ambientLocation'];
	}
	
	public static function updatePresence($id)
	{
	  mysql_query("UPDATE pg_users SET pgLastAct = ".time().", pgLastURI = '".$_SERVER['REQUEST_URI']."' WHERE pgID = $id");
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
		
		return $re;
	}
	
	public function hasBrevetto($arr)
	{
		$tr= '(';
		foreach ($arr as $elem)
			$tr .= $elem.', ';
		$tr = substr($tr,0,-2).')';
		mysql_query("SELECT 1 FROM pg_service_stories WHERE type = 'EXAM' AND owner = ".$this->ID." AND brevlink IN $tr");
		return mysql_affected_rows();
	}
	public function sendNotification($text,$subtext,$from = '518',$image='',$linker='',$uri='')
	{
		$myID = $this->ID;
		$curTime = time();

		$textE=addslashes($text);
		$textS=addslashes($subtext);
		mysql_query("INSERT INTO pg_personal_notifications (owner,text,image,time,linker,URI,subtext) VALUES ($myID,'$textE','$image',$curTime,'$linker','$uri','$textS')");
 
	}


	public function sendPadd($subject,$text,$from = '518',$type=0,$forceMail=0)
	{
		$myID = $this->ID;
		$curTime = time();

		if ($type != 0)
			$paddType=$type;
		else 
			$paddType = ($from == '518' || $from == '1580' || $from == '702' ) ? 2 : ( ( (strpos(strtoupper($subject),'OFF ') !== false) || (strpos(strtoupper($subject),'OFF:') !== false) || (strpos(strtoupper($subject),'//OFF') !== false)  )  ? 1 : 0);

		$paddClass='';
		if ($paddType == 4)
			$paddClass="masterPadd";
		if($paddType == 2)
			$paddClass="autoPadd";
		if($paddType == 1)
			$paddClass="offPadd";

		$textE=addslashes($text);
		$subjectE=addslashes($subject);
		mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,paddType) VALUES ($from,$myID,'$subjectE','<div class=\"paddMessage $paddClass\">$textE</div>',$curTime,0,$paddType)");
		if(mysql_error()){echo mysql_error();exit;}
		if(!isSet($this->paddMail)){
			$resa = mysql_fetch_assoc(mysql_query("SELECT paddMail,email FROM pg_users WHERE pgID = $myID"));
			$this->paddMail = $resa['paddMail'];
			$this->email = $resa['email'];
		}

		if($this->paddMail || $forceMail)
		{


			$sendTo = $this->email;
			$receiverName = $this->pgUser;
			$senderName = PG::getSomething($from,'username');
			$textP = nl2br(htmlentities(preg_replace('|[[\/\!]*?[^\[\]]*?]|', '', $text)));
			$subject = "[STF] $senderName >> ".$subject;
				
			$message = "<div style=\"text-align:center;\"><img src=\"https://oscar.stfederation.it/SigmaSys/logo/little_logo.png\" /></div><p>$senderName ti ha inviato un dpadd<br /><b>Testo:</b> $textP<br /><br />Accedi a <a href=\"http://www.stfederation.it\" target=\"_blank\">Star Trek: Federation</a> per consultare il padd!";

			$header = "From: $senderName <staff@stfederation.it>\r\n";
			$header .= "MIME-Version: 1.0" . "\r\n";
			$header .= "Content-type:text/html;charset=UTF-8" . "\r\n";

			mail($sendTo, $subject, $message, $header);
		}

	}

	public function sendWelcomePadd()
	{
		$this->sendPadd('Benvenuto!','<div style="text-align:center"><img src="https://oscar.stfederation.it/SigmaSys///promo_stf/little_logo.png" /><br /><b>Benvenuto in Star Trek: Federation!</b></div><br />Ti inviamo questo messaggio come riassunto del materiale informativo che trovi nella documentazione di gioco. In caso di perplessita\', non esitare a contattare lo Staff di Star Trek: Federation [POST]535[/POST]<br />
			

		Per rimanere aggiornato sulle novità di Star Trek: Federation, seguici sui canali social:
		<ul><li><a class="interfaceLinkBlue" target="_blank" href="https://t.me/stfederation">Canale Telegram</a></li><li><a class="interfaceLinkBlue" target="_blank" href="https://www.facebook.com/federation.pbc">Facebook</a> | <a class="interfaceLinkYellow" target="_blank" href="https://www.instagram.com/stfederation/">Intagram</a> e <a class="interfaceLinkBlue" target="_blank" href="https://twitter.com/stfederation">Twitter</a> </li>		<li><a class="interfaceLinkGreen" target="_blank" href="https://www.gdr-online.com/star_trek_federation.asp">Pagina GDR-Online</a>/</li></ul>
		Nuovo all\'ambientazione di Star Trek? Ecco qualche info utile: 

		<p style="margin:0px; margin-left:15px; "> [DB]186[/DB]
	
		Qui trovi il regolamento di gioco, dacci un\'occhiata: 
		[DB]150[/DB]
		
		Domande e Risposte frequenti. Hai un dubbio? Probabilmente troverai risposta nelle FAQ: 
		[DB]151[/DB]
		
		Guida al gioco di ruolo e ai principi da seguire per giocare al meglio:
		[DB]262[/DB]
		
		Chi e\' il tuo PG? Come descriverlo al meglio? Creare un buon Background e\' fondamentale. Cerca di compilarlo il prima possibile:
		[DB]241[/DB]</p>

		Qui invece trovi altre info utili:

		<p style="margin:0px; margin-left:30px; ">

		- <a href="getLog.php?session=1015" class="interfaceLink" style="text-decoration:underline" target="_blank"> Giocata di Esempio </a>
		- <a href="javascript:dbOpen()" class="interfaceLink" style="text-decoration:underline" > Documentazione Completa </a>

		[DB]265[/DB]
		[DB]242[/DB]
		[DB]259[/DB]
		[DB]248[/DB]
		</p>

		Buon gioco,<br />Il team di Star Trek Federation');
	
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
		{	
			if(($pointsPre+$i) % 12 == 0)
			{
				mysql_query("UPDATE pg_users SET pgUpgradePoints = pgUpgradePoints+1 WHERE pgID = $me");
				
				$cString = addslashes("Congratulazioni!!<br />Hai ottenuto 1 Upgrade Point!<br /><br /><p style='text-align:center'><span style='font-weight:bold'>Puoi usarli per aumentare le tue caratteristiche nella Scheda PG!</span></p><br />Il Team di Star Trek: Federation");
				$eString = addslashes("Upgrade Points!::Hai ottenuto un Upgrade Point!"); 
				
				mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField,paddType) VALUES (518,$me,'OFF: Upgrade Points!','$cString',$curTime,0,'',2),(518,$me,'::special::achiev','$eString',$curTime,0,'TEMPLATES/img/interface/personnelInterface/starIcon.png',2)");
				

			}
		}

		for($i = 0; $i < $p; $i++)
		{	
			if(($pointsPre+$i) % 400 == 0)
			{
				mysql_query("UPDATE pg_users SET pgSpecialistPoints = pgSpecialistPoints+1 WHERE pgID = $me");
				
				$cString = addslashes("Congratulazioni!!<br />Hai ottenuto 1 Punto Fortuna Critica!<br /><br /><p style='text-align:center'><span style='font-weight:bold'>Puoi usarlo per ottenere un successo critico a tua discrezione su qualunque dado!</span></p><br />Il Team di Star Trek: Federation");
				$eString = addslashes("Lucky Point!::Hai ottenuto un punto Fortuna Critica!"); 
				
				mysql_query("INSERT INTO fed_pad (paddFrom,paddTo,paddTitle,paddText,paddTime,paddRead,extraField,paddType) VALUES (518,$me,'OFF: Fortuna Critica!','$cString',$curTime,0,'',2),(518,$me,'::special::achiev','$eString',$curTime,0,'TEMPLATES/img/interface/personnelInterface/starIcon.png',2)");
				$this->sendNotification("Lucky Point","Hai ottenuto un Lucky Point!",$_SESSION['pgID'],"TEMPLATES/img/interface/index/blevinrevin_02.png",'schedaOpen');
			}
		}

	}

	public function bavosize(){
		$pgID = $this->ID;
		$pgName = $this->pgUser;
		//mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM fed_ambient_auth WHERE pgID = '$pgID';");
		//mysql_query("DELETE FROM fed_food_replications WHERE user = '$pgID';");
		mysql_query("DELETE FROM pg_incarichi WHERE pgID = '$pgID';");
		//mysql_query("DELETE FROM pg_achievement_assign WHERE owner = '$pgID';");
		mysql_query("DELETE FROM pg_alloggi WHERE pgID = '$pgID';");
		mysql_query("UPDATE pg_users SET pgBavo = 1, pgLocation = 'BAVO', pgRoom ='BAVO' WHERE pgID = $pgID");
		$this->sendPadd('Sospensione Account', "Ciao $pgName,

			Sono passati molti giorni dalla tua ultima giocata in Star Trek: Federation. Per garantire uno sviluppo funzionale degli organigrammi di bordo, il tuo personaggio è stato rimosso dal gioco attivo. Ci auguriamo di rivederti presto fra noi e ti assicuriamo che, in caso volessi tornare, il tuo PG sarà mantenuto attivo per altri 30 giorni. Al termine dei 30 giorni, il PG sarà eliminato dai nostri server.

			A presto
			Il team di Star Trek: Federation
			https://www.stfederation.it",518,2,1);
		$this->addnote("Bavosizzazione");

	}

	public function delete(){

		$pgID = $this->ID;
		$timeString =substr(time(),4,4).'_';
		mysql_query("UPDATE calendar_events SET sender = 6 WHERE sender = '$pgID';");
		mysql_query("UPDATE cdb_calls_comments SET owner = 6 WHERE owner = '$pgID';");
		mysql_query("UPDATE cdb_calls_results SET pgUser = 6 WHERE pgUser = '$pgID';");
		//mysql_query("UPDATE cdb_posts SET 'owner' = 6 WHERE owner = '$pgID';");
		//mysql_query("UPDATE cdb_posts SET 'coOwner' = 6 WHERE coOwner = '$pgID';");
		//mysql_query("UPDATE cdb_topics SET 'topicLastUser' = 6 WHERE topicLastUser = '$pgID';");
		//mysql_query("UPDATE db_elements SET lvisit = 6 WHERE lvisit = '$pgID';");
		mysql_query("UPDATE federation_chat SET sender = 6 WHERE sender = '$pgID';");
		mysql_query("UPDATE federation_sessions SET sessionOwner = 6 WHERE sessionOwner = '$pgID';");
		//mysql_query("UPDATE fed_pad SET paddFrom = 6 WHERE paddFrom = '$pgID';");
		//mysql_query("UPDATE fed_pad SET paddTo = 6 WHERE paddTo = '$pgID';");
		//mysql_query("UPDATE fed_sussurri SET susFrom = 6 WHERE susFrom = '$pgID';"); 
		//mysql_query("UPDATE fed_sussurri SET susTo = 6 WHERE susTo = '$pgID';");
		//mysql_query("UPDATE pg_notes SET owner = 6 WHERE owner = '$pgID';");
		mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM fed_ambient_auth WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM fed_food_replications WHERE user = '$pgID';");
		mysql_query("DELETE FROM pgDotazioni WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pgMedica WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_abilita_levels WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_incarichi WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_achievement_assign WHERE owner = '$pgID';");
		mysql_query("DELETE FROM pg_alloggi WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_groups_ppl WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_objects WHERE owner = '$pgID';");
		mysql_query("DELETE FROM pg_service_stories WHERE owner = '$pgID';");
		mysql_query("DELETE FROM pg_users_bios WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_users_pointStory WHERE owner = '$pgID';");
		mysql_query("DELETE FROM pg_users_tutorial WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_user_stories WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_personal_notifications WHERE owner = '$pgID';");
		mysql_query("DELETE FROM pg_visualized_elements WHERE pgID = '$pgID';");
		mysql_query("DELETE FROM pg_users_temp_auths WHERE pgID = '$pgID';");



		mysql_query("UPDATE pg_users SET pgUser = CONCAT('$timeString',pgUser), pgLocation = 'BAVO', email = CONCAT('$timeString',email), pgRoom ='BAVO',pgIncarico = '-', pgAuthOMA='BAN', pgOffAvatarC = '', pgOffAvatarN='' WHERE pgID = $pgID");
		$this->addnote("Cancellazione");

	}

	public function checkTempAuth($authType)
	{
		$pgID = $this->ID;
		$curTime = time();
		mysql_query("SELECT 1 FROM pg_users_temp_auths WHERE pgID = $pgID AND authType = '$authType' AND $curTime BETWEEN authStart AND authEnd ");
		if(mysql_affected_rows()) return 1;
		else return 0;
	}
	

	public function addMedal($what,$dater)
	{
		$pgID = $this->ID;
		mysql_query("INSERT INTO pgDotazioni (pgID,dotazioneIcon,doatazioneType,dotazioneAlt) VALUES ($pgID,'$what','MEDAL','$dater')");
	}
	
	public function addNote($what,$from=518,$reg=false)
	{
		$thisID = $this->ID;
		$thisString = addslashes($what);
		$regS = ($reg) ? '1' : '0';
		
		$curTime = time();
		mysql_query("INSERT INTO pg_notestaff (pgFrom,pgTo,what,timeCode,reg) VALUES ($from,$thisID,'$thisString',$curTime,$regS)"); 
	}

	public function getStatsRecord(){

		$stats=array();
		$pgID=$this->ID;
		
		$res=mysql_query("SELECT type,COUNT(IDE) as cnt FROM federation_chat WHERE type <> '' AND sender IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID)) GROUP BY type");
		while($reA=mysql_fetch_assoc($res))
			$acts[$reA['type']] = $reA['cnt'];

		$stats['AZIONI'] = $acts;


		$reA=mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as cnt FROM fed_food WHERE presenter IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID))"));
		$reB=mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as cnt FROM fed_food_replications WHERE user IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID))"));

		$stats['FOOD'] = array('Proposed'=>$reA['cnt'], 'Replicated'=>$reB['cnt']);

		$CDBa=mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as cnt FROM cdb_posts WHERE owner IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID))"));
		$CDBb=mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as cnt FROM cdb_posts WHERE topicID IN (SELECT pgDiario FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID)) AND owner IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID))"));

		$CDBc=mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as cnt FROM cdb_posts WHERE topicID IN (SELECT pgDiario FROM pg_users WHERE pgID = $pgID) AND owner IN (SELECT pgID FROM pg_users WHERE mainPG = (SELECT mainPG FROM pg_users WHERE pgID = $pgID))"));

		$stats['CDB'] = array('All'=>$CDBa['cnt'], 'Diari'=>$CDBb['cnt'], 'Diari (questo PG)'=>$CDBc['cnt']);

		


		return $stats;

	}
	public function getPlayRecord($days=15,$type=Null){

			$rpr = array();

			$thisMid=mktime(0,0,1,date('m'),date('d'),date('y'));
			for ($k = 0; $k < $days; $k++)
			{
				$startMid = $thisMid-($k*24*60*60);
				$endMid = $startMid+(24*60*60);
				$played = 0;
				$mastered=0;
				$connected=0;
				$syn="Non ha loggato";

				if(PG::mapPermissions("M",$this->pgAuthOMA) && $type=='master')
					mysql_query("SELECT 1 FROM federation_chat WHERE sender = ".$this->ID." AND type IN ('MASTER','MASTERSPEC') AND time BETWEEN $startMid AND $endMid");
				
				if(PG::mapPermissions("M",$this->pgAuthOMA) && mysql_affected_rows() && $type=='master'){ 
					$played = 1;
					$mastered=1;
					$connected=1;
					$syn="Ha masterato";
				}
				else
				{

					mysql_query("SELECT 1 FROM federation_chat WHERE sender = ".$this->ID." AND time BETWEEN $startMid AND $endMid");
					if(mysql_affected_rows()){ 
						$played = 1;
						$connected=1;
						$syn="Ha giocato";
					}
					else{
						$played=0;

						mysql_query("SELECT 1 FROM connlog WHERE user = ".$this->ID." AND time BETWEEN $startMid AND $endMid");
						if(mysql_affected_rows())
						{
							$connected=1;
							$syn="Ha loggato in gioco";
						}

					}
				}

				$rpr[$k] = array('kk'=>$k.' giorni fa:'.$syn,'played'=>$played,'mastered'=>$mastered, 'connected'=>$connected);
			}
			return $rpr;
	}

	public function getCTS()
	{
		$ara=array('Comando e Strategia' => 'cts_com.css','Comando e Navigazione' => 'cts_com.css', 'Ingegneria e Operazioni' => 'cts_ing.css','Tattica e Sicurezza' => 'cts_ing.css', 'Scientifica e Medica' => 
'cts_scimed.css','Scientifica' => 'cts_scimed.css','Medica' => 'cts_scimed.css', 'Navigazione' => 'cts_nav.css', 'Difesa e Sicurezza' => 'cts_dif.css','Ingegneria' => 'cts_ing.css');

		return ( (array_key_exists($this->pgSezione, $ara)) ? $ara[$this->pgSezione] : 0 );
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
	
	/*public static function sendModerationL($pgL,$moder)
	{
		$string = '<p class="offAction" title="Moderazione">'."[Moderazione (questo avviso lo vedi solo tu)] ".ucfirst(ltrim($moder)).'</p>';
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES((SELECT pgID FROM pg_users WHERE pgUser = '$pgL'),(SELECT pgRoom FROM pg_users WHERE pgUser = '$pgL'),'$string',".time().",'SPECIFIC')");
		mysql_query("INSERT INTO federation_chat (sender,ambient,chat,time,type) VALUES(".$_SESSION['pgID'].",(SELECT pgRoom FROM pg_users WHERE pgID = '".$_SESSION['pgID']."'),'$string',".time().",'SPECIFIC')");
	}*/

	
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
	
	public static function verifyOMA($id,$what)
	{
		$res = mysql_query("SELECT pgAuthOMA FROM pg_users WHERE pgID = $id");
		
		if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
		else $re = NULL;
		return PG::mapPermissions($what,$re['pgAuthOMA']);
	}

	public static function isMasCapable($id)
	{
		$res = mysql_query("SELECT 1 FROM pg_users WHERE pgID = $id AND isMasCapable = 1");
		if(mysql_affected_rows())return true;
		else return false;
	}
	public static function setSomething($id,$var,$val)
	{
		if($var == "UP")
			mysql_query("UPDATE pg_users SET pgUpgradePoints = $val WHERE pgID = $id");
	}



	public static function getSomething($id,$var)
	{

		if($var == "BG")
		{
			$res = mysql_query("SELECT * FROM pg_users_bios WHERE valid = 2 AND pgID = $id ORDER BY recID DESC LIMIT 1");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re;
		}

		if($var == 'rankCode')
		{

			$res = mysql_query("SELECT rankCode FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows())
				{
					$re = mysql_fetch_array($res); 
					return $re['rankCode'];
				}
			else return NULL;
		}

		if($var == "lastBG")
		{
			$res = mysql_query("SELECT pg_users_bios.*,pg_users.pgUser as edit_pgUser FROM pg_users_bios,pg_users WHERE pg_users_bios.pgID = $id AND pg_users.pgID = edituser AND valid < '2' ORDER BY recID DESC LIMIT 1");
			//echo "SELECT pg_users_bios.*,pg_users.pgUser as edit_pgUser FROM pg_users_bios,pg_users WHERE pg_users_bios.pgID = $id AND pg_users.pgID = edituser AND valid < '2' ORDER BY recID DESC LIMIT 1";exit;
			if(mysql_affected_rows()) $re = mysql_fetch_array($res); 
			else $re = NULL;
			return $re;
		}

		elseif($var == 'optionsRec')
		{
			$res = mysql_query("SELECT paddMail,email,parlatCSS,otherCSS FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res);  
			else $re = NULL;
			return $re;
		}
		
		elseif($var == 'prestavolto')
		{ 
			$res = mysql_query("SELECT TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(iscriDate), NOW()) as iscriDiff,iscriDate ,pgOffAvatarN,pgOffAvatarC,pgPrestige FROM pg_users WHERE pgID = $id");
			if(mysql_affected_rows()){
				$re = mysql_fetch_array($res);
				if((int)$re['iscriDate'] < 1492383822)
					$re['iscriDiff'] = $re['iscriDiff']-26;
				  
			}
			
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
		elseif($var == 'uniform')
		{

			$res = mysql_query("SELECT uniform FROM pg_users,pg_uniforms WHERE pgMostrina = mostrina AND pgID = $id");
			if(mysql_affected_rows()) $re = mysql_fetch_array($res)['uniform'];  
			else $re = 'nouniformm'; 
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
		
		/*
		else if($var == "pgUnit")
		{
			$res = mysql_query("SELECT placeName FROM pg_places,pg_users WHERE placeID = pgAssign AND pgID = ".$id);
			$resA=mysql_fetch_array($res);
			return $resA['placeName'];
		}*/
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
		if ($actual == "G" && $requested == "O") return true;

		if ($actual == "A" && $requested == "G") return true;
		if ($actual == "SM" && $requested == "G") return true;
		if ($actual == "M" && $requested == "G") return true;
		if ($actual == "SL" && $requested == "G") return true;


	
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
		elseif ($red == "G") return "Guida";
		elseif ($red == "O") return "Olomaster";
		elseif ($red == "N") return "Giocatore";
		return false;
	}
	
	public static function returnMapsStringFORDB($actual)
	{	// O M SM A
			
		if ($actual == "A") return "'A','MM','SM','M','SL','JM','G','O','N'";
		elseif ($actual == "SM") return "'G','SM','MM','SL','M','JM','O','N'";
		elseif ($actual == "MM") return "'G','MM','JM','SL','O','N'";
		elseif ($actual == "M") return "'G','M','JM','O','SL','N'";
		elseif ($actual == "JM") return "'G','JM','O','N'";
		elseif ($actual == "G") return "'G','O','N'";
		elseif ($actual == "O") return "'O','N'";
		elseif ($actual == "N") return "'N'";
		
		return "()"; 
	}
	
}

class timeHandler
{
	public static function timestampToGiulian($time)
	{
		if (date('d/m/Y') == date('d/m/Y',$time) )
			return "Oggi ".self::extrapolateHour($time);
		else {
			$tYear=(date("Y",$time)+379);
			if($tYear < 2395) {$tYear = $tYear - 11;}
			return date("d/m",$time)."/".$tYear." ".self::extrapolateHour($time);

		}
	} 
	
	public static function extrapolateDay  ($time)
	{
	return date("d/m",$time)."/".(date("Y",$time)+379);
	}
	
	public static function extrapolateDayHour($time)
	{
	return date("d/m",$time)."/".(date("Y",$time)+379)." ".self::extrapolateHour($time,false);
	}
	
	public static function extrapolateHour($time,$secs=true)
	{
		return ($secs) ? date("H:i:s",$time) : date("H:i",$time);
	}
	
	public static function getOnline($var)
	{
		if($var == NULL)
		$my = Database::query("SELECT COUNT(*) AS conto FROM pg_users WHERE pgLastAct >= ".(time()-1800));
		
		else $my = Database::query("SELECT COUNT(*) AS conto FROM pg_users WHERE pgLocation = '$var' AND pgLastAct >= ".(time()-1800));
		
		$ra = mysqli_fetch_array($my);
		return $ra['conto'];
	}
}

class Mailer
{
	public static function emergencyMailer($text,$refU){
	$string = "TimeStamp: ".date('d/m/Y ore H:i:s',time())."\n TENTATIVO FALLITO DI VIOLAZIONE\n\n".$text."\n\n"."REFERENZE: User:".$refU->pgUser." IP:".$_SERVER['REMOTE_ADDR'];
	mail("moreno@stfederation.it","[FED] Violazione di Sicurezza",$string,"From:emergency@stfederation.it");
	}
	
	public static function notificationMail($text,$refU){
		$string = "TimeStamp: ".date('d/m/Y ore H:i:s',time())."\n MODIFICA CRITICA ESEGUITA\n\n".$text."\n\n"."REFERENZE: User:".$refU->pgUser;

		foreach (array(3,1892) as $pgAdmin){
			$u=new PG($pgAdmin);
			$u->sendNotification("Modifica Critica di".$refU->pgUser,date('d/m/Y H:i:s',time()).' '.$text,$refU->ID,$refU->pgAvatarSquare,'');	
		}
	}
}
?>
