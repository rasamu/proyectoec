$(document).ready(function() {
			/* Apply fancybox to multiple items */
			$("a.grouped_elements").fancybox({
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'elastic',
				'speedIn'		:	600, 
				'speedOut'		:	200, 
				'overlayShow'	:	false,
			});
		
			var idtextareaContacto="form_contacto_mensaje";
			var idcontadorContacto="contadorContacto";
			var idtextareaDenuncia="form_denuncia_denuncia";
			var idcontadorDenuncia="contadorDenuncia";
			var max="255";
			
			$("#"+idtextareaContacto).keyup(function(){
				updateContadorTa(idtextareaContacto, idcontadorContacto,max);
			});
			$("#"+idtextareaContacto).keydown(function(){
				updateContadorTa(idtextareaContacto, idcontadorContacto,max);
			});
			$("#"+idtextareaContacto).change(function(){
				updateContadorTa(idtextareaContacto, idcontadorContacto,max);
			});
			$("#"+idtextareaDenuncia).keyup(function(){
				updateContadorTa(idtextareaDenuncia, idcontadorDenuncia,max);
			});
			$("#"+idtextareaDenuncia).keydown(function(){
				updateContadorTa(idtextareaDenuncia, idcontadorDenuncia,max);
			});
			$("#"+idtextareaDenuncia).change(function(){
				updateContadorTa(idtextareaDenuncia, idcontadorDenuncia,max);
			});
			
   				$("#form_contacto").submit(function(){
   				   $('#enviar_contacto').hide();
   					$('#loading_contacto').show();
        			   var url = $("#form_contacto").attr("action");
       				$.ajax({
       					url: url,
       					data: {
           					Mensaje:$("#form_contacto_mensaje").val(),
           					Email:$("#form_contacto_email").val(),
           					Nombre:$("#form_contacto_nombre").val(),
           					idAnuncio: id_anuncio,
       					},
       					type: "POST",
       					cache: false,
       					success:function(data){
            				if(data.responseCode==200 ){  
            					document.getElementById('form_contacto_mensaje').value="";
            					document.getElementById('form_contacto_email').value=""; 
            					document.getElementById('form_contacto_nombre').value=""; 
            					$('#loading_contacto').hide();
            					$('#enviar_contacto').show();
            			   	$("#form_contactar").hide();  
                				$('#output_contacto').html(data.greeting).addClass("mensaje").css("color","green").css("background","#DFF0D8").show();
            				}else if(data.responseCode==400){//bad request
            					$('#loading_contacto').hide();
            					$('#enviar_contacto').show();
               				$('#output_contacto').html(data.greeting).addClass("mensaje").css("color","red").css("background","#F2DEDE").show();
           					}else{           					
              					alert("An unexpeded error occured.");
              					$('#loading_contacto').hide();
            					$('#enviar_contacto').show();
           					}
       					}
       				});
      				return false;
  		 			});
  		 			
   				$("#form_denuncia").submit(function(){
   				   $('#enviar_denuncia').hide();
   					$('#loading_denuncia').show();
        			   var url = $("#form_denuncia").attr("action");
       				$.ajax({
       					url: url,
       					data: {
           					Denuncia:$("#form_denuncia_denuncia").val(),
           					idAnuncio: id_anuncio,
       					},
       					type: "POST",
       					cache: false,
       					success:function(data){
            				if(data.responseCode==200 ){  
            					document.getElementById('form_denuncia_denuncia').value="";
            					$('#loading_denuncia').hide();
            					$('#enviar_denuncia').show();
            			   	$("#form_denunciar").hide();  
                				$('#output_denuncia').html(data.greeting).addClass("mensaje").css("color","green").css("background","#DFF0D8").show();
            				}else if(data.responseCode==400){//bad request
            					$('#loading_denuncia').hide();
            					$('#enviar_denuncia').show();
               				$('#output_denuncia').html(data.greeting).addClass("mensaje").css("color","red").css("background","#F2DEDE").show();
           					}else{           					
              					alert("An unexpeded error occured.");
              					$('#loading_denuncia').hide();
            					$('#enviar_denuncia').show();
           					}
       					}
       				});
      				return false;
  		 			});
		});
		
		var id_anuncio ="";
		
		function updateContadorTa(idtextarea, idcontador,max){
			var contador = $("#"+idcontador);
			var ta =     $("#"+idtextarea);
			contador.html("0/"+max);
 
			contador.html(ta.val().length+"/"+max);
			if(parseInt(ta.val().length)>max){
				ta.val(ta.val().substring(0,max-1));
				contador.html(max+"/"+max);
			}
		}
		
		function showDialogContactar(anuncio) {
				id_anuncio=anuncio;
				updateContadorTa("form_contacto_mensaje", "contadorContacto", 255);
				$("#form_contactar").show();
				$("#output_contacto").hide();
				$("#contacto").dialog({ <!--  ------> muestra la ventana  -->
            	width: "auto",  <!-- -------------> ancho de la ventana -->
            	height: "auto",<!--  -------------> altura de la ventana -->
            	show: "scale",
            	hide: "scale",
            	resizable: "false", <!-- ------> fija o redimensionable si ponemos este valor a "true" -->
            	position: "center",<!--  ------> posicion de la ventana en la pantalla (left, top, right...) -->
            	modal: "true", <!-- ------------> si esta en true bloquea el contenido de la web mientras la ventana esta activa (muy elegante) -->
        			title: myContactar,
        			close: function(event, ui){ 
        				 document.getElementById('form_contacto_mensaje').value=""; 
        				 document.getElementById('form_contacto_email').value="";
						 document.getElementById('form_contacto_nombre').value="";
        				 $("#output_contacto").removeAttr("style").removeClass("mensaje").text("");
        			}
        		});
		}
		
		function showDialogDenunciar(anuncio) {
				id_anuncio=anuncio;				
				updateContadorTa("form_denuncia_denuncia", "contadorDenuncia", 255);
				$("#form_denunciar").show();
				$("#output_denuncia").hide();
				$("#denuncia").dialog({ <!--  ------> muestra la ventana  -->
            	width: "auto",  <!-- -------------> ancho de la ventana -->
            	height: "auto",<!--  -------------> altura de la ventana -->
            	show: "scale",
            	hide: "scale",
            	resizable: "false", <!-- ------> fija o redimensionable si ponemos este valor a "true" -->
            	position: "center",<!--  ------> posicion de la ventana en la pantalla (left, top, right...) -->
            	modal: "true", <!-- ------------> si esta en true bloquea el contenido de la web mientras la ventana esta activa (muy elegante) -->
        			title: myDenunciar,
        			close: function(event, ui){ 
        				 document.getElementById('form_denuncia_denuncia').value=""; 
        				 $("#output_denuncia").removeAttr("style").removeClass("mensaje").text("");
        			}
        		});
		}