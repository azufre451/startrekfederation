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

	public static function formatCDBLink($a)
	{
		//echo var_dump($a)."ZZZ<br/>";
		$ref=CDB::getPostFullLink($a[1]);
		if ($ref){

			$sec='<b>SECLAR</b> - '.$ref['seclar'];


			return "<a href=\"".$ref['link']."\" title=\" ► ". $ref['catName'] . " > ".$ref['topicTitle']." <hr/> <b>".$ref['author']. "</b> - ".$ref['dater']." <br/ /> ".$sec." \" class=\"tooltip internalCdbLink\">".(($ref['title'] != '') ? $ref['title'] : 'LINK')."</a>" ;
		}
		
		else return ''; 
	}



	public static function formatCDBLinkExternal($a,$mode='extended')
	{
		//echo var_dump($a)."ZZZ<br/>";
		$ref=CDB::getPostFullLink($a[1]);
		if ($ref){

			$sec='<b>SECLAR</b> - '.$ref['seclar'];

			#https://startrekfederation.it/cdb.php?topic=1501&page=1#10414
			#cdb.php?topic=
			$semiLink= str_replace('$ref[\'link\']','',$ref['link']);
			#cdbOpenToTopic('$semiLink')

			if ($mode=='extended')
				$inset=(($ref['title'] != '') ? $ref['title'] : 'LINK');
			elseif ($mode == 'small')
				$inset='<img src="TEMPLATES/img/interface/personnelInterface/external_link.png"/>';
			
			return "<a onclick=\"cdbOpenToTopic('".$semiLink."');\" href=\"javascript:void(0);\" title=\" ► ". $ref['catName'] . " > ".$ref['topicTitle']." <hr/> <b>".$ref['author']. "</b> - ".$ref['dater']." <br/ /> ".$sec." \" class=\"tooltip internalCdbLink\">".$inset."</a>" ;
			


		}
		else return ''; 
	}

	public static function formatDBLink($a)
	{
		//echo var_dump($a)."ZZZ<br/>";
		$ref=CDB::getDBLink($a[1]);
		if ($ref){


		
			 #cdbOpenToTopic('$semiLink')

			return "<a onclick=\"dbOpenToTopic('".$ref['ID']."');\" href=\"javascript:void(0);\" title=\" ► <b><span class='".$ref['coloring']."'>". $ref['catName'] . " </span> > ".( (trim($ref['tag']) != '') ? '['.$ref['tag'] . '] - ' : '').$ref['title']. "</b>\" class=\"tooltip dbLink\">".((trim($ref['tag']) != '') ? $ref['tag'] . ' - '. $ref['title'] : $ref['title'])."</a>" ;
		}
		else return ''; 
	}

	public static function replaceBBcodes($text)
	{
	    	$tRet = '';
			// BBcode array
			$find = array('~\[POST\](.*?)\[/POST\]~s');
			// HTML tags to replace BBcode
			
			//print_r(preg_search('~\[POST\](.*?)\[/POST\]~s', $text));exit;
			// Replacing the BBcodes with corresponding HTML tags
			if (basename($_SERVER['PHP_SELF']) == 'cdb.php') 
				$tRet = preg_replace_callback($find, 'self::formatCDBLink', $text);
			else
				$tRet = preg_replace_callback($find, 'self::formatCDBLinkExternal', $text);

			$find = array('~\[DB\](.*?)\[/DB\]~s');
				$tRet = preg_replace_callback($find, 'self::formatDBLink', $tRet);

			return $tRet;

	}

	public static function reduced_bbCode($str){ 

		$bbCode = array("[I]","[/I]","[U]","[/U]","\n");
		$htmlCode = array("<i>","</i>","<u>","</u>","<br />");

		return str_replace($bbCode,$htmlCode,htmlspecialchars($str));
	}

	public static function replaceBBcodes_db($text,$mode='internal')
	{
	    	
			// BBcode array
			$find = array('~\[DB\](.*?)\[/DB\]~s');
			// HTML tags to replace BBcode
			
			// Replacing the BBcodes with corresponding HTML tags
			return  preg_replace_callback($find, 'self::formatDBLink', $text);
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
		if (is_array($str)){

			if(preg_grep("/\[POST\]/", $str) || preg_grep("/\[DB\]/", $str) )
			{
						$q=array();
						foreach($str as $k=>$v)
							$q[$k] = str_replace($bbCode,$htmlCode,self::replaceBBcodes($v));	
						return $q;
			}
			else return str_replace($bbCode,$htmlCode,$str);

		}

		elseif (preg_match("/\[POST\]/", $str) | preg_match("/\[DB\]/", $str) ){
			return str_replace($bbCode,$htmlCode,self::replaceBBcodes($str));
		}

		else return str_replace($bbCode,$htmlCode,$str);

		
	}


	public static function getDBLink($DBID){
		$res=mysql_query("SELECT ID, title,tag,coloring,catName FROM db_elements,db_cats WHERE db_elements.catID = db_cats.catID AND ID = '$DBID' OR IDF = '$DBID' LIMIT 1 ");
		if(mysql_affected_rows())
		{
			
		
			return mysql_fetch_assoc($res);
		}
		else
			return 0;
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
					return array('link'=>"cdb.php?topic=$topicID&page=$pageNo#$postID",'seclar'=>$reseA['postSeclar'],'dater'=> timeHandler::timestampToGiulian($reseA['time']) ,'author' => $reseA['pgUser'],'title'=>$reseA['title'],'topicTitle'=>str_replace('"','\'',$reseA['topicTitle']),'catName'=>$reseA['catName']);
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