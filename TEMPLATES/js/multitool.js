jQuery(function(){
			
			jQuery('#dipartimento').autocomplete({source: "ajax_gradiGedder.php?dipartimenti=do",	minLength: 2});
			jQuery('#divisione').autocomplete({source: "ajax_gradiGedder.php?divisioni=do",	minLength: 2});

			jQuery( "#users")
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
	
	function closeSession(ida){ 
		jQuery.post('multitool.php?s=closeSession&ida='+ida,
			function e(data){
				
				if(data=="OK")
					alert(ida);
					jQuery('#session_'+ida+' .statuser span').html('FORCED');
					jQuery('#session_'+ida+' .statuser span').removeClass().addClass('forced');
					jQuery('#session_'+ida+' .commander').html('');
			}
		);		
	}

	function upd(vara)
	{
		jQuery('#brever').css('background-image','url(\'TEMPLATES/img/ruolini/'+vara+'\')');
	}
	function paddOpen(who){window.open ('padd.php?s=seR&to='+who+'&sub=','padd', config='scrollbars=no,status=no,location=no,resizable=no,resizale=0,top=140,left=500,width=655,height=403');}
	
	function blockList()
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di Bloccare tutti i PG selezionati?')) jQuery.post('multitool.php?s=blockBulk', {listof: jQuery('#users').prop('value')}, confirmer, 'json');
	}

	function setReassign()
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di Spostare tutti i PG selezionati?')) jQuery.post('multitool.php?s=reassignBulk', {listof: jQuery('#users').prop('value'), placeAssign: jQuery('#assegnazioneSpostamento').prop('value')}, confirmer, 'json');
	}

	function delList()
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di Cancellare tutti i PG selezionati?')) jQuery.post('multitool.php?s=delete', {listof: jQuery('#users').prop('value')}, confirmer, 'json');
	}


	function groupList()
	{
		if(jQuery('#users').val() != '' && confirm('Questo comando unificherà i record OFF (data iscrizione e achievement) dei PG selezionati. L\'utility imposterà come PG principale l\'ultimo PG della lista. Tutti gli altri saranno contrassegnati come doppi')) jQuery.post('multitool.php?s=group', {listof: jQuery('#users').prop('value')}, confirmer, 'json');
	}
	function switchList()
	{
		if(jQuery('#users').val() != '' && confirm('Questa è la procedura di cambio PG. L\'utility imposterà come PG VECCHIO il primo PG della lista e come PG NUOVO il secondo PG della lista. Casi con più di due PG non saranno accettati.')) jQuery.post('multitool.php?s=switch', {listof: jQuery('#users').prop('value')}, confirmer, 'json');
	}

	function bavoList()
	{
		if(jQuery('#users').val() != '' && confirm('Saranno tutti dei vermi verminosi e bavosi! Confermi?')) jQuery.post('multitool.php?s=bavosize', {listof: jQuery('#users').prop('value')}, confirmer, 'json');
	}
	
	function setSeclar(ida)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di riassegnare il seclar a tutti i PG selezionati?')) jQuery.post('multitool.php?s=setSeclar', {listof: jQuery('#users').prop('value'), seclar: ida}, confirmer, 'json');
	}
	function setNastrin(ida,oda)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro assegnare la medaglia a tutti i PG selezionati?')) jQuery.post('multitool.php?s=addMedals', {listof: jQuery('#users').prop('value'), medal: ida, timer: oda}, confirmer, 'json');
	}


	function setPrestige(ida,oda)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di riassegnare il prestigio a tutti i PG selezionati?')) jQuery.post('multitool.php?s=setPrestige', {listof: jQuery('#users').prop('value'), prestigeLevel: ida, reason: oda}, confirmer, 'json');
	}
	
	function giveObjects(ida)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di assegnare gli oggetti a tutti?')) jQuery.post('multitool.php?s=addServiceObj', {listof: jQuery('#users').prop('value'), obID: ida}, confirmer, 'json');
	}
	function espropriaObjects(ida)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di assegnare gli oggetti a tutti?')) jQuery.post('multitool.php?s=removeServiceObj', {listof: jQuery('#users').prop('value'), obID: ida}, confirmer, 'json');
	}
	

	function setSalute(ida)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di modificare lo status di salute a tutti i PG selezionati? (Viene inviato un Padd ad ogni PG)')) jQuery.post('multitool.php?s=setSalute', {listof: jQuery('#users').prop('value'), salute: ida}, confirmer, 'json');
	}
	
	function setMostrina(ida)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di voler cambiare la mostrina a tutti i PG selezionati?')) jQuery.post('multitool.php?s=setMostrina', {listof: jQuery('#users').prop('value'), mostrina: ida}, confirmer, 'json');
	}
	
	function sendModerazione(ida)
	{
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di voler inviare la moderazione a tutti i PG selezionati?')) jQuery.post('multitool.php?s=sendModeration', {listof: jQuery('#users').prop('value'), moder: ida}, confirmer, 'json');
	}
	
	function setIncarico()
	{
		var ida = jQuery('#incarico').val();
		var eda = jQuery('#assegnazione').val();
		var oda = jQuery('#dipartimento').val();
		var uda = jQuery('#divisione').val();
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di modificare incarico e assegnazione di tutti i PG selezionati?')) jQuery.post('multitool.php?s=setIncarico', {listof: jQuery('#users').prop('value'), incarico: ida, assegnazione: eda, dipartimento: oda, divisione: uda}, confirmer, 'json');
	}
	
	function ajax_getProfilingPadds(ida){

		 jQuery.post('multitool.php?s=getProfilingPadds', {ider: ida}, function(e){
		 	t='';
		 	y='';
		 	jQuery.each(e['padder'],function(k){ 

		 		paddID= e['padder'][k]['padID'];
				fromID =  e['padder'][k]['paddFrom'];
				fromUser =  e['padder'][k]['FromPG'];
				toID = e['padder'][k]['paddTo'];
				toUser =  e['padder'][k]['ToPG'];
				paddTitle = e['padder'][k]['paddTitle'];
				paddpgAvatarSquare  = e['padder'][k]['pgAvatarSquare'];
				paddordinaryUniform = e['padder'][k]['ordinaryUniform'];
				paddPgSpecie  = e['padder'][k]['pgSpecie'];
				paddpgSesso = e['padder'][k]['pgSesso'];
				paddpday = e['padder'][k]['pday'];
				paddphour = e['padder'][k]['phour'];
				paddpmin = e['padder'][k]['pmin'];
				paddpcontent = e['padder'][k]['pcontent'];
  

		 		t=t+'<div id="padder" class="paddMain radiusBordered"> <div class="innerPadd"><div id="padderList"><div onclick="jQuery(\'#padd_'+paddID+'\').toggle(); jQuery(this).toggleClass(\'active\');"><a onclick="schedaPOpen('+fromID+')" class="interfaceLink" href="#">'+fromUser+'</a> <span style="color:#0077c3; font-weight:bold;">></span> <a onclick="schedaPOpen('+toID+')" class="interfaceLink" href="#">'+toUser+'</a> <span>'+paddTitle+' - '+paddpday+'</span></div></div>' + '<div id="padd_'+paddID+'" style="display: none;"><div style="width:135px; float:left; margin-top:10px;"><div><img style="width:120px; height:120px; padding:7px; border:1px solid #222;" src="'+paddpgAvatarSquare+'" class="radiusBordered" /></div><div style="margin-top:5px; text-align:center;"><img src="TEMPLATES/img/ranks/'+paddordinaryUniform+'.png" style="vertical-align:middle;" /> <img style="vertical-align:middle; vertical-align:middle;" src="TEMPLATES/img/specie/'+paddPgSpecie+'_m.png"></img></div><div style="text-align:center; font-family:century gothic, arial; color:#0077c3;"><span style="font-size:25px;">'+paddphour+'</span> : <span style="font-size:25px; color:#0077c3;">'+paddpmin+'</span><br /><span style="font-size:14px;">'+paddpday+'</span></div></div><div style="width:395px; overflow-x:hidden; overflow:y:auto; margin-top:10px; margin-left:15px; padding:10px; float:left; border:1px solid #999; height:218px;" class="radiusBordered"><span>'+paddpcontent+'</span></div><div style="clear:both;" /></div></div></div></div></div>';




		 	});

		 	jQuery('#padders .container').html(t);
		 	jQuery('#padders').fadeIn();

		 }, 'json');
	}
	
	function noteAdd()
	{
		var ida = jQuery('#note').val();
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di aggiungere alle note master di tutti i PG selezionati questo testo?')) jQuery.post('multitool.php?s=addNote', {listof: jQuery('#users').prop('value'), note: ida}, confirmer, 'json');
	}
	function addPoints()
	{
		var1 = jQuery('#addPoints').val();
		var2 = jQuery('#pointDetail').val();
		if(jQuery('#users').val() != '' && confirm('Sei sicuro di aggiungere punti a tutti i PG selezionati?')) jQuery.post('multitool.php?s=addPoints', {listof: jQuery('#users').prop('value'), code: var1, detail:var2}, confirmer, 'json');
	}
	
	function confirmer(data){alert('Operazione Completata per i PG: '+jQuery('#users').val()); jQuery('#users').val('');}
	
	