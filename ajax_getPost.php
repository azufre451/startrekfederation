<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$term = stf_real_escape($_POST['term']);
//$id = $_SESSION['pgID'];
//$user = new PG($_SESSION['pgID']);

$ra = mysql_fetch_array(mysql_query("SELECT content FROM cdb_posts WHERE topicID = $term ORDER BY time DESC LIMIT 1"));
$var = str_replace($bbCode,$htmlCode,$ra['content']);
$var = str_replace('<img','<img width="60px"',$var);
$arr['content'] = $var;
echo json_encode($arr);
//echo var_dump($aar);
?>