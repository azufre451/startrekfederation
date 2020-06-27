jQuery(function(){ 
			$( "#searcher" ).autocomplete({
			source: "replicator.php",
			minLength: 2,
			select: function(event, ui) {
			if(ui.item){
				doFood(ui.item.value);
			} 
			}
			}); 
			
			$( "#prop_foodSpecie" ).autocomplete({
			source: "replicator.php?lookSpecie=do",
			minLength: 2 
			}); 
			
			
			doFood(jQuery('#customFood').val());
		});
		function doFood(ida)
		{
			
			jQuery.ajax(
			{
			url: "replicator.php",
			data:{ajaxCall: ida},
			type: 'GET',
			dataType : 'json',
			success : function(e){
					(new Image()).src = e['foodImage'];
					jQuery('#foodOrdering').prop('value',e['foodID']);
					if(jQuery('#upperIni').css('display') != 'none')
					{
						updateFood(e['foodName'],e['foodDescription'],e['foodImage']);
						jQuery('#upperIni').fadeOut(100,function(){
								jQuery('#upperPost').fadeIn(); 
								
							});
					}
					else if (jQuery('#upperPost').css('display') != 'none')
					{
						jQuery('#upperPost').fadeOut(100,function(){
								updateFood(e['foodName'],e['foodDescription'],e['foodImage']);
								jQuery('#upperPost').fadeIn();
							});
					}
				}
			});  
			
		} 

		function sendElimination(foodID)
		{
			jQuery.ajax(
			{

			url: "replicator.php?approval=true&s=delete&foodID="+foodID,
			data:{reason: prompt("Ragione per il respingimento")},
			type: 'POST',
			success: function(e){jQuery('#trelem_'+foodID).fadeOut(200)},
			dataType : 'json' 
			}
			); 


			
		}
		function updateFood(name,descript,image)
		{
			
			jQuery('#foodImage').attr('src',image);
			jQuery('#foodTitle').prop('value',name);
			if(jQuery('#foodTitle').css('display') == 'none') jQuery('#foodTitle').show();
			jQuery('#foodDescript').html(descript);
		}
		
		function selecter(ida)
		{
			jQuery(ida).siblings().removeClass('active');
			jQuery(ida).addClass('active');
			
			computeFilter();
		}
		
		function computeFilter()
		{
			var selector1 = jQuery('#selector1 div.active').prop('id');
			var selector2 = jQuery('#selector2 div.active').prop('id');
			var selector3 = jQuery('#selector3 div.active').prop('id');
			
			jQuery.ajax(
			{
				url: "replicator.php",
				data:{fCall1: selector1, fCall2: selector2, fCall3: selector3},
				type: 'POST',
				dataType : 'json',
				success : function(e){
					jQuery('#species').html('');
					strr='';
					var foodsa = new Array();
					jQuery.each(e, function(key,spec){
						if (selector3=='menuspecie')
						{
							strr+='<div><a href="javascript:void(0);" onclick="javascript:toggleSpecies(\''+key+'\')" class="interfaceLinkBlue">'+key+'</a><div style="display:none; margin-left:10px;" id="'+key+'">';
						
							jQuery(spec).each(function(k,elem){
								color = (elem['foodType'] == 'A') ? '#AAA' : '#FC0';
								strr+= '<p style="margin:0px;"><span>'+elem['iconUC']+'</span> <a class="interfaceLink" href="javascript:void(0);"  onclick="javascript:doFood('+elem['foodID']+')">'+elem['foodName']+'</a></p>';
							});
						 
							strr+='</div></div>';
						}
						else jQuery(spec).each(function(k,elem){foodsa.push(elem)}); /* Will print later */						
					});
					
					if (selector3=='menuaz'){
						foodsa.sort(function(a,b){return (a['foodName'] > b['foodName']);});
						jQuery(foodsa).each(function(k,elem){
							color = (elem['foodType'] == 'A') ? '#AAA' : '#FC0';
							strr+= '<p style="margin:0px;"><span>'+elem['iconUC']+'</span> <a class="interfaceLink" href="javascript:void(0);"  onclick="javascript:doFood('+elem['foodID']+')">'+elem['foodName']+'</a></p>';	
						});
					}
					jQuery('#species').html(strr);
				}
			});
		}
		
		function toggleSpecies(ida)
		{
		jQuery('#'+ida).toggle('drop',{},100);
		}
		
		function doReplication()
		{ 
			jQuery.ajax(
			{
			url: "ajax_sendChatEvents.php",
			data:{mode: 3, food: jQuery('#foodOrdering').val(), amb: jQuery('#placeLoc').val(), label:jQuery('#foodTitle').val()},
			type: 'POST',
			success: function(e){window.close()},
			dataType : 'json' 
			});  
			
		}

function checkImage(ida)
{
	jQuery.ajax(
			{
			url: 'replicator.php?ajax_checkInage=true',
			data: {imaUrl:jQuery('#prop_foodImage').val()},
			success: function(e){if(e['OK']) jQuery('#sizLoad').prop('value','1'); else alert('La grandezza dell\'immagine non rispetta le dimensioni richieste (390x230px)')}, 
			type: 'POST',
			async:false,
			dataType : 'json'
			});
	return (jQuery('#sizLoad').val() == '1')
}