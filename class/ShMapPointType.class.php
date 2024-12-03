<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShMapPointType
{
	static function init()
	{
		add_action('init',				array(__CLASS__, 'register_all'), 11 );
		add_action( 'parent_file',		array(__CLASS__, 'tax_menu_correction'), 1);	
		add_action( 'admin_menu', 		array(__CLASS__, 'tax_add_admin_menus'), 11);
		add_filter("manage_edit-".SHM_POINT_TYPE."_columns", array( __CLASS__,'ctg_columns')); 
		add_filter("manage_".SHM_POINT_TYPE."_custom_column",array( __CLASS__,'manage_ctg_columns'), 11.234, 3);
		add_action( SHM_POINT_TYPE.'_add_form_fields', 		array( __CLASS__, 'new_ctg'), 10, 2 );
		add_action( SHM_POINT_TYPE.'_edit_form_fields', 	array( __CLASS__, 'add_ctg'), 2, 2 );
		add_action( 'edit_'.SHM_POINT_TYPE, 				array( __CLASS__, 'save_ctg'), 10);  
		add_action( 'create_'.SHM_POINT_TYPE, 				array( __CLASS__, 'save_ctg'), 10);
		add_action( 'before_delete_post',  					array( __CLASS__, 'before_delete_post') );

	}
	
	static function before_delete_post( $post_id )
	{
		global $wpdb;
		$query = "
		DELETE FROM " . $wpdb->prefix . "point_map 
		WHERE point_id=$post_id;";
		$wpdb->query($query);
	}
	static function register_all()
	{
		//Map marker type
		$labels = array(
			'name'              => __("Map marker type", SHMAPPER),
			'singular_name'     => __("Map marker type", SHMAPPER),
			'search_items'      => __("Search Map marker type", SHMAPPER),
			'all_items'         => __("All Map marker types", SHMAPPER),
			'view_item '        => __("View Map marker type", SHMAPPER),
			'parent_item'       => __("Parent Map marker type", SHMAPPER),
			'parent_item_colon' => __("Parent Map marker type:", SHMAPPER),
			'edit_item'         => __("Edit Map marker type", SHMAPPER),
			'update_item'       => __("Update Map marker type", SHMAPPER),
			'add_new_item'      => __("Add Map marker type", SHMAPPER),
			'new_item_name'     => __("New Map marker type name", SHMAPPER),
			'menu_name'         => __("Map marker type", SHMAPPER),
		);
		register_taxonomy(SHM_POINT_TYPE, [ ], 
		[
			'label'                 => '',
			'labels'                => $labels,
			'description'           => __('Unique type of every Map markers', SHMAPPER),
			'public'                => true,
			'hierarchical'          => false,
			'update_count_callback' => '',
			'show_in_nav_menus'     => true,
			'rewrite'               => true,
			'capabilities'          => array(),
			'meta_box_cb'           => "post_categories_meta_box",
			'show_admin_column'     => true,
			'_builtin'              => false,
			'show_in_quick_edit'    => true,
		] );
	}
	static function tax_menu_correction($parent_file) 
	{
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if ( $taxonomy == SHM_POINT_TYPE )
			$parent_file = 'shm_page';
		return $parent_file;
	}
	static function tax_add_admin_menus() 
	{
		add_submenu_page( 
			'shm_page', 
			__("Map marker types", SHMAPPER), 
			__("Map marker types", SHMAPPER), 
			'edit_others_posts', 
			'edit-tags.php?taxonomy=' . SHM_POINT_TYPE
		);
    }
	static function ctg_columns($theme_columns) 
	{
		$new_columns = array
		(
			'cb' 			=> ' ',
			'id' 			=> 'id',
			'name' 			=> __('Name', 'shmapper-by-teplitsa'),
			'icon' 			=> __('Icon', 'shmapper-by-teplitsa')
		);
		return $new_columns;
	}
	static function manage_ctg_columns($out, $column_name, $term_id) 
	{
		switch ($column_name) {
			case 'id':
				$out .= $term_id;
				break;
			case 'icon': 
				$icon = get_term_meta( $term_id, 'icon', true ); 
				$color = get_term_meta( $term_id, 'color', true );
				$logo = wp_get_attachment_image_url( $icon, "full" );
				echo '<div class="hms-column-icon">
					<img src="' . esc_url( $logo ) . '" alt="">
					<div class="hms-column-color" style="background-color: ' . esc_attr( $color ) . ';"></div>
				</div>';
				break;
			default:
				break;
		}
		return $out;
	}
	static function new_ctg( $tax_name )
	{
		require_once( SHM_REAL_PATH . "tpl/input_file_form.php" );
		if ( ! isset( $color ) ) {
			$color = '';
		}
		?>
		<div class="form-field term-description-wrap">
			<label for="color">
				<?php echo esc_html__("Color", SHMAPPER);  ?>
			</label> 
			<div class="bfh-colorpicker" data-name="color" data-color="<?php echo esc_attr( $color ); ?>">
			</div>
			<input type="color" name="color" value="<?php echo esc_attr( empty($color) ? '' : $color ); ?>" />
		</div>
		<div class="form-field term-description-wrap">
			<label for="width">
				<?php echo __("Width", SHMAPPER);  ?>
			</label> 
			<input type="number" name="width" value="<?php echo esc_attr( empty($width) ? '' : $width );?>" />
		</div>
		<div class="form-field term-description-wrap">
			<label for="height">
				<?php echo __("Height", SHMAPPER);  ?>
			</label> 
			<input type="number" name="height" value="<?php echo esc_attr( empty($height) ? '' : $height ); ?>" />
		</div>
		<div class="form-field term-description-wrap">
			<label for="icon">
				<?php echo __("Icon", SHMAPPER);  ?>
			</label> 
			<div class='shm-flex'>
			<?php
				echo get_input_file_form2( "icon", empty($icon) ? '' : $icon, "icon", 0 );
			?>
			</div>
		</div>
		
		<?php
	}
	static function add_ctg( $term, $tax_name )
	{
		require_once( SHM_REAL_PATH . "tpl/input_file_form.php" );
		if ( $term ) {
			$term_id = $term->term_id;
			$icon    = get_term_meta($term_id, "icon", true);
			$color   = get_term_meta($term_id, "color", true);
			$height  = get_term_meta($term_id, "height", true);
			$height  = !$height ? 30 : $height;
			$width   = get_term_meta($term_id, "width", true);
			$width   = !$width ? 30 : $width;
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="color">
					<?php echo esc_html__("Color", SHMAPPER);  ?>
				</label> 
			</th>
			<td>
				<div class="bfh-colorpicker" data-name="color" data-color="<?php echo esc_attr( $color ); ?>">
				</div>
				<input type="color" name="color" value="<?php echo esc_attr( $color ); ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="height">
					<?php echo esc_html__( 'Height', SHMAPPER ); ?>
				</label> 
			</th>
			<td>
				<input type="number" name="height" value="<?php echo esc_attr( $height ); ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="width">
					<?php echo esc_html__( 'Width', SHMAPPER ); ?>
				</label> 
			</th>
			<td>
				<input type="number" name="width" value="<?php echo esc_html( $width ); ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="icon">
					<?php echo esc_html__( 'Icon', SHMAPPER ); ?>
				</label> 
			</th>
			<td>
				<?php
					echo get_input_file_form2( "icon", $icon, "icon", 0 );
				?>
			</td>
		</tr>
		<?php
	}
	static function save_ctg( $term_id ) {
		update_term_meta($term_id, "icon", 	sanitize_text_field($_POST['icon0']));
		update_term_meta($term_id, "color", sanitize_hex_color($_POST['color']));
		update_term_meta($term_id, "height", sanitize_text_field($_POST['height']));
		update_term_meta($term_id, "width", sanitize_text_field($_POST['width']));
	}
	static function get_icon($term, $is_locked=false)
	{
		$term_id   = '';
		$term_name = '';
		if ( isset( $term->term_id ) ) {
			$term_id = $term->term_id;
			$term_name = $term->name;
		}
		$color    = get_term_meta( $term_id, "color", true);
		$icon     = (int)get_term_meta( $term_id, "icon", true);
		$icon_url = wp_get_attachment_image_url($icon, 'medium_large');

		$default_marker = shm_get_default_marker( $color );

		$cur_bgnd = '&quot;' . $default_marker['icon'] . '&quot;';
		if ( $icon_url ) {
			$cur_bgnd = $icon_url;
		}
		$class = $is_locked ? " shm-muffle " : "";
		if ( $cur_bgnd ) {
			$color = 'transparent';
		}

		return '
		<div class="ganre_picto ' . $class . '" term="' . SHM_POINT_TYPE . '" term_id="' . $term_id . '">
			<div class="shm_type_icon" style="background-image:url(' . $cur_bgnd . ');"></div>
			<div class="ganre_label">' . $term_name . '</div>
		</div>';
	}
	static function get_all_ids()
	{
		return get_terms([
			"taxonomy" 		=> SHM_POINT_TYPE,
			"hide_empty"	=> false,
			"fields"		=> "ids"
			
		]);
	}
	static function wp_dropdown($params=-1)
	{
		if(!is_array($params))
			$params=[ "id" => "ganres", "name" => "ganres", "class"=> "form-control", "taxonomy"=> SHM_POINT_TYPE];
		$all = get_terms(['taxonomy' => $params['taxonomy'], 'hide_empty' => false ]);
		$multiple = $params['multiple'] ? " multiple " : "" ;
		$selector =$params['selector']  ? " selector='" . $params['selector'] . "' " : " s='ee' ";
		$html = "<select name='".$params['name']."' id='".$params['id']."' $multiple class='".$params['class']."' $selector>";
		foreach($all as $term)
		{
			$selected = in_array($term->term_id, $params['selected']) ? "selected" : "";
			$html .= "<option value='" . $term->term_id . "' $selected >" . $term->name . "</option>";
		}
		$html .="</select>";
		return $html;
	}
	static function get_all_data()
	{
		$types = get_terms([
			"taxonomy" 		=> SHM_POINT_TYPE,
			"hide_empty"	=> false 			
		]);
		$ret = [];
		foreach($types as $type)
		{
			$ret[] = [
				"id"		=> $type->term_id,
				"title" 	=> $type->name,
				"content"	=> $type->description,
				"icon"		=> static::get_icon_src( $type->term_id )
			]; 
		}
		return $ret;
	}
	static function get_icon_src($term_id, $size=-1)
	{
		$size 		= $size == -1 ? get_term_meta( $term_id, "height", true ) : $size;
		$icon 		= get_term_meta( $term_id, "icon", true );
		$d 			= wp_get_attachment_image_src( $icon, array($size, $size) );
		return $d;
	}

	static function get_icon_url( $term_id )
	{
		$icon_id  = get_term_meta( $term_id, 'icon', true );
		$icon_url = wp_get_attachment_image_url( $icon_id );

		return $icon_url;
	}

	static function get_icon_default_marker( $color )
	{

		if ( ! $color ) {
			$color = '#f43724';
		}
		$color = str_replace('#', '', $color );
		$color_second = shm_colour_brightness($color, 0.6);

		$marker = '
		<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="36px" viewBox="13 11 30 36">
			<path fill="#' . $color_second . '" d="M42.929,24.838c-0.234-2.622-1.135-5.137-2.615-7.3c-1.48-2.163-3.489-3.899-5.829-5.039
				c-2.341-1.14-4.933-1.644-7.523-1.463c-2.59,0.181-5.09,1.04-7.255,2.494c-1.854,1.257-3.411,2.915-4.558,4.855
				c-1.147,1.94-1.856,4.113-2.077,6.364c-0.216,2.236,0.061,4.493,0.812,6.606s1.956,4.032,3.529,5.614l9.353,9.501
				c0.164,0.168,0.359,0.301,0.574,0.392S27.785,47,28.018,47s0.464-0.047,0.679-0.138c0.215-0.091,0.41-0.224,0.574-0.392l9.317-9.501
				c1.573-1.583,2.778-3.501,3.529-5.614c0.751-2.114,1.028-4.37,0.812-6.606V24.838z M36.117,34.447L28,42.677l-8.117-8.231
				c-1.196-1.213-2.113-2.68-2.683-4.295c-0.571-1.615-0.781-3.338-0.617-5.045c0.166-1.734,0.709-3.408,1.591-4.903
				c0.882-1.495,2.08-2.772,3.509-3.739c1.872-1.261,4.07-1.934,6.317-1.934s4.445,0.673,6.317,1.934
				c1.424,0.964,2.62,2.235,3.502,3.723c0.882,1.488,1.428,3.156,1.598,4.883c0.17,1.713-0.038,3.443-0.609,5.065
				C38.237,31.757,37.318,33.23,36.117,34.447z M36.117,34.447L28,42.677l-8.117-8.231c-1.196-1.213-2.113-2.68-2.683-4.295
				c-0.571-1.615-0.781-3.338-0.617-5.045c0.166-1.734,0.709-3.408,1.591-4.903c0.882-1.495,2.08-2.772,3.509-3.739
				c1.872-1.261,4.07-1.934,6.317-1.934s4.445,0.673,6.317,1.934c1.424,0.964,2.62,2.235,3.502,3.723
				c0.882,1.488,1.428,3.156,1.598,4.883c0.17,1.713-0.038,3.443-0.609,5.065C38.237,31.757,37.318,33.23,36.117,34.447z"/>
			<ellipse fill="#' . $color . '" cx="28" cy="26" rx="10.5" ry="10.5"/>
		</svg>
		';

		return $marker;
	}

	static function get_ganre_swicher($params = -1, $type="checkbox", $form_factor="large")
	{
		if( !is_array($params) || empty($params['prefix']) ) {
			$params = array('prefix' => 'ganre');
		}

		if ( ! isset( $params['selected'] ) ) {
			$params['selected'] = array();
		}

		if ( ! isset( $params['col_width'] ) ) {
			$params['col_width'] = '';
		}

		$selected = is_array($params['selected']) ? $params['selected'] : explode(",", $params['selected']);

		$includes = empty($params['includes']) ? '' : $params['includes'];

		$row_class = isset($params['row_class']) ? $params['row_class'] : "" ;
		$row_style = isset($params['row_style']) ? $params['row_style'] : ""; ;
		$ganres	= get_terms(["taxonomy" => SHM_POINT_TYPE, 'hide_empty' => false ]);

		$html 	= "<div class='shm-row point_type_swicher $row_class' style='$row_style'>";
		switch($params['col_width'])
		{
			case 12:
				$col_width	= "shm-1";
				break;
			case 6:
				$col_width	= "shm-2";
				break;
			case 4:
				$col_width	= "shm-3";
				break;
			case 3:
				$col_width	= "shm-4";
				break;
			default:
			case 2:
				$col_width	= "shm-6";
				break;
			
		}
		foreach($ganres as $ganre) {
			if( is_array($includes) && !in_array( $ganre->term_id, $includes ) ) continue;
						
			$icon     = get_term_meta($ganre->term_id, "icon", true);
			$color    = get_term_meta($ganre->term_id, "color", true);
			$icon_url = wp_get_attachment_image_url($icon, 'medium_large');

			$default_marker = shm_get_default_marker( $color );

			$icon = '&quot;' . $default_marker['icon'] . '&quot;';
			$cur_bgnd = '';
			if ( $icon_url ) {
				$cur_bgnd = $icon_url;
			}
			$before = "";
			$after  = "";

			switch( $form_factor )
			{
				case "large":

					$marker = $cur_bgnd ? '

					<div class="shm-marker-image" style="background-image:url(' . $cur_bgnd. ');"></div>

					' : '<div class="shm-marker-svg" style="background-image:url(&quot;' . $default_marker['icon'] . '&quot;);background-size: ' . $default_marker['width'] . 'px ' . $default_marker['height'] . 'px"></div>';
					$class = "ganre_checkbox shm-marker-checkbox";
					$before = "<div class='$col_width'>";
					$after = '
						<label for="' . $params['prefix'] . '_' . $ganre->term_id . '">
							' . $ganre->name . 
							$marker .
						'</label>
					</div>';
					break;
				case "stroke":
					$class = "ganre_checkbox2";
					$img = $cur_bgnd ? '<img src="' . $cur_bgnd . '" alt="">' : '<div class="shm-filter-item-svg" style="background-image:url(' . $icon . ');"></div>';
					$after = '
						<label for="' . $params['prefix'] . '_' . $ganre->term_id . '" title="' . $ganre->name . '">' . $img . '</label>';
					break;
				case "stroke-large":
					$class = "ganre_checkbox";
					$after = "
						<label for='" . $params['prefix'] . "_" . $ganre->term_id . "' title='" . $ganre->name . "'>".
							($cur_bgnd ? "<img src='$cur_bgnd' alt='' />" : "<div class='shm-clr' style='background:$color;'></div>").
						"</label>";
					break;

				case "stroke-filter":
					$class = "ganre_checkbox2";
					$img = $cur_bgnd ? '<img src="' . $cur_bgnd . '" alt="">' : '<div class="shm-filter-item-svg" style="background-image:url(' . $icon . ');"></div>';
					$after = '
						<label for="' . $params['prefix'] . '_' . $ganre->term_id . '" title="' . $ganre->name . '"><div class="hms-filter-image">' . $img . '</div></label>';
					break;

				default:
					$class = "ganre_checkbox";
					break;
			}
			$html .= "
				$before
				<input 
					type='$type' 
					name='" . $params['prefix'] . ($type == "checkbox" ?  "[]'" : "'").
					"id='" . $params['prefix'] . "_" . $ganre->term_id . "'
					term_id='" . $ganre->term_id . "'
					class='$class'
					value='" . $ganre->term_id . "' ".
					checked(1, in_array( $ganre->term_id, $selected) ? 1 : 0, false).
				"/>
				$after";
		}

		if ( isset( $params['default_none'] ) ) {
			if ( ! isset( $class ) ) {
				$class = 'ganre_checkbox';
			}

			$default_marker = shm_get_default_marker();
			$marker_html = '<div class="shm-marker-svg" style="background-image:url(&quot;' . $default_marker['icon'] . '&quot;);background-size: ' . $default_marker['width'] . 'px ' . $default_marker['height'] . 'px"></div>';
			$html .= "
			<div class='$col_width'>
				<input 
					type='$type' 
					name='" . $params['prefix'] . ($type == "checkbox" ?  "[]'" : "'").
					"id='" . $params['prefix'] . "_" . 0 . "'
					term_id='" . 0 . "'
					class='$class'
					value='" . -1 . "' ".
					checked( -1, $selected[0], false ).
				"/>
				<label for='" . $params['prefix'] . "_" . 0 . "'>" . 
					__("Default Marker", SHMAPPER) . 
					//"<div class='shm-clr' style='background:#ffffff;'></div>" .
					$marker_html .
				"</label>
			</div>";
		}

		$html .= "
			<input type='hidden' id='".$params['prefix']."pointtype' name='".(empty($params['name']) ? '' : $params['name'])."' point='' value='".(is_array($params['selected']) ? implode(",", $params['selected']) : $params['selected']) . "' />
		</div>";

		return $html;

	}
}
