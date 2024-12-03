<?php

define("SHM_TRACKS_CONTENT_PRIORITY", 20);

class ShMaperTrack extends SMC_Post     
{
	static function init()
	{ 
		add_action( 'init',									[__CLASS__, 'add_class'], 99 );
		add_filter( "shm_admin_element",					[__CLASS__, "shm_admin_element"], 10, 3 );
		add_filter( "shmapper_get_form_fild_types", 		[__CLASS__, "shmapper_get_form_fild_types"]);
		add_filter( "shmapper_front_form_element", 			[__CLASS__, "shmapper_front_form_element"], 10, 2);
		add_filter( "shmapper_form_after_fields", 			[__CLASS__, "shmapper_form_after_fields"],  10, 4);
		add_filter( "smc-post-admin-edit", 					[__CLASS__, "smc_post_admin_edit"],  10, 5);
		add_filter( 'smc_post_fill_views_column', 			[__CLASS__, 'smc_post_fill_views_column'], 10, 5 );
		add_filter( 'shm_before_insert_request', 			[__CLASS__, 'shm_before_insert_request'], 10, 2 );	
		add_filter( "the_content",							[__CLASS__, "the_content"], SHM_TRACKS_CONTENT_PRIORITY );
		add_action( 'before_delete_post', 					[__CLASS__, 'delete_post'], 10, 2 );
		add_action( 'delete_post', 							[__CLASS__, 'delete_post'], 10, 2 );
		add_action( 'deleted_post', 						[__CLASS__, 'delete_post'], 10, 2 );
		add_action( 'after_delete_post', 					[__CLASS__, 'delete_post'], 10, 2 );
		parent::init();
	}
	static function shm_admin_element($html, $id, $data)
	{
		if( $data['type'] == SHMAPPER_TRACKS_DRAW)
		{
			if( ShMapper::$options['map_api'] != 1 ) 
				return "<li shm-num='$id'>
					" . esc_html__( 'Shmapper Track exists only in Yandex map API', SHMAPPER ) . "
				</li>";
			
		};
		return $html;
	}
	static function delete_post( $postid, $post )
	{
		$points = ShMapperTracksPoint::get_all([ static::get_type() => $postid ]);
		foreach($points as $point) 
		{
			wp_delete_post( $point->ID );
		}
	}
	static function the_content($content)
	{
		$post_id = get_the_ID();
		$post_type = get_post_type();
		if ( $post_id ) {
			if($post_type == static::get_type() && (is_single() || is_archive() ))
			{

				$track = static::get_instance($post_id);
				remove_filter( 'the_content', [__CLASS__, "the_content"], SHM_TRACKS_CONTENT_PRIORITY);
				return $track->draw() . $track->get_owner_list( __("Usage in Maps: ", SHMAPPER), ", ", " "  ) . "<div class='spacer-30'></div>".$content;

			}
			return $content;
		}
		return $content;
	}
	function draw( $params=["height"=>400, "show_markers" => true] )
	{
		if( ShMapper::$options['map_api'] != 1 ) 
			return esc_html__( 'Shmapper Track exists only in Yandex map API', SHMAPPER );
		$map = ShmMap::get_instance($this->get_meta(SHM_MAP));
		$meta = $this->get_meta("track");

		// Tracker type width.
		$tracker_width      = 4;
		$tracker_type_id    = get_post_meta( $this->id, 'shmapper_track_type', true);
		$tracker_type_width = get_term_meta( $tracker_type_id, 'width', true );
		if ( $tracker_type_width ) {
			$tracker_width = $tracker_type_width;
		}

		// Tracker type color.
		$tracker_color      = '#0066ff';
		$tracker_type_color = get_term_meta( $tracker_type_id, 'color', true );
		if ( $tracker_type_color ) {
			$tracker_color = $tracker_type_color;
		}

		$mm = [];
		try 
		{
			$meta = json_decode($meta);
		}
		catch (Exception $e)
		{
			$meta = [];
		}
		foreach($meta as $m)
		{
			if( is_array($m) )
			{
				$mm[] 	= "[" . implode(",", $m) . "]";
			}
		}
		$points = ShMapperTracksPoint::get_all([ static::get_type() => $this->id]);
		$markers = [];
		foreach($points as $point) 
		{
			$shtp 		= ShMapperTracksPoint::get_instance($point);
			$term_id 	= $shtp->get_meta(SHM_TRACK_TYPE);
			if ( ! $term_id ) {
				$term_id = '""';
			}
			$icon = '';
			if ( isset( ShMapPointType::get_icon_src($term_id)[0] ) ) {
				$icon = ShMapPointType::get_icon_src($term_id)[0];
			}
			$markers[] = "
			{ 
				coords:[" . $shtp->get_meta("latitude") . "," . $shtp->get_meta("longitude") . "], 
				track_id:\"".$point->ID."\", 
				post_title:\"". $shtp->get("post_title") ."\", 
				post_content:\"" .  str_replace([ "\r\n", "\r", "\n", '"' ], '',  ($shtp->get("post_content"))) ."\",
				" . SHMAPPER_TRACKS_TRACK . ":" . $shtp->get_meta(SHMAPPER_TRACKS_TRACK) . ", 
				" . SHM_TRACK_TYPE . ":$term_id, 
				shm_clr: \"" . get_term_meta( $term_id, "color", true)."\", 
				icon:\"" . $icon . "\" 
			}";
		}
		return "<div id='map_track_" . $map->id . "' style='width:100%; height:" . $params['height'] . "px;'>
				
		</div>
		<script>
			ymaps.ready( function()
			{
				var myMap2 = new ymaps.Map('map_track_" . $map->id . "', {
					center: [55.73, 37.75],
					zoom:8,
				}, 
				{
					searchControlProvider: 'yandex#search'
				});
				
				var myPolyline = new ymaps.Polyline(
					[ " . implode( ",", $mm ). " ],
					{ }, 
					{
						strokeWidth: $tracker_width,
						strokeColor: '$tracker_color'
					}
				);
				myMap2.geoObjects.add(myPolyline);
				myMap2.setBounds(myPolyline.geometry.getBounds());  
				var tMarkers 	= [ " . implode(",", $markers) ." ]; 
				for(var i=0; i < tMarkers.length; i++)
				{
					var e = tMarkers[i];
					console.log(e); 
					var shm_placemark = drawPlacemark(
						myMap2, 
						e.coords, 
						{ 
							background_image	: e.icon,
							shm_type_id 		: e.shmapper_track,
							shm_clr  			: e.shm_clr,
							post_title  		: e.post_title,
							post_content  		: e.post_content
						}, 
						{
							
						} 
					) ; 
					myMap2.geoObjects.add(shm_placemark);
				} 
			} );  
		</script>";
	}
	function get_owner_list( $before = "", $separator = "<br>", $after = "" )
	{
		$map = ShmMap::get_instance($this->get_meta(SHM_MAP));
		$link = is_admin() ? "/wp-admin/post.php?post=" . $map->id . "&action=edit" : get_permalink( $map->id );
		return $before . "<a href='$link'>" . $map->get("post_title") . "</a>";
	}
	
