<?php
/*

General Functions
Update: 26/08/2021
Author: Gabriel Caroprese

*/


//Obtengo el # de la cantidad de listados a mostrar
function ik_dirdatos_get_cant_listados(){
    $cantListados_options  = get_option('ik_dirdatos_config_cant_listados');
    if ($cantListados_options == NULL){
        $cant_a_listar = IK_DIRDATOS_CANT_LISTADO;
    } else {
        $cant_a_listar = $cantListados_options;
    }
    return $cant_a_listar;
}

//Obtengo el # de la cantidad de listados a mostrar
function ik_dirdatos_get_cant_registros_por_pueblo(){
    $cantRegistros_options  = get_option('ik_dirdatos_config_cant_registros');
    if ($cantRegistros_options == NULL){
        $cantRegistros = IK_DIRDATOS_CANT_POR_PUEBLO;
    } else {
        $cantRegistros = $cantRegistros_options;
    }
    return $cantRegistros;
}



//Listo los datos ya existentes
function ik_dirdatos_listar_datos($dato, $where = '', $cantidad = '', $offsetList = '', $filtro = false){
    $dato = sanitize_text_field($dato);

	
	if ($dato == 'registros'){
		$order = ' ORDER BY id DESC';
		$orderDir = 'DESC';
		$orderDirLink = 'desc';
		$orderDirChange = 'asc';
		$ordervalue = 'id';
		$idOrder = 'activo';
		$telOrder = '';
		$activoOrder = '';
		$nombreOrder = '';
		if (isset($_GET['order']) && isset($_GET['dir'])){
			$orderDato = sanitize_text_field($_GET['order']);
			$orderedDir = sanitize_text_field($_GET['dir']);
			
			if ($orderDato == 'nombre'){
				$ordervalue = 'nombre';
				$idOrder = '';
				$nombreOrder = 'activo';		
			} else if ($orderDato == 'tel'){
				$ordervalue = 'tel';	
				$idOrder = '';
				$telOrder = 'activo';				
			} else if ($orderDato == 'activo'){
				$ordervalue = 'activo';
				$idOrder = '';
				$activoOrder = 'activo';		
			}
			if ($orderedDir == 'asc'){
				$orderDir = 'ASC';
				$orderDirLink = 'asc';
				$orderDirChange = 'desc';
			} else {
				$orderDir = 'DESC';
				$orderDirLink = 'desc';
				$orderDirChange = 'asc';
			} 
			
			$order = ' ORDER BY '.$ordervalue.' '.$orderDir;
		}
	} else if ($dato == 'paises'){
		$order = ' ORDER BY nombre_es ASC';
	} else if ($dato == 'estados'){
		$order = ' ORDER BY nombre ASC';
	} else if ($dato == 'pueblos'){
		$order = ' ORDER BY nombre ASC';
	} else {
		$order = ' ORDER BY id DESC';	
	}
	
	if ($where != ''){
		$where = sanitize_text_field($where);
		if ($dato !== 'pueblos'){
			$where= " WHERE nombre_es LIKE '%".$where."%'";
		} else {
			$where= " WHERE nombre LIKE '%".$where."%'";			
		}
	}
	
	if (isset($filtro['servicios']) || isset($filtro['paises']) || isset($filtro['estados']) || isset($filtro['pueblos'])){

        if (strpos($where, 'WHERE') !== false) {
            $where .= ' AND ';
        } else {
            $where .= ' WHERE ';
        }
	    
	    if (isset($filtro['servicios'])){
	        if ($dato == 'registros'){
	            $where .= 'id_servicios = '.absint($filtro['servicios']).' AND ';
	        }
	    }
	    if (isset($filtro['pueblos'])){
	        if ($dato == 'registros'){
	            $where .= 'id_pueblo = '.absint($filtro['pueblos']).' AND ';
	        }
	    }
	    if (isset($filtro['paises'])){
	        if ($dato == 'estados' || $dato == 'pueblos'){
	            $where .= 'pais = '.absint($filtro['paises']).' AND ';
	        }
	    }
	    if (isset($filtro['estados'])){
	        if ($dato == 'pueblos'){
	            $where .= 'estado_id = '.absint($filtro['estados']).' AND ';
	        }
	    }
	    $where = substr($where, 0, -5);
	}
	
	if (is_int($offsetList) && is_int($cantidad)){
	    $offsetList = ' LIMIT '.$cantidad.' OFFSET '.$offsetList;
	} else {
	    $offsetList = '';
	}
	
    if ($dato == 'servicios'){
        $dato = 'servicios';  
    } else if($dato == 'paises'){
        $dato = 'paises';  
    }  else if($dato == 'estados'){
        $dato = 'estados';  
    } else if ($dato == 'registros'){
        $dato = 'registros';        
    } else {
        $dato = 'pueblos';
    }
    
	global $wpdb;
	$queryDato = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_".$dato.$where.$order.$offsetList;
	$datosExistente = $wpdb->get_results($queryDato);

	// Si existen campos los listo
	if (isset($datosExistente[0]->id)){ 
		if ($dato == 'registros'){
		    $url_registro = get_site_url().'/wp-admin/admin.php?page=ik_dirdatos_directorio';
			$listado = '
			<p class="search-box">
				<label class="screen-reader-text" for="tag-search-input">Buscar '.$dato.':</label>
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
			</p>	
			<table id="ik_dirdatos_datos_cargados"">
					<thead>
						<tr>
							<th><input type="checkbox" class="select_all" /></th>
							<th class="sorted '.$orderDirLink.' '.$idOrder.'"><a href="'.$url_registro.'&order=id&dir='.$orderDirChange.'">ID<span class="sorting-indicator"></span></a></th>
							<th class="sorted '.$orderDirLink.' '.$nombreOrder.'"><a href="'.$url_registro.'&order=nombre&dir='.$orderDirChange.'">Empresa<span class="sorting-indicator"></span></a></th>
							<th class="sorted '.$orderDirLink.' '.$telOrder.'"><a href="'.$url_registro.'&order=tel&dir='.$orderDirChange.'">Tel&eacute;fono<span class="sorting-indicator"></span></a></th>
							<th class="sorted '.$orderDirLink.' '.$activoOrder.'"><a href="'.$url_registro.'&order=activo&dir='.$orderDirChange.'">Estado<span class="sorting-indicator"></span></a></th>
							<th><a href="#" class="ik_dirdatos_boton_eliminar_seleccionados button action">Eliminar</a></th>
						</tr>
					</thead>
					<tbody>';		
		} else if ($dato == 'pueblos'){
		    $url_registro = get_site_url().'/wp-admin/admin.php?page=ik_dirdatos_pueblos';
			$listado = '
			<p class="search-box">
				<label class="screen-reader-text" for="tag-search-input">Buscar '.$dato.':</label>
				<input type="search" id="tag-search-input" name="s" value="">
				<input type="submit" id="ik_dir_datos_buscar_pueblo" class="button" value="Buscar">
			</p>			
			<p id="ik_dirdatos_filter_box">
                <select name="ik-filtrar-paises" class="ik-filtrar-paises" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('paises', $url_registro).'
                </select>
                <select name="ik-filtrar-estados" class="ik-filtrar-estados" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('estados', $url_registro).'
                </select>
			</p>	
			<table id="ik_dirdatos_datos_cargados">
					<thead>
						<tr>
							<th><input type="checkbox" class="select_all" /></th>
							<th>ID</th>
							<th>Pueblo</th>
							<th>Pa&iacute;s</th>
							<th><a href="#" class="ik_dirdatos_boton_eliminar_seleccionados button action">Eliminar</a></th>
						</tr>
					</thead>
					<tbody>';			
		}  else if ($dato == 'estados'){
		    $url_registro = get_site_url().'/wp-admin/admin.php?page=ik_dirdatos_estados';
			$listado = '
			<p class="search-box">
				<label class="screen-reader-text" for="tag-search-input">Buscar '.$dato.':</label>
				<input type="search" id="tag-search-input" name="s" value="">
				<input type="submit" id="ik_dir_datos_buscar_estados" class="button" value="Buscar">
			</p>	
			<p id="ik_dirdatos_filter_box">
                <select name="ik-filtrar-paises" class="ik-filtrar-paises" onchange="location = this.value;">
                '.ik_dirdatos_opciones_filtro('paises', $url_registro).'
                </select>
			</p>
			<table id="ik_dirdatos_datos_cargados">
					<thead>
						<tr>
							<th><input type="checkbox" class="select_all" /></th>
							<th>ID</th>
							<th>Estado</th>
							<th>Pa&iacute;s</th>
							<th><a href="#" class="ik_dirdatos_boton_eliminar_seleccionados button action">Eliminar</a></th>
						</tr>
					</thead>
					<tbody>';			
		} else {
		    $listado = '';
		}
	    foreach ($datosExistente as $datoExistente){
	        if ($dato == 'servicios'){
    	        $listado .= '
    	        <li>
    	            <input type="hidden" name="'.$dato.'_id[]" value="'.$datoExistente->id.'" />
                    <input type="text" required name="'.$dato.'_es[]" value="'.$datoExistente->nombre_es.'" placeholder="Servicio (Espa&ntilde;ol)" /> <input type="text" required name="'.$dato.'_en[]" value="'.$datoExistente->nombre_en.'" placeholder="Servicio (Ingl&eacute;s)" /> <a href="#" iddato="'.$datoExistente->id.'" class="ik_dirdatos_eliminar_campo_creado button">Eliminar</a>
                </li>';
	        } else if ($dato == 'paises'){
    	        $listado .= '
    	        <li>
    	            <input type="hidden" name="'.$dato.'_id[]" value="'.$datoExistente->id.'" />
                    <input type="text" required name="'.$dato.'_es[]" value="'.$datoExistente->nombre_es.'" placeholder="Pa&iacute;s (Espa&ntilde;ol)" /> <input type="text" required name="'.$dato.'_en[]" value="'.$datoExistente->nombre_en.'" placeholder="Pa&iacute;s (Ingl&eacute;s)" /> '.ik_dirdatos_selectores_zona(1, $datoExistente->zona_1).ik_dirdatos_selectores_zona(2, $datoExistente->zona_2).'<a href="#" iddato="'.$datoExistente->id.'" class="ik_dirdatos_eliminar_campo_creado button">Eliminar</a>
                </li>';
	        } else if ($dato == 'pueblos'){
				$listado .= '
				<tr iddato="'.$datoExistente->id.'">
					<td><input type="checkbox" class="select_dato" /></td>
					<td class="ik_dirdatos_iddato">'.$datoExistente->id.'</td>
					<td class="nombre">'.$datoExistente->nombre.'</td>
					<td class="pais">'.ik_dirdatos_nombre_pais_por_ID($datoExistente->pais).'</td>
					<td iddato="'.$datoExistente->id.'">
						<button class="ik_dirdatos_boton_editar_pueblo button action">Editar</button>
						<button class="ik_dirdatos_boton_eliminar_pueblo button action">Eliminar</button></td>
				</tr>';				
			} else if ($dato == 'estados'){
				$listado .= '
				<tr iddato="'.$datoExistente->id.'">
					<td><input type="checkbox" class="select_dato" /></td>
					<td class="ik_dirdatos_iddato">'.$datoExistente->id.'</td>
					<td class="nombre">'.$datoExistente->nombre.'</td>
					<td class="pais">'.ik_dirdatos_nombre_pais_por_ID($datoExistente->pais).'</td>
					<td iddato="'.$datoExistente->id.'">
						<button href="#" class="ik_dirdatos_boton_editar_estado button action">Editar</button>
						<button href="#" class="ik_dirdatos_boton_eliminar_estado button action">Eliminar</button></td>
				</tr>';				
			} else if ($dato == 'registros'){
				$activo = 'Activo';
				if (isset($datoExistente->activo)){
					if ($datoExistente->activo == 0){
						$activo = 'Inactivo';
					}
				}
				$listado .= '
				<tr iddato="'.$datoExistente->id.'">
					<td><input type="checkbox" class="select_dato" /></td>
					<td class="ik_dirdatos_iddato">'.$datoExistente->id.'</td>
					<td class="nombre">'.$datoExistente->nombre.'</td>
					<td class="tel">'.$datoExistente->tel.'</td>
					<td class="activo">'.$activo.'</td>
					<td iddato="'.$datoExistente->id.'">
						<button class="ik_dirdatos_boton_editar_registro button action">Ver M&aacute;s</button>
						<button class="ik_dirdatos_boton_eliminar_registro button action">Eliminar</button></td>
				</tr>';		
			}
	    }
		if ($dato == 'pueblos'){
			$listado .= '</tbody>
				    <tfoot>
						<tr>
							<th><input type="checkbox" class="select_all" /></th>
							<th>ID</th>
							<th>Pueblo</th>
							<th>Pa&iacute;s</th>
							<th><a href="#" class="ik_dirdatos_boton_eliminar_seleccionados button action">Eliminar</a></th>
						</tr>
					</tfoot>
					<tbody>
				</table>';
		} else if ($dato == 'registros'){
			$listado .= '</tbody>
				    <tfoot>
						<tr>
							<th><input type="checkbox" class="select_all" /></th>
							<th class="tabla-filtro '.$idOrder.'">ID</th>
							<th class="tabla-filtro '.$nombreOrder.'">Empresa</th>
							<th class="tabla-filtro '.$telOrder.'">Tel&eacute;fono</th>
							<th class="tabla-filtro '.$activoOrder.'">Estado</th>
							<th><a href="#" class="ik_dirdatos_boton_eliminar_seleccionados button action">Eliminar</a></th>
						</tr>
					</tfoot>
					<tbody>
				</table>';
		}
	    
	    return $listado;
	    
	}
	
	return false;
}

