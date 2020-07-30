<?php
/*
Plugin Name: Pasarela de pago para Redsys para Gravity Forms (modulosdepago.es)
Plugin URI: http://modulosdepago.es
Description: La pasarela de pago Redsýs de para Gravity Forms de ZhenIT Software <a href="http://www.modulosdepago.es/">vea otras pasarelas de ZhenIT Software</a>.
Version: 3.2.0
Author: Mikel Martin
Author URI: http://Zhenit.com
Text Domain: gravityformsredsys
Domain Path: /languages

------------------------------------------------------------------------

Copyright 2006-2017 ZhenIT Software.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_REDSYS_VERSION', '3.2.0' );
if ( ! defined( 'REDSYS_FILE_PATH' ) ) {
	define( 'REDSYS_FILE_PATH', dirname( __FILE__ ) . '/lib/Redsys/' );
}

require_once REDSYS_FILE_PATH . 'RedsysAPI.php';
add_action( 'gform_loaded', array( 'GF_Redsys_Bootstrap', 'load' ), 5 );

/**
 * Undocumented class
 */
class GF_Redsys_Bootstrap {
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_payment_addon_framework' ) ) {
			return;
		}

		require_once 'class-gf-redsys.php';
		GFAddOn::register( 'GFRedsys' );

		do_action( 'gfredsys_loaded' );
	}

}

/**
 * Undocumented function
 *
 * @return GFRedsys
 */
function gf_redsys() {
	return GFRedsys::get_instance();
}
