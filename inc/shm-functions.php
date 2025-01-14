<?php
/**
 * ShMapper Functions
 *
 * @package teplitsa
 */

/**
 * Get options
 */
function shm_get_options(){
	return get_option( 'shmapper-by-teplitsa' );
}

/**
 * Get map type
 */
function shm_get_map_type(){
	$shm_options = shm_get_options();
	if ( isset( $shm_options['map_api'] ) && $shm_options['map_api'] ) {
		$map_type = $shm_options['map_api'];
	}
	return (int) $map_type;
}

/**
 * Is Local
 */
function shm_is_local(){
	$shm_is_local = false;
	$whitelist = array(
		'127.0.0.1',
		'::1'
	);
	if ( in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
		$shm_is_local = true;
	}
	return $shm_is_local;
};

/**
 * Get Default Marker
 */
function shm_get_default_marker( $hex = '#f43724'){
	if ( ! $hex ) {
		$hex = '#f43724';
	}
	$color = str_replace('#', '', $hex );
	$color_second = shm_colour_brightness($color, 0.6);
	$marker = array(
		'color'  => $hex,
		'height' => '34',
		'width'  => '40',
		'icon'   => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='30px' height='36px' viewBox='13 11 30 36'%3E%3Cpath fill='%23" . $color_second . "' d='M42.929,24.838c-0.234-2.622-1.135-5.137-2.615-7.3c-1.48-2.163-3.489-3.899-5.829-5.039 c-2.341-1.14-4.933-1.644-7.523-1.463c-2.59,0.181-5.09,1.04-7.255,2.494c-1.854,1.257-3.411,2.915-4.558,4.855 c-1.147,1.94-1.856,4.113-2.077,6.364c-0.216,2.236,0.061,4.493,0.812,6.606s1.956,4.032,3.529,5.614l9.353,9.501 c0.164,0.168,0.359,0.301,0.574,0.392S27.785,47,28.018,47s0.464-0.047,0.679-0.138c0.215-0.091,0.41-0.224,0.574-0.392l9.317-9.501 c1.573-1.583,2.778-3.501,3.529-5.614c0.751-2.114,1.028-4.37,0.812-6.606V24.838z M36.117,34.447L28,42.677l-8.117-8.231 c-1.196-1.213-2.113-2.68-2.683-4.295c-0.571-1.615-0.781-3.338-0.617-5.045c0.166-1.734,0.709-3.408,1.591-4.903 c0.882-1.495,2.08-2.772,3.509-3.739c1.872-1.261,4.07-1.934,6.317-1.934s4.445,0.673,6.317,1.934 c1.424,0.964,2.62,2.235,3.502,3.723c0.882,1.488,1.428,3.156,1.598,4.883c0.17,1.713-0.038,3.443-0.609,5.065 C38.237,31.757,37.318,33.23,36.117,34.447z M36.117,34.447L28,42.677l-8.117-8.231c-1.196-1.213-2.113-2.68-2.683-4.295 c-0.571-1.615-0.781-3.338-0.617-5.045c0.166-1.734,0.709-3.408,1.591-4.903c0.882-1.495,2.08-2.772,3.509-3.739 c1.872-1.261,4.07-1.934,6.317-1.934s4.445,0.673,6.317,1.934c1.424,0.964,2.62,2.235,3.502,3.723 c0.882,1.488,1.428,3.156,1.598,4.883c0.17,1.713-0.038,3.443-0.609,5.065C38.237,31.757,37.318,33.23,36.117,34.447z'/%3E%3Cellipse fill='%23" . $color . "' cx='28' cy='26' rx='10.5' ry='10.5'/%3E%3C/svg%3E%0A",//SHM_URLPATH . 'assets/img/default-marker.svg',
	);
	return $marker;
};

/**
 * Get admin menu svg icon
 */