//Listo opciones de filtro para filtrar en listados
function ik_dirdatos_opciones_filtro($tipoDeDato, $web){
    $tipoDeDato = sanitize_text_field($tipoDeDato);
    $web = esc_url_raw($web);
    
    if ($tipoDeDato == 'paises'){
        $datoNombre = 'Pa&iacute;ses';
        $query_table = 'ik_dirdatos_paises';
        $datoListar = 'nombre_es';
    } else if ($tipoDeDato == 'estados'){
        $datoNombre = 'Estados';
        $query_table = 'ik_dirdatos_estados';
        $datoListar = 'nombre';
    } else if ($tipoDeDato == 'pueblos'){
        $datoNombre = 'Pueblos';
        $query_table = 'ik_dirdatos_pueblos';
        $datoListar = 'nombre';
    } else if ($tipoDeDato == 'servicios'){
        $datoNombre = 'Servicios';
        $query_table = 'ik_dirdatos_servicios';
        $datoListar = 'nombre_es';
    } else {
        return;
    }
    
    //Primera opcion por defecto
    $opciones = '<option value="'.$web.'">Filtrar '.$datoNombre.'</option>';
    
    
    global $wpdb;
	$queryDatoOpcion = "SELECT * FROM ".$wpdb->prefix.$query_table." ORDER BY ".$datoListar." ASC";
	$opcionesFiltro = $wpdb->get_results($queryDatoOpcion);

	// Si existen paises devuelvo el conteo
	if (isset($opcionesFiltro[0]->id)){ 
	    foreach ($opcionesFiltro as $opcionFiltro){
	        $opciones .= '<option class="ik-dirdatos-filtro-seleccion" value="'.$web.'&'.$tipoDeDato.'='.$opcionFiltro->id.'" identificador="'.$opcionFiltro->id.'">'.$opcionFiltro->$datoListar.'</option>';
	    }
	}
        
    return $opciones;
    
}

//Mostrar nombre de ID de país
function ik_dirdatos_nombre_pais_por_ID($id_pais){
    $id_pais = absint($id_pais);
	
    if ($lang=get_bloginfo("language") == 'es' || $lang=get_bloginfo("language") == 'es_ES' || $lang=get_bloginfo("language") == 'es_PR'){
	    $nombre = 'nombre_es';
	} else {
	    $nombre = 'nombre_en';
	}
	global $wpdb;
	$queryPais = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_paises WHERE id = ".$id_pais;
	$pais = $wpdb->get_results($queryPais);

	// Si existen paises devuelvo el conteo
	if (isset($pais[0]->$nombre)){ 
        $pais_nombre = $pais[0]->$nombre;
	} else {
	    $pais_nombre = '-';
	}
	
	return $pais_nombre;
	
}

