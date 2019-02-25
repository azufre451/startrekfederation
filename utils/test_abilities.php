<?php
chdir('../');
include('includes/app_include.php');

 
$a=new abilDescriptor(1);

print "<br />Umani (+53)<br />";

print $a->calculateVariationCost(array(
	array(38,0),
	array(31,1),
	array(5,0)
	)) +53; 


print "<br />BAJ<br />";

print $a->calculateVariationCost(array(
	array(7,1),
	array(12,3),
	array(18,1),
	array(21,2),
	array(19,0),
	array(5,0),
	array('WP',6),
	array('PE',5),
	));


print "<br />vulcaniani<br />";

print $a->calculateVariationCost(array(
	array(60,2),
	array(59,1),
	array(61,1),
	array(56,0),
	array(5,0),
	array('WP',5),
	array('HT',6),
	));

print "<br />AND<br />";

print $a->calculateVariationCost(array(
	array(10,2),
	array(21,1),
	array(17,1),
	array(52,2),
	array(4,3),
	array(5,0),
	array('DX',6),
	array('HT',6)
	));

print "<br />trilli<br />";

print $a->calculateVariationCost(array(
	array(21,1),
	array(20,1),
	array(9,0),
	array(35,2),
	array(5,0),
	array('IQ',6),
	array('HT',7)
	));


print "<br />Betamerde<br />";

print $a->calculateVariationCost(array(
	array(61,2),
	array(20,0),
	array(60,2), 
	array(5,0),
	array('HT',3),
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
	array(5,0),
	array('DX',3),
	array('WP',5),
	array('PE',6)
	));


print "<br />Terosiani<br />";

print $a->calculateVariationCost(array(
	array(42,1),
	array(18,1),
	array(8,2),
	array(11,2), 
	array(5,0),
	array('DX',7),
	array('WP',6),
	array('HT',4)
	));

//echo var_dump($aar);


 include('includes/app_declude.php');
?>

+2 Destrezza. I terosiani sono abili nuotatori, il che gli conferisce un senso dell'equilibrio spiccato.
+1 Forza di volonà. Il popolo terosiano, dopo le tante disgrazie nella sua storia, ha imparato a riprendersi e migliorarsi ogni volta.
-1 Costituzione. A causa della gravità ridotta di Terosia, i terosiani sono generalmente molto snelli.

+3 Economia. Sono mercanti.
+1 Acrobatica. I terosiani sono naturalmente predisposti alla grazia dei movimenti
+1 Astronomia. Il contatto con tantissime altre culture ha un impatto positivo sulla loro conoscenza di molti settori dello spazio adiacente.