<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
$disc=(isSet($_GET['filter'])) ? $_GET['filter'] : '';
$term = addslashes(@$_GET['term']);
$aar = array();



if($disc=="PID"){
    $res = mysql_query("SELECT pgUser AS value, pgID AS PUD, pgAvatarSquare as IMA FROM pg_users WHERE pgUser LIKE '$term%' LIMIT 20");
    while($row = mysql_fetch_array($res)) {
        $aar[] = array('data'=>$row,'mode'=>'view','value'=> $row['value']) ;
        $aar[] = array('data'=>$row,'mode'=>'DPadd','value'=> $row['value']) ;

        if (PG::verifyOMA($_SESSION['pgID'],'M')){
            $aar[] = array('data'=>$row,'mode'=>'Master','value'=> $row['value']);
            $aar[] = array('data'=>$row,'mode'=>'Stato-Servizio','value'=> $row['value']);
        }
    }
}
else {
    if($disc=="PNG")
    $res = mysql_query("SELECT pgUser FROM pg_users WHERE png=1 AND pgUser LIKE '$term%' LIMIT 20");
    
    else if($disc == "PREST") $res= mysql_query("SELECT pgUser FROM pg_users WHERE pgID <> '".($_SESSION['pgID'])."' AND LOWER(pgOffAvatarC) = '".addslashes(strtolower($_POST['term2']))."' AND LOWER(pgOffAvatarN) = '".addslashes(strtolower($_POST['term1']))."'");
    
    else $res = mysql_query("SELECT pgUser FROM pg_users WHERE pgUser LIKE '$term%' LIMIT 20");
    while ($row = mysql_fetch_array($res)) {
    $aar[] = $row['pgUser'];
    }
}
echo json_encode($aar);
//echo var_dump($aar);
?>