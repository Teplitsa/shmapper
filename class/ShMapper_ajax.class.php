<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

// A class for writing those exceptions to the log file
// which do not require an immediate response from the administrator
class ExceptionWriter extends Error {
	public function Write() {
	}
}

class ShMapper_ajax
{
	static $instance;
	static function get_instance()
	{
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}
	function __construct()
	{
		add_action('wp_ajax_nopriv_myajax',		array(__CLASS__, 'ajax_submit') );
		add_action('wp_ajax_myajax',			array(__CLASS__, 'ajax_submit') );
		add_action('wp_ajax_myajax-admin', 		array(__CLASS__, 'ajax_submit'));
		
		add_action('wp_ajax_nopriv_shm_set_req',	array(__CLASS__, 'shm_ajax3_submit') );
		add_action('wp_ajax_shm_set_req',			array(__CLASS__, 'shm_ajax3_submit') );
		add_action('wp_ajax_shm_set_req-admin', 	array(__CLASS__, 'shm_ajax3_submit'));
		
	}
	
	static function insert_marker($data) {
		$res 	= ShMapperRequest::insert($data);
		
		if( !ShMapper::$options['shm_map_marker_premoderation'] ) {
			$point = ShmPoint::insert([
				"post_title"	=> (string)$res->get("post_title"),
				"post_name"		=> (string)$res->get("post_name"),
				"post_content"	=> (string)$res->get_meta("description"),
				"latitude"		=> $res->get_meta("latitude"),
				"longitude"		=> $res->get_meta("longitude"),
				"location"		=> $res->get_meta("location"),
				"type"			=> (int)$res->get_meta("type"),
				"map_id"		=> (int)$res->get_meta("map"),
			]);
			
			if($attach_id = get_post_thumbnail_id($res->id)) 
			{
				set_post_thumbnail($point->id, (int)$attach_id);
			}
			
			SMC_Post::delete($res->id);
		}
		
		return $res;
	}
	
