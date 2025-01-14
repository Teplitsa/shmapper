<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

function draw_shMap($map, $args )
{
	global $shm_all_maps;
	if(!is_array($shm_all_maps))	$shm_all_maps =[];
	array_push($shm_all_maps, $map->id);
	
	$html   = "";
	$legend = "";
	$p      = "";

	$mapType           = $map->get_meta("map_type");
	$mapType           = $mapType && ShMapper::$options['map_api'] == array_keys($mapType)[0] ? $mapType : ShmMap::get_map_types();
	$mapType           = $mapType[ ShMapper::$options['map_api'] ][0];
	$id                = $map->id;
	$muniq             = isset($args['uniq']) ? $args['uniq'] : $id;
	$uniq              = "ShmMap$id$muniq";
	$title             = $map->get("post_title");
	$height            = isset($args['height']) ? $args['height'] : $map->get("height");
	$width             = $map->get_meta("width");
	$width             = $width ? $width."px" : "100%";
	$latitude          = $map->get_meta("latitude");
	$longitude         = $map->get_meta("longitude");
	$is_lock           = $map->get_meta("is_lock");

	$is_scroll_zoom    = $map->get_meta("is_scroll_zoom");
	$is_drag           = $map->get_meta("is_drag");

	$is_layer_switcher = $map->get_meta("is_layer_switcher");
	$is_zoomer         = $map->get_meta("is_zoomer");
	$is_search         = $map->get_meta("is_search");
	$is_clustered      = $map->get_meta("is_clustered");
	$is_legend         = $map->get_meta("is_legend");
	$is_filtered       = $map->get_meta("is_filtered");
	$is_fullscreen     = $map->get_meta("is_fullscreen");
	$zoom              = $map->get_meta("zoom");
	$latitude          = $latitude ? $latitude : 55;
	$longitude         = $longitude ? $longitude : 55;
	$zoom              = $zoom ? $zoom : 4;
	$leg               = "";
	$highlight_country = $map->get_meta( 'highlight_country' );
	$overlay_color     = $map->get_meta( 'overlay_color' );
	$border_color      = $map->get_meta( 'border_color' );
	$overlay_opacity   = $map->get_meta( 'overlay_opacity' );
	$shm_kml_url       = $map->get_meta( '_shm_kml_url' );

	if( $is_legend ) {

		$points = $map->get_map_points();
		$include = array();

		foreach ( $points as $point ) {
			$include[] = $point->term_id;
		}

		$include = array_unique( $include );

		if(is_array($include) && count($include))
		{
			foreach($include as $term_id)
			{
				if( !$term_id ) {
					continue;
				}

				$term = get_term($term_id);
				if( ! is_wp_error( $term ) && $term ) {
					$color = get_term_meta( $term_id, 'color', true);

					$icon_image = ShMapPointType::get_icon_default_marker( $color );
					$icon_id = get_term_meta( $term_id, 'icon', true);

					$icon_url = wp_get_attachment_image_url( $icon_id, 'medium_large' );

					if ( $icon_id ) {
						$icon_image = '<img src="' . esc_attr( $icon_url ) . '" alt="' . esc_attr( $term->name ) . '">';
					}

					$icon_html = '<div class="shm-icon">' . $icon_image . '</div>';

					$icon_name = '<span  class="shm-icon-name">' . esc_html( $term->name ) . '</span>';

					$legend_item_html = $icon_html . $icon_name;

					$leg .= '<div class="shm-legend__item"> ' . $legend_item_html . '</div>';

				}

			}
			if ( $leg ) {
				$legend = "
				<div class='shm-legend'>
					$leg
				</div>";
			}
		};
	}

	$filters = '';
	if( $is_filtered ) {

		$points = $map->get_map_points();
		$includes = array();

		foreach ( $points as $point ) {
			$includes[] = $point->term_id;
		}

		$includes = array_unique( $includes );

		if ( $includes ) {
			$filters = ShMapPointType::get_ganre_swicher([
				'prefix'		=> 'filtered'.$uniq, 
				'row_style'		=> "float:right;margin-left: 5px;margin-right: 0px;",
				"selected"		=> ShMapPointType::get_all_ids(),
				"includes"		=> $includes,
				"col_width"		=> 2
			], "checkbox",  "stroke-filter" );
		}
	}

	$is_csv = $map->get_meta("is_csv");
	$csv = "";
	
	if( $is_csv ) {
		$csv = "<a class='shm-csv-icon shm-hint' data-title='".sprintf(__("download %s.csv", SHMAPPER), $title)."' href='' map_id='$id'></a>";
	}

	$points = $map->get_map_points();

	if( $filters || $csv ) {
		$html .="
			<div class='shm-map-panel' for='$uniq'>
				$filters $csv
			</div>";
	}

	// CSS Classes.
	$classes = 'shm-map-container shm_container';
	if ( $legend ) {
		$classes .= ' shm-map-has-legend';
	}

	if ( isset( $args['isEditor'] ) && $args['isEditor'] ) {
		$classes .= ' is-server-side';
	}

	$css_height = $height . 'px';
	if ( isset( $args['minheight'] ) && $args['minheight'] ) {
		$css_height = $args['minheight'];
	}

	$css = 'height:' . $css_height . ';';

	$html .= '<div class="' . esc_attr( $classes ) . '" id="' . esc_attr( $uniq ) . '" shm_map_id="' . esc_attr( $id ) . '" style="' . $css . '"></div>';

	$html .= $legend;

	//line javascript.
	foreach ( $points as $point ) {

		$icon = $point->icon;
		$point_height = $point->height;
		$point_width = $point->width;
		if ( ! $point->icon ) {
			$color = '#f43724';
			if ( isset( $point->color ) && $point->color ) {
				$color = $point->color;
			}
			$default_marker = shm_get_default_marker( $color );
			$icon = $default_marker['icon'];
			if ( ! $point_height ) {
				$point_height = $default_marker['height'];
			}
			if ( ! $point_width ) {
				$point_width = $default_marker['width'];
			}
		};

		$p .= " 
			var p = {}; 
			p.post_id 	= '" . esc_attr( $point->ID ) . "';
			p.post_title 	= '" . esc_html( sanitize_text_field( $point->post_title ) ) . "';
			p.post_content 	= '<div class=\"shml-popup-post-content\">" . make_clickable( html_entity_decode( esc_js( shm_strip_scripts_tags( do_shortcode( $point->post_content ) ) ) ) ) . "</div> <a href=\"" . get_permalink( $point->ID ) . "\" class=\"shm-no-uline\"> <span class=\"dashicons dashicons-location\"></span></a><div class=\"shm_ya_footer\">" . esc_js( $point->location ) . "</div>';
			p.latitude 		= '" . esc_attr( $point->latitude ) . "';
			p.longitude 	= '" . esc_attr( $point->longitude ) . "';
			p.location 		= '" . esc_js( $point->location ) . "';
			p.type 			= '" . esc_attr( $point->type ) . "';
			p.term_id 		= '" . esc_attr( $point->term_id ) . "';
			p.icon 			= \"" . $icon . "\";
			p.color 		= '" . esc_attr( $point->color ) . "';
			p.height 		= " . esc_attr( $point_height ) . ";
			p.width 		= " . esc_attr( $point_width ) . ";
			points.push(p);
			";
	}

	$desabled = $is_lock ? "
					myMap.behaviors.disable('scrollZoom');
					myMap.behaviors.disable('drag');
	" : "";
	$is_admin = "";
	if(is_admin())
	{
		$is_admin = " is_admin( myMap, $map->id );";
	}
	$default_icon_id 	= $map->get_meta("default_icon_id");
	$icon = '';
	if ( wp_get_attachment_image_src($default_icon_id ) ) {
		$icon = wp_get_attachment_image_src($default_icon_id, [60, 60] )[0];
		$icon = SHM_URLPATH . 'assets/img/default-marker.svg';
	}



	$html .= "
	<script type='text/javascript'>
		jQuery(document).ready( function($)
		{
			var points 		= []; 
			$p
			var mData = {
				mapType			: '$mapType',
				uniq 			: '$uniq',
				muniq			: '$id$muniq',
				latitude		: $latitude,
				longitude		: $longitude,
				zoom			: $zoom,
				map_id			: $map->id,
				isClausterer	: ". ($is_clustered ? 1 : 0). ",
				isLayerSwitcher	: ". ($is_layer_switcher ? 1 : 0). ",
				isFullscreen	: ". ($is_fullscreen ? 1 : 0). ",
				isScrollZoom    : ". ($is_scroll_zoom ? 1 : 0 ). ",
				isDrag          : ". ($is_drag ? 1 : 0 ). ",
				isSearch		: ". ($is_search ? 1 : 0). ",
				isZoomer		: ". ($is_zoomer ? 1 : 0). ",
				isAdmin			: ". (is_admin() ? 1 : 0). ",
				isMap			: true,
				default_icon	: '$icon',
				country         : '$highlight_country',
				overlay         : '$overlay_color',
				border          : '$border_color',
				overlayOpacity  : '$overlay_opacity',
				kmlUrl          : '$shm_kml_url'
			};

			if ( map_type == 1 ) {

				ymaps.ready( function(){

					init_map( mData, points );

					ymaps.borders.load( '001' , {
						lang: shmYa.langIso,
						quality: 1
					}).then(function (result) {

						let selectOption   = '<option>...</option>';
						let optionValue    = '';
						let optionLabel    = '';
						let optionSelected = '';
						let optionCurrent  = mData.country;
						let allCountries   = [];
						let allOptions     = [];

						for (var i = 0; i < result.features.length; i++) {
							optionValue = result.features[i].properties.iso3166;
							optionLabel = result.features[i].properties.name;
							allOptions[ optionLabel ] = optionValue;
							allCountries.push( optionLabel );
						}

						// Sort countries alphabetically
						allCountries.sort();

						// Create html options
						allCountries.forEach( function( value, index ){
							optionValue = allOptions[ value ];
							optionSelected = '';
							if ( optionCurrent == optionValue ) {
								optionSelected = 'selected';
							}
							selectOption += '<option value=\"' + optionValue + '\" ' + optionSelected + '>' + value + '</option>';
						});

						// Add options to admin select
						if ( $( '[name=highlight_country]' ).length ) {
							$( '[name=highlight_country]' ).html( selectOption );
						}

					});

				} );

			} else if (map_type == 2) {
				init_map( mData, points );
			}
			
			// Disable submit post form on this page.
			$('form#post').on('keyup keypress', function(e) {
				var keyCode = e.keyCode || e.which;
				if (keyCode === 13) { 
				e.preventDefault();
					return false;
				}
			});
		});

		jQuery(\"<style type='text/css'>.shm_container .leaflet-popup .leaflet-popup-content-wrapper .leaflet-popup-content .shml-body {max-height: ".round($height * 1.5)."px !important;} </style>\").appendTo('head');

	</script>";

	return $html;
}
