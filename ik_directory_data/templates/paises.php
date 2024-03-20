<?php
/*

Template: Carga y editar Paises
Update: 11/08/2021
Author: Gabriel Caroprese

*/
if ( ! defined('ABSPATH')) exit('restricted access');


//Si se hizo un submit del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['nuevo_pais_es']) && isset($_POST['nuevo_pais_en'])){
        $nuevo_paises_es = $_POST['nuevo_pais_es'];
        $nuevo_paises_en = $_POST['nuevo_pais_en'];
		$zona_1 = (isset($_POST['zona_1'])) ? $_POST['nueva_zona_1'] : array();
		$zona_2 = (isset($_POST['zona_2'])) ? $_POST['nueva_zona_2'] : array();
    
        // Creo un conteador para el foreach
        $countDato = 0;
        
        // I check if $catOrigen is array or not. If not I don't need repetitions and I do an insert once
        if (is_array($nuevo_paises_es) && is_array($nuevo_paises_en)){
    
            foreach( $nuevo_paises_es as $pais_es ) {
    
                if (isset($pais_es) && isset($nuevo_paises_en[$countDato])) {
                    $pais_es = sanitize_text_field($pais_es);
					$pais_es = str_replace('\\', '', $pais_es);
                    $pais_en = sanitize_text_field($nuevo_paises_en[$countDato]);
					$pais_en = str_replace('\\', '', $pais_en);
                    
    				global $wpdb;
    				$querycampoRep = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_paises WHERE nombre_es LIKE '".$pais_es."' OR nombre_en LIKE '".$pais_en."'";
    				$repetido = $wpdb->get_results($querycampoRep);
    
    				// Si no existe el mismo nombre creo el dato
    				if (!isset($repetido[0]->id)){
    
						$data_campo  = array (
							'nombre_en' => $pais_en,
							'nombre_es' => $pais_es,
						);

						if (isset($zona_1[$countDato])){
							$data_campo['zona_1'] = sanitize_text_field($zona_1[$countDato]);
						}
						
						if (isset($zona_2[$countDato])){
							$data_campo['zona_2'] = sanitize_text_field($zona_2[$countDato]);					
						}			
						
						global $wpdb;
						$tabla = $wpdb->prefix.'ik_dirdatos_paises';
						$rowResult = $wpdb->insert($tabla, $data_campo, $format = NULL);
					
    				}
    				
    				$countDato = $countDato + 1;
              
                }
            }
        }
    }
    
    //Para datos ya existentes
    if (isset($_POST['paises_id']) && isset($_POST['paises_es']) && isset($_POST['paises_en'])){
        $paises_es = $_POST['paises_es'];
        $paises_en = $_POST['paises_en'];
        $paises_id = $_POST['paises_id'];
		$zona_1 = (isset($_POST['zona_1'])) ? $_POST['zona_1'] : array();
		$zona_2 = (isset($_POST['zona_2'])) ? $_POST['zona_2'] : array();
    
        // Creo un conteador para el foreach
        $countDato = 0;
        
        // I check if $catOrigen is array or not. If not I don't need repetitions and I do an insert once
        if (is_array($paises_es) && is_array($paises_en) && is_array($paises_id)){
    
            foreach( $paises_id as $pais_id ) {

                if (isset($paises_es[$countDato]) && isset($paises_en[$countDato])) {
                    $pais_es = sanitize_text_field($paises_es[$countDato]);
					$pais_es = str_replace('\\', '', $pais_es);
                    $pais_en = sanitize_text_field($paises_en[$countDato]);
                    $pais_en = str_replace('\\', '', $pais_en);
					
					$data_campo  = array (
					    'nombre_en' => $pais_en,
					    'nombre_es' => $pais_es,
					);
					
					if (isset($zona_1[$countDato])){
						$data_campo['zona_1'] = sanitize_text_field($zona_1[$countDato]);
					}
					
					if (isset($zona_2[$countDato])){
						$data_campo['zona_2'] = sanitize_text_field($zona_2[$countDato]);					
					}			
					
					global $wpdb;
                    $tabla = $wpdb->prefix.'ik_dirdatos_paises';
                    $where = [ 'id' => $pais_id ];
                        
                    $rowResult = $wpdb->update($tabla, $data_campo, $where);

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
<div id="ik_dirdatos_agregar_paises">
    <h1>Editar paises</h1>
    <form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_dirdatos_campos">
            <ul>
                <?php
                //Listo los datos ya cargados
                $listado_paises = ik_dirdatos_listar_datos('paises');
                if ($listado_paises != false){
                    echo $listado_paises;
    			} else {
    			?>
    			         <li>
                            <input type="text" required name="nuevo_pais_es[]" placeholder="*Pa&iacute;s (Espa&ntilde;ol)" /> <input type="text" required name="nuevo_pais_en[]" placeholder="*Pa&iacute;s (Ingl&eacute;s)" /> <?php echo ik_dirdatos_selectores_zona(1).ik_dirdatos_selectores_zona(2); ?><a href="#" class="ik_dirdatos_eliminar_campo button">Eliminar</a>
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
        jQuery('#ik_dirdatos_agregar_paises .ik_dirdatos_campos ul').append('<li class="paises_agregar"><input type="text" required name="nuevo_pais_es[]" placeholder="Pa&iacute;s (Espa&ntilde;ol)" /> <input type="text" required name="nuevo_pais_en[]" placeholder="Pa&iacute;s (Ingl&eacute;s)" /><?php echo ik_dirdatos_selectores_zona(1).ik_dirdatos_selectores_zona(2); ?><a href="#" class="ik_dirdatos_eliminar_campo button">Eliminar</a></li>');
        return false;
    });
    
    // Eliminar campos por crear
    jQuery(document).on('click', '#ik_dirdatos_agregar_paises .ik_dirdatos_campos .ik_dirdatos_eliminar_campo', function(){
        jQuery(this).parent().remove();
        return false;
    });
    
    // Eliminar campos por crear
    jQuery(document).on('click', '#ik_dirdatos_agregar_paises .ik_dirdatos_campos .ik_dirdatos_eliminar_campo_creado', function(){
        var confirmar =confirm('Confirmar eliminar pais ya existente.');
        var elemento_borrar = jQuery(this).parent()
        if (confirmar == true) {
         
            var iddato = parseInt(jQuery(this).attr('iddato'));
            if (iddato != 0){
                
     			var data = {
    				action: "ik_dirdatos_ajax_eliminar_pais",
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