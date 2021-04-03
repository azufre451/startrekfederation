		// nota Moreno: utilizzo del selettore JQ 'Jquery per evitare conflitti con PHPTAL XML PARSER
		function lamp()
		{
			if(jQuery('.lamp').css('visibility') == 'visible') jQuery('.lamp').css('visibility', 'hidden');
			else jQuery('.lamp').css('visibility', 'visible');
		}
		
		jQuery(function()
		{
			setInterval("localizeRefreshAuto();",20000);
			setInterval("ccc();",7000);
			setInterval('lamp()',600);
			jQuery('#messageOfStatus').fadeIn(700);
			jQuery('.tooltip').tooltip();

			jQuery(".draggableSTFModal").draggable({containment:'#federation_interfaceContainer'});
			jQuery(".xButton").on('click',function(){
				jQuery(this).parent().fadeOut(100);
			});
			
			initializeSearchBar();
			jQuery(window).keyup(swish);
			
		});

		
		
		// nota Moreno. Funzione SETTER dei parametri di configurazione della ghiera esterna.
		
		function ccc()
		{ // JQUERY YOUR SISTER! 
			jQuery.ajax(
			{
			url: 'ajax_interfaceUpdater.php?ts='+new Date().getMilliseconds(),
			success: setterCA, 
			type: 'POST',
			dataType : 'json',
			timeout:3000
			}); 
		}

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
		
		function setterCA(data)
		{
			//alert(data['NP']+' BANANA');
			
			// questo non e' male http://www.youtube.com/watch?v=Qt74jX2C1m0
		//	console.debug('PADD INCUMING: '+data['NP']+'\n'); <link id="alerter" tal:attributes="href string:TEMPLATES/css/${alertCSS}.css" rel="stylesheet" type="text/css" />
			
			if(data['NP'] == 1)
			{
				

				if(jQuery('#federation_montUL').prop('class')=='paddOFF')
				{
					notifyDesktop(data['NPtitle'],data['NPavatar'],paddOpen,'Nuovo DPADD');
					if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
					jQuery('#federation_montUL').prop('class','paddON');
					jQuery('#federation_montUL').prop('title',data['NP']+' nuovi messaggi');
				}
			}
			else
				jQuery('#federation_montUL').prop('class','paddOFF');
			
	 
			if(data['NPR'] >= 1)
			{
			

				if(!jQuery('#btnStatus').hasClass('notify'))
				{
					//notifyDesktop(data['NPtitle'],data['NPavatar'],paddOpen,'Nuovo DPADD');

					if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
					jQuery('#btnStatus').addClass('notify');
					jQuery('#btnStatus').prop('title',data['NPR']+' nuove notifiche');
				}
			}
			else
				jQuery('#btnStatus').removeClass('notify');
			


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

					if (document.getElementById('audioNotify')) document.getElementById('audioNotify').play();
					notifyDesktop('Hai un nuovo sussurro','TEMPLATES/img/logo_fed_fb.jpg',whisperOpen,'Notifica');
				}
			} 
			else jQuery('#federation_rightBottomSussurro').prop('class','sussOFF'); 
				
		if(('TEMPLATES/css/'+data['AL']+'.css') != jQuery('#alerter').attr('href'))
			{
				jQuery('#alerter').prop('href','TEMPLATES/css/'+data['AL']+'.css');
			}
		}

		function initializeSearchBar(){
			jQuery( "#searchKey" ).autocomplete({
				source: "ajax_UserSearch.php?filter=PID",
				minLength: 3,
				/* MZ: Funzione che esegue il comando per l'elemento selezionato */
				select: function(event, ui){
					
					if( ui.item.mode == 'view')
						schedaPOpen(ui.item.data.PUD);
					else if ( ui.item.mode == 'Stato-Servizio')
						schedaPOpen(ui.item.data.PUD,'ssto');
					else if ( ui.item.mode == 'Master')
						schedaPOpen(ui.item.data.PUD,'master');
					else if ( ui.item.mode == 'Admin')
						schedaPOpen(ui.item.data.PUD,'admin');
					else if ( ui.item.mode == 'DPadd')
						paddOpenTo(ui.item.value);
					else if ( ui.item.mode == 'Luogo')
						window.location = "chat.php?amb="+ui.item.data.PUD;

					jQuery('#PGsearchPanel').toggle('slide',{direction:'up'},100);
					jQuery(this).val('');
					return false;
				},

				/* MZ: Funzione che crea ogni entry dell'autocomplete */
				create: function () {
		            jQuery(this).data('ui-autocomplete')._renderItem = function (ul, item) {
		            	addparticle = (item.mode == 'view') ? '' : ' <span class="mode '+item.mode+'">'+item.mode+'</span>';
						entryIMA = (item.mode == 'Luogo') ? 'TEMPLATES/img/logo/'+item.data.IMA : item.data.IMA;
		                return jQuery('<li><div><div style="background-image:url('+entryIMA+')"></div>'+item.data.value+addparticle+'</div></li>').appendTo(ul);
		            };
		        }
			});
		}