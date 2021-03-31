# Star Trek: Federation #

![image](https://user-images.githubusercontent.com/25253438/113070814-bff3ac80-91c3-11eb-86bf-a04d0a2c375a.png)


This repo contains the code of www.stfederation.it.

## Requirements

STFederation runs on LAMP and needs some extra packages:

- [PHPTAL](https://github.com/phptal/PHPTAL)
- [Leaflet](https://github.com/Leaflet/Leaflet) (included as [leaflet.js](blob/master/TEMPLATES/js/leaflet.js))
- [PHP Parsedown](https://github.com/erusev/parsedown) and [PHP Parsedown Extra](https://github.com/erusev/parsedown-extra/) (included as [md](blob/master/includes/md/))
- [TextDiff](https://github.com/pear/Text_Diff) (included as [textdiff](blob/master/TEMPLATES/includes/includes/Text_Diff-1.2.2))
- Python3

## Current Release ##

🟩 **v. 1.8.8** - *Triumphal Zebrafish*

🟨 [dev](https://github.com/azufre451/startrekfederation/tree/parrot-dev) branch (**v. 1.9beta**) is cloned at [efesto.stfederation.it](https://efesto.stfederation.it).

## Contributions ##

Star Trek: Federation is available at [https://stfederation.it/](https://stfederation.it/) and is maintained by [Moreno Zolfo](https://github.com/azufre451). 
Additional contributors:
- [Giuseppe Buffa](https://github.com/Cole0283) 
- [Gianmaria Genetlic](https://github.com/jean-n92) 
- [Igor Sontacchi](https://twitter.com/igorsontacchi)

## Releases ##

(release notes in Italian)

- 30/04/2012 - **v. 0.0.3** - *Blue Dragon* - Prima Interfaccia consultabile.
- 02/05/2012 - **v. 0.0.4** - *Cheerful Elephant* - Gestione rudimentale del CDB. Interfaccia di gioco
- 04/05/2012 - **v. 0.0.5** - *Delightful Fox* - CDB, gestione posts, eliminazione. Montata interfaccia CDB. Gestione del diario PG. Gestione navi dinamiche.
- 07/05/2012 - **v. 0.0.5.5** - *Expert Goose* - Nuovi elementi CDB, gestione bbCode compatibile, Gestione multinave
- 07/05/2012 - **v. 0.0.5.7** - *Faithful Horse* - Predisposizione alle chat, supporto mostrine, reminder, supporto nomi chat, localizzatore. Mappatura tasti.
- 17/05/2012 - **v. 0.0.6** - *Girlish Ibex* - Scheda PG (beta), Ricerca, Assegnazioni, News, Simbologia di razza. Nuovo sistema BBCode basato su BlueScript.
- 25/05/2012 - **v. 0.0.7** - *Hungry Lama* - Scheda PG e relativi form di modifica, recupero, modifica di password, integrazione con sistema invio mail. Crittazione della password per progetto legalità . Bug Fixing. Gestione alloggi e assegnazioni.
- 01/06/2012 - **v. 0.0.8** - *Introverted Mufflon* - Pannello master e pannello admin. Gestione dello spostamento dei PG da una location all'altra. Nuovo localizzatore. Correzione di bugs vari. Fixing per compatibilità IE8 su WinXP. Supporto a brevetti, lauree. Meccanismo di controllo IP per prevenzione doppi.
- 04/06/2012 - **v. 0.0.9** - *Lusty Numbat* - Padd completo. Fixing bugs per IE < 8. Federation Tribune. Status nave (bozza). Implementazione auto-update con AJAX
- 11/06/2012 - **v. 0.1.1** - *Masculine Obstrich* - Status nave completo. Implementato meccanismo di movimento delle navi. Implementato meccanismo di attracco delle navette. Sistema di curvatura / raggiunta destinazione completato. Federation Tribune (per news) completo. Sistema appunti (padd) completato. Bug Fixing. Pulsante Resetta News in cdb.
- 12/06/2012 - **v. 0.1.2** - *Masculine Obstrich* - Pre-sistemi di sbarco/imbarco, nuovo gestore della localizzazione dei PG
- 12/06/2012 - **v. 0.1.3** - *Masculine Obstrich* - IE 8-9 Fixing. 2 Bug minori corretti
- 12/06/2012 - **v. 0.1.7** - *Nostalgic Parrot* - Spostamento delle navi, spostamento delle navette, spostamento pianeti e basi inibito. Testing sistemi di spostamento. Sbarco / Imbarco (da nave/navetta/entità verso l'esterno). Bozze per lo sbarco in hangar (attracco). Le navette possono attraccare.
- 17/06/2012 - **v. 0.1.9** - *Omniscient Quail* - Correzione di diversi bug. Database. Caricamento delle locations.
- 18/06/2012 - **v. 0.2.0** - *Promiscuous Rabbit* - Charts
- 18/06/2012 - **v. 0.2.2** - *Qualified Salmon* - Mappe pianeti, caricamento dati mappe, sistemi di spostamento sbarco imbarco sui pianeti e nelle chat. Veste grafica della chat.
- 24/06/2012 - **v. 0.2.4** - *Radical Tiger* - Chat, discorso diretto, azioni, masterate, masterate globali, immagini, immagini globali
- 24/06/2012 - **v. 0.2.5** (RELEASE 1.0) - *Superb Urraca* - Campanello, Comunicazioni subspaziali, Riscrittura sistema delle chat. Release
- 11/07/2012 - **v. 1.0.1** - *Thankful Viper* - Migliorie minori: masterate non anonime, alloggi bordati se occupati, tasto sussurri lampeggiante
- 6/09/2012 - **v. 1.0.2** - *Uber Zebra* - Bug fix, security fix. Schede mediche linkate alla scheda PG
- 01/11/2012 - **v. 1.0.3** - *Vibrant Ape* - Proposte frase master, Frasi interattive per alloggi, e segnale orario
- 15/11/2012 - **v. 1.1** - *Zazzy Baboon* - Computer di bordo migliorato: strumenti avanzati per l'inserimento mostrine, ruoli e incarichi. Log delle giocate. Migliorie minori, revisione sistema chat, implementazioni di sicurezza.
- 13/12/2012 - **v. 1.1.4** - *Angelic Dolphin* - Olomostrine per il ponte ologrammi, ruolino di servizio personalizzabile, tabellazione dei ranks.
- 24/12/2012 - **v. 1.1.4.1** - *Brave Emu* - Bugfix al sistema di logging, replicatori con liste di cibi, riscrittura sistema controllo doppi account
- 19/01/2013 - **v. 1.2** - *Crispy Falcon* - Tool teletrasporto, Log filtrabili, riscrittura di parte del sistema bacheche, studio di fattibilità sviluppo ruoli di moderazione, tool di moderazione, dispense in PDF(beta)
- 31/03/2013 - **v. 1.2.1** - *Dangerous Gorilla* - Sondaggi interattivi, inventario PG
- 13/05/2013 - **v. 1.2.6** - *Evanescent Human* - Sistema Federation Points e Achievements
- 20/06/2013 - **v. 1.2.8** - *Feudal Isopod* - Tool definitivi di moderazione, Prelievo LOG in HTML, audio di ambiente riscritto, database integrity fix. Tool Bioletto
- 01/07/2013 - **v. 1.2.6** - *Glossy Lice* - Masterate specifiche. Rimozione tool suggerimenti master.
- 18/07/2013 - **v. 1.3** - *Heuristic Mammoth* - Nuova versione del database documentazione
- 15/08/2013 - **v. 1.4** - *Implicit Nightingale* - Nuovi tool computer di bordo, riformulazione layout, ingrandimento interfaccia cdb. Ricerca migliorata. Pattern di ricerca avanzati. Inizio riscrittura del sistema di dpadd. Modifica del sistema dei brevetti, introduzione degli slot connessi con i FP
- 29/08/2013 - **v. 1.4.6** - *Lusty Otter* - Modifica delle schede PG: campi aggiuntivi per aspetto fisico, biografia, famiglia, carattere, varie, Aggiunta del Dipartimento come attributo PG, revisioni minori, bug fixing, security fix.
- 18/10/2013 - **v. 1.5** - *Metaphysical Platypus* - Modifica delle schede PG: estensione della scheda, sistema a brevetti per punteggi (abilità), modifica delle soglie e ricalcolo del sistema FP. Introduzione del tool turnazione (beta), modifiche di front-end grafico, modifiche del backend-admin. Revisioni minori e bugfix.
- 19/01/2014 - **v. 1.5.5** - *Nebulous Quetzal* - Revisione tecnica minore, bugfix, ottimizzazione pagina di login, estensione abilità, stato di servizio e ruolino in scheda PG, sistema Creative Commons per la documentazione di Database
- 17/03/2014 - **v. 1.6** - *Occasional Raven* - Replicatore interattivo, revisione completa delle charts, revisione minore dei brevetti, bugfix, miglioramento tecnico gestione dpadd
- 25/05/2017 - **v. 1.7** - *Prandial Seagull* - Sessioni, Sistema di attribuzione punti automatico, Sessioni e chat private, bugfix, Gestore di dadi, abilità e caratteristiche, Revisione grafica scheda PG, Eliminazione dei brevetti. Riapertura in Alpha Test.
- 17/10/2017 - **v. 1.7.5** - *Quiescent Tortoise* - Gestione incarichi multipli, gestione approvazioni BG da parte dello staff. Istituzione ruolo OFF di "Guida". Inserimento nuove qualifiche e medaglie. Gestione del SECLAR nelle bacheche. Revisione del Database. Bugfix vari ed assortiti.
- 31/08/2018 - **v. 1.8** - *Rampant Unicorn* - Lettura post in CDB tracciata per ogni utente, implementazione tool notifiche, modifica tool turnazione, implementazione seclar0 e bugfix CDB, implementazione oggettistica e dotazione trasportabile e visibile in giocata, implementazione abiti personalizzati, realizzazione e implementazione charts quadrante delta, modifiche all'algoritmo di attribuzione FP, bugfix generale, uscita dal beta-test, revisione documentazione / dispense.
- 23/02/2019 - **v. 1.8.5** - *Sophisticated Vicuña* - Strumenti di gestione dei background e backend Guide e Master; migliore gestione dei post in CDB e aggiunta tool di selezione del png (master). Passaggio ad architettura PHP 7.2 su hosting SSD. Modifiche tool organigrammi: aggiunti gruppi e squadre (caccia, intervento). Aggiunta la possibilità di inserire un range nelle date del tool ruolino di servizio. Aggiunta pop up per vedere i PG che condividevano la stessa assegnazione del proprio PG negli anni. Piccole modifiche al tool di gestione delle sessioni e dei dadi. Bugfix minori
- 13/02/2020 - **v. 1.8.6** - *Triumphal Zebrafish* - Riscrittura sistema BBCode per permettere link dinanici di Post in CDB ed elementi in DB con tag [POST] e [DB]. Seclar Dinamico in post CDB con tag [SECLAR=X]. Revisione messaggi padd su più tipologie (off,on,revisioni etc...). Tracciamento ultima modifica background. Multimedia in gioco (link Youtube, Vimeo, Audio). Migliorie SigmaSystem (tool hosting immagini e audio per master). Revisione modalità cancellazione account non attivi. Modifica soglie e algoritmo attribuzione punti esperienza. Bugfix minori
- 16/05/2020 - **v. 1.8.7** - (internal version) - Cleanup del codice. Procedure Multitool per il cambio/raggruppamento utente. Sistema CTS (custom CSS diviso per sezione). Deployment routine. Modifiche e pulizia al DB e Bugfix minori.
- 26/12/2020 - **v. 1.8.8** - (internal version) - Sistema MPI (multimedia panel in chat con possibilità di inviare video da youtube e vimeo). Aggiunta personalizzazione CSS delle master-screen. Sistema autorizzazioni per il download di "tutti i log". Fix bug 2395 del CDB. Sistema stats utenti (numero azioni, cibi replicati etc). Indicatori abilità aggiornati in schedaPG. Brevetti e medaglie rimovibili per GM. Implementazioni varie backend admin.
- 30/03/2021 - **v. 1.9** - *Ubiquitous Alpaca* - **Charts a tutto schermo** e aggiornamento mappe Quad Delta (v7). Tychonian Herald/Eagle aggiornabile da civili giornalisti. Stato-Servizio modificabile dal GM. Pannello scelta mostrine nel tool ruolino. **Nuova interfaccia D-Padd** e pannello BBCode interimento. **Nuovo comunicatore**. Popup mobili. Modifiche minori all'interfaccia di gioco. Pulizia DB, refactoring codice backend admin/master e bugfixes. Supporto PHP8 / JQuery 3.6 