//Mostrar ID de pais de un estado
function ik_dirdatos_ID_pais_por_estado_ID($id_estado){
    $id_estado = absint($id_estado);
	
	global $wpdb;
	$queryEstado = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_estados WHERE id = ".$id_estado;
	$estado = $wpdb->get_results($queryEstado);
    
	if (isset($estado[0]->pais)){ 
        $id_pais = absint($estado[0]->pais);
	} else {
	    $id_pais = 0;
	}
	
	return $id_pais;
	
}

//Mostrar ID de pais de un estado
function ik_dirdatos_get_estado_por_pueblo_id($id_pueblo){
    $id_pueblo = absint($id_pueblo);
	
	global $wpdb;
	$queryPueblo = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_pueblos WHERE id = ".$id_pueblo;
	$pueblo = $wpdb->get_results($queryPueblo);
    
	if (isset($pueblo[0]->estado_id)){ 
        $estado_id = $pueblo[0]->estado_id;
	} else {
	    $estado_id = 0;
	}
	
	return $estado_id;
	
}


//Hago listados de busquedas
function ik_dirdatos_listar_datos_busqueda($dato, $where = ''){
    $dato = sanitize_text_field($dato);
	
	if ($where != ''){
		$where = sanitize_text_field($where);
		if ($dato != 'pueblos' && $dato != 'registros'){
			$where= " WHERE nombre_es LIKE '%".$where."%'";
		} else {
			$where= " WHERE nombre LIKE '%".$where."%'";			
		}
	}
	
    if ($dato == 'servicios'){
        $dato = 'servicios';  
    } else if ($dato == 'registros'){
        $dato = 'registros';        
    } else {
        $dato = 'pueblos';
    }
    
	global $wpdb;
	$queryDato = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_".$dato.$where;
	$datosExistente = $wpdb->get_results($queryDato);

	// Si existen campos los listo
	if (isset($datosExistente[0]->id)){
		
		$listado = '';
	    foreach ($datosExistente as $datoExistente){
	        if ($dato == 'servicios'){
    	        $listado .= '<li class="ik_dirdatos_busqueda_listado"><input type="hidden" name="'.$dato.'_id[]" value="'.$datoExistente->id.'" /><input type="text" required name="'.$dato.'_es[]" value="'.$datoExistente->nombre_es.'" placeholder="Servicio (Espa&ntilde;ol)" /> <input type="text" required name="'.$dato.'_en[]" value="'.$datoExistente->nombre_en.'" placeholder="Servicio (Ingl&eacute;s)" /> <a href="#" iddato="'.$datoExistente->id.'" class="ik_dirdatos_eliminar_campo_creado button">Eliminar</a></li>';
	        } else if ($dato == 'pueblos'){
				$listado .= '<tr iddato="'.$datoExistente->id.'" class="ik_dirdatos_busqueda_listado"><td><input type="checkbox" class="select_dato" /></td><td class="ik_dirdatos_iddato">'.$datoExistente->id.'</td><td class="nombre">'.$datoExistente->nombre.'</td><td class="pais">'.ik_dirdatos_nombre_pais_por_ID($datoExistente->pais).'</td><td iddato="'.$datoExistente->id.'"><a href="#" class="ik_dirdatos_boton_editar_pueblo button action">Editar</a><a href="#" class="ik_dirdatos_boton_eliminar_pueblo button action">Eliminar</a></td></tr>';				
			} else if ($dato == 'registros'){
				$activo = 'Activo';
				if (isset($datoExistente->activo)){
					if ($datoExistente->activo == 0){
						$activo = 'Inactivo';
					}
				}
				$listado .= '<tr iddato="'.$datoExistente->id.'" class="ik_dirdatos_busqueda_listado"><td><input type="checkbox" class="select_dato" /></td><td class="ik_dirdatos_iddato">'.$datoExistente->id.'</td><td class="nombre">'.$datoExistente->nombre.'</td><td class="tel">'.$datoExistente->tel.'</td><td class="activo">'.$activo.'</td><td iddato="'.$datoExistente->id.'"><a href="#" class="ik_dirdatos_boton_editar_registro button action">Ver M&aacute;s</a><a href="#" class="ik_dirdatos_boton_eliminar_registro button action">Eliminar</a></td></tr>';				
			}
		}
	    
	    return $listado;
	    
	}
	
	return false;
}


//Cuento la cantidad de registros de un tipo de datos
function ik_dirdatos_cantidad_datos($dato, $where = '', $filtro = false){
    $dato = sanitize_text_field($dato);
	
	if ($where != ''){
		$where = sanitize_text_field($where);
		if ($dato !== 'pueblos'){
			$where= " WHERE nombre_es LIKE '%".$where."%'";
		} else {
			$where= " WHERE nombre LIKE '%".$where."%'";
		}
	}
	
	if (isset($filtro['servicios']) || isset($filtro['paises']) || isset($filtro['estados']) || isset($filtro['pueblos'])){

        if (strpos($where, 'WHERE') !== false) {
            $where .= ' AND ';
        } else {
            $where .= ' WHERE ';
        }
	    
	    if (isset($filtro['servicios'])){
	        if ($dato == 'registros'){
	            $where .= 'id_servicios = '.absint($filtro['servicios']).' AND ';
	        }
	    }
	    if (isset($filtro['pueblos'])){
	        if ($dato == 'registros'){
	            $where .= 'id_pueblo = '.absint($filtro['pueblos']).' AND ';
	        }
	    }
	    if (isset($filtro['paises'])){
	        if ($dato == 'estados' || $dato == 'pueblos'){
	            $where .= 'pais = '.absint($filtro['paises']).' AND ';
	        }
	    }
	    if (isset($filtro['estados'])){
	        if ($dato == 'pueblos'){
	            $where .= 'estado_id = '.absint($filtro['estados']).' AND ';
	        }
	    }
	    $where = substr($where, 0, -5);
	}
	
    if ($dato == 'servicios'){
        $dato = 'servicios';  
    } else if ($dato == 'registros'){
        $dato = 'registros';        
    } else if ($dato == 'estados'){
        $dato = 'estados';        
    } else {
        $dato = 'pueblos';
    }
    
	global $wpdb;
	$queryDato = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_".$dato.$where;
	add_post_meta(1, 'testcant', $queryDato);
	$datosExistente = $wpdb->get_results($queryDato);

	// Si existen datos devuelvo el conteo
	if (isset($datosExistente[0]->id)){ 
        
        $datos_conteo = count($datosExistente);
        return $datos_conteo;
	    
	} else {
    	return false;
	}
}


//Listado de options para select de paises
function ik_dirdatos_listar_paises(){
    
    /*
    cuando era por array
    include('templates/listado_paises.php');
	*/
	
    if ($lang=get_bloginfo("language") == 'es' || $lang=get_bloginfo("language") == 'es_ES' || $lang=get_bloginfo("language") == 'es_PR'){
	    $nombre = 'nombre_es';
	} else {
	    $nombre = 'nombre_en';
	}
	global $wpdb;
	$queryPaises = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_paises ORDER BY ".$nombre." ASC";
	$paises = $wpdb->get_results($queryPaises);

	// Si existen paises devuelvo el conteo
	if (isset($paises[0]->id)){ 
        $listado_paises = '';
		foreach ($paises as $pais){
			$listado_paises .= '<option data_name="'.$pais->$nombre.'" data_id="'.$pais->id.'" value="'.$pais->id.'">'.$pais->$nombre.'</option>';
		}
	} else {
	    $listado_paises = '<option value="0">-</option>';
	}
	
	return $listado_paises;
	
}

//Listado de options para select de estado
function ik_dirdatos_listar_estados($where = NULL){
	
	if ($where != NULL){
	    $where = absint($where);
	    $where = " WHERE pais = '".$where."'";
	} else {
		$where = '';
	}
	
	global $wpdb;
	$queryEstados = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_estados".$where." ORDER BY nombre ASC";
	$estados = $wpdb->get_results($queryEstados);

	if (isset($estados[0]->id)){ 
		$listado_estados = '';
		foreach ($estados as $estado){
		    if ($estado->id != 0){
    			$listado_estados .= '<option data_name="'.$estado->nombre.'" data_id="'.$estado->id.'" value="'.$estado->id.'">'.$estado->nombre.'</option>';
    		} else {
                $listado_estados .= '<option value="0">-</option>';    		    
    		}
		}
		
		return $listado_estados;
	} else {
	    $listado_estados = '<option value="0">-</option>';
	    return $listado_estados;
	}
}