	static function smc_post_fill_views_column( $html, $column_name, $post_id, $obj, $meta )
	{
		switch($obj[$column_name]['type'])
		{
			case "track":
				if( ShMapper::$options['map_api'] != 1 ) 
					return "<div class='shmapper-track-map' post_id='$post_id' id='map_$post_id'>" . esc_html__( 'Shmapper Track exists only in Yandex map API', SHMAPPER ) . "</div>";
				$mm = [];
				
				// return htmlspecialchars( urldecode( $meta ) );

				try 
				{
					$_meta = json_decode($meta);
				}
				catch (Exception $e)
				{
					$_meta = [];
				}

				if ( null === $_meta ) {
					$_meta = [];
				}

				// Tracker type width.
				$tracker_width      = 4;
				$tracker_type_id    = get_post_meta( $post_id, 'shmapper_track_type', true);
				$tracker_type_width = get_term_meta( $tracker_type_id, 'width', true );
				if ( $tracker_type_width ) {
					$tracker_width = $tracker_type_width;
				}

				// Tracker type color.
				$tracker_color      = '#0066ff';
				$tracker_type_color = get_term_meta( $tracker_type_id, 'color', true );
				if ( $tracker_type_color ) {
					$tracker_color = $tracker_type_color;
				}

				foreach($_meta as $m)
				{
					if( is_array($m) )
					{
						$m2 = '';
						if ( isset( $m[2] ) ) {
							$m2 = $m[2];
						}
						$mm[] 	= "[" . $m[0] . "," . $m[1] . "," . wp_json_encode( $m2 ) ."]";
					}
				}
				
				$points = ShMapperTracksPoint::get_all([ static::get_type() => $post_id ]);
				$markers = [];
				foreach($points as $point) 
				{
					$shtp 		= ShMapperTracksPoint::get_instance($point);
					$term_id 	= $shtp->get_meta(SHM_POINT_TYPE);
					if ( ! $term_id ) {
						$term_id = '""';
					}
					$icon = '';
					if ( isset( ShMapPointType::get_icon_src($term_id)[0] ) ) {
						$icon = ShMapPointType::get_icon_src($term_id)[0];
					}
					$markers[] = "
					{ 
						coords:[" . $shtp->get_meta("latitude") . "," . $shtp->get_meta("longitude") . "], 
						track_id:\"".$point->ID."\", 
						post_title:\"". $shtp->get("post_title") ."\", 
						post_content:\"" .  str_replace([ "\r\n", "\r", "\n", '"' ], '',  ($shtp->get("post_content"))) ."\",
						" . SHMAPPER_TRACKS_TRACK . ":" . $shtp->get_meta(SHMAPPER_TRACKS_TRACK) . ", 
						" . SHM_POINT_TYPE . ":$term_id, 
						shm_clr: \"" . get_term_meta( $term_id, "color", true)."\",
						icon: \"" . $icon . "\"
					}";
				}
				
				return "<div class='shmapper-track-map' post_id='$post_id' id='map_$post_id'>
					
				</div>
				<script>
					ymaps.ready( function()
					{
						var myMap = new ymaps.Map('map_$post_id', {
							center: [55.73, 37.75],
							zoom:8,
							controls: [ ],
						}, 
						{
							searchControlProvider: 'yandex#search'
						});
						
						var myPolyline = new ymaps.Polyline(
							[ " . implode( ",", $mm ). " ],
							{ }, 
							{
								strokeWidth: $tracker_width,
								strokeColor: '$tracker_color'
							}
						);
						[ " . implode( ",", $markers ) . " ].forEach(function(e)
						{
							
						});
						myMap.geoObjects.add(myPolyline);
						myMap.setBounds(myPolyline.geometry.getBounds());   
					} ); 
				</script>";			
		}
		return $html;
	}

