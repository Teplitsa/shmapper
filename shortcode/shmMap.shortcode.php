<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

function shmMap($args)
{	 
	$args = apply_filters(
		"shm_shortcode_args",
		shortcode_atts( array(
			'heigth' 	=> 450,
			"id"		=> -1,
			"map"		=> false,
			"form"		=> false,
			"uniq"		=> false
		), $args, 'shmMap' ),
		$args
	);  
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
	$html 		= "<div class='shm-title-6 shm-map-title'>" . $map->get("post_title")  . "</div>";
	$html 		= apply_filters("shm_before_front_map", $html, $args);
	if($map_enb)
	{
		$html 	.= $map->draw($args);		
	}
	$html 		= apply_filters("shm_after_front_map", $html, $args);
	if( $form_enb && $map->get_meta("is_form") && !ShMapper::$options['shm_map_is_crowdsourced'])
	{
		$form_title = $map->get_meta("form_title");
		$form_forms = $map->get_meta("form_forms");
		$html 	.= "
		<div class='shm-row '>
			<div class='shm-12'>
				<form class='shm-form-request' id='form$id' form_id='ShmMap$id$uniq' map_id='$id'>					
					<div class='shm-title'>
						$form_title
					</div>
					<div id='form_forms'>".
						ShmForm::form( $form_forms, $map ).
					"</div>
					<div class='shm-form-element'>
						<input type='submit' class='shm-request' value='" . __("Send request", SHMAPPER) . "'/>
					</div>
				</form>
			</div>
		</div>";
	}
	$html 		= apply_filters("shm_final_after_front_map", $html, $args);
	return $html;
}
