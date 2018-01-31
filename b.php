<?php 

// $re1 = mysql_query("SELECT * FROM connlog");

// $re2 = mysql_query("SELECT * FROM federation_chat");
// $re3 = mysql_query("SELECT * FROM cdb_posts");
// $re4 = mysql_query("SELECT paddTime as time FROM fed_pad");
// $re5 = mysql_query("SELECT * FROM fed_sussurri");

// $re6 = mysql_query("(SELECT time FROM federation_chat,pg_users WHERE pgID = sender AND pgAuthOMA = 'A') UNION (SELECT time FROM fed_sussurri,pg_users WHERE pgID = susFrom AND pgAuthOMA = 'A')"); 

// $re7 = mysql_query("(SELECT time FROM federation_chat WHERE sender = 1) UNION (SELECT time FROM fed_sussurri WHERE susFrom = 1)"); 
// $re8 = mysql_query("(SELECT time FROM federation_chat WHERE sender = 5) UNION (SELECT time FROM fed_sussurri WHERE susFrom = 5)"); 

// $re9 = mysql_query("(SELECT time FROM federation_chat WHERE sender = 598) UNION (SELECT time FROM fed_sussurri WHERE susFrom = 598)"); 
// $re10 = mysql_query("(SELECT time FROM federation_chat WHERE sender = 796) UNION (SELECT time FROM fed_sussurri WHERE susFrom = 796)"); 
// $re11 = mysql_query("(SELECT time FROM federation_chat WHERE sender = 814) UNION (SELECT time FROM fed_sussurri WHERE susFrom = 814)"); 
/*

$res = mysql_query("SELECT * FROM pg_brevetti_levels WHERE sector IN (25,26)");
$cumVal = array();
while ($ra = mysql_fetch_assoc($res))
{
	if(!array_key_exists($ra['owner'],$cumVal)) $cumVal[$ra['owner']] = $ra['value'];
	else $cumVal[$ra['owner']] = $cumVal + (int)($ra['value']);
	
	echo var_dump($ra);
} */


// $l=array();
// for($i=0;$i<7;$i++) $l[$i] = 0; 

// while($res = mysql_fetch_array($re1))
// {
	// $t = $res['time'];
	// $l[date('w',$t)]++;
	// echo $res['time'].$res['user']."<br />";
// }

// for($i=0;$i<7;$i++)
// {
	// echo $l[$i]."<br />";
// }

// echo "--CHAT-<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re2)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";


// echo "--POSTS-<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re3)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--PADDS-<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re4)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--SUX-<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re5)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--ADMINS -<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re6)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--IGOR -<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re7)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--moreno -<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re8)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--lloyd -<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re9)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--vos -<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re10)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";

// echo "--morgan -<br />";

// $l=array(); for($i=0;$i<7;$i++) $l[$i] = 0; 
// while($res = mysql_fetch_array($re11)) $l[date('w',$res['time'])]++; 
// for($i=0;$i<7;$i++) echo $l[$i]."<br />";


include('includes/app_include.php');

$not=mysql_query("SELECT sessionID FROM federation_sessions ORDER BY sessionID DESC LIMIT 40");

while($DDDe=mysql_fetch_assoc($not)){
	$DDD= $DDDe['sessionID'];


$re = mysql_fetch_assoc(mysql_query("SELECT AVG(realLen) AS averageLen FROM federation_chat WHERE ambient = (SELECT sessionPlace FROM federation_sessions WHERE sessionID = $DDD) AND time BETWEEN (SELECT sessionStart FROM federation_sessions WHERE sessionID = $DDD) AND (SELECT sessionEnd FROM federation_sessions WHERE sessionID = $DDD) AND type = 'DIRECT'"));
echo mysql_error();
$avig = $re['averageLen'];



$allacts=array();
$rel=mysql_query("SELECT pgUser,realLen, sender,time FROM federation_chat,pg_users WHERE sender = pgID AND ambient = (SELECT sessionPlace FROM federation_sessions WHERE sessionID = $DDD) AND time BETWEEN (SELECT sessionStart FROM federation_sessions WHERE sessionID = $DDD) AND (SELECT sessionEnd FROM federation_sessions WHERE sessionID = $DDD) AND type = 'DIRECT'");

while($res = mysql_fetch_assoc($rel))
	$allacts[]=$res;

			if((float)$avig < 800) $avig = 800;
			
			 
			$person = array(); 
			$ltime = 0;
			foreach($allacts as $var)
			{	  

				if ($ltime != 0 && $var['time'] > $ltime + 600){
					$ltime = $var['time']; continue;
				}

				$ppl = (string)$var['pgUser'];
				
				if(!array_key_exists($ppl,$person))	$person[$ppl] = array();
				
				$person[$ppl][] = $var['realLen'];
				$ltime = $var['time'];
			}
			$pointarray=array();
			
			foreach($person as $playerID => $player)
			{	

				foreach ($player as $action)
				{
					

					if(!array_key_exists($playerID,$pointarray))	$pointarray[$playerID] = 0.0;
					if($action > 500)
					{
						if($action >= 500 && $action <= $avig*1.3)
						{
							$pointarray[$playerID] += $action / (($avig*1.3) - 500) - (500 / (($avig*1.3) - 500));
							
							echo $DDD.'###'.$playerID.'###'.$action.'###'.($action / (($avig*1.3) - 500) - (500 / (($avig*1.3) - 500))) . '###'.($avig*1.3).'<br />';
						}
						else if ($action > $avig*1.3) { $pointarray[$playerID]+=1; 
						echo	$DDD.'###'.$playerID.'###'.$action.'###1<br />';
					}
					}
				}
			}
}
//			print_r(array_map('round',$pointarray));

exit;

?>						