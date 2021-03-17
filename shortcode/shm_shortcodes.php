<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

require_once SHM_REAL_PATH . 'shortcode/shmMap.shortcode.php';
function shm_add_shortcodes() {
	add_shortcode( 'shmMap', 'shmMap' );
}
