<?php
session_start();
if (!isSet($_SESSION['pgID'])){ header("Location:index.php?login=do"); exit;}

include('includes/app_include.php');
include('includes/validate_class.php');
include('includes/cdbClass.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW 

PG::updatePresence($_SESSION['pgID']);
$currentUser = new PG($_SESSION['pgID']);

$safeSeclarCats = array(14,15);

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
		$order = $vali->numberOnly($_POST['cdbOrder']);
		$type = $vali->killChars(htmlentities(addslashes($_POST['cdbType'])));
		$cat = $vali->numberOnly($_POST['catCode']);
		
		
		mysql_query("INSERT INTO cdb_topics(topicTitle,topicLastTime,topicType,topicLastUser,topicCat,orderIndex,topicSeclar,topicColorExt)  VALUES('$title',".time().",'$type',".$_SESSION['pgID'].",$cat,$order,$seclar,'$colorExtension')");
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

else if(isSet($_GET['topicEe']))
{
	$nan = $vali->numberOnly($_POST['cdbID']);
	if (PG::mapPermissions("SM",$currentUser->pgAuthOMA))
	{
		$title = 		  $vali->killChars((addslashes($_POST['cdbTitle'])));
		$colorExtension = $vali->killChars((addslashes($_POST['cdbColor'])));
		$seclar = $vali->numberOnly($_POST['cdbSeclar']);
		$type = $vali->killChars((addslashes($_POST['cdbType'])));
		$topicID = $vali->numberOnly($_POST['cdbID']);
		
	mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE',topicTitle = '$title', topicType = '$type', topicSeclar = $seclar, topicColorExt = '$colorExtension', topicLastTime = (SELECT GREATEST(time,lastEdit) FROM cdb_posts WHERE topicID = $nan ORDER BY time DESC LIMIT 1), topicLastUser = IFNULL((SELECT owner FROM cdb_posts WHERE topicID = $nan ORDER BY time DESC LIMIT 1), ".$_SESSION['pgID'].") WHERE topicID = $topicID");
	mysql_query("UPDATE cdb_posts SET postSeclar = $seclar WHERE topicID = $topicID AND postSeclar < $seclar");
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
	
	$res = mysql_query("SELECT * FROM cdb_calls ORDER BY activeTo DESC");
	
	$calls = array();
	while ($resA = mysql_fetch_array($res))
	$calls[$resA['callType']][] = $resA;
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
		
		$ral = mysql_query("SELECT COUNT(*) as cont FROM pg_users_pointStory WHERE owner = $userID AND causeE LIKE '%$lClause%'"); 
		$rell = mysql_fetch_assoc($ral);
		if($rell['cont'] >= 3)
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
	
	if (PG::mapPermissions("M",$currentUser->pgAuthOMA) && ($_POST['usersMaster'] != ""))
	{
	$usersMaster = addslashes($_POST['usersMaster']);
	$usersMasterID = mysql_query("SELECT pgID FROM pg_users WHERE pgUser = '$usersMaster' AND png=1");
	if(mysql_affected_rows())
	{ $ider = mysql_fetch_array($usersMasterID);
		  $masterPnG = new PG($ider['pgID']);
		  $masterPnG->getIncarichi();
		
		$departmentString = ($masterPnG->pgDipartimento != '') ? '<br />Dipartimento '.$masterPnG->pgDipartimento : '';
		
		$tipoFirma = ($_POST['postFirma'] == "corta") ? "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$masterPnG->pgGrado." ".$masterPnG->pgUser : "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$masterPnG->pgGrado." ".$masterPnG->pgNomeC." ".$masterPnG->pgUser ." ".$masterPnG->pgNomeSuff."<br />".$masterPnG->pgIncarico."$departmentString<br />".(PG::getLocationName($masterPnG->pgAssign));
		$firma=addslashes($tipoFirma);
		mysql_query("INSERT INTO cdb_posts(title,content,owner,coOwner,time,topicID,postSeclar,postNotes,signature) VALUES('$title','$content',".$masterPnG->ID.",".$_SESSION['pgID'].",".time().",$topicCode,$seclar,'$notes','$firma')");
		mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE',topicLastUser = ".$masterPnG->ID.", topicLastTime = ".time()." WHERE topicID = $topicCode");
		header("Location:cdb.php?topic=$topicCode");
		exit;
	}
	}
	
	$currentUser->getIncarichi();
	$departmentString = ($currentUser->pgDipartimento != '') ? '<br />Dipartimento '.$currentUser->pgDipartimento : '';
	$tipoFirma = ($_POST['postFirma'] == "corta") ? "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$currentUser->pgGrado." ".$currentUser->pgUser : "<hr align=\"center\" size=\"1\" width=\"230\" color=\"#999\" />".$currentUser->pgGrado." ".$currentUser->pgNomeC." ".$currentUser->pgUser ." ".$currentUser->pgNomeSuff."<br />".$currentUser->pgIncarico."$departmentString<br />".(PG::getLocationName($currentUser->pgAssign));
	$firma=addslashes($tipoFirma);
	mysql_query("INSERT INTO cdb_posts(title,content,owner,time,topicID,postSeclar,postNotes,signature) VALUES('$title','$content',".$_SESSION['pgID'].",".time().",$topicCode,$seclar,'$notes','$firma')");
	mysql_query("UPDATE cdb_topics SET lastTopicEvent='CREATE', topicLastUser = ".$_SESSION['pgID'].", topicLastTime = ".time()." WHERE topicID = $topicCode");
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
	$template = new PHPTAL('TEMPLATES/cdb_search.htm');
	$s=false;
	if($reachPattern == 'DBS1' || $reachPattern == 'DBS2' || $reachPattern == 'DBS3')
	{
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
		else $res = mysql_query("SELECT ID as postID, cdb_posts.topicID, content, time,topicCat,postSeclar,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND restrictions = 'N' AND (content LIKE '%$searchKey%' OR title LIKE '%$searchKey%') ORDER BY time DESC");
 
		
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
			'content' => str_replace($bbCode,$htmlCode,$resA['content']),
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
		}
		$template->posts = $posts;
		$template->topics = $topics;
	}
	
	else if($reachPattern == 'AUT')
	{ 
		$posts = array();
		$topics = array();
		$res = mysql_query("SELECT ID as postID, cdb_posts.topicID as topID, content, time, postSeclar,postNotes,title,signature,pgUser,pgID, topicTitle, topicType, topicColorExt FROM cdb_posts,pg_users,cdb_topics,cdb_cats WHERE topicCat = catCode AND  cdb_posts.topicID = cdb_topics.topicID AND  owner = pgID AND (restrictions = 'N') AND pgUser = '$searchKey' ORDER BY time");

				while($resA = mysql_fetch_array($res))
		{
			$udel = (($resA['pgID'] == $_SESSION['pgID']) || $currentUser->pgAuthOMA == "A") ? 1 : NULL;
			$title = ($resA['title'] == "") ? '-' : $resA['title'];
			$posts[$resA['topID']][] = array(
			'ID' => $resA['postID'],
			'title' => $title,
			'pgUser' => $resA['pgUser'],
			'pgUserID' => $resA['pgID'],
			'content' => str_replace($bbCode,$htmlCode,$resA['content']),
			'time' => timeHandler::timestampToGiulian($resA['time']),
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
			'content' => str_replace($bbCode,$htmlCode,$resA['content']),
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

else if(isSet($_GET['topic'])) 
{
	$template = new PHPTAL('TEMPLATES/cdb_topic.htm');
	$topic = $vali->numberOnly($_GET['topic']); 

	

	
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
			'content' => str_replace($bbCode,$htmlCode,$resE['content']),
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
			'content' => str_replace($bbCode,$htmlCode,$resA['content']),
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
	$res = mysql_query("SELECT catCode, catName, restrictions, catDesc, IFNULL((SELECT topicLastTime FROM cdb_topics WHERE topicCat = catCode ORDER BY topicLastTime DESC LIMIT 1),0) as topicLastTime FROM cdb_cats WHERE catSuper = '$aggregator' ORDER BY catName");
	$cats=array();
	$elements=false;
	while ($reso = mysql_fetch_array($res))
	{
		if (PG::mapPermissions($reso['restrictions'],$currentUser->pgAuthOMA))
		{
			$cats[] = array("ID" => $reso["catCode"], "name" => $reso["catName"], "desc" => $reso['catDesc'], "last" => $reso['topicLastTime']);
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
		$resI = mysql_query("SELECT topicID,lastTopicEvent,topicTitle,topicTitle,topicLastTime,pgUser,topicColorExt FROM cdb_topics,pg_users WHERE topicLastUser=pgID AND topicCat = $category AND topicType='I' ORDER BY orderindex DESC,topicTitle");
		$resA = mysql_query("SELECT topicID,lastTopicEvent,topicTitle,topicTitle,topicLastTime,pgUser,topicColorExt FROM cdb_topics,pg_users WHERE topicLastUser=pgID AND topicCat = $category AND topicType='A' ORDER BY orderindex DESC,topicTitle");
		$resN = mysql_query("SELECT topicID,lastTopicEvent,topicTitle,topicTitle,topicLastTime,pgUser,topicColorExt FROM cdb_topics,pg_users WHERE topicLastUser=pgID AND topicCat = $category AND topicType='N' ORDER BY orderindex DESC,topicLastTime DESC");
	
		while ($reso = mysql_fetch_array($resI))
		{
			$topicsI[] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 	"lastU" => $reso['pgUser'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);
		}
	
		while ($reso = mysql_fetch_array($resA))
		{
			$topicsA[] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 	"lastU" => $reso['pgUser'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);
		}
		
		while ($reso = mysql_fetch_array($resN))
		{
			$topicsN[] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 	"lastU" => $reso['pgUser'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);
		}
	}
	else {header("Location:cdb.php"); exit;}
	$template->topicsI = $topicsI;
	$template->topicsA = $topicsA;
	$template->topicsN = $topicsN;
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

		$currentLocation = (PG::getLocation($currentUser->pgLocation));
		 $res = mysql_query("SELECT topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,topicColorExt FROM cdb_topics,pg_users WHERE topicLastUser=pgID AND (topicCat IN (".$currentLocation['catGDB'].",".$currentLocation['catDISP'].",".$currentLocation['catRAP'].")) ORDER by topicLastTime DESC LIMIT 15");

		$topics['LOCAL'] = array();
		while ($reso = mysql_fetch_array($res))
			$topics['LOCAL'][] = array("ID" => $reso['topicID'],"lastTopicEvent" => $reso['lastTopicEvent'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);
		
		// FLOTTA
		$res = mysql_query('SELECT topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,topicColorExt FROM cdb_topics,pg_users,cdb_cats WHERE topicLastUser=pgID AND topicCat = catCode AND catSuper = \'FL\' ORDER BY topicLastTime DESC LIMIT 10');
		while ($reso = mysql_fetch_array($res))
			$topics['FL'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'],"lastTopicEvent" => $reso['lastTopicEvent'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);

		$res = mysql_query('SELECT topicID,lastTopicEvent,topicTitle,topicLastTime,pgUser,topicType,topicColorExt FROM cdb_topics,pg_users,cdb_cats WHERE topicLastUser=pgID AND topicCat = catCode AND catSuper = \'CIV\' ORDER BY topicLastTime DESC LIMIT 10');
		while ($reso = mysql_fetch_array($res))
			$topics['CIV'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'],"lastTopicEvent" => $reso['lastTopicEvent'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);

		$res = mysql_query('SELECT restrictions,lastTopicEvent,topicID,topicTitle,topicLastTime,pgUser,topicType,topicColorExt FROM cdb_topics,pg_users,cdb_cats WHERE topicLastUser=pgID AND topicCat = catCode AND catSuper = \'HE\' AND restrictions IN (' . PG::returnMapsStringFORDB($currentUser->pgAuthOMA). ') ORDER BY topicLastTime DESC LIMIT 10');
		while ($reso = mysql_fetch_array($res))
		$topics['HE'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'],"lastTopicEvent" => $reso['lastTopicEvent'], 'topicType' => $reso['topicType'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);
		

		$res = mysql_query('SELECT topicID,lastTopicEvent,topicTitle,restrictions,topicLastTime,pgUser,topicType,topicColorExt FROM cdb_topics,pg_users,cdb_cats WHERE topicLastUser=pgID AND topicCat = catCode AND catSuper = \'MA\' AND catCode <> 47 AND restrictions IN (' .PG::returnMapsStringFORDB($currentUser->pgAuthOMA) .') ORDER BY topicLastTime DESC LIMIT 15');		while ($reso = mysql_fetch_array($res))
		{	
			$topics['MA'][] = array("ID" => $reso['topicID'], "title" => $reso['topicTitle'],"lastTopicEvent" => $reso['lastTopicEvent'], "lastT" => timeHandler::timestampToGiulian($reso['topicLastTime']), "lastU" => $reso['pgUser'], 'topicType' => $reso['topicType'], "classExt" => $reso['topicColorExt'], "lastTstamp" => $reso['topicLastTime']);	
			$masters = true;
		}	
	if (isSet($masters)) $template->showMA = true;
	$template->topics = $topics;
}

else if (isSet($_POST['emoSel']))
{
	$emo = $_POST['emoSel'];
	$id = $currentUser->ID;
	
	$my = mysql_query("SELECT rankCode FROM pg_users WHERE pgID = $id");
	$myA = mysql_fetch_array($my);
	$parameter = $myA['rankCode'];
	
	if ($emo == "SER") $query ="UPDATE pg_users SET pgMostrina = (SELECT ordinaryUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "HIG") $query ="UPDATE pg_users SET pgMostrina = (SELECT dressUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "TAT") $query ="UPDATE pg_users SET pgMostrina = (SELECT tacticalUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "DES") $query ="UPDATE pg_users SET pgMostrina = (SELECT desertUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "POL") $query ="UPDATE pg_users SET pgMostrina = 'POL' WHERE pgID = $id";
	else if ($emo == "FAT") $query ="UPDATE pg_users SET pgMostrina = (SELECT faticaUniform FROM pg_ranks WHERE prio = '$parameter' ) WHERE pgID = $id";
	else if ($emo == "EVA") $query ="UPDATE pg_users SET pgMostrina = 'EVA' WHERE pgID = $id";
	else if ($emo == "NBC") $query ="UPDATE pg_users SET pgMostrina = 'NBC' WHERE pgID = $id";
	else if ($emo == "VOL") $query ="UPDATE pg_users SET pgMostrina = 'VOL' WHERE pgID = $id";
	else if ($emo == "CAM") $query ="UPDATE pg_users SET pgMostrina = 'CAM' WHERE pgID = $id";
	else $query ="UPDATE pg_users SET pgMostrina = 'CIV' WHERE pgID = $id";
	
	mysql_query($query);
	
	header('Location:cdb.php');
	exit;
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
	 
	$curID = $_SESSION['pgID'];
	$oneMonth = $curTime - 2505600; 
	$idR = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgLock=0 AND png=0 AND pgAssign = '$place' AND pgLastAct >= $oneMonth");
	while($res = mysql_fetch_assoc($idR))
	{ 
			$idA = $res['pgID']; 
			mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (518, $idA, 'NUOVO EVENTO MASTER', 'È stato inserito un nuovo evento master ($eventTitle) nel Computer di Bordo. Controlla la sezione Eventi Master del computer per visualizzarlo!',$curTime,0)");
	}
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
$queryLocal = mysql_query("SELECT 1 FROM cdb_topics WHERE topicCat IN (".$currentLocation['catGDB'].",".$currentLocation['catDISP'].",".$currentLocation['catRAP'].") AND topicLastTime > ".$currentUser->pgLastVisit." ORDER BY topicLastTime DESC LIMIT 1");
$template->newLocals = (mysql_affected_rows()) ? 'yes' : 'no'; 

// AGGIORNAMENTI NELLE CATEGORIE GLOBALI?
$maxChars = 43;

$uLT=$currentUser->pgLastVisit;

$reiss = mysql_query("SELECT catSuper,COUNT(*) as CPL  FROM cdb_topics,cdb_cats WHERE topicCat = catCode AND topicLastTime > $uLT GROUP BY catSuper"); 

$superCatsCounts=array('FL'=>0,'CIV'=>0,'HE'=>0,'MA'=>0);

while ($reso = mysql_fetch_array($reiss)) 
{
	$superCatsCounts[$reso['catSuper']]=$reso['CPL'];

}





/*


while ($reso = mysql_fetch_array($reiss))
{
$title = (strlen($reso['topicTitle']) > $maxChars) ? substr($reso['topicTitle'],0,$maxChars).'...' : $reso['topicTitle'];
$lastTopicsFL[] = array('ID' => $reso['topicID'],"lastTopicEvent" => $reso['lastTopicEvent'], 'title' => $title, 'titleL' => $reso['topicTitle'], 'lastT' => timeHandler::timestampToGiulian($reso['topicLastTime']), 'lastU' => $reso['pgUser'], 'classExt' =>$reso['topicColorExt'], 'lastTstamp' => $reso['topicLastTime'], 'topicType' => $reso['topicType'], 'catName' => $reso['catName']);
} 
$reiss = mysql_query("SELECT restrictions,lastTopicEvent, topicID,topicTitle,topicLastTime,topicLastUser,topicType,topicColorExt, pgUser, catName, catSuper FROM cdb_topics,cdb_cats,pg_users WHERE pgID = topicLastUser AND topicCat = catCode AND catSuper = 'CIV' ORDER by topicLastTime DESC LIMIT 5");
while ($reso = mysql_fetch_array($reiss))
{
$title = (strlen($reso['topicTitle']) > $maxChars) ? substr($reso['topicTitle'],0,$maxChars).'...' : $reso['topicTitle'];
$lastTopicsCI[] = array('ID' => $reso['topicID'],"lastTopicEvent" => $reso['lastTopicEvent'], 'title' => $title, 'titleL' => $reso['topicTitle'], 'lastT' => timeHandler::timestampToGiulian($reso['topicLastTime']), 'lastU' => $reso['pgUser'], 'classExt' =>$reso['topicColorExt'], 'lastTstamp' => $reso['topicLastTime'], 'topicType' => $reso['topicType'], 'catName' => $reso['catName']);
}

$reiss = mysql_query("SELECT restrictions,lastTopicEvent, topicID,topicTitle,topicLastTime,topicLastUser,topicType,topicColorExt, pgUser, catName, catSuper FROM cdb_topics,cdb_cats,pg_users WHERE pgID = topicLastUser AND topicCat = catCode AND catSuper = 'HE' AND restrictions IN (".PG::returnMapsStringFORDB($currentUser->pgAuthOMA).") ORDER by topicLastTime DESC LIMIT 5");

while ($reso = mysql_fetch_array($reiss))
{
$title = (strlen($reso['topicTitle']) > $maxChars) ? substr($reso['topicTitle'],0,$maxChars).'...' : $reso['topicTitle'];
$lastTopicsHE[] = array('ID' => $reso['topicID'],"lastTopicEvent" => $reso['lastTopicEvent'], 'title' => $title, 'titleL' => $reso['topicTitle'], 'lastT' => timeHandler::timestampToGiulian($reso['topicLastTime']), 'lastU' => $reso['pgUser'], 'classExt' =>$reso['topicColorExt'], 'lastTstamp' => $reso['topicLastTime'], 'topicType' => $reso['topicType'], 'catName' => $reso['catName']);
}

/*$reiss = mysql_query("SELECT restrictions,lastTopicEvent, topicID,topicTitle,topicLastTime,topicLastUser,topicType,topicColorExt, pgUser, catName, catSuper FROM cdb_topics,cdb_cats,pg_users WHERE pgID = topicLastUser AND topicCat = catCode AND catSuper = 'MA' AND catCode <> 47 AND restrictions IN (".PG::returnMapsStringFORDB($currentUser->pgAuthOMA).") ORDER by topicLastTime DESC LIMIT 3");

while ($reso = mysql_fetch_array($reiss))
{
	$title = (strlen($reso['topicTitle']) > $maxChars) ? substr($reso['topicTitle'],0,$maxChars).'...' : $reso['topicTitle'];
	$lastTopicsMA[] = array('ID' => $reso['topicID'], 'title' => $title, 'titleL' => $reso['topicTitle'], 'lastT' => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 'lastU' => $reso['pgUser'], 'classExt' =>$reso['topicColorExt'], 'lastTstamp' => $reso['topicLastTime'], 'topicType' => $reso['topicType'], 'catName' => $reso['catName']);
}

*/

$reiss = mysql_query("SELECT lastTopicEvent, topicID,topicTitle,topicLastTime,topicLastUser,topicType,topicColorExt, pgUser FROM cdb_topics,pg_users WHERE pgID = topicLastUser AND (topicCat IN (".$currentLocation['catGDB'].",".$currentLocation['catDISP'].",".$currentLocation['catRAP'].")) ORDER by topicLastTime DESC LIMIT 7");


while ($reso = mysql_fetch_array($reiss))
{
$title = (strlen($reso['topicTitle']) > 27) ? substr($reso['topicTitle'],0,27).'...' : $reso['topicTitle'];
$lastTopicsLOC[] = array('ID' => $reso['topicID'], 'title' => $title, 'titleL' => $reso['topicTitle'], 'lastT' => timeHandler::timestampToGiulian($reso['topicLastTime']),"lastTopicEvent" => $reso['lastTopicEvent'], 'lastU' => $reso['pgUser'], 'classExt' =>$reso['topicColorExt'], 'lastTstamp' => $reso['topicLastTime'], 'topicType' => $reso['topicType']);
}

$re = mysql_fetch_array(mysql_query("SELECT uniform,pgSesso FROM pg_uniforms,pg_users WHERE pgID = ".$_SESSION['pgID']." AND pgMostrina = mostrina"));
$template->uniform = 'TEMPLATES/img/uniformi/'.$re['uniform'].strtolower($re['pgSesso']).'.png';

$rea = mysql_query("SELECT * FROM fed_master_news WHERE PLACE = '".$currentUser->pgLocation."' ORDER BY time DESC LIMIT 10");
$newsMas = array();
while($real = mysql_fetch_array($rea)){$real['datum'] = timeHandler::extrapolateDay($real['time']); $newsMas[] = $real;}


$template->superCatsCounts = $superCatsCounts;
$template->lastTopicsLOC = $lastTopicsLOC;

$template->newsMas = $newsMas;
if(PG::mapPermissions('JM',$currentUser->pgAuthOMA)) $ddd = 'yes'; else $ddd=NULL;
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
