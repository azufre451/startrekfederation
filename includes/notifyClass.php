<?php


class NotificationEngine
{


	public static function getCDBUpdates($me,$assign)
	{

		$limitL = time() - 1296000; 
		if (!$me instanceof PG){
			$me=new PG($me);

		}

		mysql_query("SELECT topicID FROM cdb_cats,cdb_topics WHERE trackUsers = 0 AND topicLastTime > $limitL AND catCode = topicCat AND catSuper IN ('FL','CIV','HE','$assign','MA') AND restrictions IN (".PG::returnMapsStringFORDB($me->pgAuthOMA).") AND  topicID NOT IN (SELECT what FROM pg_visualized_elements WHERE type = 'CDB' AND pgID = ".$me->ID.")");  
		return mysql_affected_rows();

	}

	public static function getMyNotifications($me)
	{
		$limitL = time() - 1296000; 
		$res =mysql_fetch_assoc ( mysql_query(
		"
		( 
			SELECT CONCAT('a',recID) FROM pg_prestige_stories WHERE time > $limitL AND recID NOT IN (SELECT what FROM pg_visualized_elements WHERE type ='PRESTIGE' AND pgID = $me)
		)
		UNION
		( 
			SELECT CONCAT('b',newsID) FROM fed_news WHERE aggregator = 'FED' AND newsTime > $limitL AND newsID NOT IN (SELECT what FROM pg_visualized_elements WHERE type ='NEWS' AND pgID =  $me)
		) 
		UNION 
		( SELECT CONCAT('c',recID) FROM fed_master_news,pg_places WHERE time > $limitL AND placeID=place AND placeID IN (SELECT pgPlace FROM pg_incarichi WHERE  pgID =  $me)  AND recID NOT IN (SELECT what FROM pg_visualized_elements WHERE type ='MASTEREVENTS' AND pgID = $me)
		)
		UNION
		(
			SELECT CONCAT('d',recID) FROM pg_personal_notifications WHERE (owner = $me OR owner = 6) AND time > $limitL AND recID NOT IN (SELECT what FROM pg_visualized_elements WHERE type ='NOTIFS' AND (pgID =  $me OR owner = 6))
		)
		"

		));
		return mysql_affected_rows();

	}
	
}

?>