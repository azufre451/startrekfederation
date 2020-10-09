jQuery(document).ready(function(){
	if ( jQuery('#regform').length ) 
		jQuery('#regform')[0].reset();
	
	jQuery(".carousel").dualSlider({
		auto:true,
		autoDelay:6000,
		easingCarousel: "swing",
		easingDetails: "easeOutBack",
		durationCarousel: 1000,
		durationDetails: 600
	});	

	jQuery('.tooltip').tooltip({
		content: function () {
			return jQuery(this).prop('title');
		}
	});
}); 


function setViewer(vare)
{ 
	if(jQuery('#'+vare).length)
		jQuery('.sliding').fadeOut(120).promise().done( function(){jQuery('#'+vare).fadeIn(120);});
	else window.location.href = 'index.php?mod='+vare;
	
}

function fullScreen(){
	var larg_schermo = screen.availWidth - 10;
	var altez_schermo = screen.availHeight - 30;
	window.open("login.php", "fed_main", "width=" + larg_schermo + ",height=" + altez_schermo + ",top=0,left=0,location=no,menubar=n,resizable=yes,scrollbar=yes");
}

function fullScreenTest(){
	var larg_schermo = screen.availWidth - 10;
	var altez_schermo = screen.availHeight - 30;
	window.open("login.php?test=true", "fed_main", "width=" + larg_schermo + ",height=" + altez_schermo + ",top=0,left=0,location=no,menubar=n,resizable=yes,scrollbar=yes");
}

