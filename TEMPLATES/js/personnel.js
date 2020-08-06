function load_playrecord_vector(ida){
			
			
			jQuery('#player_record_btn').html('... caricamento ...').removeClass('interfaceLinkGreen');
			jQuery.ajax(
			{
			url: 'scheda.php',
			dataType : 'json',
			data: {pgID: ida, s: 'ajax_getactivityrecord'},
			success: function(e){
				gen='';
				for (var d in e.reverse()){
					var classer = 'no';
					if(e[d].mastered) { classer='yesMas'; }
					else if(e[d].played) { classer='yesPlay'; }
					else if (e[d].connected) { classer='yesConn'; }
					gen += '<p style="float:left; margin:0px; width:10px; height:10px;" class="'+classer+'" title="'+e[d].kk+'" />';
					}
					jQuery('#player_record_btn').fadeOut(100,function(){
						jQuery('#player_record').append(gen);	
					});


				
			},
			type: 'GET'
			});
	}

	function load_statsrecord_vector(ida){
			
			
			jQuery('#player_stats_btn').html('... caricamento ...').removeClass('interfaceLinkGreen');
			jQuery.ajax(
			{
			url: 'scheda.php',
			dataType : 'json',
			data: {pgID: ida, s: 'ajax_getstats'},
			success: function(e){
				gen='<table>';
				for (var d in e){

					

						tval='<ul>';
						for (var t in e[d]){
							tval += ('<li>'+t + ' : '+ e[d][t]+'</li>');
						}
						tval+='</ul>';
					
			 

					gen+= '<tr><td class="tval">'+d+'</td>  <td>'+tval+'</td></tr>';
					
				}

				gen+='</table>'; 
				jQuery('#statsPanel').html(gen).fadeIn();
				jQuery('#player_stats_btn').fadeOut();





				
			},
			type: 'GET'
			});
	}