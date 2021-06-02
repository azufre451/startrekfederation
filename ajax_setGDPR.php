<?php
session_start();
if (!isSet($_SESSION['pgID'])){exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
$vali = new Validator();

$person=$vali->numberOnly($_SESSION['pgID']);

$PG = new PG($person);

mysql_query("UPDATE pg_users SET pgGDPR=1 WHERE pgID = $person");

$PG->addNote('Approvazione GDPR');

echo json_encode(array("status"=>'OK'));
exit;

?>						