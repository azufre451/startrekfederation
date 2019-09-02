<?php
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
//include('includes/bbcode.php');




include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 


function cmp_topics($a, $b) { 
	
    if ($a == $b) {
      	return 0;
   }
   elseif($a == 'I'){
   	return -1;
   }
   elseif($b == 'I'){
   	return 1;
   }
   elseif($a == 'N'){
   	return 1;
   }
   elseif($b == 'N'){
   	return -1;
   }
   return (strstr($a,$b)) ? -1 : 1;
}

PG::updatePresence($_SESSION['pgID']);
$currentUser = new PG($_SESSION['pgID']);

$safeSeclarCats = array(14,15);

$limiL = $curTime - 2592000;
////if($currentUser->pgAssign != 'USS2'){ header("Location:main.php"); exit;}

if ($currentUser->pgAuthOMA == 'BAN'){header("Location:http://images1.wikia.nocookie.net/__cb20111112213451/naruto/images/f/f0/Sasuke.jpeg"); exit;}
$vali = new validator();

if(isSet($_POST['cdbCartellaCreate']))
{
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) //ALMENO UN MASTER
	{
		$title = 		  $vali->killChars(strtoupper(addslashes($_POST['cdbCartellaCreate'])));
		$colorExtension = $vali->killChars(htmlentities(addslashes($_POST['cdbColor'])));
		$seclar = $vali->numberOnly($_POST['cdbSeclar']);
		//$order = $vali->numberOnly($_POST['cdbOrder']);
		$type = ($_POST['cdbType'] == '') ? 'N' : strtoupper($vali->killChars(htmlentities(addslashes($_POST['cdbType']))));
		$type = str_replace('IMPORTANTI','I',$type);
		$cat = $vali->numberOnly($_POST['catCode']);
		
		
		mysql_query("INSERT INTO cdb_topics(topicTitle,topicLastTime,topicType,topicLastUser,topicCat,orderIndex,topicSeclar,topicColorExt)  VALUES('$title',".time().",'$type',".$_SESSION['pgID'].",$cat,'',$seclar,'$colorExtension')");
		header("Location:cdb.php?cat=$cat");
		exit;
	}
	
	else  //ALMENO UN MASTER
	{
		$title = $vali->killChars((addslashes($_POST['cdbCartellaCreate'])));
		$seclar = ($vali->numberOnly($_POST['cdbSeclar']) > $currentUser->pgSeclar) ? ($currentUser->pgSeclar) : ($vali->numberOnly($_POST['cdbSeclar']));
		$cat = $vali->numberOnly($_POST['catCode']); //TODO - SEC.P
		
		mysql_query("INSERT INTO cdb_topics(topicTitle,topicLastTime,topicType,topicLastUser,topicCat,orderIndex,topicSeclar,topicColorExt)  VALUES('$title',".time().",'N',".$_SESSION['pgID'].",$cat,0,$seclar,'')");
		header("Location:cdb.php?cat=$cat");
		exit;
	}
}
else if (isSet($_GET['getallgroups'])){
	$nan = $vali->numberOnly($_GET['getallgroups']);
	$ltp=array('IMPORTANTI');
	$rese=mysql_query("SELECT DISTINCT topicType FROM cdb_topics WHERE topicType NOT IN ('N','I') AND topicCat = (SELECT topicCat FROM cdb_topics WHERE topicID = $nan)");
	while($res=mysql_fetch_assoc($rese))
		$ltp[] = $res['topicType'];

	echo json_encode($ltp);
	exit;
} 
else if(isSet($_GET['topicEe']))
{
	$nan = $vali->numberOnly($_POST['cdbID']);
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA))
	{
		$title = 		  $vali->killChars((addslashes($_POST['cdbTitle'])));
		$colorExtension = $vali->killChars((addslashes($_POST['cdbColor'])));
		$GlobalUserSecBypass = $vali->killChars((addslashes($_POST['GlobalUserSecBypass'])));
		
		$seclar = $vali->numberOnly($_POST['cdbSeclar']);
		$type = ($_POST['cdbType'] == '') ? 'N' : strtoupper( $vali->killChars((addslashes($_POST['cdbType']))));
		$type = str_replace('IMPORTANTI','I',$type);
		$topicID = $vali->numberOnly($_POST['cdbID']);
		
	mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE',topicTitle = '$title', topicType = '$type', topicSeclar = $seclar, topicColorExt = '$colorExtension', topicLastTime = (SELECT GREATEST(time,lastEdit) FROM cdb_posts WHERE topicID = $nan ORDER BY time DESC LIMIT 1), topicLastUser = IFNULL((SELECT owner FROM cdb_posts WHERE topicID = $nan ORDER BY time DESC LIMIT 1), ".$_SESSION['pgID'].") WHERE topicID = $topicID");
	mysql_query("UPDATE cdb_posts SET postSeclar = $seclar WHERE topicID = $topicID AND postSeclar < $seclar");

	$k3 = explode(',',addslashes($_POST['GlobalUserSecBypass']));
		foreach ($k3 as $k3a){
			$k3a = trim($k3a);
			if($k3a != '')
				{
					$usr=mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$k3a'"));
					if (mysql_affected_rows())
					{
						$usrID=$usr['pgID'];
						mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE pgID = $usrID AND postID IN (SELECT ID FROM cdb_posts WHERE topicID = $topicID)");
						mysql_query("INSERT INTO cdb_posts_seclarExceptions (pgID, postID) (SELECT '$usrID',ID  FROM cdb_posts WHERE topicID = $topicID)");
					}
				}
			}

		$k4 = explode(',',addslashes($_POST['GlobalUserSecBypassRemove']));
		foreach ($k4 as $k4a){
			$k4a = trim($k4a);
			if($k4a != '')
				{
					$usr=mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$k4a'"));
					if (mysql_affected_rows())
					{
						$usrID=$usr['pgID'];
						mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE pgID = $usrID AND postID IN (SELECT ID FROM cdb_posts WHERE topicID = $topicID)");
					}
				}
			}



 

	header("Location:cdb.php?topic=$topicID");
	}
	else header("Location:cdb.php");
	exit;
}

else if(isSet($_GET['guide']))
{
	$template = new PHPTAL('TEMPLATES/cdb_guida.htm');
}

//else if("news")

else if(isSet($_GET['disableTopic']))
{
	$nan = $vali->numberOnly($_GET['disableTopic']);
	
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) CDB::deleteTopic($nan);
	
	header("Location:cdb.php"); exit;
}

else if(isSet($_GET['resetnews']))
{
	mysql_query("UPDATE pg_users SET pgLastVisit = ".time()." WHERE pgID =".$_SESSION['pgID']);
	header('Location:cdb.php');
}

else if(isSet($_GET['topicE']))
{
	$template = new PHPTAL('TEMPLATES/topic_edit.htm');
	$topicE = $vali->numberOnly($_GET['topicE']);
	
	
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) //ALMENO UN SMASTER
	{
	
	$res = mysql_query("SELECT topicSeclar,topicID,topicTitle,topicType,topicColorExt,catCode,catName FROM cdb_topics,cdb_cats WHERE cdb_topics.topicCat = catCode AND topicID = $topicE");
	$resA = mysql_fetch_array($res);
		

	$resSec = mysql_query("SELECT pgUser,IF((COUNT(*))=(SELECT COUNT(*) FROM cdb_posts WHERE topicID = $topicE), 1, 0) as hx,COUNT(*) as cnn FROM cdb_posts RIGHT OUTER JOIN (cdb_posts_seclarExceptions JOIN pg_users ON cdb_posts_seclarExceptions.pgID = pg_users.pgID) ON postID = ID WHERE topicID = $topicE GROUP BY pgUser HAVING hx = 1");
	


	$GlobalUserSecBypass='';
	while($rasSec = mysql_fetch_assoc($resSec))
		$GlobalUserSecBypass .= $rasSec['pgUser'].',';
	

	$template->GlobalUserSecBypass = $GlobalUserSecBypass;
	$template->topicSeclar = $resA['topicSeclar'];
	$template->topicID = $resA['topicID'];
	$template->topicTitle = $resA['topicTitle'];
	$template->topicType = $resA['topicType'];
	$template->topicColorExt = $resA['topicColorExt'];
	$template->catID = $resA['catCode'];
	$template->catName = $resA['catName'];
	}
	else{ header("Location:cdb.php");exit;}
}

