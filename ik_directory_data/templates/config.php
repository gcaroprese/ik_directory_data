<?php
/*

Template: Config del plugin
Update: 11/08/2021
Author: Gabriel Caroprese

*/

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['cantlistados']) ){
    
        // Levanto las variables que submití en el form
        
        $cantListados = absint($_POST['cantlistados']);
        
        //Me aseguro que el limite al menos sea uno
        if ($cantListados < 0){
            $cantListados = 1;
        }
        
        if (get_option('ik_dirdatos_config_cant_listados') == NULL){
            
            add_option('ik_dirdatos_config_cant_listados', $cantListados);
    
        } else {
                        
            update_option('ik_dirdatos_config_cant_listados', $cantListados);
                
        }    
        $result = 'Guardado';
    }
    if (isset($_POST['cantregistros']) ){
    
        // Levanto las variables que submití en el form
        
        $cantRegistros = absint($_POST['cantregistros']);
        
        //Me aseguro que el limite al menos sea uno
        if ($cantRegistros < 0){
            $cantRegistros = 1;
        }
        
        if (get_option('ik_dirdatos_config_cant_registros') == NULL){
            
            add_option('ik_dirdatos_config_cant_registros', $cantRegistros);
    
        } else {
                        
            update_option('ik_dirdatos_config_cant_registros', $cantRegistros);
                
        }    
        $result = 'Guardado';
    }
    if (isset($_POST['producto_asociado']) ){
    
        // Levanto las variables que submití en el form
        
        $producto_asociado = absint($_POST['producto_asociado']);
        
        if (get_option('ik_dirdatos_producto_asociado') == NULL){
            
            add_option('ik_dirdatos_producto_asociado', $producto_asociado);
    
        } else {
                        
            update_option('ik_dirdatos_producto_asociado', $producto_asociado);
                
        }    
        $result = 'Guardado';
    }
    if (isset($_POST['politica_privacidad']) ){
    
        // Levanto las variables que submití en el form
        
        $politica_privacidad = absint($_POST['politica_privacidad']);
        
        if (get_option('ik_dirdatos_privacidad') == NULL){
            
            add_option('ik_dirdatos_privacidad', $politica_privacidad);
    
        } else {
                        
            update_option('ik_dirdatos_privacidad', $politica_privacidad);
                
        }    
        $result = 'Guardado';
    }
    if (isset($_POST['metodo_pago']) ){
    
        // Levanto las variables que submití en el form
        
        $metodo_pago = sanitize_text_field($_POST['metodo_pago']);
        
        if (get_option('ik_dirdatos_metodo_pago') == NULL){
            
            add_option('ik_dirdatos_metodo_pago', $metodo_pago);
    
        } else {
                        
            update_option('ik_dirdatos_metodo_pago', $metodo_pago);
                
        }    
        $result = 'Guardado';
    }
} else {
    $cantListados = ik_dirdatos_get_cant_listados();
    $cantRegistros = ik_dirdatos_get_cant_registros_por_pueblo();
    $result = '';
}

?>
<div id="ik_dir_datos_panel_config">
    <h2>Config - IK Directorio Datos</h2>
    <form action="" method="post" id="db-woomoodle-form" enctype="multipart/form-data" autocomplete="no">
        <p>
            <label>
                <span>Cantidad de listados aleatorios a mostrar</span><br/>
                <input required type="number" name="cantlistados" value="<?php echo $cantListados; ?>" autocomplete="off" />
            </label>  
        </p>
        <p>
            <label>
                <span>Cantidad de registros por pueblo</span><br/>
                <input required type="number" name="cantregistros" value="<?php echo $cantRegistros; ?>" autocomplete="off" />
            </label>  
        </p>
        <p>
            <label>
                <span>Producto asociado para pago de registro</span><br/>
                <select id="ik_dirdatos_producto_asociado" name="producto_asociado"><?php echo ik_dirdatos_lista_options_productos(); ?></select>
            </label>  
        </p>
        <p>
            <label>
                <span>Política de Privacidad (usar shortocode: [dirdatos_politica_privacidad])</span><br/>
                <select id="ik_dirdatos_privacidad" name="politica_privacidad"><?php echo ik_dirdatos_lista_options_paginas(); ?></select>
            </label>  
        </p> 
        <p>
            <label>
                <span>M&eacute;todo de Pago</span><br/>
                <select id="ik_dirdatos_metodo_pago" name="metodo_pago"><?php echo ik_dirdatos_lista_metodos_activos_pago(); ?></select>
            </label>  
        </p> 
    	<input type="submit" class="button" value="Guardar">
    	<p id="ik_dato_guardado"><?php echo $result; ?></p>
    </form>
</div>
<script>
    jQuery('#ik_dirdatos_producto_asociado').val('<?php echo ik_dirdatos_producto_asociado(); ?>')
</script>
<script>
    jQuery('#ik_dirdatos_metodo_pago').val('<?php echo get_option('ik_dirdatos_metodo_pago'); ?>')
</script>
<script>
    jQuery('#ik_dirdatos_privacidad').val('<?php echo get_option('ik_dirdatos_privacidad'); ?>')
</script>
<script>
    jQuery('#ik_dato_guardado').fadeOut(2600);
</script>