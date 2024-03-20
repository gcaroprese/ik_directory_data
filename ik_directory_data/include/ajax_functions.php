<?php
/*

Ajax Functions
Update: 26/08/2021
Author: Gabriel Caroprese

*/

//Ajax para dar informacion de un registro
add_action('wp_ajax_nopriv_ik_dirdatos_ajax_get_registros_datos', 'ik_dirdatos_ajax_get_registros_datos');
add_action( 'wp_ajax_ik_dirdatos_ajax_get_registros_datos', 'ik_dirdatos_ajax_get_registros_datos');
function ik_dirdatos_ajax_get_registros_datos(){
	if (isset($_POST['pais_value']) && isset($_POST['services_value']) && isset($_POST['pueblo_value'])){

        $servicios = explode(",", $_POST['services_value']);
        if (is_array($servicios)){
            $where = ' WHERE (id_servicios = ';
            $countService = 0;
            foreach ($servicios as $servicio){
                if ($countService > 0){
                    $where .= ' OR id_servicios = ';
                }
                $servicio = absint($servicio);
                $where .= $servicio;
                $countService = $countService + 1;
            }
            
            $where .= ' OR id_servicios = 0)';
        
        } else {
            $servicio = absint($servicios);
            $where = ' WHERE (id_servicios = '.$servicio.' OR id_servicios = 0)';
        }

        if ($servicio == 0){
            $mensaje = '<div class="ik_dirdatos_registro_listado" style="text-align: center;">'. __( 'Completa los campos requeridos.', 'ik-directorio-datos').'</div>';

            echo json_encode($mensaje);
            wp_die();               
        }
        
        $where .= ' AND id_pueblo = '.absint($_POST['pueblo_value']);

    	global $wpdb;
    	$queryRegistros = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_registros ".$where." AND activo = 1 GROUP BY(nombre) ORDER BY RAND() LIMIT ".ik_dirdatos_get_cant_listados();
    	$registros = $wpdb->get_results($queryRegistros);
    
    	if (isset($registros[0]->id)){ 
    		$mensaje = '';
    		foreach ($registros as $registro){
    		    if ($registro->whatsapp != '' && $registro->whatsapp != '-' && $registro->whatsapp != NULL){
    		        $whatsapp = '<span class="ik_dirdatos_listado_tel"><i class="fab fa-whatsapp-square"></i> <a target="_blank" href="https://wa.me/'.ik_dirdatos_formato_tel($registro->whatsapp, true, true).'">'.$registro->whatsapp.'</a></span>';
    		    } else {
    		        $whatsapp = '';
    		    }
    			$mensaje .= '<div class="ik_dirdatos_registro_listado">
    			<span class="ik_dirdatos_listado_nombre">'.__( 'Empresa: ', 'ik-directorio-datos').$registro->nombre.'</span>
    			<span class="ik_dirdatos_listado_tel"><i class="fas fa-phone-square"></i> <a href="tel:'.ik_dirdatos_formato_tel($registro->tel, true).'">'.$registro->tel.'</a></span>'.$whatsapp.'
    			<span class="ik_dirdatos_listado_email"><i class="fas fa-envelope-square"></i> <a target="_blank" href="mailto:'.$registro->email.'">'.$registro->email.'</a></span>';

                if ($registro->direccion != ''){
                    $mensaje .= '<span class="ik_dirdatos_listado_direccion"><i class="fas fa-map-marked-alt"></i> '.$registro->direccion.'</span>';
                }

                if ($registro->descripcion != ''){
                    $mensaje .= '<span class="ik_dirdatos_listado_descripcion">'.$registro->descripcion.'</span>';
                }
                $mensaje .= '</div>';
    		}
    		
    		echo json_encode( $mensaje);
    	} else {
    	   $mensaje = '<div class="ik_dirdatos_registro_listado" style="text-align: center;">'. __( 'No se encontraron datos de empresas que brinden el servicio que buscas.', 'ik-directorio-datos').'</div>';
    		echo json_encode($mensaje);
    	}
	} else {
	   $mensaje = '<div class="ik_dirdatos_registro_listado" style="text-align: center;">'. __( 'Completa los campos requeridos.', 'ik-directorio-datos').'</div>';

        echo json_encode($mensaje);    
	}
	wp_die();
}

