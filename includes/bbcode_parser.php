<?php
function translate_bbCode($str){   

$replace=array( 
'[B]' => '<b>',
'[/B]' => '</b>',
'[I]' => '<span style=\"font-style:italic\" ">',
'[/I]' => '<span>',
'[U]' => '<span style=\"text-decoration:underline\" ">',
'[/U]' => '<span>',
'[CENTER]' => '<div style=\"text-align:center;\" align=\"center\">',
'[/CENTER]' => '</div>',
'[RIGHT]' =>  "<p style=\"text-align:right\">","</p>",
'[/RIGHT]' => '</p>',
'[LEFT]' =>  "<p style=\"text-align:right\">","</p>", 
'[/LEFT]' => '</p>',
"[COLOR=RED]" => "<span class=\"cdbPostRed\">",
"[COLOR=BLUE]" => "<span class=\"cdbPostBlue\">",
"[COLOR=YELLOW]" => "<span class=\"cdbPostYellow\">",
"[COLOR=GREEN]" => "<span class=\"cdbPostGreen\">",
"[COLOR=WHITE]" => "<span class=\"cdbPostWhite\">",
"[COLOR=GRAY]" => "<span class=\"cdbPostGray\">",
"[/COLOR]" => '</span>',
"[SIZE=1]" => "<span class=\"cdbPostLittleSize\">",
"[SIZE=2]" => "<span class=\"cdbPostNormalSize\">",
"[SIZE=3]" => "<span class=\"cdbPostBigSize\">",
"[/SIZE]" => "</span>",
"\n" => "<br />",
"[IMG]" => '<img src=\"',
"[/IMG]" => '"></img>',
"[URL]" => "<a target=\"_blank\" class=\"interfaceLink\" href=\"",
"[/URL]" => "\">LINK</a>",
"[script]" => '-script-',
"[/script]" => '-script-',
"[adminOsteScript14215]" => '<script>',
"[/adminOsteScript14215]" => '</script>');
 
return str_replace(array_keys($replace),array_values($replace),$str);
return strtr($str,$replace);
}

?>