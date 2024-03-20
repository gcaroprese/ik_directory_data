<?php
/*

Template: Carga y editar Servicios
Update: 11/08/2021
Author: Gabriel Caroprese

*/
if ( ! defined('ABSPATH')) exit('restricted access');


//Si se hizo un submit del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['nuevo_servicio_es']) && isset($_POST['nuevo_servicio_en'])){
        $nuevo_servicios_es = $_POST['nuevo_servicio_es'];
        $nuevo_servicios_en = $_POST['nuevo_servicio_en'];
    
        // Creo un conteador para el foreach
        $countDato = 0;
        
        // I check if $catOrigen is array or not. If not I don't need repetitions and I do an insert once
        if (is_array($nuevo_servicios_es) && is_array($nuevo_servicios_en)){
    
            foreach( $nuevo_servicios_es as $servicio_es ) {
    
                if (isset($servicio_es) && isset($nuevo_servicios_en[$countDato])) {
                    $servicio_es = sanitize_text_field($servicio_es);
                    $servicio_en = sanitize_text_field($nuevo_servicios_en[$countDato]);
                    
    				global $wpdb;
    				$querycampoRep = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_servicios WHERE nombre_es LIKE '".$servicio_es."' OR nombre_en LIKE '".$servicio_en."'";
    				$repetido = $wpdb->get_results($querycampoRep);
    
    				// Si no existe el mismo nombre creo el dato
    				if (!isset($repetido[0]->id)){
    
    						global $wpdb;
    						$data_campo  = array (
    						    'nombre_en' => $servicio_en,
    						    'nombre_es' => $servicio_es,
    						);
    
    						$tabla = $wpdb->prefix.'ik_dirdatos_servicios';
    						$rowResult = $wpdb->insert($tabla,  $data_campo , $format = NULL);
    					
    				}
    				
    				$countDato = $countDato + 1;
              
                }
            }
        }
    }
    
    //Para datos ya existentes
    if (isset($_POST['servicios_id']) && isset($_POST['servicios_es']) && isset($_POST['servicios_en'])){
        $servicios_es = $_POST['servicios_es'];
        $servicios_en = $_POST['servicios_en'];
        $servicios_id = $_POST['servicios_id'];
    
        // Creo un conteador para el foreach
        $countDato = 0;
        
        // I check if $catOrigen is array or not. If not I don't need repetitions and I do an insert once
        if (is_array($servicios_es) && is_array($servicios_en) && is_array($servicios_id)){
    
            foreach( $servicios_id as $servicio_id ) {

                if (isset($servicios_es[$countDato]) && isset($servicios_en[$countDato])) {
                    $servicio_es = sanitize_text_field($servicios_es[$countDato]);
                    $servicio_en = sanitize_text_field($servicios_en[$countDato]);
                    
					$data_campo  = array (
					    'nombre_en' => $servicio_en,
					    'nombre_es' => $servicio_es,
					);

					global $wpdb;
                    $tabla = $wpdb->prefix.'ik_dirdatos_servicios';
                    $where = [ 'id' => $servicio_id ];
                        
                    $dato_campo  = array (
                   				    'nombre_en' => $servicio_en,
					                'nombre_es' => $servicio_es,
                            );
                    $rowResult = $wpdb->update($tabla,  $data_campo, $where);

    				$countDato = $countDato + 1;
    				
              
                }
            }
        }
    }
    $result = 'Guardado';
} else {
    $result = '';
}
?>
<div id="ik_dirdatos_agregar_servicios">
    <h1>Editar Servicios</h1>
    <form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_dirdatos_campos">
            <ul>
                <?php
                //Listo los datos ya cargados
                $listado_servicios = ik_dirdatos_listar_datos('servicios');
                if ($listado_servicios != false){
                    echo $listado_servicios;
    			} else {
    			?>
    			         <li>
                            <input type="text" required name="nuevo_servicio_es[]" placeholder="Servicio (Espa&ntilde;ol)" /> <input type="text" required name="nuevo_servicio_en[]" placeholder="Servicio (Ingl&eacute;s)" /> <a href="#" class="ik_dirdatos_eliminar_campo button">Eliminar</a>
                        </li>
    			<?php
    			}
                ?>
            </ul>
            <a href="#" class="button button-primary" id="ik_dirdatos_agregar_campos">Agregar Campos</a>
        </div>
        <input type="submit" class="button button-primary" value="Guardar" />
        <p id="ik_dato_guardado"><?php echo $result; ?></p>
    </form>
</div>
<script>
    jQuery('#ik_dato_guardado').fadeOut(2600);

    // Agregar campos
    jQuery(document).on('click', '#ik_dirdatos_agregar_campos', function(){
        jQuery('#ik_dirdatos_agregar_servicios .ik_dirdatos_campos ul').append('<li><input type="text" required name="nuevo_servicio_es[]" placeholder="Servicio (Espa&ntilde;ol)" /> <input type="text" required name="nuevo_servicio_en[]" placeholder="Servicio (Ingl&eacute;s)" /> <a href="#" class="ik_dirdatos_eliminar_campo button">Eliminar</a></li>');
        return false;
    });
    
    // Eliminar campos por crear
    jQuery(document).on('click', '#ik_dirdatos_agregar_servicios .ik_dirdatos_campos .ik_dirdatos_eliminar_campo', function(){
        jQuery(this).parent().remove();
        return false;
    });
    
    // Eliminar campos por crear
    jQuery(document).on('click', '#ik_dirdatos_agregar_servicios .ik_dirdatos_campos .ik_dirdatos_eliminar_campo_creado', function(){
        var confirmar =confirm('Confirmar eliminar servicio ya existente.');
        var elemento_borrar = jQuery(this).parent()
        if (confirmar == true) {
         
            var iddato = parseInt(jQuery(this).attr('iddato'));
            if (iddato != 0){
                
     			var data = {
    				action: "ik_dirdatos_ajax_eliminar_servicio",
    				"post_type": "post",
    				"iddato": iddato,
    			};  
        
        		// Respuesta de la ejecución
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
    
</script>