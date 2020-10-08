<?php
session_start();
include('includes/app_include.php');

include('includes/validate_class.php');

include('includes/bbcode.php');
include("includes/PHPTAL/PHPTAL.php"); //NEW
$action = (isSet($_GET['action'])) ? $_GET['action'] : NULL;

$vali = new validator();

if ($action == "doLogin")
{
	$user = ucfirst(addslashes($_POST['loginUser']));
	
	if($user != 'Rezaei' && $user != 'Prevost') exit;

	$pass = md5($_POST['loginPass']); 
	$res = mysql_query("SELECT * FROM pg_users WHERE pgUser = '$user' AND pgPass = '$pass'"); 
	
	if ($reso = mysql_fetch_array($res))
	{
		if ($reso['pgAuthOMA'] == 'BAN'){
			$pgID=$reso['pgID'];
			header("Location:login.php?mode=ban&pgID=$pgID");}

		else{
		$_SESSION['pgID'] = $reso['pgID'];
		PG::updatePresence($_SESSION['pgID']);
		mysql_query("INSERT INTO connlog (user,time,ip,notes) VALUES (".$_SESSION['pgID'].",$curTime,'".$_SERVER['REMOTE_ADDR']."','".gethostbyaddr($_SERVER['REMOTE_ADDR'])."')");
		header("Location:main.php");
		}

		exit;
	}
	else
	{
		include('includes/app_declude.php');
		header('Location:login.php?mode=e');
		exit;
	}
}

else if($action == "changePWD")
{	
	if (!isSet($_SESSION['pgID'])){  header("Location:index.php?login=do"); exit;}
	
	$oldPWD = md5($_POST['passAtt']);
	$newPWD = md5($_POST['passNeu1']);
	$current = $_SESSION['pgID'];
	
	$res = mysql_query("UPDATE pg_users SET pgPass = '$newPWD' WHERE pgID = $current AND pgPass = '$oldPWD'");
	
	
	if (mysql_affected_rows() == 0){$template = new PHPTAL('TEMPLATES/static/changeFailed.htm'); $esito = "NEGATIVO";}
	else{ $template = new PHPTAL('TEMPLATES/static/changeok.htm'); $esito = "POSITIVO";}
	$pgUser = PG::getSomething($current,'username');
	$head = 'From: webmaster@stfederation.it' . "\r\n";

	mail(PG::getSomething($current,'email'),"Star Trek Federation - Avviso di modifica della password","Star Trek Federation - AVVISO:\n\nCiao, $pgUser,\n\nTu, o qualcuno per te, ha provveduto ad eseguire una modifica della password di accesso a Star Trek Federation. L'operazione ha avuto esito $esito.\nLa presente unicamente per conoscenza. Nel caso tu non riconosca la modifica della password, ti invitiamo a contattattare un amministratore quanto prima.\n\nSaluti,\nIl team di Star Trek Federation",$head);
	
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	} exit;
}

else if($action == "recoverPWD")
{	
	
	$emailrecupero = addslashes($_POST['emailrecupero']);
	$passer = createRandomPassword();
	$pwd = md5($passer);
	
	$res = mysql_query("UPDATE pg_users SET pgPass = '$pwd' WHERE email = '$emailrecupero' AND email <> ''");
	
	if (mysql_affected_rows() == 0){$template = new PHPTAL('TEMPLATES/static/resetFail.htm');  $esito = "NEGATIVO";}
	else{ $template = new PHPTAL('TEMPLATES/static/resetok.htm'); $esito = "POSITIVO";
	
	$head = 'From: webmaster@stfederation.it' . "\r\n";
	
	mail($emailrecupero,"Star Trek Federation - Avviso di reset della password","Star Trek Federation - AVVISO:\n\nCiao, Ciao,\n\nTu, o qualcuno per te, ha provveduto ad eseguire un RESET della password di accesso a Star Trek Federation. L'operazione ha avuto esito $esito.\nLa presente unicamente per conoscenza.\n\nLa password, che e' stata generata in modo casuale e' la seguente:\n
	$passer\n\n\n Nel caso tu non riconosca la modifica della password, ti invitiamo a contattattare un amministratore quanto prima.\n\nSaluti,\nIl team di Star Trek Federation",$head);
	}


	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	} exit;
}


else if($action == 'ajax')
{
	$user = ucfirst(strtolower($vali->killChars(addslashes($_POST['usr']))));
	$pass = md5($_POST['pwd']);
	$res = mysql_query("SELECT pgID,pgUser FROM pg_users WHERE pgUser = '$user' AND pgPass = '$pass' AND pgAuthOMA <> 'BAN'");
	
	if ($reso = mysql_fetch_array($res))
	{
		$_SESSION['pgID'] = $reso['pgID'];
		PG::updatePresence($_SESSION['pgID']);
		mysql_query("INSERT INTO connlog (user,time,ip,isp) VALUES (".$_SESSION['pgID'].",$curTime,'".$_SERVER['REMOTE_ADDR']."','')");
		$iRet = array('user'=>$reso['pgUser']);
		echo json_encode($iRet);
	}
	
	else
	{
		include('includes/app_declude.php');
	}
}

else if ($action == "logout"){
	mysql_query("UPDATE pg_users SET pgLastVisit = ".time().", pgLastAct = ".(time()-1801)." WHERE pgID = ".$_SESSION['pgID']);
	session_destroy();
}

else if ($action == "mobile"){
	$template = new PHPTAL('TEMPLATES/login_mobile.htm');
	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
}

else
{
	$template = new PHPTAL('TEMPLATES/login.htm');
	$res = mysql_query("SELECT tipImage, tipText FROM cdb_tips WHERE active='A' ORDER BY RAND() LIMIT 1");
	
	if(isSet($_GET['test'])){
		$template->test=true;
	}
	
	$reso = mysql_fetch_array($res);
	$template->tipImage = $reso ['tipImage'];
	$template->tipText = $reso ['tipText'];
	
	$res =  mysql_query("SELECT imaURL FROM cdb_random_images WHERE type='INTRO' ORDER BY RAND() LIMIT 1");
	$reso = mysql_fetch_array($res);
	$template->backLoginImage = $reso['imaURL'];
	$template->gameServiceInfo = $gameServiceInfo;
	if(@$_GET['mode'] == 'e') $template->error = true;

	if(@$_GET['mode'] == 'ban'){
		$pgID = $vali->numberOnly($_GET['pgID']);

		$bQ=mysql_query("SELECT * FROM pg_users_temp_auths WHERE pgID = $pgID AND authType = 'BAN' ORDER BY authStart DESC LIMIT 1");

		

		if (mysql_affected_rows()){
			$banReason = mysql_fetch_assoc($bQ);
			$banReason['content'] = str_replace($bbCode,$htmlCode,$banReason['text']);	
			
		}
		else{
			$banReason = array('authEnd'=>0,'content'=>'');
			
		}
		
		$template->banReason=$banReason;
	}

	try 
	{
		echo $template->execute();
	}
		catch (Exception $e){
	echo $e;
	}
}

function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}


include('includes/app_declude.php');
?>