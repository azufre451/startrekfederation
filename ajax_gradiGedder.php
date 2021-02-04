<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');

$term = trim(addslashes($_POST['term']));
$desiredPar=$_POST['desiredPar'];


$selected_place = $_POST['place'];
$selected_section = ($desiredPar != 'sezioni') ? $_POST['section']: '';
$selected_dep = ($desiredPar != 'dipartimenti') ? $_POST['dep']: '';
$selected_div = ($desiredPar != 'divisioni') ? $_POST['div']: '';
$selected_group = ($desiredPar != 'gruppi') ? $_POST['group']: '';
$selected_incarico = ($desiredPar != 'incarichi') ? $_POST['incarico']: '';

$ctlPG_place = ($selected_place != '') ? " AND pgPlace = '". addslashes($selected_place)."'" : '';
$ctlPNG_place = ($selected_place != '') ? " AND pngPlace = '". addslashes($selected_place)."'" : '';

$ctlPG_section = ($selected_section != '') ? " AND incSezione = '". addslashes($selected_section)."'" : '';
$ctlPNG_section = ($selected_section != '') ? " AND pngSezione = '". addslashes($selected_section)."'" : '';

$ctlPG_div = ($selected_div != '') ? " AND incDivisione = '". addslashes($selected_div)."'" : '';
$ctlPNG_div = ($selected_div != '') ? " AND pngDivisione = '". addslashes($selected_div)."'" : '';

$ctlPG_dep = ($selected_dep != '') ? " AND incDipartimento = '". addslashes($selected_dep)."'" : '';
$ctlPNG_dep = ($selected_dep != '') ? " AND pngDipartimento = '". addslashes($selected_dep)."'" : '';

$ctlPG_group = ($selected_group != '') ? " AND incGroup = '". addslashes($selected_group)."'" : '';
$ctlPNG_group = ($selected_group != '') ? " AND pngIncGroup = '". addslashes($selected_group)."'" : '';

$ctlPG_incarico = ($selected_incarico != '') ? " AND incIncarico = '". addslashes($selected_incarico)."'" : '';
$ctlPNG_incarico = ($selected_incarico != '') ? " AND pngIncarico = '". addslashes($selected_incarico)."'" : '';


$ctlPG = $ctlPG_place . $ctlPG_section . $ctlPG_div . $ctlPG_dep . $ctlPG_group . $ctlPG_incarico;
$ctlPNG = $ctlPNG_place . $ctlPNG_section . $ctlPNG_div . $ctlPNG_dep . $ctlPNG_group . $ctlPNG_incarico;

if ($desiredPar == 'sezioni'){

	$q = "(SELECT incSezione as pTerm FROM pg_incarichi WHERE incSezione LIKE '%$term%' $ctlPG) UNION DISTINCT  (SELECT pngSezione as pTerm FROM png_incarichi WHERE pngSezione LIKE '%$term%' $ctlPNG) ORDER BY pTerm";
}

if ($desiredPar == 'divisioni'){

	$q = "(SELECT incDivisione as pTerm FROM pg_incarichi WHERE incDivisione LIKE '%$term%' $ctlPG) UNION DISTINCT  (SELECT pngDivisione as pTerm FROM png_incarichi WHERE pngDivisione LIKE '%$term%' $ctlPNG) ORDER BY pTerm";
}

if ($desiredPar == 'gruppi'){
	$q = "(SELECT incGroup as pTerm FROM pg_incarichi WHERE incGroup LIKE '$term%' $ctlPG) UNION DISTINCT  (SELECT pngIncGroup as pTerm FROM png_incarichi WHERE pngIncGroup LIKE '%$term%' $ctlPNG) ORDER BY pTerm";
}

if ($desiredPar == 'dipartimenti'){
	$q = "(SELECT incDipartimento as pTerm FROM pg_incarichi WHERE incDipartimento LIKE '%$term%' $ctlPG) UNION DISTINCT  (SELECT pngDipartimento as pTerm FROM png_incarichi WHERE pngDipartimento LIKE '%$term%' $ctlPNG) ORDER BY pTerm";
}
 
if ($desiredPar == 'incarichi'){
	$q = "(SELECT incIncarico as pTerm FROM pg_incarichi WHERE incIncarico LIKE '%$term%' $ctlPG) UNION DISTINCT  (SELECT pngIncarico as pTerm FROM png_incarichi WHERE pngIncarico LIKE '%$term%' $ctlPNG) ORDER BY pTerm";
}
  
$res = mysql_query($q); 
$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['pTerm'];
}
echo json_encode($aar); 
?>
 
