function cdbOpenToTopic(ida){window.open ('cdb.php?topic='+ida,'cdb', config='scrollbars=yes,status=no,location=no,resizable=no,resizale=0,top=0,left=100,width=1000,height=660');}
	

function editStory(storyID)
{
   jQuery.ajax(
          {
            url: 'scheda.php?s=getStory',
            data: {storyID:storyID},
            type: 'POST',
            dataType : 'json',
            success : function(e){

              jQuery('#story_rank option').attr('selected',false);
              jQuery('#story_rank option[value="'+e['prio']+'"]').attr('selected','selected');
              

              jQuery('#story_dataG option').attr('selected',false);
              jQuery('#story_dataG option[value="'+e['day']+'"]').attr('selected','selected');
              

              jQuery('#story_dataM option').attr('selected',false);
              jQuery('#story_dataM option[value="'+e['month']+'"]').attr('selected','selected');
              

              jQuery('#story_dataA option').attr('selected',false);
              jQuery('#story_dataA option[value="'+e['year']+'"]').attr('selected','selected');
              
              jQuery('#story_what').attr('value',e['what']);
              jQuery('#where').attr('value',e['wherer']);

              jQuery('#storyEdit').attr('value',e['storyID']);
              jQuery('#rankAdder').fadeIn();

            },
            timeout:4500
          }
  );
}

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

			
	jQuery('.tooltipClass').tooltip({

    content: function () {
        return '<div><p>Altri incarichi associati:</p>'+this.getAttribute("title")+'</div>';
    }
  });	

  jQuery('.tooltip').tooltip({

    content: function () {
        return '<div>'+this.getAttribute("title")+'</div>';
    }
  });

 
  });
	
	function getOthers(me,unit,year,pgid){
 
    utp='';
    
    eu='';
    year.split('#').forEach( function(e) {u=e.split('-')[0]; if (u!='') eu+=u+'-';   }); 
    eu=eu.substring(0,eu.length-1);
    if (year.split('#').length > 1) jQuery('#year_specifier').html('nel periodo '+eu);
    else jQuery('#year_specifier').html('nel '+eu);

    
     jQuery.ajax(
          {
            url: 'ajax_getSSTO.php',
            data: {a_year:year, a_unit: unit,a_pgid:pgid},
            type: 'POST',
            dataType : 'json',
            timeout:4500
          }).then(function( e ) {

              jQuery.each(e, function(q,v) {
                
                pgUser = e[q]['pgUser'];
                ordinaryUniform = e[q]['ordinaryUniform'];
                pgID = e[q]['pgID'];
                what = e[q]['what'];
                utp+='<p style="margin:0px;"><img src="TEMPLATES/img/ranks/'+ordinaryUniform + '.png"></img>  <a href="javascript:void(0);" class="interfaceLink" onclick="schedaPOpen('+pgID+')">' + pgUser + '</a> - <span style="color:#CCC; font-size:11px; text-transform:uppercase">'+what+'</span> </p>';

              });

              if (utp == '')
                utp='Nessuno :-(';

              jQuery('#ntpa_show').html(utp);

              jQuery('#ntpa_show_box').fadeIn();
          });      
    }