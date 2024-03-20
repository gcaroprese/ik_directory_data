<?php
/*

Template: Carga y editar Pueblos
Update: 11/08/2021
Author: Gabriel Caroprese

*/
if ( ! defined('ABSPATH')) exit('restricted access');

$cantidadListado = 30;

// Cheque datos de Paginado
if (isset($_GET["listado"])){
    // I check if value is integer to avoid errors
    if (strval($_GET["listado"]) == strval(intval($_GET["listado"])) && $_GET["listado"] > 0){
        $paginado = intval($_GET["listado"]);
    } else {
        $paginado = 1;
    }
} else {
     $paginado = 1;
}
$offset = ($paginado - 1) * $cantidadListado;


$url_registro = get_site_url().'/wp-admin/admin.php?page=ik_dirdatos_pueblos';
if (isset($_GET['estados']) || isset($_GET['paises'])){
    if (isset($_GET['paises'])){
        $paises_filtro = absint($_GET['paises']);
        $queryFiltro['paises'] = $paises_filtro;
    }
    if (isset($_GET['estados'])){
        $estados_filtro = absint($_GET['estados']);
        $queryFiltro['estados'] = $estados_filtro;
    }
    
} else {
    $queryFiltro = false;
}

//Si se hizo un submit del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['nuevo_pais']) && isset($_POST['nuevo_pueblo']) && isset($_POST['nuevo_estado'])){
        $nuevo_pais = $_POST['nuevo_pais'];
        $nuevo_pueblo = $_POST['nuevo_pueblo'];
        $nuevo_estado = $_POST['nuevo_estado'];
    
        // Creo un conteador para el foreach
        $countDato = 0;
        
        // I check if $catOrigen is array or not. If not I don't need repetitions and I do an insert once
        if (is_array($nuevo_pais) && is_array($nuevo_pueblo) && is_array($nuevo_estado)){
    
            foreach( $nuevo_pais as $pais ) {
    
                if (isset($pais) && isset($nuevo_pueblo[$countDato]) && isset($nuevo_estado[$countDato])) {
                    $pais = absint($pais);
                    $pueblo = sanitize_text_field($nuevo_pueblo[$countDato]);
					$pueblo = str_replace('\\', '', $pueblo);
                    $estado_id = absint($nuevo_estado[$countDato]);

					global $wpdb;
					$data_campo  = array (
					    'estado_id' => $estado_id,
					    'pais' => $pais,
					    'nombre' => $pueblo,
					);

					$tabla = $wpdb->prefix.'ik_dirdatos_pueblos';
					$rowResult = $wpdb->insert($tabla,  $data_campo , $format = NULL);
					
				}
    				
    			$countDato = $countDato + 1;
              
            }
        }
    }
    $result = 'Guardado';
} else {
    $result = '';
}
?>
<div id="ik_dirdatos_agregar_pueblos">
    <h1>Pueblos</h1>
    <form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_dirdatos_campos">
            <ul>
				<li>
					<select name="nuevo_pais[]" class="ik_dirdatos_paises_select"><?php echo ik_dirdatos_listar_paises(); ?></select> <select name="nuevo_estado[]" class="ik_dirdatos_estados_select"><option value="0">-</option></select> <input type="text" required name="nuevo_pueblo[]" placeholder="Pueblo" /> <a disabled href="#" class="ik_dirdatos_eliminar_campo button" style="opacity: 0">Eliminar</a>
				</li>
            </ul>
            <a href="#" class="button button-primary" id="ik_dirdatos_agregar_campos">Agregar Campos</a>
        </div>
        <input type="submit" class="button button-primary" value="Guardar" />
        <p id="ik_dato_guardado"><?php echo $result; ?></p>
    </form>
