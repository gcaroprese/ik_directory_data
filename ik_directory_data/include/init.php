<?php
/*

Funciones Iniciales
Update: 26/08/2021
Author: Gabriel Caroprese

*/

//Script de activacion
function ik_dirdatos_activacion(){
    // Creo una tabla en la base de datos para almacenar los datos del directorio
    ik_dirdatos_dbcrear();
}

// Voy a desactivar el plugin si Dokan y Dokan Pro no se encuentran instalados
add_action( 'admin_notices', 'ik_dirdatos_dependencia_woocommerce' );
function ik_dirdatos_dependencia_woocommerce() {
    if (!class_exists('WC_Order')) {
        echo '<div class="error"><p>' . __( 'Atención: IK Directorios necesesita de Woocommerce para funcionar correctamente' ) . '</p></div>';
        $pluginDirectorio = 'ik-directorio-datos/ik-directorio-datos.php';
        deactivate_plugins($pluginDirectorio);
    }
}

//funcion para crear tablas de DB
function ik_dirdatos_dbcrear() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$tabla_dirdatos_paises = $wpdb->prefix . 'ik_dirdatos_paises';
	$tabla_dirdatos_pueblos = $wpdb->prefix . 'ik_dirdatos_pueblos';
	$tabla_dirdatos_estados = $wpdb->prefix . 'ik_dirdatos_estados';
	$tabla_dirdatos_servicio = $wpdb->prefix . 'ik_dirdatos_servicios';
	$tabla_dirdatos_registros = $wpdb->prefix . 'ik_dirdatos_registros';

	$sql = "CREATE TABLE ".$tabla_dirdatos_paises." (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		nombre_en varchar(60) NOT NULL,
		nombre_es varchar(60) NOT NULL,
		zona_1 varchar(7) DEFAULT 'est' NOT NULL,
		zona_2 varchar(7) DEFAULT 'ct' NOT NULL,
		UNIQUE KEY id (id)
	) ".$charset_collate.";
	CREATE TABLE ".$tabla_dirdatos_pueblos." (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		estado_id bigint(20) NOT NULL,
		pais bigint(20) NOT NULL,
		nombre varchar(60) NOT NULL,
		detalles longtext NOT NULL,
		UNIQUE KEY id (id)
	) ".$charset_collate.";
    CREATE TABLE ".$tabla_dirdatos_estados." (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		pais bigint(20) NOT NULL,
		nombre varchar(60) NOT NULL,
		detalles longtext NOT NULL,
		UNIQUE KEY id (id)
	) ".$charset_collate.";
	CREATE TABLE ".$tabla_dirdatos_servicio." (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		nombre_en varchar(60) NOT NULL,
		nombre_es varchar(60) NOT NULL,
		descripcion_en longtext NOT NULL,
		descripcion_es longtext NOT NULL,
		UNIQUE KEY id (id)
	) ".$charset_collate.";
	CREATE TABLE ".$tabla_dirdatos_registros." (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		nombre varchar(70) NOT NULL,
		id_pueblo bigint(20) NOT NULL,
		id_servicios longtext NOT NULL,
		tel varchar(20) NOT NULL,
		whatsapp varchar(20) NOT NULL,
		email varchar(50) NOT NULL,
		direccion tinytext NOT NULL,
		descripcion longtext NOT NULL,
		id_registro varchar(60) NOT NULL,
		activo int(1) DEFAULT '1' NOT NULL,
		order_id bigint(20) DEFAULT '0' NOT NULL,
		UNIQUE KEY id (id)
	) ".$charset_collate.";";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}


//Creo el menú de WP Admin para cobros
function ik_dirdatos_admin_menu(){
    add_menu_page('Directorio', 'Directorio', 'manage_options', 'ik_dirdatos_directorio', 'ik_dirdatos_directorio', IK_DIRDATOS_PLUGIN_DIR_PUBLIC . 'img/directorio-icon-plugin.png' );
    add_submenu_page('ik_dirdatos_directorio', 'Config', 'Config', 'manage_options', 'ik_dirdatos_config', 'ik_dirdatos_config', 2 );
    add_submenu_page('ik_dirdatos_directorio', 'Servicios', 'Servicios', 'manage_options', 'ik_dirdatos_servicios', 'ik_dirdatos_servicios', 3 );
    add_submenu_page('ik_dirdatos_directorio', 'Paises', 'Paises', 'manage_options', 'ik_dirdatos_paises', 'ik_dirdatos_paises', 4 );
    add_submenu_page('ik_dirdatos_directorio', 'Estados', 'Estados', 'manage_options', 'ik_dirdatos_estados', 'ik_dirdatos_estados', 5 );
    add_submenu_page('ik_dirdatos_directorio', 'Pueblos', 'Pueblos', 'manage_options', 'ik_dirdatos_pueblos', 'ik_dirdatos_pueblos', 6 );
}
add_action('admin_menu', 'ik_dirdatos_admin_menu');


/*
    Cargo el contenido de cada menu
                                    */
function ik_dirdatos_servicios(){
    include(IK_DIRDATOS_PLUGIN_DIR.'/templates/servicios.php');
}
function ik_dirdatos_pueblos(){
    include(IK_DIRDATOS_PLUGIN_DIR.'/templates/pueblos.php');
}
function ik_dirdatos_paises(){
    include(IK_DIRDATOS_PLUGIN_DIR.'/templates/paises.php');
}
function ik_dirdatos_estados(){
    include(IK_DIRDATOS_PLUGIN_DIR.'/templates/estados.php');
}
function ik_dirdatos_config(){
    include(IK_DIRDATOS_PLUGIN_DIR.'/templates/config.php');
}
function ik_dirdatos_directorio(){
    include(IK_DIRDATOS_PLUGIN_DIR.'/templates/registros.php');
}


//Agrego los scripts y styles en WP Admin
function ik_dirdatos_add_js_scripts() {
	wp_register_style( 'ik_dirdatos_css_style', IK_DIRDATOS_PLUGIN_DIR_PUBLIC . 'css/ik_backend_directorio.css', false, '1.1.11', 'all' );
    wp_register_script( 'ik_dirdatos_select2', IK_DIRDATOS_PLUGIN_DIR_PUBLIC . 'js/select2.js', '', '', true );
	wp_enqueue_style('ik_dirdatos_css_style');
	wp_enqueue_script( 'ik_dirdatos_select2' );
}
add_action( 'admin_enqueue_scripts', 'ik_dirdatos_add_js_scripts' );

//Agrego los scripts y styles en frontend
function ik_dirdatos_add_js_scripts_frontend() {
	wp_enqueue_style('ik_dirdatos_css_fontawesome', IK_DIRDATOS_PLUGIN_DIR_PUBLIC . 'css/fontawesome/css/all.css', '0.1.0', 'all');
}
add_action('wp_enqueue_scripts', 'ik_dirdatos_add_js_scripts_frontend');

?>