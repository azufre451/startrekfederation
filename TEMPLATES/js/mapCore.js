		// nota Moreno: utilizzo del selettore JQ 'Jquery per evitare conflitti con PHPTAL XML PARSER
		function lamp()
		{
			if(jQuery('.lamp').css('visibility') == 'visible') jQuery('.lamp').css('visibility', 'hidden');
			else jQuery('.lamp').css('visibility', 'visible');
		}
		
		jQuery(function()
		{
			setInterval("ccc();",7000);
			setInterval('lamp()',600);
			jQuery('#messageOfStatus').fadeIn(700);
		});
	
		
		// nota Moreno. Funzione SETTER dei parametri di configurazione della ghiera esterna.
		function ccc()
		{ // JQUERY YOUR SISTER! 
			jQuery.ajax(
			{
			url: 'aggio.php?ts='+new Date().getMilliseconds(),
			success: setterCA, 
			type: 'POST',
			dataType : 'json',
			timeout:1000
			}); 
		}
		
		function setterCA(data)
		{
			//alert(data['NP']+' BANANA');
			
			// questo non e' male http://www.youtube.com/watch?v=Qt74jX2C1m0
		//	console.debug('PADD INCUMING: '+data['NP']+'\n'); <link id="alerter" tal:attributes="href string:TEMPLATES/css/${alertCSS}.css" rel="stylesheet" type="text/css" />
			
			if(data['NP'] == 1)
			{
				if(jQuery('#federation_montUL').prop('class')=='paddOFF')
				{
				if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
				jQuery('#federation_montUL').prop('class','paddON');
				jQuery('#federation_montUL').prop('title',data['NP']+' nuovi messaggi');
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
		//else alert(('TEMPLATES/css/'+data['AL']+'.css')+' IS EQUAL TO '+jQuery('#alerter').prop('href'));
		}