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



	public static function bbcode($str){
			$bbCode = array(
	"[B]","[/B]",
	"[I]","[/I]",
	"[U]","[/U]",
	"[CENTER]","[/CENTER]",
	"[LEFT]","[/LEFT]",
	"[RIGHT]","[/RIGHT]",
	"[COLOR=RED]","[COLOR=BLUE]",
	"[COLOR=YELLOW]","[COLOR=WHITE]",
	"[COLOR=GREEN]","[COLOR=GRAY]",
	"[SIZE=1]","[SIZE=2]",
	"[SIZE=3]","[/SIZE]","[/COLOR]","\n","[IMG]","[/IMG]",'[URL]','[/URL]','<script','</script>','<adminOsteScript14215','</adminOsteScript14215>','[QUOTE]','[/QUOTE]','[OB_OK]',);


	$htmlCode = array(
	"<b>","</b>",
	"<i>","</i>",
	"<u>","</u>",
	"<div style=\"text-align:center;\" align=\"center\">","</div>",
	"<p style=\"text-align:left\">","</p>",
	"<p style=\"text-align:right\">","</p>",
	"<span class=\"cdbPostRed\">","<span class=\"cdbPostBlue\">",
	"<span class=\"cdbPostYellow\">","<span class=\"cdbPostWhite\">",
	"<span class=\"cdbPostGreen\">","<span class=\"cdbPostGray\">",
	"<span class=\"cdbPostLittleSize\">","<span class=\"cdbPostNormalSize\">",
	"<span class=\"cdbPostBigSize\">","</span>","</span>","<br />","<img src=\"","\"/>","<a target=\"_blank\" class=\"interfaceLink\" href=\"","\">LINK</a>",'script','script','<script','</script>','<p class="quoter">','</p>','<span class="obrindApproval">OK</span>',);

		//replaceBBcodes
		if(preg_match("/\[POST\]/", $str))
		{
			return str_replace($bbCode,$htmlCode,replaceBBcodes($str));
			//return str_replace($bbCode,$htmlCode,$str);

		}
		else return str_replace($bbCode,$htmlCode,$str);
	}


	public static function getPostFullLink($postID){

		
		$res=mysql_query("SELECT topicID FROM cdb_posts WHERE ID = '$postID'");
		if (mysql_affected_rows())
		{
			$resA=mysql_fetch_assoc($res);
			$topicID=$resA['topicID'];
			$counterres = mysql_query("SELECT count(*) as contatore FROM cdb_posts WHERE topicID = $topicID");
			$counterresa = mysql_fetch_array($counterres);
			$postNo = ($counterresa['contatore'] != 0) ? $counterresa['contatore'] : 1;
	
			$elementsPerPage = 15;

			mysql_query("SET @row_number := 0");
			$rese=mysql_query("SELECT (@row_number:=@row_number + 1) AS num,title,topicTitle,ID,catName,pgUser,time,postSeclar FROM cdb_posts,cdb_topics,cdb_cats,pg_users WHERE cdb_posts.topicID = cdb_topics.topicID AND catCode = topicCat AND owner = pgID  AND cdb_topics.topicID = '$topicID' ORDER BY time");
			
			while($reseA=mysql_fetch_assoc($rese))
			{
				//echo $reseA['title'];
				if($reseA['ID'] == $postID)
				{
					$pageNo = ceil($reseA['num'] / $elementsPerPage);		
					return array('link'=>"cdb.php?topic=$topicID&page=$pageNo#$postID",'seclar'=>$reseA['postSeclar'],'dater'=> timeHandler::timestampToGiulian($reseA['time']) ,'author' => $reseA['pgUser'],'title'=>$reseA['title'],'topicTitle'=>$reseA['topicTitle'],'catName'=>$reseA['catName']);
				}
			}
			return 0;
			  
		}
		else return 0;

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