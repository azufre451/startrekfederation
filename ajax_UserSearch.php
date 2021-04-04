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
    $curOMA = PG::getOMA($pgID);


    $pgLocations = mysql_fetch_assoc(mysql_query("SELECT pgLocation, pointerL FROM pg_places, pg_users WHERE pgLocation = placeID AND pgID = $pgID"));
    
    if(PG::mapPermissions('A',$curOMA))
    {
        $filterLocationList = "('DEFAULT')";
        $filtePG = '';
    }
    else
    {
        $filterLocationList = "('DEFAULT','SECRET')";
        $filtePG = "AND pgSSF = 0";
    }


        
    $res = mysql_query(
            "( SELECT pgUser AS value, pgID AS PUD, pgAvatarSquare AS IMA, 'person' AS entryType, '' as aux1
               FROM pg_users 
               WHERE pgUser LIKE '$term%' $filtePG
             ) UNION ALL
             ( SELECT placeName AS value, pointerL AS PUD, CONCAT('TEMPLATES/img/logo/',place_littleLogo1) AS IMA, 'planet' AS entryType, '' as aux1
               FROM pg_places 
               WHERE pointerL <> '' AND attracco = ''
               AND placeName LIKE '%$term%'
            ) UNION ALL 
            ( SELECT IF(type = 'NORMAL', CONCAT(tag,' - ',title),title) AS value, ID AS PUD, CONCAT('TEMPLATES/img/tips/',catImage) AS IMA, 'dbElement' AS entryType, '' as aux1
               FROM db_elements, db_cats
               WHERE db_elements.catID = db_cats.catID AND (title LIKE '%$term%' OR tag LIKE '%$term%')
            ) UNION ALL 
            ( SELECT locName AS value, locID AS PUD, IF(icon NOT LIKE '%i_generic.png' AND icon <> '',icon,CONCAT('TEMPLATES/img/logo/',placeLogo)) AS IMA, 'place' AS entryType, placeName as aux1
                FROM pg_places, fed_ambient 
                WHERE 
                    ambientLocation = placeID 
                    AND ambientType NOT IN $filterLocationList
                    AND locName LIKE '%$term%'
                    AND (placeID = '{$pgLocations['pgLocation']}'
                        OR attracco = '{$pgLocations['pgLocation']}'
                        OR (pointerL = '{$pgLocations['pointerL']}' AND pointerL <> ''))
            )  
            LIMIT 15");

    while($row = mysql_fetch_array($res)) {
        if($row['entryType'] == 'person') {
            $aar[] = array('data'=>$row, 'mode'=>'view', 'modeLabel'=> "Scheda PG" ) ;
            $aar[] = array('data'=>$row, 'mode'=>'dpadd', 'modeLabel'=> "DPadd") ;
            $aar[] = array('data'=>$row, 'mode'=>'pgAuthor', 'modeLabel'=> "Post in CDB") ;
            
            if (PG::mapPermissions('M',$curOMA)){
                $aar[] = array('data'=>$row,'mode'=>'master', 'modeLabel'=> "Scheda Master");
                $aar[] = array('data'=>$row,'mode'=>'ssto', 'modeLabel'=> "Stato Servizio");
                if (PG::mapPermissions('A',$curOMA)){
                    $aar[] = array('data'=>$row,'mode'=>'admin', 'modeLabel'=> "Scheda Admin");
                }
            }
        }
        elseif($row['entryType'] == 'place')
            $aar[] = array('data'=>$row,'mode'=>'place', 'modeLabel'=> "Chat");
        elseif($row['entryType'] == 'dbElement'){

            $row['value'] = ucwords(strtolower($row['value']));

            $aar[] = array('data'=>$row,'mode'=>'dbElement', 'modeLabel'=> "Database");
        }
        elseif($row['entryType'] == 'planet')
            $aar[] = array('data'=>$row,'mode'=>'charts', 'modeLabel'=> "Charts");
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