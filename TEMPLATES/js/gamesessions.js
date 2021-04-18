//v 1.9

jQuery(function(){
		
		jQuery( "#tusers")
		// don't navigate away from the field on tab when selecting an item

		.autocomplete({
			source: function( request, response ) {
				$.getJSON( "ajax_userGetter.php", {
					term: extractLast( request.term )
				}, response );
			}, 
			search: function()
			{
				// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 3 ) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
			}
		});
});
		
function split( val ) {
		return val.split( /,\s*/ );
}

function extractLast( term ) {
	return split( term ).pop();
}



function initSessionsPanel(){  
			jQuery('#YesSessions, #NoSessions, .viewQuest').hide();

			jQuery('#sessionsP').fadeIn(100);

			jQuery.post('ajax_sessionManager.php?action=check-ambient', {amb: jQuery('#ambientID').prop('value')},
						
				function(e){
					if (e==0) {
						jQuery('#NoSessions').show();
					}
					else
					{
						jQuery('#sessionDivLabel').html(e['session']['sessionLabel'] +' (' + e['session']['sessionLength'] + ' min.)' );
						jQuery('#sessionDivOwnerPlace').html(e['session']['pgUser']);

						if(parseInt(e['session']['sessionIntervalTime']) != 8) jQuery('#sessionDivTimer').html(', durata del turno: <span style="color:#FFCC00; font-weight:bold;">'+e['session']['sessionIntervalTime']+' minuti</span>');
						jQuery('#sessionDivDate').html(e['session']['sessionStart']);

						if(e['session']['sessionMaster'] == "1") {jQuery('.viewQuest').show();}

						var strr='';

						for (var pg in e['people'])
						{
							strr+="<tr><td style='text-align:left; height:20px;'><img src='TEMPLATES/img/ranks/"+e['people'][pg]['ordinaryUniform']+ ".png'></img><a href='javascript:void(0);' onclick='schedaPOpen("+e['people'][pg]['pgID']+")' class='interfaceLink'> "+e['people'][pg]['pgUser']+"</a></td> <td>"+e['people'][pg]['COUNT(realLen)']+"</td> <td>"+ Math.floor(e['people'][pg]['SUM(realLen)']/1000)+"<span style='color:#3188e1'>k</span> </td> <td>"+ Math.floor(e['people'][pg]['averageLen'])+"</td></tr>";
						} 

						strr+="<tr style=\"margin-top:2px; border-top:1px solid #555;\"><td colspan='3'>Mediana dei caratteri:</td><td style=\"background-color:#041d2f; color:#0d314b; color:white; padding:2px; line-height:10px; font-weight:bold;\">"+e['allAVG']+" <span class=\"tooltip\" title=\"Cerca di stare vicino a questo valore per ottenere il maggior numero di FP!\">[?]</span> </td></tr>"; 
						jQuery('#YesSessions #tablePG').html(strr); 
						jQuery('#YesSessions').show();
					}


				}, 'json');


			jQuery.post('ajax_sessionManager.php?action=check-private', {amb: jQuery('#ambientID').prop('value')},

			function(t){
				var strr='';
				var k= JSON.parse(t);
				
				if (k == 0){strr='Tutti';}
				else{
				for (var pg in k)
				{	
					
					strr+="<a href='javascript:void(0);' onclick='schedaPOpen("+k[pg]['id']+")' class='interfaceLink'> "+k[pg]['user']+"</a>, ";
				}
				}

				jQuery('#PtablePG').html("Sessione riservata: "+strr);
			}); 
		} 

function closeSession(){

	if(confirm('Vuoi veramente chiudere la sessione di gioco?'))
	{

		jQuery.ajax(
		{
			url: 'ajax_sessionManager.php?action=close-active',
			data:{amb: jQuery('#ambientID').prop('value')},
			complete: function(e){
				noti(e);
				initSessionsPanel();
			}, 
			type: 'POST',
			dataType : 'json',
			timeout:5000
		}); 
	}
	
}

function openSession(){
	var masterCheck;

	if(jQuery('#masterCheck').is(':checked')){ masterCheck = 1;}
	else {masterCheck = 0;}

	//jQuery.ajax('ajax_sessionManager.php?action=open-new', {amb: jQuery('#ambientID').prop('value'), master:masterCheck,maxchar:jQuery('#tcharrer').prop('value'),maxturner:jQuery('#maxturner').val(), lister:jQuery('#tusers').val(), label:jQuery('#sessionLabel').prop('value')});


	jQuery.ajax(
	{
		url: 'ajax_sessionManager.php?action=open-new',
		data:{amb: jQuery('#ambientID').prop('value'), master:masterCheck,maxchar:jQuery('#tcharrer').prop('value'),maxturner:jQuery('#maxturner').val(), lister:jQuery('#tusers').val(), label:jQuery('#sessionLabel').prop('value'), descript: jQuery('#sessionDescript').prop('value')},
		complete: function(e){
			noti(e);
			initSessionsPanel();
		}, 
		type: 'POST',
		dataType : 'json',
		timeout:5000
	}); 
}
