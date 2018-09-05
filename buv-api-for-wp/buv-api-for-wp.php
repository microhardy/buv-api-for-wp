<?php
/**
 * Admin Page for BUV API-1
 * 
 * @author Thomas Hardegger
 * @version 05.09.2018
 * STATUS: Reviewed
*/
/*
Plugin Name: BUV API-1 Schnittstelle für WordPress
Plugin URI: http://www.buv.ch/verband/web-support/
Description: erlaubt die Datenabfrage der BUV Spiele E-Kids und Junioren-D
Version: 1.23
Author: Thomas Hardegger
Author URI: www.buv.ch
License: GPL2

----------------------------------------------------------------------------------------
Copyright 2015 Thomas Hardegger (email : webmaster@buv.ch)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
----------------------------------------------------------------------------------------

Changelog:
V1.18 - 20.01.2018 - unnötige Log-Meldungen auskommentiert. 
V1.20 - 15.03.2018 - E-Kids Runde 2 gefixt, Index neue in Plugin BUV-GR
V1.21 - 15.03.2018 - E-Kids Runde 2 in Admin und Default
V1.22 - 06.03.2018 - Fehler abgefangen, wenn keine Rounden in aktueller Saison definiert PHP 7.2
V1.23 - 05-09.2018 - Fehler abgefangen, wenn keine Gruppen in aktueller Saison definiert PHP 7.2
*/

// Sicherstellen, dass keine Infos ausgegeben werden wenn direkt aufgerufen
if ( !function_exists( 'add_action' ) ) {
	echo 'Hallo!  Ich bin ein Plugin. Viel machen kann ich nicht wenn du mich direkt aufrust :)';
	exit;
}

/* ------------------------------------------------------------------------------------ */
// Konstanten
/* ------------------------------------------------------------------------------------ */
if ( ! defined( 'BUV_API_WP_VERSION' ) )
 define('BUV_API_WP_VERSION', '1.0');
 
if ( ! defined( 'BUV_API_WP_PLUGIN_URL' ) )
 define('BUV_API_WP_PLUGIN_URL', plugin_dir_url( __FILE__ )); // http://www.churunichockey.ch/wp-content/plugins/BUV-api-for-wp/

 
if ( ! defined( 'BUV_API_WP_PLUGIN_PATH' ) )
 define('BUV_API_WP_PLUGIN_PATH', plugin_dir_path( __FILE__ )); // httpdocs/wp-content/plugins/BUV-api-for-wp/
 
if ( ! defined( 'BUV_API_WP_PLUGIN_BASENAME' ) )
 define( 'BUV_API_WP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // BUV-api-for-wp/BUV-api-for-wp.php

if ( ! defined( 'BUV_API_WP_PLUGIN_DIRNAME' ) )
 define( 'BUV_API_WP_PLUGIN_DIRNAME', dirname( BUV_API_WP_PLUGIN_BASENAME ) ); // BUV-api-for-wp
    
global $wpdb;

/* ------------------------------------------------------------------------------------ */
// Administrationsbereich (Backend)
/* ------------------------------------------------------------------------------------ */
if ( is_admin() )
	require_once BUV_API_WP_PLUGIN_PATH . 'admin/buv_api_wp_admin.php';


/* ------------------------------------------------------------------------------------ */
// Besucherbereich (Frontend)
/* ------------------------------------------------------------------------------------ */
if ( ! is_admin() ){ // Alles wird nur im Frontend ausgeführt

 /* ------------------------------------------------------------------------------------*/
 // BUV-Konstante API Key mit Plugin-Optionen überschreiben
 /* ------------------------------------------------------------------------------------ */
 	$plugin_options = get_option( 'BUV_WP_plugin_options' );
 	// $api_key = $plugin_options['BUV_api_key'];
  //if (!defined('CFG_BUV_API_KEY')) define('CFG_BUV_API_KEY', $api_key);
  if (!defined('CFG_BUV_CLUB')) define('CFG_BUV_CLUB', 'Chur Unihockey');
  if (!defined('CFG_BUV_VERSION')) define('CFG_BUV_VERSION', 'beta 01');
  
 /* ------------------------------------------------------------------------------------ */
 // BUV-API--PHP-Framework verlinken
 /* ------------------------------------------------------------------------------------ */
 try{
  require_once BUV_API_WP_PLUGIN_PATH . '/includes/buv/php/buv_api_html_lib.php';
  require_once BUV_API_WP_PLUGIN_PATH . '/includes/buv/lib/BusinessServer/vendor/autoload.php';
  require_once BUV_API_WP_PLUGIN_PATH . '/includes/buv/lib/BusinessServer/src/buendnerunihockey/Public.php';
  require_once BUV_API_WP_PLUGIN_PATH . '/includes/buv/php/buv_api_wp.class.php';
 }

 catch (BUVException $ex) {
  echo "<p class='error BUV'>BUV Error found<br><strong>{$ex->getMessage()}</strong></p>\n";
 }
 
} // End if ( ! is_admin() )

?>