	/*	
		input forms edit post's meta ( card "Parameters" )
	*/	
	static function smc_post_admin_edit( $html, $meta, $obj, $key, $value )
	{ 
		if( $value['type'] == "gpx")
		{
			$html = "<div>
				<div class='button gpx' gpx-src-id='$obj->id' >".
					__("See source", SHMAPPER_TRACKS).
					"<pre class='_hidden' gpx-pre-data=''>".$obj->track_to_xml()."</pre>
				</div>
				<div class='button gpx-dnld' gpx-dnld-file='$obj->id' gpx-data-title='" . $obj->get("post_title") . "'>".
					__("Download *.gpx file", SHMAPPER_TRACKS).
				"</div>
			</div>";
		}
		if( $value['type'] == "id")
		{
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
			$SMC_Object_type	= SMC_Object_Type::get_instance();
			$class				= $SMC_Object_type->get_class_by_name($key);
			switch($value['object'])
			{
				case "post":
					
					$html = $class::wp_dropdown([
						"style" 	=> "width:100%;",
						"name"		=> $key,
						"selected"	=> $meta,
						"posts"		=> $class::get_all()
					]);
					break;
				case "taxonomy":
					$html = $class::wp_dropdown([
						"style" 	=> "width:100%;",
						"name"		=> $key,
						"selected"	=> [$meta]
					]); 
					break;
			}
		}
		
		if( $value['type'] == "track")
		{ 
			$mm = [];

			try {
				$_meta = json_decode($meta);
			} catch (Exception $e) {
				$_meta = [];
			}



			// Tracker type width.
			$tracker_width      = 4;
			$tracker_type_id    = get_post_meta( $obj->id, 'shmapper_track_type', true);
			$tracker_type_width = get_term_meta( $tracker_type_id, 'width', true );
			if ( $tracker_type_width ) {
				$tracker_width = $tracker_type_width;
			}

			// Tracker type color.
			$tracker_color      = '#0066ff';
			$tracker_type_color = get_term_meta( $tracker_type_id, 'color', true );
			if ( $tracker_type_color ) {
				$tracker_color = $tracker_type_color;
			}

			if ( ! $_meta ) {
				$_meta = [];
			}

			foreach($_meta as $m)
			{
				if( is_array($m) )
				{
					$m2 = null;
					if ( isset( $m[2] ) ) {
						$m2 = $m[2];
					}
					$mm[] 	= "[" . $m[0] . "," . $m[1] . "," . wp_json_encode( $m2 ) . "]";
				}
			}
			$points = ShMapperTracksPoint::get_all([ static::get_type() => $obj->id]);
			$markers = [];
			foreach($points as $point) 
			{
				$shtp 		= ShMapperTracksPoint::get_instance($point);
				$term_id 	= $shtp->get_meta(SHM_POINT_TYPE);
				if ( ! $term_id ) {
					$term_id = '""';
				}
				$icon = '';
				if ( isset( ShMapPointType::get_icon_src($term_id)[0] ) ) {
					$icon = ShMapPointType::get_icon_src($term_id)[0];
				}
				$markers[] = "
				{ 
					marker_id: " . $shtp->id . ", 
					coords:[" . $shtp->get_meta("latitude") . "," . $shtp->get_meta("longitude") . "], 
					track_id:\"".$obj->id."\", 
					post_title:\"". $shtp->get("post_title") ."\", 
					post_content:\"" .  str_replace([ "\r\n", "\r", "\n", '"' ], '',  ($shtp->get("post_content"))) ."\",
					" . SHMAPPER_TRACKS_TRACK . ":" . $shtp->get_meta(SHMAPPER_TRACKS_TRACK) . ", 
					" . SHM_POINT_TYPE . ":$term_id, 
					shm_clr: \"" . get_term_meta( $term_id, "color", true)."\", 
					icon:\"" . $icon . "\" 
				}";
			}
			
			$map = ShmMap::get_instance(get_post_meta($obj->id, SHM_MAP, true));
			
			if( ShMapper::$options['map_api'] != 1 ) 
				return "<div gpx-id='$obj->id' id='shm_map--' style='width:100%; height:400px;' class='sh-align-middle'>
						" . esc_html__( 'Shmapper Track exists only in Yandex map API', SHMAPPER ) . "
					</div>
					<div>
						<input type='hidden' style='width:100%;' value='[" . implode( ",", $mm ) . "]' name='track'/>
					</div> ";
			
			$html = "<style>
				.shm-type-icon-1
				{
					border: 1px solid #99999970;
				}
			</style>
			<div> 
				<div gpx-id='$obj->id' id='shm_map' style='width:100%; height:400px;'>
				
				</div>
				<div>
					<input type='hidden' style='width:100%;' value='[" . implode( ",", $mm ) . "]' name='track'>
				</div> 
				<div class='spacer-10'></div> 
			</div>
			<script>
				tPoints 	= [ " . implode( ",", $mm ). " ];
				track_id 	= " . $obj->id . ";
				shmTrackWidth = " . $tracker_width . ";
				shmTrackColor = '$tracker_color';
				tMarkers 	= [
					" . implode(",", $markers) ."
				]; 
			</script>";
		}
		return $html;
	}

