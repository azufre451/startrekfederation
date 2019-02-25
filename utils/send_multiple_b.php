<?php
session_start();
chdir ("../");

if (!isSet($_SESSION['pgID'])){echo "Errore di Login. Ritorna alla homepage ed effettua il login correttamente!"; exit;}
    
include('includes/app_include.php');
include('includes/validate_class.php');

PG::updatePresence($_SESSION['pgID']);

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_DEPRECATED);

$vali = new validator();
$currentUser = new PG($_SESSION['pgID']);

$targets=mysql_query("SELECT pgID FROM fed_pad,pg_users WHERE paddFrom = 1414 AND LOWER(paddTitle) = 'partecipazioni' AND paddTo = pgID AND png = 1");

function return_random($ar){

	return $ar[array_rand($ar)];
}
$toP = new PG(1414);
while ($at = mysql_fetch_assoc($targets))
{ 

	$p=new PG($at['pgID']);
	if ($p->pgUser == 'Zor' || $p->pgUser == 'Blackwood' || $p->pgUser == 'Velikanova' || $p->pgUser == 'Brixington')
		continue;

	$particle = ($p->pgSesso == 'M') ? 'o' : 'a';


	$preambles=array(
	'Gentilissima,',
	'Stimatissima,',
	'Stimatissimo Ten. Com. Brennan',
	'Congratulazioni vivissime, Tenente Comandante!',
	);

	$declines_pre1=array(
	'Le faccio i miei migliori auguri perché la cerimonia sia un momento di felicità e gioia,',
	'Sono contentissim'.$particle.' di ricevere questa bellissima notizia, ',
	'Una notizia davvero entusiasmante, visti anche i nostri trascorsi a bordo della Starbase Tycho. Abbiamo bisogno di momenti felici, ',
	'La vostra decisione non può che trovare la mia più completa approvazione, sono felicissim'.$particle.' del vostro invito, ',
	'Vi auguro che la luce di mille stelle possa splendere sulla vostra unione,',
	'Non potevate dare a tutta questa stazione notizia più bella,',
	'Leggervi è motivo di estrema gioia, e sono contentissim'.$particle.' di ricevere notizie così positive da parte di due ufficiali così ben voluti da tutti,',
	'Le mie felicitazioni scritte possono solo avvicinarsi lontanamente alla gioia che ho provato nel ricevere questo invito,',
	'Un matrimonio è sempre un evento bellissimo, da vivere e preparare,',
	'Un matrimonio di questo calibro sicuramente è una occasione di giubilo per tutta la stazione,');



	$declines_pre2=array('e spero di poterle fare le mie congratulazioni di persona.',
	'e confido di poterle esporre le mie felicitazioni di persona quanto prima.',
	'e non mancherò di cercare di incorciarla in servizio per farle le mie congratulazioni il più presto possibile.',
	'e sarebbe un piacere poterle dire che parteciperò alla vostra bella cerimonia.',
	'e, compatibilmente con i miei tanti impegni, sarebbe bellissimo poter partecipare.',
	'e vorrei poterle dire fin da subito che presenzierò in prima fila alla cerimonia.',
	'e le preannuncio che cercherò di placcarla al più presto per farle le mie felicitazioni dal vivo!',
	'Tuttavia, mi sfugge la ragione del suo (seppur cortese) invito, conoscendoci noi appena poco più dello stretto necessario professionale.');



	$declines_pre3=array('Purtroppo, devo essere costrett'.$particle.' a declinare il vostro cortese invito.',
	'Temo, ahimé, di non poter presenziare alla cerimonia, però.',
	'Al vostro invito, per quanto gradito, devo purtroppo opporre un educato rifiuto.',
	'Confido nella sua comprensione, ma devo purtroppo declinare il suo invito.',
	'Nonostante partecipare sarebbe, per me, un onore, temo di essere impossibilitat'.$particle.' a farlo.',
	'Le premetto che farò di tutto per partecipare. Sfortunatamente, ciò potrebbe non essere abbastanza.',
	'Purtroppo non potrò partecipare, per quanto sarebbe bello poter essere dei vostri.',
	'I miei impegni, però, rendono la mia partecipazione alla cerimonia, un task di difficile realizzazione.',
	'Temo però di non riuscire a partecipare.',
	'Ho paura di non essere cert'.$particle.' della mia presenza per il 29 Luglio.');


	$declines_pre4=array('Proprio in quella data sarò impegnat'.$particle.' per via di',
	'Nei giorni immediatamente vicini, dovrò assistere ad una serie di ',
	'Ho confermato la mia partecipazione ad',
	'Ho in forse un impegno di sezione per ',
	'Sarò completamente impossibilitat'.$particle.' a partecipare per via di',
	'Il prossimo 29 Luglio sarò di servizio per ',
	'Sarò impegnat'.$particle.' in maniera imprevedibile per ',
	'Non mi ritengo abbastanza vicino alle vostre persone per sentirmi a mio agio a partecipare ad un momento così importante per voi. Inoltre, ho dato parziale conferma, per quel giorno, per',
	'Il Governo mi ha dato mandato, per quella data, di intervenire in questioni relative a',
	'Sarò impegnat'.$particle.' nel',
	'Sarò purtroppo impegnato per tutta la giornata per via di');

	$declines_pre5=array('una importante riunione dipartimentale, da lungo tempo indetta.',
	'attività addestrative presso Terosia che mi porteranno a non godere di un alloggio stabile sulla Tycho.',
	'una missione di pianificata da tempo, e non poosso garantire la mia presenza.',
	'rilievi tecnici improrogabili da condursi presso il limitare del Sistema Terosiano.',
	'aggiornamenti agli inventari delle stive di carico dei livelli inferiori',
	'ridistribuire il carico energetico dei sottosistemi non vitali dei ponti superiori.',
	'ricostruzioni mirate di sottosistemi critici alla navigazione',
	'una importante seminario tecnico, oramai impossibile da disdire');

	$declines_pre6=array('Ci tengo a precisare che, qualora la situazione dovesse cambiare, farò di tutto per essere presente, e vi informerò per tempo.',
	'Nel caso le circostanze mi permettessero di poter presenziare, non mancherò di farvelo sapere.',
	'Non penso purtroppo che la situazione possa volgere in maniera diversa, ma vi informerò qualcosa la situazione dovesse mutare.',
	'Pur essendo la mia partecipazione estremamente improbabile, vi informerò repentinamente di eventuali cambi di programma.',
	'Con la gioia nel cuore e tantissimi cari saluti,',
	'Nel rinnovarLe ancora le mie felicitazioni, le faccio i miei più cari auguri,',
	'Ancora congratulazioni e grazie in ogni caso per il suo invito,',
	'Mi faccia sapere quando avrà un momento libero per poterla disturbase di persona, così da potermi scusare anticipatamente per la mia assenza');

	$declines_pre7=array('Cordiali Saluti','Un caro saluto','Saluti','Grazie ancora','Nella speranza che stia bene','I miei migliori auguri','La ringrazio immenssamente per i suoi pensieri nei miei confronti');



	$UTR= return_random($preambles) . '<br /><br />' . return_random($declines_pre1). ' ' . return_random($declines_pre2) . ' ' . return_random($declines_pre3) . ' ' . return_random($declines_pre4) . '  ' . return_random($declines_pre5) . ' ' .  return_random($declines_pre6) . '<br /><br /><br />' . return_random($declines_pre7) . ',<br />' . $p->pgNomeC . ' ' . $p->pgUser ;
	
	$toP->sendPadd('Re: Partecipazione',$UTR,$p->ID);
	#echo $UTR;

	
}





include('includes/app_declude.php');
