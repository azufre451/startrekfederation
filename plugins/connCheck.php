<?



function parseCountryCodeToFlag($code)
{
    $exceptions = [
        'en' => 'gb',
        'uk' => 'gb',
    ];

    $code = str_replace(array_keys($exceptions), array_values($exceptions), $code);

    $emoji = [];
    foreach(str_split($code) as $c) {
        if(($o = ord($c)) > 64 && $o % 32 < 27) {
            $emoji[] = hex2bin("f09f87" . dechex($o % 32 + 165));
            continue;
        }
        
        $emoji[] = $c;
    }

    return join($emoji);
}


function getIPConfictsOfUser($pgID){
	$PGs = array();
	$doppi = array();
	$threeMonths = time()-7776000;

	$logQ = mysql_query("SELECT IP,notes,time as lastLogin FROM connlog WHERE user = $pgID ORDER BY time DESC");
		
		
	while($logQA = mysql_fetch_array($logQ))
	{
		if(!isSet($lastIP)) $lastIP = array('lastIP' =>$logQA['IP'],'hostname'=>$logQA['notes'],'lastLogin'=>date('d-m-Y H:i',$logQA['lastLogin']));

		if(array_key_exists($logQA['IP'],$doppi))
			continue;
		
		$logE = mysql_query("SELECT DISTINCT pgID,pgUser,FROM_UNIXTIME(pgLastAct) as lastAct,ordinaryUniform,time FROM pg_users,connlog,pg_ranks WHERE prio = rankCode AND pgID = user AND pgID <> $pgID AND png = 0 AND time > $threeMonths AND IP = '".$logQA['IP']."' ORDER BY time DESC");

		while($pgg = mysql_fetch_assoc($logE)){
			if (!array_key_exists($pgg['pgID'], $PGs))
				$PGs[$pgg['pgID']] = $pgg;

			if(!array_key_exists($logQA['IP'], $doppi))
				$doppi[$logQA['IP']] = array();

			

			$doppi[$logQA['IP']][] = $PGs[$pgg['pgID']];
		}
	}

	return array('lastIP'=>$lastIP,'doppi'=>$doppi);
}

function checkIP($lastIP,$key)
{
		// Set the strictness for this query. (0 (least strict) - 3 (most strict))
		$strictness = 1;

		// You may want to allow public access points like coffee shops, schools, corporations, etc...
		$allow_public_access_points = 'true';

		// Reduce scoring penalties for mixed quality IP addresses shared by good and bad users.
		$lighter_penalties = 'false';

		// Create parameters array.
		$parameters = array(
			'user_agent' => $lastIP['ua'],
			'user_language' => $lastIP['ul'],
			'strictness' => $strictness,
			'allow_public_access_points' => $allow_public_access_points,
			'lighter_penalties' => $lighter_penalties
		);

		$formatted_parameters = http_build_query($parameters);
		$url = sprintf(
			'https://www.ipqualityscore.com/api/json/ip/%s/%s?%s', 
			$key,
			$lastIP['IP'], 
			$formatted_parameters
		);

		// Fetch The Result
		$timeout = 5;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

		$json = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($json, true);
		$result['lastIP'] = $lastIP;
		$result['flag'] = parseCountryCodeToFlag($result['country_code']);

		return $result;
}
?>