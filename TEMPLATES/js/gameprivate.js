function initPrivatePanel(){
	
	
	
	jQuery('#NoPrivate, #YesPrivate').hide();

	jQuery('#privateP').fadeIn(100); 
jQuery.post('ajax_sessionManager.php?action=check-private', {amb: jQuery('#ambientID').prop('value')},
			
	function(e){
		if (e==0) {
			jQuery('#NoPrivate').show();
		}
		else
		{
			 
			
			// jQuery('#sessionDivLabel').html(e['session']['sessionLabel'] +' (' + e['session']['sessionLength'] + ' min.)' );
			// jQuery('#sessionDivOwnerPlace').html(e['session']['pgUser']);
			// jQuery('#sessionDivDate').html(e['session']['sessionStart']);
			
			// if(e['session']['sessionMaster'] == "1") {jQuery('.viewQuest').show();}
			
			var strr='';

			for (var pg in e)
			{
				var strrowner='';
				if(e[pg]['owner']) strrowner = '&nbsp;&nbsp; <span style="font-size:11px; color:red;">[*]</span>';
				
				strr+="<tr><td style='text-align:left;'><img src='TEMPLATES/img/ranks/"+e[pg]['rankimage']+ ".png'></img><a href='javascript:void(0);' onclick='schedaPOpen("+e[pg]['id']+")' class='interfaceLink'> "+e[pg]['user']+"</a>"+strrowner+"</td></tr>";
			} 
			jQuery('#YesPrivate #tablePG').html(strr);  
			jQuery('#YesPrivate').show();
		}
			
			 
	}, 'json');
	
}

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

function startPrivate(ida){
	jQuery.post('ajax_sessionManager.php?action=open-private', {amb: jQuery('#ambientID').prop('value'), lister:ida},initPrivatePanel);
	jQuery(' #YesPrivate #privateSwitch').html('<a class="interfaceLinkBlue" href="javascript:void(0);" onclick="javascript:stopPrivate();">[ - Chiudi questa chat privata - ]</a>');
	jQuery('#sessionSwitch').fadeOut();
}

function stopPrivate(ida){
	jQuery.post('ajax_sessionManager.php?action=close-private', {amb: jQuery('#ambientID').prop('value')},initPrivatePanel);
	jQuery('#sessionSwitch').fadeIn();
}