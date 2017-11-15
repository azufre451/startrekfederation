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


		function notifyDesktop(txt,icon,func,title){
			
			 jQuery("#easyNotify").easyNotify( {
	    		title: title,
	    		options: {
	      			body: txt,
	      			icon: icon,
	      			lang: 'it-IT',
	      			onClick: func
	    		}
	  		}); 
	  	}

		
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
			var nonConsiderer = []; 
			var turnOpener = '';
			var turnskipping = 0;
			var lastPos = 0;
		 
			 // jQuery('#federation_chatDiv p.directiveRemove').reverse().each(function(e){
				// if(nonConsiderer.indexOf(jQuery(this).text()) == -1)
					// nonConsiderer.push(jQuery(this).text());
			 // });
			 
			 jQuery('#federation_chatDiv p .chatUser, #federation_chatDiv p .actionUser, #federation_chatDiv p.directiveRemove').reverse().each(function(e){
				
				if(nonConsiderer.indexOf(jQuery(this).text()) == -1){ 
				
				if (jQuery(this).attr("class") == 'directiveRemove'){nonConsiderer.push(jQuery(this).text());}
				else
				{
					if(indexer.indexOf(jQuery(this).text()) == -1 && turnskipping < 2)
					{
						if(indexer.length == 0){ turnOpener = jQuery(this).text();}
						indexer.push(jQuery(this).text()); 
					}
					else if(jQuery(this).text() == turnOpener){turnskipping++;}
				}
				}
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

				notifyDesktop('Tocca a te azionare','TEMPLATES/img/logo_fed_fb.jpg',paddOpen,'Notifica');  
				
				timerLLL = setInterval(function(){var title = document.title;document.title = (title == "STAR TREK FEDERATION" ? "TUO TURNO - STAR TREK FEDERATION" : "STAR TREK FEDERATION");}, 1000);
				jQuery('#turnIndicatorNotified').prop('value',1)

				}

			}
			else
			{
				stringer+= '<p class="turnElement" '+classer+'>'+indexer[index]+'</p>';  
			}
			
		} 
		
		//clearInterval(timerLLL);
		if(indexer.indexOf(currentUsername) != -1) stringer+="<hr/><p style=\"margin:0px;\"><a href=\"javascript:void(0);\" class=\"interfaceLinkRed\" style=\"font-size:11px; text-align:lefft;\" onclick=\"javascript:removeTurner('')\"> [ ESCI ] </a> <img style=\"vertical-align:middle;\" src=\"TEMPLATES/img/interface/personnelInterface/info.png\" title=\"Uscita dalla turnazione. Premendo questo tasto segnali a tutti i giocatori l'uscita (oppure l'inattività, del tuo PG). Puoi farlo anche scrivendo la parola \"exit\" in un'azione di chat (deve contenere solo exit).\" /></p>";
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
					notifyDesktop(data['NPtitle'],data['NPavatar'],paddOpen,'Nuovo DPADD');

					if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();	
					jQuery('#federation_montUL').prop('class','paddON');
					jQuery('#federation_montUL').prop('title','Nuovi messaggi');
				}
			}
			else
				jQuery('#federation_montUL').prop('class','paddOFF');
			
			if(data['NOTIFY'])
			{
				notifyDesktop(data['NOTIFY']['TEXT'],data['NOTIFY']['IMG'],function(){},data['NOTIFY']['TITLE']);

				
				jQuery.post('ajax_delete_specific.php?A=b');

				if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();

			}
			
			if(data['SU']){
				if(jQuery('#federation_rightBottomSussurro').prop('class')=='sussOFF')
				{
					jQuery('#federation_rightBottomSussurro').prop('class','sussON');
					notifyDesktop('Hai un nuovo sussurro','TEMPLATES/img/logo_fed_fb.jpg',whisperOpen,'Notifica');
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

			if("DICER" in data && data['DICER'].length > 0){
				

				data['DICER'].forEach(function(v,k){
					

					jQuery('#adler').append('<tr class="diceEvent r_'+v['recID']+'"><td>'+v['pgUser']+'</td><td><img src="TEMPLATES/img/interface/personnelInterface/abilita/'+v['abImage']+'" style="width:50px;" /></td><td><span class="valDice">'+v['outcome']+'</span>  <input class="abiDice" type="hidden" value="'+v['abID']+'" /> <input class="recID" type="hidden" value="'+v['recID']+'" /> <input class="pgID" type="hidden" value="'+v['pgID']+'" />  </td>  <td>'+v['threshold']+' + <select class="abMod" onchange="recompute(this);"><option value="-6">-6</option> <option value="-3">-3</option> <option value="0" selected="selected">0</option> <option value="3">+3</option> <option value="6">+6</option> </select></td> <td class="abiOutcome">'+v['outcomeW']+'</td> </tr>');


				});

				//$diceOutcomes[] = array('pgID'=>$chatLi['sender'],'pgUser' => PG::getSomething($chatLi['sender'],'username'),'outcome' => $chatLi['dicerOutcome'], 'abID' => $chatLi['dicerAbil'], 'abName' => $abi['abName'], 'abImage' => $abi['abImage'] );
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
			
			if(lent > 2000) color = 0;
			else if(lent > 1500) color = 24;
			else if(lent > 1300) color = 54;
			else 
			{
			if(lent <= 300) color = (lent*16/300)
			if(lent <= 400) color = (lent*28/400)
			if(lent <= 650) color = (lent*54/650)
			if(lent <= 850) color = (lent*125/850)
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
			
			var win = window.open('getLog.php?amb='+place+'&lastID='+id); 
		}
		
		function playSound(ida,mode)
		{
			if (mode != 'extern')
			{
				jQuery('#au1').prop('src','http://miki.startrekfederation.it/audioBase/'+ida+'.ogg');
				jQuery('#au2').prop('src','http://miki.startrekfederation.it/audioBase/'+ida+'.mp3');
			}
			else jQuery('#au1').prop('src',ida);
			
			
			if (document.getElementById('mainAu')) document.getElementById('mainAu').pause();
			if (document.getElementById('mainAu')) document.getElementById('mainAu').load();
			if (document.getElementById('mainAu')) document.getElementById('mainAu').play();
		}
		
		function removeTurner(ida)
		{
			if(ida == '') ida = jQuery('#pgID').val();
			jQuery.post('ajax_sendChatEvents.php', {amb: jQuery('#locID').prop('value'),chatLine: ida, mode:90}, noti);
		}

		function initDice(){
		
			jQuery('#dicePanel').fadeIn(100);

		}

		function rollDice(){jQuery.post('ajax_manipulateAbilities.php?action=roll', {amb: jQuery('#ambientID').prop('value'), abID: jQuery('#selectedDiceSkill').val()}, function(e){jQuery('#dicePanel').fadeOut(100); 

			jQuery.ajax(
			{
			url: jQuery('#getterURL').prop('value')+'?ts='+new Date().getMilliseconds(),
			data:{ambient: jQuery('#ambientID').prop('value'), lastID: jQuery('#lastID').prop('value')},
			success: selfUpdater, 
			type: 'POST',
			dataType : 'json',
			}); 
		});}

		function diceChanger(){

			at=jQuery('#selectedDiceSkill').val();

			jQuery('#aSK0,#aSK1,#aAB1').hide();

			jQuery.ajax(
			{
			url: 'ajax_manipulateAbilities.php?action=getAbil',
			data:{abID: at},
			type: 'POST',
			dataType : 'json',
			timeout:4500,
			success: function(e){

				prebox='<p><span class="col_FC">F. Critico</span>&nbsp;|&nbsp;<span class="col_F">Fallimento</span>&nbsp;|&nbsp;<span class="col_S">Successo</span>&nbsp;|&nbsp;<span class="col_SC">S. Critico</span></p>';
				jQuery('#strDicer').attr('title',e['STAT']['string']+prebox);


				for (i = 1; i <= 20; i++){
					jQuery('#dicer_'+i).removeClass().addClass('dicer_'+e['STAT']['ara'][String(i)]);
				}

				jQuery('#exDicer').fadeIn(100);

				jQuery('#aAB1').removeClass().addClass(e['AB']['abClass'])

				jQuery('#aAB1 .abImage').attr('src','TEMPLATES/img/interface/personnelInterface/abilita/'+e['AB']['abImage']);
				if (e['AB']['value']  == null){
					jQuery('#aAB1 .abText').html('--');
					jQuery('#aAB1 .abImage').addClass('grayScale');
				}
				else{
					jQuery('#aAB1 .abText').html(e['AB']['value']);
					jQuery('#aAB1 .abImage').removeClass('grayScale');
				}

				jQuery('#aAB1 .abGauge').removeClass(
							function (index, className) {
    							return (className.match (/p[0-9]{1,3}/g) || []).join(' ');
							}
						).addClass('p'+e['AB']['levelperc']).attr('title',"<span style='color:#5d85a6; font-weight:bold;'>"+e['AB']['abName']+"</span> - Liv. "+(e['AB']['value'] != null ? e['AB']['value'] : '-')+"<hr/>"+e['AB']['leveldesc']);


				jQuery('#aAB1').fadeIn(100);

				jQuery('#aSK0,#aSK1').hide();

				for (key in e['DEP']){
					ab=e['DEP'][key][0];
					jQuery('#aSK'+key+' .abImage').attr('src','TEMPLATES/img/interface/personnelInterface/abilita/'+ab['abImage']);
					jQuery('#aSK'+key+' .abText').html(ab['value']);
					jQuery('#aSK'+key+' .abGauge').removeClass(
							function (index, className) {
    							return (className.match (/p[0-9]{1,3}/g) || []).join(' ');
							}
						).addClass('p'+ab['levelperc']).attr('title',"<span style='color:#5d85a6; font-weight:bold;'>"+ab['abName']+"</span> - Liv. "+(ab['value'] != null ? ab['value'] : '-')+"<hr/>"+ab['leveldesc']);


					jQuery('#aSK'+key).fadeIn(100);

				}
				

			}
			}); 

		}