<?php
class CDB
{
	public static function mergeTopics($t1,$t2)
	{
		mysql_query("UPDATE cdb_posts SET topicID = $t2 WHERE topicID = $t1");
	}
	
	public static function linkTopic($t1,$t2)
	{
		if(CDB::topicExists($t1) && CDB::topicExists($t2))
		{
		CDB::mergeTopics($t1,$t2);
		mysql_query("UPDATE cdb_topics SET topicLink = $t2 WHERE topicID=$t1");
		}
	}
	
	public static function deleteTopic($t1)
	{
		mysql_query("DELETE FROM cdb_posts WHERE topicID=$t1");
		mysql_query("DELETE FROM cdb_topics WHERE topicID=$t1");
	}
	
	public static function moveTopic($t1,$c1)
	{
		mysql_query("UPDATE cdb_topics SET topicCat = '$c1' WHERE topicID = $t1");
	}
	
	public static function topicExists($t1)
	{
		mysql_query("SELECT 1 FROM cdb_topics WHERE topicID = $t1");
		return mysql_affected_rows();
	}
	
	public static function checkPostAccess($t1,$user){

		$userID = $user->ID;
		mysql_query("SELECT 1 FROM cdb_posts_seclarExceptions WHERE postID = '$t1' AND pgID = '$userID'");
		if (mysql_affected_rows()) return 1;

		return 0;
	}

	public static function getPostAccess($t1){

		
		$res = mysql_query("SELECT pgUser FROM cdb_posts_seclarExceptions,pg_users WHERE postID = '$t1' AND pg_users.pgID = cdb_posts_seclarExceptions.pgID");
		$re=array();
		while($resA = mysql_fetch_assoc($res))
		{
			$re[] = $resA['pgUser'];
		}
		
		return $re;
	}

}
?>