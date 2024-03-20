<?php
/*
Plugin Name: IK Directorio Datos
Description: Gestiona servicios, pueblos y datos de directorio
Version: 4.2.7
Author: Gabriel Caroprese
Requires at least: 5.3
Requires PHP: 7.2
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$ik_dirdatos_dir = dirname( __FILE__ );
$ik_dirdatos_public_dir = plugin_dir_url(__FILE__ );
//Defino el valor por defecto de mostreo de resultados y registros por pueblo
define ('IK_DIRDATOS_CANT_LISTADO', 3);
define ('IK_DIRDATOS_CANT_POR_PUEBLO', 3);
define( 'IK_DIRDATOS_PLUGIN_DIR', $ik_dirdatos_dir);
define( 'IK_DIRDATOS_PLUGIN_DIR_PUBLIC', $ik_dirdatos_public_dir);

//Definos las organizaciones zonales
define( 'IK_DIRDATOS_PAIS_REGION_1', array(
	'est' => array(
		'es'=> 'Estado',
		'en'=> 'State',
		),
	'prov' => array(
		'es'=> 'Provincia',
		'en'=> 'Province',
		),
	'reg' => array(
		'es'=> 'Regi&oacute;n',
		'en'=> 'Region',
		),		
	'dep' => array(
		'es'=> 'Departamento',
		'en'=> 'Deparment',
		),
	'mun' => array(
		'es'=> 'Municipio',
		'en'=> 'Municipality',
		),
	'can' => array(
		'es'=> 'Cant&oacute;n',
		'en'=> 'Canton',
		),
	'ct' => array(
		'es'=> 'Ciudad',
		'en'=> 'City',
		),
	'cd' => array(
		'es'=> 'Condado',
		'en'=> 'County',
		),
	'none' => array(
		'es'=> '-',
		'en'=> '-',
		)
	)
);
define( 'IK_DIRDATOS_PAIS_REGION_2', array(
	'ct' => array(
		'es'=> 'Ciudad',
		'en'=> 'City',
		),
	'tw' => array(
		'es'=> 'Pueblo',
		'en'=> 'Town',
		),
	'mn' => array(
		'es'=> 'Municipio',
		'en'=> 'Municipality',
		),
	'cd' => array(
		'es'=> 'Condado',
		'en'=> 'County',
		),
	'prov' => array(
		'es'=> 'Provincia',
		'en'=> 'Province',
		),
	'reg' => array(
		'es'=> 'Regi&oacute;n',
		'en'=> 'Region',
		),		
	'dep' => array(
		'es'=> 'Departamento',
		'en'=> 'Deparment',
		),
	'can' => array(
		'es'=> 'Cant&oacute;n',
		'en'=> 'Canton',
		),
	)
);

//I add plugin functions
require_once($ik_dirdatos_dir . '/include/init.php');
require_once($ik_dirdatos_dir . '/include/general_functions.php');
require_once($ik_dirdatos_dir . '/include/ajax_functions.php');
register_activation_hook( __FILE__, 'ik_dirdatos_activacion' );

//Carga de lenguajes
function ik_directorio_datos_textdomain_init() {
    load_plugin_textdomain( 'ik-directorio-datos', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'ik_directorio_datos_textdomain_init' );

//add credits
add_action( 'wp_head', 'ik_directorio_datos_head_credits');
function ik_directorio_datos_head_credits() {
	echo '<!-- Powered | Website Development by Inforket LLC | Gabriel Caroprese - https://inforket.com -->';
}
?>