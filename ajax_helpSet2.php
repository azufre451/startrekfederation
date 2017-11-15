<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');
include('includes/validate_class.php');

$vali = new validator();

$a8 = $vali->numberOnly($_GET['a8']); // exp pbc 0-4
$a9 = $vali->numberOnly($_GET['a9']); // exp pbc 0-4
$a10 = $vali->numberOnly($_GET['a10']); // exp pbc 0-4
$a11 = $vali->numberOnly($_GET['a11']); // exp pbc 0-4
$a12 = $vali->numberOnly($_GET['a12']); // exp pbc 0-4
$a13 = $vali->numberOnly($_GET['a13']); // exp pbc 0-4
$a14 = $vali->numberOnly($_GET['a14']); // exp pbc 0-4
$a15 = $vali->numberOnly($_GET['a15']); // exp pbc 0-4
$a16 = $vali->numberOnly($_GET['a16']); // exp pbc 0-4
$a17 = $vali->numberOnly($_GET['a17']); // exp pbc 0-4
$a18 = $vali->numberOnly($_GET['a18']); // exp pbc 0-4
$a19 = $vali->numberOnly($_GET['a19']); // exp pbc 0-4
$a20 = $vali->numberOnly($_GET['a20']); // exp pbc 0-4
$a21= $vali->numberOnly($_GET['a21']); // exp pbc 0-4
$a22 = $vali->numberOnly($_GET['a22']); // exp pbc 0-4
$a23 = $vali->numberOnly($_GET['a23']); // exp pbc 0-4
$a24 = $vali->numberOnly($_GET['a24']); // exp pbc 0-4
$a25 = $vali->numberOnly($_GET['a25']); // exp pbc 0-4
$a26 = $vali->numberOnly($_GET['a26']); // exp pbc 0-4
$a27 = $vali->numberOnly($_GET['a27']); // exp pbc 0-4
$a28 = $vali->numberOnly($_GET['a28']); // exp pbc 0-4
$a29 = $vali->numberOnly($_GET['a29']); // exp pbc 0-4
$a29b = $vali->numberOnly($_GET['a29b']); // exp pbc 0-4
$a30 = $vali->numberOnly($_GET['a30']); // exp pbc 0-4
$a31 = $vali->numberOnly($_GET['a31']); // exp pbc 0-4
$a32 = $vali->numberOnly($_GET['a32']); // exp pbc 0-4
$a33 = $vali->numberOnly($_GET['a33']); // exp pbc 0-4
$a34 = $vali->numberOnly($_GET['a34']); // exp pbc 0-4
$age = $vali->numberOnly($_GET['age']); // exp pbc 0-4
$laurer = $vali->numberOnly($_GET['laurer']); // exp pbc 0-4

$r= mysql_query("SELECT pgPointTREK, pgPointPBC FROM pg_users WHERE pgID = ".$_SESSION['pgID']);
$re=mysql_fetch_array($r);
$P1 = $re['pgPointTREK'];
$P2 = $re['pgPointPBC'];
$P3 = round(($P1+$P2)/2);


//$L = ($laurer == 0) ? 0 : (($laurer <40) ? 2 : 1)

if($laurer == 0) $bounds=array(
"332"=>array(19,10,332),
"342"=>array(20,20,342),
"352"=>array(21,35,352),
"362"=>array(23,50,362),
"372"=>array(26,85,372));
/*,
"382"=>array(39,80,382),
"392"=>array(45,85,392));
*/

//else if($laurer >= 40){ $bounds=array("332"=>array(21,10,332),"342"=>array(23,20,342),"352"=>array(27,35,352),"362"=>array(31,50,362),"372"=>array(36,70,372),"382"=>array(41,80,382),"392"=>array(47,85,392));}
else if($laurer >= 40){ $bounds=array("332"=>array(21,10,332),"342"=>array(22,20,342),"352"=>array(23,35,352),"362"=>array(25,50,362),"372"=>array(27,85,372));}

//else if($laurer < 40) $bounds=array("332"=>array(24,10,332),"342"=>array(26,20,342),"352"=>array(30,35,352),"362"=>array(34,50,362),"372"=>array(39,70,372),"382"=>array(44,80,382),"392"=>array(50,85,392));
else if($laurer < 40) $bounds=array("332"=>array(24,10,332),"342"=>array(25,20,342),"352"=>array(26,35,352),"362"=>array(28,50,362),"372"=>array(30,85,372));