//Listado de options para select de pueblos
function ik_dirdatos_listar_pueblos($where = 0, $pais = NULL){
	$where = absint($where);
	
	if ($where > 0){
	    $where = absint($where);
	    $where = " WHERE estado_id = '".$where."'";
		if ($pais !== NULL){
			$pais = absint($pais);
			$where .= " AND pais = '".$pais."'";
		}
    } else if ($pais !== NULL){
		$pais = absint($pais);
		$where = " WHERE pais = '".$pais."'";
	} else {
		$where = '';
	}
	
	global $wpdb;
	$queryPueblos = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_pueblos".$where." ORDER BY nombre ASC";
	$pueblos = $wpdb->get_results($queryPueblos);
	
	if (isset($pueblos[0]->id)){ 
		$listado_pueblos = '';
		foreach ($pueblos as $pueblo){
			$listado_pueblos .= '<option data_id="'.$pueblo->id.'" value="'.$pueblo->id.'">'.$pueblo->nombre.'</option>';
		}

		return $listado_pueblos;
	} else {
	    $listado_pueblos = '<option value="0">-</option>';
		
	    return $listado_pueblos;
	}
}

//Devolver label de estado o pueblo para saber que tipo de organizacion territorial es
function ik_dirdatos_return_label($dato, $valor_dato){
	$valor_dato = absint($valor_dato);
	
	//Segun idioma busco el nombre de dato indicado
	if ($lang=get_bloginfo("language") == 'es' || $lang=get_bloginfo("language") == 'es_ES' || $lang=get_bloginfo("language") == 'es_PR'){
		$idioma = 'es';
		$labelDefecto = ($dato == 'estado') ? 'Estado' : 'Ciudad';
	} else {
		$idioma = 'en';
		$labelDefecto = ($dato == 'estado') ? 'State' : 'City';
	}			

	$zona = ($dato == 'estado') ? 'zona_1' : 'zona_2';
	
	global $wpdb;
	$queryPais = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_paises WHERE id = ".$valor_dato;
	$pais = $wpdb->get_results($queryPais);

	// Si existen paises devuelvo el conteo
	if (isset($pais[0]->$zona)){ 
		
		$zona_array = ($zona == 'zona_1') ? IK_DIRDATOS_PAIS_REGION_1 : IK_DIRDATOS_PAIS_REGION_2;

        $label = $zona_array[$pais[0]->$zona][$idioma];
		
	} else {
	    $label = $labelDefecto;
	}
	
	return $label;
}



//Listado de options para select de servicios
function ik_dirdatos_listar_servicios(){
	if ($lang=get_bloginfo("language") == 'es' || $lang=get_bloginfo("language") == 'es_ES' || $lang=get_bloginfo("language") == 'es_PR'){
	    $nombre = 'nombre_es';
	} else {
	    $nombre = 'nombre_en';
	}
	global $wpdb;
	$queryServicios = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_servicios ORDER BY nombre_es ASC";
	$servicios = $wpdb->get_results($queryServicios);

	if (isset($servicios[0]->id)){ 
		$listado_servicios = '';
		foreach ($servicios as $servicio){
			$listado_servicios .= '<option data_id="'.$servicio->id.'" value="'.$servicio->id.'">'.$servicio->$nombre.'</option>';
		}
		
		return $listado_servicios;
	} else {
	    return;
	}
}

//Funcion para darle formato al telefono
function ik_dirdatos_formato_tel($phoneNumber, $link = false, $whatsapp = false){
    $phoneFormatting = sanitize_text_field($phoneNumber);
    $phoneFormatting = str_replace('"', '', $phoneFormatting);
    $phoneFormatting = str_replace(',', '', $phoneFormatting);
    $phoneFormatting = str_replace('.', '', $phoneFormatting);
    $phoneFormatting = str_replace('-', '', $phoneFormatting);
    $phoneFormatting = str_replace(')', '', $phoneFormatting);
    $phoneFormatting = str_replace('(', '', $phoneFormatting);
    $phoneFormatting = str_replace('+', '', $phoneFormatting);
    $phoneFormatting = str_replace('_', '', $phoneFormatting);
    $phoneFormatting = str_replace(' ', '', $phoneFormatting);
    $phone = intval($phoneFormatting);
    
    //Para formatear para link de tel: o whatsapp
    if ($link == false){
        if (strlen((string)$phone) < 7 && strlen((string)$phone) > 13){
            $phone_formated = '-';
        } else {
            if (strlen((string)$phone) == 7){ //4102721
                $first3Numbers = substr($phone, 0, 3);
                $last4Numbers = substr($phone, 3, 4);
                $phone_formated = $first3Numbers.'-'.$last4Numbers; // 410-2721
            } else if (strlen((string)$phone) == 9){ // 651097965
                $first3Numbers = substr($phone, 0, 3);
                $second3Numbers = substr($phone, 3, 3);
                $last3Numbers = substr($phone, 6, 3);
                $phone_formated = '('.$first3Numbers.') '.$second3Numbers.'-'.$last3Numbers; // (651) 097-965
            } else if (strlen((string)$phone) == 10){ // 3524102721
                $first3Numbers = substr($phone, 0, 3);
                $second3Numbers = substr($phone, 3, 3);
                $last4Numbers = substr($phone, 6, 4);
                $phone_formated = '('.$first3Numbers.') '.$second3Numbers.'-'.$last4Numbers; // (352) 410-2721
            } else if (strlen((string)$phone) == 11){ //3444497965
                $first2Numbers = substr($phone, 0, 2);
                $areCode = substr($phone, 2, 3);
                $threeNumbers = substr($phone, 5, 3);
                $lastDigits = substr($phone, 8, 3);
                $phone_formated = '+'.$first2Numbers.' ('.$areCode.') '.$threeNumbers.'-'.$lastDigits; //+34 444 097 965
            } else if (strlen((string)$phone) == 12){ //543764616757
                $first2Numbers = substr($phone, 0, 2);
                $areCode = substr($phone, 2, 3);
                $threeNumbers = substr($phone, 5, 3);
                $lastDigits = substr($phone, 8, 4);
                $phone_formated = '+'.$first2Numbers.' ('.$areCode.') '.$threeNumbers.'-'.$lastDigits; //+54 (376) 461-6757
            } else if (strlen((string)$phone) == 13){
                $first2Numbers = substr($phone, 0, 2);
                $thirdDigit = substr($phone, 2, 1);
                $areaCode = substr($phone, 3, 3);
                $threeNumbers = substr($phone, 6, 3);
                $lastDigits = substr($phone, 9, 4);
                $phone_formated = '+'.$first2Numbers.' '.$thirdDigit.' ('.$areaCode.') '.$threeNumbers.'-'.$lastDigits; //+54 9 (376) 461-6757
            } else {
                $phone_formated = '-';
            }
        }
    } else {
		//Es un link de whatsApp
		if ($whatsapp == true){
			//Me aseguro que tenga el # de pais
			if (strlen((string)$phone) == 10){
				//Le agrego el +1 de USA
				$phone_formated = intval('1'.$phone);			
			} else if (strlen((string)$phone) == 9){
				//Le agrego el +34 de Spain
				$phone_formated = intval('34'.$phone);			
			} else {
				$phone_formated = $phone;
			}
		} else {
			//es un numero regular
			$phone_formated = $phone;
		}
    }
    return $phone_formated;
}

//Funcion para chequear que un registro_id de transaccion no esta repetido
function ik_dirdatos_check_registro_id_repeated($id, $donde = 'postmeta'){
    $id = absint($id);
    $donde = sanitize_text_field($donde);
	if ($id == 0){
		return true;
	}

    if ($donde != 'postmeta'){
        global $wpdb;
        $queryRegistro = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_registros WHERE id_registro ='".$id."'";
        $dato = 'id';
    } else {
        global $wpdb;
        $queryRegistro = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key='id_registro' AND meta_value = '".$id."'";
        $dato = 'post_id';
    }
    global $wpdb;
	$registroFound = $wpdb->get_results($queryRegistro);

	if (isset($registroFound[0]->$dato)){ 
		return true;
	} else {
	    return false;
	}    
    
}

//Funcion para obtener IP del registrante
function ik_dirdatos_get_registrante_ip() {

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    
        //check ip from share internet
        
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    
    } else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    
        //to check ip is pass from proxy
        
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    
    } else {
    
        $ip = $_SERVER['REMOTE_ADDR'];
    
    }
    
    return $ip;
}

