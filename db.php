<?php
session_start();

include('includes/app_include.php');
include('includes/validate_class.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 
include("includes/md/Parsedown.php"); //NEW 

include('includes/cdbClass.php');
include("includes/md/ParsedownExtra.php"); //NEW 

function numberToRomanRepresentation($number) {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}


if(isSet($_SESSION['pgID'])) PG::updatePresence($_SESSION['pgID']);
//else {header('Location:index.php'); exit;}
$vali = new validator();


if(isSet($_GET['dopdf']))
{	
	exit;

	if(!isSet($_SESSION['pgID'])) header('Location:index.php');
	require('includes/tcpdf/tcpdf.php');
	
	$elementID = $vali->numberOnly($_GET['dopdf']);

	$fileName='tmp_dbDispensa_'.$elementID.".pdf";
	
	$cat = mysql_query("SELECT db_cats.catID,catName,catImage,title,content FROM db_cats,db_elements WHERE db_cats.catID = db_elements.catID AND ID = $elementID");
	if ($resA = mysql_fetch_array($cat))
	{
		$catID = $resA['catID'];
		$catName = $resA['catName'];
		$catImage = $resA['catImage'];
		$title = $resA['title'];
		$content = CDB::bbcode($resA['content']);
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
	$pdf->Output(__DIR__.$fileName,"F");
	unset($pdf);
	
	$size = filesize(__DIR__ .$fileName);//calcola dimensione del file 
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
readfile(__DIR__.$fileName);

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


else if (isSet($_GET['adiDo']))
{
	if(!PG::verifyOMA($_SESSION['pgID'],'A')){header('Location:db.php'); exit;}	
	
	$IDF = addslashes($_POST['IDF']);
	$tag = addslashes($_POST['tag']);
	$title = addslashes($_POST['title']);
	$content = addslashes($_POST['content']);
	$entryType = addslashes($_POST['entryType']);
	$formatType = addslashes($_POST['formatType']);
	$crossLink = $vali->numberOnly(addslashes($_POST['crossLink']));
	$catID = $vali->numberOnly(addslashes($_POST['catID']));


	$sBB = '0'; $eMD = '0';
	if($formatType == "2"){$sBB = '1'; $eMD = '0';}
	if($formatType == "3"){$sBB = '0'; $eMD = '1';}

	
	mysql_query("INSERT INTO db_elements (IDF,catID,title,content,crosslink,type,tag,skipBB,enableMD) VALUES ('$IDF','$catID','$title','$content','$crossLink','$entryType','$tag','$sBB','$eMD')");


	if (mysql_error()){
		echo mysql_error();exit;
	}

	$resa = mysql_fetch_assoc(mysql_query("SELECT ID FROM db_elements ORDER BY ID DESC LIMIT 1"));

	if (mysql_error()){
		echo mysql_error();exit;
	}
	

	header('Location:db.php?element='.$resa['ID']);
}

else if (isSet($_GET['ediDo']))
{
	if(!PG::verifyOMA($_SESSION['pgID'],'A')){header('Location:db.php'); exit;}	
	$ID = $vali->numberOnly(addslashes($_GET['ediDo']));
	$IDF = addslashes($_POST['IDF']);
	$tag = addslashes($_POST['tag']);
	$title = addslashes($_POST['title']);
	$content = addslashes($_POST['content']);
	$entryType = addslashes($_POST['entryType']);
	$formatType = addslashes($_POST['formatType']);
	$crossLink = $vali->numberOnly(addslashes($_POST['crossLink']));
	$catID = $vali->numberOnly(addslashes($_POST['catID']));


	$ft = 'skipBB = 0, enableMD = 0';
	if($formatType == "2") $ft = 'skipBB = 1, enableMD = 0';
	if($formatType == "3") $ft = 'skipBB = 0, enableMD = 1';

	
	mysql_query("UPDATE db_elements SET IDF = '$IDF', catID = '$catID', title = '$title', content = '$content', crosslink = '$crossLink', type = '$entryType', tag = '$tag', $ft WHERE ID = '$ID'");
if (mysql_error()){
		echo mysql_error();exit;
	}
	header('Location:db.php?element='.$ID);
}

else if(isSet($_GET['delelement'])){


	if(!isSet($_SESSION['pgID']) || !PG::verifyOMA($_SESSION['pgID'],'A')){header('Location:db.php'); exit;}	
	$ID = $vali->numberOnly(addslashes($_GET['delelement']));

	mysql_query("UPDATE db_elements SET IDF = '', catID = '9999' WHERE ID = '$ID'");

	header('Location:db.php');


}


else if(isSet($_GET['neuelement'])){
	
	

	if(!PG::verifyOMA($_SESSION['pgID'],'A')){header('Location:db.php'); exit;}	

	$cats = mysql_query("SELECT catID,catName FROM db_cats");
		 
	$Rcats= array();
	while($resA = mysql_fetch_assoc($cats)) $Rcats[] = $resA;
	
	$template = new PHPTAL('TEMPLATES/db_element_add.html');
	$template->cats = $Rcats; 
}


else if(isSet($_GET['edielement'])){
	
	$id = addslashes($_GET['edielement']);



	if(!PG::verifyOMA($_SESSION['pgID'],'A')){header('Location:db.php'); exit;}	


	$cats = mysql_query("SELECT catID,catName FROM db_cats");
	$Rcats= array();
	while($resA = mysql_fetch_assoc($cats)) $Rcats[] = $resA;


	$cat = mysql_query("SELECT db_cats.catID,catName,ID,tag,IDF,catImage,coloring,title,content,skipBB,crosslink,totallink,enableMD FROM db_cats,db_elements WHERE db_cats.catID = db_elements.catID AND ID = '$id'");
		 
	if ($resA = mysql_fetch_array($cat))
	{
		$template = new PHPTAL('TEMPLATES/db_element_edit.html');
		$template->resA = $resA; 
		$template->cats = $Rcats; 

	}
	else {header('Location:db.php'); exit;}

}

else if(isSet($_GET['element']) || isSet($_GET['litref']))
{

	$id = (isSet($_GET['element'])) ? $_GET['element'] : ( isSet($_GET['litref']) ? $_GET['litref'] : '');
	$idf = (isSet($_GET['element'])) ? 'ID' : ( isSet($_GET['litref']) ? 'IDF' : '');

	$id = addslashes($id);

	$cat = mysql_query("SELECT db_cats.catID,catName,ID,catImage,title,content,skipBB,crosslink,totallink,enableMD FROM db_cats,db_elements WHERE db_cats.catID = db_elements.catID AND $idf = '$id'");
		
	$Parsedown = new ParsedownExtra();
	

	if(isSet($_SESSION['pgID'])){
		
		mysql_query("SELECT 1 FROM db_elements WHERE $idf = '$id' AND lvisit = ".$_SESSION['pgID']);
		echo mysql_error();
		if(!mysql_affected_rows()) mysql_query("UPDATE db_elements SET visits = visits+1, lvisit = ".$_SESSION['pgID']." WHERE $idf = '$id'");
	}

	if ($resA = mysql_fetch_array($cat))
	{
		if($resA['crosslink'] != NULL) 
			if($resA['crosslink'] != '0')
				header("Location:db.php?element=".$resA['crosslink']);

		if($resA['totallink'] != NULL) header("Location:".$resA['totallink']);
		$template = new PHPTAL('TEMPLATES/db_element.htm');
		$template->ID = $resA['ID'];
		$template->catID = $resA['catID'];
		$template->catName = $resA['catName'];
		$template->catImage = $resA['catImage'];
		$template->title = $resA['title'];
		$template->content = ($resA['skipBB']) ? $resA['content'] : (($resA['enableMD']) ? CDB::bbcode($Parsedown->text($resA['content']),NULL,NULL,NULL,1) : CDB::bbcode($resA['content'],NULL,NULL,"\n",1)) ; 
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
$ele = mysql_query("SELECT ID, title,tag,type, (MATCH (content) AGAINST ('$qstring' IN BOOLEAN MODE)) AS priority, (MATCH (title) AGAINST ('$qstring' IN BOOLEAN MODE)) AS titlePrio FROM db_elements WHERE catID < 50 AND MATCH (content) AGAINST ('$qstring' IN BOOLEAN MODE) $sectionFilter ORDER BY titlePrio DESC, priority");

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


else if(isSet($_GET['shipRegister']) || isSet($_GET['shipRegisterNF']))
{

	if (isSet($_GET['shipRegister']))
	{
		$shipQuery="SELECT * FROM fed_ships,fed_ships_classes,fed_ships_fleets WHERE fleetno = fleet AND fed_ships_classes.class = fed_ships.class AND fleetno<=50 ORDER BY name";
		$template = new PHPTAL('TEMPLATES/db_shipRegister.htm');

	}
	elseif(isSet($_GET['shipRegisterNF']))
	{
		$shipQuery="SELECT * FROM fed_ships,fed_ships_classes,fed_ships_fleets WHERE fleetno = fleet AND fed_ships_classes.class = fed_ships.class AND fleetno>50 ORDER BY name";
		$template = new PHPTAL('TEMPLATES/db_shipRegisterNF.htm');

	}

	$cat = mysql_query($shipQuery);
	 
	$shipsFleet = array();
	while($res = mysql_fetch_assoc($cat)){
		
		$romanFleet = numberToRomanRepresentation((int)($res['fleet']));
		if (!isSet($shipsFleet[$romanFleet])) $shipsFleet[$romanFleet] = array();

		$shipsFleet[$romanFleet][] = $res;

		if($res['admiral']) $admirals[$romanFleet] = $res;
	} 
	
	ksort($shipsFleet);
	//echo "<HTML><pre>".print_r($shipsFleet["I"][0]['ouniform'])."</pre></HTML>";exit;
	$template->shipsFleet = $shipsFleet;
	$template->admirals = $admirals;
 
	$cat = mysql_query($shipQuery);
	$shipsClasses = array();
	while($res = mysql_fetch_array($cat)){
		$neu = str_replace(' ','-',$res['class']);
		if (!isSet($shipsClasses[$neu])) $shipsFleet[$neu] = array();

		$res['romanFleet'] = numberToRomanRepresentation((int)($res['fleetno']));
		$shipsClasses[$neu][] = $res; 
	} 
	
	ksort($shipsClasses);
	$template->shipsClasses = $shipsClasses; 
	
	$cat = mysql_query($shipQuery);
	$shipsLetters = array();
	while($res = mysql_fetch_array($cat)){
		if(strstr($res['name'],'U.S.S.')) $namae = explode('U.S.S. ',$res['name']);
		elseif(strstr($res['name'],'R.T.S.')) $namae = explode('R.T.S. ',$res['name']);
		elseif(strstr($res['name'],'N.F.S.')) $namae = explode('N.F.S. ',$res['name']);
		else{
			echo $res['name'];exit;
		}
		
		if (!isSet($shipsLetters[strtoupper(substr($namae[1],0,1))])) $shipsFleet[strtoupper(substr($namae[1],0,1))] = array();
		$res['romanFleet'] = numberToRomanRepresentation((int)($res['fleetno']));
		$shipsLetters[strtoupper(substr($namae[1],0,1))][] = $res; 
	} 
	
	ksort($shipsLetters);
	$template->shipsLetters = $shipsLetters; 
	
	
	$cat = mysql_query($shipQuery);
	$shipsTypes = array();
	while($res = mysql_fetch_array($cat)){
		$neu = str_replace(' ','-',$res['descript']);
		if (!isSet($shipsTypes[$neu])) $shipsTypes[$neu] = array();
		$res['romanFleet'] = numberToRomanRepresentation((int)($res['fleetno']));
		$shipsTypes[$neu][] = $res; 
	} 
	$template->shipsTypes = $shipsTypes; 
	
	
}

else
{
$template = new PHPTAL('TEMPLATES/db_index.htm');
$cat = mysql_query("SELECT catID,catName,catImage FROM db_cats WHERE catID < 1000 ORDER BY catID");
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
$lasts = mysql_query("SELECT ID,title,tag,brief FROM db_elements WHERE (crosslink IS NULL OR crossLink = 0) ORDER BY ID DESC LIMIT 4");
$laster = array();

while ($c = mysql_fetch_array($lasts))
{	
	$titler = (strlen($c['title']) > 25) ? substr($c['title'],0,25).'...' : $c['title'];
	$title = $c['title'];
	$tasser = array('ID' => $c['ID'], 'title'=>	$titler,'rtitle' => $title,'tag' => $c['tag'],'brief'=>$c['brief']);
	$laster[] = $tasser;
}
	
$template->topper = $topper;
$template->laster = $laster;
$template->categories = $categories;
}

if (isSet($_SESSION['pgID']) && PG::verifyOMA($_SESSION['pgID'],'A')){$template->isAdmin = true;}

$template->searchable = (isSet($_SESSION['pgID'])) ? true : false;
//$template->user = $currentUser;
//$template->currentDate = $currentDate;
//$template->currentStarDate = $currentStarDate;
//$template->gameName = $gameName;
//$template->gameVersion = $gameVersion;
//$template->debug = $debug;
//$template->gameServiceInfo = $gameServiceInfo;


$template->description = "Star Trek: Federation è un GDR Play By Chat di fantascienza, ambientato nell'anno 2396 e ispirato all'ambientazione di Star Trek. Vivi l'avventura di creare, costruire e giocare il tuo personaggio: sali a bordo e... Via! Si parte verso nuove avventure!";
$template->metaKeywords = "Star Trek, PBC, Gioco di Ruolo,Star Trek: Federation, Flotta Stellare, Federazione, Classe Intrepid, USS Endeavour, GDR, Play By Chat, Navi stellari, Borg";
$template->gameOptions = $gameOptions;


	try 
	{
		echo $template->execute();
	}
	catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

 

?>