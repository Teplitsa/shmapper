<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShMapperRequest extends SMC_Post
{
	static function init()
	{
		add_action('init',					[__CLASS__, 'add_class'], 15 );
		add_action('before_delete_post', 	[__CLASS__, 'before_delete_post'] ); 
		add_action( 'admin_menu', 			[__CLASS__, 'add_menu_notification'] );
		parent::init();
	}
	static function get_type()
	{
		return SHM_REQUEST;
	}	
	static function add_class()
	{
		$labels = array(
			'name' => __('Map Request', SHMAPPER),
			'singular_name' => __("Map Request", SHMAPPER),
			'add_new' => __("add Map Request", SHMAPPER),
			'add_new_item' => __("add Map Request", SHMAPPER),
			'edit_item' => __("edit Map Request", SHMAPPER),
			'new_item' => __("add Map Request", SHMAPPER),
			'all_items' => __("all Map Requests", SHMAPPER),
			'view_item' => __("view Map Request", SHMAPPER),
			'search_items' => __("search Map Request", SHMAPPER),
			'not_found' =>  __("Map Request not found", SHMAPPER),
			'not_found_in_trash' => __("no found Map Request in trash", SHMAPPER),
			'menu_name' => __("all Map Requests", SHMAPPER)
		);
		$args = array(
			 'labels' => $labels
			,'public' => true
			,'show_ui' => true
			,'has_archive' => true 
			,'exclude_from_search' => false
			,'menu_position' => 19
			,'menu_icon' => "dashicons-edit"
			,'show_in_menu' => "shm_page"
			,'show_in_rest' => true
			,'supports' => array(  'title', "editor", "thumbnail" )
			,'capability_type' => 'page'
		);
		register_post_type(SHM_REQUEST, $args);
	}
	
	static function add_views_column( $columns )
	{
		$columns = parent::add_views_column( $columns );
		unset($columns['contacts']);
		unset($columns['location']);
		unset($columns['longitude']);
		unset($columns['notify_date']);
		unset($columns['notify_user']);
		unset($columns['author']);
		$columns['thumb'] = "<div class='shm-camera' title='" . __("Image", SHMAPPER) ."'></div>";
		$new = [];
		foreach($columns as $key => $val)
		{
			switch($key)
			{
				case "notified":
					$new[$key] = __("Approving", SHMAPPER);
					break;
				case "session":
					$new["session"] = __("Author");
					break;
				case "latitude":
					$new["location"] = __("GEO location", SHMAPPER);
					break;
				default:
					$new[$key] = $val;
			}
		}
		return $new;
	}
	static function fill_views_column($column_name, $post_id) 
	{	
		$obj = static::get_instance( $post_id );
		switch($column_name)
		{
			case "location":
				echo __("Latitude", SHMAPPER).": <strong>" . $obj->get_meta("latitude") ."</strong>".
				"<br>".
				 __("Longitude", SHMAPPER).": <strong>" . $obj->get_meta("longitude") ."</strong>".
				"<br>".
				 __("Location", SHMAPPER).": <strong>" . $obj->get_meta("location") ."</strong>";
				break;
			case "session":
				$contacts = $obj->get_meta("contacts");
				echo implode("<br>", $contacts);
				break;
			case "type":
				$term_id = $obj->get_meta("type");
				$term = get_term($term_id, SHM_POINT_TYPE);
				if($term_id)
				{
					$icon = ShMapPointType::get_icon($term , $obj->get_meta("notified"));
				}
				else
				{
					$map_id = $obj->get_meta("map");
					$diid = get_post_meta($map_id, "default_icon_id", true);
					$icon	= "<div class='shm_type_icon' style='background-image:url(" . wp_get_attachment_image_url($diid, [60, 60]).");'>
					</div>";	
				}
				echo $icon;
				break;
			case "notified":
				echo $obj->get_notified_form();
				break;
			case "thumb":
				$img = get_the_post_thumbnail_url( $post_id, [75, 75] );
				echo "<div class='shm_type_icon2' style='background-image:url($img);' title='$img'></div>" ;
				break;
			default:
				parent::fill_views_column($column_name, $post_id);
				break;
		}
	}

	static function view_admin_edit($obj)
	{

	    require_once(SHM_REAL_PATH."class/SMC_Object_type.php");

		$SMC_Object_type	= SMC_Object_Type::get_instance();
		$bb				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];
        $html = '';

		foreach($bb as $key => $value)
		{
			if($key == 't' || $key == 'class' || $key == 'contacts' || $key == 'notify_user' ) continue;
			$meta = get_post_meta( $obj->id, $key, true);

//			$$key = $meta;

			switch( $value['type'] )
			{
				case "number":
					$h = "<input type='number' name='$key' id='$key' value='$meta' class='sh-form'/>";
					break;
				case "boolean":
					$h = "<input type='checkbox' class='checkbox' name='$key' id='$key' value='1' " . checked(1, $meta, 0)."><label for='$key'>".(mb_strlen($meta) > 1 ? $meta : '')."</label>";
					break;
				case "post":
					$h = "$meta";
					break;
				default:
					$h = "<input type='' name='$key' id='$key' value='$meta' class='sh-form'>";
			}
			switch($key)
			{
				case "map":
					$h = ShmMap::wp_dropdown([
						"selected"	=> $meta,
						"class"		=> "sh-form",
						"name"		=> "map",
						"id"		=> "map",
						"posts"     => ShmMap::get_all(),
					]);
					break;
				case "type":
					$h = ShMapPointType::get_ganre_swicher([
						'selected' 	=> $meta,
						'prefix'	=> "type",
						'col_width'	=> 3
					], 'radio' );
					
					
					break;
				case "description":
					$h = "<textarea name='$key' id='$key' class='sh-form'>$meta</textarea>";
					break;
			}

			$html .= "<div class='shm-row'>
				<div class='shm-3 shm-md-12 sh-right sh-align-middle'>".$value['name'] . "</div>
				<div class='shm-9 shm-md-12 '>
					$h
				</div>
			</div>
			<div class='spacer-5'></div>";
		}

		echo empty($html) ? '' : $html;

	}
	static function save_admin_edit($obj)
	{
		require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
		$SMC_Object_type	= SMC_Object_Type::get_instance();
		$_obj				= $SMC_Object_type->object [static::get_type()];
		$arr 				= [];
		foreach($_obj as $key=>$value)
		{
			if( $key == 't' || $key == 'class'  || $key == 'contacts' || $key == 'notify_user' ) continue;
			if($key == "notified" && $_POST[$key] != 1)
				$arr[$key] = -1;
			else
			    $arr[$key] = sanitize_text_field($_POST[$key]);
		}
		
		return $arr;
	}
	static function insert($data)
	{
		$h = array();
		$map 			= ShmMap::get_instance((int)$data['id']);
		$h['map_id'] 	= $map->get('post_title');
		$contents		= array();
		$form			= $map->get_meta('form_forms');
		$emails			= array();
		$contacts		= array();
		$title = $description = '';

		if( $data['shm_form_name'] )
		{
		    $contacts[] = sanitize_text_field($data['shm_form_name']);
		    $author		= sanitize_text_field($data['shm_form_name']);
		}
		if( $data['shm_form_phone'] )
		    $contacts[] = sanitize_text_field($data['shm_form_phone']);
		if( $data['shm_form_email'])
		{
		    $contacts[] = sanitize_email($data['shm_form_email']);
		    $emails[] 	= sanitize_email($data['shm_form_email']);
		}
		foreach($form as $key => $val)
		{
			if($val['type'] == SHMAPPER_MARK_TYPE_ID)
				continue;
			if($val['type'] == SHMAPPER_EMAIL_TYPE_ID)
			{
			    $emails[] 	= sanitize_email($data['elem'][$key]);
			    $contacts[] = sanitize_email($data['elem'][$key]);					
			}	
			if(
				$val['type'] == SHMAPPER_PHONE_TYPE_ID ||
				$val['type'] == SHMAPPER_NAME_TYPE_ID 
			)
			    $contacts[] = sanitize_text_field($data['elem'][$key]);
			if($val['type'] == SHMAPPER_NAME_TYPE_ID)
			    $author		= sanitize_text_field($data['elem'][$key]);
			if($val['type'] == SHMAPPER_TEXTAREA_TYPE_ID)
			{
				$description .= $data['elem'][$key];
			}
			if($key == 1)
			{
				//$description .= $data['elem'][1] . ", title type=". SHMAPPER_TITLE_TYPE_ID;
			}
			if($val['type'] == SHMAPPER_TITLE_TYPE_ID)
			{
			    $title .= sanitize_text_field($data['elem'][$key]);
			}
			$tpp  = ShmForm::get_type_by( "id", $val['type'] );
			if(SHMAPPER_IMAGE_TYPE_ID != $val['type'] )
			    $contents[] =  "<small>".$tpp['title'].":</small> <strong>".sanitize_text_field($data['elem'][$key])."</strong>";
		}
		$contents[] =  "<div>" . sanitize_text_field($data['shm_point_loc']) . "</div>";
		$h['contents'] 		= implode("<br>", $contents);
		
		$arr = apply_filters(
			"shm_before_insert_request", 
			[
				"post_type" 	=> static::get_type(),
				"post_name" 	=> $title ? $title : $map->get("post_name"),
				"post_title" 	=> $title ? $title : $map->get("post_title"),
				"post_content"	=> sanitize_text_field( $h['contents'] ),
				"map"			=> (int)$data['id'],
				"location"		=> sanitize_text_field($data['shm_point_loc']),
				"latitude"		=> ( (int) ($data['shm_point_lat'] * 10000)) / 10000,
				"longitude"		=> ( (int) ($data['shm_point_lon'] * 10000)) / 10000,
				"type"			=> sanitize_text_field($data['shm_point_type']),
				"contacts"		=> $contacts,
				"description"	=> $description,
				"author"		=> $author
			],  
			$data
		);
		if(!$arr['forbiddance'])
			$new_req = parent::insert($arr);
		
		$arr = apply_filters(
			"shm_after_insert_request", 
			[
				"post_type" 	=> static::get_type(),
				"post_name" 	=> $title ? $title : $map->get("post_name"),
				"post_title" 	=> $title ? $title : $map->get("post_title"),
				"post_content"	=> sanitize_text_field( $h['contents'] ),
				"map"			=> (int)$data['id'],
				"location"		=> sanitize_text_field($data['shm_point_loc']),
				"latitude"		=> ( (int) ($data['shm_point_lat'] * 10000)) / 10000,
				"longitude"		=> ( (int) ($data['shm_point_lon'] * 10000)) / 10000,
				"type"			=> sanitize_text_field($data['shm_point_type']),
				"contacts"		=> $contacts,
				"description"	=> $description,
				"author"		=> $author
			],
			$new_req, 
			$data
		);		
		//attach
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		$overrides = array( 'test_form' => false );
		foreach( $_FILES as $file )
		{				
			$movefile = wp_handle_upload( $file, $overrides );
			if ( $movefile && empty($movefile['error']) ) 
			{
				$filename  		= $movefile['file'];
				$filetype 		= $movefile['type'];
				$filetype 		= wp_check_filetype( basename( $filename ), null );
				$url			= $movefile['url'];
				$parent_post_id = $new_req->id;
				$wp_upload_dir 	= wp_upload_dir();
				
				$attachment 	= [
					'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				];
				// Вставляем запись в базу данных.
				$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
				set_post_thumbnail($parent_post_id, $attach_id);
				$h['contents'] .= "<p>
					<img src='". wp_get_attachment_image_url($attach_id, "full")."' style='max-height:200px; width:auto' />
				</p>";
			} 
			else 
			{
				//echo "File upload attacks are possible!\n";
			}
			
		}
		
		
		
		//notify map owner
		if($notify_owner = $map->get_meta("notify_owner"))
		{
			$author_id 	= $map->get("post_author");
			$user 		= get_user_by("id", $author_id);
			$email	 	= $user->get('user_email');
			$semail  	=  get_bloginfo( "admin_email" );
			$suser		= $author ? $author : __("Uknown User", SHMAPPER);
			$site		= get_bloginfo("name");
			$headers = array(
				"From: $site <$semail>",
				'content-type: text/html',
			);
			$is = wp_mail(
				$email,
				sprintf(__("<%s> Request to your Map from [%s] [%s]", SHMAPPER), $site, $suser, $map->get("post_title")) ,
				$h['contents']. "\n\n<p>" . 
				sprintf(__("You may see this %s", SHMAPPER), get_bloginfo("url") . "/wp-admin/edit.php?post_type=shm_request")
			    . "</p>",
				$headers
			);
		}
		
		return $new_req;
	}
	function get_notified_form()
	{
		if($notify = $this->get_meta("notified") > 0) {

			$user = get_user_by("id", $this->get_meta("notify_user"));
			$user_name = '';

			$html = '';
			if ( $user ) {
				$html .= "<p>" . $user_name . "</p>";
			} else {
				$user_name = esc_html__( 'Visitor', 'shmapper-by-teplitsa' );
			}
			if ( $this->get_meta("notify_date") ) {
				$html .= "<p>" . date("j.n.Y H:m", $this->get_meta("notify_date"));
			}

		}
		else
		{
			$html = "
			<div clas='shm-row'>
				<div class='shm-12'>
					<div class='button button-large button-primary ' shm_notify_req='$this->id'>".
						__("Approve", SHMAPPER). 
					"</div>
					<div class='button button-large button-alert' shm_trash_req='$this->id' title='".__("Trash", SHMAPPER)."'>
						<span class='dashicons dashicons-trash' style='margin-top: 4px;'></span>
					</div>
				</div>
			</div>";
		}
		return $html;
	}
	function notify()
	{
		$this->update_meta("notify_user", get_current_user_id());
		$this->update_meta("notify_date", time());
		$this->update_meta("notified", true);
		$point = ShmPoint::insert([
			"post_title"	=> (string)$this->get("post_title"),
			"post_name"		=> (string)$this->get("post_name"),
			"post_content"	=> (string)$this->get_meta("description"),
			"latitude"		=> $this->get_meta("latitude"),
			"longitude"		=> $this->get_meta("longitude"),
			"location"		=> $this->get_meta("location"),
			"type"			=> (int)$this->get_meta("type"),
			"map_id"		=> (int)$this->get_meta("map"),
		]);
		if($attach_id = get_post_thumbnail_id($this->id))
		{
			set_post_thumbnail($point->id, (int)$attach_id);
		}
		return $point;
	}
	static function before_delete_post( $post_id )
	{
		$obj = static::get_instance($post_id);
		if($obj->get("post_type") !== static::get_type()) return;
		if( has_post_thumbnail( $post_id ) ) 
		{
				$attachment_id = get_post_thumbnail_id( $post_id );
				if (
					empty ( 
						get_posts([
							'post_type' => 'any', 
							'post_status' => 'any', 
							'fields' => 'ids', 
							'no_found_rows' => true, 
							'posts_per_page' => -1, 
							'meta_key' => '_thumbnail_id', 
							'meta_value' => $attachment_id, 
							'post__not_in' => array( $post_id )
						]) 
					)  
				) 
				{
					$attachment_urls = array( wp_get_attachment_url( $attachment_id ) );
					foreach ( get_intermediate_image_sizes() as $size ) 
					{
						$intermediate = image_get_intermediate_size( $attachment_id, $size );
						if ( $intermediate )
						{
							$attachment_urls[] = $intermediate['url'];
						}
					}			  
					$used = array();
					foreach ( $attachment_urls as $attachment_url ) 
					{
						$used = array_merge( 
							$used, 
							get_posts( [ 
								'post_type' => 'any',
								'post_status' => 'any', 
								'fields' => 'ids', 
								'no_found_rows' => true, 
								'posts_per_page' => -1, 
								's' => $attachment_url, 
								'post__not_in' => array( $post_id )
							] ) 
						);
					}			  
					if ( empty( $used ) )
						wp_delete_attachment( $attachment_id, true );
				}
		 }
		return $post_id;
	}
	static function add_menu_notification()
	{
		global $submenu ;
		$not_approved = get_posts([
			"numberposts" 	=> -1,
			"post_type"		=> static::get_type(),
			"fields"		=> "ids",
			"post_status"	=> "publish",
			"meta_query"	=> [
				"relation"	=> "OR",
				[
					"key"		=> "notified",
					"compare"	=> "NOT EXISTS"
				],
				[
					"key"		=> "notified",
					"value"		=> -1,
					"compare"	=> "=",
					
				]
			]
		]);
		//var_dump($submenu["shm_page"] );
		//wp_die();
		if(count($not_approved))
		{
			foreach ( $submenu["shm_page"] as $key => $value ) 
			{
				if ( $submenu["shm_page"][$key][2] ==  "edit.php?post_type=shm_request"  ) 
				{
					$submenu["shm_page"][$key][0] .= ' <span class="awaiting-mod">' . count($not_approved) . '</span>';
					return;
				}
			}
		}
	}
}