	static function save_admin_edit($obj)
	{
		$arr = [];
		$gpx = '';
		if ( isset( $_POST['gpx'] ) ) {
			$gpx = $_POST['gpx'];
		}
		$arr['gpx'] 			= $gpx;
		$arr['track'] 			= $_POST['track'];
		$arr['shm_author'] 		= $_POST['shm_author'];
		$arr['shm_author_email']= $_POST['shm_author_email'];
		$arr[SHM_MAP] 			= $_POST[SHM_MAP];
		$arr[SHM_TRACK_TYPE]	= $_POST[SHM_TRACK_TYPE];
		return $arr;
	}
	static function get_dump($data)
	{
		ob_start();
		var_dump($data);
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}


	static function shm_before_insert_request( $arr, $data )
	{
		//var_dump( $arr );
		//var_dump( $data );
		//wp_die(  );
		$map 			= ShmMap::get_instance((int)$data['id']);
		$form			= $map->get_meta('form_forms');
		if( $data['shm_form_name'] )
		{
			$shm_author = $data['shm_form_name'];
			if ( 'undefined' === $shm_author ) {
				$shm_author = esc_html__( 'Anonymous author', 'shmapper-by-teplitsa' );
			}
		}
		if( $data['shm_form_email'] )
		{
			$shm_author_email = $data['shm_form_email'];
			if ( 'undefined' === $shm_author_email ) {
				$shm_author_email = '';
			}
		}
		foreach($form as $key => $val)
		{
			if( $val['type'] == SHMAPPER_TRACKS_DRAW )
			{		
				if( ShMapper::$options['map_api'] != 1 ) 
				{
					return $arr;
				}
				$full_track		= urldecode( stripslashes($data["elem"][$key]) );
				$track_data 	= json_decode( $full_track );
				$track 			= wp_json_encode( $track_data->coords, true );
				$markers 		= $track_data->markers;
				
				$imgg = false;
				$content = $arr['description'];
				$gpx = "";
				if (isset( $_FILES[0] ) ) 
				{
					$fileTmpPath 	= $_FILES[0]['tmp_name'];
					$fileName 		= $_FILES[0]['name'];
					$fileSize 		= $_FILES[0]['size'];
					$fileType 		= $_FILES[0]['type'];
					$fileNameCmps 	= explode(".", $fileName);
					$fileExtension 	= strtolower(end($fileNameCmps));
					$newFileName 	= md5(time() . $fileName) . '.' . $fileExtension;
					$allowedfileExtensions = array('gpx', 'xml'); 
					if (in_array($fileExtension, $allowedfileExtensions)) 
					{
						$uploadFileDir 	= SHMTRACKS_REAL_PATH . 'temp/';
						$dest_path 		= $uploadFileDir . $newFileName;
						if(move_uploaded_file($fileTmpPath, $dest_path))
						{
							$gpx	.= file_get_contents($dest_path);
							unlink($dest_path);
						}
						else
						{
							$gpx .= "No upload GPX file";
						}
					}
				}
				else
				{
					$gpx_errors = "Errors: ". static::get_dump( $_FILES['error'] );
				}
			}
		}
		
		if( isset( $track_data->coords ) && count( $track_data->coords ) )
		{			
			$tracker = static::insert([
				"post_name" 	=> $arr["post_title"],
				"post_title" 	=> $arr["post_title"] ,
				"post_content" 	=> "" . $content . json_decode( $data ),
				'post_status'	=> ShMapper::$options['shm_map_marker_premoderation'] ? "draft" : "publish",
				"shm_author"	=> $shm_author,
				"shm_author_email"	=> $shm_author_email,
				"gpx"			=> $gpx,
				"gpx_errors"	=> $gpx_errors,
				"track"			=> $track,
				"full_track"	=> $full_track,
				"form"			=> json_decode( $data ),
				SHM_MAP			=> $map->id
			]);
			
			if( isset( $markers ) && count( $markers ) )
			{
				foreach($markers as $marker)
				{
					$m = ShMapperTracksPoint::insert([
						"post_name" 		=> $marker->post_title ,
						"post_title" 		=> $marker->post_title ,
						"post_content" 		=> $marker->post_content , 
						static::get_type()	=> $tracker->id,
						SHM_POINT_TYPE		=> $marker->shm_type_id , 
						"track"				=> $track,
						"latitude"			=> $marker->coordinates[0],
						"longitude"			=> $marker->coordinates[1]
					]);
				}
			}

			$arr['forbiddance'] = true;
		}
		return $arr;
	}
	static function get_type()
	{
		return SHMAPPER_TRACKS_TRACK;
	}
	static function add_class()
	{
		$labels = array(
			'name' => __('Map track', SHMAPPER_TRACKS),
			'singular_name' => __("Map track", SHMAPPER_TRACKS),
			'add_new' => __("Add Map track", SHMAPPER_TRACKS),
			'add_new_item' => __("Add Map track", SHMAPPER_TRACKS),
			'edit_item' => __("Edit Map track", SHMAPPER_TRACKS),
			'new_item' => __("Add Map track", SHMAPPER_TRACKS),
			'all_items' => __("All Map tracks", 'shmapper-by-teplitsa'),
			'view_item' => __("View Map track", SHMAPPER_TRACKS),
			'search_items' => __("Search Map track", SHMAPPER_TRACKS),
			'not_found' =>  __("Map track not found", SHMAPPER_TRACKS),
			'not_found_in_trash' => __("No found Map track in trash", SHMAPPER_TRACKS),
			'menu_name' => __("Map tracks", SHMAPPER_TRACKS)
		);
		$args = array(
			 'labels' => $labels
			,'public' => true
			,'show_ui' => true
			,'has_archive' => true 
			,'exclude_from_search' => false
			,'menu_position' => 25 
			,'show_in_menu' => "shm_page" 
			,'supports' => [ 'title', 'editor' ]
			,'capability_type' => 'page'
			,'taxonomies'          => [],
		);
		register_post_type(SHMAPPER_TRACKS_TRACK, $args);
	}
	static function shmapper_get_form_fild_types ($arr)
	{ 
		$arr[] = [
			"id"		=> SHMAPPER_TRACKS_DRAW,
			"name" 		=> SHMAPPER_TRACKS_DRAW, 
			"title" 	=> __("Track drawer", SHMAPPER_TRACKS), 
			'fields' 	=> [ 'title', 'description' ]
		];
		return $arr;
	}
	