//Ajax para validar o modificar registro_id
add_action('wp_ajax_nopriv_ik_dirdatos_ajax_get_payment_link', 'ik_dirdatos_ajax_get_payment_link');
add_action( 'wp_ajax_ik_dirdatos_ajax_get_payment_link', 'ik_dirdatos_ajax_get_payment_link');
function ik_dirdatos_ajax_get_payment_link(){
    if (isset($_POST['registro_id'])){
        $registro_id = intval($_POST['registro_id']);
        
        if ($registro_id != 0){
            
            //Busco el # de orden asociado al registro_id
            global $wpdb;
	        $queryRegistro = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key='id_registro' AND meta_value = '".$registro_id."' ORDER BY meta_id DESC";
    	    $registro = $wpdb->get_results($queryRegistro);
            
            if (isset($registro[0]->post_id)){
                
                $pago_link = get_post_meta($registro[0]->post_id, 'url_pago', true);

                $result_registro['id_registro'] = ik_dirdatos_create_id_register();

                if ($pago_link != NULL){
                    $result_registro['location'] = $pago_link;
                } else {
                    $result_registro['location'] = get_site_url();
                }
                    echo json_encode($result_registro);
  
            }
        }
    }
    wp_die();
}


//Ajax para dar informacion de un registro
add_action( 'wp_ajax_ik_dirdatos_ajax_get_registro_a_editar', 'ik_dirdatos_ajax_get_registro_a_editar');
function ik_dirdatos_ajax_get_registro_a_editar(){
	if (isset($_POST['iddato'])){
	    $iddato = absint($_POST['iddato']);
    	global $wpdb;
    	$queryRegistroID = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_registros WHERE id = ".$iddato;
    	$registro = $wpdb->get_results($queryRegistroID);
    
    	if (isset($registro[0]->id)){
    	    $datosRegistro['response'] = true;
    	    $datosRegistro['nombre']= $registro[0]->nombre;
    	    $datosRegistro['id_pueblo'] = $registro[0]->id_pueblo;
    	    $datosRegistro['id_estado'] = ik_dirdatos_get_estado_por_pueblo_id($datosRegistro['id_pueblo']);
    	    $datosRegistro['id_pais'] = ik_dirdatos_ID_pais_por_estado_ID($datosRegistro['id_estado']);
    	    $datosRegistro['listadoestados'] = ik_dirdatos_listar_estados($datosRegistro['id_pais']);
    	    $datosRegistro['listadopueblos'] = ik_dirdatos_listar_pueblos($datosRegistro['id_estado']);
    	    $datosRegistro['id_servicios'] = $registro[0]->id_servicios;
    	    $datosRegistro['tel'] = $registro[0]->tel;
    	    $datosRegistro['whatsapp'] = $registro[0]->whatsapp;
    	    $datosRegistro['email'] = $registro[0]->email;
    	    $datosRegistro['activo'] = $registro[0]->activo;
    	    $datosRegistro['order_id'] = $registro[0]->order_id;
    	    $datosRegistro['direccion'] = $registro[0]->direccion;
    	    $datosRegistro['descripcion'] = $registro[0]->descripcion;    	
    	    echo json_encode( $datosRegistro);
    	}
	} 
	wp_die();      
}


//Ajax para dar id de pais de un estado por su id
add_action( 'wp_ajax_ik_ajax_dirdatos_ID_pais_por_estado_ID', 'ik_ajax_dirdatos_ID_pais_por_estado_ID');
function ik_ajax_dirdatos_ID_pais_por_estado_ID(){
	if (isset($_POST['iddato'])){
	    $iddato = absint($_POST['iddato']);
        $pais_id = ik_dirdatos_ID_pais_por_estado_ID($iddato);
        echo json_encode($pais_id);
	} 
	wp_die();      
}