//Funcion para crear un id_registro
function ik_dirdatos_create_id_register(){
    //Creo un registroID basado en el tiempo, la IP y un # aleatorio
    $registro_id = str_replace('.', '', ik_dirdatos_get_registrante_ip());
    $registro_id = str_replace(':', '', $registro_id);
    $registro_id = strtotime(date('Y-m-d H:i:s')).$registro_id.mt_rand(100, 999);
    
    return $registro_id;
}

//Funcion para devolver el producto asociado al plugin
function ik_dirdatos_producto_asociado(){
    $producto_asociado = absint(get_option('ik_dirdatos_producto_asociado'));
    
    return $producto_asociado;
}

//Funcion para devolver listado de opciones de productos para pagar registro
function ik_dirdatos_lista_options_productos(){

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );

    $option_data = new WP_Query( $args );
    $options_data_list = '';
    while ( $option_data->have_posts() ) : $option_data->the_post();
        global $product;
        $options_data_list .= '<option value="'.get_the_id().'">'.get_the_title().'</option>';
    endwhile;

    wp_reset_query();
    
    return $options_data_list;
}

//Funcion para devolver listado de opciones de productos para pagar registro
function ik_dirdatos_lista_options_paginas(){

    $args = array(
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );

    $option_data = new WP_Query( $args );
    $options_data_list = '';
    while ( $option_data->have_posts() ) : $option_data->the_post();
        $options_data_list .= '<option value="'.get_the_id().'">'.get_the_title().'</option>';
    endwhile;

    wp_reset_query();
    
    return $options_data_list;
}


//Función para validar limite de registros por pueblo
function ik_dirdatos_validar_limite_registros($pueblo_name){
	$limite = ik_dirdatos_get_cant_registros_por_pueblo();
	$pueblo_id = ik_dirdatos_get_pueblo_id_by_name($pueblo_name);
	
	global $wpdb;
	$queryRegistros = "SELECT *  FROM ".$wpdb->prefix."ik_dirdatos_registros WHERE id_pueblo = ".$pueblo_id. ' GROUP BY (nombre)';
	$registros = $wpdb->get_results($queryRegistros);
	
	if (isset($registros[0]->id)){

		if (count($registros) == $limite || count($registros) > $limite){
			return false;
		} else {
			return true;
		}
	} else {
		return true;
	}
	
}

//Funcion para devolver métodos de pago activos
function ik_dirdatos_lista_metodos_activos_pago(){

    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $enabled_gateways = [];
    $options_data_list = '';
    
    if( $gateways ) {
        foreach( $gateways as $gateway_id => $gateway ) {
    
            if( $gateway->enabled == 'yes' ) {
                $options_data_list .= '<option value="'.$gateway_id.'">'.$gateway->get_title().'</option>';
            }
        }
    }
    return $options_data_list;
}

//Creo shortcode/tag de Contact 7 para IDdeRegistro para validar chequeo de registro y controlar pagos
if (class_exists('WPCF7_ContactForm')){

    add_action( 'wpcf7_init', 'ik_dirdatos_cf7_tag_id_registro' );
     
    function ik_dirdatos_cf7_tag_id_registro() {
      wpcf7_add_form_tag( 'id_registro', 'ik_dirdatos_cf7_tag_id_registro_handler' );
    }
     
    function ik_dirdatos_cf7_tag_id_registro_handler( $tag ) {
        $registroID = ik_dirdatos_create_id_register();
        $inputRegistroID = '<div class="ik_dirdatos_id_registro"><input type="hidden" class="ik_dirdatos_id_registro" name="id_registro" value="'.$registroID.'" /></div><input type="hidden" class="ik_dirdatos_pueblo_id" name="pueblo_id" value="0" />';
        
        return $inputRegistroID;
    }
    
    //Accion para validar que el registro tiene pueblo definido
    add_filter('wpcf7_validate_select*', 'ik_dirdatos_cf7_validar_crear_registro', 20, 2);
    function ik_dirdatos_cf7_validar_crear_registro($result, $tag) {
        $tag = new WPCF7_FormTag($tag);
        $result['valid'] = true;

        if ('your-pueblo' == $tag->name) {
            if (sanitize_text_field($_POST['your-pueblo']) == '-' || sanitize_text_field($_POST['your-pueblo']) == ''){
                $result->invalidate($tag, __( "The field is required.", 'contact-form-7') );
            } else {
				if (ik_dirdatos_validar_limite_registros(sanitize_text_field($_POST['your-pueblo'])) == false){
					$result->invalidate($tag, __("Lo lamento. El límite de registros para este pueblo o ciudad ya fue alcanzado.", 'ik-directorio-datos'));
				}
				
			}
        }
		
        return $result;
    }
    
    //Valido el valor ID de registro asegurandome que no este repetido
    add_action( 'wpcf7_before_send_mail', 'ik_dirdatos_validar_id_registro' );
    function ik_dirdatos_validar_id_registro($WPCF7_ContactForm){
        $wpcf7 = WPCF7_ContactForm :: get_current() ;
        $submission = WPCF7_Submission :: get_instance();
        if ($submission){
            $posted_data = $submission->get_posted_data();
          
            //Si no hay nada no hago nada
            if ( empty ($posted_data))
            return;
            
			if (isset($posted_data['id_registro'])){
            	$registro_id = $posted_data['id_registro'];
			} else{
				$registro_id = 0;
			}
            //Chequeo si el id de registro se encuentra repetido
            $registro_id_repeated = ik_dirdatos_check_registro_id_repeated($registro_id);

            if ($registro_id_repeated == true){
                $registro_id = ik_dirdatos_create_id_register();
                
                $mail = $WPCF7_ContactForm->prop('mail');
                $mail['id_registro'] = $registro_id;
                
                // Salvo todo
                $WPCF7_ContactForm->set_properties( array("mail" => $mail)) ;   
                
            }
            return $WPCF7_ContactForm ;
        }
    }
    
    
    //Accion para crear registro pendiente de publicar
    add_action( 'wpcf7_mail_sent', 'ik_dirdatos_procesar_nuevo_registro' );
    
    function ik_dirdatos_procesar_nuevo_registro($contact_form){
        
        $submission = WPCF7_Submission :: get_instance();
        $campos = $submission->get_posted_data();
        
        if (isset($campos['id_registro']) && isset($campos['pueblo_id'])){

            $registro_id = absint($campos['id_registro']);
            $pueblo_id = absint($campos['pueblo_id']);

            if ($pueblo_id != 0 && $registro_id != 0){
				
				$data_campos  = array (
					'nombre' => $campos['your-name'],
					'id_pueblo' => $pueblo_id,
					'id_servicios' => $campos['services'],
					'tel' => $campos['your-phone'],
					'whatsapp' => $campos['your-mobilephone'],
					'email' => $campos['your-email'],
					'id_registro' => $registro_id,
					'status' => 0,
				);
				
				if (isset($campos['details'])){
					$data_campos['details'] = $campos['details'];				
				}
				
				$url_pago = ik_dirdatos_crear_orden_woocommerce($data_campos);

            }
        }
            
    }
}