else if(isSet($_GET['calls']))
{
	$template = new PHPTAL('TEMPLATES/cdb_call.htm');
	
	$res = mysql_query("SELECT * FROM cdb_calls ORDER BY activeTo DESC LIMIT 20");
	
	$calls = array();
	while ($resA = mysql_fetch_array($res))
		$calls[] = $resA;
	$template->calls = $calls;
}


else if(isSet($_GET['callView']))
{
	$template = new PHPTAL('TEMPLATES/cdb_call_view.htm');
	$var = $vali->numberOnly($_GET['callView']);
	$res = mysql_query("SELECT * FROM  cdb_calls WHERE call_id = $var");
	while ($resA = mysql_fetch_array($res))
	$call = $resA;
	
	if($call['activeTo'] > $curTime) $template->votable=true;
	
	$res = mysql_query("SELECT recID,element,elementImage FROM  cdb_calls_elements WHERE call_id = $var");
	while ($resA = mysql_fetch_array($res))
	$callElements[] = $resA;
	
	$res = mysql_query("SELECT COUNT(*) AS conto FROM cdb_calls_results WHERE call_id = $var");
	$resE = mysql_fetch_array($res);
	$counterGlobal=$resE['conto'];
	
	$results =array();
	$res = mysql_query("SELECT element,COUNT(*) AS conto FROM cdb_calls_results WHERE call_id = $var GROUP BY element");
	while ($resA = mysql_fetch_array($res))
	$results[$resA['element']] = $resA['conto']*100 / $counterGlobal;
	
	$userID =$_SESSION['pgID'];
	$re = mysql_query("SELECT 1 FROM cdb_calls_results WHERE call_id=$var AND pgUser = $userID");
	if(!mysql_affected_rows()){$template->voted = 'yes';}
	$comm = mysql_query("SELECT callID,cID,text,timer,pgUser,pgID FROM cdb_calls_comments,pg_users WHERE owner=pgID AND callID = $var ORDER BY timer");
	echo mysql_error();
	$commArr=array();
	while($ca = mysql_fetch_array($comm))
		$commArr[]=$ca;
		
	if (isSet($_GET['errorCall'])) $template->errorCall = true;
	if (isSet($_GET['errorCallLowPerm'])) $template->errorCallLowPerm = true;
	
	$template->commArr = $commArr;
	$template->callElements = $callElements;
	$template->callElementsNumber = count($callElements);
	$template->results = $results;
	$template->counterGlobal = $counterGlobal;
	$template->call = $call;
}

else if(isSet($_GET['callVote']))
{
	$call = $vali->numberOnly($_GET['callVote']);
	$element = $vali->numberOnly($_GET['element']);
	$userID = $_SESSION['pgID'];
	$user = new PG($_SESSION['pgID']);
	
	if($user->png || $user->pgLock || $user->pgAuthOMA == 'BAN' || $user->pgAssign=="BAVO"){header('Location:cdb.php?callView='.$call); exit;}
	
	$re = mysql_query("SELECT 1 FROM cdb_calls,cdb_calls_comments WHERE call_id = callID AND owner = $userID AND activeTo >= ".time());
	
	if(mysql_affected_rows())
	{
		$rel = mysql_fetch_assoc(mysql_query("SELECT restricter FROM cdb_calls_restrict WHERE callID = $call"));
		$lClause=$rel['restricter']; 
		
		$ral = mysql_query("SELECT COUNT(*) as cont FROM pg_users_pointStory WHERE points > 0 AND owner = $userID AND causeE LIKE '%$lClause%'"); 
		$rell = mysql_fetch_assoc($ral);
		if($rell['cont'] >= 5)
		{
			$re = mysql_query("SELECT 1 FROM cdb_calls_results WHERE call_id = $call AND pgUser = $userID");
			if(mysql_affected_rows()){
			mysql_query("UPDATE cdb_calls_results SET element = $element, date = ".time()." WHERE pgUser = $userID AND call_id = $call");
			}
			else
			{mysql_query("INSERT INTO cdb_calls_results (call_id,pgUser,element,date) VALUES ($call,$userID,$element,".time().")");
			
			$currentUser->addPoints(1,'SOND','Risposta Sondaggio','Risposta Sondaggio'); 
			
			}
			header('Location:cdb.php?callView='.$call);
		}
		else{header('Location:cdb.php?errorCallLowPerm=true&callView='.$call);}
	}
	
	else{header('Location:cdb.php?errorCall=true&callView='.$call);}
	
}

else if(isSet($_GET['moveTopic']))
{
	$template = new PHPTAL('TEMPLATES/topic_move.htm');
	$topicE = $vali->numberOnly($_GET['moveTopic']);
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) //ALMENO UN SMASTER
	{
	$res = mysql_query("SELECT topicID,topicTitle,topicType,topicColorExt,catCode,catName FROM cdb_topics,cdb_cats WHERE cdb_topics.topicCat = catCode AND topicID = $topicE");
	$resA = mysql_fetch_array($res);
	
	$template->topicID = $resA['topicID'];
	$template->topicTitle = $resA['topicTitle'];
	$template->topicType = $resA['topicType'];
	$template->topicColorExt = $resA['topicColorExt'];
	$template->catID = $resA['catCode'];
	$template->catName = $resA['catName'];
	
	$res = mysql_query("SELECT catCode,catName FROM cdb_cats");
	$allCat = array();
	while($resA = mysql_fetch_array($res))
	$allCat[$resA['catCode']] = $resA['catCode'].' - '.$resA['catName'];
	$template->allCat = $allCat;
	}
	
	else{ header("Location:cdb.php");exit;}
}


else if(isSet($_GET['moveTopicDo']))
{
	
	$topicID = $vali->numberOnly($_POST['topicID']);
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA))
	{	
		if (isSet($_GET['link']))
		{
			$destination = $vali->numberOnly($_POST['destination']);
			CDB::linkTopic($topicID,$destination);
		}
		else
		{
			$nan = $vali->numberOnly($_POST['destination']);
			CDB::moveTopic($topicID,$nan);
		}
		header("Location:cdb.php?topic=$topicID");
	}
	else header("Location:cdb.php");
	exit;
}


else if(isSet($_GET['topicLock']))
{
	$nan = $vali->numberOnly($_GET['topicLock']);
	
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) //ALMENO UN SMASTER
		mysql_query("UPDATE cdb_topics SET topicLock=1-topicLock WHERE topicID = $nan");
	
	header("Location:cdb.php?topic=$nan");
	exit;
}

else if(isSet($_GET['firsterTopic']))
{
	$nan = $vali->numberOnly($_GET['firsterTopic']);
	
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) //ALMENO UN SMASTER
		mysql_query("UPDATE cdb_topics SET specialo=1-specialo WHERE topicID = $nan");
	
	header("Location:cdb.php?topic=$nan");
	exit;
}

else if(isSet($_GET['lurkerProtect']))
{
	$nan = $vali->numberOnly($_GET['lurkerProtect']);
	
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA)) //ALMENO UN SMASTER
		mysql_query("UPDATE cdb_topics SET trackUsers=1-trackUsers WHERE topicID = $nan");
	
	header("Location:cdb.php?topic=$nan");
	exit;
}