//Ajax para dar informacion de un pueblo depeniendo su id
add_action( 'wp_ajax_ik_dirdatos_ajax_get_datos_pueblo_por_id', 'ik_dirdatos_ajax_get_datos_pueblo_por_id');
function ik_dirdatos_ajax_get_datos_pueblo_por_id(){
	if (isset($_POST['iddato'])){
	    $iddato = absint($_POST['iddato']);
    	global $wpdb;
    	$queryPuebloID = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_pueblos WHERE id = ".$iddato;
    	$pueblo = $wpdb->get_results($queryPuebloID);
    
    	if (isset($pueblo[0]->id)){
    	    $datosPueblo['response'] = true;
    	    $datosPueblo['pueblo']= $pueblo[0]->nombre;
    	    $datosPueblo['id_pais'] = $pueblo[0]->pais;
    	    $datosPueblo['id_estado'] = $pueblo[0]->estado_id;
    	    $datosPueblo['listadoestados'] = ik_dirdatos_listar_estados($pueblo[0]->pais);
    	    echo json_encode( $datosPueblo);
    	}
	} 
	wp_die();      
}


//Ajax para devolver opciones de estados dependiendo el pais seleccionado
add_action('wp_ajax_nopriv_ik_dirdatos_ajax_get_estados_de_pais', 'ik_dirdatos_ajax_get_estados_de_pais');
add_action( 'wp_ajax_ik_dirdatos_ajax_get_estados_de_pais', 'ik_dirdatos_ajax_get_estados_de_pais');
function ik_dirdatos_ajax_get_estados_de_pais(){
	if (isset($_POST['pais_value'])){
	    $wherePais = absint($_POST['pais_value']);
	   
	    $pueblos_opciones = ik_dirdatos_listar_estados($wherePais);
	    echo json_encode( $pueblos_opciones );
	}
	wp_die();      
}


//Ajax para devolver opciones de pueblos dependiendo el estado seleccionado
add_action('wp_ajax_nopriv_ik_dirdatos_ajax_get_pueblos_de_pais', 'ik_dirdatos_ajax_get_pueblos_de_pais');
add_action( 'wp_ajax_ik_dirdatos_ajax_get_pueblos_de_pais', 'ik_dirdatos_ajax_get_pueblos_de_pais');
function ik_dirdatos_ajax_get_pueblos_de_pais(){
	if (isset($_POST['estado_value']) || isset($_POST['pais_value'])){
	    $whereEstado = (isset($_POST['estado_value'])) ? absint($_POST['estado_value']) : 0;
		if (isset($_POST['pais_value'])){
			$wherePais = absint($_POST['pais_value']);
			$pueblos_opciones = ik_dirdatos_listar_pueblos($whereEstado, $wherePais);
		} else {
			$pueblos_opciones = ik_dirdatos_listar_pueblos($whereEstado);
		}
	    echo json_encode( $pueblos_opciones );
	}
	wp_die();      
}

//Ajax para devolver opciones de pueblos dependiendo el estado seleccionado
add_action('wp_ajax_nopriv_ik_dirdatos_ajax_get_label', 'ik_dirdatos_ajax_get_label');
add_action( 'wp_ajax_ik_dirdatos_ajax_get_label', 'ik_dirdatos_ajax_get_label');
function ik_dirdatos_ajax_get_label(){
	if (isset($_POST['dato']) && isset($_POST['valor_dato'])){
	    $dato = ($_POST['dato'] == 'estado') ? 'estado' : 'pueblo';
	    $valor_dato = absint($_POST['valor_dato']);
		$nombre_label = ik_dirdatos_return_label($dato, $valor_dato);
		update_option(date(i), $nombre_label);
	    echo json_encode( $nombre_label );
	}
	wp_die();      
}


//Ajax para listar una busqueda
add_action( 'wp_ajax_ik_dirdatos_ajax_buscar_dato', 'ik_dirdatos_ajax_buscar_dato');
function ik_dirdatos_ajax_buscar_dato(){
    if(isset($_POST['busqueda']) && isset($_POST['tipo'])){
        $tipo = sanitize_text_field($_POST['tipo']);
        $busqueda = sanitize_text_field($_POST['busqueda']);
        
        $resultado = ik_dirdatos_listar_datos_busqueda($tipo, $busqueda);
        
        if ($resultado == false ){
            $resultado = '<tr class="ik_dirdatos_busqueda_listado" ><td colspan="10"><div><p>No se encontraron resultados.</p><a href="#" id="ik_dirdatos_button_mostrartodo" class="button button-primary">Mostrar Todo</a></div></td></tr>';
        }
        
        
        echo json_encode( $resultado );
    }
    wp_die();         
}

