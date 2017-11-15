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
	
}
?>