function fullScreen()
{
	var larg_schermo = screen.availWidth - 10;
	var altez_schermo = screen.availHeight - 30;
	window.open("login.php", "fed_main", "width=" + larg_schermo + ",height=" + altez_schermo + ",top=0,left=0,location=no,menubar=n,resizable=yes,scrollbar=yes");
}

function setViewer(vare)
{
				if(jQuery('#'+vare).length)
				jQuery('.sliding').fadeOut(120,function(){jQuery('#'+vare).fadeIn(120);});
				else window.location.href = 'index.php?mod='+vare;
}

function checkIscri()
{
	if (String(jQuery('#pgEmail').prop('value')).search(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i) == -1)
	{	
jQuery('#error').prop('value','Inserisci un indirizzo email corretto per continuare.');
jQuery('#error').fadeIn(400); setTimeout(function(){jQuery('#error').fadeOut(20);},2000);
return false;
	}
	
	if(jQuery('#pgName').prop('value') == '')
	{	
jQuery('#error').prop('value','Devi inserire uno username per continuare!');
jQuery('#error').fadeIn(400); setTimeout(function(){jQuery('#error').fadeOut(20);},2000);
return false;
	}
	return true;
}

function setNamae(val)
{
	jQuery('#schedaName').html(val);
	jQuery('#imaUser').html(val);
	jQuery('#autName').html(val+' '+jQuery('#pgAuth').prop('value'));
	
}

function foc1(){if(!($.browser.msie)) jQuery('#rg1').fadeOut(50,function(){jQuery('#rg2').fadeIn(100);});}
function foc2(ida){if(ida.value == "") jQuery('#rg2').fadeOut(50,function(){jQuery('#rg1').fadeIn(100);});}


function setSpecie(val)
{
	jQuery('#specieName').html(val);
	var sex = (jQuery('#pgSesso').prop('value') == 'M') ? 'm' : 'f';
	jQuery('#imaSpecie').prop('src','TEMPLATES/img/specie/'+val+'_'+sex+'.png');
}

function setSesso(val)
{
	var valer = (val == 'M') ? 'Maschio' : 'Femmina';
	jQuery('#sessoName').html(valer);
	
	var sex = (val == 'M') ? 'm' : 'f';
	jQuery('#imaSpecie').prop('src','TEMPLATES/img/specie/'+jQuery('#pgSpecie').prop('value')+'_'+sex+'.png');
}