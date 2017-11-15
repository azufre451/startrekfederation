var map
		
		jQuery(function(){
			jQuery('.introb').button();
			
			$( "#systemSearcher" ).autocomplete({
			source: "ajax_planetSearch.php?mode=1",
			minLength: 2,
			});
			
		});
		
		function lookerUp()
		{
			jQuery.ajax(
			{
			url: "ajax_planetSearch.php?mode=3",
			data:{term: jQuery('#systemSearcher').val()},
			type: 'POST',
			dataType : 'json',
			success : function(e){map.setView(map.unproject([e[0],e[1]],6),6); jQuery('#searcher').fadeOut()}
			});  
		}
		
		function setPosition(ida,oba)
		{
			map.setView(map.unproject([ida,oba],6),6)
		}
		
		function initiate(top,left)
		{ 	
		var mapMinZoom = 3;
        var mapMaxZoom = 6;
		
        map = L.map('map', { 
          maxZoom: mapMaxZoom,
          minZoom: mapMinZoom,
          crs: L.CRS.Simple, // Simple plane for easier X Y coordinate system
		  maxBounds: [[-156,0],[0,157]]
		}).setView([0,0],6); 
		
		 
        L.tileLayer('http://www.startrekfederation.it/TEMPLATES/img/charts/exo7/{z}/{x}/{y}.png', {
          minZoom: mapMinZoom, maxZoom: mapMaxZoom, 
          attribution: 'Star Trek: Federation',
          noWrap: true          
        }).addTo(map); 
		
		var popup = L.popup();

		map.on('dblclick', function(e) {
			popup
				.setLatLng(e.latlng)
				.setContent("<p>Solo un punto vuoto nello spazio... <br/>Coordinate: "+map.project(e.latlng,6)+"</p>")
				.openOn(map); 
		});
		
		if(top>0 && left >0){map.setView(map.unproject([top,left]),6); var maker = L.marker(map.unproject([top,left]),{title: "La tua posizione"}).addTo(map);}
		else {map.setView(map.unproject([3950,3940]),6);}
		
		}
		 
		function doE()
		{
			map.panTo(map.unproject([3300,3093]),6);
		}
		
		function getMessage(planID)
		{
			var LL
			var t = jQuery.ajax(
			{
			url: "ajax_planetSearch.php?mode=2",
			data:{term: planID},
			type: 'POST',
			dataType : 'json',
			async : false,
			success : function(e){LL = e},
			timeout:2500
			});  
			return LL;
		}
		
		function addMarker(top,left)
		{
		// var marker = L.circle(map.unproject([top,left]), 2000, {color: 'green',fillColor: '#3F3',fillOpacity: 1, className: 'circleLop'}).addTo(map);
		 var marker = L.circleMarker(map.unproject([top,left]), {radius:3, color: 'green', fillColor: '#3F3', fillOpacity: 1, weight: 3}).addTo(map);

		 marker.on('click',function(){
			coordinatesContent = getMessage(top+';'+left);
			if(coordinatesContent.length == 1)
			{
				if(coordinatesContent[0]['N0'] == 'Pianeta')
				marker.bindPopup('<div style="width: 450px;"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/'+coordinatesContent[0]['N3']+'\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/'+coordinatesContent[0]['N2']+'\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b1.png"/></td><td class="bdr">'+coordinatesContent[0]['N1']+' '+((coordinatesContent[0]['N4'] != '') ? '<span class="iGray">('+coordinatesContent[0]['N4']+')</span>' : '')+' '+((coordinatesContent[0]['N13'] != 0) ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\''+coordinatesContent[0]['N13']+'\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : '')+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b3.png"/></td><td class="bdr">'+coordinatesContent[0]['N5']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b5.png"/></td><td class="bdr">'+coordinatesContent[0]['N6']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b6.png"/></td><td class="bdr">'+coordinatesContent[0]['N12']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b7.png"/></td><td class="bdr">'+coordinatesContent[0]['N8']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b8.png"/></td><td class="bdr">'+coordinatesContent[0]['N9']+' ore</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b9.png"/></td><td class="bdr">'+coordinatesContent[0]['N7']+' </td></tr></table></div></div>'); 
				else
				marker.bindPopup('<div style="width: 450px;"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/'+coordinatesContent[0]['N3']+'\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/'+coordinatesContent[0]['N2']+'\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a1.png"/></td><td class="bdr">'+coordinatesContent[0]['N1']+' '+((coordinatesContent[0]['N13'] != 0) ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\''+coordinatesContent[0]['N13']+'\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : '')+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a2.png"/></td><td class="bdr">'+coordinatesContent[0]['N12']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a3.png"/></td><td class="bdr">'+coordinatesContent[0]['N5']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a4.png"/></td><td class="bdr"><img src="TEMPLATES/img/ranks/'+coordinatesContent[0]['N10b']+'.png"></img> <a class="iLink" href="javascript:schedaPOpen('+coordinatesContent[0]['N10e']+');"> '+coordinatesContent[0]['N10c']+' '+coordinatesContent[0]['N10d']+'</a></td></tr>'+((coordinatesContent[0]['N11'] != '') ? '<tr><td class="borderOne"><img src="TEMPLATES/img/charts/a5a.png"/></td><td class="bdr">'+coordinatesContent[0]['N11']+'</td></tr>' : '')+'<tr><td class="borderOne"><img src="TEMPLATES/img/charts/a6.png"/></td><td class="bdr">'+coordinatesContent[0]['N8']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a7.png"/></td><td class="bdr">'+coordinatesContent[0]['N7']+' </td></tr></table></div></div>');
			}
			else
			{
				arelem ='';
				i=0;
				arelemIndex = '<p class="title" style="text-align:center;">Unità alla Locazione:</p><br/>';
				$(coordinatesContent).each(function(k,e){
					if(e['N0'] == 'Pianeta')
					{
						arelem+='<div style="display:none;" id="dlem_'+i+'"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/'+e['N3']+'\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/'+e['N2']+'\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b1.png"/></td><td class="bdr">'+e['N1']+' '+((e['N4'] != '') ? '<span class="iGray">('+e['N4']+')</span>' : '')+' '+((e['N13'] != 0) ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\''+e['N13']+'\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : '')+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b3.png"/></td><td class="bdr">'+e['N5']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b5.png"/></td><td class="bdr">'+e['N6']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b6.png"/></td><td class="bdr">'+e['N12']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b7.png"/></td><td class="bdr">'+e['N8']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b8.png"/></td><td class="bdr">'+e['N9']+' ore</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/b9.png"/></td><td class="bdr">'+e['N7']+' </td></tr></table></div></div>';
					}
					else
					{
						arelem+='<div style="display:none;" id="dlem_'+i+'"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/'+e['N3']+'\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/'+e['N2']+'\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a1.png"/></td><td class="bdr">'+e['N1']+' '+((e['N13'] != 0) ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\''+e['N13']+'\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : '')+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a2.png"/></td><td class="bdr">'+e['N12']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a3.png"/></td><td class="bdr">'+e['N5']+'</td></tr><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a4.png"/></td><td class="bdr"><img src="TEMPLATES/img/ranks/'+e['N10b']+'.png"></img> <a class="iLink" href="javascript:schedaPOpen('+e['N10e']+');"> '+e['N10c']+' '+e['N10d']+'</a></td></tr>'+((e['N11'] != '') ? '<tr><td class="borderOne"><img src="TEMPLATES/img/charts/a5a.png"/></td><td class="bdr">'+e['N11']+'</td></tr>' : '')+'<tr><td class="borderOne"><img src="TEMPLATES/img/charts/a6.png"/></td><td class="bdr">'+e['N8']+'</td><tr><td class="borderOne"><img src="TEMPLATES/img/charts/a7.png"/></td><td class="bdr">'+e['N7']+' </td></tr></table></div></div>';
					}
					
					arelemIndex = arelemIndex+'<div class="iElement" onclick="deShowMultiple(\'#dlem_'+i+'\');" style="background-image:url(\'TEMPLATES/img/logo/'+e['N3']+'\');" title="'+e['N1']+'"></div>';
					i++;
				});
			marker.bindPopup('<div style="width: 450px;"><div id="arelmDiv">'+arelemIndex+'</div>'+arelem+'</div>');
			}
		 });
		} 
		
		function deShowMultiple(ida)
		{
			jQuery('#arelmDiv').fadeOut(300,function(){jQuery(ida).fadeIn(150);});
		}
		
		function addMarkersFromJSon(jsonO)
		{  
			//$.each(jQuery.parseJSON(jsonO), function( index, value ) {$.each(value,function(ind,val){alert(ind+' '+val);}); });	
			$.each(jQuery.parseJSON(jsonO), function( index, value ) {addMarker(value[0],value[1]);});	
		}