$i=0;
$ranksAv=array();

foreach($bounds as $elem)
{
	$i++;
	if($elem[0] <= $age && $elem[1] <= $P3) $ranksAv[] = $elem[2]; 
}

sort($ranksAv);
$subArray = array_slice($ranksAv,-1);

$com=-3;
$tat=0;
$ing=2;
$sci=2;
$med=1;

switch($a8)
{
	case 0: $com++; $sci++;break;
	case 1: $sci++; $ing++;break;
	case 2: $tat++; $ing++;break;
	case 3: $com++; $sci++;break;
	case 4: $tat++; $med++;break;
}
switch($a9)
{
	case 0: $ing++; $sci++;break;
	case 1: $com++; $med++;break;
	case 2: $tat++; $ing++;break;
	case 3: $com++; $tat++;break;
	case 4: $sci++; $med++;break;
}
switch($a10)
{
	case 0: $ing++; $sci++; $med++;break;
	case 1: $com++; $ing++; $tat++;break;
}
switch($a11)
{
	case 0: $ing++; $tat++; break;
	case 1: $ing++; $com++;break;
	case 2: $sci++; $med++;break;
	case 3: $com++; $tat++;break;
	case 4: $tat++; $sci++;break;
}
switch($a12)
{
	case 0: $ing++; $ing++; break;
	case 1: $tat++; $tat++;break;
	case 2: $com++; $com++;break;
	case 3: $med++; $med++;break;
	case 4: $sci++; $sci++;break;
}
switch($a13)
{
	case 0: $sci++; $ing++; break;
	case 1: $com++;break;
	case 2: $com++; $tat++;break;
}
switch($a14)
{
	case 0: $sci++; $med++; break;
	case 1: $com++; $tat++; $med++;break;
}
switch($a15)
{
	case 0: $com++; $com++; break;
	case 1: $tat++; $tat++;break;
	case 2: $ing++; $ing++;break;
	case 3: $sci++; $sci++;break;
	case 4: $med++; $med++;break;
}
switch($a16)
{
	case 0: $com++; $tat++; $med++;break;
	case 1: $sci++; $med++; $ing++;break;
	case 2: $sci++; $med++; break;
	case 3: $com++; $ing++; $tat++;break;
}
switch($a17)
{
	case 0: $ing++; $med++;break;
	case 1: $com++; $tat++;break;
	case 2: $sci++;break;
	case 3: $sci++; $med++;break;
}
switch($a18)
{
	case 0: $tat++; $com++;break;
	case 1: $sci++; $ing++; $med++;break;
	case 2: $com++; break;
}
switch($a19)
{
	case 0: $ing++; $ing++; break;
	case 1: $com++; $med++; $tat++; $sci++; break;
}
switch($a20)
{
	case 0: $com++; $tat++;break;
	case 1: $sci++; $ing++;break;
	case 2: $sci++;$ing++;break;
	case 3: $tat++;$ing++;break;
}
switch($a21)
{
	case 0: $com++; $tat++; break;
	case 1: $sci++; $med++;$ing++; break;
	case 2: $com++; $med++;$tat++; break;
}
switch($a22)
{
	case 0: $tat++; $sci++;$ing++;break;
	case 1: $com++; $tat++;$med++; break;
	case 2: $med++; $com++;break;
}
switch($a23)
{
	case 0: $com++; $tat++;break;
	case 2: $com--; $tat--;break;
}
switch($a24)
{
	case 0: $med++; $ing++; $sci++;break;
	case 1: $com++; $tat++;break;
	case 2: $com++; $med++;break;
}
switch($a25)
{
	case 0: $tat++; $ing++;break;
	case 1: $com++; $sci++;break;
	case 2: $tat++; $med++;break;
	case 3: $com++; $com++;break;
}
switch($a26)
{
	case 0: $tat++; $com++;break;
	case 1: $sci++; $sci++; $med++;break;
	case 2: $med++; $ing++;break;
	case 3: $ing++; $com++;break;
}
switch($a27)
{
	case 0: $med++; $com++;break;
	case 1: $sci++; $sci++; $med++;$ing++;break;
	case 2: $med++; $sci++; $tat++; break;
	case 3: $tat++; $com++;break;
}
switch($a28)
{
	case 0: $med++; $com++;break;
	case 1: $sci++; $com++; $ing++;break;
	case 2: $med++; $sci++;break;
	case 3: $tat++; $tat++;break;
}
switch($a29)
{
	case 0: $ing++; $sci++;break;
	case 1: $com++; $tat++; $ing++;break;
	case 2: $com++; $tat++;break;
	case 3: $med++; $sci++;break;
}
switch($a29b)
{
	case 0: $com--; $tat--;break;
	case 1: $sci++; $ing++; break;
}
switch($a30)
{
	case 0: $com++; $med++;break;
	case 1: $com++; $tat++; break;
	case 2: $sci++; $ing++;break;
}
switch($a31)
{
	case 0: $com++; $tat++;break;
	case 1: $ing++; $tat++; break;
	case 2: $med++; $sci++;$med++; break;
}
switch($a32)
{
	case 0: $ing++;$ing++;break;
	case 1: $com++;$tat++;$sci++;$med++;$ing++; break;
	case 2: $tat++;$com++;break;
	case 3: $sci++; $sci++;break;
	case 4: $com++;break;
}

