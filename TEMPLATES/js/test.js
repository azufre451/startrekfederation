
// bbCode control by
// subBlue design
// www.subBlue.com

// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

// Helpline testos
b_help = "Grassetto: [B]testo[/B]";
i_help = "Corsivo: [I]testo[/I]";
u_help = "Sottolineato: [U]testo[/U]";
q_help = "Testo al centro: [CENTER]testo[/CENTER]";
c_help = "Testo a sinistra: [LEFT]codice[/LEFT]";
l_help = "Testo a destra: [RIGHT]testo[/RIGHT]";
o_help = "Testo giustificato: [JUS]testo[/JUS]";
p_help = "Inserisci un'immagine: [IMG]http://image_url[/IMG]";
w_help = "Inserisci URL: [LINK]http://url[/LINK]testo[FINELINK]";
a_help = "Chiudi tutti i bbCode tags aperti";
s_help = "Colore font: [COLOR=RED]testo[/COLOR]";
f_help = "Dimensione font: [SIZE=x]testo[/SIZE]";
h_help = "Inserisci una linea di divisione"

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[B]','[/B]','[I]','[/I]','[U]','[/U]','[CENTER]','[/CENTER]','[LEFT]','[/LEFT]','[RIGHT]','[/RIGHT]','[JUS]','[/JUS]','[IMG]','[/IMG]','[LINK]','[/LINK]testo-da-linkare[FINELINK]');
imageTag = false;

// Shows the help testos in the helpline window
function helpline(help) {
	document.form1.helpbox.value = eval(help + "_help");
}


// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}


function checkForm() {

	formErrors = false;

	if (document.form1.testo.value.length < 2) {
		formErrors = "Devi scrivere un messaggio per inserirlo";
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	} else {
		bbstyle(-1);
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}

function emoticon(text) {
	var txtarea = document.form1.testo;
	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function bbfontstyle(bbopen, bbclose) {
	var txtarea = document.form1.testo;

	/*if ((clientVer >= 4) && is_ie && is_win) {*/
	if(document.selection && document.selection.createRange()){
		theSelection = document.selection.createRange().text;
		if (!theSelection) {
			txtarea.value += bbopen + bbclose;
			txtarea.focus();
			return;
		}
	//	document.selection.createRange().text = bbopen + theSelection + bbclose;
	//	txtarea.focus();		
		txtarea.focus(txtarea.caretPos );  
		txtarea.caretPos = document.selection.createRange().duplicate();
		tSel = document.selection.createRange().text;
		text = bbopen + tSel + bbclose;
		txtarea.caretPos.text = text;
		txtarea.caretPos.moveStart( "character", text.length * -1 );
		txtarea.caretPos.select();	
		return;
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbopen, bbclose);
		return;
	}
	else
	{
		txtarea.value += bbopen + bbclose;
		txtarea.focus();
	}
	storeCaret(txtarea);
}


function bbstyle(bbnumber) {
	var txtarea = document.form1.testo;

	 txtarea.focus();
 donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			txtarea.value += bbtags[butnumber + 1];
			buttext = eval('document.form1.addbbcode' + butnumber + '.value');
			eval('document.form1.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}
	/*if ((clientVer >= 4) && is_ie && is_win) {*/
	if(document.selection && document.selection.createRange()){
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {
			// Add tags around selection
			//document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			//txtarea.focus();
			//theSelection = '';
			// Add tags around selection
			//document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			//txtarea.focus();
			//theSelection = '';
		txtarea.focus(txtarea.caretPos );  
		txtarea.caretPos = document.selection.createRange().duplicate();
		tSel = document.selection.createRange().text;
		text = bbtags[bbnumber]  + tSel + bbtags[bbnumber+1];
		txtarea.caretPos.text = text;
		txtarea.caretPos.moveStart( "character", text.length * -1 );
		txtarea.caretPos.select();	
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0) )
	{
		mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}

	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				txtarea.value += bbtags[butnumber + 1];
				buttext = eval('document.form1.addbbcode' + butnumber + '.value');
				eval('document.form1.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
				imageTag = false;
			}
			txtarea.focus();
			return;
	} else { // Open tags

		if (imageTag && (bbnumber != 14)) {		// Close image tag before adding another
			txtarea.value += bbtags[15];
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			document.form1.addbbcode14.value = "Img";	// Return button back to normal state
			imageTag = false;
		}

		// Open tag
		txtarea.value += bbtags[bbnumber];
		if ((bbnumber == 14) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		eval('document.form1.addbbcode'+bbnumber+'.value += "*"');
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}


// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var sTop=txtarea.scrollTop;
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	var tmptext = open + s2 + close;
	txtarea.value=s1 + tmptext + s3;
	var cPos = selStart+tmptext.length;
	txtarea.focus();
	txtarea.selectionStart=selStart;
	txtarea.selectionEnd=cPos;
	txtarea.scrollTop=sTop;
	return;
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}





function linea() 
 { 
  txt="[HR]"; 
 document.form1.testo.value+=txt; 
 } 
//-->

</script>

<script language="JavaScript" type="text/JavaScript">
<!--

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

//-->