	/*	
		Front form's element DRAW|UPLOAD TRACK
	*/	
	static function shmapper_front_form_element( $html1, $element )
	{		
		if( $element['type'] ==  SHMAPPER_TRACKS_DRAW )
		{ 
			if( ShMapper::$options['map_api'] != 1 ) 
			{
				return esc_html__( 'Shmapper Track exists only in Yandex map API', SHMAPPER );
			}

			$switched_enabled_markers = '';
			if ( isset( $element['switched_enabled_markers'] ) && $element['switched_enabled_markers'] ) {
				$switched_enabled_markers = $element['switched_enabled_markers'];
			}

			$el_gpx = '';
			if ( isset( $element['gpx'] ) ) {
				$el_gpx = $element['gpx'];
			}
			$require		= isset($element['require']) && $element['require'] == 1 ? " required " : ""; 
			$file_require	= isset($element['file_require']) && $element['file_require'] == 1 ? " required " : ""; 
			$type 			= ShmForm::get_type_by("id", $element['type']);
			$data_types = " data-types='" . implode( ",", $type['fields'] ) . "' ";

			//$shmW = $el_gpx ? " shm-w-50 " : " shm-w-100 ";

			$shmW = '';

			$btnText = $element['draw_button_label'] ? $element['draw_button_label'] : __( 'Start draw new Track', SHMAPPER_TRACKS);
			$editBtn = "<div class='shmapper_tracks_edit $shmW' $switched_enabled_markers>
				<div class='button shm-form-button'>" . $btnText . "</div>
			</div>
			";

			if( isset( $element['gpx']) && $element['gpx'] )
			{
				$flop = $element['placeholder'] ? $element['placeholder'] : __("Сhoose local GPX-file", SHMAPPER_TRACKS);
				$file_map = $flop; 
				$uploader = "<div class='shm-form-file shm-form-track' $switched_enabled_markers> 
					<label class='button shm-form-file-label' flop='$flop'>$file_map</label>
					<input type='file' class='sh-form-file' name='__elem[]' accept='.gpx' $file_require  $data_types title='" . __("Сhoose files", SHMAPPER) . "'/> 
				</div>";

				$pult = "
				<div class='shm-track-pult'>
					<div class='shm-form-slider'>
						<div class='shm-flex'>
							<div class='shm-descr-pult'>".
								__("Set range fliping of route's dots", SHMAPPER_TRACKS) . 
							"</div>
							
						</div>
						<div class='shm-flex shm-align-items-center'>
							<input type='range' value='100' min='1' max='1000' name='shm-range' class='shm-range' style='display: inline-flex; '/>
							<div class='shm-range-label sh-right' >
								100
							</div>
							<div class='shm-button shm-track-edit' >".
								__("edit", SHMAPPER) . 
							"</div>
						</div>
					
					</div>
				</div>
				<div class='shm-track-error'></div>
				";

				// $html1 = "<div class='shmapper_tracks_upld _hidden'>
				// 	<div class='button'>".
				// 		__("Upload GPX-file", SHMAPPER_TRACKS) .
				// 	"</div>
				// </div>
				$html1 = "<div class='shm-track-upload-cont $shmW'>
					$uploader 
					$pult
				</div>
				";
			}
			else
			{
				$html1 = "";
			}
			$value_data = '';
			if ( isset( $data['shmtrack_edit'] ) ) {
				$value_data = $data['shmtrack_edit'];
			}
			$html1 = "<div class=''>" . $html1. $editBtn . "</div><input type='hidden' name='elem[]' value='" . $value_data . "' $require $data_types class='sh-form shmw-100'/>";
		}
		return $html1;
	}
	static function shmapper_form_after_fields( $html, $id, $data, $type )
	{
		if(  $data['type'] == SHMAPPER_TRACKS_TRACK  || $data['type'] == SHMAPPER_TRACKS_DRAW )
		{
			if( ShMapper::$options['map_api'] != 1 ) 
			{
				return esc_html__( 'Shmapper Track exists only in Yandex map API', SHMAPPER );
			}
			$html .= "
			<div class='shm-12 shm-draw-button-label'>
				<div class='shm--draw-button shm-t'>
					<small>" . 
						__( "Draw button label", SHMAPPER_TRACKS ).
					"</small>
					<input  
						class='sh-form'
						placeholder='".__( "start draw button label", SHMAPPER_TRACKS )."' 
						name='form_forms[$id][draw_button_label]'
						value='" .  $data['draw_button_label'] . "'
					>
				</div>
			</div>
			<div class='shm-12 shm-placeholder-label'>
				<div class='shm--placeholder shm-t'>
					<small>" . 
						__( "Input label", SHMAPPER_TRACKS ).
					"</small>
					<input  
						class='sh-form'
						placeholder='".__( "input label", SHMAPPER_TRACKS )."' 
						name='form_forms[$id][placeholder]'
						value='" .  $data['placeholder'] . "'
					>
				</div>
			</div>
			<div class='shm-12 '>
				<div class='spacer-10'></div>
				<input type='checkbox' class='checkbox11' id='gpx$id' name='form_forms[$id][gpx]' value='1' ".checked(1, $data['gpx'], false)."'/>							
				<label for='gpx$id'>". __( "Added GPX-input form", SHMAPPER_TRACKS ) ."</label>
			</div>
			<div class='shm-12'>
				<div class='spacer-10'></div>
				<input type='checkbox' class='checkbox11' id='switched_enabled_markers$id' name='form_forms[$id][switched_enabled_markers]' value='1' ".checked(1, $data['switched_enabled_markers'], false)."'/>							
				<label for='switched_enabled_markers$id'>". __( "Disabled markers", SHMAPPER_TRACKS ) ."</label>
			</div>";
		}
		return $html;
	}
	