//Ajax para borrar un servicio
add_action( 'wp_ajax_ik_dirdatos_ajax_eliminar_servicio', 'ik_dirdatos_ajax_eliminar_servicio');
function ik_dirdatos_ajax_eliminar_servicio(){
    if(isset($_POST['iddato'])){
        $idServicio = absint($_POST['iddato']);

        global $wpdb;
        $tablaEliminar = $wpdb->prefix.'ik_dirdatos_servicios';
        $rowResult = $wpdb->delete( $tablaEliminar , array( 'id' => $idServicio ) );
        
        echo json_encode( true );
    }
    wp_die();         
}

//Ajax para borrar un servicio
add_action( 'wp_ajax_ik_dirdatos_ajax_eliminar_estado', 'ik_dirdatos_ajax_eliminar_estado');
function ik_dirdatos_ajax_eliminar_estado(){
    if(isset($_POST['iddato'])){
        $id_estado = absint($_POST['iddato']);

        global $wpdb;
        $tablaEliminar = $wpdb->prefix.'ik_dirdatos_estados';
        $rowResult = $wpdb->delete( $tablaEliminar , array( 'id' => $id_estado ) );
        
        echo json_encode( true );
    }
    wp_die();         
}

//Ajax para borrar un registro
add_action( 'wp_ajax_ik_dirdatos_ajax_eliminar_registro', 'ik_dirdatos_ajax_eliminar_registro');
function ik_dirdatos_ajax_eliminar_registro(){
    if(isset($_POST['iddato'])){
        $idServicio = absint($_POST['iddato']);

        global $wpdb;
        $tablaEliminar = $wpdb->prefix.'ik_dirdatos_registros';
        $rowResult = $wpdb->delete( $tablaEliminar , array( 'id' => $idServicio ) );
        
        echo json_encode( true );
    }
    wp_die();         
}


//Ajax para borrar un pueblo
add_action( 'wp_ajax_ik_dirdatos_ajax_eliminar_pueblo', 'ik_dirdatos_ajax_eliminar_pueblo');
function ik_dirdatos_ajax_eliminar_pueblo(){
    if(isset($_POST['iddato'])){
        $idPueblo = absint($_POST['iddato']);

        global $wpdb;
        $tablaEliminar = $wpdb->prefix.'ik_dirdatos_pueblos';
        $rowResult = $wpdb->delete( $tablaEliminar , array( 'id' => $idPueblo ) );
        
        echo json_encode( true );
    }
    wp_die();         
}

//Ajax para borrar un pais
add_action( 'wp_ajax_ik_dirdatos_ajax_eliminar_pais', 'ik_dirdatos_ajax_eliminar_pais');
function ik_dirdatos_ajax_eliminar_pais(){
    if(isset($_POST['iddato'])){
        $idPais = absint($_POST['iddato']);

        global $wpdb;
        $tablaEliminar = $wpdb->prefix.'ik_dirdatos_paises';
        $rowResult = $wpdb->delete( $tablaEliminar , array( 'id' => $idPais ) );
        
        echo json_encode( true );
    }
    wp_die();         
}

//Ajax para editar un pueblo
add_action( 'wp_ajax_ik_dirdatos_ajax_editar_pueblo', 'ik_dirdatos_ajax_editar_pueblo');
function ik_dirdatos_ajax_editar_pueblo(){
    if(isset($_POST['iddato']) && isset($_POST['pueblo']) && isset($_POST['pais']) && isset($_POST['estado'])){
        $idPueblo = absint($_POST['iddato']);
        $pueblo = sanitize_text_field($_POST['pueblo']);
		$pueblo = str_replace('\\', '', $pueblo);
        $pais = sanitize_text_field($_POST['pais']);
        $estado = sanitize_text_field($_POST['estado']);

        global $wpdb;
        $tableupdate = $wpdb->prefix.'ik_dirdatos_pueblos';
        $where = [ 'id' => $idPueblo ];
            
        $datos_modificados  = array (
                        'pais'=>$pais,
                        'nombre'=>$pueblo,
                        'estado_id'=>$estado,
                );
        $rowResult = $wpdb->update($tableupdate,  $datos_modificados , $where);
        
        echo json_encode( true );
    }
    wp_die();         
}

