<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/app_include.php');
include('includes/validate_class.php');
include("../includes/PHPTAL/PHPTAL.php");



if(isSet($_GET['readnews']))
{
	$vali = new validator();
	$to = $vali->numberOnly($_GET['readnews']);
	setlocale(LC_TIME, 'it_IT');
	
	$news = mysql_query("SELECT * FROM fed_news WHERE newsID = $to");
	$template = new PHPTAL('TEMPLATES/index_news.htm');
	
	$newsA = mysql_fetch_array($news);
	
	$template->ID = $newsA['newsID'];
	$template->title = $newsA['newsTitle'];
	$template->subtitle = ($newsA['aggregator'] == 'FED') ? $newsA['newsSubTitle'].' - ' : '';
	$template->titParticle = ($newsA['aggregator'] == 'FED') ? 'The Federation Tribune' : 'Star Trek: Federation News';
	$template->shortDescript = substr($newsA['newsText'],0,270);
	$template->aggregator = $newsA['aggregator'];
	$template->text = str_replace($bbCode,$htmlCode,$newsA['newsText']);
	$template->timer = (strftime('%e', $newsA['newsTime']).' '.ucfirst(strftime('%B', $newsA['newsTime'])).' '.(date('Y', $newsA['newsTime'])+368));
}

else if(isSet($_GET['gallery']))
{
	$template = new PHPTAL('TEMPLATES/index_gallery.htm');
}

else if(isSet($_GET['directory']))
{
	$template = new PHPTAL('TEMPLATES/index_directory.htm');
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

$res =  mysql_query("SELECT imaURL FROM cdb_random_images WHERE type='INTRO' ORDER BY RAND() LIMIT 1");
$reso = mysql_fetch_array($res);

$res = mysql_query("SELECT tipImage, tipText FROM cdb_tips WHERE active='A' ORDER BY RAND() LIMIT 1");
$resoT = mysql_fetch_array($res);


$template = new PHPTAL('TEMPLATES/index.htm');
$template->tipImage = $resoT ['tipImage'];

$template->login = (isSet($_GET['login'])) ? true : false;
$template->logged = (isSet($_SESSION['pgID'])) ? true : false;
$template->loginRedo = (isSet($_GET['login']) && $_GET['login'] == 'redo') ? true : false;

$template->tipText = $resoT ['tipText'];
$template->backLoginImage = $reso['imaURL'];
$template->mod = (isSet($_GET['mod'])) ? $_GET['mod'] : '';
$template->error = (isSet($_GET['error'])) ? $_GET['error'] : NULL; 
$template->success = (isSet($_GET['success'])) ? $_GET['success'] : NULL; 
$template->currentStarDate = $currentStarDate;

setlocale(LC_TIME, 'it_IT');
$template->datae = strftime('%e').' '.ucfirst(strftime('%B')).' '.(date('Y')+368);

$news = mysql_query("SELECT * FROM fed_news WHERE aggregator = 'FED' ORDER BY newsTime DESC LIMIT 3");
$articles=array();
while($re = mysql_fetch_array($news))
$articles[] = $re;

$newsAct = mysql_query("SELECT * FROM fed_news ORDER BY newsTime DESC LIMIT 10");
$resNews = array();
while($rea = mysql_fetch_array($newsAct))
{
$txt = str_replace($bbCode,$htmlCode,$rea['newsText']);
$txtL = str_replace($bbCode,$htmlCode,$rea['newsText']);
$txt = str_replace("<br />","",$txt);
$txt = str_replace("<br>","",$txt);
$lim = 200 - strlen($rea['newsTitle']);
$resNews[] = array('title' => $rea['newsTitle'],'aggregator'=>$rea['aggregator'],'rtext' => $txtL ,'text' => (strlen($txt) > $lim) ? substr($txt,0,$lim).'...' : $txt,'data' => date('d/m/Y',$rea['newsTime']),'toLink' => $rea['toLink'], 'newsID' => $rea['newsID']);
}

/*Aut Reg*/
$a1 = array('ALFA','BETA','GAMMA','DELTA','ETA','EPSILON','ZETA','ETA','THETA','IOTA','KAPPA','LAMBDA','MI','NI','XI','OMICRON','PI','RHO','SIGMA','TAU','YPSILON','PHI','CHI','PSI','OMEGA');
$template->aut = $a1[rand(0,24)].' '.$a1[rand(0,24)].' '.rand(0,10).' '.rand(0,10);


/*Admins*/
$adm = mysql_query("SELECT pgID, pgSpecie, pgSesso, pgUser, pgGrado,pgAvatar, pgIncarico, pgSezione, ordinaryUniform FROM pg_users,pg_ranks WHERE prio = rankCode AND pgID IN (1,5,796)");
while ($admres = mysql_fetch_array($adm)) $admT[$admres['pgID']] = $admres;
$template->admT = $admT;

$template->articles=$articles;
$template->gameVersion = $gameVersion;
$template->debug = $debug;
$template->tips = $tips;
$template->resNews = $resNews;
$template->gameServiceInfo = $gameServiceInfo;
}

$template->online = timeHandler::getOnline(NULL);
//echo "WALALO:".mktime(23,59,59,1,14,2013);
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
?>