<?php 

class ShMapperDriverPreview
{
	static $google_table_id; 
	static function get_source()
	{
		require_once(SHM_REAL_PATH . "assets/google-sheets/google-sheets.php"); 
		static::$google_table_id  		= ShMapperDrive::$options["google_table_id"];
		$matrix 						= get_sheet(static::$google_table_id);
		return $matrix;
	}
	static function get_preview()
	{
		$matrix = static::get_source();
		$html = "";
		$i = 0;
		foreach($matrix as $m)
		{
			$cont = "";
			if($i > 0)
			{
				if( ShMapperDrive::$options['is_google_point_type'] )
				{
					$nType 	= getSingleGoogleOrder(ShMapperDrive::$options['google_point_type']);
					$type 	= get_term($m[$nType], SHM_POINT_TYPE);
				}
				if(!$type || is_wp_error($type))
				{
					$nType 	= ShMapperDrive::$options[ 'point_type' ];
					$type 	= get_term( $nType, SHM_POINT_TYPE );
				}
				$icon = ShMapPointType::get_icon($type, false, false);
				$geocode = implode(" - " , static::getGeoPosition( $m ));
				
				if(ShMapperDrive::$options['is_google_post_date'])
					$post_date	= strtotime( $m[ getSingleGoogleOrder(ShMapperDrive::$options['post_date']) ]);
				else
					$post_date	= time();
				$ii = 0;
				foreach(ShMapperDrive::$options['google_matrix_data'] as $n)
				{
					if( $n->include ) 
					{
						if($m[$ii])
						{
							$cont .= 
								"
								<div class='small shm-color-lightgrey '>" .
									$matrix[0][$ii].
								"</div>
								<div class=' '>" .
									$m[$ii] .
								"</div>";
						}
					}
					$ii++;
				}
				$cont = $m[ getSingleGoogleOrder(ShMapperDrive::$options['shmd_post_desc']) ];
				$html .= "<div class='shmapper-drive-post-content' pid='" . $m[0] . "'>
					<div class='title'>" . 
						$icon . " " . $m[ getSingleGoogleOrder(ShMapperDrive::$options['shmd_post_title']) ] .
					"</div>
					<div class='content'>
						$cont
					</div> 
					<div class=' small shm-color-cyan shmapper-drive-modal-geocode'>
						$geocode
					</div>
					<div class=' small shm-color-lightgrey'>".
						static::$google_table_id .
					"</div>
				</div>";
			}
			$i++;
		}
		return $html;
	}
	static function update()
	{
		static::delete();
		$matrix = static::get_source();
		$map_id	= (int)ShMapperDrive::$options['map_id'];
		$i = 0;
		foreach($matrix as $m)
		{
			$cont = "";
			if($i > 0)
			{
				$geocode = static::getGeoPosition( $m );
				$copies = get_posts([
					"post_type" 	=> ShmPoint::get_type(),
					"numberposts"	=> -1,
					"post_status"	=> "publish",
					"fields"		=> "ids",
					'meta_query' 	=> [
						'relation' 	=> 'AND',
						[
							"key"	=> "latitude",
							"value"	=> $geocode[1]
						],
						[
							"key"	=> "longitude",
							"value"	=> $geocode[0]
						]
					]
				]);
				if( count( $copies ) > 0 )
				{ 
					if( ShMapperDrive::$options["shm_doubled"] == 0 ) continue;
					// point type 
					if( ShMapperDrive::$options['is_google_point_type'] )
					{
						$nType 	= getSingleGoogleOrder(ShMapperDrive::$options['google_point_type']);
						$type 	= get_term($m[$nType], SHM_POINT_TYPE);
					}
					if(!$type || is_wp_error($type))
					{
						$nType 	= ShMapperDrive::$options[ 'point_type' ];
						$type 	= get_term( $nType, SHM_POINT_TYPE );
					}					
					$icon = ShMapPointType::get_icon($type, false, false);
					
					// post_date - 4					
					if(ShMapperDrive::$options['is_google_post_date'])
						$post_date	= strtotime( $m[ getSingleGoogleOrder(ShMapperDrive::$options['post_date']) ]);
					else
						$post_date	= time();
					
					$ii = 0;
					foreach(ShMapperDrive::$options['google_matrix_data'] as $n)
					{
						if( $n->include ) 
						{
							if($m[$ii])
							{
								$cont .= "
									<div class='small shm-color-lightgrey '>" .
										$matrix[0][$ii].
									"</div>
									<div class=' '>" .
										$m[$ii] .
									"</div>";
							}
						}
						$ii++;
					}
					$post_title		= $m[ getSingleGoogleOrder(ShMapperDrive::$options['shmd_post_title']) ];
					$post_content	= $m[ getSingleGoogleOrder(ShMapperDrive::$options['shmd_post_desc']) ];
					//$post_content	= $cont ;
					$point 			= ShMapperPointMessage::insert([
						'post_name'    	=> $post_title,
						'post_title'    => $post_title,
						'post_content'  => $post_content,
						'latitude'  	=> $geocode[1],
						'longitude'  	=> $geocode[0], 
						SHM_POINT		=> $copies[0],
						"google_table_id"=> static::$google_table_id
					]);
					wp_set_object_terms( $point->id, [ $type->term_id ], SHM_POINT_TYPE );
					wp_update_post([
						"ID"		=> $point->id,
						"post_date"	=> date_i18n("Y-m-d H:i:s", $post_date)
					]);
				}
				else
				{
					if( ShMapperDrive::$options['is_google_point_type'] )
					{
						$nType 	= getSingleGoogleOrder( ShMapperDrive::$options['google_point_type'] );
						$type 	= get_term($m[$nType], SHM_POINT_TYPE);
					}
					if(!$type || is_wp_error($type))
					{
						$nType 	= ShMapperDrive::$options[ 'point_type' ];
						$type 	= get_term( $nType, SHM_POINT_TYPE );
					}					
					$icon = ShMapPointType::get_icon($type, false, false);
					
					// post_date - 4					
					if(ShMapperDrive::$options['is_google_post_date'])
						$post_date	= strtotime( $m[ getSingleGoogleOrder(ShMapperDrive::$options['post_date']) ]);
					else
						$post_date	= time();
					
					$ii = 0;
					foreach(ShMapperDrive::$options['google_matrix_data'] as $n)
					{
						if( $n->include ) 
						{
							if($m[$ii])
							{
								$cont .= "
									<div class='small shm-color-lightgrey '>" .
										$matrix[0][$ii].
									"</div>
									<div class=' '>" .
										$m[$ii] .
									"</div>";
							}
						}
						$ii++;
					}
					$post_title		= $m[ getSingleGoogleOrder(ShMapperDrive::$options['shmd_post_title']) ];
					$post_content	= $m[ getSingleGoogleOrder(ShMapperDrive::$options['shmd_post_desc']) ];
					//$post_content	= $cont;
					$point 			= ShmPoint::insert([
						'post_name'    	=> $post_title,
						'post_title'    => $post_title,
						'post_content'  => $post_content,
						'latitude'  	=> $geocode[1],
						'longitude'  	=> $geocode[0], 
						"google_table_id"=> static::$google_table_id
					]);
					wp_set_object_terms( $point->id, [ $type->term_id ], SHM_POINT_TYPE );
					$point->add_to_map($map_id);
					wp_update_post([
						"ID"		=> $point->id,
						"post_date"	=> date_i18n("Y-m-d H:i:s", $post_date)
					]);
				}
			}
			$i++;
		}
				
		//var_dump($copies );
		//wp_die();
				
	}
	static function delete()
	{
		static::$google_table_id  		= ShMapperDrive::$options["google_table_id"];
		$posts = get_posts([
			"numberposts"	=> -1,
			"post_type"		=> [ ShmPoint::get_type(), ShMapperPointMessage::get_type() ],
			"post_status"	=> "publish",
			"fields"		=> "ids",
			'meta_query' 	=> [
				'relation' 	=> 'OR',
				[
					"key"	=> "google_table_id",
					"value"	=> static::$google_table_id,
					"compare" => "LIKE"
				]
			]
		]);
		//var_dump( $posts );
		//wp_die();
		if( count($posts) > 0 )
		{
			foreach($posts as $postid)
			{
				wp_delete_post( $postid, true );
			}
		}
	}
	
