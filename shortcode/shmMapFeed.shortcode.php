<?php 
function shmMapFeed($args)
{
	$args = shortcode_atts( array(
		'heigth' 	=> 450,
		"id"		=> -1,
		"map"		=> false,
		"form"		=> false,
		"uniq"		=> false
	), $args, 'shmMapFeed' );
	$id				= $args['id'];
	$args['uniq']	= $args['uniq'] ? $args['uniq'] : substr( MD5(rand(0, 100000000)), 0, 8 );
	$uniq			= $args['uniq'];
	$map 			= ShmMap::get_instance($args['id']);
	if(!$map->is_enabled() || $map->get("post_type") !== SHM_MAP)
	{
		return __("No map on ID ", SHMAPPER) . $args['id'];
	}
	$points 		= $map->get_map_points([ "order" => "ASC", "orderby" => "date" ]);
	foreach($points as $p)
	{
		$point = ShmPoint::get_instance($p);
		$html .= $point->feed_draw(); 
	}
	return $html;
}