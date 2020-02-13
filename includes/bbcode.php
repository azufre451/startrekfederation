<?php

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
"<span class=\"cdbPostBigSize\">","</span>","</span>","<br />","<img src=\"","\"/>","<a target=\"_blank\" class=\"interfaceLink\" href=\"","\">LINK</a>",'script','script','<script','</script>','<div class="quoter">','</div>','<span class="obrindApproval">OK</span>',);

function reduced_bbCode($str){ 

$bbCode = array("[I]","[/I]","[U]","[/U]","\n");
$htmlCode = array("<i>","</i>","<u>","</u>","<br />");

return str_replace($bbCode,$htmlCode,htmlspecialchars($str));
}


	function formatCDBLink($a)
	{
	//echo var_dump($a)."ZZZ<br/>";
	$ref=CDB::getPostFullLink($a[1]);
	if ($ref){

		$sec='<b>SECLAR</b> - '.$ref['seclar'];


		return "<a href=\"".$ref['link']."\" title=\" ► ". $ref['catName'] . " > ".$ref['topicTitle']." <hr/> <b>".$ref['author']. "</b> - ".$ref['dater']." <br/ /> ".$sec." \" class=\"tooltip internalCdbLink\">".(($ref['title'] != '') ? $ref['title'] : 'LINK')."</a>" ;
	}
	else return ''; 
	}

function replaceBBcodes($text)
{
    	$text = strip_tags($text);
		// BBcode array
		$find = array('~\[POST\](.*?)\[/POST\]~s');
		// HTML tags to replace BBcode
		
		// Replacing the BBcodes with corresponding HTML tags
		return preg_replace_callback($find, 'formatCDBLink', $text);
}



?>