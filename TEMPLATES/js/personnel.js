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

function checkIP(ida){
			
			gen='';
			jQuery('#player_IP_btn').html('... caricamento ...').removeClass('interfaceLinkRed');
			jQuery.ajax(
			{
			url: 'scheda.php',
			dataType : 'json',
			data: {pgID: ida, s: 'checkIP'},
			success: function(e){
				
				gen += "<ul><li><b>Ultimo IP:</b> "+e['lastIP']['IP']+'</li>';
				gen += "<li><b>Host:</b> "+e['lastIP']['notes']+'</li>';
				gen += "<li><b>UA:</b> "+e['lastIP']['ua']+'</li>';
				gen += "<li><b>UL:</b> "+e['lastIP']['ul']+'</li></ul>';

				gen += "<ul><li><b>ISP:</b> "+e['ISP']+' '+e['organization']+'</li>';
				gen += "<li><b>Luogo: </b>"+e['country_code']+' '+e['flag']+' '+e['region']+' '+e['city']+'</li>';
				gen += "<li><b>OS:</b> "+e['operating_system']+"</li>";
				gen += "<li><b>Browser:</b> "+e['browser']+"</li></ul>";
				
				gen += "<p style=\"color:red; font-weight:bold;\">Warnings</p><ul>";
				gen += "<li><b>Fraud Score:</b> "+e['fraud_score']+"</li>";
				if(e['mobile'])
					gen += "<li>📱 (mobile)</li>";
				if(e['proxy'])
					gen += "<li>🌎 Rilevato Proxy</li>";
				if(e['tor'])
					gen += "<li>🧅 Rilevato TOR</li>";
				if(e['vpn'])
					gen += "<li>🕵️ Rilevata VPN</li>";
				if(e['recent_abuse'])
					gen += "<li>🚨 Recent Abuse</li>";

				gen += "</ul>";

				jQuery('#trackInfo').html(gen).fadeIn();
				jQuery('#player_IP_btn').fadeOut();
			},
			type: 'GET'
			});
	}