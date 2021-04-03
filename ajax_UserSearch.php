<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');

$disc=(isSet($_GET['filter'])) ? $_GET['filter'] : '';
$term = addslashes(@$_GET['term']);
$aar = array();

if($disc=="PID"){
    /* $res = mysql_query("SELECT pgUser AS value, pgID AS PUD, pgAvatarSquare as IMA FROM pg_users WHERE pgUser LIKE '$term%' LIMIT 20"); */

    $pgID=$_SESSION['pgID'];
        
        $locQuery = mysql_query(
            "SELECT 
                pgLocation, 
                pointerL
            FROM
                pg_places, pg_users 
            WHERE 
                pgLocation = placeID AND pgID = $pgID
            ");
        $pgLocations = mysql_fetch_array($locQuery);
        
        $res = mysql_query(
            "(SELECT 
                pgUser AS value, 
                pgID AS PUD, 
                pgAvatarSquare AS IMA,
                'person' AS entryType
            FROM 
                pg_users 
            WHERE 
                pgUser LIKE '$term%')
            UNION
            (SELECT 
                locName AS value,
                locID AS PUD,
                placeLogo AS IMA,
                'place' AS entryType
            FROM 
                pg_places, fed_ambient 
            WHERE 
                ambientLocation = placeID 
                AND ambientType NOT IN ('DEFAULT')
                AND locName LIKE '%$term%'
                AND (placeID = '{$pgLocations['pgLocation']}'
                    OR attracco = '{$pgLocations['pgLocation']}'
                    OR (pointerL = '{$pgLocations['pointerL']}' AND pointerL <> '')))  
            LIMIT 20");

    while($row = mysql_fetch_array($res)) {
        if($row['entryType'] == 'person') {
            $aar[] = array('data'=>$row,'mode'=>'view','value'=> $row['value']) ;
            $aar[] = array('data'=>$row,'mode'=>'DPadd','value'=> $row['value']) ;
            if (PG::verifyOMA($_SESSION['pgID'],'M')){
                $aar[] = array('data'=>$row,'mode'=>'Master','value'=> $row['value']);
                $aar[] = array('data'=>$row,'mode'=>'Stato-Servizio','value'=> $row['value']);
                if (PG::verifyOMA($_SESSION['pgID'], 'A')){
                    $aar[] = array('data'=>$row,'mode'=>'Admin','value'=> $row['value']);
                }
            }
        }
        else { $aar[] = array('data'=>$row,'mode'=>'Luogo','value'=> $row['value']); }
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