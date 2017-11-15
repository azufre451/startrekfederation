<?php
session_start();
if (!isSet($_SESSION['pgID'])){header('Location:login.php');
 exit;
}include('includes/app_include.php');
include('includes/validate_class.php');

$vali = new validator();

$a1 = $vali->numberOnly($_GET['a1']); // PBC 0-2
$a2 = $vali->numberOnly($_GET['a2']); // on off 0-2
$a3 = $vali->numberOnly($_GET['a3']); // exp pbc 0-4

$sumGDR = round(($a1+$a2+$a3)*100 / 8);

$a4 = $vali->numberOnly($_GET['a4']); // exp  trek 0-4
$a5 = $vali->numberOnly($_GET['a5']); // ds9 0-3 6
$a6 = $vali->numberOnly($_GET['a6']); // film 0-2
$a7 = $vali->numberOnly($_GET['a7']); // libri  0-2 4

$pgFirst = $vali->numberOnly($_GET['pgFirst']); // libri  0-2

$sumTREK = round(($a4+($a5*2)+$a6+($a7*2))*100 / 16);

$date = date('d.m.Y')." --> Ingresso Diretto Senza Sondaggio";

$res = mysql_query("UPDATE pg_users SET pgFirst=$pgFirst, pgNote = '$date', pgPointTREK=$sumTREK, pgPointPBC=$sumGDR WHERE pgID = ".$_SESSION['pgID']);

$stringST = "Benvenuto. Io sono il Guardiamarina Williams e sono attendente presso l\'ufficio centrale di reclutamento. Se sta leggendo o ascoltando queste parole significa che ha fatto richiesta di imbarco su un vascello della Flotta Stellare e che in questo momento è in viaggio o è già a bordo e che le è stato consegnato questo file sotto la dichiarazione di aver compiuto l\'età minima per l\'arruolamento (19 anni per gli umani) alla data attuale, anno 2382. 

Lasci che le presenti in linea di massima la situazione, in modo che possa orientarsi fin da subito. Innanzitutto potrebbe aver fatto richiesta di trasferimento a bordo di una nave della flotta perché l\'istituto per cui lavora come cittadino federale ha proposto lei per un progetto, in tal caso è inserito in quelle che noi comunemente chiamiamo \"attività civili a bordo\". Se la carriera militare non è il motivo del suo trasferimento, le suggeriamo di farlo presente al colloquio di imbarco perché il personale di arruolamento di bordo non sempre è abilitato alla lettura della sua scheda personale coperta dal rigoroso SECLAR 5 e quindi sapere che non è li presente come nuovo marinaio. Se diversamente ha scelto di essere mandato a bordo perché la sua carriera è nella Flotta Stellare allora la procedura che serguirà sarà quella di avere un colloquio di imbarco con un incaricato di turno ed una visita medica, non necessariamente in quest\'ordine, e successivamente un colloquio con il Consigliere di Bordo.

Questi colloqui serviranno per approfondire le dinamiche di inserimento nella Flotta e la sua idoneità fisica all\'imbarco. Se soffre di allergie, intolleranze o se ha subito interventi chirurgici o contratto e superato malattie infettive la visita medica è il momento giusto per farlo presente. Una volta a bordo le verrà assegnato un incarico o una serie di incarichi al fine di farle conoscere tutti i sistemi di bordo, di prepararla essenzialmente al funzionamento della nave ed alla vita di bordo. Questo periodo la porterà a classificarsi successivamente come Marinaio Scelto, normalmente già assegnato ad una sezione delle cinque che a bordo gestiscono specifiche competenze. Queste sezione, come le sarà già noto, sono: COMANDO E NAVIGAZIONE, TATTICA E SICUREZZA INGEGNERIA SCIENTIFICA e MEDICA.
		
COMANDO E NAVIGAZIONE: Competenze di coordinamento, guida e assistenza nell\'area \"Comando\" attraverso l\'omonimo dipartimento, il dipartimento legale, quello diplomatico, quello per le comunicazioni e via dicendo OPPURE con competenze collegate alla navigazione ed il timone del vascello, delle navette e attività correlate.

TATTICA E SICUREZZA: Sezione con competenze collegate ai sistemi di difesa della nave e la sicurezza dell\'equipaggio. Si occupa dell\'armamento, dell\'analisi tattica e delle scorte in opeazioni di sbarco. La presenza di un membro della sezione è prerogativa di qualsiasi operazione ad alto rischio.

INGEGNERIA: Questa sezione si occupa dei sistemi di bordo e più generalmente di tutte le operazioni elettroniche e meccaniche, dalla progettazione alla manutenzione ordinaria.

SCIENTIFICA: Organizzata in numerosi laboratori, la Sezione Scientifica si occupa della ricerca a bordo dei vascelli. Sono normalmente responsabili dei sistemi sensori a lungo e corto raggio della nave per i quali si interfacciano regolarmente con Ingegneria e supportano le altre sezioni nelle analisi sul campo e in laboratorio, siano esse di tipo biologico, astronomico o di qualsiais altre branca della scienza moderna.

MEDICA: Suddivisa in vari dipartimenti, la sezione medica si occupa dell\'assistenza e cura del personale di bordo, sia fisicamente che mentalmente, attraverso il Dipartimento Consiglieri. Fondamentale a bordo di un vascello la sezione medica è composta da personale laureato o diplomato in specifici campi per consentire all\'equipaggio di trovare in qualsiasi momento assistenza per qualsiasi situazione di ordine medico.
		
Durante la sua carriera come membro della Flotta Stellare, ma anche come personale civile inquadrato nell\'organigramma, le saranno poste di fronte numerose occasioni per distinguersi. Il personale a comando del vascello valuterà caso per caso il suo operato e prenderà in considerazione ogni possibile avanzamento di carriera. Ogni ulteriore informazione le potrà essere data dal personale di bordo. 
		
Nota OFF: Per iniziare la tua avventura chiudi questa finestra e apri i SUSSURRI con il tasto qui a destra!";

mysql_query("INSERT INTO fed_pad (paddFrom, paddTo, paddTitle, paddText, paddTime, paddRead) VALUES (518,".$_SESSION['pgID'].", 'Benvenuto!', '$stringST',".time().",0)");

echo json_encode(array('OK'));

?>