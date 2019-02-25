
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
	function tribuneOpen(ida){window.open ('padd.php?s=readTribune&newsID='+ida,'padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width=655,height=403');}
	function commOpen(){window.open ('comm.php','comm', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=650,height=160');}
	function schedaOpen(){window.open ('scheda.php','scheda', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=653,height=605');}
	function schedaMedOpen(){window.open ('scheda.php?s=me','scheda', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=653,height=605');}
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
			jQuery.post('servicePage.php?remDot=true', {iRem: ida,oID: eba}, function(e){ if(e['STA']) {
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
	
	