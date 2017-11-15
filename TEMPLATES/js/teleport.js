/*
* Original Author: Moreno Zolfo, Star Trek Federation GdR. All rights reserved.
*/ 
		function selectTeleportable(ider)
		{
			if(!jQuery('#'+ider).hasClass('littleUserSign_active')) jQuery('#'+ider).addClass('littleUserSign_active');
			else jQuery('#'+ider).removeClass('littleUserSign_active')
		}
		function showTeleportChanger()
		{
			jQuery('#locationTeleportNotifierP').fadeOut(300,function(){jQuery('#teleportChangerP').fadeIn(100);});
		}
		
		function sendayaTeleportFromChange()
		{
			jQuery('#locationTeleportNotifier').html(jQuery('#teleportChanger option:selected').text());
			jQuery.post('ajax_usersintogetter.php', {amb: jQuery('#teleportChanger').prop('value')}, teleportFromUpdater, 'json');
		}
		
		function teleportFromUpdater(data)
		{
			var stringer = '';
			
			for (var pg in data)
			{
				//alert(data[pg]['pgUser']);
				var pgUser=data[pg]['pgID'];
				
				 stringer+= "<div class=\"littleUserSign\"  title=\""+data[pg]['pgUser']+"\" id=\"tele_"+pgUser+"\" style=\"background-image:url('"+data[pg]['pgAvatar']+"')\" onclick=\"javascript:selectTeleportable('tele_"+pgUser+"');\" ></div>";
			}
			 jQuery('.littleUserSign').fadeOut(100,function(){
			
			 jQuery('#userSigner').html(stringer);
			 jQuery('#teleportChangerP').fadeOut(300,function(){jQuery('#locationTeleportNotifierP').fadeIn(100);});
			
			 });
			
		}
		
		function doTeleport()
		{
			jQuery('.littleUserSign_active').each(function(e){
			
			var ider = jQuery(this).prop('id').substr(5);

			jQuery.post('ajax_send_teleport.php', {amb: jQuery('#ambientID').prop('value'), destination:jQuery('#teleportTo').prop('value'),  sended: ider}, teleportFromUpdater, 'json');
			
			});
			
			jQuery('#teleportPanel').fadeOut(300);
		}