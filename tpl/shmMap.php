<?php

function draw_shMap($map, $args )
{
	global $shm_all_maps;
	if(!is_array($shm_all_maps))	$shm_all_maps =[];
	array_push($shm_all_maps, $map->id);
	
	$html		= "";
	$legend		= "";
	
	$mapType	= $map->get_meta("map_type");
	$mapType	= $mapType && ShMapper::$options['map_api']  == array_keys($mapType)[0]
		? $mapType 
		: ShmMap::get_map_types();
	$mapType	= $mapType[ ShMapper::$options['map_api'] ][0];
	$id 		= $map->id;
	$muniq		= isset($args['uniq']) ? $args['uniq'] : $id;
	$uniq		= "ShmMap$id$muniq";
	$title		= $map->get("post_title");
	$height		= isset($args['height']) ? $args['height'] : $map->get("height");
	$width		= $map->get_meta("width");
	$width 		= $width ? $width."px" : "100%";
	$latitude	= $map->get_meta("latitude");
	$longitude	= $map->get_meta("longitude");
	$is_lock	= $map->get_meta("is_lock");
	$is_layer_switcher	= $map->get_meta("is_layer_switcher");
	$is_zoomer	= $map->get_meta("is_zoomer");
	$is_search	= $map->get_meta("is_search");
	$is_clustered= $map->get_meta("is_clustered");
	$is_legend 	= $map->get_meta("is_legend");
	$is_filtered = $map->get_meta("is_filtered");
	$is_fullscreen = $map->get_meta("is_fullscreen");
	$zoom		= $map->get_meta("zoom");
	$latitude	= $latitude		? $latitude	 : 55;
	$longitude	= $longitude	? $longitude : 55;
	$zoom		=  $zoom ? $zoom : 4;
	$leg 		= "";
	if( $is_legend )
	{
		$include = $map->get_include_types();
		if(is_array($include) && count($include))
		{
			foreach($include as $term_id)
			{
				if( !$term_id ) {
					continue;
				}

				$term = get_term($term_id);
				if( !is_wp_error($term) ) { // echo '<pre>HERE: '.print_r($include, 1).'</pre>';
					
					$color = get_term_meta($term_id, "color", true);
					$leg .= "<div class='shm-icon' style='background-color:$color;'><img src='" . ShMapPointType:: get_icon_src ($term_id, 20)[0] . "' width='20' /></div> <span  class='shm-icon-name'>" . $term->name . "</span>";

				}

			}
			$legend = "
			<div class='shm-legend' style='width:$width;'>
				$leg
			</div>";
		};
	}
	if( $is_filtered )
	{
		$includes = $map->get_include_types();
		$filters = ShMapPointType::get_ganre_swicher([
			'prefix'		=> 'filtered'.$uniq, 
			'row_style'		=> "float:right;margin-left: 5px;margin-right: 0px;",
			"selected"		=> ShMapPointType::get_all_ids(),
			"includes"		=> $includes,
			"col_width"		=> 2
		], "checkbox",  "stroke" );
	} else {
        $filters = '';
    }

    $is_csv = $map->get_meta("is_csv");
    $csv = "";
    
    if($is_csv) {
        $csv = "<a class='shm-csv-icon shm-hint' data-title='".sprintf(__("download  %s.csv", SHMAPPER), $title)."' href='' map_id='$id'></a>";
    }

	$points		= $map->get_map_points();
	if($is_filtered || $is_csv)
	{
		$html .="
			<div class='shm-map-panel' for='$uniq' style='width:$width;'>
				$filters $csv
			</div>";
	}
	$html 		.= "
	<div class='shm_container' id='$uniq' shm_map_id='$id' style='height:" . $height . "px; width:$width;'>
	</div>$legend ";
	$p = "";
	$str = ["
","

"];

//line javascript
	foreach($points as $point)
	{
		$p .= " 
			var p = {}; 
			p.post_id 	= '" . $point->ID . "';
			p.post_title 	= '" . $point->post_title . "';
			p.post_content 	= '<div class=\"shml-popup-post-content\">" . html_entity_decode( esc_js($point->post_content) ) . "</div> <a href=\"" .get_permalink($point->ID) . "\" class=\"shm-no-uline\"> <span class=\"dashicons dashicons-location\"></span></a><div class=\"shm_ya_footer\">" . esc_js($point->location) . "</div>';
			p.latitude 		= '" . $point->latitude . "'; 
			p.longitude 	= '" . $point->longitude . "'; 
			p.location 		= '" . esc_js($point->location) . "'; 
			p.type 			= '" . $point->type . "'; 
			p.term_id 		= '" . $point->term_id . "'; 
			p.icon 			= '" . $point->icon . "'; 
			p.color 		= '" . $point->color . "'; 
			p.height 		= " . $point->height . "; 
			p.width 		= " . $point->width . "; 
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
	$icon				= wp_get_attachment_image_src($default_icon_id, [60, 60])[0];
	$html 		.= "
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
				isDesabled		: ". ($is_lock ? 1 : 0). ",
				isSearch		: ". ($is_search ? 1 : 0). ",
				isZoomer		: ". ($is_zoomer ? 1 : 0). ",
				isAdmin			: ". (is_admin() ? 1 : 0). ",
				isMap			: true,
				default_icon	: '$icon'
			};
			/*
			var clear_form = new CustomEvent(
				'init_map', 
				{
					bubbles : true, 
					cancelable : true, 
					detail : {mData:mData, points:points}
				}
			);
			document.documentElement.dispatchEvent(clear_form);
			*/
			
			if(map_type == 1)
				ymaps.ready( function(){ init_map( mData, points ) } );
			else if (map_type == 2)
				init_map( mData, points );
			
		});

        jQuery(\"<style type='text/css'>.shm_container .leaflet-popup .leaflet-popup-content-wrapper .leaflet-popup-content .shml-body {max-height: ".round($height * 1.5)."px !important;} </style>\").appendTo('head');

	</script>";
	return $html;
}