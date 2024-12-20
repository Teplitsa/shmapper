<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

function shmMap($args) {

	$args = shortcode_atts( array(
		'heigth'       => 450,
		'minheight'    => '450px',
		'anchor'       => '',
		'id'           => -1,
		'map'          => false,
		'form'         => false,
		'isForm'       => false,
		'formWidth'    => '',
		'formAlign'    => '',
		'formSpacing'  => '',
		'uniq'         => false,
		'classes'      => array(),
		'align'        => false,
		'isblock'      => false,
		'isEditor'     => false,
		'mapBlockType' => 'classic',
	), $args, 'shmMap' );

	$id				= $args['id'];
	$args['uniq']	= $args['uniq'] ? $args['uniq'] : substr( MD5(rand(0, 100000000)), 0, 8 );
	$uniq			= $args['uniq'];
	$map 			= ShmMap::get_instance($args['id']);
	if(!$map->is_enabled() || $map->get("post_type") !== SHM_MAP)
	{
		return __("No map on ID ", SHMAPPER) . $args['id'];
	}
	$map_enb	= $args["map"]  || ( !$args["map"] && !$args["form"]) ? 1 : 0;
	$form_enb	= $args["form"] || ( !$args["map"] && !$args["form"]) ? 1 : 0;

	$is_form = false;
	if ( $form_enb && $map->get_meta("is_form") && !ShMapper::$options['shm_map_is_crowdsourced'] ) {
		$is_form = true;
	}

	$html = '';

	$is_title = ( $map->get_meta( 'is_title' ) !== '' ) ? $map->get_meta( 'is_title' ) : '1';

	$style = '';
	if ( $map->get_meta( 'width' ) ) {
		$width =  $map->get_meta( 'width' ) . 'px';
		if ( 'full' === $args['align'] ) {
			$width = '100%';
		}
		$style = '--shm-map-max-width:' . $width . ';';
	}

	if ( ! $args['isblock'] && $map->get_meta( 'height' ) ) {
		$args['height']    = $map->get_meta( 'height' );
		$args['minheight'] = $map->get_meta( 'height' ) . 'px';
	}

	if ( $args['isblock'] && $args['minheight'] ) {
		$style .= '--shm-map-min-height:' . $args['minheight'] . ';';
		$args['height'] = (int) $args['minheight'];
	}

	if ( $is_title && ! $args['isblock'] ) {
		$html .= '<div class="shm-title-6 shm-map-title">' . esc_html( $map->get( 'post_title' ) ) . '</div>';
	}

	$form_style_attr = '';
	$form_style = '';
	if ( $args['formWidth'] ) {
		$form_style = '--shm-form-width:' . $args['formWidth'] . ';';
	}

	if ( $args['formSpacing'] && 'fullscreen' === $args['mapBlockType'] ) {
		if ( isset( $args['formSpacing']['top'] ) && $args['formSpacing']['top'] ) {
			$form_style .= 'margin-top:' . $args['formSpacing']['top'] . ';';
		}
		if ( isset( $args['formSpacing']['bottom'] ) && $args['formSpacing']['bottom'] ) {
			$form_style .= 'margin-bottom:' . $args['formSpacing']['bottom'] . ';';
		}
		if ( isset( $args['formSpacing']['right'] ) && $args['formSpacing']['right'] ) {
			$style .= 'padding-right:' . $args['formSpacing']['right'] . ';';
		}
		if ( isset( $args['formSpacing']['left'] ) && $args['formSpacing']['left'] ) {
			$style .= 'padding-left:' . $args['formSpacing']['left'] . ';';
		}
	}

	if ( $args['formAlign'] && 'fullscreen' === $args['mapBlockType'] ) {
		if ( 'left' == $args['formAlign'] ) {
			$form_style .= 'margin-left: 0;';
		}
		if ( 'right' == $args['formAlign'] ) {
			$form_style .= 'margin-right: 0;';
		}
	}

	if ( $form_style ) {
		$form_style_attr = ' style="' . esc_attr( $form_style ) . '"';
	}

	// Is Block
	if ( $args['isblock'] ) {
		$map_enb = true;
		if ( $args['isForm'] ) {
			$is_form = true;
		} else {
			$is_form = false;
		}
	}

	if ( $map_enb ) {
		$html .= $map->draw( $args );
	}

	if ( $is_form ) {
		$form_title = $map->get_meta( 'form_title' );
		if ( $form_title ) {
			$form_title = '<div class="shm-form-title">' . esc_html( $form_title ) . '</div>';
		}
		$form_forms = $map->get_meta( 'form_forms' );
		$html .= '
		<div class="shm-form-container"' . $form_style_attr . '>
			<form class="shm-form-request" id="form' . esc_attr( $id ) . '" form_id="ShmMap' . esc_attr( $id . $uniq ) . '" map_id="' . esc_attr( $id ) . '">
				' . $form_title . '
				<div id="form_forms">' . ShmForm::form( $form_forms, $map ) . '</div>
				<div class="shm-form-element">
					<input type="submit" class="shm-form-submit shm-request" value="' . esc_attr__( 'Send request', SHMAPPER ) . '">
				</div>
			</form>
		</div>';
	}

	$classes = array(
		'class' => 'shm-map-block',
	);

	$classes = array_merge( $classes, $args['classes'] );

	$block_attr = 'class="' . implode(' ', $classes) . '"';

	if ( $style ) {
		$block_attr .= ' style="' . esc_attr( $style ) . '"';
	}

	if ( $args['anchor'] ) {
		$block_attr .= ' id="' . esc_attr( $args['anchor'] ) . '"';
	}

	$html = '<div ' . $block_attr . '>' . $html . '</div>';

	$html = apply_filters( 'shm_final_after_front_map', $html, $args );

	return $html;
}
