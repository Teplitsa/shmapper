<?php

class ShMapperPointMessage extends SMC_Post
{
	static function init()
	{
		add_action('init',							[__CLASS__, 'add_class'], 14 );	
		add_filter( "shmapper_driver_feed_after",	[__CLASS__, "shmapper_driver_feed_after"], 15, 2);		
		parent::init();
	}
	static function get_type()
	{
		return SHMAPPER_POINT_MESSAGE;
	}
	
	static function add_class()
	{
		$labels = array(
			'name' => __('Map marker message', SHMAPPER),
			'singular_name' => __("Map marker message", SHMAPPER),
			'add_new' => __("Add Map marker message", SHMAPPER),
			'add_new_item' => __("Add Map marker message", SHMAPPER),
			'edit_item' => __("Edit Map marker message", SHMAPPER),
			'new_item' => __("Add Map marker message", SHMAPPER),
			'all_items' => __("All Map marker messages", SHMAPPER),
			'view_item' => __("view Map marker message", SHMAPPER),
			'search_items' => __("Search Map marker message", SHMAPPER),
			'not_found' =>  __("Map marker message not found", SHMAPPER),
			'not_found_in_trash' => __("No found Map marker message in trash", SHMAPPER),
			'menu_name' => __("Map marker messages", SHMAPPER)
		);
		$args = array(
			 'labels' => $labels
			,'public' => true
			,'show_ui' => true
			,'has_archive' => true 
			,'exclude_from_search' => false
			,'menu_position' => 19
			,'menu_icon' => "dashicons-location"
			,'show_in_menu' => "shm_page"
			,'show_in_rest' => true
			,'supports' => array(  'title', "editor", "thumbnail")
			,'capability_type' => 'post'
		);
		register_post_type(SHMAPPER_POINT_MESSAGE, $args);
	}
	
	static function view_admin_edit($obj)
	{
		require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
		$SMC_Object_type	= SMC_Object_Type::get_instance();
		$bb					= $SMC_Object_type->object [static::get_type()];
		$html = "";
		foreach($bb as $key=>$value)
		{
			if($key == 't' || $key == 'class' ) continue;
			$meta = get_post_meta( $obj->id, $key, true);
			switch( $value['type'] )
			{
				case "number":
					$h = "<input type='number' name='$key' id='$key' value='$meta' class='sh-form'/>";
					break;
				case "boolean":
					$h = "<input type='checkbox' class='checkbox' name='$key' id='$key' value='1' " . checked(1, $meta, 0) . "/><label for='$key'></label>";
					break;
				case "post":
					$class = $value['class'];
					$h = $class::wp_dropdown([
						"class" 	=> "form-control",
						"name"		=> $key,
						"selected"	=> $meta,
						'args'		=> -1
					]);
					break;
				default:
					$h = "<input type='' name='$key' id='$key' value='$meta' class='sh-form'/>";
			}
			
			$html .="<div class='shm-row' $opacity>
				<div class='shm-3 sh-right sh-align-middle'>".$value['name'] . "</div>
				<div class='shm-9'>
					$h
				</div>
			</div>
			<div class='spacer-5'></div>";
		}
		
		echo $html;
	}
	static function save_admin_edit($obj)
	{
		return [
		    SHM_POINT => $_POST[SHM_POINT]
		];
	}
	
	static function shmapper_driver_feed_after($text, $point_post)
	{
		$messages = get_posts([
			"post_type" 	=> static::get_type(),
			"post_status"	=> "publish",
			"numberposts"	=> -1,
			"meta_query"	=> [
				"relation"	=> "AND",
				[
					"key"	=> SHM_POINT, 
					"value"	=> $point_post->ID,
					"compare"=> "="					
				]
			]
		]);
		foreach($messages as $p)
		{
			/**/
			$terms = wp_get_object_terms( $p->ID, SHM_POINT_TYPE );
			$icons = "";
			foreach( $terms as $type )
			{
				$icons .= ShMapPointType::get_icon($type, false, false);
			}
			
			$text .= "<div class='shmapper-drive-post-content' message_id='" . $p->ID . "' pid='" . $point_post->ID . "'>
				<div class='title d-flex'>" . 
					$icons . " " . 
					$p->post_title .
				"</div>
				<div class='content'>" .
					$p->post_content .
				"</div>
			</div>";
		}
		/**/
		return $text  ;
	}
}