//Creacion de pedido de Woocommerce con datos de registroID en pedido para publicar registro en directorio
function ik_dirdatos_crear_orden_woocommerce($datos_orden){
    
    //ID de producto asociado al plugin
    if (get_option('ik_dirdatos_producto_asociado') !== false){
        $producto_id = ik_dirdatos_producto_asociado();
        if ($producto_id != 0){
            global $woocommerce;
            
            $datos_cliente = array(
              'company'    => $datos_orden['nombre'],
              'email'      => $datos_orden['email'],
              'phone'      => $datos_orden['tel'],
            );            
        
            // Creo la orden
            $order = wc_create_order();
            
            // Agrego el producto asociado al plugin
            $producto_asociado_orden = wc_get_product( $producto_id );
            $order->add_product( $producto_asociado_orden, 1 );
            
            //Actualizo el total con el producto agregado
            $order->calculate_totals();
            $order->set_address($datos_cliente, 'billing');
            $order->update_status( 'pending' );
    
            //Guardo los datos
            $order_id = $order->save();
            
			//Detalles de servicios
			if (isset($datos_orden['details'])){
				add_post_meta($order_id, 'details', $datos_orden['details']);
			}		
			
            //Agrego el ID de registro a la orden
            add_post_meta($order_id, 'id_registro', $datos_orden['id_registro']);

			//Guardo datos del registro del directorio
            add_post_meta($order_id, 'datos_directorio', $datos_orden);

			//Agrego el ID de registro a la orden
            add_post_meta($order_id, 'id_registro', $datos_orden['id_registro']);
    
            //Creo un carrito y le agrego el producto
            if ( is_null( WC()->cart ) ) {
                wc_load_cart();
            }
            
			WC()->cart->empty_cart();
            
            //Agrego producto al carrito
            WC()->cart->add_to_cart( $producto_id );
    
    
            //Redirijo al pago
            $metodo_pago = get_option('ik_dirdatos_metodo_pago');

            if ($metodo_pago !== false){
               
				//Consigo el URL de pago
				$orderKey = get_post_meta($order_id, '_order_key', true);

    
    			$url_de_pago = wc_get_checkout_url().'/'.get_option('woocommerce_checkout_pay_endpoint').'/'.$order_id.'/?pay_for_order=true&key='.$orderKey;


                // Redirecciono a checkout
				add_post_meta($order_id, 'url_pago', $url_de_pago);
				add_post_meta($datos_orden['id_registro'], 'url_pago', $url_de_pago);
				return $url_de_pago;
            }
        }
    }
    
}

//Funcion para devolver id de pueblo basado en su nombre
function ik_dirdatos_get_pueblo_id_by_name($name_pueblo){
    $name_pueblo = sanitize_text_field($name_pueblo);
    
    global $wpdb;
    $queryPueblo = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_pueblos WHERE nombre LIKE '".$name_pueblo."'";

	$pueblo = $wpdb->get_results($queryPueblo);

	if (isset($pueblo[0]->id)){ 
	    return $pueblo[0]->id;
	} else {
	    return false;
	}
    
}

//Funcion para devolver Id de servicio basado en su nombre
function ik_dirdatos_get_service_id_by_name($nombre_servicio){
    $nombre_servicio = sanitize_text_field($nombre_servicio);
    
    global $wpdb;
    $queryServicio = "SELECT * FROM ".$wpdb->prefix."ik_dirdatos_servicios WHERE nombre_en LIKE '".$nombre_servicio."' OR nombre_es LIKE '".$nombre_servicio."'";
        $dato = 'post_id';
	$servicio = $wpdb->get_results($queryServicio);

	if (isset($servicio[0]->id)){ 
	    return $servicio[0]->id;
	} else {
	    return false;
	}
    
}

//function para subir registro de directorio automaticamente luego del pago completado
add_action('save_post','ik_dirdatos_subir_registro_cuando_pagado');
function ik_dirdatos_subir_registro_cuando_pagado($post_id){
	$post = get_post( $post_id );
	
	//Me fijo que sea un pedido
    if ($post->post_type != 'shop_order'){
        return;
    } else {
		//Chequeo que sea un pedido que requiera chequeo
		$statusOrder = get_post_status($post_id);
			
		if ($statusOrder == 'wc-completed' || $statusOrder == 'wc-processing'){

            //Me fijo si fue publicado
			$publicado = get_post_meta($post_id, 'ik_dirdatos_registro_publicado', true);

			if (intval($publicado) != 1){
				//Creo el registro
				add_post_meta($post_id, 'ik_dirdatos_registro_publicado', '1');
				$registro = ik_dirdatos_crear_registro($post_id);
			}
		}
	}
}

//function para subir registro de directorio automaticamente luego del pago completado desde Thank you page
add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order' );
function custom_woocommerce_auto_complete_order( $order_id ) { 
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );

    if( $order->has_status( 'processing' ) ) {
        $order->update_status( 'completed' );
    }
    if( $order->has_status( 'completed' ) ) {
		//Me fijo si fue publicado
		$publicado = get_post_meta($order_id, 'ik_dirdatos_registro_publicado', true);

		if (intval($publicado) != 1){
			//Creo el registro
			add_post_meta($order_id, 'ik_dirdatos_registro_publicado', '1');
			$registro = ik_dirdatos_crear_registro($order_id);
		}
        
    }
}

//Funcion para crer registro
function ik_dirdatos_crear_registro($id_orden){
    $id_orden = absint($id_orden);

    //Valor Return por default
    $registro_creado = false;
    
    $id_registro = absint(get_post_meta($id_orden, 'id_registro', true));

        
	$datosDirectorio = get_post_meta($id_orden, 'datos_directorio', true);
	
	//Si el dato es correcto creo el registro
	if (is_array($datosDirectorio)){
					
		$nombre_empresa = sanitize_text_field($datosDirectorio['nombre']);
		$nombre_empresa = str_replace('\\', '', $nombre_empresa);
		
		if (is_array($datosDirectorio['id_servicios'])){
			
			foreach($datosDirectorio['id_servicios'] as $servicio){
				//Verifico el ID
				$servicio_id = ik_dirdatos_get_service_id_by_name($servicio);

				global $wpdb;
				$data_campos  = array (
					'nombre' => $nombre_empresa,
					'id_pueblo' => absint($datosDirectorio['id_pueblo']),
					'id_servicios' => $servicio_id,
					'tel' => ik_dirdatos_formato_tel($datosDirectorio['tel']),
					'whatsapp' => ik_dirdatos_formato_tel($datosDirectorio['whatsapp']),
					'email' => sanitize_email($datosDirectorio['email']),
					'id_registro' => $id_registro,
				);
				
				$descripcion = get_post_meta($id_orden, 'details', true);
				
				if ($descripcion){
					$data_campos['descripcion'] = sanitize_textarea_field($descripcion);
				}
		
				$tabla = $wpdb->prefix.'ik_dirdatos_registros';
				$rowResult = $wpdb->insert($tabla,  $data_campos , $format = NULL);
			$registro_creado = $wpdb->insert_id;
			}
		} else {
			//Verifico el ID
			$servicio_id = ik_dirdatos_get_service_id_by_name($datosDirectorio['id_servicios']);

			global $wpdb;
			$data_campos  = array (
				'nombre' => $nombre_empresa,
				'id_pueblo' => absint($datosDirectorio['id_pueblo']),
				'id_servicios' => $servicio_id,
				'tel' => ik_dirdatos_formato_tel($datosDirectorio['tel']),
				'whatsapp' => ik_dirdatos_formato_tel($datosDirectorio['whatsapp']),
				'email' => sanitize_email($datosDirectorio['email']),
				'id_registro' => $id_registro,
				'order_id' => $id_orden,		
			);
			
			$descripcion = get_post_meta($id_orden, 'details', true);
			
			if ($descripcion){
				$data_campos['descripcion'] = sanitize_textarea_field($descripcion);
			}
	
			$tabla = $wpdb->prefix.'ik_dirdatos_registros';
			$rowResult = $wpdb->insert($tabla,  $data_campos , $format = NULL);
			$registro_creado = $wpdb->insert_id;
			
			//Agrego el registro a la orden
			add_post_meta($id_orden, 'registro_id', $registro_creado);
		}
		
	}
	
    return $registro_creado;
}

