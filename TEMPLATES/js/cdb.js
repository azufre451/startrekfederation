function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}
		
		function isNumberKey(evt)
		{

         var charCode = (evt.which) ? evt.which : event.keyCode

         if ((charCode < 58 && charCode > 47) || (charCode < 106 && charCode > 95) || charCode == 8)
            return true;

         return false;
		 
		 }

		function showlink(ida)
		{

			jQuery('#dialogLinker').attr('value','[POST]'+ida+'[/POST]');
			 $( "#dialog-message" ).dialog({
      modal: true,
 

        buttons: {
        "Copia": function(){ jQuery('#dialogLinker').select(); document.execCommand("copy"); jQuery('#dialog-message').dialog( "close" );}
      }

    });
		}
 
 
		jQuery(function(){
			jQuery('.tooltip').tooltip({
          content: function () {
              return $(this).prop('title');
          }
      });


			jQuery( "#users")
			// don't navigate away from the field on tab when selecting an item

			.autocomplete({
				source: function( request, response ) {
					$.getJSON( "ajax_userGetter.php?wpng=1", {
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
			
			 $( "#usersMaster" ).autocomplete({
			 source: "ajax_userGetter.php?epng_only=1",
			 minLength: 2,
			 });
			 
			})
			
			function goTrasncript(rap,stringUse)
			{
				if (jQuery('#format1').prop('checked')) formatID = 1;
				if (jQuery('#format2').prop('checked')) formatID = 2;
				if (jQuery('#format3').prop('checked')) formatID = 3;
				if (jQuery('#format4').prop('checked')) formatID = 4;
				if (jQuery('#format5').prop('checked')) formatID = 5;
				
				jQuery.post('ajax_getCDBFormats.php', {stringer: stringUse, format: formatID, rapType: rap}, settercdb, 'json');
				
				if (jQuery('#no-notify').prop('checked'))
				{
				jQuery('#pepoleList').attr('value',stringUse);
				jQuery('#peopleListMirror').html(stringUse);
				jQuery('#peopleListMirrorContain').fadeIn();
				}
				else{
					jQuery('#pepoleList').attr('value','');
					jQuery('#peopleListMirror').html('');
					jQuery('#peopleListMirrorContain').fadeOut();
				}


			}
			
			function goStardate(d,m,y,h,i,s)
			{
				jQuery.post('ajax_stardateGetter.php', {day: d, mon: m, yea:y, hou:h,min:i}, settercdb, 'json');
			}
			
			
			function settercdb(data)
			{
				
				var box = jQuery("#postContent");
				box.val(box.val() + data);
				jQuery('#pstAdva').fadeOut(50);
			}