<?php
session_start();

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 

if(isSet($_SESSION['pgID'])) PG::updatePresence($_SESSION['pgID']);
else {header('Location:index.php'); exit;}
$vali = new validator();


if(isSet($_GET['dopdf']))
{	
	if(!isSet($_SESSION['pgID'])) header('Location:index.php');
	require('includes/tcpdf/tcpdf.php');
	
	$elementID = $vali->numberOnly($_GET['dopdf']);

	$fileName='temp/dbDispensa_'.$elementID.".pdf";
	
	$cat = mysql_query("SELECT db_cats.catID,catName,catImage,title,content FROM db_cats,db_elements WHERE db_cats.catID = db_elements.catID AND ID = $elementID");
	if ($resA = mysql_fetch_array($cat))
	{
		$catID = $resA['catID'];
		$catName = $resA['catName'];
		$catImage = $resA['catImage'];
		$title = $resA['title'];
		
		$htmldCode = array(
"<b>","</b>",
"<i>","</i>",
"<u>","</u>",
"<div style=\"text-align:center;\" align=\"center\">","</div>",
"<p style=\"text-align:left\">","</p>",
"<p style=\"text-align:right\">","</p>",
"<span style=\"color:red; font-weight:bold;\">","<span style=\"color:#4464c1; font-weight:bold;\">",
"<span style=\"color:black; font-weight:bold;\">","<span style=\"font-weight:bold; color:black;\">",
"<span style=\"color:#179a10; font-weight:bold;\">","<span style=\"color:#333; font-weight:bold;\">",
"<span style=\"font-size:13px; font-weight:bold;\">","<span style=\"font-size:13px;\">",
"<span style=\"font-size:16px; font-weight:bold;\">","</span>","</span>","<br />","<img src=\"","\"/>","<a target=\"_blank\" class=\"interfaceLink\" href=\"","\">LINK</a>",'script','script');

		$content = str_replace($bbCode,$htmldCode,$resA['content']);
	}
	$user = PG::getSomething($_SESSION['pgID'],'username');
	$date = date('d/m/Y');
	
	

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Star Trek: Federation Online GDR');
$pdf->SetTitle("$title - Star Trek: Federation - Database ($catName)");

// set default header data
$pdf->SetHeaderData('logo.jpg', 30, "$title - Database ($catName)","Generato da $user il $date");
$pdf->SetFont('Helvetica');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT-5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT-5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// ---------------------------------------------------------

// set font
// add a page
$pdf->AddPage();
$pdf->writeHTML($content, true, false, true, false, '');

		
	
	
	
	if(is_file($fileName)) unlink($fileName);
	$pdf->Output($fileName,"F");
	unset($pdf);
	
	$size = filesize($fileName);//calcola dimensione del file 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Cache-Control: private');
header('Pragma: no-cache');
header("Content-Transfer-Encoding: binary");
header("Content-length: {$size}");
header("Content-type: application/pdf");
$tit = $title.'.pdf';
header("Content-disposition: attachment; filename=\"{$tit}\"");
readfile($fileName);

header("Location: db.php?element=$elementID");
}

if(isSet($_GET['cat']))
{
$id = $vali->numberOnly($_GET['cat']);
$cat = mysql_query("SELECT catID,catName,catImage,ordering,coloring FROM db_cats WHERE catID = $id");

if ($resA = mysql_fetch_array($cat)) 
	{
		$ele = mysql_query("SELECT ID,title,tag,classTag,type FROM db_elements WHERE catID = $id ORDER BY iorder DESC,".$resA['ordering']);
		$template = new PHPTAL('TEMPLATES/db_cat.htm');
		$template->catName = $resA['catName'];
		$template->catImage = $resA['catImage'];
		$template->catID = $resA['catID'];
		
		$template->colorer = $resA['coloring'];
		$elements = array('IMPORTANT' => array(),'OFF' => array(),'NORMAL' => array());
		while($resE = mysql_fetch_array($ele))
		$elements[$resE['type']][] = $resE;
		$template->elements = $elements;
		
	}
else {header('Location:db.php'); exit;}
}

