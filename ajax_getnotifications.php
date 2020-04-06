<?php
session_start();
if (!isSet($_SESSION['pgID'])){header("Location:login.php"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');

function cmp($a, $b)
{
    if ($a['timer'] == $b['timer']) {
        return 0;
    }
    return ($a['timer'] > $b['timer']) ? -1 : 1;
}


$noti=array(); 
//$currentAssignQ = mysql_fetch_assoc(mysql_query("SELECT pgAssign FROM pg_users WHERE pgID = ".$_SESSION['pgID']));
//$currentAssign=$currentAssignQ['pgAssign'];


/* PRESTIGIO */

$lastVars = mysql_query("SELECT pg_prestige_stories.*, pg_users.pgID,pg_users.pgUser,pg_users.pgAvatarSquare, pg_users.pgSesso FROM pg_prestige_stories,pg_users WHERE owner=pgID ORDER BY time DESC LIMIT 5");
while($lastVarsRes=mysql_fetch_assoc($lastVars))
{
	$sexParticle= ( $lastVarsRes['pgSesso'] == 'M') ? 'o' : 'a';
	
	if($lastVarsRes['variation'] >= 0) {
		$directionality = 'più';
		$directionalityPreamble='<span style="color:#5cec15; font-weight:bold">+';
	}
	else{
		$directionality = 'meno';
		$directionalityPreamble='<span style="color:#ec1f15; font-weight:bold">';
	}

	$texto=  $lastVarsRes['pgUser'].' è diventat'.$sexParticle.' '.$directionality.' famos'.$sexParticle;
	$textoLittle=  '['.$directionalityPreamble.$lastVarsRes['variation'].'</span>] - ' . $lastVarsRes['reason'];

	$noti[] = array('image'=>$lastVarsRes['pgAvatarSquare'], 'text'=> $texto,'subtext' => $textoLittle,'uri'=>$lastVarsRes['pgID'],'timer'=>$lastVarsRes['time'],'dater'=>date('d/m', $lastVarsRes['time']),'opener'=>'schedaPOpen');

	mysql_query("INSERT IGNORE INTO pg_visualized_elements (type,what,pgID,time) VALUES ('PRESTIGE',".$lastVarsRes['recID'].",".$_SESSION['pgID'].",$curTime) ");

} 




$lastVars = mysql_query("SELECT * FROM fed_news WHERE aggregator = 'FED' ORDER BY newsTime DESC LIMIT 5");

while($lastVarsRes=mysql_fetch_assoc($lastVars)){

	$texto=  '<b>News:</b> '. $lastVarsRes['newsTitle'];
	$textoLittle =  substr($lastVarsRes['newsText'],0,100).'...';
	$noti[] = array('image'=>'https://oscar.stfederation.it/SigmaSys/logo/te_logo2.png', 'text'=> $texto,'subtext' => $textoLittle,'timer'=>$lastVarsRes['newsTime'],'uri'=>$lastVarsRes['newsID'],'dater'=>date('d/m', $lastVarsRes['newsTime']),'opener'=>'tribuneOpen');


	mysql_query("INSERT IGNORE INTO pg_visualized_elements (type,what,pgID,time) VALUES ('NEWS',".$lastVarsRes['newsID'].",".$_SESSION['pgID'].",$curTime) ");

} 




$lastVars = mysql_query("SELECT fed_master_news.*,placeLogo FROM fed_master_news,pg_places WHERE placeID=place AND placeID IN (SELECT pgPlace FROM pg_incarichi WHERE  pgID = ".$_SESSION['pgID'].") ORDER BY time DESC LIMIT 5");

while($lastVarsRes=mysql_fetch_assoc($lastVars)){

	$texto=  '<b>Evento Master:</b> '. $lastVarsRes['title'];
	$textoLittle =  substr($lastVarsRes['content'],0,100).'...';
	$noti[] = array('image'=>'TEMPLATES/img/logo/'.$lastVarsRes['placeLogo'], 'text'=> $texto,'subtext' => $textoLittle,'timer'=>$lastVarsRes['time'],'uri'=>'0','dater'=>date('d/m', $lastVarsRes['time']),'opener'=>'cdbOpen');

		mysql_query("INSERT IGNORE INTO pg_visualized_elements (type,what,pgID,time) VALUES ('MASTEREVENTS',".$lastVarsRes['recID'].",".$_SESSION['pgID'].",$curTime) ");


}

$limitL = time() - 1296000; 
$lastVars = mysql_query("SELECT * FROM pg_personal_notifications WHERE time > $limitL AND owner IN (".$_SESSION['pgID'].",6) ORDER BY time DESC ");

while($lastVarsRes=mysql_fetch_assoc($lastVars)){

	$texto=  $lastVarsRes['text'];
	$noti[] = array('image'=>$lastVarsRes['image'], 'text'=> $texto,'subtext' => $lastVarsRes['subtext'],'timer'=>$lastVarsRes['time'],'uri'=>$lastVarsRes['URI'],'dater'=>date('d/m', $lastVarsRes['time']),'opener'=>$lastVarsRes['linker']);

	mysql_query("INSERT IGNORE INTO pg_visualized_elements (type,what,pgID,time) VALUES ('NOTIFS',".$lastVarsRes['recID'].",".$_SESSION['pgID'].",$curTime) ");

}



usort($noti, "cmp");
$aar=array('notifications'=>$noti);
echo json_encode($aar);
?>