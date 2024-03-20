<?php
/*

Template: Carga y editar Registros
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

$url_registro = get_site_url().'/wp-admin/admin.php?page=ik_dirdatos_directorio';
if (isset($_GET['servicios']) || isset($_GET['pueblos'])){
    if (isset($_GET['servicios'])){
        $servicio_filtro = absint($_GET['servicios']);
        $queryFiltro['servicios'] = $servicio_filtro;
    }
    if (isset($_GET['pueblos'])){
        $pueblos_filtro = absint($_GET['pueblos']);
        $queryFiltro['pueblos'] = $pueblos_filtro;
    }
    
} else {
    $queryFiltro = false;
}


$offset = ($paginado - 1) * $cantidadListado;

//Si se hizo un submit del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['servicios']) && isset($_POST['negocio']) && isset($_POST['direccion']) && isset($_POST['pueblo']) && isset($_POST['telefono']) && isset($_POST['email'])){
        $servicios_id = intval($_POST['servicios']);
        $negocio = sanitize_text_field($_POST['negocio']);
        $negocio = str_replace('\\', '', $negocio);
        $pueblo_id = intval($_POST['pueblo']);
        $telefono = ik_dirdatos_formato_tel($_POST['telefono']);
        $email = sanitize_email($_POST['email']);
		
		if (isset($_POST['direccion'])){
			$direccion = sanitize_text_field($_POST['direccion']);
		} else{
			$direccion = '';
		}

		
        if (isset($_POST['whatsapp'])){
            $whatsapp = ik_dirdatos_formato_tel($_POST['whatsapp']);
        } else {
            $whatsapp = '';
        }
        if (isset($_POST['descripcion'])){
            $descripcion = sanitize_textarea_field($_POST['descripcion']);
        } else {
            $descripcion = '';
        }

		global $wpdb;
		$data_campos  = array (
		    'nombre' => $negocio,
		    'id_pueblo' => $pueblo_id,
		    'id_servicios' => $servicios_id,
		    'tel' => $telefono,
		    'whatsapp' => $whatsapp,
		    'email' => $email,
		    'direccion' => $direccion,
		    'descripcion' => $descripcion,
		);

		$tabla = $wpdb->prefix.'ik_dirdatos_registros';
		$rowResult = $wpdb->insert($tabla,  $data_campos , $format = NULL);
		
    }
    $result = 'Guardado';
} else {
    $result = '';
}
?>
<div id="ik_dirdatos_agregar_registros">
    <h1>Registros</h1>
    <form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_dirdatos_campos">
            <p>
                <h4>Servicio</h4>
    			<select multiselect name="servicios" class="ik_dirdatos_servicios_multiselect"><?php echo ik_dirdatos_listar_servicios(); ?></select> 
    		</p>
    		<p>
                <h4>Negocio</h4>
    		    <input type="text" required name="negocio" placeholder="Nombre del Negocio" /> 
    		</p>	
    		<p>
                <h4>Direcci&oacute;n</h4>
    		    <input type="text" name="direccion" placeholder="Direcci&oacute;n" /> 
    		</p>	
            <p>
                <h4>Pais</h4>
    			<select name="pais" class="ik_dirdatos_paises_select"><?php echo ik_dirdatos_listar_paises(); ?></select> 
    		</p>
    		<p>
    		    <div id="ik_dirdatos_estado_field">
                    <h4>Estado</h4>
        			<select name="estado" class="ik_dirdatos_estado_select"><option>-</option></select>
        		</div>
    		</p>
    		<p>
                <h4>Pueblo</h4>
    			<select name="pueblo" class="ik_dirdatos_pueblos_select"><?php echo ik_dirdatos_listar_pueblos(); ?></select> 
    		</p>
    		<p>
                <h4>Tel&eacute;fono</h4>
    		    <input type="tel" required name="telefono" placeholder="Tel&eacute;fono" /> 
    		</p>    		
    		<p>
                <h4>WhatsApp</h4>
    		    <input type="tel" name="whatsapp" placeholder="WhatsApp" /> 
    		</p>	
    		<p>
                <h4>Email</h4>
    		    <input type="email" required name="email" placeholder="Email" />
    		</p>	
    		<p>
                <h4>Descripci&oacute;n</h4>
    		    <textarea name="descripcion"></textarea>
    		</p>
        </div>
        <input type="submit" class="button button-primary" value="Agregar Registro" />
        <p id="ik_dato_guardado"><?php echo $result; ?></p>
    </form>
</div>
<div id ="ik_dirdatos_registros_existentes">
<?php
	//Listo los datos ya cargados
	$listado_registros = ik_dirdatos_listar_datos('registros', '', $cantidadListado, $offset, $queryFiltro);
	if ($listado_registros != false){
	    $listado_registros_todos = ik_dirdatos_cantidad_datos('registros', '', $queryFiltro);
		echo $listado_registros;
		echo ik_dirdatos_listado_paginas($paginado, 'ik_dirdatos_directorio', $cantidadListado, $listado_registros_todos);

	} else {
	    echo '<p class="search-box">
				<label class="screen-reader-text" for="tag-search-input">Buscar Registros:</label>
				<input type="search" id="tag-search-input" name="s" value="">
				<input type="submit" id="ik_dir_datos_buscar_registro" class="button" value="Buscar">
			</p>	
			<p id="ik_dirdatos_filter_box">
                <select name="ik-filtrar-pueblos" class="ik-filtrar-pueblos" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('pueblos', $url_registro).'
                </select>
                <select name="ik-filtrar-servicios" class="ik-filtrar-servicios" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('servicios', $url_registro).'
                </select>
			</p>';
	}
?>
</div>
<script>
jQuery(document).ready(function ($) {
    jQuery('.ik_dirdatos_paises_select').select2();
	jQuery('.ik_dirdatos_paises_select').val('1');
	jQuery('.ik_dirdatos_paises_select').trigger('change');
	jQuery('.ik_dirdatos_estado_select').select2();
	jQuery('.ik_dirdatos_pueblos_select').select2();
	jQuery('.ik_dirdatos_servicios_multiselect').select2();
	
    jQuery('#ik_dato_guardado').fadeOut(2600);
    
    //Atualizo listado de Estados del campo a agregar dependiendo del pais
    jQuery('#ik_dirdatos_agregar_registros').on('change', '.ik_dirdatos_paises_select', function(e){
        e.preventDefault();
        var select_estado = jQuery('#ik_dirdatos_estado_field select.ik_dirdatos_estado_select');
        var pais_value = jQuery(this).val();
        var data = {
			action: "ik_dirdatos_ajax_get_estados_de_pais",
			"post_type": "post",
			"pais_value": pais_value,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
				select_estado.empty();
                select_estado.append(data);
                
                //Asigno valor por defecto
                var value_estado_default = select_estado.find('option:first-child').attr('value');
                select_estado.val(value_estado_default);

                select_estado.select2();
                select_estado.trigger('change');
		    }        
        });
        
    });
    
    //Atualizo listado de pueblos del campo a agregar dependiendo del estado
    jQuery('#ik_dirdatos_estado_field').on('change', '.ik_dirdatos_estado_select', function(e){
        e.preventDefault();
        var select_pueblo = jQuery('#ik_dirdatos_agregar_registros select.ik_dirdatos_pueblos_select');
        var estado_value = jQuery(this).val();
        var data = {
			action: "ik_dirdatos_ajax_get_pueblos_de_pais",
			"post_type": "post",
			"estado_value": estado_value,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
				select_pueblo.empty();
                select_pueblo.append(data);
                
                //Asigno valor por defecto
                var value_estado_default = select_pueblo.find('option:first-child').attr('value');
                select_pueblo.val(value_estado_default);

                select_pueblo.select2();
                select_pueblo.trigger('change');
		    }        
        });
        
    });
 
    //Atualizo listado de Estados del campo a agregar dependiendo del pais
    jQuery('#ik_dirdatos_datos_cargados').on('change', '#ik_dirdatos_pais_select_editado', function(e){
        e.preventDefault();
        var select_estado = jQuery('#ik_dirdatos_estado_select_editado');
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
    				select_estado.empty();
                    select_estado.append(data);
                    
                    //Asigno valor por defecto
                    var value_estado_default = select_estado.find('option:first-child').attr('value');
                    select_estado.val(value_estado_default);
    
                    select_estado.select2();
                    select_estado.trigger('change');
    		    }
            });
        } else {
            //Le agrego un valor al id de cambios
            var pais_cambiado = pais_cambiado + 1;
            jQuery(this).attr('cambiado', pais_cambiado);
        }
        
    });
    
    //Atualizo listado de Estados del campo a agregar dependiendo del pais
    jQuery('#ik_dirdatos_datos_cargados').on('change', '#ik_dirdatos_estado_select_editado', function(e){
        e.preventDefault();
        var select_pueblo = jQuery('#ik_dirdatos_pueblo_select_editado');
        var estado_value = jQuery(this).val();
        var estado_cambiado = parseInt(jQuery(this).attr('cambiado'));

        //Si es la primera vez no hago nada, ya que quiero que quede el valor previamente asignado en la base de datos
        if (estado_cambiado > 0){       
            var data = {
    			action: "ik_dirdatos_ajax_get_pueblos_de_pais",
    			"post_type": "post",
    			"estado_value": estado_value,
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
    			    var data = JSON.parse(response);
    				select_pueblo.empty();
                    select_pueblo.append(data);
                    
                    //Asigno valor por defecto
                    var value_pueblo_default = select_pueblo.find('option:first-child').attr('value');
                    select_pueblo.val(value_pueblo_default);
    
                    select_pueblo.select2();
                    select_pueblo.trigger('change');
    		    }
            });
        } else {
            //Le agrego un valor al id de cambios
            var estado_cambiado = estado_cambiado + 1;
            jQuery(this).attr('cambiado', estado_cambiado);
        }
        
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
        var confirmar = confirm('Confirmar eliminar registros.');
        if (confirmar == true) {
            jQuery('#ik_dirdatos_datos_cargados tbody tr').each(function() {
            var elemento_borrar = jQuery(this).parent();
                if (jQuery(this).find('.select_dato').prop('checked') == true){
                    
                    var registro_tr = jQuery(this);
                    var iddato = registro_tr.attr('iddato');
                    
                    var data = {
        				action: "ik_dirdatos_ajax_eliminar_registro",
        				"post_type": "post",
        				"iddato": iddato,
        			};  
        
            		jQuery.post( ajaxurl, data, function(response) {
            			if (response){
                            registro_tr.fadeOut(700);
                            registro_tr.remove();
            		    }        
                    });
                }
            });
        }
        jQuery('#ik_dirdatos_datos_cargados th .select_all').attr('seleccionado', 'no');
        jQuery('#ik_dirdatos_datos_cargados th .select_all').prop('checked', false);
        jQuery('#ik_dirdatos_datos_cargados th .select_all').removeAttr('checked');
        return false;
    });
    
    jQuery('#ik_dirdatos_datos_cargados').on('click','td .ik_dirdatos_boton_eliminar_registro', function(e){
        e.preventDefault();
        var confirmar =confirm('Confirmar eliminar registro ya existente.');
        if (confirmar == true) {
            var iddato = jQuery(this).parent().attr('iddato');
            var registro_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
            
            var data = {
    			action: "ik_dirdatos_ajax_eliminar_registro",
    			"post_type": "post",
    			"iddato": iddato,
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
                    registro_tr.fadeOut(700);
                    registro_tr.remove();
                    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
    		    }        
            });
        }
    });
    
    jQuery('#ik_dirdatos_datos_cargados').on('click','td .ik_dirdatos_boton_editar_registro', function(e){
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
        var registro_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
        
        var data = {
			action: "ik_dirdatos_ajax_get_registro_a_editar",
			"post_type": "post",
			"iddato": iddato,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
				
				var order_data = '';
				if(parseInt(data.order_id) > 0){
					order_data = '<p style="padding: 20px;font-size: 20px;"><a target="_blank" href="<?php echo get_site_url(); ?>/wp-admin/post.php?post='+parseInt(data.order_id)+'&action=edit">Orden #'+parseInt(data.order_id)+'</a></p>';
				}
				
                registro_tr.after('<tr id="ik_dirdatos_edicion_dinamica_dato" class="ik_dirdatos_editor_dato"><td colspan="6"><div>'+order_data+'<p><select name="activo" id="ik_dirdatos_activo_select_editado"><option value="1">Activo</option><option value="0">Inactivo</option></select></p><p><select name="pais" cambiado="0" id="ik_dirdatos_pais_select_editado"><?php echo ik_dirdatos_listar_paises(); ?></select> <select cambiado="0" name="estado" id="ik_dirdatos_estado_select_editado">'+data.listadoestados+'</select> <select name="pueblo" id="ik_dirdatos_pueblo_select_editado">'+data.listadopueblos+'</select></p><p><select name="servicios" id="ik_dirdatos_servicios_select_editado"><?php echo ik_dirdatos_listar_servicios(); ?></select> <input type="email" id="ik_dirdatos_email_select_editado" required name="email" placeholder="Email" value="'+data.email+'" style="margin: 0px 4px;" /></p><p><input type="tel" required name="tel" id="ik_dirdatos_tel_select_editado" placeholder="Tel&eacute;fono" value="'+data.tel+'" style="margin: 0px 4px;" /> <input type="tel" name="tel" id="ik_dirdatos_whatsapp_select_editado" placeholder="WhatsApp" value="'+data.whatsapp+'" style="margin: 0px 4px;" /></p><p><input type="text" required name="nombre" placeholder="Empresa" value="'+data.nombre+'" id="ik_dirdatos_nombre_select_editado" style="margin: 0px 4px;" /> <input type="text" required name="direccion" id="ik_dirdatos_direccion_select_editado" placeholder="Direcci&oacute;n" value="'+data.direccion+'" style="margin: 0px 4px;" /></p><textarea required id="ik_dirdatos_descripcion_select_editado" name="descripcion">'+data.descripcion+'</textarea><a href="#" class="button button-primary" id="ik_dirdatos_boton_guardardatos_registro" iddato="'+iddato+'">Guardar Cambios</a><a href="#" class="button button-primary" id="ik_dirdatos_boton_cancelar_edicion_dinamica" style="margin-left: 5px;">Cancelar</a></div></td></tr>');
                	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_servicios_select_editado').select2();
	                jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pueblo_select_editado').select2();
                	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_servicios_select_editado').val(data.id_servicios);
                	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pueblo_select_editado').val(data.id_pueblo);
					jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_activo_select_editado').val(data.activo);
	                jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_servicios_select_editado').trigger('change');
	                jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_activo_select_editado').trigger('change');
	                
	                if (data.id_pais != 0){
                        var pais_id_select = data.id_pais;
                    } else {
                        var pais_id_select = jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pais_select_editado option[data_name="Puerto Rico"]').val();
                    }
    	                
	                
               	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pais_select_editado').val(pais_id_select);
               	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pais_select_editado').select2();
	                jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pais_select_editado').trigger('change');
               	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_estado_select_editado').val(data.id_estado);
               	jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_estado_select_editado').select2();
	                jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_estado_select_editado').trigger('change');	                
	                jQuery('#ik_dirdatos_edicion_dinamica_dato #ik_dirdatos_pueblo_select_editado').trigger('change');
	                var repetido = {};
                    jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_editor_dato').each(function() {
                      var txt = jQuery(this).html();
                      if (repetido[txt])
                        jQuery(this).remove();
                      else
                        repetido[txt] = true;
                    });
                    jQuery('.ik_dirdatos_boton_editar_registro').prop('disabled', false);
		    }
        });
    });
	
    jQuery('#ik_dirdatos_datos_cargados').on('click','#ik_dirdatos_boton_cancelar_edicion_dinamica', function(e){
        e.preventDefault();
		jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
	});
	
    jQuery('#ik_dirdatos_datos_cargados').on('click','#ik_dirdatos_boton_guardardatos_registro', function(e){
        e.preventDefault();
        
        var iddato = jQuery(this).attr('iddato');
        var registro_tr = jQuery('#ik_dirdatos_datos_cargados tbody').find('tr[iddato='+iddato+']');
        var servicio = jQuery('#ik_dirdatos_servicios_select_editado').val();
        var nombre = jQuery('#ik_dirdatos_nombre_select_editado').val();
        var direccion = jQuery('#ik_dirdatos_direccion_select_editado').val();
        var pueblo = jQuery('#ik_dirdatos_pueblo_select_editado').val();
        var tel = jQuery('#ik_dirdatos_tel_select_editado').val();
        var whatsapp = jQuery('#ik_dirdatos_whatsapp_select_editado').val();
        var email = jQuery('#ik_dirdatos_email_select_editado').val();
        var descripcion = jQuery('#ik_dirdatos_descripcion_select_editado').val();
        var activo = jQuery('#ik_dirdatos_activo_select_editado').val();
        var activo_texto = jQuery('#ik_dirdatos_activo_select_editado option[value='+activo+']').text();
		
        var data = {
			action: "ik_dirdatos_ajax_editar_registro",
			"post_type": "post",
			"iddato": iddato,
			"servicio": servicio,
			"nombre": nombre,
			"direccion": direccion,
			"pueblo": pueblo,
			"tel": tel,
			"whatsapp": whatsapp,
			"email": email,
			"descripcion": descripcion,
			"activo": activo,
		};  

		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var telEditado = JSON.parse(response);

			    jQuery('#ik_dirdatos_edicion_dinamica_dato').fadeOut(500);
			    jQuery('#ik_dirdatos_edicion_dinamica_dato').remove();
                registro_tr.fadeOut(500);
                registro_tr.find('.nombre').text(nombre);
                registro_tr.find('.tel').text(telEditado);
                registro_tr.find('.activo').text(activo_texto);
                registro_tr.fadeIn(500);
		    }        
        });
    });
    
    jQuery('#ik_dirdatos_registros_existentes').on('click','#ik_dir_datos_buscar_registro', function(e){
        e.preventDefault();
        
        var busqueda = jQuery('#tag-search-input').val();
        if (busqueda != '' && busqueda != undefined){
            var data = {
    			action: "ik_dirdatos_ajax_buscar_dato",
    			"post_type": "post",
    			"busqueda": busqueda,
    			"tipo": 'registros',
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
    			    var data = JSON.parse(response);
    			    jQuery('#ik_dirdatos_datos_cargados tbody tr').fadeOut(500);
    			    jQuery('#ik_dirdatos_registros_existentes .ik_dirdatos_paginas').fadeOut(500);
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
    
    jQuery('#ik_dirdatos_registros_existentes').on('click','#ik_dirdatos_button_mostrartodo', function(e){
        e.preventDefault();
        
        jQuery('#ik_dirdatos_datos_cargados .ik_dirdatos_busqueda_listado').remove();
	    jQuery('#ik_dirdatos_datos_cargados tbody tr').fadeIn(500);
	    jQuery('#ik_dirdatos_registros_existentes .ik_dirdatos_paginas').fadeIn(500);
    });
});
</script>
<?php
//Si hay filtros
if (isset($_GET['servicios'])){
    $servicio_id = absint($_GET['servicios']);
    if ($servicio_id != 0){
        echo '<script>
        var id_servicio = "'.$servicio_id.'";
        var valor_servicio = jQuery("#ik_dirdatos_filter_box .ik-filtrar-servicios option[identificador=\'"+id_servicio+"\']").attr("value");
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-servicios").val(valor_servicio);  
        
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&servicios="+id_servicio);
        });
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-estados option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&servicios="+id_servicio);
        });
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-pueblos option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&servicios="+id_servicio);
        });
        
        </script>';
    }
}

if (isset($_GET['pueblos'])){
    $pueblo_id = absint($_GET['pueblos']);
    if ($pueblo_id != 0){
        echo '<script>
        var id_pueblo = "'.$pueblo_id.'";
        var valor_pueblo = jQuery("#ik_dirdatos_filter_box .ik-filtrar-pueblos option[identificador=\'"+id_pueblo+"\']").attr("value");
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-pueblos").val(valor_pueblo);  
        
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-servicios option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&pueblos="+id_pueblo);
        });
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-estados option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&pueblos="+id_pueblo);
        });
        jQuery("#ik_dirdatos_filter_box .ik-filtrar-paises option").each(function() {
            jQuery(this).val(jQuery(this).val()+"&pueblos="+id_pueblo);
        });
        
        </script>';
    }
}

?>