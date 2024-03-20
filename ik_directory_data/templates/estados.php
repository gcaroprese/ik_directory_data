<?php
/*

Template: Carga y editar Estados
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


$url_registro = get_site_url().'/wp-admin/admin.php?page=ik_dirdatos_estados';
if (isset($_GET['paises'])){
    if (isset($_GET['paises'])){
        $paises_filtro = absint($_GET['paises']);
        $queryFiltro['paises'] = $paises_filtro;
    }
} else {
    $queryFiltro = false;
}

//Si se hizo un submit del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['nuevo_pais']) && isset($_POST['nuevo_estado'])){
        $nuevo_pais = $_POST['nuevo_pais'];
        $nuevo_estado = $_POST['nuevo_estado'];
    
        // Creo un conteador para el foreach
        $countDato = 0;
        
        // I check if $catOrigen is array or not. If not I don't need repetitions and I do an insert once
        if (is_array($nuevo_pais) && is_array($nuevo_estado)){
    
            foreach( $nuevo_pais as $pais ) {
    
                if (isset($pais) && isset($nuevo_estado[$countDato])) {
                    $pais = sanitize_text_field($pais);
                    $estado = sanitize_text_field($nuevo_estado[$countDato]);
					$estado = str_replace('\\', '', $estado);
    
    						global $wpdb;
    						$data_campo  = array (
    						    'pais' => $pais,
    						    'nombre' => $estado,
    						);
    
    						$tabla = $wpdb->prefix.'ik_dirdatos_estados';
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
<div id="ik_dirdatos_agregar_estados">
    <h1>Editar Estados</h1>
    <form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_dirdatos_campos">
            <ul>
				<li>
					<select name="nuevo_pais[]" class="ik_dirdatos_paises_select"><?php echo ik_dirdatos_listar_paises(); ?></select> <input type="text" required name="nuevo_estado[]" placeholder="estado" /> <a disabled href="#" class="ik_dirdatos_eliminar_campo button" style="opacity: 0">Eliminar</a>
				</li>
            </ul>
            <a href="#" class="button button-primary" id="ik_dirdatos_agregar_campos">Agregar Campos</a>
        </div>
        <input type="submit" class="button button-primary" value="Guardar" />
        <p id="ik_dato_guardado"><?php echo $result; ?></p>
    </form>
</div>
<div id ="ik_dirdatos_estados_existentes">
<?php
	//Listo los datos ya cargados
	$listado_estados = ik_dirdatos_listar_datos('estados', '', $cantidadListado, $offset, $queryFiltro);
	if ($listado_estados != false){
	    $listado_estados_todos = ik_dirdatos_cantidad_datos('estados', '', $queryFiltro);
		echo $listado_estados;
		echo ik_dirdatos_listado_paginas($paginado, 'ik_dirdatos_estados', $cantidadListado, $listado_estados_todos);

	} else {
	    echo '<p class="search-box">
				<label class="screen-reader-text" for="tag-search-input">Buscar Estados:</label>
				<input type="search" id="tag-search-input" name="s" value="">
				<input type="submit" id="ik_dir_datos_buscar_estados" class="button" value="Buscar">
			</p>	
			<p id="ik_dirdatos_filter_box">
                <select name="ik-filtrar-paises" class="ik-filtrar-paises" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('paises', $url_registro).'
                </select>
			</p>';
	}
?>
</div>
<script>
jQuery(document).ready(function ($) {
	jQuery('.ik_dirdatos_paises_select').select2();
	jQuery('.ik_dirdatos_estado_select').select2();
	jQuery('.ik_dirdatos_paises_select').val('2');
	jQuery('.ik_dirdatos_paises_select').trigger('change');
    jQuery('#ik_dato_guardado').fadeOut(2600);

    // Agregar campos
    jQuery(document).on('click', '#ik_dirdatos_agregar_campos', function(){
        jQuery('#ik_dirdatos_agregar_estados .ik_dirdatos_campos ul').append('<li><select name="nuevo_pais[]" class="ik_dirdatos_paises_select_agregado"><?php echo ik_dirdatos_listar_paises(); ?></select> <input type="text" required name="nuevo_estado[]" placeholder="estado" /> <a href="#" class="ik_dirdatos_eliminar_campo button">Eliminar</a></li>');
		jQuery('.ik_dirdatos_paises_select_agregado').val('Puerto Rico');
		jQuery('.ik_dirdatos_paises_select_agregado').trigger('change');
		jQuery('#ik_dirdatos_agregar_estados .ik_dirdatos_campos ul select').each(function(i){			
			jQuery(this).addClass('ik_dirdatos_paises_select-'+i);
			jQuery(this).removeClass('ik_dirdatos_paises_select_agregado');
			jQuery(this).select2();
		});
        return false;
    });
    
    // Eliminar campos por crear
    jQuery(document).on('click', '#ik_dirdatos_agregar_estados .ik_dirdatos_campos .ik_dirdatos_eliminar_campo', function(){
        jQuery(this).parent().remove();
        return false;
    });
    
    // Eliminar estado
    jQuery(document).on('click', '#ik_dirdatos_agregar_estados .ik_dirdatos_campos .ik_dirdatos_eliminar_campo_creado', function(){
        var confirmar =confirm('Confirmar eliminar estado ya existente.');
        var elemento_borrar = jQuery(this).parent();
        if (confirmar == true) {
         
            var iddato = parseInt(jQuery(this).attr('iddato'));
            if (iddato != 0){
                
     			var data = {
    				action: "ik_dirdatos_ajax_eliminar_estado",
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
                
                var estado_tr = jQuery(this);
                var iddato = estado_tr.attr('iddato');
                
                var data = {
    				action: "ik_dirdatos_ajax_eliminar_estado",
    				"post_type": "post",
    				"iddato": iddato,
    			};  
    
        		jQuery.post( ajaxurl, data, function(response) {
        			if (response){
                        estado_tr.fadeOut(700);
                        estado_tr.remove();
        		    }        
                });
            }
        });  
        jQuery('#ik_dirdatos_datos_cargados th .select_all').attr('seleccionado', 'no');
        jQuery('#ik_dirdatos_datos_cargados th .select_all').prop('checked', false);
        jQuery('#ik_dirdatos_datos_cargados th .select_all').removeAttr('checked');
        return false;
    });

    jQuery('#ik_dirdatos_datos_cargados').on('click','td .ik_dirdatos_boton_eliminar_estado', function(e){
        e.preventDefault();
        var confirmar =confirm('Confirmar eliminar estado.');
        if (confirmar == true) {
            var iddato = jQuery(this).parent().attr('iddato');
            var estado_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
            
            var data = {
    			action: "ik_dirdatos_ajax_eliminar_estado",
    			"post_type": "post",
    			"iddato": iddato,
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
                    estado_tr.fadeOut(700);
                    estado_tr.remove();
                    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
    		    }        
            });
        }
    });

	jQuery('#ik_dirdatos_datos_cargados').on('click','#ik_dirdatos_boton_cancelar_edicion_dinamica', function(e){
        e.preventDefault();
		jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
	});
	
    jQuery('#ik_dirdatos_datos_cargados').on('click','td .ik_dirdatos_boton_editar_estado', function(e){
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
        var estado_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
        var valor_estado = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+'] .nombre').text();

        var data = {
			action: "ik_ajax_dirdatos_ID_pais_por_estado_ID",
			"post_type": "post",
			"iddato": iddato,
		};  
		
		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
                estado_tr.after('<tr id="ik_dirdatos_edicion_dinamica_dato" class="ik_dirdatos_editor_dato"><td colspan="5"><div><select name="pais" class="ik_dirdatos_paises_select_editado"><?php echo ik_dirdatos_listar_paises(); ?></select><input type="text" required name="estado" placeholder="estado" value="'+valor_estado+'" style="margin: 0px 4px;" /><a href="#" class="button button-primary" id="ik_dirdatos_boton_guardardatos_estado" iddato="'+iddato+'">Guardar Cambios</a><a href="#" class="button button-primary" id="ik_dirdatos_boton_cancelar_edicion_dinamica" style="margin-left: 5px;">Cancelar</a></div></td></tr>');
                var repetido = {};
                jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
                  var txt = jQuery(this).text();
                  if (repetido[txt])
                    jQuery(this).remove();
                  else
                    repetido[txt] = true;
                });
                jQuery('.ik_dirdatos_paises_select_editado').val(data);
        		jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_editor_dato select').select2();
        		jQuery('.ik_dirdatos_paises_select_editado').trigger('change');
        		jQuery('.ik_dirdatos_boton_editar_estado').prop('disabled', false);
    	    }        
        });
    
    });

    jQuery('#ik_dirdatos_datos_cargados').on('click','#ik_dirdatos_boton_guardardatos_estado', function(e){
        e.preventDefault();
        
        var iddato = jQuery(this).attr('iddato');
        var estado_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
        var estado = jQuery('#ik_dirdatos_edicion_dinamica_dato input[name=estado]').val();
        var pais = jQuery('#ik_dirdatos_edicion_dinamica_dato select[name=pais]').val();
        
        var data = {
			action: "ik_dirdatos_ajax_editar_estado",
			"post_type": "post",
			"iddato": iddato,
			"estado": estado,
			"pais": pais,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
			    jQuery('#ik_dirdatos_edicion_dinamica_dato').fadeOut(500);
			    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
                estado_tr.fadeOut(500);
                estado_tr.find('.nombre').text(estado);
                estado_tr.find('.pais').text(data);
                estado_tr.fadeIn(500);
		    }        
        });
    });
    
    jQuery('#ik_dirdatos_estados_existentes').on('click','#ik_dir_datos_buscar_estado', function(e){
        e.preventDefault();
        
        var busqueda = jQuery('#tag-search-input').val();
        if (busqueda != '' && busqueda != undefined){
            var data = {
    			action: "ik_dirdatos_ajax_buscar_dato",
    			"post_type": "post",
    			"busqueda": busqueda,
    			"tipo": 'estados',
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
    			    var data = JSON.parse(response);
    			    jQuery('#ik_dirdatos_datos_cargados tbody tr').fadeOut(500);
    			    jQuery('#ik_dirdatos_estados_existentes .ik_dirdatos_paginas').fadeOut(500);
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
    
    jQuery('#ik_dirdatos_estados_existentes').on('click','#ik_dirdatos_button_mostrartodo', function(e){
        e.preventDefault();
        
        jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_busqueda_listado').remove();
	    jQuery('#ik_dirdatos_datos_cargados tbody tr').fadeIn(500);
	    jQuery('#ik_dirdatos_estados_existentes .ik_dirdatos_paginas').fadeIn(500);
    });
});
</script>
<?php
//Si hay filtros
if (isset($_GET['paises'])){
    $pais_id = absint($_GET['paises']);
    if ($pais_id != 0){
        echo '<script>
        var id_pais = "'.$pais_id.'";
        var valor_pais = jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises option[identificador=\'"+id_pais+"\']").attr("value");
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises").val(valor_pais); 
        </script>';
    }
}

?>