//Agrego campos de opciones en cf7
add_action( 'wp_footer', 'ik_dirdatos_agregar_datos_cf7' );
function ik_dirdatos_agregar_datos_cf7(){
    echo '<script>
    jQuery(document).ready(function ($) {
        var ik_dirdatos_paises = \''.ik_dirdatos_listar_paises().'\';
        var ik_dirdatos_servicios = \''.ik_dirdatos_listar_servicios().'\';
		var pueblos_puertorico = \''.ik_dirdatos_listar_pueblos(0, 1).'\';
		var limite_texto_es = "Lo lamento. El límite de registros para este pueblo o ciudad ya fue alcanzado.";
		var limite_texto = "'.__("Lo lamento. El límite de registros para este pueblo o ciudad ya fue alcanzado.", 'ik-directorio-datos').'";
		
		setInterval(function(){ 
			jQuery(".wpcf7-not-valid-tip").each(function() {
				var texto_limite = jQuery(this).text().replace(limite_texto_es, limite_texto);
				jQuery(this).text(texto_limite);
			});
		}, 100);
        
        jQuery("#ik_dirdatos_ver_registros .wpcf7-submit").attr("id", "ik_dirdatos_ver_registros_boton");
        jQuery("#ik_dirdatos_ver_registros .wpcf7-submit").removeClass("wpcf7-submit");
        jQuery("#ik_dirdatos_ver_registros .wpcf7-submit").removeClass("wpcf7-form-control");
        
        jQuery("select.ik_dirdatos_select_pais").empty();
        jQuery("select.ik_dirdatos_select_pais").append(ik_dirdatos_paises);
		jQuery("select.ik_dirdatos_select_pais").val("1");
		jQuery("select.ik_dirdatos_select_pais option[data_name=\'Puerto Rico\']").attr("selected", true);

        setInterval(function(){
            jQuery(".wpcf7-form select.wpcf7-select option").each(function() {
                var texto_option= jQuery(this).text();
                jQuery(this).val(texto_option);
            });
        }, 300);
          
        jQuery("select.ik_dirdatos_select_servicios").empty();
        jQuery("select.ik_dirdatos_select_servicios").append(ik_dirdatos_servicios);
        
		
    	function ik_dirdatos_js_select_pais(elemento){
    		var pais_datavalue = elemento.val();
    		var pais_value = elemento.find("option[value=\'"+pais_datavalue+"\']").attr("data_id");
    		var data = {
    			action: "ik_dirdatos_ajax_get_estados_de_pais",
    			"post_type": "post",
    			"pais_value": pais_value,
    		};  
    		
    		jQuery.post( "' . admin_url('admin-ajax.php') . '", data, function(response) {
    			if (response){
    				var data = JSON.parse(response);
    				if (data != \'<option value="0">-</option>\'){
    					jQuery("#ik_dirdatos_select_estado").fadeIn(500);						
    					jQuery("select.ik_dirdatos_select_estado").empty();
    					jQuery("select.ik_dirdatos_select_estado").append(data);
						ik_dirdatos_js_actualizar_label("estado");
    				} else {
    					jQuery("#ik_dirdatos_select_estado").fadeOut(500);	
    					jQuery("select.ik_dirdatos_select_estado").empty();						
    				}
    				setTimeout(function(){
    					var value_estado_default = jQuery("#ik_dirdatos_select_estado .ik_dirdatos_select_estado option:first-child").attr("value");
    					jQuery("#ik_dirdatos_select_estado .ik_dirdatos_select_estado").val(value_estado_default);
    					ik_dirdatos_js_actualizar_pueblos();
    				}, 600);
    			}        
    		});
    	}
        jQuery(".wpcf7-form").on("change","select.ik_dirdatos_select_pais", function(e){
        		e.preventDefault();
    		ik_dirdatos_js_select_pais(jQuery(this));
        });
        
        jQuery(".wpcf7-form").on("change","select.ik_dirdatos_select_estado", function(e){
        		e.preventDefault();
    		ik_dirdatos_js_actualizar_pueblos();
        });
        
    	ik_dirdatos_js_select_pais(jQuery(".wpcf7-form select.ik_dirdatos_select_pais"));
    	
    	function ik_dirdatos_js_asignar_pueblo_id(elemento){
            var pueblo_id = jQuery(elemento).find("option:selected").attr("data_id");
            jQuery(".ik_dirdatos_pueblo_id").val(pueblo_id);    	    
    	}
    	
        function ik_dirdatos_js_actualizar_pueblos(){
    		var estado_datavalue = jQuery("#ik_dirdatos_select_estado .ik_dirdatos_select_estado").val();
    		var estado_value = jQuery("#ik_dirdatos_select_estado .ik_dirdatos_select_estado").find("option[value=\'"+estado_datavalue+"\']").attr("data_id");
    		var pais_datavalue = jQuery(".wpcf7-form select.ik_dirdatos_select_pais").val();
    		var pais_value = jQuery(".wpcf7-form .ik_dirdatos_select_pais").find("option[value=\'"+pais_datavalue+"\']").attr("data_id");
    		
			var data = {
				action: "ik_dirdatos_ajax_get_pueblos_de_pais",
				"post_type": "post",
				"estado_value": estado_value,
				"pais_value": pais_value,
			};  
			
			jQuery.post( "' . admin_url('admin-ajax.php') . '", data, function(response) {
				if (response){
					var data = JSON.parse(response);
					ik_dirdatos_js_actualizar_label("pueblo");
					jQuery(".your-pueblo .ik_dirdatos_select_pueblo").empty();
					jQuery(".your-pueblo .ik_dirdatos_select_pueblo").append(data);  
				}
			});
    	}

        function ik_dirdatos_js_actualizar_label(dato){
			
			if(dato == "estado" || dato == "pueblo"){
				var pais_datavalue = jQuery(".wpcf7-form select.ik_dirdatos_select_pais").val();
				var valor_dato = jQuery(".wpcf7-form .ik_dirdatos_select_pais").find("option[value=\'"+pais_datavalue+"\']").attr("data_id");
				
				var data = {
					action: "ik_dirdatos_ajax_get_label",
					"post_type": "post",
					"dato": dato,
					"valor_dato": valor_dato,
				};  
				
				jQuery.post( "' . admin_url('admin-ajax.php') . '", data, function(response) {
					if (response){
						var data = JSON.parse(response);
						if(dato == "estado"){
							jQuery("#ik_dirdatos_select_estado .field-name").html(data);  
						} else if(dato == "pueblo"){
							if(valor_dato === "1" && data == "Ciudad"){
								jQuery(".wpcf7-form .your-pueblo .field-name").text("Pueblo");								
							} else {
								jQuery(".wpcf7-form .your-pueblo .field-name").html(data);
							}
						}
					}
				});				
			}
    	}
        
    	setInterval(function(){ 
    	   ik_dirdatos_js_asignar_pueblo_id(".your-pueblo .ik_dirdatos_select_pueblo");
    	}, 500);
	
    	ik_dirdatos_js_select_pais(jQuery(this));

        jQuery("#ik_dirdatos_ver_registros_boton").on( "click", function() {
			
            var pais_datavalue = jQuery(".wpcf7 .your-country select").val();
            var pueblo_datavalue = jQuery(".wpcf7 .your-pueblo select").val();
			
            var pais_value = jQuery(".wpcf7 .your-country select").find("option[value=\'"+pais_datavalue+"\']").attr("data_id");
            var pueblo_value = jQuery(".wpcf7 .your-pueblo select").find("option[value=\'"+pueblo_datavalue+"\']").attr("data_id");

			var services_value = "";
	        jQuery(".wpcf7 .services select option:checked").each(function() {
				services_value = services_value+jQuery(this).attr("data_id")+",";
			});
			var services_value = services_value.substring(0, services_value.length - 1);

            var data = {
				action: "ik_dirdatos_ajax_get_registros_datos",
				"post_type": "post",
				"pais_value": pais_value,
				"services_value": services_value,
				"pueblo_value": pueblo_value,
			};  

    		jQuery.post( "' . admin_url('admin-ajax.php') . '", data, function(response) {
    			if (response){
    				var data = JSON.parse(response);
    				jQuery("#ik_dirdatos_registros_encontrados").remove();
    				jQuery(\'<div id="ik_dirdatos_registros_encontrados">\'+data+\'</div>\').insertAfter("#ik_dirdatos_ver_registros");
    		    }
            });
            
            return false;
        });
        
        document.addEventListener( "wpcf7mailsent", function( event ) {
        
            var registro_id = jQuery("#ik_dirdatos_register input.ik_dirdatos_id_registro").val();

            var data = {
				action: "ik_dirdatos_ajax_get_payment_link",
				"post_type": "post",
				"registro_id": registro_id,
			};  

    		jQuery.post( "' . admin_url('admin-ajax.php') . '", data, function(response) {
    			if (response){
    			    var data = JSON.parse(response);
					jQuery("input.ik_dirdatos_id_registro").val(data.id_registro);
					location = data.location;
    		    }
            });

        }, false );

    });    
    </script>'; 
}


//Shortcode para política de privacidad
function dirdatos_politica_privacidad(){
	$cookie_name = get_bloginfo( 'name' ).'Cookie';
	$cookie_name = preg_replace("/[^a-zA-Z0-9]+/", "", $cookie_name);
	
	$id_pagina_privacidad = absint(get_option('ik_dirdatos_privacidad'));
	if ($id_pagina_privacidad != false){
		$texto_popup = 'Usamos cookies para asegurarnos que tengas la mejor experiencia en nuestro sitio web. Si contin&uacute;as navegando, asumimos que est&aacute;s de acuerdo.';
		$text_boton = 'Aceptar';

		$privacy_policy = '<style>
			a.ik_dirdatos_politica_privacidad {
				text-align: center;
				margin: 0 auto;
				display: block;
				color: #fff;
			}
			#ik_dirdatos_privacy_policy_popup {
				position: fixed;
				bottom: 20px;
				right: 0;
				z-index: 99999;
				width: 100%;
				color: #fff;
			}
			.ik_dirdatos_privacy_policy_popup_content{
				background: #0C46AA;
				padding: 25px;
				width: 90%;
				border-radius: 12px;
				margin: 0 auto;
				display: block;
				border: 1px solid #fff;
			}
			.ik_dirdatos_privacy_policy_popup_content a {
				color: #fff;
				background: #F5821F;
				border-radius: 5px;
				padding: 5px 12px;
				margin-right: 5px;
			}
			.ik_dirdatos_privacy_policy_popup_content span {
				padding-bottom: 21px;
				display: block;
			}
			@media (max-width: 767px){
				.ik_dirdatos_privacy_policy_popup_content span{
				font-size: 16px;
				}
				.ik_dirdatos_privacy_policy_popup_content a {
					padding: 5px 10px! important;
					margin-right: 2px! important;
					display: inline-block;
					text-align: center;
					font-size: 14px;
				}
			}
			@media (min-width: 1080px){
				.ik_dirdatos_privacy_policy_popup_content {
					display: table! important;
				}
				.ik_dirdatos_privacy_policy_popup_content span {
					margin-right: 1%;
					max-width: 77%;
					float: left;
					margin-top: 15px;
				}
				.ik_dirdatos_privacy_policy_popup_buttons{
					margin-left: 1%;
					max-width: 21%;
					float: left;
					text-align: center;
				}
				.ik_dirdatos_privacy_policy_popup_content a {
					line-height: 1;
					width: 100%;
    				max-width: 300px;
					display: inline-table;
					text-align: center;
					margin: 7px! important;
					padding: 8px! important;
				}
			}
			</style>';

		if (function_exists('pll_get_post') && function_exists('pll_current_language')) {
			$lenguajeActual = pll_current_language('locale'); 
			$id_pagina_traduccion = pll_get_post($id_pagina_privacidad, $lenguajeActual);
			$privacy_policy .= '<a class="ik_dirdatos_politica_privacidad" href="'.get_the_permalink($id_pagina_traduccion).'" target="_blank">'.get_the_title($id_pagina_traduccion).'</a>';
			$button_privacy = $privacy_policy;
			if ($lenguajeActual == 'en_US'){
				$texto_popup = 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.';
				$text_boton = 'Accept';
			}
		} else {
			$privacy_policy .= '<a class="ik_dirdatos_politica_privacidad" href="'.get_the_permalink($id_pagina_privacidad).'" target="_blank">'.get_the_title($id_pagina_privacidad).'</a>';
			$button_privacy = $privacy_policy;
		}
		if (!is_page($id_pagina_traduccion) && !is_page($id_pagina_privacidad)){
			//If cookie doesn't exist
			if (!isset($_COOKIE[$cookie_name])) {
				$privacy_policy .= '<div id="ik_dirdatos_privacy_policy_popup">
						<div class="ik_dirdatos_privacy_policy_popup_content">
							<span>'.$texto_popup.'</span>
							<div class="ik_dirdatos_privacy_policy_popup_buttons">
								<a href="#" id="ik_dirdatos_privacy_policy_accept">'.$text_boton.'</a>
								<a href="'.get_the_permalink($id_pagina_traduccion).'" id="ik_dirdatos_privacy_policy_page">'.get_the_title($id_pagina_traduccion).'</a>
							</div>
						</div>
						<script>
						function setCookie(name,value,days) {
							var expires = "";
							if (days) {
								var date = new Date();
								date.setTime(date.getTime() + (days*24*60*60*1000));
								expires = "; expires=" + date.toUTCString();
							}
							document.cookie = name + "=" + (value || "")  + expires + "; path=/";
						}
						jQuery(".ik_dirdatos_privacy_policy_popup_buttons").on("click", "#ik_dirdatos_privacy_policy_accept", function(e){
							e.preventDefault();
							setCookie("'.$cookie_name.'", "Cookie Accepted", "7");
							jQuery("#ik_dirdatos_privacy_policy_popup").fadeOut(600);
							return false;
						});
						</script>';
			}
		}
		return $privacy_policy;
	}

	return;
}
add_shortcode('dirdatos_politica_privacidad', 'dirdatos_politica_privacidad');



