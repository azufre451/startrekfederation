<?php
chdir('../');
include('includes/app_include.php');

 
$a=new abilDescriptor(5);

print "<br />Umani (+53)<br />";

print $a->calculateVariationCost(array(
	array(38,0),
	array(31,1),
	)) +50; 


print "<br />BAJ<br />";

print $a->calculateVariationCost(array(
	array(7,1),
	array(12,3),
	array(18,1),
	array(21,2),
	array(19,0),
	array('WP',6),
	array('PE',5),
	));


print "<br />vulcaniani<br />";

print $a->calculateVariationCost(array(
	array(60,2),
	array(59,1),
	array(61,1),
	array(56,0),
	array('WP',6),
	array('HT',6),
	));

print "<br />AND<br />";

print $a->calculateVariationCost(array(
	array(10,2),
	array(21,1),
	array(17,1),
	array(52,2),
	array(4,3),
	array('DX',6),
	array('HT',6)
	));

print "<br />trilli<br />";

print $a->calculateVariationCost(array(
	array(21,1),
	array(20,1),
	array(9,0),
	array(35,2),
	array('IQ',6),
	array('HT',7)
	));


print "<br />Betamerde<br />";

print $a->calculateVariationCost(array(
	array(61,2),
	array(20,0),
	array(60,2), 
	array('HT',4),
	array('WP',6)
	));


print "<br />Keyral<br />";

print $a->calculateVariationCost(array(
	array(60,2), 
	array(61,1),
	array(12,2),
	array(11,1),
	array(21,0),
	array('HT',4),
	array('DX',6),
	array('WP',6)
	
	));

print "<br />Boliani<br />";

print $a->calculateVariationCost(array(
	array(7,2),
	array(11,2),
	array(35,2),
	array(34,0),
	array(20,2), 
	array(19,1), 
	array('DX',3),
	array('WP',5),
	array('PE',6)
	));

print "<br />Xenex<br />";

print $a->calculateVariationCost(array(
	array(12,1),
	array(21,2),
	array(52,1),
	array(56,1), 
	array('IQ',3),# 5 -2
	array('DX',6),# 5 +1
	array('WP',6),# 4 +1
	array('PE',6) # 4 +2
	));


print "<br />Terosiani<br />";

print $a->calculateVariationCost(array(
    array(42,1),
    array(18,1),
    array(8,2),
    array(11,2),
	array('DX',7),
	array('WP',5), 
	array('HT',4)
	));


print "<br />Caitiani<br />";

print $a->calculateVariationCost(array(
	array(10,1),
	array(12,3),
	array(21,2),
	array('DX',6),
	array('PE',6),
	array('HT',3)
	));




print "<br />Tellar<br />";

print $a->calculateVariationCost(array(
	array('DX',3),
	array('HT',6),
	array('WP',6),
	array(13,2),	
	array(8,2), //
	array(22,1),
	array(34,2),
	array(21,2),
	array(33,1)
	));
//echo var_dump($aar);


 include('includes/app_declude.php');
?>