</div>
<div id ="ik_dirdatos_pueblos_existentes">
<?php
	//Listo los datos ya cargados
	$listado_pueblos = ik_dirdatos_listar_datos('pueblos', '', $cantidadListado, $offset, $queryFiltro);
	if ($listado_pueblos != false){
	    $listado_pueblos_todos = ik_dirdatos_cantidad_datos('pueblos', '', $queryFiltro);
		echo $listado_pueblos;
		echo ik_dirdatos_listado_paginas($paginado, 'ik_dirdatos_pueblos', $cantidadListado, $listado_pueblos_todos);
  
	}  else {
	    echo '<p class="search-box">
				<label class="screen-reader-text" for="tag-search-input">Buscar Registros:</label>
				<input type="search" id="tag-search-input" name="s" value="">
				<input type="submit" id="ik_dir_datos_buscar_registro" class="button" value="Buscar">
			</p>	
			<p id="ik_dirdatos_filter_box">
                <select name="ik-filtrar-paises" class="ik-filtrar-paises" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('paises', $url_registro).'
                </select>
                <select name="ik-filtrar-estados" class="ik-filtrar-estados" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('estados', $url_registro).'
                </select>
			</p>';
	}
?>
</div>
<script>
jQuery(document).ready(function ($) {
	jQuery('.ik_dirdatos_estados_select').select2();
    jQuery('.ik_dirdatos_estados_select').trigger('change');
    jQuery('#ik_dato_guardado').fadeOut(2600);	
	jQuery('select.ik_dirdatos_paises_select').val('2');
	jQuery('.ik_dirdatos_paises_select').select2();
	jQuery('select.ik_dirdatos_paises_select').trigger('change');
    jQuery('#ik_dato_guardado').fadeOut(2600);


    //Atualizo listado de Estados del campo a agregar dependiendo del pais
	function ik_dirdatos_js_agregar_estados(elemento){
        var listado = elemento.parent();
        var pais_value = elemento.val();
        if (pais_value){			
			var data = {
				action: "ik_dirdatos_ajax_get_estados_de_pais",
				"post_type": "post",
				"pais_value": pais_value,
			};  
	
			jQuery.post( ajaxurl, data, function(response) {
				if (response){
					var data = JSON.parse(response);
					listado.find('select.ik_dirdatos_estados_select').empty();
					listado.find('select.ik_dirdatos_estados_select').append(data);
					
					//Asigno valor por defecto
					var value_estado_default = listado.find('select.ik_dirdatos_estados_select option:first-child').attr('value');
					listado.find('select.ik_dirdatos_estados_select').val(value_estado_default);

					listado.find('select.ik_dirdatos_estados_select').select2();
					listado.find('select.ik_dirdatos_estados_select').trigger('change');
				}        
			});
		}		
	}
    jQuery('#ik_dirdatos_agregar_pueblos').on('change', 'select[name="nuevo_pais[]"]', function(e){
        e.preventDefault();
		ik_dirdatos_js_agregar_estados(jQuery(this));
    });   
    ik_dirdatos_js_agregar_estados(jQuery('#ik_dirdatos_agregar_pueblos select[name="nuevo_pais[]'));
	
    //Atualizo listado de Estados del capo a editar dependiendo del pais
    jQuery('#ik_dirdatos_datos_cargados').on('change', '.ik_dirdatos_paises_select_editado', function(e){
        e.preventDefault();
        var listado = jQuery(this).parent();
        var pais_value = jQuery(this).val();
        var pais_cambiado = parseInt(jQuery(this).attr('cambiado'));

        //Si es la primera vez no hago nada, ya que quiero que quede el valor previamente asignado en la base de datos
        if (pais_cambiado > 0){
                
                var data = {
        			action: "ik_dirdatos_ajax_get_estados_de_pais",
        			"post_type": "post",
        			"pais_value": pais_value,
        		};  
        
        		jQuery.post( ajaxurl, data, function(response) {
        			if (response){
        			    var data = JSON.parse(response);
        				listado.find('select[name=estado]').empty();
                        listado.find('select[name=estado]').append(data);
                        
                        //Asigno valor por defecto
                        var value_estado_default = listado.find('select[name=estado] option:first-child').attr('value');
                        listado.find('select[name=estado]').val(value_estado_default);
    
                        listado.find('select[name=estado]').select2();
                        listado.find('select[name=estado]').trigger('change');
        		    }        
                });
        } else {
            //Le agrego un valor al id de cambios
            var pais_cambiado = pais_cambiado + 1;
            jQuery(this).attr('cambiado', pais_cambiado);
        }
    });
    
    // Agregar campos
    jQuery(document).on('click', '#ik_dirdatos_agregar_campos', function(){
        jQuery('#ik_dirdatos_agregar_pueblos .ik_dirdatos_campos ul').append('<li><select name="nuevo_pais[]" class="ik_dirdatos_paises_select_agregado"><?php echo ik_dirdatos_listar_paises(); ?></select> <select name="nuevo_estado[]" class="ik_dirdatos_estados_select"><option >-</option></select> <input type="text" required name="nuevo_pueblo[]" placeholder="Pueblo" /> <a href="#" class="ik_dirdatos_eliminar_campo button">Eliminar</a></li>');
		jQuery('.ik_dirdatos_paises_select_agregado').val('Puerto Rico');
		jQuery('.ik_dirdatos_paises_select_agregado').trigger('change');
		jQuery('#ik_dirdatos_agregar_pueblos .ik_dirdatos_campos ul select').each(function(i){			
			jQuery(this).addClass('ik_dirdatos_paises_select-'+i);
			jQuery(this).removeClass('ik_dirdatos_paises_select_agregado');
			jQuery(this).select2();
		});
        return false;
    });
    
    // Eliminar campos por crear
    jQuery(document).on('click', '#ik_dirdatos_agregar_pueblos .ik_dirdatos_campos .ik_dirdatos_eliminar_campo', function(){
        jQuery(this).parent().remove();
        return false;
    });
    
    // Eliminar pueblo
    jQuery(document).on('click', '#ik_dirdatos_agregar_pueblos .ik_dirdatos_campos .ik_dirdatos_eliminar_campo_creado', function(){
        var confirmar =confirm('Confirmar eliminar pueblo ya existente.');
        var elemento_borrar = jQuery(this).parent();
        if (confirmar == true) {
         
            var iddato = parseInt(jQuery(this).attr('iddato'));
            if (iddato != 0){
                
     			var data = {
    				action: "ik_dirdatos_ajax_eliminar_pueblo",
    				"post_type": "post",
    				"iddato": iddato,
    			};  
    
        		jQuery.post( ajaxurl, data, function(response) {
        			if (response){
                        elemento_borrar.fadeOut(700);
                        elemento_borrar.remove();
        		    }        
                });
            }
        }
        return false;
    });
    
    jQuery("#ik_dirdatos_datos_cargados th .select_all").on( "click", function() {
        if (jQuery(this).attr('seleccionado') != 'si'){
            jQuery('#ik_dirdatos_datos_cargados th .select_all').prop('checked', true);
            jQuery('#ik_dirdatos_datos_cargados th .select_all').attr('checked', 'checked');
            jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
                jQuery(this).find('.select_dato').prop('checked', true);
                jQuery(this).find('.select_dato').attr('checked', 'checked');
            });        
            jQuery(this).attr('seleccionado', 'si');
        } else {
            jQuery('#ik_dirdatos_datos_cargados th .select_all').prop('checked', false);
            jQuery('#ik_dirdatos_datos_cargados th .select_all').removeAttr('checked');
            jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
                jQuery(this).find('.select_dato').prop('checked', false);
                jQuery(this).find('.select_dato').removeAttr('checked');
            });   
            jQuery(this).attr('seleccionado', 'no');
            
        }
    });
    
    jQuery("#ik_dirdatos_datos_cargados .ik_dirdatos_boton_eliminar_seleccionados").on( "click", function() {
        jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
            var elemento_borrar = jQuery(this).parent();
            if (jQuery(this).find('.select_dato').prop('checked') == true){
                
                var pueblo_tr = jQuery(this);
                var iddato = pueblo_tr.attr('iddato');
                
                var data = {
    				action: "ik_dirdatos_ajax_eliminar_pueblo",
    				"post_type": "post",
    				"iddato": iddato,
    			};  
    
        		jQuery.post( ajaxurl, data, function(response) {
        			if (response){
                        pueblo_tr.fadeOut(700);
                        pueblo_tr.remove();
        		    }        
                });
            }
        });  
        jQuery('#ik_dirdatos_datos_cargados th .select_all').attr('seleccionado', 'no');
        jQuery('#ik_dirdatos_datos_cargados th .select_all').prop('checked', false);
        jQuery('#ik_dirdatos_datos_cargados th .select_all').removeAttr('checked');
        return false;
    });

    jQuery('#ik_dirdatos_datos_cargados').on('click','td .ik_dirdatos_boton_eliminar_pueblo', function(e){
        e.preventDefault();
        var confirmar =confirm('Confirmar eliminar pueblo.');
        if (confirmar == true) {
            var iddato = jQuery(this).parent().attr('iddato');
            var pueblo_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
            
            var data = {
    			action: "ik_dirdatos_ajax_eliminar_pueblo",
    			"post_type": "post",
    			"iddato": iddato,
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
                    pueblo_tr.fadeOut(700);
                    pueblo_tr.remove();
                    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
    		    }        
            });
        }
    });
    jQuery('#ik_dirdatos_datos_cargados').on('click','td .ik_dirdatos_boton_editar_pueblo', function(e){
        e.preventDefault();
        jQuery(this).prop('disabled', true);
        var repetido = {};
        jQuery('#ik_dirdatos_datos_cargados tbody tr .ik_dirdatos_iddato').each(function() {
          var txt = jQuery(this).text();
          if (repetido[txt])
            jQuery(this).parent().remove();
          else
            repetido[txt] = true;
        });
        jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_editor_dato').remove();
        var iddato = jQuery(this).parent().attr('iddato');
        var pueblo_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
        
        var data = {
			action: "ik_dirdatos_ajax_get_datos_pueblo_por_id",
			"post_type": "post",
			"iddato": iddato,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
    			var data = JSON.parse(response);
                jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
                pueblo_tr.after('<tr id="ik_dirdatos_edicion_dinamica_dato" class="ik_dirdatos_editor_dato"><td colspan="5"><div><select name="pais" cambiado="0" class="ik_dirdatos_paises_select_editado"><?php echo ik_dirdatos_listar_paises(); ?></select><select name="estado" class="ik_dirdatos_estados_select_editado">'+data.listadoestados+'</select><input type="text" required name="pueblo" placeholder="Pueblo" value="'+data.pueblo+'" style="margin: 0px 4px;" /><a href="#" class="button button-primary" id="ik_dirdatos_boton_guardardatos_pueblo" iddato="'+iddato+'">Guardar Cambios</a><a href="#" class="button button-primary" id="ik_dirdatos_boton_cancelar_edicion_dinamica" style="margin-left: 5px;">Cancelar</a></div></td></tr>');
                var repetido = {};
                jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
                  var txt = jQuery(this).text();
                  if (repetido[txt])
                    jQuery(this).remove();
                  else
                    repetido[txt] = true;
                });
                jQuery('.ik_dirdatos_paises_select_editado').val(data.id_pais);
        		jQuery('.ik_dirdatos_paises_select_editado').trigger('change');
        		jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_editor_dato select').select2();
            	jQuery('.ik_dirdatos_estados_select_editado').val(data.id_estado);
            		jQuery('.ik_dirdatos_estados_select_editado').trigger('change');
        		jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_editor_dato select').select2();
        		jQuery('.ik_dirdatos_boton_editar_pueblo').prop('disabled', false);
    	    }        
        });
    });
	
    jQuery('#ik_dirdatos_datos_cargados').on('click','#ik_dirdatos_boton_cancelar_edicion_dinamica', function(e){
        e.preventDefault();
		jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
	});

    jQuery('#ik_dirdatos_datos_cargados').on('click','#ik_dirdatos_boton_guardardatos_pueblo', function(e){
        e.preventDefault();
        
        var iddato = jQuery(this).attr('iddato');
        var pueblo_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
        var pueblo = jQuery('#ik_dirdatos_edicion_dinamica_dato input[name=pueblo]').val();
        var pais = jQuery('#ik_dirdatos_edicion_dinamica_dato select[name=pais]').val();
        var pais_nombre = jQuery('#ik_dirdatos_edicion_dinamica_dato select[name=pais] option[value='+pais+']').text();
        var estado = jQuery('#ik_dirdatos_edicion_dinamica_dato select[name=estado]').val();
        
        var data = {
			action: "ik_dirdatos_ajax_editar_pueblo",
			"post_type": "post",
			"iddato": iddato,
			"pueblo": pueblo,
			"estado": estado,
			"pais": pais,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    jQuery('#ik_dirdatos_edicion_dinamica_dato').fadeOut(500);
			    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
                pueblo_tr.fadeOut(500);
                pueblo_tr.find('.nombre').text(pueblo);
                pueblo_tr.find('.pais').text(pais_nombre);
                pueblo_tr.fadeIn(500);
		    }        
        });
    });
    
    jQuery('#ik_dirdatos_pueblos_existentes').on('click','#ik_dir_datos_buscar_pueblo', function(e){
        e.preventDefault();
        
        var busqueda = jQuery('#tag-search-input').val();
        if (busqueda != '' && busqueda != undefined){
            var data = {
    			action: "ik_dirdatos_ajax_buscar_dato",
    			"post_type": "post",
    			"busqueda": busqueda,
    			"tipo": 'pueblos',
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
    			    var data = JSON.parse(response);
    			    jQuery('#ik_dirdatos_datos_cargados tbody tr').fadeOut(500);
    			    jQuery('#ik_dirdatos_pueblos_existentes .ik_dirdatos_paginas').fadeOut(500);
    			    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
    			    jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_busqueda_listado').remove();
                    jQuery(data).prependTo('#ik_dirdatos_datos_cargados tbody');
                    
                    //Eliminar filas repetidas
                    var repetido = {};
                    jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
                      var txt = jQuery(this).text();
                      if (repetido[txt])
                        jQuery(this).remove();
                      else
                        repetido[txt] = true;
                    });
    		    }        
            });
        }
    
    });
    
    jQuery('#ik_dirdatos_pueblos_existentes').on('click','#ik_dirdatos_button_mostrartodo', function(e){
        e.preventDefault();
        
        jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_busqueda_listado').remove();
	    jQuery('#ik_dirdatos_datos_cargados tbody tr').fadeIn(500);
	    jQuery('#ik_dirdatos_pueblos_existentes .ik_dirdatos_paginas').fadeIn(500);
    });
});
</script>
<?php
//Si hay filtros
if (isset($_GET['paises'])){
    $servicio_id = absint($_GET['paises']);
    if ($servicio_id != 0){
        echo '<script>
        var id_pais = "'.$servicio_id.'";
        var valor_pais = jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises option[identificador=\'"+id_pais+"\']").attr("value");
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises").val(valor_pais);  
        
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-estados option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&paises="+id_pais);
        });

        </script>';
    }
}

if (isset($_GET['estados'])){
    $estado_id = absint($_GET['estados']);
    if ($estado_id != 0){
        echo '<script>
        var id_estado = "'.$estado_id.'";
        var valor_estado = jQuery("#ik_dirdatos_filter_box .ik-filtrar-estados option[identificador=\'"+id_estado+"\']").attr("value");
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-estados").val(valor_estado);  
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&estados="+id_estado);
        });
        
        </script>';
    }
}

?>