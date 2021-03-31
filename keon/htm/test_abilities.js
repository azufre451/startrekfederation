 jQuery(function() { jQuery('.tooltip, img').tooltip({
          content: function () {
              return jQuery(this).prop('title');
          }
      });

  
	 jQuery( "#inputSpecc" ).autocomplete({
	      source: "test_abilities.php?abSearch=do",
	      minLength: 2,
	      select: function( event, ui ) {

	      	options='';
	      	sta= (ui.item.abClass == 'ABIL' ? -4 : 0);
	      	for(k=sta;k < 15; k++){
	      		sel='';
	      		if(k==0) sel='selected="selected"'

	      		options+='<option value="'+k+'" '+sel+'>'+k+'</option>';
	      	}
	        
	        jQuery('#row_tinser_'+ui.item.abID).html('');
	        jQuery('#fifi').append('<tr id="row_tinser_'+ui.item.abID+'"><td style="width:50px;"><img src="../TEMPLATES/img/interface/personnelInterface/abilita/'+ui.item.abImage+'" title="'+ui.item.abDescription+'" /> </td><td>'+ui.item.value+'</td><td><select class="neon abex" data-abil="'+ui.item.abID+'" type="text">'+options+'</select> <a href="javascript:void(0);" onclick="cuva(this);" class="interfaceLinkRed">X</a></td></tr>');
	        jQuery('#row_tinser_'+ui.item.abID+' img').tooltip();
	        ui.item.value = "";  
	      }
     });

	});

 	function cuva(a){
 		console.log(a);
 		jQuery(a).parent().parent().html('');
 	}

	 function sendCar(){

	 	apex=[];
	 	jQuery('.abex').each(function(){
	 		apex.push([jQuery(this).attr('data-abil'),jQuery(this).val()])
	 		
	 	});


	 	jQuery.ajax(
			{
			url: 'test_abilities.php?ajax=do',
			data:{payload:apex},
			success:function(e){ jQuery('#upter').html(e['cost'] + ' UP'); jQuery('#stringer').val(e['txt']); jQuery('#stringer').fadeIn(); },
			type: 'POST',
			dataType : 'json',
			timeout:3000
		});
	 }

