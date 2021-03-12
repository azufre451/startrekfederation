function jmp(to)
	{
		if(to ==  'newPadd')
			jQuery('#authBlock').fadeOut(300);
		else
			jQuery('#authBlock').show();
		
		jQuery('.padderMain').fadeOut(100);
		jQuery('#'+to).fadeIn(400);
	}
	
	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}

	jQuery(function(){

		
	jQuery('.tooltip, #intestLeft a, .BBbuttonList input[type=button]').tooltip({
      		content: function () {
          	return jQuery(this).prop('title');
      	}
  		});

		jQuery( "#users")
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function( request, response ) {
				$.getJSON( "ajax_userGetter.php?extGropus=do", {
					term: extractLast( request.term )
				}, response );
			},
			search: function()
			{
				// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 1 ) {
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
		function togglePaddIndicator(ida)
		{if(jQuery(ida).hasClass('selectedPadd')) jQuery(ida).removeClass('selectedPadd').addClass('unselectedPadd'); else jQuery(ida).removeClass('unselectedPadd').addClass('selectedPadd');}
		
		function deleteSelected()
		{
		var padds = new Array(); 
		var i = 0;
		jQuery('.selectedPadd').each(function(e){
			padds[i++] = jQuery(this).prop('id');
		});
		
		jQuery.post('ajax_padds.php?action=deleteMany', {padds: padds}, function(data){
			if(data['OK'])
				jQuery('.selectedPadd').each(function(e){
					jQuery('#padd_'+jQuery(this).prop('id')).fadeOut(350);
				});
		}, 'json');
		}
		
		function selectAll(mode)
		{
			var aggregator;
			if(jQuery('#incoming').css('display') == 'block') aggregator = '.incoming';
			if(jQuery('#outgoing').css('display') == 'block') aggregator = '.outgoing';
			if(mode==0) jQuery(aggregator+' > .unselectedPadd').removeClass('unselectedPadd').addClass('selectedPadd');
			if(mode==1) jQuery(aggregator+' > .selectedPadd').removeClass('selectedPadd').addClass('unselectedPadd');
		}
		
		function selectRead()
		{
			var aggregator;
			if(jQuery('#incoming').css('display') == 'block') aggregator = '.incoming';
			if(jQuery('#outgoing').css('display') == 'block') aggregator = '.outgoing';
			jQuery(aggregator+'.incoming.read > .unselectedPadd').removeClass('unselectedPadd').addClass('selectedPadd');
		}