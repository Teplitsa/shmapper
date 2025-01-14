<?php
/**
 * Plugin Name: ShMapper by Teplitsa
 * Plugin URI: http://genagl.ru/?p=652
 * Description: Location and logistics services for NKO
 * Version: 1.5.1
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Teplitsa. Technologies for Social Good
 * Author URI: https://te-st.org
 * License: GPL2
 * Text Domain: shmapper-by-teplitsa
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Contributors:
	Genagl (genag1@list.ru)
	Lev "ahaenor" Zvyagintsev (ahaenor@gmail.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)
	Teplitsa Support Team (suptestru@gmail.com)
	Ion Burdianov (burdianov@gmail.com)

 * License: GPLv2 or later
	Copyright 2018  Genagl  (email: genag1@list.ru)

	GNU General Public License, Free Software Foundation <http://www.gnu.org/licenses/gpl-2.0.html>

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

// Paths.
define( 'SHM_URLPATH', plugin_dir_url( __FILE__ ) );
define( 'SHM_REAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHMAPPER', 'shmapper-by-teplitsa' );
define( 'SHMAPPERD', 'shmapper-drive' );
define( 'SHM_MAP', 'shm_map' );
define( 'SHM_POINT', 'shm_point' );
define( 'SHM_POINT_TYPE', 'shm_point_type' );
define( 'SHMAPPER_POINT_MESSAGE', 'shm_point_msg' );
define( 'SHM_REQUEST', 'shm_request' );
define( 'SHMAPPER_PLAIN_TEXT_TYPE_ID', 1 );
define( 'SHMAPPER_NAME_TYPE_ID', 2 );
define( 'SHMAPPER_PLAIN_NUMBER_TYPE_ID', 3 );
define( 'SHMAPPER_EMAIL_TYPE_ID', 4 );
define( 'SHMAPPER_PHONE_TYPE_ID', 5 );
define( 'SHMAPPER_TEXTAREA_TYPE_ID', 6 );
define( 'SHMAPPER_IMAGE_TYPE_ID', 7 );
define( 'SHMAPPER_MARK_TYPE_ID', 8 );
define( 'SHMAPPER_TITLE_TYPE_ID', 9 );
define( 'SHM_CSV_STROKE_SEPARATOR', ';' );
define( 'SHM_CSV_ROW_SEPARATOR', '
');
define( 'SHMAPPER_VERSION', '1.5.1' );

/** Load textdomain */
function shm_load_textdomain_mofile( $mofile, $domain ) {
	if ( 'shmapper-by-teplitsa' === $domain ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
		if ( 'de_DE' === $locale ) {
			$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
		}
	}
	return $mofile;
}
add_filter( 'load_textdomain_mofile', 'shm_load_textdomain_mofile', 10, 2 );

require_once SHM_REAL_PATH . 'inc/shm-functions.php';
require_once SHM_REAL_PATH . 'class/ShMapper.class.php';
require_once SHM_REAL_PATH . 'class/ShMapper_ajax.class.php';
if ( ! class_exists( 'SMC_Post' ) ) {
	require_once SHM_REAL_PATH . 'class/SMC_Post.php';
}
if ( ! class_exists( 'SMC_Object_type' ) ) {
	require_once SHM_REAL_PATH . 'class/SMC_Object_type.php';
}
require_once SHM_REAL_PATH . 'class/ShmMap.class.php';
require_once SHM_REAL_PATH . 'class/ShMapPointType.class.php';
require_once SHM_REAL_PATH . 'class/ShmPoint.class.php';
require_once SHM_REAL_PATH . 'class/ShMapperRequest.class.php';
require_once SHM_REAL_PATH . 'class/ShmForm.class.php';
require_once SHM_REAL_PATH . 'class/ShMapper_Assistants.class.php';
require_once SHM_REAL_PATH . 'class/ShmAdminPage.class.php';
require_once SHM_REAL_PATH . 'shortcode/shm_shortcodes.php';
require_once SHM_REAL_PATH . 'widget/ShMap.widget.php';
require_once SHM_REAL_PATH . 'class/ShMapperDrive.class.php';
require_once SHM_REAL_PATH . 'shmapperTracks.plugin.php';

/** Add Gutenberg Blocks */
if ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
	require_once SHM_REAL_PATH . 'inc/editor/editor.php';
}

register_activation_hook( __FILE__, array( 'ShMapper', 'activate' ) );
register_activation_hook( __FILE__, array( 'ShMapperDrive', 'activate' ) );

/** Shamapper init */
function init_shmapper() {
	ShMapper::get_instance();
	ShMapper_Assistants::get_instance();
	ShMapper_ajax::get_instance();
	ShmMap::init();
	ShMapperRequest::init();
	ShMapPointType::init();
	ShmPoint::init();
	ShmForm::init();
}
add_action( 'init', 'init_shmapper', 1 );

/** Shamapper Drive init */
function init_shmapper_drive() {
	require_once SHM_REAL_PATH . 'class/ShMapperDrive_ajax.class.php';
	require_once SHM_REAL_PATH . 'class/ShMapperPointMessage.class.php';
	ShMapperDrive::get_instance();
	ShMapperDrive_ajax::get_instance();
	ShMapperPointMessage::init();
}
add_action( 'init', 'init_shmapper_drive', 2 );

/** Is session */
function shm_is_session() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return is_plugin_active( 'wp-session-manager/wp-session-manager.php' );
}

/**
 * On activation plugin
 */
function shm_register_activation_hook() {
	shm_flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'shm_register_activation_hook' );
