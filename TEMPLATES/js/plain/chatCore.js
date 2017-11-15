		var are;
		var timerLLL;
		// nota Moreno: utilizzo del selettore JQ 'Jquery per evitare conflitti con PHPTAL XML PARSER
		jQuery(function()
		{
			are = setInterval("ccc();",5000);
			setInterval(function(){ $('.lamp').each( function(){ $(this).css('visibility' , $(this).css('visibility') === 'hidden' ? '' : 'hidden') } ); }, 350);

			jQuery("#federation_chatDiv").scrollTop(jQuery("#federation_chatDiv")[0].scrollHeight);	
		});
		
		$(window).resize(function() {
			resizeBar();
		});
		
		function resizeBar(){jQuery("#chatMainBar").css('width',(jQuery('#chatBar').width()-340)+'px');}
		// nota Moreno. Funzione SETTER dei parametri di configurazione della ghiera esterna.
		function ccc()
		{ // JQUERY YOUR SISTER! 
			jQuery.ajax(
			{
			url: jQuery('#getterURL').prop('value')+'?ts='+new Date().getMilliseconds(),
			data:{ambient: jQuery('#ambientID').prop('value'), lastID: jQuery('#lastID').prop('value'), getAudio: jQuery('#getAudio').prop('value')},
			success: setterCA, 
			type: 'POST',
			dataType : 'json',
			timeout:4500
			}); 
		}
		
		function deleteLast(ida)
		{
			 jQuery('#federation_chatDiv p .chatUser:contains("'+ida+'"):last').parent().fadeOut(300);
		}
		
		function selectOccur(tex)
		{
			jQuery('#federation_chatDiv').highlight(tex);
			if (typeof timer !== 'undefined'){
				clearTimeout(timer);
				timer = null;
			}
		}
		$.fn.reverse = function() {return $(this.get().reverse());}
		
		function showTurni()
		{
			var index; 
			var stringer = ''; 
			var indexer = []; 
			var turnOpener = '';
			var turnskipping = 0;
			var lastPos = 0;
		
			 jQuery('#federation_chatDiv p .chatUser, #federation_chatDiv p .actionUser').reverse().each(function(e){
				
				//alert(jQuery(this).parent().html().substr(0,5));
				
				if(indexer.indexOf(jQuery(this).text()) == -1 && turnskipping < 2)
				{
					if(indexer.length == 0){ turnOpener = jQuery(this).text();}
					indexer.push(jQuery(this).text()); 
				}
				else if(jQuery(this).text() == turnOpener){turnskipping++;}
				
			 });
			
			indexer.reverse();
			currentUsername = jQuery('#pgUser').val();
			
			for (index = 0; index < indexer.length; index++) {
			var classer = '';
			if(indexer[index] == currentUsername) classer = ' style="color:#3188F3;" '; 
			
			if(index == 0 && indexer[index] == currentUsername)
			{			
				
				stringer+= '<p class="turnElement myTurnElement lamp" '+classer+'>'+indexer[index]+'</p>'; 
				if(!parseInt(jQuery('#turnIndicatorNotified').val())){
				timerLLL = setInterval(function(){var title = document.title;document.title = (title == "STAR TREK FEDERATION" ? "TUO TURNO - STAR TREK FEDERATION" : "STAR TREK FEDERATION");}, 1000);
				jQuery('#turnIndicatorNotified').prop('value',1)}
			}
			else
			{
				stringer+= '<p class="turnElement" '+classer+'>'+indexer[index]+'</p>';  
			}
			
		} 
		
		//clearInterval(timerLLL);
		jQuery('#reminderBrevs').html(stringer);
		}
		
		/* function getAbilities(vargani)		{				if(jQuery('#whoAbilities').val() != vargani)				{				timerAbil = setTimeout(function(){				jQuery.post('ajax_getAbilities.php', {term: vargani}, function(data){				var stringer = '';				for (var abil in data)				{				//Luca: se hai bisogno di una mano per il codice, non esitare a chiedere. Ti rispondo volentieri! M.						 stringer+= "<img class=\"littleBrevImage\"  title=\""+data[abil]['descript']+"\" src=\"TEMPLATES/img/ruolini/brevetti/"+data[abil]['image']+"\" /> ";				}				jQuery('#reminderBrevs').html(stringer);				jQuery('#whoAbilities').val(vargani);				}, 'json');				}, 500)				}		} */

		function deselectOccur(tex)
		{
			 timer = setTimeout(function(){
				jQuery('#federation_chatDiv').unhighlight();
			}, 40);
		}
		
		function setterCA(data)
		{
			//alert(data['NP']+' BANANA');
			
			// questo non e' male http://www.youtube.com/watch?v=Qt74jX2C1m0
		//	console.debug('PADD INCUMING: '+data['NP']+'\n'); <link id="alerter" tal:attributes="href string:TEMPLATES/css/${alertCSS}.css" rel="stylesheet" type="text/css" />
			
			selfUpdater(data);
			
			if(data['NP'] == 1)
			{
				if(jQuery('#federation_montUL').prop('class')=='paddOFF')
				{
				if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
				jQuery('#federation_montUL').prop('class','paddON');
				jQuery('#federation_montUL').prop('title','Nuovi messaggi');
				}
			}
			else
				jQuery('#federation_montUL').prop('class','paddOFF');
			
			if(data['NOTIFY'])
			{
				jQuery('#alertTitle').html(data['NOTIFY']['TITLE']);
				jQuery('#alertMessage').html(data['NOTIFY']['TEXT']);
				jQuery('#messageAlert img.imager').prop('src',data['NOTIFY']['IMG']);
				
				jQuery('#messageAlert').fadeIn(600,function(){
				jQuery.post('ajax_delete_specific.php?A=b');
				if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
				setTimeout(function(){
				jQuery('#messageAlert').fadeOut(600);
				},10000);
				});
			}
			
			if(data['SU']){
				if(jQuery('#federation_rightBottomSussurro').prop('class')=='sussOFF')
				{
					jQuery('#federation_rightBottomSussurro').prop('class','sussON');
					if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
				}
			} 
			else jQuery('#federation_rightBottomSussurro').prop('class','sussOFF'); 
				
		if(('TEMPLATES/css/'+data['AL']+'.css') != jQuery('#alerter').prop('href')) jQuery('#alerter').prop('href','TEMPLATES/css/'+data['AL']+'.css');
		
		if(data['LIGHT'])
		jQuery('#reminderLight').prop('src','TEMPLATES/img/interface/mainInterface/l'+data['LIGHT']+'.png');
		
		if(data['COLOR']){
		jQuery('#lightBar').css('background-color',data['COLOR']);
		jQuery('#lightBar').css('box-shadow','0 0 5px '+data['COLOR']);
		}
		
		if(data['TEMP'])
		{jQuery('#reminderTemp').text(data['TEMP']+'°C');}
		}
		
		function sendChat()
		{
		if(jQuery('#chatInput').prop('value')!='')
		{
			var text = jQuery('#chatInput').prop('value');
			jQuery.ajax(
			{
			url: jQuery('#listenerURL').prop('value'),
			data: {amb: jQuery('#locID').prop('value'), chatLine: text, userSpecific: jQuery('#chatUser').val(), chatTag: jQuery('#chatTag').prop('value')},
			success: noti,
			async:false,
			type: 'POST'
			});
		}
		}
		
		function noti(e)
		{
		jQuery('#chatInput').prop('value','');
		jQuery('#counter').html('0');
		jQuery('#counter').css('color','#333');
		jQuery('#turnIndicatorNotified').prop('value',0);
		document.title = "STAR TREK: FEDERATION";
		clearInterval(timerLLL);
		
		
		jQuery.ajax(
			{
			url: jQuery('#getterURL').prop('value')+'?ts='+new Date().getMilliseconds(),
			data:{ambient: jQuery('#ambientID').prop('value'), lastID: jQuery('#lastID').prop('value')},
			success: selfUpdater, 
			type: 'POST',
			dataType : 'json',
			}); 
		
		}
		
		function selfUpdater(data)
		{
			if(data['CH'] != '') 
			{
					
			if(jQuery('#lastID').prop('value') != data['LCH'])
			{
			if(jQuery('#initialID').val() == 0){
				jQuery('#initialID').val(data['LCH']);
				jQuery('#loggerbtn').show();
			}
			jQuery('#federation_chatDiv').append(data['CH']);
			jQuery('#lastID').prop('value',data['LCH']);
			jQuery("#federation_chatDiv").scrollTop(jQuery("#federation_chatDiv")[0].scrollHeight);
			}
			}
			
			if(data['LIGHT'])
			jQuery('#reminderLight').prop('src','TEMPLATES/img/interface/mainInterface/l'+data['LIGHT']+'.png');
			
			if(data['TEMP'])
			{jQuery('#reminderTemp').text(data['TEMP']+'°C');}
			
			if(data['COLOR']){
				jQuery('#lightBar').css('background-color',data['COLOR']);
				jQuery('#lightBar').css('box-shadow','0 0 5px '+data['COLOR']);
			}
			showTurni();
		}
		
		function sX(e)
		{			
			var pitapa = (window.event) ? e.keyCode : e.which;
			if(pitapa == 13)
			{
				sendChat();
				//alert('goo');
				return false;
			}
			
			var color;
			
			var lent = jQuery('#chatInput').val().length;
			
			if(lent > 800) color = 0;
			else if(lent > 750) color = 24;
			else if(lent > 700) color = 54;
			else 
			{
			if(lent <= 50) color = (lent*16/50)
			if(lent <= 100) color = (lent*28/100)
			if(lent <= 250) color = (lent*54/250)
			if(lent <= 550) color = (lent*125/550)
			else color = 125;
			}
			
			jQuery('#counter').html(lent);
			jQuery('#counter').css('color','hsl('+color+',93%,50%)');
			
		}
		
		function dlindlon(ambient){jQuery.post('ajax_sendChatLine.php', {amb: ambient, chatLine: '*mst::sounder**'}, noti);}
				
		function doRedirectToMona(ida) {
			
			jQuery.post('ajax_delete_specific.php?A=a');
			setTimeout(function(){location.href = "chat.php?amb="+ida;},1000);
		}
		
		function toggleBiobed()
		{
			if(jQuery('#biobedMode').val() == 0){
			jQuery('#biobedMode').val('1');
			jQuery('#biobedControlImage').prop('src','TEMPLATES/img/interface/mainInterface/icon_med2.png');
			jQuery.post('ajax_sendChatEvents.php', {amb: jQuery('#locID').prop('value'),chatLine: '', mode:1}, noti);
			}
			else{
			jQuery('#biobedMode').val('0');
			jQuery('#biobedControlImage').prop('src','TEMPLATES/img/interface/mainInterface/icon_med.png');
			jQuery.post('ajax_sendChatEvents.php', {amb: jQuery('#locID').prop('value'), chatLine: '', mode:2}, noti);
			}
			
		}
		
		function doRedirectTotal(ida) {
			
			jQuery.post('ajax_delete_specific.php?A=a');
			setTimeout(function(){location.href =ida;},1000);
		}
		
		function getLog()
		{
			var id = jQuery('#initialID').val();
			var place = jQuery('#ambientID').val();
			
			var win = window.open('getLog.php?amb='+place+'&lastID='+id,'_blank');
			win.focus();
		}
		
		function playSound(ida)
		{
			jQuery('#au1').prop('src','http://miki.startrekfederation.it/audioBase/'+ida+'.ogg');
			jQuery('#au2').prop('src','http://miki.startrekfederation.it/audioBase/'+ida+'.mp3');
			if (document.getElementById('mainAu')) document.getElementById('mainAu').pause();
			if (document.getElementById('mainAu')) document.getElementById('mainAu').load();
			if (document.getElementById('mainAu')) document.getElementById('mainAu').play();
		}