switch($a33)
{
	case 0: $com++;$tat++;$sci++;$med++;$ing++; break;
	case 1: $com--; $tat--; break;
	case 2: $com--; $tat--; $com--; $tat--;break;
}
switch($a34)
{
	case 0: $med=$med-10;break;
	case 1: $tat=$tat-10;break;
	case 2: $sci=$sci-10;break;
	case 3: $com=$com-10;break;
	case 4: $ing=$ing-10;break;
}

$lauN = rand(86,100);
switch($laurer)
{
			case 0: $lauC = ""; break; //">Laurea in Lingue</option>
			case 2: $sci+=5; $lauC = "Laurea in Biologia - Votazione $lauN / 100"; break; //">Laurea in Lingue</option>
			case 2: $com+=5; $lauC = "Laurea in Lingue - Votazione $lauN / 100"; break; //">Laurea in Lingue</option>
			case 3: $com+=5; $lauC = "Laurea in Scienze Logistiche - Votazione $lauN / 100";break; //">Laurea in Scienze Logistiche</option>
			case 4: $com+=5; $lauC = "Laurea in Scienze Politiche - Votazione $lauN / 100";break; //">Laurea in Scienze Politiche</option>
			case 5: $com+=5; $lauC = "Laurea in Diplomazia Interculturale - Votazione $lauN / 100";break; //">Laurea in Diplomazia Interculturale</option>
			case 6: $com+=5; $lauC = "Laurea in Giurisprudenza - Votazione $lauN / 100";break; //">Laurea in Giurisprudenza</option>
			case 7: $sci+=5; $lauC = "Laurea in Scienze Naturali - Votazione $lauN / 100";break; //">Laurea in Scienze Naturali</option>
			case 8: $sci+=5; $lauC = "Laurea in Fisica - Votazione $lauN / 100";break; //">Laurea in Fisica</option>
			case 9: $sci+=5; $lauC = "Laurea in Chimica - Votazione $lauN / 100";break; //">Laurea in Chimica o Biologia</option>
			case 10: $sci+=5; $lauC = "Laurea in Geologia - Votazione $lauN / 100";break; //">Laurea in Geologia</option>
			case 11: $med+=10; $lauC = "Laurea in Medicina e Chirurgia - Votazione $lauN / 100";break; //">Laurea in Medicina e Chirurgia</option>
			case 12: $med+=5; $lauC = "Laurea in Infermieristica - Votazione $lauN / 100";break; //">Laurea in Infermieristica</option>
			case 13: $med+=5; $lauC = "Laurea in Psicologia - Votazione $lauN / 100";break; //">Laurea in Psicologia</option>
			case 14: $ing+=5; $lauC = "Laurea in Ingegneria Astronavale - Votazione $lauN / 100";break; //">Laurea in Ingegneria Astronavale</option>
			case 15: $ing+=5; $lauC = "Laurea in Ingegneria della Propulsione - Votazione $lauN / 100";break; //">Laurea in Ingegneria della Propulsione</option>
			case 16: $ing+=5; $lauC = "Laurea in Ingegneria delle Comunicazioni - Votazione $lauN / 100";break; //">Laurea in Ingegneria delle Comunicazioni</option>
			case 17: $ing+=5; $lauC = "Laurea in Ingegneria dei Sistemi Difensivi - Votazione $lauN / 100";break; //">Laurea in Ingegneria dei Sistemi Difensivi</option>
			case 18: $tat+=5; $lauC = "Laurea in Difesa e Sicurezza - Votazione $lauN / 100";break; //">Laurea in Difesa e Sicurezza</option>
			case 19: $com+=5; $lauC = "Laurea in Scienze Forensi - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 40: $com+=5; $lauC = "Diploma di Navigatore - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 41: $com+=5; $lauC = "Diploma di Assistente Amministrativo - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 42: $ing+=5; $lauC = "Diploma di Manutentore - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 43: $sci+=5; $lauC = "Diploma di Tecnico Astrometrico - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 44: $sci+=5; $lauC = "Diploma di Tecnico di Laboratorio - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 45: $tat+=5; $lauC = "Diploma di Addetto alla Sorveglianza - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 46: $ing+=5; $lauC = "Diploma di Assistente di Sala Macchine - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 47: $med+=5; $lauC = "Diploma di Operatore Sanitario - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
			case 48: $com+=10; $lauC = "Diploma di Volo - Votazione $lauN / 100";break; //">Laurea in Scienze Forensi</option>+++
}

