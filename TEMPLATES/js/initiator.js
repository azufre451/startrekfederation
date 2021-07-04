var windowSizes = 
		{
			'scheda':
				{
					"Opera": {"w":'655',"h":'640'},
					"Firefox": {"w":'655',"h":'605'},
					"Safari": {"w":'655',"h":'605'},
					"IE": {"w":'655',"h":'605'},
					"Edge": {"w":'655',"h":'605'},
					"Chrome": {"w":'655',"h":'605'},
					"Default": {"w":'655',"h":'605'}
				},
			'db':
				{
					"Opera": {"w":'1153',"h":'730'},
					"Firefox": {"w":'1153',"h":'730'},
					"Safari": {"w":'1153',"h":'730'},
					"IE": {"w":'1153',"h":'730'},
					"Edge": {"w":'1153',"h":'730'},
					"Chrome": {"w":'1153',"h":'730'},
					"Default": {"w":'1153',"h":'730'}
				},
			'cdb':
				{
					"Opera": {"w":'1000',"h":'705'},
					"Firefox": {"w":'1000',"h":'670'},
					"Safari": {"w":'1000',"h":'670'},
					"IE": {"w":'1000',"h":'670'},
					"Edge": {"w":'1000',"h":'670'},
					"Chrome": {"w":'1000',"h":'670'},
					"Default": {"w":'1000',"h":'670'}
				},
			'replicator':
				{
					"Opera": {"w":'655',"h":'660'},
					"Firefox": {"w":'655',"h":'660'},
					"Safari": {"w":'655',"h":'660'},
					"IE": {"w":'655',"h":'660'},
					"Edge": {"w":'655',"h":'660'},
					"Chrome": {"w":'655',"h":'660'},
					"Default": {"w":'655',"h":'660'}
				},
			'multitool':
				{
					"Opera": {"w":'860',"h":'665'},
					"Firefox": {"w":'860',"h":'630'},
					"Safari": {"w":'860',"h":'630'},
					"IE": {"w":'860',"h":'630'},
					"Edge": {"w":'860',"h":'630'},
					"Chrome": {"w":'860',"h":'630'},
					"Default": {"w":'860',"h":'630'}
				},
			'whisper':
				{
					"Opera": {"w":'854',"h":'655'},
					"Firefox": {"w":'854',"h":'620'},
					"Safari": {"w":'854',"h":'620'},
					"IE": {"w":'854',"h":'620'},
					"Edge": {"w":'854',"h":'620'},
					"Chrome": {"w":'854',"h":'620'},
					"Default": {"w":'854',"h":'620'}
				},
			'padd':
				{
					"Opera": {"w":'655',"h":'638'},
					"Firefox": {"w":'655',"h":'603'},
					"Safari": {"w":'655',"h":'603'},
					"IE": {"w":'655',"h":'603'},
					"Edge": {"w":'655',"h":'603'},
					"Chrome": {"w":'655',"h":'603'},
					"Default": {"w":'655',"h":'603'}
				},
			'chart':
				{
					"Opera": {"w":'1165',"h":'670'},
					"Firefox": {"w":'1160',"h":'630'},
					"Safari": {"w":'1160',"h":'630'},
					"IE": {"w":'1160',"h":'630'},
					"Edge": {"w":'1160',"h":'630'},
					"Chrome": {"w":'1160',"h":'630'},
					"Default": {"w":'1160',"h":'630'}
				},
			'comm':
				{
					"Opera": {"w":'650',"h":'195'},
					"Firefox": {"w":'650',"h":'160'},
					"Safari": {"w":'650',"h":'160'},
					"IE": {"w":'650',"h":'160'},
					"Edge": {"w":'650',"h":'160'},
					"Chrome": {"w":'650',"h":'160'},
					"Default": {"w":'650',"h":'160'}
				}
		};


	/* Custom user-triggered resize handler (with +10w/+30h offset)*/
	function resizeOnDemand(ida) {
		oriSize = getSizeOf(ida);

		prevWidth = parseInt(oriSize['w']); 
		prevHeight = parseInt(oriSize['h']);

		curWidth =  parseInt(prevWidth * window.devicePixelRatio)
		curHeight = parseInt(prevHeight * window.devicePixelRatio)

		if (curWidth != prevWidth){
			window.resizeTo(curWidth+10,curHeight+30);
		}

		if(window.devicePixelRatio == 1){
			window.resizeTo(prevWidth+10,prevHeight+30);
		}
	}

	function swish(e)
	{

		var keynum;
		var keychar;
		var numcheck;

		keynum = e.key;
		switch(keynum)
		{  
			case '1': jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv1").fadeIn('fast'); break;
			case '2': jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv2").fadeIn('fast'); break;
			case '3': jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv3").fadeIn('fast'); break;
			case 'Escape': jQuery(".popup, .escape_popup, .draggableSTFModal").fadeOut('fast'); break;
			case 'Enter': openSearchBar(); break;
		}
	}

	function openSearchBar(){
		if (jQuery('#PGsearchPanel').length){
			jQuery('#searchKey').val('');
			jQuery('#PGsearchPanel').toggle('slide',{direction:'up'},100);
			jQuery('#searchKey').focus();
		}
	}
	
	function browserDetect(){
		// Opera 8.0+
		if ((!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0 )
			return 'Opera';

		// Firefox 1.0+
		if(typeof InstallTrigger !== 'undefined')
			return 'Firefox';

		// Safari 3.0+ "[object HTMLElementConstructor]" 
		if ( /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification)))
		return 'Safari';

		// Internet Explorer 6-11
		if( /*@cc_on!@*/false || !!document.documentMode)
			return 'IE';

		// Edge 20+
		if(!!window.StyleMedia)
			return 'Edge';

		// Chrome 1 - 71
		if (!!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime))
			return 'Chrome';
	}

	function tomap(vare)
	{
		 jQuery("div[id^='mapDiv']").fadeOut('fast'); jQuery("#mapDiv"+vare).fadeIn('fast');
	}
	
	function toggleMPI()
	{
		if( jQuery('#MPIbtn').hasClass('btnon'))
		{
		 jQuery('#MPIbtn').removeClass('btnon').addClass('btnactive');
		 jQuery('#lateralMPI').show('blind',{ direction: 'right' });
		 jQuery('#audioControl').attr('src','TEMPLATES/img/interface/mainInterface/icon_audio.png');
		}

		else if( jQuery('#MPIbtn').hasClass('btnnotify'))
		{
		 jQuery('#MPIbtn').removeClass('btnnotify').addClass('btnactive');
		 jQuery('#lateralMPI').show('blind',{ direction: 'right' });
		 jQuery('#audioControl').attr('src','TEMPLATES/img/interface/mainInterface/icon_audio.png');
		}
		else{
			jQuery('#MPIbtn').removeClass('btnactive').addClass('btnon');
			jQuery('#lateralMPI').hide('blind',{ direction: 'right' });

			jQuery('#audioControl').attr('src','TEMPLATES/img/interface/mainInterface/icon_audio_off.png');
		}
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

	function postOpener(ida){
		window.open ('chat.php?amb='+ida,'fed_main', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0');
	}

	function calendarOpen(){
		pars=getSizeOf('cdb');
		window.open ('calendar.php','cdb', config='toolbar=no,scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}
	
	function cdbOpen(){
		pars=getSizeOf('cdb');
		window.open ('cdb.php','cdb', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}
	
	function cdbOpenToTopic(ida){
		pars=getSizeOf('cdb');
		window.open ('cdb.php?topic='+ida,'cdb', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function dbOpen(){
		pars=getSizeOf('db');
		window.open ('db.php','db', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}
	
	function dbOpenToTopic(ida){
		pars=getSizeOf('db');
		window.open ('db.php?element='+ida,'db', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}
	
	function dbOpenToTopicLit(ida){
		pars=getSizeOf('db');
		window.open ('db.php?litref='+ida,'db', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}
		
	function paddOpen(){
		pars=getSizeOf('padd');
		window.open ('padd.php?anm=yes','padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width='+pars['w']+',height='+pars['h']);
	}

	function paddOpenTo(ida){
		pars=getSizeOf('padd'); 
		window.open ('padd.php?s=seR&to='+ida,'padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width='+pars['w']+',height='+pars['h']);
	}
	
	function statusOpen(){
		pars=getSizeOf('padd');
		window.open ('padd.php?s=sh','padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width='+pars['w']+',height='+pars['h']);
	}

	function openLike(ida,oba){
		pars=getSizeOf(ida);
		return window.open ('about:blank',oba, config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function statusBoxOpen(){

		jQuery.ajax(
			{
			url: 'ajax_getnotifications.php?ts='+new Date().getMilliseconds(),
			success: function(e){ 

				jQuery('#statusContainer').html('');
				jQuery.each(e['notifications'],function(k){
					noti=e['notifications'][k];
					if (noti['opener'] != '')
						linker="onclick=\"javascript:metaPOpen('"+noti['uri']+"',"+noti['opener']+");\"";
					else
						linker='';

					jQuery('#statusContainer').append("<div "+linker+"><div class=\"di\"><img src=\""+noti['image']+"\" ></div><div class=\"dt\"><p><span class=\"dater\">"+noti['dater']+"</span> "+noti['text']+'</p><p class=\"sub\">'+noti['subtext']+'</p></div><div style=\"clear:both;\"></div></div>');
				});
				jQuery('#statusBox').toggle('blind',100);
				jQuery('#btnStatus').removeClass('notify');
			}, 
			type: 'POST',
			dataType : 'json',
			timeout:4500
			}); 
	}

	function localizeRefreshAuto(){if (jQuery('#localizeBox').is(':visible')) {localizeRefresh();}}

	function setAvatar(src)
	{
		jQuery('#commDestImg').fadeOut(100,function(){
			avt=jQuery(src).find('option:selected').attr('data-avatar');
			if ( avt.includes('c_generic.png'))
				avt='TEMPLATES/img/logo/logo_sfc.png';

			jQuery('#commDestImg').css('background-image', "url('"+avt+"')");

			if ( jQuery(src).find('option:selected').attr('entity-type') )
				jQuery('#commDestImg').addClass('boxGlow')
			else
				jQuery('#commDestImg').removeClass('boxGlow');

			jQuery('#commDestImg').attr('title', jQuery(src).find('option:selected').attr('data-title'));
			jQuery('#commDestImg').fadeIn(100);
		});
	}

	function commRefreshPplIterator(e)
	{
		jQuery('#commDestOpt').html('<option value="0" selected="selected" data-avatar="TEMPLATES/img/logo/logo_sfc.png"> Tutto il Personale </option>');
		jQuery('#commDestImg').css('background-image',"url('TEMPLATES/img/logo/logo_sfc.png')");
		jQuery('#commDestImg').removeClass('boxGlow');
		
		jQuery.each(e['people'],function(k){
			jQuery('#commDestOpt').append('<optgroup label="'+e['people'][k]['plName']+'" id="com_'+k+'"></optgroup>');
			e['people'][k]['ppl'].forEach(function(p){
				jQuery('#com_'+k).append('<option entity-type="person" value="'+p['ID']+'" data-title="'+p['user_grado']+' - Sezione '+p['user_sezione']+'" data-avatar="'+p['pgAvatar']+'">'+p['pgUser']+'</option>')
			})
		});
	}

	function commRefreshPlacesIterator(e)
	{
		jQuery('#commDestOpt').html('<option value="0" selected="selected" data-avatar="TEMPLATES/img/logo/logo_sfc.png"> Tutto il Personale </option>');
		jQuery('#commDestImg').css('background-image',"url('TEMPLATES/img/logo/logo_sfc.png')");
		jQuery('#commDestImg').removeClass('boxGlow');
		jQuery('#commDestOpt').html('');
		jQuery('#commDestImg').attr('title','');

		jQuery.each(e['places'],function(k){
			jQuery('#commDestOpt').append('<optgroup label="'+e['places'][k]['plName']+'" id="com_pla_'+k+'"></optgroup>');
			e['places'][k]['places'].forEach(function(p){
				current = (p['myLocat'] == 1) ? 'selected="selected"' : "" ;

				jQuery('#com_pla_'+k).append('<option value="'+p['locID']+'" data-avatar="'+p['image']+'" '+current+'>'+p['locName']+'</option>')
			})
		});		
	}

	function commRefresh(stm){

		if(stm == 'ppl')
			refresher=commRefreshPplIterator;
		if(stm == 'pla')
			refresher=commRefreshPlacesIterator;

		jQuery('#commStyle').val(stm);
		

		jQuery.ajax(
				{
				url: 'ajax_localize.php?s=comm&stm='+stm+'&ts='+new Date().getMilliseconds(),
				success: refresher, 
				type: 'POST',
				dataType : 'json',
				timeout:4500
				});
	}

	function localizeRefresh(){

		
		var saveList = []
		jQuery('.locBlock:visible').each(function(){
	  		saveList.push(jQuery(this).attr('id').replace('loc_',''));
		});
		
		jQuery.ajax(
			{
			url: 'ajax_localize.php?s=loc&ts='+new Date().getMilliseconds(),
			success: function(e){
				strhtml = '';
				ttp=0;
				jQuery('#localizeBoxContainer').html('');
				jQuery.each(e,function(k){

					ship=k; 
					strhtml += '<a class="interfaceLink" style="cursor:pointer" onclick="jQuery(\'#loc_'+k+'\').toggle();"><div class="locControl");"> \
						<div class="locControl_ship"> <img src="TEMPLATES/img/logo/'+e[k][0]['assign_logo']+'" style="width:100px; vertical-align:middle;" /> <div class="online">'+e[k].length+'</div> </div> \
						<div><p class="locControl_shipName">'+e[k][0]['ship_name']+'</p></div> \
					</div></a><div style=\"clear:both;\"/>';


					save= saveList.includes(k) ? 'style="display:block;"' : '';

					strhtml += '<div id="loc_'+k+'" class="locBlock" '+save+'>';
					
					jQuery.each(e[k],function(p){
						pg = e[k][p];
						ttp+=1;
						pgt=pg['pgSpecie'];

						if(pg['user_sesso'] == 'M')
						{  
							pgs='TEMPLATES/img/specie/' + pg['pgSpecie'] + '_m.png';
							pgt+=' - Maschio';
						}
						else if(pg['user_sesso'] == 'F')
						{
							pgs='TEMPLATES/img/specie/' + pg['pgSpecie'] + '_f.png';
							pgt+=' - Femmina';
						}
						else{
							pgs='TEMPLATES/img/specie/' + pg['pgSpecie'] + '_t.png';
							pgt+=' - Terzo Genere';
						}

						if (pg['ambientType'] != 'DEFAULT') goable = '<a href=javascript:void(0);" onclick=\"javascript:fullScreen(\''+pg['pgPlaceI']+'\');\"><span>'+pg['place_name']+'</span></a>';
						else goable = '<span>'+pg['place_name']+'</span>';


						if (pg['pgIC']) presence = '<img title="Il giocatore è attivo nella chat" src="TEMPLATES/img/interface/personnelInterface/online.png" />';
						else presence = '<img title="Il giocatore non è attivo nella chat" src="TEMPLATES/img/interface/personnelInterface/offline.png" />';

						lockValue = (pg['locked'] == 1) ? '<span class="radiusBordered5 lockedIcon">LOCK</span>' : '';
						

						strhtml += '<div class="userRow leftCol"><div class="'+pg['rank_class']+'" style="background-image:url(\'TEMPLATES/img/ranks/'+pg['rank_mostrina']+'.png\');" title="'+pg['user_grado']+' - Sezione '+pg['user_sezione']+'"></div><img src="'+pgs+'" title="'+pgt+'"></img> '+lockValue+'<a class="interfaceLink" href="javascript:void(0);" onclick="schedaPOpen('+pg['ID']+');">'+pg['pgUser']+'</a></div> <div class="userRow rightCol">'+goable+presence+'</div><div style="clear:both"/>';
					});
					strhtml += '</div>';
					
					jQuery('#localizeBoxContainer').append(strhtml);
					
					strhtml='';
				});
				jQuery('#localizeBoxTail').html(ttp+' PG Connessi');
			}, 
			type: 'POST',
			dataType : 'json',
			timeout:4500
			});
	}

	function fullScreen(chat)
	{
		var wi = screen.availWidth - 10;
		var au = screen.availHeight - 30;
		window.open("chat.php?amb="+chat, "fed_main", "width=" + wi + ",height=" + au + ",top=0,left=0,location=no,menubar=no,resizable=yes,scrollbar=yes");
	}

	function locOpen(){
		jQuery.when(localizeRefresh()).then(function(){jQuery('#localizeBox').toggle('blind',100)});
	}
	
	function toggleMyPresence(eventID){
			jQuery.ajax(
			{
			url: 'ajax_set_event_participation.php?ts='+new Date().getMilliseconds(),
			data: {eventID: eventID},
			success: function(e){ 
				if(e)
				{
					if(e['mode'] == 'REM')
						{
							jQuery('#participationMark_'+eventID).removeClass().addClass('participationYes').prop('title','Conferma Partecipazione');
							jQuery('#participationBox_'+eventID+' #participationBox_element_'+e['pgID']).fadeOut(function(){jQuery(this).remove()});
						}
					if(e['mode'] == 'ADD')
						{
							jQuery('#participationBox_'+eventID).append('<span style="display:none;" class="eventParticip" id="participationBox_element_'+e['pgID']+'" onclick="toggleMyPresence('+eventID+')"><img src="'+e['pgAvatarSquare']+'" title="'+e['pgUser']+'"/></span>');
							jQuery('#participationMark_'+eventID).removeClass().addClass('participationNo').prop('title','Annulla Partecipazione');
							jQuery('#participationBox_'+eventID+' #participationBox_element_'+e['pgID']).fadeIn();
						}
				}
			}, 
			type: 'POST',
			dataType : 'json',
			timeout:4500
			}); 
	}

	function metaPOpen(par,fun){ fun(par); }

	function link2place(ida){
		ida = ida.match(/[a-zA-Z0-9_,]+/g);
		window.location.href = 'main.php?l='+ida;
	}
	
	function tribuneOpen(ida){
		pars=getSizeOf('padd');
		window.open ('padd.php?s=readTribune&newsID='+ida,'padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width='+pars['w']+',height='+pars['h']);
	}
	
	function commOpen(){
		jQuery.when(commRefresh('ppl')).then(
		function(){
			jQuery('#communicatorBox').toggle('blind',100);
		});
	}
	
	function schedaOpen(){
		pars=getSizeOf('scheda');
		window.open ('scheda.php','scheda', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}	

	function schedaMedOpen(){
		pars=getSizeOf('scheda');
		window.open ('scheda.php?s=me','scheda', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}
	
	function schedaSstoOpen(){
		pars=getSizeOf('scheda');
		window.open ('scheda.php?s=ssto','scheda', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function schedaPOpen(ida,eba=''){
		pars=getSizeOf('scheda');
		ebaE = (eba != '') ? '&s='+eba.match(/[a-zA-Z]+/g) : '';

		if(typeof(ida) == 'number' || typeof(parseInt(ida)))
			window.open ('scheda.php?pgID='+ida+ebaE,'schedaP', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function masterShadow(){
		pars=getSizeOf('multitool');
		window.open ('multitool.php','shadow', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function chartOpen(toIda=''){
		pars=getSizeOf('chart');
		ida = (toIda != '') ? '?coords='+toIda.match(/[a-zA-Z0-9;:]+/g) : '';
		window.open ('chart.php'+ida,'chart', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function whisperOpen(){
		pars=getSizeOf('whisper');
		window.open ('whisper.php','whisper', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function repliOpen(toIda){
		pars=getSizeOf('replicator');
		toIda = toIda.match(/[a-zA-Z0-9_,]+/g);
		window.open ('replicator.php?loc='+toIda,'replicator', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function repliOpenP(toFood){
		pars=getSizeOf('replicator');
		if(typeof(ida) == 'number')
			window.open ('replicator.php?loc='+jQuery('#locID').val()+'&foodID='+toFood,'replicator', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width='+pars['w']+',height='+pars['h']);
	}

	function doLogout(){
		jQuery.post('login.php?action=logout', {}, function(){
		window.close();
		});
	}

	function getSizeOf(ida)
	{
		if (browserDetect() in windowSizes[ida])
			browser=browserDetect();
		else
			browser='Default';
		
		var height=windowSizes[ida][browser]['h'] * window.devicePixelRatio;
		var width=windowSizes[ida][browser]['w'] * window.devicePixelRatio;
		//alert(browser+':: '+width+' x '+height);
		return {'w':width,'h':height};
	}

	function openDotazione(){
			jQuery.ajax(
			{
			url: 'servicePage.php?getDot=true&me=true', 
			success: function(e){

				jQuery('#mostriLoc').prop('src','TEMPLATES/img/ranks/'+e['pgMostrina']+'.png');
				jQuery('#mostriLoc').prop('title',e['pgGrado']+' - Sezione '+e['pgSezione']);
				jQuery('#raceLoc').prop('src','TEMPLATES/img/specie/'+e['pgSpecie']+'_'+ e['pgSesso'].toLowerCase()+'.png');
				
				jQuery('#ordinaryVestDescription').html(e['currentDescript']);

				if (e['DATA']['ABITI'][0] && e['pgMostrina'] == 'CIV')
				{ 
					jQuery('#additionalVestDetailStatic').html(e['DATA']['ABITI'][0]['descr']);
					jQuery('#additionalVestDetailStatic_stagec').val(e['DATA']['ABITI'][0]['descr']);

					customImage = (e['DATA']['ABITI'][0]['image'] != '') ? e['DATA']['ABITI'][0]['image'] : 'TEMPLATES/img/uniformi/nouniformf_small.png'

					jQuery('#uniLoc').prop('src',customImage);
					jQuery('#uniloc_stage').val(customImage); 
				}
				else{
					jQuery('#uniLoc').prop('src','TEMPLATES/img/uniformi/'+e['currentUniform']+'_small.png');
				}
				
				jQuery('#currentDotazione p').remove();
				jQuery('#selecterAdd option').remove();

				jQuery.each(e['DATA']['OBJECT'],function(k){
					jQuery('#currentDotazione').append("<p style=\"background-image:url('"+e['DATA']['OBJECT'][k]['image']+"');\" onclick=\"javascript:remDot(this,"+e['DATA']['OBJECT'][k]['recID']+","+e['DATA']['OBJECT'][k]['oID']+")\" class=\"tooltip\" title=\""+e['DATA']['OBJECT'][k]['oName']+"\" />");
				});

				jQuery.ajax(
				{
					url:'servicePage.php?getMyObj=true',
					timeout:4500,
					type: 'POST',
					dataType : 'json',
					success: function(e){
						jQuery.each(e['SERVICE'],function(k){
							jQuery('#selecterAddServ').append('<option value="'+e['SERVICE'][k]['oID']+'">'+e['SERVICE'][k]['oName']+'</option>');
						});
						jQuery.each(e['PERSONAL'],function(k){
							jQuery('#selecterAddPers').append('<option value="'+e['PERSONAL'][k]['oID']+'">'+e['PERSONAL'][k]['oName']+'</option>');
						});

						jQuery('#dotazioner').fadeIn(300);
					}
				});
			}, 
			type: 'POST',
			dataType : 'json',
			timeout:4500
			});
		}

		function remDot(ob,ida,eba){
				jQuery.post('servicePage.php?remDot=true', {iRem: ida,oID: eba}, function(e){ if(e['STA']){
					if (e['RT']['oType'] == 'PERSONAL')
						ol='#selecterAddPers';
					else 
						ol='#selecterAddServ';

					jQuery(ol).append('<option value="'+e['RT']['oID']+'">'+e['RT']['oName']+'</option>');
					jQuery(ob).fadeOut();
				}
			},'json');
		}

		function addDot(ob)
		{
			if (ob != '')
				jQuery.post('servicePage.php?addDot=true', {iAdd: ob}, function(e){
					if(e['STA']) {
						jQuery('#currentDotazione').append("<p style=\"background-image:url('"+e['RT']['image']+"');\" onclick=\"javascript:remDot(this,"+e['RT']['recID']+","+e['RT']['oID']+")\" class=\"tooltip\" title=\""+e['RT']['oName']+"\" />"); 
						jQuery('#selecterAdd option[value="'+ob+'"]').remove();
					}
				},'json');
		}
 
		function prevMostrina(ida)
		{
			jQuery.post('servicePage.php?prevMos=true', {emoSel: ida}, function(e){ setFormer(e); if (ida == 'CIV'){

				jQuery('#dscr').append("<textarea id=\"additionalVestDetail\" name=\"additionalVestDetail\" placeholder=\"Puoi inserire qui una descrizione aggiuntiva degli abiti civili: apparirà come popup se qualcuno passerà il cursore sulla mostrina in chat!\"></textarea> URL Immagine: <input class=\"neon\" type=\"text\" id=\"additionalVestImage\" onchange=\"updatePreviewVest(this)\" name=\"additionalVestImage\"></input>");
				jQuery('#additionalVestDetail').val(jQuery('#additionalVestDetailStatic_stage').val());
				jQuery('#uniLoc').attr('src',jQuery('#uniloc_stage').val());
				jQuery('#additionalVestImage').val(jQuery('#uniloc_stage').val());
			} }, 'json');
		}

		function updatePreviewVest(e)
		{
			jQuery('#uniLoc').attr('src',jQuery(e).val()); 
		}
		
		function setFormer(e)
		{ 
			jQuery('#uniLoc').attr('src','TEMPLATES/img/uniformi/'+e['DLU']+'_small.png');
			jQuery('#mostriLoc').attr('src','TEMPLATES/img/ranks/'+e['DLT']+'.png');
			jQuery('#dscr').html(e['TTT']);
			jQuery('#confirmer').css('visibility','visible');
		}

		function confirmC()
		{ 
			ida = jQuery('#emoSel').val();
			additionalVestDetail = (jQuery('#additionalVestDetail').length) ? jQuery('#additionalVestDetail').val() : '';
			additionalVestImage = (jQuery('#additionalVestImage').length) ? jQuery('#additionalVestImage').val() : '';

			jQuery.post('servicePage.php?setMos=true', {emoSel: ida, civDetail: additionalVestDetail, civimage:additionalVestImage}, function(e){if(e['DLT']) {jQuery('#remindMos').attr('src','TEMPLATES/img/ranks/'+e['DLT']+'.png'); jQuery('#dotazioner').fadeOut();}}, 'json');
		}