	static function shm_ajax3_submit()
	{
		/**/
		$data = $_POST;
		$data['elem']	= explode(",", $data['elem']);
		foreach($data['elem'] as $i => $v) {
			$data['elem'][$i] = str_replace("{{shmapper_comma}}", ",", $v);
		}
		
		if( ShMapper::$options['shm_settings_captcha'] )
		{
			require_once( SHM_REAL_PATH . "assets/recaptcha-php/recaptcha.class.php" );
			$reCaptcha = new ReCaptcha( ShMapper::$options['shm_captcha_secretKey'] );					
			$response = $reCaptcha->verifyResponse(
				$_SERVER["REMOTE_ADDR"],
				$data['cap']
			);
			switch( $response->success )
			{
				case(true):
					$res    = static::insert_marker($data);
					$msg 	= ShMapper::$options['shm_succ_request_text'];
					break;
				default:
					$msg 	= ShMapper::$options['shm_error_request_text'] . " : " . $response->errorCodes->msg;
					break;
			}
			$grec = ShMapper_Assistants::shm_after_request_form("");
		}
		else
		{
			$res = static::insert_marker($data);
			$msg	= ShMapper::$options['shm_succ_request_text'];
		}
		
		//load image
		if( $res AND $res->id > 1 ) {
			
		}
		$form = ShmForm::form( get_post_meta( $data['id'], "form_forms", true ), ShmMap::get_instance($data['id'])  );
		$answer = [
			"reload"		=> ShMapper::$options['shm_reload'] ? 1 : 0,
			'res'			=> $res,
			'data'			=> $data,
			"msg"			=> $msg,
			//"form"		=> $form,
			"grec"			=> $grec,
			//"attach_id"	=> $attach_id,
			'grecaptcha'	=> ShMapper::$options['shm_settings_captcha']
		];
		wp_die( json_encode( $answer ) );
	}
	static function ajax_submit()
	{
		try
		{
			static::myajax_submit();
		}
		catch(Error $ex)
		{
			$d = [	
				"Error",
				array(
					'msg'	=> $ex->getMessage (),
					'log'	=> $ex->getTrace ()
				  )
			];
			$d_obj		= json_encode( $d );				
			print $d_obj;
			wp_die();
		}
		wp_die();
	}
	static function myajax_submit()
	{
		global $wpdb;
		$nonce = $_POST['nonce'];
		if ( !wp_verify_nonce( $nonce, 'myajax-nonce' ) ) die ( $_POST['params'][0] );
		
		$params	= $_POST['params'];
		$action = sanitize_text_field($params[0]);
		$d		= array( $action, array() );				
		switch($action)
		{				
			case "test":	
				$map_id = sanitize_text_field($params[1]);
				$num = sanitize_text_field($params[2]);
				$d = array(	
					$action,
					array( 
						"text"		=> 'testing',
					)
				);
				break;			
			case "shm_doubled":	
				$map_id = sanitize_text_field($params[1]);
				$map	= ShmMap::get_instance( $map_id );
				$new_map = $map->doubled();
				$d = array(	
					$action,
					array( 
						"text"		=> 'shm_doubled',
					)
				);
				break;		
			case "shm_wnext":	
				$step	= (int)get_option("shm_wizard_step");
				$step++;
				if($step < count(ShMapper::get_wizzard_lst()))
				{
					$stepData 	= ShMapper::get_wizzard_lst()[$step];
					$messge		= __("Next step", SHMAPPER);
				}
				else
				{
					ShMapper::$options["wizzard"] = 0;
					ShMapper::update_options();
					$step = 0;
					$messge		= __("Congratulation! That's all!", SHMAPPER);
				}
				update_option("shm_wizard_step", $step);
				$d = array(	
					$action,
					array( 
						"href"		=> $stepData['href'],
						"msg"		=> $messge
					)
				);
				break;			
			case "shm_wclose":	
				ShMapper::$options["wizzard"] = 0;
				ShMapper::update_options();
				update_option("shm_wizard_step", 0);
				$d = array(	
					$action,
					array( 
						"msg"	=> __("Wizzard closed", SHMAPPER) ,
					)
				);
				break; 			
			case "shm_wrestart":	
				ShMapper::$options["wizzard"] = 1;
				ShMapper::update_options();
				update_option("shm_wizard_step", 0);
				$d = array(	
					$action,
					array( 
						"msg"	=> __("Wizzard restarted", SHMAPPER),
					)
				);
				break; 	
			case "shm_notify_req":	
				$req_id = sanitize_text_field($params[1]);
				$req = ShMapperRequest::get_instance($req_id);
				$new_id = $req->notify();
				$d = array(	
					$action,
					array( 
						"text"		=> $req->get_notified_form(),
						"post_id"	=> $req_id,
						"newpointid"=> $new_id, 
						"msg"		=> __("Approve succesfully and insert new Map marker", SHMAPPER)
					)
				);
				break;		
			case "shm_trash_req":	
				$req_id = sanitize_text_field($params[1]);
				$req = ShMapperRequest::get_instance($req_id);
				wp_trash_post( $req_id );
				$d = array(	
					$action,
					array( 
						"post_id"	=> $req_id,
						"msg"		=> __("Request put to Trash", SHMAPPER)
					)
				);
				break;		
			case "shm_add_before":
				$num = sanitize_text_field($params[1]);
				$post_id = sanitize_text_field($params[2]);
				$type_id = sanitize_text_field($params[3]);				
				$d = array(	
					$action,
					array( 
						"text"		=> ShmForm::get_admin_element($num,["type" => $type_id]),
						"order"		=> $num,
						"type_id"	=> $type_id
					)
				);
				break;			
			case "shm_add_after":	
				$num = sanitize_text_field($params[1]);
				$post_id = sanitize_text_field($params[2]);
				$type_id = sanitize_text_field($params[3]);						
				$d = array(	
					$action,
					array( 
						"text"		=> ShmForm::get_admin_element($num,["type" => $type_id]),
						"order"		=> $num,
						"type_id"	=> $type_id
					)
				);
				break;		
			case "shm_csv":	
				$map_id = sanitize_text_field($params[1]);
				$map = ShmMap::get_instance($map_id);
				$link = $map->get_csv();
				$d = array(	
					$action,
					[ 
						"text"		=> $link,
						"name"		=> "map" //$map->get("post_title")
					]
				);
				break;		
			case "shm_set_req":	
				$data = $params[1];
				if( ShMapper::$options['shm_settings_captcha'] )
				{
					require_once( SHM_REAL_PATH . "assets/recaptcha-php/recaptcha.class.php" );
					$reCaptcha = new ReCaptcha( ShMapper::$options['shm_captcha_secretKey'] );					
					$response = $reCaptcha->verifyResponse(
						$_SERVER["REMOTE_ADDR"],
						sanitize_text_field($data['cap'])
					);
					switch( $response->success )
					{
						case(true):
							$res 	= ShMapperRequest::insert($data);
							$msg 	= ShMapper::$options['shm_succ_request_text'];
							break;
						default:
							$msg 	= ShMapper::$options['shm_error_request_text'] . " : " . $response->errorCodes->msg;
							break;
					}
					$grec = ShMapper_Assistants::shm_after_request_form("");
					/**/
					//$msg = "msg: ". $data['cap'];
				}
				else
				{
					$res 	= ShMapperRequest::insert($data);
					$msg	= ShMapper::$options['shm_succ_request_text'];
				}
				
				$d = array(	
					$action,
					array( 
						"msg"	=> $msg,
						"res"	=> $res,
						//"grec"	=> $grec,
						//'grecaptcha' => ShMapper::$options['shm_settings_captcha']
					)
				);
				break;	
			case "shm_delete_map_hndl":		
				$data 		= $params[1];
				$id 		= sanitize_text_field($data["id"]);
				$map 	= ShmMap::get_instance( $id );
				$res	= $map->shm_delete_map_hndl($data);
				$d = array(	
					$action,
					array( 
						"msg"		=> $res['message'],
						"res"		=> $res,
						"data"		=> $data,
						"id"		=> $id
					)
				);
				break;	
			case "shm_delete_map":	
				$id 	= sanitize_text_field($params[1]);
				$href 	= sanitize_text_field($params[2]);
				$map 	= ShmMap::get_instance( $id );
				$d = array(	
					$action,
					array( 
						"text"		=> [ 
							"title" 	=> sprintf(__("Are you want delete %s?", SHMAPPER), $map->get("post_title") ), 
							"content" 	=> $map->get_delete_form( $href ),
							"send" 		=> __("Delete"),
							"sendHandler" => "shm_delete_map_hand",
							"sendArgs" 	=> $id
						],
					)
				);
				break;
			case "shm_add_point_prepaire":	
				$map_id = $params[1][0] = sanitize_text_field($params[1][0]);
				$x		= $params[1][1] = sanitize_text_field($params[1][1]);
				$y		= $params[1][2] = sanitize_text_field($params[1][2]);
				$ad		= $params[1][3] = sanitize_text_field($params[1][3]);
				$d = array(	
					$action,
					array( 
						"text" => [
							'title' 	=> esc_html__( 'add Map Point', SHMAPPER ),
							"content" 	=> ShmPoint::get_insert_form( $params[1] ),
							"send" 		=> esc_html__( 'Create', SHMAPPER ),
							"sendHandler" => "create_point"
						],
					)
				);
				break;		
			case "shm_create_map_point":
				$data = $params[1];
				$point = ShmPoint::insert($data);
				$type_term_id = sanitize_text_field($data['type']);
				$type = get_term($type_term_id, SHM_POINT_TYPE);
				$pointdata = [
					"post_title"	=> sanitize_text_field($data["post_title"]),
					"post_content"	=> $data["post_content"],
					"latitude"		=> sanitize_text_field($data["latitude"]),
					"longitude"		=> sanitize_text_field($data["longitude"]),
					"location"		=> sanitize_text_field($data["location"]),
					"color"			=> get_term_meta($type->term_id, "color", true),
					"height"		=> get_term_meta($type->term_id, "height", true),
					"icon"			=> ShMapPointType::get_icon_src($type->term_id)[0],
					"term_id"		=> $type_term_id,
					"mapid"			=> "ShmMap".sanitize_text_field($data['map_id']).sanitize_text_field($data['map_id'])
				];
				$d = array(	
					$action,
					array( 
						"id"		=> $point->id,
						"data"		=> $pointdata,
						"msg"		=> esc_html__( 'Success', SHMAPPER ),
					)
				);
				break;
			case "shm_voc":	
				$voc = sanitize_text_field($params[1]);
				ShMapper::$options[$voc] = sanitize_text_field($params[2]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=> __("Change Vocabulaty: ", SHMAPPER) . $voc.": ".ShMapper::$options[$voc],
					)
				);
				break; 
			case "map_api":
				ShMapper::$options['map_api'] = sanitize_text_field( $params[1] );
				ShMapper::update_options();
				$d = array(
					$action,
					array( 
						"msg"    => sanitize_text_field( $params[1]) == 1 ? "Yandex Map API" : "OpenStreet Map API",
						'reload' => 'true',
					),
				);
				break;
			case "shm_yandex_maps_api_key":
				ShMapper::$options['shm_yandex_maps_api_key'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(
					$action,
					array(
						"msg"	=> __( "Yandex.Maps API key Saved" , SHMAPPER),
						"hide_dang" => sanitize_text_field($params[1]) != "" && ShMapper::$options['shm_yandex_maps_api_key'] != "" ? 1 : 0
					)
				);
				break;
			case "shm_default_coordinates":
				ShMapper::$options['shm_default_longitude'] = $params[1][0];
				ShMapper::$options['shm_default_latitude']  = $params[1][1];
				ShMapper::update_options();
				$d = array(
					$action,
					array(
						"msg"   => esc_html__( "New coordinates saved" , SHMAPPER ),
						"value" => array( $params[1][0], $params[1][1] ),
					),
				);
				break;
			case "shm_default_zoom":
				ShMapper::$options['shm_default_zoom'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(
					$action,
					array(
						"msg" => __( "New coordinates saved" , SHMAPPER ),
					),
				);
				break;
			case "shm_map_is_crowdsourced":	
				ShMapper::$options['shm_map_is_crowdsourced'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=> __(sanitize_text_field($params[1]) ? "Users can add Placemarks" : "Users don't can add Placemarks", SHMAPPER),
					)
				);
				break; 
			case "shm_map_marker_premoderation":	
				ShMapper::$options['shm_map_marker_premoderation'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=>  __(sanitize_text_field($params[1]) ? "Pre-moderation on" : "Pre-moderation off", SHMAPPER),
					)
				);
				break; 
			case "shm_reload":	
				ShMapper::$options['shm_reload'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=>  __(sanitize_text_field($params[1]) ? "Reload mode" : "Not relaod mode", SHMAPPER),
					)
				);
				break; 
			case "shm_settings_captcha":	
				ShMapper::$options['shm_settings_captcha'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=> __(sanitize_text_field($params[1]) ? "captha added" : "captcha removed", SHMAPPER),
					)
				);
				break; 
			case "shm_captcha_siteKey":	
				ShMapper::$options['shm_captcha_siteKey'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=> __( "Set key" , SHMAPPER),
						"hide_dang" => sanitize_text_field($params[1]) != "" && ShMapper::$options['shm_captcha_secretKey'] != "" ? 1 : 0
					)
				);
				break; 
			case "shm_captcha_secretKey":	
				ShMapper::$options['shm_captcha_secretKey'] = sanitize_text_field($params[1]);
				ShMapper::update_options();
				$d = array(	
					$action,
					array( 
						"msg"	=> __( "Set key" , SHMAPPER),
						"hide_dang" => sanitize_text_field($params[1]) != "" && ShMapper::$options['shm_captcha_siteKey'] != "" ? 1 : 0
					)
				);
				break; 
			default:
				do_action("shm_ajax_submit", $params);
				break;
		}
		$d_obj		= json_encode(apply_filters("shm_ajax_data", $d, $params));				
		print $d_obj;
		wp_die();
	}
}