	static function getGeoPosition( $m )
	{
		switch( ShMapperDrive::$options['google_geo_position'])
		{
			case 0:
				return [
					$m[ getSingleGoogleOrder( static::$options['google_geo_lon'] ) ],
					$m[ getSingleGoogleOrder( static::$options['google_geo_lat'] ) ]
				];
			case 1:
				$adress_identer		= ShMapperDrive::$options['google_geo_adress'];
				$adress				= $m[ getSingleGoogleOrder( $adress_identer )]; 
				return static::geocode( $adress );
			default:
				return [];
		}
	}
	static function geocode($address)
	{
		//$address = "Москва, Тверская, 12";
		$key = "921be5a4-36cd-4485-8f57-2c24f94dab32";
		$yandex_key = "8202b217-b7c5-4c31-8abb-6030094cc780";
		$address = urlencode($address);
		$location = json_decode(file_get_contents("https://geocode-maps.yandex.ru/1.x/?apikey={$key}&geocode={$address}&format=json"), true);
		$address = $location["response"]["GeoObjectCollection"]["featureMember"][0]["GeoObject"]["metaDataProperty"]["GeocoderMetaData"]["text"];
		$location = $location["response"]["GeoObjectCollection"]["featureMember"][0]["GeoObject"]["Point"]["pos"];
		$location = explode(" ", $location);
		return $location;
	}
}