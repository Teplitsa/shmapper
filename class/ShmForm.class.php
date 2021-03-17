<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShmForm
{
	static function init()
	{
		
	}
	static function get_default()
	{
		return [
			[ 
				"type" 			=> SHMAPPER_MARK_TYPE_ID,
				"require"		=> 1, 
				"title"			=> __("Place the mark to Map", SHMAPPER),
				"placemarks"	=> [],
				"placeholde"	=> "",
				"description"	=> "",
			],	
			[ 
				"type" 			=> SHMAPPER_TITLE_TYPE_ID,
				"require"		=> 1, 
				"title"			=> __("Put a title", SHMAPPER),
				"placeholde"	=> "",
				"description"	=> "",
			],	
			/*[ 
				"type" 			=> SHMAPPER_NAME_TYPE_ID,
				"require"		=> 1, 
				"title"			=> __("How call You?", SHMAPPER),
				"placeholde"	=> "",
				"description"	=> "",
			],		
			[ 
				"type" 			=> SHMAPPER_EMAIL_TYPE_ID,
				"require"		=> 1, 
				"title"			=> __("Send Your e-mail please", SHMAPPER),
				"placeholde"	=> "",
				"description"	=> "",
			],*/		
			[ 
				"type" 			=> SHMAPPER_TEXTAREA_TYPE_ID,
				"require"		=> 1, 
				"title"			=> __("Write description", SHMAPPER),
				"placeholde"	=> "",
				"description"	=> "",
			],	
			
		];
	}
	static function getTypes()
	{
		return apply_filters("shmapper_get_form_fild_types", [
			[
				"id"		=> SHMAPPER_TITLE_TYPE_ID, //9
				"name" 		=> "title", 
				"title" 	=> __("input title", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description']
			],/**/
			[
				"id"		=> SHMAPPER_PLAIN_TEXT_TYPE_ID, //1
				"name" 		=> "text", 
				"title" 	=> __("input text", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description']
			],
			/*[
				"id"		=> SHMAPPER_NAME_TYPE_ID, //2
				"name" 		=> "name", 
				"title" 	=> __("User name", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description']
			],
			[
				"id"		=> SHMAPPER_PLAIN_NUMBER_TYPE_ID, //3
				"name" 		=> "number", 
				"title" 	=> __("input number", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description']
			],
			[	
				"id"		=> SHMAPPER_EMAIL_TYPE_ID, //4
				"name" 		=> "email", 
				"title" 	=> __("input email", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description' ]
			],
			[
				"id"		=> SHMAPPER_PHONE_TYPE_ID, //5
				"name" 		=> "phone", 
				"title" 	=> __("input phone", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description' ]
			],*/
			[
				"id"		=> SHMAPPER_TEXTAREA_TYPE_ID, //6
				"name" 		=> "textarea", 
				"title" 	=> __("textarea", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description' ]
			],
			[
				"id"		=> SHMAPPER_IMAGE_TYPE_ID, //7
				"name" 		=> "file", 
				"title" 	=> __("input file", SHMAPPER), 
				'fields' 	=> ['title', 'placeholder', 'description']
			],		
			[
				"id"		=> SHMAPPER_MARK_TYPE_ID, //8
				"name" 		=> "placemark", 
				"title" 	=> __("enabled Map markers", SHMAPPER), 
				'fields' 	=> ['title', 'placemarks', 'description']
			]		
		]);
	}
	static function get_type_by ($field="id", $id = 1)
	{
		foreach(static::getTypes() as $type)
		{
			if($type[$field] == $id)
				return $type;
		}
		return false;
	}
	static function wp_dropdown($params=-1)
	{
		if(!is_array($params))
			$params = ['id' => 'shmform', 'name' => 'shmform', "class" => "sh-form"];
		$selector = $params['selector']  ? " selector='" . $params['selector'] . "' " : " s='ee' ";
		$html = "<select id='" .$params['id']. "' name='" .$params['name']. "' class='" .$params['class']. "' $selector>";
		$html .= "<option value='0' data-fields='' > -- </option>";
		foreach(static::getTypes() as $type)
		{
			$selected = $params['selected']== $type['id'] ? "selected" : "";
			$html .= "
			<option value='".$type['id']."' $selected  data-fields='" . implode( ",", $type['fields'] ) . "' >".
				$type['title'].
			"</option>";
		}
		$html .= "</select>";
		return $html;
	}
	
	static function wp_params_dropdown( $meta, $id, $selected = -1 )
	{
		$html 		= "<select name='form_forms[$id][input-type]' class='sh-form'>";
		foreach($meta as $m)
		{
			if(!is_array($m))	$m = ['id'=> $m, "title" => $m];
			$s 		= $selected == $m['id'] ? " selected " : "";
			$html 	.= "<option value='".$m['id']."' $s>".$m["title"]."</option>";
		}
		$html 		.= "</select>";
		return $html;
	}
	
	
	static function wp_params_radio( $params=-1, $id, $post_id=1 )
	{
		$params		= is_array($params) ? $params : [];
		$html 		= "
		<div class='shm-types-radio shm-win' row_id='$id' post_id='$post_id'>
			<div class='shm-row'>
				<div class='shm-12'>
					<div class='shm-float-right shm-close-btn'>X</div>
				</div>";
		foreach(static::getTypes() as $m)
		{
			$html 	.= "
				<div class='shm-12'>
					<input class='radio' type='radio' name='form_forms_form' id='type" . $m['id'] . "' value='" . $m['id'] . "'/> 
					<label for='type" . $m['id'] . "'>" . $m['title'] . "</label>
					<div class='spacer-10'></div>
				</div>";
		}
		$html 		.= "
			</div>
		</div>";
		return $html;
	}
	
	static function get_admin_element( $id, $data=-1 )
	{
		$data 	= !is_array($data) ? [ "require"=>1, "selected" => 0 ] : $data;		
		$type 	= static::get_type_by("id", $data['type']);
		$fields = $type['fields'];
		$mark_emable = false;
		return apply_filters(
			"shm_admin_element", 
			"<li shm-num='$id' type_id='" . $type['id'] . "'>
				<input type='hidden' name='form_forms[$id][type]' value='" . $type['id'] . "' /> 
				<div class='shm-row'>
					<div class='shm-4'>
						<small class=''>" . __("Type of element", SHMAPPER) . "</small>
						<div class='spacer-101'></div>" .					
						static::wp_params_dropdown( static::getTypes(), $id, $type['id'] ) . 
					"</div>
					<div class='shm-8'>
						<div class='shm-row'>
							
							<div class='shm-12 shm-title-label'>
								<div class='shm--title shm-t' ".(!in_array("title", $fields) ? " style='display:none;' " : "" )." >
									<small class=''>".
										__("Label of element", SHMAPPER) .
									"</small>
									<input class='sh-form' placeholder='" .__("write title", SHMAPPER). "' name='form_forms[$id][title]'  value='".$data['title']."'/>
								</div>
							</div>
							
							<div class='shm-12 shm-placeholder-label'>
								<div class='shm--placeholder shm-t' ".(!in_array("placeholder", $fields)?"style='display:none;'":"")." >
									<small class=''>".
										__("Placeholder", SHMAPPER) .
									"</small>
									<input class='sh-form' placeholder='" .__("write placeholder", SHMAPPER). "' name='form_forms[$id][placeholder]'  value='".(empty($data['placeholder']) ? '' : $data['placeholder'])."' />
								</div>
								<div class='shm--placemarks shm-t' ".(!in_array("placemarks", $fields) ? "style='display:none;'":"")." >
									<small class=''>".
										__("Placemark types", SHMAPPER) .
									"</small>".
									ShMapPointType::get_ganre_swicher([
										"prefix" 	=> "ganre$id". MD5(rand(0,100000000)), 
										"id" 		=> $id, 
										"name" 		=> "form_forms[$id][placemarks]", 
										"selected"	=> empty($data['placemarks']) ? '' : $data['placemarks'],
										"col_width"	=> 6
									]).
								"</div>
								
							</div>
							<div class='shm-12 shm-description-label'>
								<div class='shm--description shm-t' ".(!in_array("description", $fields)?"style='display:none;'":"")." >
									<small class=''>".
										__("Description", SHMAPPER) .
									"</small>
									<input class='sh-form' placeholder='" .__("write description", SHMAPPER). "' name='form_forms[$id][description]'  value='".$data['description']."' />
								</div>
							</div>" .
							apply_filters("shmapper_form_after_fields", "", $id, $data, $type) .
							"<div class='shm-12  shm-require-label'>
								<div class='spacer-10'></div>
								<input type='checkbox' class='checkbox11' id='require$id' name='form_forms[$id][require]' value='1' ".checked(1, $data['require'], false)."'/>							
								<label for='require$id'>". __("Element is required", SHMAPPER) ."</label>
								<div class='shm-float-right'>
									<!--a class='shm-change-input' c='shm_add_before'>" . __("Add before", SHMAPPER) . "</a--> 
									<a class='shm-change-input' c='shm_add_after'>" . __("Add after", SHMAPPER) . "</a> 
									<a class='shm-change-input' c='shm_delete_me'>" . __("Delete me", SHMAPPER) . "</a> 
									
								</div>
							</div>
						</div>
					</div>
					
				</div>				
			</li>",
			$id, 
			$data
		);
	}
	static function get_admin_element1( $id, $data=-1 )
	{
		$data 	= !is_array($data) ? [ "require"=>1, "selected" => 0 ] : $data;		
		$type 	= static::get_type_by("id", $data['type']);
		$fields = $type['fields'];
		return "
		<li shm-num='$id' type_id='" . $type['id'] . "'>
			<div class='shm-row'>
				<div class='shm-12'>
					<h3>" . __("Type of element", SHMAPPER) . "</h3>
					<h3>" . $type['title'] . "</h3>
				</div>
				<input type='hidden' name='form_forms[$id][type]' value='" . $type['id'] . "' /> 
				<div class='shm-12'>
					<div class='shm-row'>
						<div class='shm-2'>							
							<label for='require$id'>". __("require", SHMAPPER) ."</label>
							<input type='checkbox' class='checkbox11' id='require$id' name='form_forms[$id][require]' value='1' ".checked(1, $data['require'], false)."'/>
						</div>
						<div class='shm-10'>
							<div class='shm--title shm-t' ".(!in_array("title", $fields) ? " style='display:none;' " : "" )." >
								<small class=''>".
									__("Label of element", SHMAPPER) .
								"</small>
								<input class='sh-form' placeholder='" .__("write title", SHMAPPER). "' name='form_forms[$id][title]'  value='".$data['title']."'/>
							</div>
						</div>
					</div>
				</div>
				<div class='shm-12'>
					<div class='shm--placeholder shm-t' ".(!in_array("placeholder", $fields)?"style='display:none;'":"")." >
						<small class=''>".
							__("Placeholder", SHMAPPER) .
						"</small>
						<input class='sh-form' placeholder='" .__("write placeholder", SHMAPPER). "' name='form_forms[$id][placeholder]'  value='".$data['placeholder']."' />
					</div>
					<div class='shm--placemarks shm-t' ".(!in_array("placemarks", $fields) ? "style='display:none;'":"")." >
						<small class=''>".
							__("Placemark types", SHMAPPER) .
						"</small>".
						ShMapPointType::get_ganre_swicher([
							"prefix" 	=> "ganre$id". MD5(rand(0,100000000)), 
							"id" 		=> $id, 
							"name" 		=> "form_forms[$id][placemarks]", 
							"selected"	=> $data['placemarks'],
							"col_width"	=> 6
						]).
					"</div>
					<div class='shm--description shm-t' ".(!in_array("description", $fields)?"style='display:none;'":"")." >
						<small class=''>".
							__("Description", SHMAPPER) .
						"</small>
						<input class='sh-form' placeholder='" .__("write description", SHMAPPER). "' name='form_forms[$id][description]'  value='".$data['description']."' />
					</div>
				</div>
				<div class='shm-12'>
					<div class='shm-float-right'>
						<!--a class='shm-change-input' c='shm_add_before'>" . __("Add before", SHMAPPER) . "</a--> 
						<a class='shm-change-input' c='shm_add_after'>" . __("Add after", SHMAPPER) . "</a> 
						<a class='shm-change-input' c='shm_delete_me'>" . __("Delete me", SHMAPPER) . "</a> 
						
					</div>
					<!--div class='shm-title-3 shm-color-cyan'>" . $type['title'] . "</div-->
				</div>
			</div>				
		</li>";
	}
	static function get_admin_element2( $id, $data=-1 )
	{
		$data 	= !is_array($data) ?["enable"=>1, "require"=>1, "selected" => 0] : $data;		
		$type 	= static::get_type_by("id", $data['type']);
		$fields = $type['fields'];
		return "
		<li>
			<div class='shm-row' shm-num='$id' >
				<div class='shm-2 sh-align-middle'>
					<input type='checkbox' class='checkbox' id='enable$id' name='form_forms[$id][enable]' value='1' ".checked(1, $data['enable'], false)."'/>
					<label for='enable$id'>". __("enable", SHMAPPER) ."</label>
				<p></p>
					<input type='checkbox' class='checkbox' id='require$id' name='form_forms[$id][require]' value='1' ".checked(1, $data['require'], false)."'/>
					<label for='require$id'>". __("require", SHMAPPER) ."</label>
				</div>
				<div class='shm-5'>
					<div class='shm--title shm-t' ".(!in_array("title", $fields) ? " style='display:none;' " : "" )." >
						<small class=''>".
							__("Label of element", SHMAPPER) .
						"</small>
						<input class='sh-form' placeholder='" .__("write title", SHMAPPER). "' name='form_forms[$id][title]'  value='".$data['title']."'/>
					</div>
					<div class='shm--type'>
						<small class=''>".
							__("Type of element", SHMAPPER) .
						"</small>".
						ShmForm::wp_dropdown([
							"name" 		=> "form_forms[$id][type]",
							"id" 		=> "type$id",
							"class" 	=> "sh-form",
							"selected"	=> $data['type'],
							"selector"	=> "type"
						]) .
						"
					</div>
				</div>
				<div class='shm-5'>
					<div class='shm--placeholder shm-t' ".(!in_array("placeholder", $fields)?"style='display:none;'":"")." >
						<small class=''>".
							__("Placeholder", SHMAPPER) .
						"</small>
						<input class='sh-form' placeholder='" .__("write placeholder", SHMAPPER). "' name='form_forms[$id][placeholder]'  value='".$data['placeholder']."' />
					</div>
					<div class='shm--placemarks shm-t' ".(!in_array("placemarks", $fields) ? "style='display:none;'":"")." >
						<small class=''>".
							__("Placemark types", SHMAPPER) .
						"</small>".
						ShMapPointType::get_ganre_swicher([
							"prefix" 	=> "ganre$id", 
							"id" 		=> $id, 
							"name" 		=> "form_forms[$id][placemarks]", 
							"selected"	=> $data['placemarks']
						]).
					"</div>
					<div class='shm--description shm-t' ".(!in_array("description", $fields)?"style='display:none;'":"")." >
						<small class=''>".
							__("Description", SHMAPPER) .
						"</small>
						<input class='sh-form' placeholder='" .__("write description", SHMAPPER). "' name='form_forms[$id][description]'  value='".$data['description']."' />
					</div>
					<!--div class=' ' >
						<small class=''>".
							__("The name of the parameter that refers to this element", SHMAPPER) .
						"</small>" .
						static::wp_params_dropdown($type['meta'], $id, $data['input-type']) .
					"</div-->
				</div>
			</div>				
		</li>";
	}
	static function form($data, $map )
	{		
		$default_icon_id 	= $map->get_meta("default_icon_id");
		$is_personal_data 	= $map->get_meta("is_personal_data");
		$is_name_iclude 	= $map->get_meta("is_name_iclude");
		$personal_name 		= $map->get_meta("personal_name");
		$is_name_required 	= $map->get_meta("is_name_required");
		$is_email_iclude 	= $map->get_meta("is_email_iclude");
		$personal_email 	= $map->get_meta("personal_email");
		$is_email_required 	= $map->get_meta("is_email_required");
		$is_phone_iclude 	= $map->get_meta("is_phone_iclude");
		$personal_phone 	= $map->get_meta("personal_phone");
		$is_phone_required 	= $map->get_meta("is_phone_required");		
		$def_mark 			= "";
		
		
		$html	= apply_filters("shm_before_request_form", "");
		$html 	.= "";
		$html1	= apply_filters("shm_start_request_form", "");
		$i = 0;
		foreach($data as $element)
		{
			if(!is_array($element))	continue;
			$require	= isset($element['require']) && $element['require'] == 1 ? " required " : ""; 
			$html1 		.= "<div class='shm-form-element form-field-".$element['type']."'>";
			$html1 		.= $element['title'] ? "<div class='shm-form-title'>" . $element['title'] . "</div>" : "";
			$type 		= static::get_type_by("id", $element['type']);
			$data_types = " data-types='".implode( ",", $type['fields'] )."' ";
			switch($element['type'])
			{
				case SHMAPPER_PLAIN_TEXT_TYPE_ID:					
					$html1 .= "<input type='text' class='sh-form' placeholder='".$element['placeholder']."'  name='elem[]' $require $data_types />";
					break;
				case SHMAPPER_NAME_TYPE_ID:						
					$html1 .= "<input type='text' class='sh-form' placeholder='".$element['placeholder']."'  name='elem[]' $require $data_types />";	
					break;
				case SHMAPPER_PLAIN_NUMBER_TYPE_ID:				
					$html1 .= "<input type='number' class='sh-form' placeholder='".$element['placeholder']."'  name='elem[]' $require $data_types />";	
					break;
				case SHMAPPER_EMAIL_TYPE_ID:		
					$html1 .= "<input type='email' class='sh-form' placeholder='".$element['placeholder']."'  name='elem[]' $require $data_types />";
					break;
				case SHMAPPER_PHONE_TYPE_ID:				
					$html1 .= "<input type='phone' class='sh-form' placeholder='".$element['placeholder']."'  name='elem[]'    $require $data_types />";	
					break;
				case SHMAPPER_TEXTAREA_TYPE_ID:			
					$html1 .= "<textarea class='sh-form' placeholder='".$element['placeholder']."' name='elem[]' $require  rows='5' $data_types></textarea>";					
					break;
				case SHMAPPER_IMAGE_TYPE_ID:	
					$file_map =  "<span class='dashicons dashicons-upload'></span> " .
							($element['placeholder'] ? $element['placeholder'] : __("Сhoose files", SHMAPPER))	;			
					$html1 .= "
					<div class='shm-form-file'>
						<label class='shm_nowrap'>$file_map</label>
						<input type='file' class='sh-form' name='elem[]' $require  $data_types/>
					</div>";
					break;
				case SHMAPPER_MARK_TYPE_ID:	
					$mark_emable = true;
					$html1 .= static::getTypeSwitcher( $element, $map, $require );
					break;
				default:			
					$html1 .= apply_filters(
						"shmapper_front_form_element", 
						"<input class='sh-form' placeholder='".$element['placeholder']."'  name='elem[]' $require />", 
						$element 
					);
					break;
			}
			$req	= $require ? "<small class='req_descr'>".__("This required field", SHMAPPER)."</small>" : "$require";
			$html1 .= $element['description'] ? "<div class='shm-description'>" . $req . $element['description'] ."</div>" : "<div class='shm-description'>$req</div>";
			
			$html1 .= "</div>";
			$i++;
		}
		if(!$mark_emable)
		{
			$diid = $map->get_meta("default_icon_id");
			$icon	= wp_get_attachment_image_src($diid, [60, 60])[0];
			
			if(!$icon)
				$icon = ShMapper::$options['map_api'] == 2 ? "https://unpkg.com/leaflet@1.3.4/dist/images/marker-icon.png"
				: SHM_URLPATH . 'assets/img/ym_default.png';
			$desc	= "
			<div class='shm-float-right'>	
				<div class='sh-right shm-form-title'>" . 
					__("Drag icon and place it to map.", SHMAPPER) . 
				"</div>
				<div class='shm-description'>
					<small class='req_descr'>".__("This required field", SHMAPPER)."</small>
				</div>
			</div>";
			$def_mark = true ? "
			<div class='shm-form-element  form-field-8'>$desc
				<div class='shm-form-placemarks'>
					<div class='shm-type-icon' style='background-image:url($icon);background-color:#EEE;' shm_map_id='' ></div>
				</div>" 
					: 
			"<div class='shm-form-element  form-field-8'>$desc
				<div class='shm-form-placemarks'>
					<div class='shm-type-icon' shm_type_id='default' shm_map_id=''>
						<div class='shm-color-crcl' style='background:$clr'></div>
					</div>
				</div>";
			
			$def_mark .= "<input type=hidden name='shm_point_type' class='sh-form shm-bg-transparent small' />
				<input type=hidden name='shm_point_lat' class='sh-form shm-bg-transparent small' />
				<input type=hidden name='shm_point_lon' class='sh-form shm-bg-transparent small' />
				<input type=text name='shm_point_loc' class='sh-form shm-bg-transparent small ".(count($terms) > 1 ? "hidden" : "")."' />						
			</div>";
		}
		if( $is_personal_data )
		{
			$require	= $is_name_required ? " required " : ""; 
			$html1 		.= $is_name_iclude ? "
			<div class='shm-form-element'>
				<div class='shm-form-title'>" . __("Your name",SHMAPPER) . "</div>
				<input type='text' class='sh-form' placeholder='".$personal_name."'  name='shm_form_name' $require/>
			</div>" 		: "";	
			$html1 .= $is_name_required ? "<div class='shm-description'>
				<small class='req_descr'>".__("This required field", SHMAPPER)."</small>
			</div>" : "";	
			
			
			$require	= $is_email_required ? " required " : ""; 
			$html1 		.= $is_email_iclude ? "
			<div class='shm-form-element'>
				<div class='shm-form-title'>" . __("Your e-mail",SHMAPPER) . "</div>
				<input type='text' class='sh-form' placeholder='".$personal_email."'  name='shm_form_email' $require/>
			</div>" 	: "";			
			$html1 .= $is_email_required ? "<div class='shm-description'>
				<small class='req_descr'>".__("This required field", SHMAPPER)."</small>
			</div>" : "";
			
			$require	= $is_phone_required ? " required " : ""; 
			$html1 		.= $is_phone_iclude ? "
			<div class='shm-form-element'>
				<div class='shm-form-title'>" . __("Your phone",SHMAPPER) . "</div>
				<input type='text' class='sh-form' placeholder='".$personal_phone."'  name='shm_form_phone' $require />
			</div>" 	: "";			
			$html1 .= $is_phone_required ? "<div class='shm-description'>
				<small class='req_descr'>".__("This required field", SHMAPPER)."</small>
			</div>" : "";	
			
			$att		= "
			<div class='shm-form-element'>
				<div class='shm-description'>
					<input type='checkbox' name='shm_personal_check' required /> ".
					ShMapper::$options['shm_personal_text'] .
				"</div>
			</div>";
		}
		$html1 			.= apply_filters("shm_end_request_form", "");
		$html = $def_mark . $html . $html1 . (empty($att) ? '' : $att) . apply_filters("shm_after_request_form", "");
		return $html ;
	}
	static function getTypeSwitcher(
		$element, 
		$map, 
		$require,
		$params=[
			"icon_class" => "shm-type-icon", 
			"container_class" => 'shm-form-placemarks'
		] 
	)
	{
		$mark_emable = true;			
		$terms = explode(",", $element["placemarks"]);
		$icons = "";
		if(count($terms))
		{
			foreach($terms as $term_id)
			{
				$clr  = get_term_meta($term_id, "color", true);
				$icon = '';
				if ( ShMapPointType::get_icon_src($term_id) ) {
					$icon = ShMapPointType::get_icon_src($term_id)[0];
				}

				if($icon)
				{
					$icon_width = get_term_meta( $term_id, "width", true );
					$icon_height = get_term_meta( $term_id, "height", true );
					$icons .= "
					<div class='".$params["icon_class"]."' style='background-image:url($icon);' shm_type_id='$term_id' shm_map_id='' shm_clr='$clr' data-icon-width='".$icon_width."' data-icon-height='".$icon_height."'>
					</div>";
				}
				else
				{
					$diid = $map->get_meta("default_icon_id");
					$icon	= wp_get_attachment_image_url($diid, [60, 60]);			
					if(!$icon) {
						if(ShMapper::$options['map_api'] == 2) {
							$icon = "https://unpkg.com/leaflet@1.3.4/dist/images/marker-icon.png"; // 25 x 41
							$icon_width = 25;
							$icon_height = 41;
						}
						else {
							$icon = SHM_URLPATH . 'assets/img/ym_default.png';	// 34 x 41
							$icon_width = 34;
							$icon_height = 41;
						}
					}
					else {
						$icon_width = "";
						$icon_height = "";
					}
					
					$icons .=  !$icon 
						? 
						"<div class='".$params["icon_class"]."' shm_type_id='$term_id' shm_map_id='' shm_clr='$clr' data-icon-width='".$icon_width."' data-icon-height='".$icon_height."'>
							<div class='shm-color-crcl' style='background:$clr'></div>
						</div>" 
						:
						"<div class='".$params["icon_class"]."' style='background-image:url($icon);' shm_map_id='' data-icon-width='".$icon_width."' data-icon-height='".$icon_height."'></div>";
				} 
			}
			return "<div class='" . $params['container_class'] . "' $require >$icons</div>
			<input type=hidden name='shm_point_type' class='sh-form shm-bg-transparent small' />
			<input type=hidden name='shm_point_lat' class='sh-form shm-bg-transparent small' />
			<input type=hidden name='shm_point_lon' class='sh-form shm-bg-transparent small' />
			<input type=hidden name='elem[]' class='sh-form shm-bg-transparent small' />
			<input type=text name='shm_point_loc' class='sh-form shm-bg-transparent small ".(count($terms) > 1 ? "hidden" : "")."' />";
			$element['description'] .= __("Drag choosed icon and place it to map or click it and enter exact address.", SHMAPPER);
		}
	}
}