function toggleVisit(ida)
{
	var tit = ''; 
	var hti = jQuery('#toggle_'+ida).html();  
	if(hti.indexOf('ESPANDI') > 0) tit = '[RIDUCI]';
	if(hti.indexOf('RIDUCI') > 0) tit = '[ESPANDI]';  
	
	jQuery('#navisi_'+ida).toggle();
	jQuery('#visi_'+ida).toggle('fade',{},100,function(){jQuery('#toggle_'+ida).html(tit);});
	
}

				function selectSex(ida,sexo)
				{
					jQuery('.selectable.sex').removeClass('active');
					jQuery(ida).addClass('active');
					
					if (sexo == 'm'){ jQuery('.race_fem, .race_place').hide(); jQuery('.race_mas').show();}
					if (sexo == 'f'){jQuery('.race_mas, .race_place').hide(); jQuery('.race_fem').show();}  
					
					jQuery('#select_sesso').val(sexo);
				}
				
				function selectDivision(ida,divi){

					if (jQuery(ida).hasClass('selectable'))
					{
						jQuery('.selectable.division').removeClass('active');
						jQuery(ida).addClass('active'); 
						jQuery('.divihandler').hide();
						jQuery('#divi_'+divi).show();
						
						var ari = { "sfC":'nfCduty_small', "sfT":'nfTduty_small', "sfI":'nfOduty_small', "sfS":'nfSduty_small', "sfN":'nfNduty_small', "bar":'civbarm',  "ing":'civingm',  "sci":'civscim',  "com":'nfFLY_small','civ':'civpolm','jou':'civgiom'};
						 
						jQuery('#select_grado').val(jQuery('#divi_'+divi).val());
						jQuery('#select_gradoimage').val(jQuery('#divi_'+divi).val().split('_')[1]);
						
						jQuery('#select_uniform').val(ari[divi]);
						jQuery('#select_sezione').val(divi);
					}
					else{
						alert('Questa sezione al momento non è selezionabile in fase di iscrizione. Puoi comunque contattare lo staff di gioco una volta iscritto per valutare la possibilità di giocare in questo ruolo!')
					}
				}
				
				function selectRace(ida,race)
				{
					jQuery('.selectable.race').removeClass('active');
					jQuery(ida).addClass('active');
					jQuery('#select_razza').val(race);
					
				}
				
				function setRole(ida){
					jQuery('#select_grado').val(ida);
					 
					 jQuery('#select_gradoimage').val(ida.split('_')[1]);
				}
				 
				function regPage(ida){
					
					if(ida=="2") {   
					
					
					if(jQuery('#select_email').val().length > 0  && jQuery('#select_nome').val().length > 0 && jQuery('#select_cognome').val().length > 0 && jQuery('.selectable.sex.active').length > 0 && jQuery('.selectable.division.active').length > 0)
					{

					

					jQuery('.species_spec').hide();
					jQuery('.sections_spec').hide();
					jQuery('#sp_'+jQuery('#select_razza').val()).show();
					jQuery('#se_'+jQuery('#select_sezione').val()).show();
					jQuery('#mirror_name').html(jQuery('#select_nome').val()+' '+jQuery('#select_cognome').val())
					jQuery('#mirror_uniform').attr('src','TEMPLATES/img/uniformi/'+jQuery('#select_uniform').val()+'.png');
					jQuery('#mirror_razza').attr('src','TEMPLATES/img/specie/'+jQuery('#select_razza').val()+'_'+jQuery('#select_sesso').val()+'.png');
					jQuery('#mirror_razza_t').html(jQuery('#select_razza').val());
					if (jQuery('#select_gradoimage').val() != '') jQuery('#mirror_grado').attr('src','https://oscar.stfederation.it/SigmaSys/PIPSN/'+jQuery('#select_gradoimage').val()+'.png');
					else jQuery('#mirror_grado').hide();
					
					jQuery('#REG #page1, #REG #page3').fadeOut(200).promise().done( function(){jQuery('#REG #page2, #REG #bpage2').fadeIn(100);});
					
					
					}
					else {

						alert("Compila tutti i campi prima di passare al passo successivo");
					}
					}
					else if(ida=="1") {jQuery('#REG #page2, #REG #page3').fadeOut(200).promise().done( function(){jQuery('#REG #page1, #REG #bpage2').fadeIn(100);});}
					else if(ida=="3") {jQuery('#REG #page1, #REG #bpage2').fadeOut(200).promise().done( function(){jQuery('#REG #page3').fadeIn(100);});}
					
					
				}
				
				function verify_consistency_car(){
					tplo=0; 
					jQuery(".valer").each(function(i) {  k=parseInt(jQuery(this).val()); tplo+=k;});

					if(tplo >= 23){alert('Attenzione: i valori delle caratteristiche non possono superare un totale di 23, e la singola caratteristica non può superare il valore di 8!'); return false;}
					return true;

				}


				function char_addNextLVL(ida)
				{
					if (verify_consistency_car()){

					curVal = Number(jQuery('#val_'+ida).val());
					if (curVal < 15)
					{ 
					target_15 = curVal + 1;
					target = Number((target_15 / 15 *100) | 0); 
					if(target_15 > 8){
						alert('Attenzione: La singola caratteristica non può superare il valore di 8!'); 
						return false;
					}

					jQuery('#val_'+ida).attr('value',target_15);
					jQuery('#'+ida+'_marker_current').html(String(target_15));
					if (!jQuery('#'+ida+'_marker_current').hasClass('act'))
						jQuery('#'+ida+'_marker_current').addClass('act');
					jQuery('#bar_'+ida+' span').css('width',target+'%');
					}

					}

					return false;
				}
				
				function char_subPrevLVL(ida)
				{
					curVal = Number(jQuery('#val_'+ida).val());
					if (curVal > 0)
					{
					target_15 = curVal - 1;
					target = Number((target_15 / 15 *100) | 0); 

					
					jQuery('#val_'+ida).attr('value',target_15);
					jQuery('#'+ida+'_marker_current').html(String(target_15));
					if (!jQuery('#'+ida+'_marker_current').hasClass('act'))
						jQuery('#'+ida+'_marker_current').addClass('act');
					jQuery('#bar_'+ida+' span').css('width',target+'%');
					}
					return false;
				}

				function doRegister(){

					if(!jQuery('#terms_conditions_check').prop('checked'))
					{
						alert("Attenzione: devi accettare i termini e condizioni del sito prima di registrarti!");
					}

					else if (parseInt(jQuery('#val_ht').val())+parseInt(jQuery('#val_dx').val())+parseInt(jQuery('#val_iq').val())+parseInt(jQuery('#val_pe').val())+parseInt(jQuery('#val_wp').val()) != 23){

							alert("Attenzione: la somma delle caratteristiche deve essere pari a 23!");
					}
					else{
						
						jQuery.ajax({ type: "POST",async:false, url: 'service.php?registerUser=do',data: 
							{

							select_nome:jQuery('#select_nome').val(),
							select_cognome:jQuery('#select_cognome').val(),
							select_razza:jQuery('#select_razza').val(),
							select_email:jQuery('#select_email').val(),
							select_sesso : jQuery('#select_sesso').val(),
							pgAuth: jQuery('#pgAuth').val(),
							select_grado: jQuery('#select_grado').val(),
							select_ht: jQuery('#val_ht').val(),
							select_dx: jQuery('#val_dx').val(),
							select_iq: jQuery('#val_iq').val(),
							select_pe: jQuery('#val_pe').val(),
							select_wp: jQuery('#val_wp').val()
							},
						success: regNoti,
						dataType : 'json'
						});
					}
				}

				function regNoti(eer){ 
							if (eer['err'])
							{
								if (eer['err'] == "IE") 
								alert("Attenzione: si è verificato un errore di inserimento. Controlla di aver inserito i parametri in modo corretto, e ricorda che la somma dei singoli punteggi delle abilità deve essere pari a 23. In caso di problemi contattaci a staff@stfederation.it");
								if (eer['err'] == "UE") alert("Attenzione: si è verificato un errore di inserimento. Questo cognome personaggio risulta essere già in uso");
								if (eer['err'] == "ME") alert("Attenzione: si è verificato un errore di inserimento. Questo indirizzo email risulta essere già in uso");
							}
							else{
								alert("Congratulazioni! Il Personaggio è stato registrato. Riceverai una mail di conferma con le credenziali di accesso!");
								window.location.href='index.php';
								

							}

							
						}