else if(isSet($_GET['dia']))
{
	$res = mysql_query("SELECT pgDiario FROM pg_users WHERE pgID = ".$_SESSION['pgID']);
	$resA = mysql_fetch_array($res);
	$aaa = $resA['pgDiario'];
	if($aaa == 0 && !($currentUser->pgLock))
	{
		$res = mysql_query("SELECT topicID FROM cdb_topics WHERE 1 ORDER BY topicID DESC LIMIT 1");
		$resI = mysql_fetch_array($res);
		$lastI = $resI['topicID']+1;
		
		$name = strtoupper(addslashes($currentUser->pgNomeC." ".$currentUser->pgUser ." ".$currentUser->pgNomeSuff));
		
		$destination = ($currentUser->pgSpecie == 'Romulana') ? 42 : 14;
		mysql_query("INSERT INTO cdb_topics (topicID,topicTitle, topicLastTime, topicLastUser, topicCat, topicSeclar) VALUES ($lastI,'DIARIO DI $name',".time().",".$_SESSION['pgID'].",$destination,5)");
		mysql_query("UPDATE pg_users SET pgDiario = $lastI WHERE pgID = ".$_SESSION['pgID']);
		header("Location:cdb.php?topic=$lastI");
	}
	
	else{ header("Location:cdb.php?topic=$aaa"); exit;}
}

else if (isSet($_GET['addPost']))
{ 
	if($currentUser->pgLock || $currentUser->pgAssign=="BAVO"){exit;}
	
	$title = $vali->killChars(addslashes($_POST['postTitolo']));
	$seclar = $vali->numberOnly($_POST['postSeclar']);
	$content = addslashes($_POST['postContent']);
	$topicCode = $vali->numberOnly($_POST['topicID']);
	$notes = $vali->killChars(addslashes($_POST['postNote']));


	$resSec = mysql_query("SELECT pgID,IF((COUNT(*))=(SELECT COUNT(*) FROM cdb_posts WHERE topicID = $topicCode), 1, 0) as hx,COUNT(*) as cnn FROM cdb_posts RIGHT OUTER JOIN cdb_posts_seclarExceptions ON postID = ID WHERE topicID = $topicCode GROUP BY pgID HAVING hx = 1");
	
	$usersToClear=array();
	while($rasSec = mysql_fetch_assoc($resSec))
		$usersToClear[]=$rasSec['pgID'];


	
	if (PG::mapPermissions("M",$currentUser->pgAuthOMA) && ($_POST['usersMaster'] != ""))
	{
	$usersMaster = addslashes($_POST['usersMaster']);
	$usersMasterID = mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$usersMaster' AND png=1");
	if(mysql_affected_rows())
		{ $ider = mysql_fetch_assoc($usersMasterID);
			  $masterPnG = new PG($ider['pgID']);
			  $masterPnG->getIncarichi();
			
			$departmentString = ($masterPnG->pgDipartimento != '') ? '<br />Dipartimento '.$masterPnG->pgDipartimento : '';
			
			$tipoFirma = ($_POST['postFirma'] == "corta") ? "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$masterPnG->pgGrado." ".$masterPnG->pgUser : "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$masterPnG->pgGrado." ".$masterPnG->pgNomeC." ".$masterPnG->pgUser ." ".$masterPnG->pgNomeSuff."<br />".$masterPnG->pgIncarico."$departmentString<br />".(PG::getLocationName($masterPnG->pgAssign));
			$firma=addslashes($tipoFirma);
			mysql_query("INSERT INTO cdb_posts(title,content,owner,coOwner,time,topicID,postSeclar,postNotes,signature) VALUES('$title','$content',".$masterPnG->ID.",".$_SESSION['pgID'].",".time().",$topicCode,$seclar,'$notes','$firma')");
			mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE',topicLastUser = ".$masterPnG->ID.", topicLastTime = ".time()." WHERE topicID = $topicCode");
		}
	else{
		$usersMasterID = mysql_query("SELECT Rgrado,pngIncarico,pngName,pngSurname,pngDipartimento,placeName FROM png_incarichi,pg_ranks,pg_places WHERE prio = pngRank AND placeID = pngPlace AND pngSurname = '$usersMaster'  AND prioritary = 1 ORDER BY pngID ASC");
		if(mysql_affected_rows())
		{
			$rese = mysql_fetch_assoc($usersMasterID);

			$pngName = ucfirst(strtolower(htmlentities($rese['pngName'])));
			$pngSurname = ucfirst(strtolower(htmlentities($rese['pngSurname'])));
			$pngplaceName = $rese['placeName'];
			$pngIncarico = $rese['pngIncarico'];
			$rank = $rese['Rgrado'];


			$departmentString = ($rese['pngDipartimento'] != '') ? '<br />Dipartimento '.$rese['pngDipartimento']  : '';
			$tipoFirma = ($_POST['postFirma'] == "corta") ? "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />$rank $pngSurname" : "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />$rank $pngName $pngSurname<br />$pngIncarico $departmentString<br />$pngplaceName";
			$firma=addslashes($tipoFirma);

			mysql_query("INSERT INTO cdb_posts(title,content,owner,coOwner,time,topicID,postSeclar,postNotes,signature) VALUES('$title','$content',".$_SESSION['pgID'].",".$_SESSION['pgID'].",".time().",$topicCode,$seclar,'$notes','$firma')");
			mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE',topicLastUser = ".$_SESSION['pgID'].", topicLastTime = ".time()." WHERE topicID = $topicCode");
		}
	}
	}
	else{
	$currentUser->getIncarichi();
	$departmentString = ($currentUser->pgDipartimento != '') ? '<br />Dipartimento '.$currentUser->pgDipartimento : '';
	$tipoFirma = ($_POST['postFirma'] == "corta") ? "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$currentUser->pgGrado." ".$currentUser->pgUser : "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$currentUser->pgGrado." ".$currentUser->pgNomeC." ".$currentUser->pgUser ." ".$currentUser->pgNomeSuff."<br />".$currentUser->pgIncarico."$departmentString<br />".(PG::getLocationName($currentUser->pgAssign));
	$firma=addslashes($tipoFirma);
	mysql_query("INSERT INTO cdb_posts(title,content,owner,time,topicID,postSeclar,postNotes,signature) VALUES('$title','$content',".$_SESSION['pgID'].",".time().",$topicCode,$seclar,'$notes','$firma')");
	mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE', topicLastUser = ".$_SESSION['pgID'].", topicLastTime = ".time()." WHERE topicID = $topicCode");
	}



	mysql_query("DELETE FROM pg_visualized_elements WHERE type ='CDB' AND what = $topicCode");
	$POSTID=mysql_fetch_assoc(mysql_query("SELECT ID FROM cdb_posts WHERE title = '$title' ORDER BY time DESC LIMIT 1"));
	$postIDCo=$POSTID['ID'];
	foreach($usersToClear as $u)
		mysql_query("INSERT INTO cdb_posts_seclarExceptions (pgID, postID) VALUES ('$u','$postIDCo')");


 	if (mysql_affected_rows() && $_POST['peopleList'] != '')
	{  
			
			$listPG=explode(',',trim($_POST['peopleList']));
			foreach ($listPG as $to)
			{
				$to=trim($to);
				if($to!="") 
				{	
					$ids=mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '".addslashes($to)."'");
					if (mysql_affected_rows())
					{


						$idsA = mysql_fetch_assoc($ids);

						$toP = new PG($idsA['pgID'],2);
						$nt = ($title == '') ? 'Nessun titolo' : $title;
						
						
						$overSeclar="";
						$seclarColor='#2f8ad0';
						if((int)($toP->pgSeclar) < (int)($seclar))
						{	mysql_query("INSERT IGNORE INTO cdb_posts_seclarExceptions (pgID, postID) VALUES (".$toP->ID.",'$postIDCo')"); 
							$overSeclar = "Il post è stato inserito a <span style=\"color:red; font-weight:bold;\">SECLAR $seclar</span> ma puoi visualizzarlo ugualmente, essendone destinatario";
							$seclarColor = '#FF0000';
						}

					
						$smallTitle = addslashes(substr($title,0,50));

						mysql_query("INSERT INTO pg_personal_notifications (owner,text,subtext,image,time,URI,linker) VALUES (".$toP->ID.",'<b>Sei stato citato in CDB</b>: $smallTitle','[ - <span style=\"font-size:11px; color:$seclarColor;\"> SECLAR $seclar</span> - ]','".$currentUser->pgAvatarSquare."',$curTime,'$topicCode#$postIDCo','cdbOpenToTopic')"); 
					}

						

					//$toP->sendPadd('[CDB] - '.$nt,"Ciao $to <br /><br />Il PG ".$currentUser->pgUser." ha inserito un nuovo post che probabilmente ti riguarda: <a class=\"interfaceLinkBlue\" href=\"javascript:void(0);\" onclick=\"javascript:cdbOpenToTopic('$topicCode#$postIDCo')\">$nt</a>. $overSeclar <br />Lo staff",'518'); 
				}
			}
	}


	


	header("Location:cdb.php?topic=$topicCode");
	exit;
	
	
}
 
