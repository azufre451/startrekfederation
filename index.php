<?php
error_reporting(E_ALL);

  
ini_set('display_errors', 1);
setlocale(LC_TIME, 'it_IT');
//echo date('F');exit;

include('includes/app_include.php');
include('includes/validate_class.php');

include('includes/bbcode.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
//phpinfo();exit; 

if (isSet($_GET['login']) && $_GET['login'] == 'do')
{

	header("Location:login.php");
	exit;
}

if(isSet($_GET['readnews']))
{
	$vali = new validator();
	$to = $vali->numberOnly($_GET['readnews']);
	setlocale(LC_TIME, 'it_IT');
	

	if (isSet($_GET['fmn']))
	{
		$news = mysql_query("SELECT * FROM fed_master_news WHERE recID = $to");
		$template = new PHPTAL('TEMPLATES/index_news.htm');
		
		$newsA = mysql_fetch_array($news);
		
		$template->ID = $newsA['recID'];
		$template->title = $newsA['title'];
		$template->subtitle = '';
		$template->titParticle = 'Star Trek: Federation News';
		$template->shortDescript = substr($newsA['content'],0,270);
		$template->aggregator = '';
		$template->text = str_replace($bbCode,$htmlCode,$newsA['content']);
		$template->timer = (strftime('%e', $newsA['time']).' '.ucfirst(strftime('%B', $newsA['time'])).' '.(date('Y', $newsA['time'])+379));
	
	}

	else{

	$news = mysql_query("SELECT * FROM fed_news WHERE newsID = $to");
	$template = new PHPTAL('TEMPLATES/index_news.htm');
	
	$newsA = mysql_fetch_array($news);
	
	$template->ID = $newsA['newsID'];
	$template->title = $newsA['newsTitle'];
	$template->subtitle = ($newsA['aggregator'] == 'FED') ? $newsA['newsSubTitle'].' - ' : '';
	$template->titParticle = ($newsA['aggregator'] == 'FED') ? 'The Tychonian Eagle' : 'Star Trek: Federation News';
	$template->shortDescript = substr($newsA['newsText'],0,270);
	$template->aggregator = $newsA['aggregator'];
	$template->text = str_replace($bbCode,$htmlCode,$newsA['newsText']);
	$template->timer = (strftime('%e', $newsA['newsTime']).' '.ucfirst(strftime('%B', $newsA['newsTime'])).' '.(date('Y', $newsA['newsTime'])+379));
	
	}
	if ($to==165){
		$ada = fopen("ada.txt", "a");
		fwrite($ada,date('d/m/Y H:i:s')."\t".$_SERVER['REMOTE_ADDR']."\r\n"); 
	}
}

else if(isSet($_GET['gallery']))
{
	$template = new PHPTAL('TEMPLATES/index_gallery.htm');
}

else if(isSet($_GET['directory']))
{
	$template = new PHPTAL('TEMPLATES/index_directory.htm');
}

else if(isSet($_GET['contacts']))
{
	$template = new PHPTAL('TEMPLATES/index_cont.htm');
}
else if(isSet($_GET['thanks']))
{
	$template = new PHPTAL('TEMPLATES/thankyou.htm');
}

else if(isSet($_GET['guide']))
{
	$template = new PHPTAL('TEMPLATES/index_guida.htm');
}


else
{
	$template = new PHPTAL('TEMPLATES/index.htm');

	$news = mysql_query("SELECT * FROM fed_news WHERE aggregator = 'FED' ORDER BY newsTime DESC LIMIT 3");
$articles=array();
while($re = mysql_fetch_array($news))
$articles[] = $re;

$newsAct = mysql_query("(SELECT newsID,newsTitle,newsText,newsTime,aggregator,toLink FROM fed_news) UNION (SELECT recID as newsID, title as newsTitle, content as newsText, time as newsTime, 'FDX' as aggregator, '' as toLink FROM fed_master_news) ORDER BY newsTime DESC LIMIT 4");
$resNews = array();
while($rea = mysql_fetch_array($newsAct))
{
$txt = str_replace($bbCode,$htmlCode,$rea['newsText']);
$txtL = str_replace($bbCode,$htmlCode,$rea['newsText']);
$txt = str_replace("<br />","",$txt);
$txt = str_replace("<br>","",$txt);
$lim = 250 - strlen($rea['newsTitle']);
$resNews[$rea['newsTime']] = array('title' => $rea['newsTitle'],'aggregator'=>$rea['aggregator'],'rtext' => $txtL ,'text' => (strlen($txt) > $lim) ? substr($txt,0,$lim).'...' : $txt,'data' => timeHandler::extrapolateDay($rea['newsTime']),'toLink' => $rea['toLink'], 'newsID' => $rea['newsID']);
}


/*Aut Reg*/
$a1 = array('ALFA','BETA','GAMMA','DELTA','ETA','EPSILON','ZETA','ETA','THETA','IOTA','KAPPA','LAMBDA','MI','NI','XI','OMICRON','PI','RHO','SIGMA','TAU','YPSILON','PHI','CHI','PSI','OMEGA');
$template->aut = $a1[rand(0,24)].' '.$a1[rand(0,24)].' '.rand(0,10).' '.rand(0,10);


krsort($resNews);
$template->resNews = $resNews;
$template->mod = (isSet($_GET['mod'])) ? $_GET['mod'] : ''; 

/*Aut Reg*/
$template->aut = $a1[rand(0,24)].' '.$a1[rand(0,24)].' '.rand(0,10).' '.rand(0,10);


/*Bonus*/

$bonuses=array();
$resModQ=mysql_query("SELECT abName,abImage,abMod,reason,0 as special,species FROM pg_abilita_bonus,pg_abilita WHERE pg_abilita.abID = pg_abilita_bonus.abID ORDER BY type ASC, abMod ASC");
while($resMod=mysql_fetch_assoc($resModQ))
{
	if(!array_key_exists($resMod['species'],$bonuses))
		$bonuses[$resMod['species']] = array();

	$resMod['abMod'] = str_replace('0','*',$resMod['abMod']);
	$resMod['reason'] = '<span style="font-weight:bold; color:#FFCC00">'.$resMod['abName'].'</span> - '.$resMod['reason'];
	$bonuses[$resMod['species']][] = $resMod;
}
$bonuses['Umana'][]= array('abName'=>'Punti Bonus','abImage'=>'','abMod'=>'+50 UP','reason'=>'In luce della loro elevata versatilità, gli umani ottengono un bonus di 50 Upgrade Points','special'=>1);


$template->bonuses = $bonuses;

 
$template->tips = $tips;
$template->resNews = $resNews;
$template->gameServiceInfo = $gameServiceInfo;




}

$template->online = timeHandler::getOnline(NULL);
$template->thisYear = $thisYear;
$template->gameOptions = $gameOptions;
$template->keywords="Star Trek, Voyager, Enterprise, The Next Generation, Tricorder, Phaser, Borg. Gioco di Ruolo, Federazione, Klingon, Romulani, Starbase Tycho, GDR, Play by Chat, Picard, Discovery";
$template->description="Gioco di Ruolo online che unisce l'interpretazione di un eroe del serial televisivo alla scrittura creativa via chat. Crea il tuo personaggio oggi!";

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
?>