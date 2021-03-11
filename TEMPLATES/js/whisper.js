var are;
		var area;
		jQuery(function()
		{
			are = setInterval("updater();",8000); 
			jQuery("#chatDiv").scrollTop(jQuery("#chatDiv")[0].scrollHeight);
		});
		
		window.onfocus = function() {jQuery('#focused').prop('value','1'); jQuery.post('whisper.php?justFocus=do');}		
		window.onblur = function() {jQuery('#focused').prop('value','0');}		
		
		function updater()
		{ // JQUERY YOUR SISTER! 
			jQuery.ajax(
			{
			url: 'ajax_whisperGetter.php?ts='+new Date().getMilliseconds(),
			data:{vinculum:jQuery('#publiGetterChannel').prop('value'),lastID: jQuery('#lastID').prop('value'), focused: jQuery('#focused').prop('value')},
			success: setterCA, 
			type: 'POST',
			dataType : 'json',
			timeout:5000
			});  
		}
		
		function selectUser(ida){
		    nt=jQuery('#chatInput').prop('value');
		    jQuery('#chatInput').prop('value',nt+' @'+ida+' ');
		}

		function upa()
		{ // JQUERY YOUR SISTER!  
			jQuery.ajax(
			{
			url: 'ajax_onlineGetter.php?ts='+new Date().getMilliseconds(),
			success: setterPG,
			type: 'POST',
			dataType : 'json',
			timeout:5000
			}); 
		}
		
		function setterCA(data)
		{
			if(data['CH'] != '') 
			{
			if(jQuery('#lastID').prop('value') != data['LCH'])
			{
			jQuery('#chatDiv').append(data['CH']);
			jQuery('#lastID').prop('value',data['LCH']);
			jQuery("#chatDiv").scrollTop(jQuery("#chatDiv")[0].scrollHeight);
			}
			}
			if(data['PGP'] != '') {
				sendToValue = jQuery('#selectedSendTo').prop('value');
				if (sendToValue == 0 || sendToValue == 7)
					classTutti = 'class="activ"';
				else classTutti = '';

				jQuery('#onlineUserList').html('');
				jQuery('#onlineUserList').append('<div id="ST_'+jQuery('#publiGetterChannel').prop('value')+'" onclick="setSendTo(this)" '+classTutti+'><img src="TEMPLATES/img/ranks/CIV.png" /> <span> TUTTI </span></div> <hr style="width:80%; border-color:#555;" />');

				data['PGP'].forEach(function(ea){

 					if (sendToValue == ea['pgID'])
						ct = 'class="activ"';
					else ct = '';

					if( ea['role'] == 'M' || ea['role'] == 'G' || ea['role'] == 'A' || ea['role'] == 'L')
						classStaff = 'class="gani_'+ea['role']+'"';
					else classStaff = '';
					jQuery('#onlineUserList').append('<div id="ST_'+ea['pgID']+'" onclick="setSendTo(this)" ondblclick="schedaPOpen('+ea['pgID']+')" '+ct+'><img src="TEMPLATES/img/ranks/'+ea['pgMostrina']+'.png" /> <span '+classStaff+'> '+ea['label']+'</span></div>');
				}); 
				
			}
		}
		 

		function sendChat()
		{
		if(jQuery('#chatInput').prop('value')!='')
 
			jQuery.ajax(
			{
			url: 'ajax_sendWhisper.php',
			data: {chatLine: jQuery('#chatInput').prop('value'), chatTo: jQuery('#selectedSendTo').prop('value')},
			success: updater,
			async:false,
			type: 'POST'
			});
		
		jQuery("#chatInput").prop('value','');
		jQuery("#chatInput").focus();
		}
		
			function sX(e)
		{
			var pitapa = (window.event) ? e.keyCode : e.which;
			if(pitapa == 13) sendChat(jQuery('#locID').prop('value'),jQuery('#chatInput').prop('value'),jQuery('#chatTag').prop('value'));
		}

		function setSendTo(el){

			jQuery('#onlineUserList div').removeClass();
			jQuery(el).addClass('activ');
			jQuery('#selectedSendTo').prop('value',jQuery(el).prop('id').replace('ST_',''));
		}