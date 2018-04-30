
	function swish(e)
	{
	var keynum;
	var keychar;
	var numcheck;

if(window.event) // IE8 e precedenti (farabrutti!)
	{
	keynum = e.keyCode;
	}
else if(e.which) // IE9/Firefox/Chrome/Opera/Safari
	{
	keynum = e.which;
	}
	
	switch(keynum)
	{  
		case 49: jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv1").fadeIn('fast'); break;
		case 50: jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv2").fadeIn('fast'); break;
		case 51: jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv3").fadeIn('fast'); break;
	}
	}
	
	function tomap(vare)
	{
		 jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv"+vare).fadeIn('fast');
	}
	
	function toggleAudio()
	{
		if(jQuery('#audioControl').attr('src') == 'TEMPLATES/img/interface/mainInterface/icon_audio.png') 
			{
				jQuery('#audioControl').attr('src','TEMPLATES/img/interface/mainInterface/icon_audio_off.png');
				jQuery.post('ajax_setAudioOption.php', {prest: 0});
				
				if (document.getElementById('loopAu')) document.getElementById('loopAu').pause();
			}
		else {
				jQuery('#audioControl').attr('src','TEMPLATES/img/interface/mainInterface/icon_audio.png');
				jQuery.post('ajax_setAudioOption.php', {prest: 1});
				if (document.getElementById('loopAu')) document.getElementById('loopAu').play();
			}
	}
	
	function postOpenerSpecial(ida){
	if(ida == 'allo') window.open ('coLocation.php?get=quarters','fed_main', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0');
	else if(ida == 'holo') window.open ('coLocation.php?get=holodeck','fed_main', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0');
	}
	function postOpener(ida){window.open ('chat.php?amb='+ida,'fed_main', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0');}
	function cdbOpen(){window.open ('cdb.php','cdb', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1000,height=670');}
	function cdbOpenToTopic(ida){window.open ('cdb.php?topic='+ida,'cdb', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1000,height=670');}
	function dbOpen(){window.open ('db.php','db', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1153,height=730');}
	function dbOpenToTopic(ida){window.open ('db.php?element='+ida,'db', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1153,height=730');}
	function dbOpenToTopicLit(ida){window.open ('db.php?litref='+ida,'db', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1153,height=730');}
	function locOpen(){window.open ('localize.php','localize', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=505,height=605');}
	function paddOpen(){window.open ('padd.php','padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width=655,height=403');}
	function statusOpen(){window.open ('padd.php?s=sh','padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width=655,height=403');}
	function commOpen(){window.open ('comm.php','comm', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=650,height=160');}
	function schedaOpen(){window.open ('scheda.php','scheda', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=653,height=605');}
	function masterShadow(){window.open ('multitool.php','shadow', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=860,height=630');}
	function schedaPOpen(ida){window.open ('scheda.php?pgID='+ida,'schedaP', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=653,height=605');}
	function chartOpen(){window.open ('chart.php','chart', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1160,height=630');}
	function whisperOpen(){window.open ('whisper.php','whisper', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=854,height=618');}
	function repliOpen(toIda){window.open ('replicator.php?loc='+toIda,'replicator', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,height=615,width=660');}
	function repliOpenP(toFood){window.open ('replicator.php?loc='+jQuery('#locID').val()+'&foodID='+toFood,'replicator', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,height=615,width=660');}
	function doLogout(){
	jQuery.post('login.php?action=logout', {}, function(){
	window.close();
	});
	}
	
	