function ik_dirdatos_listado_paginas($pagina, $pageDataURL, $cantidadLimite, $listado_todos){
	$output_paginado = '';
	$pageDataURL = sanitize_text_field($pageDataURL);
	$pagina = absint($pagina);
	$cantidadLimite = absint($cantidadLimite);
	$listado_todos = absint($listado_todos);
	$total_paginas = intval($listado_todos / $cantidadLimite) + 1;
	
	if ($listado_todos > $cantidadLimite && $pagina <= $total_paginas){
		$output_paginado .= '<div class="ik_dirdatos_paginas">';
		
		//Habilito n de paginas a mostrar
		$mitadlistado = intval($total_paginas/2);
		
		$pagSiguiente = $pagina+1;
		$pagAnterior = $pagina-1;
		
		$paginasHabilitadas[] = 1;
		$paginasHabilitadas[] = 2;
		$paginasHabilitadas[] = $total_paginas;
		$paginasHabilitadas[] = $total_paginas - 1;
		$paginasHabilitadas[] = $mitadlistado - 2;
		$paginasHabilitadas[] = $mitadlistado - 1;
		$paginasHabilitadas[] = $mitadlistado;
		$paginasHabilitadas[] = $mitadlistado + 1;
		$paginasHabilitadas[] = $mitadlistado + 2;
		$paginasHabilitadas[] = $pagina+3;
		$paginasHabilitadas[] = $pagina+2;
		$paginasHabilitadas[] = $pagSiguiente;
		$paginasHabilitadas[] = $pagina;
		$paginasHabilitadas[] = $pagAnterior;
		
		if ($total_paginas > 10 && $pagina > 1){
			$output_paginado .= '<a href="'.get_site_url().'/wp-admin/admin.php?page='.$pageDataURL.'&listado='.$pagAnterior.'"><</a>';
			$output_final = '<a href="'.get_site_url().'/wp-admin/admin.php?page='.$pageDataURL.'&listado='.$pagSiguiente.'">></a>';
		} else if ($total_paginas > 10) {
			$output_final = '<a href="'.get_site_url().'/wp-admin/admin.php?page='.$pageDataURL.'&listado='.$pagSiguiente.'">></a>';
		} else {
			$output_final = '';
		} 
		
		for ($i = 1; $i <= $total_paginas; $i++) {
			
			$mostrar_pagina = false;
			
			//Muestro solo las paginas habilitadas
			if (in_array($i, $paginasHabilitadas)) {
				$mostrar_pagina = true;
			}
			
			if ($mostrar_pagina == true){
				if ($pagina == $i){
					$PageNActual = 'class="actual_pagina"';
				} else {
					$PageNActual = "";
				}
				$output_paginado .= '<a '.$PageNActual.' href="'.get_site_url().'/wp-admin/admin.php?page='.$pageDataURL.'&listado='.$i.'">'.$i.'</a>';
			}
		}
		$output_paginado .= $output_final.'</div>';
	}
	
	return $output_paginado;
}

//devuelvo selector de regiones o organizaciones zonales
function ik_dirdatos_selectores_zona($n_zona, $seleccion = NULL){
	$opciones_zonas = ($n_zona == 1) ? IK_DIRDATOS_PAIS_REGION_1 : IK_DIRDATOS_PAIS_REGION_2;
	$opciones_zonas_n = ($n_zona == 1) ? 'zona_1' : 'zona_2';
	$opciones_zonas_n = ($seleccion == NULL) ? 'nueva_'.$opciones_zonas_n : $opciones_zonas_n;
	$output = '<select name="'.$opciones_zonas_n.'[]">';
	
	
	foreach ($opciones_zonas as $value => $opcion){
		$seleccionado = ($seleccion == $value) ? 'selected' : '';
		$output .= '<option '.$seleccionado.' value="'.$value.'">'.$opcion['es'].'</option>';
	}
	
	$output .= '</select>';

	return $output;
}