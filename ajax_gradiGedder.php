<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');

$term = addslashes($_GET['term']);

if (isSet($_GET['sezioni'])) $q = "SELECT DISTINCT Rsezione as pTerm FROM pg_ranks WHERE Rsezione LIKE '$term%'";
if (isSet($_GET['divisioni'])) $q = "(SELECT incDivisione as pTerm FROM pg_incarichi WHERE incDivisione LIKE '$term%') UNION DISTINCT  (SELECT pngDivisione as pTerm FROM png_incarichi WHERE pngDivisione LIKE '$term%')";


else if (isSet($_GET['dipartimenti'])) $q = "(SELECT incDipartimento as pTerm FROM pg_incarichi WHERE incDipartimento LIKE '$term%') UNION DISTINCT  (SELECT pngDipartimento as pTerm FROM png_incarichi WHERE pngDipartimento LIKE '$term%')";



$res = mysql_query($q); 
$aar = array();
while ($row = mysql_fetch_array($res)) {
$aar[] = $row['pTerm'];
}
echo json_encode($aar); 
?>