//Ajax para editar un estado
add_action( 'wp_ajax_ik_dirdatos_ajax_editar_estado', 'ik_dirdatos_ajax_editar_estado');
function ik_dirdatos_ajax_editar_estado(){
    if(isset($_POST['iddato']) && isset($_POST['pais']) && isset($_POST['estado'])){
        $idPueblo = absint($_POST['iddato']);
        $nombre = sanitize_text_field($_POST['estado']);
		$nombre = str_replace('\\', '', $nombre);
        $pais = sanitize_text_field($_POST['pais']);
        $nombre_pais = ik_dirdatos_nombre_pais_por_ID($pais);

        global $wpdb;
        $tableupdate = $wpdb->prefix.'ik_dirdatos_estados';
        $where = [ 'id' => $idPueblo ];
            
        $datos_modificados  = array (
                        'pais'=>$pais,
                        'nombre'=>$nombre,
                );
        $rowResult = $wpdb->update($tableupdate,  $datos_modificados , $where);
        
        //Devuelvo el nombre del pais
        echo json_encode( $nombre_pais );
    }
    wp_die();         
}

//Ajax para editar un registro
add_action( 'wp_ajax_ik_dirdatos_ajax_editar_registro', 'ik_dirdatos_ajax_editar_registro');
function ik_dirdatos_ajax_editar_registro(){
    if(isset($_POST['iddato']) && isset($_POST['servicio']) && isset($_POST['nombre']) && isset($_POST['direccion']) && isset($_POST['pueblo']) && isset($_POST['tel']) && isset($_POST['whatsapp']) && isset($_POST['email']) && isset($_POST['descripcion'])){
 
        $idRegistro = absint($_POST['iddato']);
        $id_servicio = absint($_POST['servicio']);
        $nombre = sanitize_text_field($_POST['nombre']);
		$nombre = str_replace('\\', '', $nombre);
        $direccion = sanitize_text_field($_POST['direccion']);
		$direccion = str_replace('\\', '', $direccion);
        $id_pueblo = sanitize_text_field($_POST['pueblo']);
        $tel = ik_dirdatos_formato_tel($_POST['tel']);
        $whatsapp = ik_dirdatos_formato_tel($_POST['whatsapp']);
        $email = sanitize_email($_POST['email']);
        $descripcion = sanitize_textarea_field($_POST['descripcion']);
		$descripcion = str_replace('\\', '', $descripcion);
		$activo = (isset($_POST['activo'])) ? absint($_POST['activo']) : 1;
		$activo = ($activo > 0) ? 1 : 0;

        global $wpdb;
        $tableupdate = $wpdb->prefix.'ik_dirdatos_registros';
        $where = [ 'id' => $idRegistro ];
            
        $datos_modificados  = array (
                        'nombre'=>$nombre,
                        'id_pueblo'=>$id_pueblo,
                        'id_servicios'=>$id_servicio,
                        'tel'=>$tel,
                        'whatsapp'=>$whatsapp,
                        'email'=>$email,
                        'direccion'=>$direccion,
                        'descripcion'=>$descripcion,
                        'activo'=>$activo,
                );

        $rowResult = $wpdb->update($tableupdate,  $datos_modificados , $where);
        
        echo json_encode( $tel );
    }
    wp_die();         
}


//Ajax para devolver nuevo id_registro de transaccion
add_action('wp_ajax_nopriv_ik_dirdatos_ajax_get_new_register_id', 'ik_dirdatos_ajax_get_new_register_id');
add_action( 'wp_ajax_ik_dirdatos_ajax_get_new_register_id', 'ik_dirdatos_ajax_get_new_register_id');
function ik_dirdatos_ajax_get_new_register_id(){
    $register_id = ik_dirdatos_create_id_register();

	echo json_encode( $register_id );
	wp_die();      
}

?>