	function track_to_xml()
	{	
		if($gpx = $this->get_meta( "gpx" ) )
		{
			return $gpx;
		}
		
		$shm_author			= $this->get_meta( "shm_author" );
		$shm_author_email	= $this->get_meta( "shm_author_email" );
		$track		= json_decode( $this->get_meta( "track" ) );
		$string = "<?xml version='1.0' encoding='UTF-8' ?>
<gpx xmlns='http://www.topografix.com/GPX/1/1' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd'>	
</gpx>";	
		$node 		=  new SimpleXMLElement( $string );
		$gpx	 	= $node;
		$gpx->addAttribute( "version", "1.0" );
		$gpx->addAttribute( "creator", get_bloginfo("url") . " by ShmapperTrack for Wordpress" ); 
		$time 		= $gpx->addChild( "time", gmdate( "Y-m-d H:i:s" ) );
		
		$metadata 	= $gpx->addChild( "metadata" );
		$name		= $metadata->addChild( "name", $this->get("post_title") ); 
		$desc		= $metadata->addChild( "desc", $this->get("post_content") );		
		$author		= $metadata->addChild( "author" );
		$aname		= $author->addChild( "name", $shm_author ?  $shm_author : get_bloginfo("name") );
		if($shm_author_email)
		{
			$aemail	= $author->addChild( "email", $shm_author_email );
		}
		$trk 		= $gpx->addChild( "trk" );
		$trk_name	= $trk->addChild( "name", $this->get("post_title")  );
		$trkseg		= $trk->addChild( "trkseg" );
		if($track && is_array($track))
		{
			foreach( $track as $trkpt )
			{
				$t	= $trkseg->addChild("trkpt");
				$t->addAttribute( "lat", $trkpt[0] );
				$t->addAttribute( "lon", $trkpt[1] );
			}
		}
		return $gpx->asXML();
		
	}
}
