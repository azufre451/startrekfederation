<?php  

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
//if (!PG::mapPermissions('A',PG::getOMA($_SESSION['pgID']))) {echo "AUTORIZZAZIONI NON SUFFICIENTI2! TRASSONE!"; exit;}

$newsAct = mysql_query("SELECT * FROM fed_news ORDER BY newsTime DESC");
$strs = "";
while($rea = mysql_fetch_array($newsAct)){ 

	$title = ($rea['aggregator'] == 'FED') ? "Federation Tribune: ".$rea['newsTitle'] : "News: ".$rea['newsTitle'];
	$idLink = "http://www.startrekfederation.it/index.php?readnews=".$rea['newsID'];
	
	$link = ($rea['aggregator'] == 'FED' || $rea['toLink'] == '') ? $idLink : $rea['toLink'];
 
	$descript = substr($rea['newsText'],0,350).'...';
	$dtt = date(DATE_RSS,$rea['newsTime']);
	$imago = "http://www.startrekfederation.it/TEMPLATES/img/interface/index/".$rea['aggregator'].'.png';
 
	$strs.="<item>
<title>$title</title>
<link>$link</link>
<description></img>$descript</description>
<pubdate>$dtt</pubdate>
</item>";
}

$strl = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<rss version=\"2.0\">

<channel>
  <title>Star Trek: Federation - News</title>
  <link>http://www.startrekfederation.it</link> 
	$strs
</channel>

</rss>";
 
$out = fopen("TEMPLATES/static/feedrd.rss",'w');
fwrite($out,$strl);
fclose($out);

echo "DONE";

include('includes/app_declude.php');	
?>