else if (isSet($_GET['editPost']))
{
	$topicID = $vali->numberOnly($_POST['topicID']);
	$pID = $vali->numberOnly($_GET['editPost']); 
	if($_GET['m']=='14') //if($currentUser->pgAuthOMA == "A" || ($postOwner == $_SESSION['pgID']) || ($postCoOwner == $_SESSION['pgID']))
	{ 
		$postSeclar = $vali->numberOnly($_POST['postSeclar']);
		$title = $vali->killChars(addslashes($_POST['postTitolo']));
		$note = addslashes($vali->killChars($_POST['postNote'])); 
		$content = addslashes($_POST['postContent']);
		
		mysql_query("UPDATE cdb_topics SET lastTopicEvent='EDIT', topicLastUser = ".$_SESSION['pgID'].", topicLastTime = $curTime WHERE topicID = $topicID AND topicLock = 0");
		if(mysql_affected_rows()){
			mysql_query("UPDATE cdb_topics SET lastTopicEvent='EDIT', topicLastUser = ".$_SESSION['pgID'].", topicLastTime = $curTime WHERE topicLink = $topicID AND topicLock = 0");
			mysql_query("UPDATE cdb_posts SET lastEdit = ".time().", content = '$content', title = '$title',postSeclar='$postSeclar', postNotes = '$note' WHERE ID = $pID");

			mysql_query("DELETE FROM pg_visualized_elements WHERE type ='CDB' AND what = $topicID");
		}
	}
	elseif($_GET['m'] =='10' && $currentUser->pgAuthOMA == "A")
	{
		if($_POST['userOwner'] != '')
		{
			$k1 = addslashes(str_replace(',','',trim($_POST['userOwner'])));

			$k1_res = ($k1 != '') ? mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$k1'")) : NULL;
			
			$k2 = addslashes(str_replace(',','',trim($_POST['userCoOwner'])));

			$k2_res = ($k2 != '') ? mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$k2'")) : NULL;
			
			$k3 = explode(',',addslashes($_POST['userSecBypass']));

			mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE postID = $pID");

			foreach ($k3 as $k3a){
				$k3a = trim($k3a);
				$k3a_res = ($k3a != '') ? mysql_fetch_assoc(mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$k3a'")) : NULL;
				mysql_query("INSERT INTO cdb_posts_seclarExceptions (pgID, postID) VALUES ('".$k3a_res['pgID']."',$pID)"); 
			}

			mysql_query("UPDATE cdb_posts SET owner = ".$k1_res['pgID'].", coOwner = ".(($k2_res) ? $k2_res['pgID'] : 0)." WHERE ID = $pID"); 
		}  
	}
	
	
	header("Location:cdb.php?topic=$topicID#$pID");
	exit;
}

elseif (isSet($_GET['editAssignPost']))
{
	echo "1!";
}

else if (isSet($_GET['deletePost']))
{
	$deletePost = $_GET['deletePost'];
	$resPost = mysql_query("SELECT coOwner, owner,topicID FROM cdb_posts WHERE ID = $deletePost");
	if(mysql_affected_rows())
	{
	$resAPost = mysql_fetch_array($resPost);
	$postOwner = $resAPost['owner'];
	$postCoOwner = $resAPost['coOwner'];
	$topicID = $resAPost['topicID'];
	}
	
	if($currentUser->pgAuthOMA == "A" || ($postOwner == $_SESSION['pgID']) || $postCoOwner == $_SESSION['pgID'])
	{
		mysql_query("DELETE FROM cdb_posts WHERE ID = $deletePost");
		mysql_query("DELETE FROM cdb_posts_seclarExceptions WHERE postID = $deletePost");
		header("Location:cdb.php?topic=$topicID");
		exit;
	}
	else{ header("Location:cdb.php"); exit;}
}

else if(isSet($_GET['postE']))
{	
	$template = new PHPTAL('TEMPLATES/post_edit.htm');
	$postE = $vali->numberOnly($_GET['postE']);
	$resPost = mysql_query("SELECT owner,coOwner,topicID FROM cdb_posts WHERE ID = $postE");
	
	if(mysql_affected_rows())
	{
	$resAPost = mysql_fetch_array($resPost);
	$postOwner = $resAPost['owner'];
	$template->postOwnerUser = PG::getSomething($postOwner,'username');
	$postCoOwner = $resAPost['coOwner'];
	$template->postCoOwnerUser = PG::getSomething($postCoOwner,'username');
	$topicID = $resAPost['topicID'];
	}
	
	if($currentUser->pgAuthOMA == "A" || ($postOwner == $_SESSION['pgID']) || ($postCoOwner == $_SESSION['pgID']))
	{
	$res = mysql_query("SELECT * FROM cdb_posts,cdb_topics,cdb_cats WHERE cdb_topics.topicCat = catCode AND ID = $postE AND cdb_topics.topicID = cdb_posts.topicID");
	$resA = mysql_fetch_array($res);
	
	$template->editID = $resA['ID'];
	$template->bounceYear = $bounceYear; 
	$template->editContent = $resA['content'];
	$template->editTitle = $resA['title'];
	$template->editNotes = $resA['postNotes'];
	$template->postSeclar = $resA['postSeclar'];
	$r= CDB::getPostAccess($resA['ID']);

	$template->userSecBypass = implode(",", $r);
	$template->topicSeclar = $resA['topicSeclar'];
	$template->topicID = $resA['topicID'];
	$template->topicTitle = $resA['topicTitle'];
	$template->topicType = $resA['topicType'];
	$template->topicColorExt = $resA['topicColorExt'];
	
	$template->catID = $resA['catCode'];
	$template->catName = $resA['catName'];
	}
	else{ header("Location:cdb.php"); exit;}
}

else if(isSet($_GET['addPointsReport']) && isSet($_GET['page']) && isSet($_GET['topic']))
{

	$toTopic =   $vali->numberOnly($_GET['topic']);
	$toPage=  $vali->numberOnly($_GET['page']);
	$postID = $vali->numberOnly($_GET['addPointsReport']);

	if(PG::mapPermissions("M",$currentUser->pgAuthOMA))
	{	
	$res = mysql_fetch_array(mysql_query("SELECT ID, title,owner,topicID FROM cdb_posts WHERE ID = $postID"));
	$pgID = $vali->numberOnly($res['owner']);
	$targetPG = new PG($pgID); 
	$addomRapID = "Rap:".$res['topicID'].'#'.$res['ID'];
	$rese = mysql_query("SELECT 1 FROM pg_users_pointStory WHERE causeM = '$addomRapID'");
	if(!mysql_affected_rows() && ($pgID != $_SESSION['pgID'] || PG::mapPermissions("A",$currentUser->pgAuthOMA)))
		$targetPG->addPoints(2,'R',$addomRapID,'Rapporto: '.addslashes($res['title']),$_SESSION['pgID']); 
	}
	header("Location:cdb.php?topic=$toTopic&page=$toPage#$postID"); exit;
}

else if(isSet($_POST['searchKey']))
{
	if($_POST['searchKey']=="") { header("Location:cdb.php"); exit;}
	
	$searchKey = addslashes(str_replace(array('+','-','%'),array('','',''),$vali->killChars($_POST['searchKey'])));
	$reachPattern = $_POST['searchPattern'];
	$s=false;

	if($reachPattern == 'MEDA')
	{
		$template = new PHPTAL('TEMPLATES/cdb_medal_search.htm');
		$ppl = mysql_query("SELECT pgID FROM ");

	}
	elseif($reachPattern == 'DBS1' || $reachPattern == 'DBS2' || $reachPattern == 'DBS3')
	{
		$template = new PHPTAL('TEMPLATES/cdb_search.htm');
		$posts = array();
		$topics = array();
		
		if (strlen($searchKey) > 3 && strpos($searchKey,'\'') === false)
		{
			if($reachPattern == 'DBS1')
				$qstring = $searchKey;
			else if($reachPattern == 'DBS3')
				$qstring = '"'.$searchKey.'"';
			else if($reachPattern == 'DBS2'){
				$qstring='';
				$key = explode(' ',$searchKey);
				foreach($key as $ale){$qstring .= '+'.$ale.' ';}
			}
				
			$res = mysql_query("SELECT ID as postID, cdb_posts.topicID, content, time,topicCat, postSeclar,owner,coOwner,postNotes,title,signature,pgUser,pgID, (MATCH (content) AGAINST ('$qstring' IN BOOLEAN MODE)) AS priority, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND restrictions = 'N' AND MATCH (content,title) AGAINST ('$qstring' IN BOOLEAN MODE) ORDER BY priority, time DESC");  
		}
		else $res = mysql_query("SELECT ID as postID, cdb_posts.topicID, content, time,topicCat,postSeclar,owner,coOwner,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND restrictions = 'N' AND (content LIKE '%$searchKey%' OR title LIKE '%$searchKey%') ORDER BY time DESC");
 
		
		while($resA = mysql_fetch_array($res))
		{

			if( $resA['postSeclar'] <= $currentUser->pgSeclar || $resA['owner'] == $currentUser->ID || PG::mapPermissions('SM',$currentUser->pgAuthOMA) || $resA['coOwner'] == $currentUser->ID || in_array($resA['topicCat'],$safeSeclarCats) ||  CDB::checkPostAccess($resA['postID'],$currentUser))

		{
			$udel = (($resA['pgID'] == $_SESSION['pgID']) || $currentUser->pgAuthOMA == "A") ? 1 : NULL;
			$title = ($resA['title'] == "") ? '-' : $resA['title'];
			$posts[$resA['topicID']][] = array(
			'ID' => $resA['postID'],
			'title' => $title,
			'pgUser' => $resA['pgUser'],
			'pgUserID' => $resA['pgID'],
			'content' => CDB::bbcode($resA['content']),
			'time' => timeHandler::timestampToGiulian($resA['time']),
			'accessible' => ($resA['postSeclar'] <= $currentUser->pgSeclar || PG::mapPermissions('SM',$currentUser->pgAuthOMA) ||  $resA['owner'] == $currentUser->ID || $resA['coOwner'] == $currentUser->ID ) ? 1 : CDB::checkPostAccess($resA['postID'],$currentUser), 

			'postSeclar' => $resA['postSeclar'],
			'postNote' => $resA['postNotes'],
			'signature' => $resA['signature'],
			'topicID' => $resA['topicID'],
			'userdel' => $udel
			); 
			
			$topics[$resA['topicID']] = array('topicID' => $resA['topicID'], 'title' => $resA['topicTitle'],'topicType' => $resA['topicType'],'classExt'=>$resA['topicColorExt']); 
			$s = true;
		}
		}
		$template->posts = $posts;
		 
		$template->topics = $topics;
	}
	
	else if($reachPattern == 'AUT')
	{ 
		$template = new PHPTAL('TEMPLATES/cdb_search.htm');
		$posts = array();
		$topics = array();
		$res = mysql_query("SELECT ID as postID, cdb_posts.topicID as topID, content,owner,coOwner, time, postSeclar,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND (restrictions = 'N') AND pgUser = '$searchKey' ORDER BY time");

				while($resA = mysql_fetch_array($res))
		{
			$udel = (($resA['pgID'] == $_SESSION['pgID']) || $currentUser->pgAuthOMA == "A") ? 1 : NULL;
			$title = ($resA['title'] == "") ? '-' : $resA['title'];
			$posts[$resA['topID']][] = array(
			'ID' => $resA['postID'],
			'title' => $title,
			'pgUser' => $resA['pgUser'],
			'pgUserID' => $resA['pgID'],
			'content' => CDB::bbcode($resA['content']),
			'time' => timeHandler::timestampToGiulian($resA['time']),
			'accessible' => ($resA['postSeclar'] <= $currentUser->pgSeclar || PG::mapPermissions('SM',$currentUser->pgAuthOMA) ||  $resA['owner'] == $currentUser->ID || $resA['coOwner'] == $currentUser->ID ) ? 1 : CDB::checkPostAccess($resA['postID'],$currentUser), 
			'postSeclar' => $resA['postSeclar'],
			'postNote' => $resA['postNotes'],
			'signature' => $resA['signature'],
			'topicID' => $resA['topID'],
			'userdel' => $udel
			);
			
			$topics[$resA['topID']] = array('topicID' => $resA['topID'], 'title' => $resA['topicTitle'],'topicType' => $resA['topicType'],'classExt'=>$resA['topicColorExt']); 
			$s = true;
		}


		$template->posts = $posts;
		$template->topics = $topics;
	}

	else if($reachPattern == 'PGG')
	{ 
		$res = mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$searchKey'");
		$resA = mysql_fetch_array($res);
		
			$s = $resA['pgID'];
			if($s >= 1)
			header('Location:scheda.php?pgID='.$s);
			else header('Location:cdb.php');
			exit;
	}
	
	if($s==false) { $template->over=true; $template->posts = array();}
}

else if(isSet($_GET['meSearch']))
{
	$template = new PHPTAL('TEMPLATES/cdb_search.htm'); 
	
	if($_GET['meSearch'] == 1) $query="SELECT ID as postID, cdb_posts.topicID, content, time, postSeclar,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND owner = pgID AND restrictions = 'N' AND pgID =".$_SESSION['pgID'].' ORDER BY time DESC';
	
	elseif($_GET['meSearch'] == 2 && strpos($currentUser->pgUser,'\'')) $query="SELECT ID as postID, cdb_posts.topicID, content, time, postSeclar,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE owner <> ".$_SESSION['pgID']." AND topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND restrictions = 'N' AND content LIKE '%".addslashes($currentUser->pgUser)."%' ORDER BY time DESC"; 
	
	elseif($_GET['meSearch'] == 2) $query="SELECT ID as postID, cdb_posts.topicID, content, time, postSeclar,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE owner <> ".$_SESSION['pgID']." AND topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND restrictions = 'N' AND  MATCH (content) AGAINST ('".addslashes($currentUser->pgUser)."' IN BOOLEAN MODE) ORDER BY time DESC"; 
	 
		$posts = array();
		$topics = array();
		$res = mysql_query($query);

			while($resA = mysql_fetch_array($res))
		{
			$udel = (($resA['pgID'] == $_SESSION['pgID']) || $currentUser->pgAuthOMA == "A") ? 1 : NULL;
			$title = ($resA['title'] == "") ? '-' : $resA['title'];
			$posts[$resA['topicID']][] = array(
			'ID' => $resA['postID'],
			'title' => $title,
			'pgUser' => $resA['pgUser'],
			'pgUserID' => $resA['pgID'],
			'content' => CDB::bbcode($resA['content']),
			'time' => timeHandler::timestampToGiulian($resA['time']),
			'postSeclar' => $resA['postSeclar'],
			'postNote' => $resA['postNotes'],
			'signature' => $resA['signature'],
			'topicID' => $resA['topicID'],
			'userdel' => $udel
			);
			
			$topics[$resA['topicID']] = array('topicID' => $resA['topicID'], 'title' => $resA['topicTitle'],'topicType' => $resA['topicType'],'classExt'=>$resA['topicColorExt']); 
			$s = true;
		}
		$template->posts = $posts;
		$template->topics = $topics;
	
	if(!isSet($s)) { $template->over=true; $template->posts = array();}
}
else if  (isSet($_GET['test'])) 
{
//	echo var_dump(CDB::getPostFullLink('16220')); 


	$bbtext = "this is a [post]16220[/post]";
	$htmltext = replaceBBcodes($bbtext);

	#$bbtext = "this is a [post]1622000[/post]";
	#$htmltext = replaceBBcodes($bbtext);
	echo $htmltext;


	
	exit;

}
else if(isSet($_GET['topic'])) 
{
	$template = new PHPTAL('TEMPLATES/cdb_topic.htm');
	$topic = $vali->numberOnly($_GET['topic']); 

	mysql_query("INSERT IGNORE INTO pg_visualized_elements (type,what,pgID,time) VALUES ('CDB',".$topic.",".$_SESSION['pgID'].",$curTime) ");


	
	$res = mysql_query("SELECT topicSeclar,topicLink,topicID,topicTitle,topicType,topicColorExt,catCode,catName,restrictions,topicLock,specialo,trackUsers FROM cdb_topics,cdb_cats WHERE topicID = $topic AND cdb_topics.topicCat = catCode");
	if(!mysql_affected_rows()){ header('Location:cdb.php'); exit;}
	$resA = mysql_fetch_array($res);
	
	if($resA['topicLink'] <> 0){ $topic = $resA['topicLink'];
	 //echo "YOU ARE REDIRECTED"; exit; 
	 } //REDIRECTION

	 	$counterres = mysql_query("SELECT count(*) as contatore FROM cdb_posts WHERE topicID = $topic");
		$counterresa = mysql_fetch_array($counterres);
		$postNo = ($counterresa['contatore'] != 0) ? $counterresa['contatore'] : 1;
	
		$elementsPerPage = 15;
		$pageNo = ceil($postNo / $elementsPerPage);		
		$page = (isSet($_GET['page'])) ? $vali->numberOnly($_GET['page']) : $pageNo;
		$lower = $elementsPerPage * ($page - 1);
		$upper = $elementsPerPage;
	
	$topicSeclar = $resA['topicSeclar'];
	$template->pageNo = $pageNo;
	$template->currentPage = $page; 
	$template->bounceYear = $bounceYear; 

	$template->topicID = $resA['topicID']; // Effective (link)
	$template->topicIDE = $topic; // Identity
	
	$topicTitle = $resA['topicTitle'];
	$topicType = $resA['topicType'];
	$topicSpecial = ($resA['specialo']) ? true : false;
	$topicColorExt = $resA['topicColorExt'];
	$catID = $resA['catCode'];
	$catName = $resA['catName'];
	$catRestrictions = $resA['restrictions'];
	
	if ($resA['trackUsers'])
	{
		$template->lurkerProtect = true;
		
		if(!PG::mapPermissions('SM',$currentUser->pgAuthOMA))
		{
			$rsp=mysql_fetch_assoc(mysql_query("SELECT owner,coOwner FROM cdb_posts WHERE topicID = $topic ORDER BY time LIMIT 1"));
			if($rsp['owner'] != $currentUser->ID && $rsp['coOwner'] != $currentUser->ID)
				$template->preventShow = true;
		} 
	}
	else $template->lurkerProtect = false;
	
	if (PG::mapPermissions($catRestrictions,$currentUser->pgAuthOMA))
	{ 
		$template->topicTitle = $topicTitle;
		$template->topicSeclar = $topicSeclar;
		$template->topicType = $topicType;
		$template->topicColorExt = $topicColorExt;
		$template->catID = $catID;
		$template->catName = $catName;
		$template->topicLock = $resA['topicLock'];

		$posts=array();
		$reso = mysql_query("SELECT lastEdit,owner,pgID,coOwner,title,ID,pgUser,pgID,content,time,postSeclar,postNotes,signature, (SELECT if(count(*) > 0,1,0) FROM pg_users_pointStory WHERE pg_users_pointStory.owner = cdb_posts.owner AND causeM LIKE CONCAT('%',CONCAT(cdb_posts.ID,'%'))) as FPA FROM cdb_posts,pg_users WHERE topicID = $topic AND owner = pgID ORDER BY time LIMIT $lower, $upper");

		if ($topicSpecial && $page != 1) {
			 
			$resE = mysql_fetch_array(mysql_query("SELECT lastEdit,owner,pgID,coOwner,title,ID,pgUser,pgID,content,time,postSeclar,postNotes,signature, (SELECT if(count(*) > 0,1,0) FROM pg_users_pointStory WHERE pg_users_pointStory.owner = cdb_posts.owner AND causeM LIKE CONCAT('%',CONCAT(cdb_posts.ID,'%'))) as FPA FROM cdb_posts,pg_users WHERE topicID = $topic AND owner = pgID ORDER BY time LIMIT 1"));
			$udel = (($resE['owner'] == $_SESSION['pgID']) || $currentUser->pgAuthOMA == "A" || $resE['coOwner'] == $_SESSION['pgID']) ? 1 : NULL;
			$title = ($resE['title'] == "") ? '-' : $resE['title'];
			$posts[] = array(
			'ID' => $resE['ID'],
			'title' => $title,
			'pgUser' => $resE['pgUser'],
			'pgUserID' => $resE['pgID'],
			'content' => CDB::bbcode($resE['content']),
			'time' => timeHandler::timestampToGiulian($resE['time']),
			'lastEdit' => timeHandler::timestampToGiulian($resE['lastEdit']),
			'postSeclar' => $resE['postSeclar'],
			'accessible' => ($resE['postSeclar'] <= $currentUser->pgSeclar || PG::mapPermissions('SM',$currentUser->pgAuthOMA) ||  $resE['owner'] == $currentUser->ID || $resE['coOwner'] == $currentUser->ID || in_array($catID,$safeSeclarCats) ) ? 1 : CDB::checkPostAccess($resE['ID'],$currentUser), 
			'postNote' => $resE['postNotes'],
			'signature' => $resE['signature'],
			'userdel' => $udel,
			'FPtoA' => !$resE['FPA'],
			'coOwner' => ($resE['coOwner'] && PG::mapPermissions('JM',$currentUser->pgAuthOMA)) ? PG::getSomething($resE['coOwner'],'username') : NULL
			);
		}


		if(mysql_affected_rows()) 
		{	while($resA = mysql_fetch_array($reso))
			{
			$udel = (($resA['owner'] == $_SESSION['pgID']) || $currentUser->pgAuthOMA == "A"  || $resA['coOwner'] == $_SESSION['pgID']) ? 1 : NULL;
			$title = ($resA['title'] == "") ? '-' : $resA['title'];



			$posts[] = array(
			'ID' => $resA['ID'],
			'title' => $title,
			'pgUser' => $resA['pgUser'],
			'pgUserID' => $resA['pgID'],
			'content' => CDB::bbcode($resA['content']),
			'time' => timeHandler::timestampToGiulian($resA['time']),
			'lastEdit' => timeHandler::timestampToGiulian($resA['lastEdit']),
			'postSeclar' => $resA['postSeclar'], 
			'accessible' => ($resA['postSeclar'] <= $currentUser->pgSeclar || PG::mapPermissions('SM',$currentUser->pgAuthOMA) || $resA['owner'] == $currentUser->ID || $resA['coOwner'] == $currentUser->ID || in_array($catID,$safeSeclarCats) ) ? 1 : CDB::checkPostAccess($resA['ID'],$currentUser), 
			'postNote' => $resA['postNotes'],
			'signature' => $resA['signature'],
			'userdel' => $udel,
			'FPtoA' => !$resA['FPA'],
			'coOwner' => ($resA['coOwner'] && PG::mapPermissions('JM',$currentUser->pgAuthOMA)) ? PG::getSomething($resA['coOwner'],'username') : NULL
			);
			}
		}

		$template->posts = $posts;
		$template->lastPost = end($posts);
		$template->postCounter = count($posts);
		if(PG::mapPermissions('M',$currentUser->pgAuthOMA)) $ddd = 'yes'; else $ddd=NULL;
		if(PG::mapPermissions('M',$currentUser->pgAuthOMA)) $eee = 'yes'; else $eee=NULL;
		$template->showMaster = $ddd;
		$template->showStaff = $eee;
	}
	else
	{
		header("Location:cdb.php");
		exit;
	}
}

else if(isSet($_GET['aggregator']))
{ 	
	$template = new PHPTAL('TEMPLATES/cdb_sub.htm');
	$aggregator = $vali->killChars($_GET['aggregator']);
	$res = mysql_query("SELECT catCode, catName, restrictions, catDesc, (SELECT COUNT(*) FROM cdb_topics WHERE topicCat = catCode AND topicLastTime > $limiL AND topicID NOT IN (SELECT what FROM pg_visualized_elements WHERE type = 'CDB' AND pgID = ".$_SESSION['pgID'].") ) as tosee FROM cdb_cats WHERE catSuper = '$aggregator'  ORDER BY catName");
	$cats=array();
	$elements=false;
	while ($reso = mysql_fetch_array($res))
	{
		if (PG::mapPermissions($reso['restrictions'],$currentUser->pgAuthOMA))
		{
			$cats[] = array("ID" => $reso["catCode"], "name" => $reso["catName"], "desc" => $reso['catDesc'], "tosee" => $reso['tosee']);
			$elements=true;
		}
	}
	if(!$elements)
	{	header("Location:cdb.php");
		exit;
	}
	$template->subcats = $cats;
}

else if (isSet($_GET['cat']))
{
	$topicsI = array();
	$topicsA = array();
	$topicsN = array();
	
	$template = new PHPTAL('TEMPLATES/cdb_main.htm');
	if (isSet($_GET['cat'])) $category =  $vali->numberOnly($_GET['cat']);
	else{ header("Location:cdb.php"); exit;}
	
	$resA = mysql_query("SELECT restrictions,catName,catCode FROM cdb_cats WHERE catCode = $category");
	$resAres = mysql_fetch_array($resA);
	$catRestriction = $resAres['restrictions'];
	$catName = $resAres['catName'];
	$catCode = $resAres['catCode'];
	
	if (PG::mapPermissions($catRestriction,$currentUser->pgAuthOMA))
	{
		$me=$_SESSION['pgID'];

		$res = mysql_query("SELECT pg_visualized_elements.time as seen,topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,IF(topicType = 'I',0,topicLock) as topicLock,topicColorExt FROM pg_users,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = '$me' AND type = 'CDB' AND what = topicID) WHERE topicCat = $category AND topicLastUser=pg_users.pgID ORDER BY topicLock,topicLastTime DESC");

		$topics = array();

		while ($reso = mysql_fetch_array($res))
		{
			if (!array_key_exists($reso['topicType'],$topics))
				$topics[$reso['topicType']] = array();

			$topics[strtoupper($reso['topicType'])][] = array("ID" => $reso['topicID'],'topicType'=>strtoupper($reso['topicType']), "title" => $reso['topicTitle'],'topicLock'=>$reso['topicLock'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 	"lastU" => $reso['pgUser'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false) );
		}

	}
	else {header("Location:cdb.php"); exit;}
	
	uksort($topics, 'cmp_topics'); 
	//uasort() 
	$template->topics = $topics;
	//$template->topicsI = $topics['I'];
	//$template->topicsA = $topics['A'];
	//$template->topicsN = $topics['N'];

	$template->currentCat = $category;
	$template->currentCatNAme = $catName;
	
	if(PG::mapPermissions('JM',$currentUser->pgAuthOMA)) $ddd = 'yes'; else $ddd=NULL;
	$template->showSearchMaster = $ddd;
}

else if (isSet($_GET['medDel']))
{
	$medDel = $vali->numberOnly($_GET['medDel']);
	$id = $vali->numberOnly($_GET['ID']);
	
	if ($currentUser->pgSezione == 'Medica' || $currentUser->pgSezione == 'Medicina Civile' || $currentUser->pgSezione == 'Ricerca e Terapia' || PG::mapPermissions('SL',$currentUser->pgAuthOMA))
	mysql_query("DELETE FROM pgMedica WHERE recID = $medDel");

	header("Location:scheda.php?pgID=$id&s=me");
}
else if (isSet($_GET['news']))
{
	$topics = array();
	
	$template = new PHPTAL('TEMPLATES/cdb_news.htm');
		$me=$currentUser->ID;

		$currentLocation = (PG::getLocation($currentUser->pgLocation));
		 $res = mysql_query("SELECT pg_visualized_elements.time as seen,topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,topicLink, IF(topicType='I',0,topicLock) as topicLock,topicColorExt FROM pg_users,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = $me AND type = 'CDB' AND what = topicID) WHERE topicLastUser=pg_users.pgID AND (topicCat IN (".$currentLocation['catGDB'].",".$currentLocation['catDISP'].",".$currentLocation['catRAP'].")) ORDER by topicLastTime DESC LIMIT 15");

		$topics['LOCAL'] = array();
		while ($reso = mysql_fetch_array($res))
			$topics['LOCAL'][] = array("ID" => $reso['topicID'],"lastTopicEvent" => $reso['lastTopicEvent'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'],"topicType"=>$reso['topicType'],'topicLink' => $reso['topicLink'],"topicLock" => $reso['topicLock'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false) );
		
		// FLOTTA
		$res = mysql_query('SELECT pg_visualized_elements.time as seen,topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,topicLink, IF(topicType=\'I\',0,topicLock) as topicLock,topicColorExt FROM pg_users,cdb_cats,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = '.$me.' AND type = \'CDB\' AND what = topicID) WHERE topicLastUser=pg_users.pgID AND topicCat = catCode AND catSuper = \'FL\' ORDER BY topicLastTime DESC LIMIT 10');

		while ($reso = mysql_fetch_array($res))
			$topics['FL'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'],"lastTopicEvent" => $reso['lastTopicEvent'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'],"topicType"=>$reso['topicType'],'topicLink' => $reso['topicLink'],"topicLock" => $reso['topicLock'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false));

		$res = mysql_query('SELECT pg_visualized_elements.time as seen,topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,topicLink, IF(topicType=\'I\',0,topicLock) as topicLock,topicColorExt FROM pg_users,cdb_cats,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = '.$me.' AND type = \'CDB\' AND what = topicID) WHERE topicLastUser=pg_users.pgID AND topicCat = catCode AND catSuper = \'CIV\' ORDER BY topicLastTime DESC LIMIT 10');
		while ($reso = mysql_fetch_array($res))
			$topics['CIV'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'],"lastTopicEvent" => $reso['lastTopicEvent'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'],"topicType"=>$reso['topicType'],'topicLink' => $reso['topicLink'],"topicLock" => $reso['topicLock'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false));

		$res = mysql_query('SELECT pg_visualized_elements.time as seen,restrictions,lastTopicEvent,topicID,topicTitle,topicLastTime,pgUser,topicType,topicLink, IF(topicType=\'I\',0,topicLock) as topicLock,topicColorExt FROM pg_users,cdb_cats,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = '.$me.' AND type = \'CDB\' AND what = topicID) WHERE topicLastUser=pg_users.pgID AND topicCat = catCode AND catSuper = \'HE\' AND restrictions IN (' . PG::returnMapsStringFORDB($currentUser->pgAuthOMA). ') ORDER BY topicLastTime DESC LIMIT 10');
		while ($reso = mysql_fetch_array($res))
		$topics['HE'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'],"lastTopicEvent" => $reso['lastTopicEvent'], 'topicType' => $reso['topicType'],"topicType"=>$reso['topicType'],'topicLink' => $reso['topicLink'],"topicLock" => $reso['topicLock'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false));
		

		$res = mysql_query('SELECT pg_visualized_elements.time as seen,topicID,lastTopicEvent,topicTitle,restrictions,topicLastTime,pgUser,topicType,topicLink,IF(topicType=\'I\',0,topicLock) as topicLock,topicColorExt FROM pg_users,cdb_cats,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = '.$me.' AND type = \'CDB\' AND what = topicID)  WHERE topicLastUser=pg_users.pgID AND topicCat = catCode AND catSuper = \'MA\' AND catCode <> 47 AND restrictions IN (' .PG::returnMapsStringFORDB($currentUser->pgAuthOMA) .') ORDER BY topicLastTime DESC LIMIT 15');

		while ($reso = mysql_fetch_array($res))
		{	
			$topics['MA'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'],"lastTopicEvent" => $reso['lastTopicEvent'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'],"topicType"=>$reso['topicType'],'topicLink' => $reso['topicLink'],"topicLock" => $reso['topicLock'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false));	
			$masters = true;
		}	
	if (isSet($masters)) $template->showMA = true;

	
	$template->topics = $topics;
} 

elseif(isSet($_GET['deleteMasterEvent']))
{
	if (!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	$recID = $vali->numberOnly($_GET['deleteMasterEvent']);

	mysql_query("DELETE FROM fed_master_news WHERE recID = $recID");
	header('Location:cdb.php');


}
elseif(isSet($_GET['insertMasterEvent']))
{

	if (!PG::mapPermissions('M',$currentUser->pgAuthOMA)) exit;
	$eventTitle = addslashes($_POST['eventTitle']);
	$eventText = addslashes($_POST['eventText']);
	$place = addslashes($_POST['place']);
	$time = time();
	
	mysql_query("INSERT INTO fed_master_news (title,content,time,place) VALUES ('$eventTitle','$eventText',$time,'$place')");

	header('Location:shadow_scheda.php');
	exit;
}

else
{
	$template = new PHPTAL('TEMPLATES/cdb_index.htm');
	$currentLocation = (PG::getLocation($currentUser->pgLocation));
	$template->positionSelector = $currentLocation;
//$updatedAggregatorsRes= mysql_query("altro uomo FROM cdb_cats,cdb_topics WHERE cdb_topics.topicCat = catCode AND ");

/**NEWS HOME PAGE */

	$lastTopicsFL = array();
	$lastTopicsCI = array();
	$lastTopicsHE = array();
	$lastTopicsMA = array();
	$lastTopicsLOC = array();

// AGGIORNAMENTI NELLE CATEGORIE LOCALI?
//$queryLocal = mysql_query("SELECT 1 FROM cdb_topics WHERE topicCat IN (".$currentLocation['catGDB'].",".$currentLocation['catDISP'].",".$currentLocation['catRAP'].") AND topicLastTime > ".$currentUser->pgLastVisit." ORDER BY topicLastTime DESC LIMIT 1");
//$template->newLocals = (mysql_affected_rows()) ? 'yes' : 'no'; 

// AGGIORNAMENTI NELLE CATEGORIE GLOBALI?
$maxChars = 43;

//$uLT=$currentUser->pgLastVisit;
$me = $_SESSION['pgID'];

$reiss = mysql_query("SELECT catSuper,COUNT(*) as CPL  FROM cdb_topics,cdb_cats WHERE trackUsers = 0 AND  topicCat = catCode AND topicLastTime > $limiL AND topicID NOT IN (SELECT what FROM pg_visualized_elements WHERE pgID = $me AND type = 'CDB') AND restrictions IN (".PG::returnMapsStringFORDB($currentUser->pgAuthOMA).") GROUP BY catSuper"); 


$superCatsCounts=array('FL'=>0,'CIV'=>0,'HE'=>0,'MA'=>0);

while ($reso = mysql_fetch_array($reiss)) 
{
	$superCatsCounts[$reso['catSuper']]=$reso['CPL'];

} 
$reiss = mysql_query("SELECT pg_visualized_elements.time as seen, lastTopicEvent, topicID,topicTitle,topicLastTime,topicLastUser,topicType,topicColorExt, pgUser FROM pg_users,cdb_topics LEFT JOIN pg_visualized_elements ON (pg_visualized_elements.pgID = $me AND type = 'CDB' AND what = topicID) WHERE pg_users.pgID = topicLastUser AND (topicCat IN (".$currentLocation['catGDB'].",".$currentLocation['catDISP'].",".$currentLocation['catRAP'].")) ORDER by topicLastTime DESC LIMIT 7");


while ($reso = mysql_fetch_array($reiss))
{
$title = (strlen($reso['topicTitle']) > 27) ? substr($reso['topicTitle'],0,27).'...' : $reso['topicTitle'];
$lastTopicsLOC[] = array('ID' => $reso['topicID'],'seen' => (($reso['seen'] || $reso['topicLastTime'] < $limiL) ? true : false) , 'title' => $title, 'titleL' => $reso['topicTitle'], 'lastT' => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 'lastU' => $reso['pgUser'], 'classExt' =>$reso['topicColorExt'], 'lastTstamp' => $reso['topicLastTime'], 'topicType' => $reso['topicType']);
}

$re = mysql_fetch_array(mysql_query("SELECT uniform,pgSesso FROM pg_uniforms,pg_users WHERE pgID = ".$_SESSION['pgID']." AND pgMostrina = mostrina"));
$template->uniform = 'TEMPLATES/img/uniformi/'.$re['uniform'].strtolower($re['pgSesso']).'.png';

$rea = mysql_query("SELECT * FROM fed_master_news WHERE PLACE = '".$currentUser->pgLocation."' ORDER BY time DESC LIMIT 10");
$newsMas = array();
while($real = mysql_fetch_array($rea)){$real['datum'] = timeHandler::extrapolateDay($real['time']); $newsMas[] = $real;}


$template->superCatsCounts = $superCatsCounts;
$template->lastTopicsLOC = $lastTopicsLOC;

$template->newsMas = $newsMas;
if(PG::mapPermissions('G',$currentUser->pgAuthOMA)) $ddd = 'yes'; else $ddd=NULL;
$template->showSearchMaster = $ddd;
}

if(PG::mapPermissions('M',$currentUser->pgAuthOMA)) $template->isMaster = true;
if(PG::mapPermissions('SM',$currentUser->pgAuthOMA)) $template->isGlobalMaster = true;
if(PG::mapPermissions('A',$currentUser->pgAuthOMA)) $template->isAdmin = true;
$template->user = $currentUser;
$template->currentDate = $currentDate;
$template->curTime = $curTime;
$template->currentStarDate = $currentStarDate;
$template->gameName = $gameName;
$template->gameVersion = $gameVersion;
$template->debug = $debug;
$template->gameServiceInfo = $gameServiceInfo;
$template->gameOptions = $gameOptions;

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
	
include('includes/app_declude.php');

?>
