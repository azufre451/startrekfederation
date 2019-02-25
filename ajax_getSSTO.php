<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');

$vali = new validator();


$unit = addslashes($_POST['a_unit']);
$year = addslashes($_POST['a_year']);
$pgid = addslashes($_POST['a_pgid']);
$yearL = explode('-',$year);
$yearN = $vali->numberOnly($yearL[0]);


$res = mysql_query("(SELECT DISTINCT pgUser,rankImage,pg_user_stories.pgID as pgID,pg_user_stories.what as what FROM pg_users,pg_user_stories WHERE pg_user_stories.pgID <> '$pgid' AND YEAR(dater) = '$yearN' AND pg_user_stories.pgID = pg_users.pgID AND UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(wherer),'\'',''),'.',''),',',''),'-','_'),' ','_')) = UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM('$unit'),'\'',''),'.',''),',',''),'-','_'),' ','_')) GROUP BY pgUser,pgID ORDER BY pgUser ASC)");
echo mysql_error();
$aar = array();

while ($row = mysql_fetch_array($res))
{
	$aar[] = array('pgUser' => $row['pgUser'],'ordinaryUniform'=> $row['rankImage'],'pgID' => $row['pgID'],'what' => $row['what']);
}
echo json_encode($aar);
//echo var_dump($aar);
?>