function shm_get_menu_icon( $base64 = true ) {
$icon = '<svg width="20" height="20" viewBox="0 0 20 24" fill="#a7aaad" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M9.99976 1.32628e-05C12.0556 -0.00337215 14.0699 0.641419 15.8061 1.85887C20.3183 5.02111 20.6507 11.5524 17.0553 16.3276C15.0034 19.0518 12.745 21.5776 10.3048 23.8776C10.2108 23.9646 10.0936 24.0052 9.97831 23.9995C9.92924 23.9972 9.88049 23.9862 9.83407 23.9669C9.78451 23.9466 9.73734 23.917 9.69494 23.8776C7.25447 21.5781 4.99607 19.0526 2.94444 16.3286C-0.650146 11.5524 -0.318786 5.02111 4.19372 1.85887C5.92989 0.641419 7.9439 -0.00337215 9.99976 1.32628e-05ZM6.60184 13.5198C6.66069 13.4521 6.73305 13.4026 6.81177 13.3716C6.99335 13.3003 7.20832 13.3279 7.36738 13.4615C8.85573 14.7128 11.1443 14.7128 12.6327 13.4615C12.8606 13.2701 13.2034 13.2961 13.3982 13.5198C13.593 13.7438 13.5665 14.0805 13.3388 14.2719C11.4439 15.8649 8.55619 15.8649 6.66123 14.2719C6.51596 14.1498 6.45261 13.9685 6.47623 13.795C6.48551 13.7279 6.50777 13.662 6.54354 13.6011C6.55999 13.5727 6.57959 13.5456 6.60184 13.5198ZM7.55717 6.4C7.55717 6.10549 7.31407 5.86667 7.01431 5.86667C6.71449 5.86667 6.47145 6.10549 6.47145 6.4V8H4.84287C4.66264 8 4.50304 8.08619 4.40419 8.21904C4.33872 8.30731 4.30002 8.41589 4.30002 8.53333C4.30002 8.82789 4.54305 9.06666 4.84287 9.06666H6.47145V10.6667C6.47145 10.8484 6.56395 11.0091 6.70547 11.1055C6.79325 11.1651 6.89955 11.2 7.01431 11.2C7.12353 11.2 7.22504 11.1683 7.31011 11.1138C7.4588 11.0188 7.55717 10.8539 7.55717 10.6667V9.06666H9.18574C9.3816 9.06666 9.55336 8.96485 9.6488 8.812C9.69945 8.73072 9.7286 8.63541 9.7286 8.53333C9.7286 8.23882 9.48551 8 9.18574 8H7.55717V6.4ZM13.5286 10.1333C14.428 10.1333 15.1572 9.41696 15.1572 8.53333C15.1572 7.64976 14.428 6.93333 13.5286 6.93333C12.6293 6.93333 11.9 7.64976 11.9 8.53333C11.9 9.41696 12.6293 10.1333 13.5286 10.1333Z"/>
</svg>';

	if ( $base64 ) {
		//phpcs:ignore
		return 'data:image/svg+xml;base64,' . base64_encode( $icon );
	}

	return $icon;
}

function shm_colour_brightness( $hex, $percent ) {
	// Work out if hash given
	$hash = '';
	if ( stristr( $hex, '#' ) ) {
		$hex = str_replace('#', '', $hex );
		$hash = '#';
	}
	/// HEX TO RGB
	$rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
	//// CALCULATE
	for ($i = 0; $i < 3; $i++) {
		// See if brighter or darker
		if ($percent > 0) {
			// Lighter
			$rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
		} else {
			// Darker
			$positivePercent = $percent - ($percent * 2);
			$rgb[$i] = round($rgb[$i] * (1 - $positivePercent)); // round($rgb[$i] * (1-$positivePercent));
		}
		// In case rounding up causes us to go to 256
		if ($rgb[$i] > 255) {
			$rgb[$i] = 255;
		}
	}
	//// RBG to Hex
	$hex = '';
	for ($i = 0; $i < 3; $i++) {
		// Convert the decimal digit to hex
		$hexDigit = dechex($rgb[$i]);
		// Add a leading zero if necessary
		if (strlen($hexDigit) == 1) {
			$hexDigit = "0" . $hexDigit;
		}
		// Append to the hex string
		$hex .= $hexDigit;
	}
	return $hash . $hex;
}

/**
 * Disable Gugenberg
 *
 * @param bool   $current_status Current gutenberg status. Default true.
 * @param string $post_type      The post type being checked.
 */
function shmapper_disable_gutenberg( $current_status, $post_type ) {
	if ( in_array( $post_type, array( SHM_POINT ), true ) ) {
		return false;
	}
	return $current_status;
}
add_filter( 'use_block_editor_for_post_type', 'shmapper_disable_gutenberg', 10, 2);

/**
 * Add support upload mime type files gpx and kml.
 */
function shm_upload_mimes( $mime_types ) {
	$mime_types['gpx'] = 'text/gpx';
	$mime_types['kml'] = 'text/xml';
	return $mime_types;
}
add_filter( 'upload_mimes', 'shm_upload_mimes' );

/**
 * Rewrite rules.
 */
function shm_flush_rewrite_rules(){
	ShmMap::add_class();
	ShMaperTrack::add_class();
	ShMapperRequest::add_class();
	ShMapperTracksPoint::add_class();
	ShmPoint::add_class();
	ShMapPointType::register_all();
	ShMapTrackType::register_all();
	flush_rewrite_rules();
}

/**
 * Strip tags script and style
 */
function shm_strip_scripts_tags( $text ) {
	$text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text );
	return $text;
}