$offset=array("MED"=>0,"SCI"=>2,"ING"=>4,"TAT"=>6,"COM"=>8);
$elem = array();

$elem[$com] = "COM";
$elem[$tat] = "TAT";
$elem[$med] = "MED";
$elem[$sci] = "SCI";
$elem[$ing] = "ING";

$dat=date('d.m.Y');
$rankLine='';
ksort($elem);
$subArraySec = array_slice($elem,-1);

$offs=$offset[$subArraySec[0]];

$code=$subArray[0]+$offs;

$stringer = "Code: ".$code." - ".$subArray[0]." + ".$offs."Age:$age laurea:".$laurer."COSEQ: COM".$com."-ING".$ing."-TAT".$tat."-SCI".$sci."-MED".$med;
mysql_query("UPDATE pg_users SET pgNote='$stringer' WHERE pgID = ".$_SESSION['pgID']);

$res = mysql_query("SELECT * FROM pg_ranks WHERE prio = $code AND prio IN(332,334,336,338,340,342,344,346,348,350,352,354,356,358,360,362,364,366,368,370,372,374,376,378,380,382,384,386,388,390,392,394,396,398,400,411,413,415,417,419)");
if($rea=mysql_fetch_array($res))
{
	$ima = ($laurer > 40) ? 'DIP_CIV.png' : 'LAU_CIV.png';
	if($laurer != 0) mysql_query("INSERT INTO pgDotazioni (pgID,dotazioneIcon,doatazioneType,dotazioneAlt) VALUES (".$_SESSION['pgID'].",'$ima','LAUR','$lauC')");
	PG::setMostrina($_SESSION['pgID'],$code); $yealla = 2382-$age;
	mysql_query("UPDATE pg_users SET pgFixYear='$yealla', pgFirst=1, pgNote = CONCAT(pgNote,'\n$dat --> Auto Assegnazione Grado') WHERE pgID = ".$_SESSION['pgID']);
	
	$rankLine .= '<img src="TEMPLATES/img/ranks/'.$rea['ordinaryUniform'].'.png" /> - '.$rea['Note'].'<br />';

	mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead, paddDeleted) VALUES (518,".$_SESSION['pgID'].", 'Sondaggio Orientativo', 'Ti e\' stato assegnato il grado automatico.<br />$rankLine<br />Buon Gioco!',".time().",0,0)");
	mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead, paddDeleted) VALUES (".$_SESSION['pgID'].",1, '[AUTOOrientamento] ', 'LAU: $laurer COM $com TAT $tat MED $med SCI $sci ING $ing <br />$rankLine<br />Buon Gioco!',".time().",0,0)");	
}
else{
mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead, paddDeleted) VALUES (518,".$_SESSION['pgID'].", 'Sondaggio Orientativo', 'Non e\' stato possibile assegnarti alcun grado, probabilmente relativo al fatto che hai selezionato un PG di età troppo bassa per aspirare ad un grado superiore al Marinaio Recluta. In caso non fosse così, contatta un amministratore per risolvere questo problema.',".time().",0,0)");

mysql_query("UPDATE pg_users SET pgFirst=1, pgNote = CONCAT(pgNote,'\n$dat --> Auto Assegnazione Grado - Norank') WHERE pgID = ".$_SESSION['pgID']);

}


echo json_encode(array('OK'));
?>