<?php
include('includes/app_include.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW

$res = mysql_query("SELECT * FROM db_elements");

$template = new PHPTAL('TEMPLATES/test2.htm');
$things= array();
while($rea = mysql_fetch_array($res))
{
$nam=$rea['ID'].":".$rea['title'];
$nam = addslashes($nam);
echo $nam;
mysql_query("INSERT INTO cdb_topics (topicTitle, topicLastTime, topicType,topicCat,	topicLastUser) VALUES ('$nam','1494882882','N','57','1');");
$rel = mysql_fetch_array(mysql_query("SELECT topicID FROM cdb_topics WHERE topicCat = 57 ORDER BY topicID DESC LIMIT 1"));
echo mysql_error();
$TID= $rel['topicID'];
$EID= $rea['ID'];
mysql_query("INSERT INTO cdb_posts (title,content,owner,coOwner,time,topicID) VALUES ('$nam',(SELECT content FROM db_elements WHERE ID = $EID),'1',598,'1494882882',$TID);");

echo mysql_error();
}
 
?>						