<?php

class Session
{
	private $defLiner = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<link rel="shortcut icon" href="favicon.ico" />
		
		<title>Star Trek: Federation - Log: ###PLH_TITLE###</title>
	<script>
			function selectOccur(tex){}
			function deselectOccur(tex){}
	</script>
	<style>	
	body
	{	
		background-color:black;
		color:white;
		display: block;
	    height:auto;
		font-family:Verdana, Helvetica, Arial;
	}
	img{border:0px;}

	.chatAction,.subspaceCom,.commMessage,.chatQuotation,.chatQuotationAction,.chatUser{font-style:italic;}
	.chatAction,.subspaceCom,.commMessage,.chatTag,.masterAction, .globalAction,.offAction,.auxAction, .tempMasterAction,.specificMasterAction,.oloMasterAction{font-weight:bold;}
	.chatAction,.subspaceCom,.commMessage,.chatDirect{margin:0px;margin-top:4px;}
	.chatAction,.subspaceCom,.commMessage{font-size:14px;}

	.chatAction{color:#3188F3; line-height:1.3em;}
	.subspaceCom,.commMessage{color:#ffefcc;}
	.subspaceComPre,.commPreamble{font-size:12px;color:#e8a30e;}
	.chatDirect{font-size:15px;color:#EEE;}
	.chatInvisi{height:0px;}
	.chatQuotation{color:#d7a436;}
	.chatQuotationAction,.chatUser{color:#999;}

	.chatTag{font-size:12px;color:#d7a436;}
	.chatUser{margin-right:5px;}
	.highlight{
	text-shadow: 0 0 2px #47cd35;
	color:#47cd35;
	}

	.turnElement{
		margin:0px;
		margin-top:2px;
		font-size:13px;
		font-weight:bold;
		text-align:left;
		margin-left:3px;
		font-family:Helvetica;
	}

	.myTurnElement
	{
		text-transform:uppercase;
	}

	.masterAction, .globalAction,.offAction,.auxAction,.specificMasterAction,.oloMasterAction,.diceAction
	{
		padding:8px;
		border:1px solid;
		font-size:15px;
		margin:5px;
		text-align:center;
	}
	.globalAction > div:first-child , .masterAction > div:first-child, .oloMasterAction > div:first-child, .offAction > div:first-child, .auxAction > div:first-child, .specificMasterAction   > div:first-child, .diceAction  > div:first-child, .oloMasterAction  > div:first-child {
	float:left;
	font-size: 12px;
	margin-top:-8px;
	margin-left:-8px;
	padding: 3px 10px; 
	border-bottom-width:1px;
	border-right-width:1px;
	border-bottom-style:solid;
	border-right-style:solid;
	}
	.globalAction{border-color:#3188F3; color:#3188F3;}
	.globalAction > div:first-child {background-color: #14335a; border-color:#3188F3; color:white;}

	/*Master*/
	.masterAction{border-color:red; color:red;}
	.masterAction > div:first-child {background-color: #850000; border-color:red; color:white;} 

	/*OFF*/
	.auxAction, .oloMasterAction{border-color:#b3b3b3; color:#b3b3b3;}
	.auxAction > div:first-of-type, .oloMasterAction > div:first-child {background-color: #333; border-color:#b3b3b3; color:white;} 
	 
	.offAction{border-color:#1db716; color:#1db716;}  
	.offAction > div:first-child {background-color: #175a14; border-color: #1db716; color:white;}
	 
	.diceAction{border-color:#ff8a00; color:#ff8a00;}
	.diceAction > div:first-child {background-color: #a95b00; border-color:#ff8a00; color:white;} 

	.specificMasterAction{border-color:#c67729; color:#c67729;}  
	.specificMasterAction > div:first-child {background-color: #8a5e09; border-color: #c67729; color:white;}
	  

	.imageAction{text-align:center; margin:5px;}
	.imageAction img {border:0px; max-height:250px; border:1px solid #3188F3; padding:5px;}
	.imaLer:hover{border:1px solid #ff9900;}
	.imaLer{border:1px solid black;}
	.blackOpacity img {vertical-align:middle;}

	.directiveRemove{display:none;}


	.diceOutcomeBox{

		clear:both;
		font-size: :15px;
	    display: inline-block;
	    /* for ie6/7: */
	    *display: inline;
	    text-align: left;
		background-color:black;
		color:white;
		border:0px;
	}


	.diceOutcomeBox div{

		background-color:black;
		margin-top:5px;
		margin-left:10px;
		float:left; 
		border-right:1px solid #333;
	padding:5px;

		padding-right:10px;
		width:140px;
		
	}

	.diceOutcomeBox img{
		width:30px;
		vertical-align: middle;
	}

	.diceOutcomeBox p.bar
	{
		width:100%;
		height: 4px;
		margin:0;
		margin-bottom:5px;

	}
	.diceOutcomeBox div.S p.bar{
		background-color:#35971e;
	}

	.diceOutcomeBox div.F p.bar{
		
		background-color:#d72b2b;
		
	}

	.diceOutcomeBox div.FC p.bar{
		background-color:#971e1e;
		
	}

	.diceOutcomeBox div.SC p.bar{
		background-color:#1e7597;	
	}

	.diceOutcomeBox p.label{text-align:right; margin:0px; margin-top:4px; font-size:12px;}

	.diceOutcomeBox div.FC p.label::after{color:#971e1e; content:"[ Fall. Critico ]";}
	.diceOutcomeBox div.F p.label::after{color:#d72b2b; content:"[ Fallimento ]";}
	.diceOutcomeBox div.S p.label::after{color:#35971e; content:"[ Successo ]";}
	.diceOutcomeBox div.SC p.label::after{color:#1e7597; content:"[ Succ. Critico ]";}



	.diceOutcomeBox span.bmal::before{content:"[";}
	.diceOutcomeBox span.bmal::after{content:"]";}

	.diceOutcomeBox div.S span.bmal::before, .diceOutcomeBox div.S span.bmal::after{
		color:#35971e;
	}

	.diceOutcomeBox div.F span.bmal::before, .diceOutcomeBox  div.F span.bmal::after{
		
		color:#d72b2b;
		
	}

	.diceOutcomeBox div.FC span.bmal::before, .diceOutcomeBox div.FC span.bmal::after{
		color:#971e1e;
		
	}

	.diceOutcomeBox div.SC span.bmal::before, .diceOutcomeBox  div.SC span.bmal::after{
		color:#1e7597;	
	}





	.blackOpacity
	{
		background-color:rgba(0,0,0);
		background-color:rgba(0,0,0,0.9);
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#B2000000,endColorstr=#B2000000);
	}  


	 
	.blackOpacity img {vertical-align:middle;}
	.chatUser
	{
	cursor:pointer;
	}

	.repliLine{width:630px; margin:auto; height:auto;}
	.repliLeft, .repliRight{margin:0px; font-weight:normal; text-align:center;  font-size:15px; font-weight:bold; display: inline-block; vertical-align:middle;}
	.repliLeft img{vertical-align:middle; float:left; width:150px; display: table-cell; vertical-align:middle;}
	.repliRight span{font-style:italic; color:white; font-weight:normal; font-size:13px;}
	.repliLeft{width:150px;}
	.repliRight{width:455px; margin-left:5px;}

	.officers{margin:0px;}

	input, select, textarea, button
	{
		color:#999;
		border:1px solid #999;
		background-color:black;
	    font-family: Helvetica;
	    font-size: 13px;
	    padding: 1px;
	}

	textarea {font-size:13px;}
	input:focus, select:focus, textarea:focus, button:focus
	{
		color:white;
		border:1px solid white;
	}

	input:hover, select:hover, textarea:hover, button:hover
	{
		color:white;
		border:1px solid white;
	}

	</style>	
	</head>


	<body style="background-color:black;">
	<div style="float:left; width:40%; border:1px solid #333; margin-left:30px; padding:20px;"><p style="text-align:center;color:orange; font-weight:bold;">Presenti alla giocata:</p><br /><table><tr style="padding:20px; font-size:12px; text-align:center;"><td style="width:180px;">PG</td><td>Prima Azione</td><td>Ultima Azione</td><td>Azioni</td></tr>
	';
	
	public function __construct($sessid)
	{
		$sesser = mysql_fetch_assoc(mysql_query("SELECT * from federation_sessions LEFT JOIN fed_ambient ON locID = sessionPlace LEFT JOIN pg_places ON placeID =  ambientLocation WHERE sessionID = $sessid"));

		$this->sessionID = $sessid; 
		$this->archived=$sesser['archived']; 
		$this->placeName=$sesser['placeName']; 
		$this->locName=$sesser['locName'];
		$this->placeLogo=$sesser['placeLogo']; 
		$this->locID = $sesser['locID'];
		$this->sessionIniTime = $sesser['sessionStart']; 
		$this->sessionStopTime = $sesser['sessionEnd']; 
		$this->sessionLabel=$sesser['sessionLabel'];
		$this->isPrivate = ($sesser['sessionPrivate']) ? 1 : 0;
		$this->sessionPrivate = ($sesser['sessionPrivate']) ? '<p style="font-size:15; color:red; font-weight:bold;"> Giocata Privata </p>' : ''; 
		 
	}

	public function getText($tofile,$showPrivate,$showDWL=0)
	{
		if ($this->archived)
		{
			$sessionFile='saved_sessions/archive_all/'.strtoupper($this->locID).'_session_'.$this->sessionID.'.html';
			if(file_exists($sessionFile))
			{
				$inf=fopen($sessionFile,'r');
				$this->htmlLiner = fread($inf, filesize($sessionFile));
				fclose($inf);
			}
		}  
		else
		{

			$downloadButton = ($showDWL) ? "<div style=\"text-align:center;\"> Scarica una copia della giocata in <a style=\"color:#FFCC00\" href=\"getLog.php?session=".$this->sessionID."&toFile=do\">formato HTML</a> </div> <br/>" : "";

			$filter = ($showPrivate) ? '' : 'AND privateAction = 0';

			$this->htmlLiner = str_replace('###PLH_TITLE###',$this->sessionLabel,$this->defLiner);
		
			$presents = mysql_query("SELECT DISTINCT pgUser,ordinaryUniform,pgGrado,pgSezione,MIN(time) as minner, MAX(time)  as maxer,COUNT(chat) as chatter FROM pg_users,federation_chat,pg_ranks WHERE sender=pgID AND prio=rankCode AND ambient = '".$this->locID."' AND (time BETWEEN ".$this->sessionIniTime." AND ".$this->sessionStopTime.") AND type IN ('DIRECT','ACTION') $filter GROUP BY pgUser,ordinaryUniform,pgGrado,pgSezione ORDER BY minner ASC");
			echo mysql_error();
			$userLister='';
			while($resa=mysql_fetch_array($presents))
			{
				$ima=$resa['ordinaryUniform'];
				$person = $resa['pgUser'];
				$minner = date('H:i:s',$resa['minner']);
				$maxer = date('H:i:s',$resa['maxer']);
				$chatter = $resa['chatter'];
				$title= $resa['pgGrado']." - ".$resa['pgSezione'];
				$this->htmlLiner .= "<tr class=\"chatUser officers\" style=\"font-size:12px;\"><td style=\"color:white;\"><img src=\"TEMPLATES/img/ranks/$ima.png\" title=\"$title\" /> $person</td><td>$minner</td><td>$maxer</td><td>$chatter</td></tr>";
				$userLister.="$person, ";
			}
			

			$this->htmlLiner.="</table></div><div style=\"float:right; text-align:center; width:30%; margin-left:30px;\"><div style=\"border:1px solid #666; padding:20px;\"><b>Codice Auto-Mostrine per CDB:</b> <i>Copia questa lista e incollala nel tool \"Avanzate\" del CDB per ottenere la lista dei presenti con mostrine e incarichi.</i><br /><br />
			
			<input value=\"$userLister\" onclick=\"javascript:this.select();\" style=\"width:97%\"/></div>
			
			<p style=\"font-family:Arial; font-weight:bold; font-size:22px;\"><img src=\"TEMPLATES/img/logo/".$this->placeLogo."\" height=\"100px\" align=\"left\">".$this->placeName."<br />".$this->locName."</p>".$this->sessionPrivate."
			
			</div><div style=\"clear:both\" /><br /><div style=\"clear:both;\"></div> ".$downloadButton." <hr /><div style=\"padding:20px; border:1px solid #666; margin-top:20px;\">
			";
			


			$chatLines = mysql_query("SELECT chat,time FROM federation_chat WHERE ambient = '".$this->locID."' AND (time BETWEEN ".$this->sessionIniTime." AND ".$this->sessionStopTime.") AND type NOT IN ('APM','AUDIO','AUDIOE','SPECIFIC','SERVICE') $filter ORDER BY time");
			
			
			while($chatLi = mysql_fetch_array($chatLines))
			{	
				if(!isSet($head))
				{
				$this->htmlLiner.="<p style=\"text-align:center; color:white; font-weight:bold;\">Inizio del log alle: <span style=\"color:#3188F3;\">".date('H:i:s',$chatLi['time'])."</span> del <span style=\"color:#3188F3;\">".date('d-m-Y',$chatLi['time'])."</span></p><br />";
				$head=true;
				}
				$this->htmlLiner.=$chatLi['chat'];
			}
			
			$this->htmlLiner.="</div></body></html>";
			$this->htmlLiner = str_replace('TEMPLATES/img/','https://www.startrekfederation.it/TEMPLATES/img/',$this->htmlLiner);
		}

		if ($tofile){
	   		$fileName = "temp/log_".$_SESSION['pgID'].".html";
			$fh = fopen($fileName, 'w');
			fwrite($fh, $this->htmlLiner);
			$size = filesize($fileName);//calcola dimensione del file 
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Cache-Control: private');
			header('Pragma: no-cache');
			header("Content-Transfer-Encoding: binary");
			header("Content-length: {$size}");
			header("Content-type: text/html");
			$tit = date('d-m-Y').' - '.$this->locName.' - '.date('H.i').'.html';
			header("Content-disposition: attachment; filename=\"{$tit}\"");
			readfile($fileName);
			exit;
		}
		else{

			return $this->htmlLiner;
		}
	}
}
?>