else if(isSet($_GET['element']))
{
	$id = $vali->numberOnly($_GET['element']);
	$cat = mysql_query("SELECT db_cats.catID,catName,ID,catImage,title,content,skipBB,crosslink,totallink FROM db_cats,db_elements WHERE db_cats.catID = db_elements.catID AND ID = $id");
	
	
	if(isSet($_SESSION['pgID'])){
		
		mysql_query("SELECT 1 FROM db_elements WHERE ID = $id AND lvisit = ".$_SESSION['pgID']);
		echo mysql_error();
		if(!mysql_affected_rows()) mysql_query("UPDATE db_elements SET visits = visits+1, lvisit = ".$_SESSION['pgID']." WHERE ID = $id");
	}
	if ($resA = mysql_fetch_array($cat))
	{
		if($resA['crosslink'] != NULL) header("Location:db.php?element=".$resA['crosslink']);
		elseif($resA['totallink'] != NULL) header("Location:".$resA['totallink']);
		$template = new PHPTAL('TEMPLATES/db_element.htm');
		$template->ID = $resA['ID'];
		$template->catID = $resA['catID'];
		$template->catName = $resA['catName'];
		$template->catImage = $resA['catImage'];
		$template->title = $resA['title'];
		$template->content = ($resA['skipBB']) ? $resA['content'] : str_replace($bbCode,$htmlCode,$resA['content']);
		$template->searchable = (isSet($_SESSION['pgID'])) ? true : false;

	}
	else {header('Location:db.php'); exit;}
}

else if(isSet($_GET['searcher']))
{
if(!isSet($_SESSION['pgID'])) header('Location:db.php');
$mode=$_POST['mode'];
$sectionFilter = isSet($_POST['section']) ? $sectionFilter = "AND catID = ".$vali->numberOnly($_POST['section']): '';
$keyer = addslashes(str_replace(array('+','-'),array('',''),$_POST['key']));
if($mode==0){$qstring = $keyer;}
if($mode==2){$qstring = '"'.$keyer.'"';}
if($mode==1){
$qstring='';
$key = explode(' ',$keyer);
foreach($key as $ale){$qstring .= '+'.$ale.' ';}
}
$ele = mysql_query("SELECT ID, title,tag,type, (MATCH (content) AGAINST ('$qstring' IN BOOLEAN MODE)) AS priority, (MATCH (title) AGAINST ('$qstring' IN BOOLEAN MODE)) AS titlePrio FROM db_elements WHERE MATCH (content) AGAINST ('$qstring' IN BOOLEAN MODE) $sectionFilter ORDER BY titlePrio DESC, priority");

		$template = new PHPTAL('TEMPLATES/db_cat.htm');
		$template->catName = "RICERCA DI \"".strtoupper($keyer)."\"";
		$template->catImage = '';
		$template->catID = isSet($_POST['section']) ? $vali->numberOnly($_POST['section']) : '';
		$elements = array('IMPORTANT' => array(),'OFF' => array(),'NORMAL' => array());
		while($resE = mysql_fetch_array($ele))
		$elements[$resE['type']][] = $resE;
		
		$template->elements = $elements;
		$template->colorer = "interfaceLinkBlue";
		$template->searcher = true;

}


