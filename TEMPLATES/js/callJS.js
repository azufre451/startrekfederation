function sX(e)
		{
			var color; 
			
			var lent = jQuery('#testoComm').val().length; 
			if(lent > 240) color = 0;
			else if(lent < 50) color = 24; 
			else color = 80;
			
			
			jQuery('#counter').html(lent);
			jQuery('#counter').css('color','hsl('+color+',93%,50%)');
} 