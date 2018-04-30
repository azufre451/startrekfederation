function cdbOpenToTopic(ida){window.open ('cdb.php?topic='+ida,'cdb', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1000,height=660');}
	
		jQuery(function(){			
	
			
			// $( "#where" ).autocomplete({
      // minLength: 2,
      // source: "ajax_searchShip.php",
      // focus: function( event, ui ) {
        // $( "#where" ).val( ui.item.label );
        // return false;
      // },
      // select: function( event, ui ) {
        // $( "#where" ).val( ui.item.label );
        // $( "#where-id" ).val( ui.item.value );
        // $( "#where-description" ).html( ui.item.desc ); 
 
        // return false;
      // }
    // })	
	// .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
      // return $( "<li>" )
        // .append( "<a>" + item.label + "<br />" + item.desc + "</a>" )
        // .appendTo( ul );
    // };
	 
 
    $( "#where" ).autocomplete({
      minLength: 1,
      source: 'ajax_searchShip.php',
      focus: function( event, ui ) {
        $( "#where" ).val( ui.item.label );
        return false;
      },
      select: function( event, ui ) {
        $( "#where" ).val( ui.item.label );
        /*$( "#where-icon" ).attr( "src", ui.item.icon );*/
 
        return false;
      }
    })
    .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<a>" + item.label + "<br />&nbsp;&nbsp;&nbsp;&nbsp;" + item.desc + "</a>" )
        .appendTo( ul );
    };		
			
			
	});
	
	
		