else if(isSet($_GET['shipRegister']))
{
	$template = new PHPTAL('TEMPLATES/db_shipRegister.htm');
	$cat = mysql_query("SELECT * FROM fed_ships,fed_ships_classes,fed_ships_fleets WHERE fleetno = fleet AND fed_ships_classes.class = fed_ships.class ORDER BY name");
	$shipsFleet = array();
	while($res = mysql_fetch_array($cat)){
		if (!isSet($shipsFleet[$res['fleet']])) $shipsFleet[$res['fleet']] = array();
		$shipsFleet[$res['fleet']][] = $res;
		if($res['admiral']) $admirals[$res['fleet']] = $res;
	} 
	
	ksort($shipsFleet);
	$template->shipsFleet = $shipsFleet;
	$template->admirals = $admirals;
 
	$cat = mysql_query("SELECT * FROM fed_ships,fed_ships_classes,fed_ships_fleets WHERE fleetno = fleet AND fed_ships_classes.class = fed_ships.class  ORDER BY name");
	$shipsClasses = array();
	while($res = mysql_fetch_array($cat)){
		$neu = str_replace(' ','-',$res['class']);
		if (!isSet($shipsClasses[$neu])) $shipsFleet[$neu] = array();
		$shipsClasses[$neu][] = $res; 
	} 
	
	ksort($shipsClasses);
	$template->shipsClasses = $shipsClasses; 
	
	$cat = mysql_query("SELECT * FROM fed_ships,fed_ships_classes,fed_ships_fleets WHERE fleetno = fleet AND fed_ships_classes.class = fed_ships.class  ORDER BY name");
	$shipsLetters = array();
	while($res = mysql_fetch_array($cat)){
		$namae = explode('U.S.S. ',$res['name']);
		
		if (!isSet($shipsLetters[strtoupper(substr($namae[1],0,1))])) $shipsFleet[strtoupper(substr($namae[1],0,1))] = array();
		$shipsLetters[strtoupper(substr($namae[1],0,1))][] = $res; 
	} 
	
	ksort($shipsLetters);
	$template->shipsLetters = $shipsLetters; 
	
	
	$cat = mysql_query("SELECT * FROM fed_ships,fed_ships_classes,fed_ships_fleets WHERE fleetno = fleet AND fed_ships_classes.class = fed_ships.class  ORDER BY name");
	$shipsTypes = array();
	while($res = mysql_fetch_array($cat)){
		$neu = str_replace(' ','-',$res['descript']);
		if (!isSet($shipsTypes[$neu])) $shipsTypes[$neu] = array();
		$shipsTypes[$neu][] = $res; 
	} 
	$template->shipsTypes = $shipsTypes; 
	
	
}

else
{
$template = new PHPTAL('TEMPLATES/db_index.htm');
$cat = mysql_query("SELECT catID,catName,catImage FROM db_cats ORDER BY catID");
$categories = array();

while ($c = mysql_fetch_array($cat))
	$categories[] = $c;
	
$topVis = mysql_query("SELECT ID,title,tag FROM db_elements ORDER BY visits DESC, title ASC LIMIT 6");
$topper = array();

while ($c = mysql_fetch_array($topVis))
{	
	$titler = (strlen($c['title']) > 35) ? substr($c['title'],0,35).'...' : $c['title'];
	$title = $c['title'];
	$tasser = array('ID' => $c['ID'], 'title'=>	$titler,'rtitle' => $title,'tag' => $c['tag']);
	$topper[] = $tasser;
}	
$lasts = mysql_query("SELECT ID,title,tag FROM db_elements WHERE crosslink IS NULL ORDER BY ID DESC LIMIT 5");
$laster = array();

while ($c = mysql_fetch_array($lasts))
{	
	$titler = (strlen($c['title']) > 35) ? substr($c['title'],0,35).'...' : $c['title'];
	$title = $c['title'];
	$tasser = array('ID' => $c['ID'], 'title'=>	$titler,'rtitle' => $title,'tag' => $c['tag']);
	$laster[] = $tasser;
}
	
$template->topper = $topper;
$template->laster = $laster;
$template->categories = $categories;
}

$template->searchable = (isSet($_SESSION['pgID'])) ? true : false;
//$template->user = $currentUser;
//$template->currentDate = $currentDate;
//$template->currentStarDate = $currentStarDate;
//$template->gameName = $gameName;
//$template->gameVersion = $gameVersion;
//$template->debug = $debug;
//$template->gameServiceInfo = $gameServiceInfo;

	try 
	{
		echo $template->execute();
	}
	catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

 

?>