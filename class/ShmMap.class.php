<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShmMap extends SMC_Post
{
	static function get_map_types()
	{
		return [
			"1" => [
				"map",
				"hybrid",
				"satellite",
			],
			"2" => [
				"OpenStreetMap",
				"Topographic",
				"Streets",
				"Gray",
				"DarkGray", 
				"Imagery",
				"Physical"
			]
		];
	}
	static function init()
	{
		$typee = static::get_type();
		add_action('init',					array(__CLASS__, 'add_class'), 14 );
		add_action('admin_menu',			array(__CLASS__, 'my_form_fields'), 11);
		add_action('admin_menu',			array(__CLASS__, 'shortcode_fields'), 11);
		add_action('admin_menu',			array(__CLASS__, 'admin_redirect'), 11);
		add_filter("the_content",			array(__CLASS__, "the_content"));
		add_filter( 'post_row_actions', 		[__CLASS__, 'post_row_actions'], 10, 2 );
		add_action("smc_before_doubled_post",	[__CLASS__, "smc_before_doubled_post"], 10, 2);
		add_action("smc_after_doubled_post",	[__CLASS__, "smc_after_doubled_post"], 10, 2);
		add_filter("bulk_actions-edit-{$typee}",[__CLASS__, "register_my_bulk_actions"]);
		parent::init();
	}
	static function get_osm_types()
	{
		return [
			"Topographic",
			"Streets",
			"NationalGeographic",
			"Oceans",
			"Gray",
			"DarkGray",
			"StreetsRelief", 
			"Imagery",
			"ImageryClarity",
			"ImageryFirefly",
			"Physical"
		];
	}
	static function register_my_bulk_actions( $bulk_actions )
	{
		unset($bulk_actions['trash']);
		$bulk_actions = parent::register_my_bulk_actions( $bulk_actions );
		return $bulk_actions;
	}
	static function get_type()
	{
		return SHM_MAP;
	}
	static function get_extra_fields_title()
	{
		return __("Step 1. Set up your map.", SHMAPPER);
	}
	static function add_class()
	{
		$labels = array(
			'name' => __('Map', SHMAPPER),
			'singular_name' => __("Map", SHMAPPER),
			'add_new' => __("add Map", SHMAPPER),
			'add_new_item' => __("add Map", SHMAPPER),
			'edit_item' => __("edit Map", SHMAPPER),
			'new_item' => __("add Map", SHMAPPER),
			'all_items' => __("all Maps", SHMAPPER),
			'view_item' => __("view Map", SHMAPPER),
			'search_items' => __("search Map", SHMAPPER),
			'not_found' =>  __("Map not found", SHMAPPER),
			'not_found_in_trash' => __("no found Map in trash", SHMAPPER),
			'menu_name' => __("all Maps", SHMAPPER)
		);
		$args = array(
			 'labels' => $labels
			,'public' => true
			,'show_ui' => true
			,'has_archive' => true
			,'exclude_from_search' => false
			,'menu_position' => 17
			,'menu_icon' => "dashicons-location-alt"
			,'show_in_menu' => "shm_page"
			,'show_in_rest' => true
			,'supports' => array(  'title', 'author' )
			,'capability_type' => 'page'
		);
		register_post_type(SHM_MAP, $args);
	}
	
	static function add_views_column( $columns )
	{
		$_columns["cb"]				= " ";
		$_columns['ids']			= __("ID", "shmapper-by-teplitsa");
		$_columns['title']			= __("Title", "shmapper-by-teplitsa");
		$_columns['is_csv']	= "
		<span 
			class='dashicons dashicons-editor-justify shm-notify' 
			title='" . __("Export csv", SHMAPPER)."'>
		</span>";
		$_columns['is_legend']	= "
		<span 
			class='dashicons dashicons-image-filter shm-notify' 
			title='" . __("Legend exists", SHMAPPER)."'>
		</span>";
		$_columns['is_form']	= "
		<span 
			class='dashicons dashicons-clipboard shm-notify' 
			title='" . __("Form exists", SHMAPPER)."'>
		</span>";
		$_columns['notify_owner']	= "
		<span 
			class='dashicons dashicons-megaphone shm-notify' 
			title='" . __("Notify owner of Map", SHMAPPER)."' data-title='" . __("Notify owner of Map", SHMAPPER)."'>
		</span>";
		$_columns['shortcodes']		= __("shortcodes", "shmapper-by-teplitsa");
		$_columns['placemarks']		= __("Map markers", "shmapper-by-teplitsa");
		$_columns['author']			= __("Author", "shmapper-by-teplitsa");
		return $_columns;
	}
	
	static function fill_views_column($column_name, $post_id) 
	{
		$obj = static::get_instance($post_id);
		switch($column_name)
		{
			case "ids":
				echo esc_html( $post_id );
				break;
			case "placemarks":
				echo "<div class='shm-title-2'>" . esc_attr( $obj->get_point_count() ) . "</div>";
				break;
			case "shortcodes":
				$html = "
				<div class='shm-row'>
					<div class='shm-12 shm-md-12 shm-color-lightgrey small'>".
						__("include all (map and request form)", SHMAPPER) .
					"</div>
					<div class='shm-12 shm-md-12'>
						<input type='text' disabled  class='sh-form' value='[shmMap id=\"" . esc_attr( $post_id ) . "\"]' />
					</div>
				</div>
				<div class='shm-row'>
					<div class='shm-12 shm-md-12 shm-color-lightgrey small' >".
						__("only map", SHMAPPER) .
					"</div>
					<div class='shm-12 shm-md-12'>
						<input type='text' disabled  class='sh-form' value='[shmMap id=\"" . esc_attr( $post_id ) . "\" map=\"true\"]' />
					</div>
				</div>
				</div>
				<div class='shm-row'>
					<div class='shm-12 shm-md-12 shm-color-lightgrey small'>".
						__("only request form", SHMAPPER) .
					"</div>
					<div class='shm-12 shm-md-12'>
						<input type='text' disabled  class='sh-form' value='[shmMap id=\"" . esc_attr( $post_id ) . "\" form=\"true\"]' />
					</div>
				</div>
				";
				echo $html;
				break;
			default:
				parent::fill_views_column($column_name, $post_id);
		}
	}

	static function admin_redirect()
	{
		global $pagenow, $submenu ;
		if( !empty($_GET["page"]) && "shm_page" == $_GET["page"] && "admin.php" === $pagenow)
		{
			wp_redirect( admin_url( '/admin.php?page=shm_settings_page' ) );
		}
	}	
	static function shortcode_fields() 
	{
		add_meta_box( 'shortcode_fields', __('Including Map to post', SHMAPPER), [__CLASS__, 'shortcode_fields_box_func'], static::get_type(), 'side', 'low'  );		
	}
	
	static function shortcode_fields_box_func( $post )
	{	
		$lt = static::get_instance( $post );
		echo static::shortcode_fields_edit($lt);
	}
	static function shortcode_fields_edit($obj)
	{
		$html = '
		<p class="description">' . __( 'You can insert a card into a post or page by copying this shortcode.', 'shmapper-by-teplitsa' ) . '</p>
		<input type="text" disabled class="sh-form" value=\'[shmMap id="' . esc_attr( $obj->id ) . '"]\'>
		<br>
		<p class="description">' . __( 'And also you can use a Gutenberg block ShMapper Map.', 'shmapper-by-teplitsa' ) . '</p>
		<div class="shm-metabox-block-example">
			<img src="' . esc_url( SHM_URLPATH . 'assets/img/block-example.png' ) . '">
		</div>
		';
		return $html;
	}
	
	static function my_extra_fields() 
	{
		add_meta_box( 'map_fields', static::get_extra_fields_title(), [__CLASS__, 'extra_fields_box_func'], static::get_type(), 'normal', 'high'  );
		
	}
	static function my_form_fields() 
	{
		add_meta_box( 'form_fields', __('Step 2. May anover Users add information for Map.', SHMAPPER), [__CLASS__, 'form_fields_box_func'], static::get_type(), 'normal', 'high'  );
		
	}
	
	static function view_admin_edit($obj)
	{
		require_once( SHM_REAL_PATH . "tpl/input_file_form.php" );
		$map_source        = ShmMap::get_map_types()[ ShMapper::$options['map_api'] ][0];
		$height            = $obj->get_meta( 'height' ) ? $obj->get_meta( 'height' ) : 400;
		$latitude          = $obj->get_meta( 'latitude' );
		$longitude         = $obj->get_meta( 'longitude' );
		$zoom              = $obj->get_meta( 'zoom' );
		$width             = $obj->get_meta( 'width' );
		$is_search         = $obj->get_meta( 'is_search' );
		$is_zoomer         = $obj->get_meta( 'is_zoomer' );
		$is_layer_switcher = $obj->get_meta( 'is_layer_switcher' );
		$is_fullscreen     = $obj->get_meta( 'is_fullscreen' );
		$is_csv            = $obj->get_meta( 'is_csv' );
		$is_title          = ( $obj->get_meta( 'is_title' ) !== '' ) ? $obj->get_meta( 'is_title' ) : '1';
		$is_legend         = $obj->get_meta( 'is_legend' );
		$is_filtered       = $obj->get_meta( 'is_filtered' );
		$default_icon_id   = $obj->get_meta( 'default_icon_id' );
		$is_clustered      = $obj->get_meta( 'is_clustered' );
		$is_lock           = $obj->get_meta( 'is_lock' );
		$is_scroll_zoom    = $obj->get_meta( 'is_scroll_zoom' );
		$is_drag           = $obj->get_meta( 'is_drag' );
		$form_title        = $obj->get_meta( 'form_title' );
		$highlight_country = $obj->get_meta( 'highlight_country' );
		$overlay_color     = $obj->get_meta( 'overlay_color' ) ? $obj->get_meta( 'overlay_color' ) : '#d1d1d1';
		$border_color      = $obj->get_meta( 'border_color' ) ? $obj->get_meta( 'border_color' ) : '#d1d1d1';
		$overlay_opacity   = $obj->get_meta( 'overlay_opacity' ) ? $obj->get_meta( 'overlay_opacity' ) : '0.8';
		$kml_url           = $obj->get_meta( '_shm_kml_url' );

		if ( ! $is_scroll_zoom && ! $is_drag && $is_lock ) {
			$is_scroll_zoom = 1;
			$is_drag        = 1;
		}

		$html 	= "
			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.1. Pan map and choose zoom", SHMAPPER). "</h3>
				<div class='shm-12'>
					<div class='shm-map-block'>
					" . $obj->draw( [ 'height'=>$height, 'id' => $obj->id ] ) . "
					</div>
				</div>
				<div class='shm-12'>
					<input type='hidden' value='". esc_attr( $latitude ) ."' name='latitude' />
					<input type='hidden' value='". esc_attr( $longitude ) ."' name='longitude' />
					<input type='hidden' value='". esc_attr( $zoom ) ."' name='zoom' />
				</div>
			</div>
			<div class='spacer-15'></div>
			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.2. Set size for map's div (per pixels)", SHMAPPER). "</h3>
				<div class='shm-12'>
					<div class='shm-admin-block'>
						<label>" . __("Height", "shmapper-by-teplitsa") . "</label>
						<input type='number' value='". esc_attr( $height ) ."' name='height' />
						<p class='description'>" . __("Empty for ", "shmapper-by-teplitsa") . "400px</p>
					</div>
					<div class='shm-admin-block'>
						<label>" . __("Width", "shmapper-by-teplitsa") . "</label>
						<input type='number' value='". esc_attr( $width ) ."' name='width' />
						<p class='description'>" . __("Empty for ", SHMAPPER) . "100%</p>
					</div>
					<div class='shm-admin-block'>
					</div>
				</div>
			</div>
			<div class='spacer-5'></div>
			<hr/>
			<div class='spacer-5'></div>
			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.3. Include interface", SHMAPPER). "</h3>
				<div class='shm-12'>
					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_search, false) ."' name='is_search' id='is_search'/>
						<label for='is_search'>" . __("Map search", SHMAPPER) . "</label>
					</div>

					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_scroll_zoom, false) ."' name='is_scroll_zoom' id='is_scroll_zoom'/>
						<label for='is_scroll_zoom'>" . __( "Disable scroll zoom", 'shmapper-by-teplitsa' ) . "</label>
					</div>
				</div>

				<div class='shm-12'>
					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_zoomer, false) ."' name='is_zoomer' id='is_zoomer'/>
						<label for='is_zoomer'>" . __("Map zoom slider enabled", SHMAPPER) . "</label>
					</div>

					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_drag, false) ."' name='is_drag' id='is_drag'/>
						<label for='is_drag'>" . __( "Disable dragging", 'shmapper-by-teplitsa' ) . "</label>
					</div>
				</div>

				<div class='shm-12'>
					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_layer_switcher, false) ."' name='is_layer_switcher' id='is_layer_switcher'/>
						<label for='is_layer_switcher'>" . __("Map layer switcher", SHMAPPER) . "</label>
					</div>
				</div>

				<div class='shm-12'>
					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_fullscreen, false) ."' name='is_fullscreen' id='is_fullscreen'/>
						<label for='is_fullscreen'>" . __("Map full screen", SHMAPPER) . "</label>
					</div>
				</div>

				<div class='shm-12'>
					<div class='shm-admin-block'>
						<input type='checkbox' value='1' ". checked(1, $is_clustered, false) ."' name='is_clustered' id='is_clustered'/>
						<label for='is_clustered'>" . __("Formating Marker to cluster", SHMAPPER) . "</label>
					</div>
				</div>

				<div class='spacer-10'></div>
				<h4 class='shm-12'>". __("Choose layers", SHMAPPER). "</h4>
				<div class='shm-12'>".
					static::get_type_radio([
						"id"		=> "map_type",
						"name"		=> "map_type",
						"selected"	=> $obj->get_meta("map_type"),
					]).
				"</div>
			</div>
			
			<div class='spacer-5'></div>
			<hr>
			<div class='spacer-5'></div>

			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.4. Map title", SHMAPPER). "</h3>
				<div class='shm-12'>
					<label for='is_title'><input type='checkbox' value='1' " . checked( 1, $is_title, false ) . "' name='is_title' id='is_title'/> " . __( "Show map title", SHMAPPER ) . "</label>
				</div>
			</div>

			<div class='spacer-5'></div>
			<hr>
			<div class='spacer-5'></div>

			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.5. May User download data in *.csv?", SHMAPPER). "</h3>
				<div class='shm-12'>
					<input type='checkbox' value='1' ". checked(1, $is_csv, false) ."' name='is_csv' id='is_csv'/>
					<label for='is_csv'>" . __("Export csv", SHMAPPER) . "</label>
					
				</div>
			</div>

			<div class='spacer-5'></div>
			<hr>
			<div class='spacer-5'></div>

			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.6. Will the legend be displayed?", SHMAPPER). "</h3>
				<div class='shm-12'>
					<input type='checkbox' value='1' ". checked(1, $is_legend, false) ."' name='is_legend' id='is_legend'/>
					<label for='is_legend'>" . __("Legend exists", SHMAPPER) . "</label>
					
				</div>
			</div>
			<div class='spacer-5'></div>
			<hr/>
			<div class='spacer-5'></div>
			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.7. Will Marker type filter be displayed?", SHMAPPER). "</h3>
				<div class='shm-12'>
					<input type='checkbox' value='1' ". checked(1, $is_filtered, false) ."' name='is_filtered' id='is_filtered'/>
					<label for='is_filtered'>" . __("Filters exists", SHMAPPER) . "</label>
				</div>
			</div>
			<div class='spacer-5'></div>
			<hr/>
			<div class='spacer-5'></div>
			<div class='shm-row'>
				<h3 class='shm-12'>". __("1.8. Default Marker icon", SHMAPPER). "</h3>
				<div class='shm-12'>".
					get_input_file_form2( "" , $default_icon_id, "default_icon_id").
				"</div>
				<p class='description shm-12'>".
					__("Recommended size is 64х64 px, format is .png", SHMAPPER) .
				"</p>
			</div>";

			if ( $map_source === 'map' ) {
				$html 	.= "
				<div class='spacer-5'></div>
				<hr/>
				<div class='spacer-5'></div>
				<div class='shm-row'>

					<h3 class='shm-12'>". __( "1.9. Highlight the country on the map", SHMAPPER ) . "</h3>

					<div class='shm-12'>
						<select class='small-text' name='highlight_country' data-value='" . esc_attr( $highlight_country ) . "'>
							<option>" . esc_html__( "Loading countries ... ", SHMAPPER ) . "</option>
						</select>
						<p class='description'>".
							__("Select country", SHMAPPER) .
						"</p>
						<div class='spacer-5'></div>
						<div class='spacer-5'></div>
					</div>
					<div class='shm-12'>
						<div class='shm-admin-block'>
							<input type='text' name='overlay_color' value='" . esc_attr( $overlay_color ) . "'>
							<p class='description'>".
								__("Choose map overlay color", SHMAPPER) .
							"</p>
						</div>
						<div class='shm-admin-block'>
							<input type='text' name='border_color' value='" . esc_attr( $border_color ) . "'>
							<p class='description'>".
								__("Choose country border color", SHMAPPER) . 
							"</p>
						</div>
						<div class='shm-admin-block'>
							<input type='range' min='0.1' max='1' step='0.1' class='shm-range' name='overlay_opacity' value='" . esc_attr( $overlay_opacity ) . "'>
							<p class='description'>".
								__("Overlay opacity", SHMAPPER) .
							"</p>
						</div>
					</div>

				</div>
				";
			}

			if ( shm_get_map_type() === 1 ) {

				$hide_clear = ' hidden';
				if ( $kml_url ) {
					$hide_clear = '';
				}
				$html 	.= '
				<div class="spacer-5"></div>
				<hr>
				<div class="spacer-5"></div>

				<div class="shm-row">

					<h3 class="shm-12">' . esc_html__( '1.10. Load elements on the map from file .KML', SHMAPPER ) . '</h3>
					<div class="shm-12">

						<div class="shm-form-inline">
							<input type="text" value="' . esc_url( $kml_url ) . '" name="shm_kml_url" class="large-text shm-input-kml-url" readonly>
							<button class="button button-secondary shm-load-kml-button" data-title="' .  esc_html__( 'Select KML File', SHMAPPER ) . '" data-button="' .  esc_html__( 'Insert KML File Url', SHMAPPER ) . '">' . esc_html__( 'Upload KML File', SHMAPPER ) . '</button>
							<button class="button button-link button-link-delete shm-clear-kml-button' . $hide_clear . '">' . esc_html__( 'Clear', SHMAPPER ) . '</button>
						</div>

						<p class="description">
							<a href="https://yandex.com/map-constructor/" target="_blank">' . esc_html__( 'Yandex Map Constructor', SHMAPPER ) . '</a>
						</p>
						<div class="spacer-5"></div>
						<div class="spacer-5"></div>
					</div>

				</div>
				';
			}

		return $html;
	}
	static function form_fields_box_func( $post )
	{	
		$lt = static::get_instance( $post );
		echo static::view_form_fields_edit($lt);			
	}
	static function view_form_fields_edit($obj)
	{
		$is_form = $obj->get_meta("is_form") ? 1 : 0;
		$is_filtered = $obj->get_meta("is_filtered") ? 1 : 0;
		$form_title = $obj->get_meta("form_title");
		$notify_owner = $obj->get_meta("notify_owner") ?  1 : 0;
		$form_forms = $obj->get_meta("form_forms");
		$is_personal_data = $obj->get_meta("is_personal_data");
		$is_name_iclude = $obj->get_meta("is_name_iclude");
		$personal_name = $obj->get_meta("personal_name");
		$is_name_required = $obj->get_meta("is_name_required");
		$is_email_iclude = $obj->get_meta("is_email_iclude");
		$personal_email = $obj->get_meta("personal_email");
		$is_email_required = $obj->get_meta("is_email_required");
		$is_phone_iclude = $obj->get_meta("is_phone_iclude");
		$personal_phone = $obj->get_meta("personal_phone");
		$is_phone_required = $obj->get_meta("is_phone_required");
		$html 	= "
			<div class='shm-row'>
				<div class='shm-12'>
					<input type='checkbox' value='1' name='is_form' id='is_form' " . checked(1, $is_form, 0) . " /> 
					<label for='is_form'>". __("Enable crowdsourcing function (free add Users new Markers)", SHMAPPER). "</label>
				</div>
			</div>
			<div class='shm-map-form-admin'>
				<div class='spacer-5'></div>
				<div class='shm-row'>
					<h3 class='shm-12'>". __("2.1. What is the name of your information form?", SHMAPPER). "</h3>
					<div class='shm-12'>
						<input type='text' value='".$form_title . "' name='form_title' id='form_title' class='shmw-100 shm-form'/>
						<p class='description'>" .
							__("For example &laquo;All beaches by the river&raquo;", SHMAPPER) .
						"</p>
					</div>
				</div>
				<div class='spacer-5'></div>
				<div class='shm-row'>
					<h3 class='shm-12'>". __("2.2. Will I notify the author about new posts?", SHMAPPER). "</h3>
					<div class='shm-12'>
						<input type='checkbox' value='1' ". checked(1, $notify_owner, false) ."' name='notify_owner' id='notify_owner'/>
						<label for='notify_owner'>" . __("Notify owner of Map", SHMAPPER) . "</label>
					</div>
				</div>
				<div class='spacer-5'></div>
				<div class='shm-row'>
					<h3 class='shm-12'>". __("2.3. What information can users enter?", SHMAPPER). "</h3>
					<div class='shm-12'>
						<p class='description'>" .
							__("You can create your own forms using form elements: Heading, Text field, Textarea, Upload file, Markers, Track drawer.", SHMAPPER) .
						"</p>
					</div>
					<div class='shm-12'>".
						static::formEditor( $form_forms ? $form_forms : ShmForm::get_default() ).
					"</div>
				</div>
				<div class='spacer-5'></div>
				<div class='shm-row'>
					<h3 class='shm-12'>". __("2.4. Can users leave their contact information?", SHMAPPER). "</h3>
					<div class='shm-12'>
						<input type='checkbox' value='1' ". checked(1, $is_personal_data, false) ."' name='is_personal_data' id='is_personal_data'/>
						<label for='is_personal_data'>" . __("Users can leave their contact details for feedback.", SHMAPPER) . "</label>
					</div>
				</div>
				<div class='spacer-5'></div>
				<div class='shm-row shm-map-resonals'>
					<h3 class='shm-12'>". __("2.5. What data users will have to put?", SHMAPPER). "</h3>
					<div class='shm-12'>
						<div class='shm-incblock sh-center'>
							<label for='is_name_iclude'>" . __("Include", SHMAPPER) . "</label><br>
							<input type='checkbox' value='1' ". checked(1, $is_name_iclude, false) ."' name='is_name_iclude' id='is_name_iclude'/>
						</div>

						<div class='shm-incblock'>
							<label for='personal_name'>" . __("Personal name", SHMAPPER) . "</label><br>
							<input type='text' value='$personal_name' name='personal_name' id='personal_name' class='shm-admin-block'/>
						</div>

						<div class='shm-incblock'>
							<label for='is_name_required'>" . __("Required", SHMAPPER) . "</label><br>
							<input type='checkbox' value='1' ". checked(1, $is_name_required, false) ."' name='is_name_required' id='is_name_required'/>
						</div>
					</div>
					<div class='shm-12'>
						<div class='shm-incblock sh-center'>
							<label for='is_email_iclude'>" . __("Include", SHMAPPER) . "</label><br>
							<input type='checkbox' value='1' ". checked(1, $is_email_iclude, false) ."' name='is_email_iclude' id='is_email_iclude'/>
						</div>

						<div class='shm-incblock'>
							<label for='personal_email'>" . __("Personal email", SHMAPPER) . "</label><br>
							<input type='text' value='$personal_email' name='personal_email' id='personal_email' class='shm-admin-block'/>
						</div>

						<div class='shm-incblock'>
							<label for='is_email_required'>" . __("Required", SHMAPPER) . "</label><br>
							<input type='checkbox' value='1' ". checked(1, $is_email_required, false) ."' name='is_email_required' id='is_email_required'/>
						</div>
					</div>
					<div class='shm-12'>
						<div class='shm-incblock sh-center'>
							<label for='is_phone_iclude'>" . __("Include", SHMAPPER) . "</label><br>
							<input type='checkbox' value='1' ". checked(1, $is_phone_iclude, false) ."' name='is_phone_iclude' id='is_phone_iclude'/>
						</div>

						<div class='shm-incblock'>
							<label for='personal_phone'>" . __("Personal phone", SHMAPPER) . "</label><br>
							<input type='text' value='$personal_phone' name='personal_phone' id='personal_phone' class='shm-admin-block'/>
						</div>

						<div class='shm-incblock'>
							<label for='is_phone_required'>" . __("Required", SHMAPPER) . "</label><br>
							<input type='checkbox' value='1' ". checked(1, $is_phone_required, false) ."' name='is_phone_required' id='is_phone_required'/>
						</div>
					</div>
				</div>
			</div>";

		return $html;
	}
	static function save_admin_edit($obj)
	{
		$is_scroll_zoom = empty($_POST['is_scroll_zoom']) ? 0 : 1;
		$is_drag        = empty($_POST['is_drag']) ? 0 : 1;

		$fields = array(
			"map_type"			=> empty($_POST['map_type']) ? '' : $_POST['map_type'],
			"latitude"			=> sanitize_text_field($_POST['latitude']),
			"longitude"			=> sanitize_text_field($_POST['longitude']),
			"zoom"				=> sanitize_text_field($_POST['zoom']),
			"is_legend"			=> empty($_POST['is_legend']) ? 0 : 1,
			"is_filtered"		=> empty($_POST['is_filtered']) ? 0 : 1,
			"is_csv"			=> empty($_POST['is_csv']) ? 0 : 1,
			"is_title"			=> empty($_POST['is_title']) ? 0 : 1,

			"is_lock"			=> empty($_POST['is_lock']) ? 0 : 1,
			"is_scroll_zoom"	=> $is_scroll_zoom,
			"is_drag"			=> $is_drag,

			"is_clustered"		=> empty($_POST['is_clustered']) ? 0 : 1,
			"is_search"			=> empty($_POST['is_search']) ? 0 : 1,
			"is_zoomer"			=> empty($_POST['is_zoomer']) ? 0 : 1,
			"is_layer_switcher"	=> empty($_POST['is_layer_switcher']) ? 0 : 1,
			"is_fullscreen"		=> empty($_POST['is_fullscreen']) ? 0 : 1,
			"default_icon_id"	=> sanitize_text_field($_POST['default_icon_id']),
			"width"				=> sanitize_text_field($_POST['width']),
			"height"			=> sanitize_text_field($_POST['height']),

			'highlight_country' => sanitize_text_field( isset( $_POST['highlight_country'] ) ? $_POST['highlight_country'] : '' ),
			'overlay_color'     => sanitize_hex_color( isset( $_POST['overlay_color'] ) ? $_POST['overlay_color'] : '' ),
			'border_color'      => sanitize_hex_color( isset( $_POST['border_color'] ) ? $_POST['border_color'] : '' ),
			'overlay_opacity'   => sanitize_text_field( isset( $_POST['overlay_opacity'] ) ? $_POST['overlay_opacity'] : '' ),
			'_shm_kml_url'      => esc_url_raw( isset( $_POST['shm_kml_url'] ) ? $_POST['shm_kml_url'] : '' ),

			"is_form"			=> empty($_POST['is_form']) ? 0 : 1,
			"form_title"		=> sanitize_text_field($_POST['form_title']),
			"form_contents"		=> sanitize_textarea_field(empty($_POST['form_contents']) ? '' : $_POST['form_contents']),
			"notify_owner"		=> empty($_POST['notify_owner']) ? 0 : 1,
			"form_forms"		=> empty($_POST['form_forms']) ? '' : $_POST['form_forms'],
			"is_personal_data"	=> sanitize_text_field(empty($_POST['is_personal_data']) ? '' : $_POST['is_personal_data']),
			"is_name_iclude"	=> sanitize_text_field(empty($_POST['is_name_iclude']) ? '' : $_POST['is_name_iclude']),
			"personal_name"		=> sanitize_text_field(empty($_POST['personal_name']) ? '' : $_POST['personal_name']),
			"is_name_required"	=> sanitize_text_field(empty($_POST['is_name_required']) ? '' : $_POST['is_name_required']),
			"is_email_iclude"	=> sanitize_text_field(empty($_POST['is_email_iclude']) ? '' : $_POST['is_email_iclude']),
			"personal_email"	=> sanitize_text_field(empty($_POST['personal_email']) ? '' : $_POST['personal_email']),
			"is_email_required"	=> sanitize_text_field(empty($_POST['is_email_required']) ? '' : $_POST['is_email_required']),
			"is_phone_iclude"	=> sanitize_text_field(empty($_POST['is_phone_iclude']) ? '' : $_POST['is_phone_iclude']),
			"personal_phone"	=> sanitize_text_field(empty($_POST['personal_phone']) ? '' : $_POST['personal_phone']),
			"is_phone_required"	=> sanitize_text_field(empty($_POST['is_phone_required']) ? '' : $_POST['is_phone_required']),
		);

		if ( $is_scroll_zoom || $is_drag ) {
			$fields['is_lock'] = 0;
		}

		return $fields;
	}
	static function post_row_actions($actions, $post)
	{
		if($post->post_type !== static::get_type()) return $actions;
		$actions['doubled'] = "<a href class='shm_doubled' post_id='$post->ID'>".__("Double", SHMAPPER)."</a>";
		return $actions;
	}
	static function smc_before_doubled_post($metas, $smc_post)
	{
		
		if($smc_post->get("post_type") !== ShmPoint::get_type())	return $metas;
		
		$types 	= get_the_terms($smc_post->id, SHM_POINT_TYPE);
		$maps	= $smc_post->get_owners();
		$metas['type'] 		= $types[0]->term_id;
		$metas['map_id'] 	= $maps[0]->ID;
		return $metas;
	}
	static function smc_after_doubled_post($new_smc_post, $origin_smc_post)
	{
		global $wpdb;
		if($origin_smc_post->get("post_type") !== static::get_type()) return;
		$old_points		= $origin_smc_post->get_points();
		
		foreach($old_points as $point)
		{
			$p = ShmPoint::get_instance($point);
			$new_point = $p->doubled();
			$new_point->remove_from_map($origin_smc_post->id);
			$new_point->add_to_map($new_smc_post->id);
		}
	}
	static function formEditor($data)
	{
		$html 	= "
		<div style='display:block;border:#888 1px solid;padding:0px;' id='form_editor'>
			<ul class='shm-card'>";
		$i 		= 0;
		foreach($data as $dat)
		{
			$html .= ShmForm::get_admin_element( $i, $dat );
			$i++;
		}
		$html .= ShmForm::wp_params_radio( -1, -1 ) . "
			</ul>
		</div>";
		return $html;
		
	}
	function get_include_types()
	{
		$form_forms = $this->get_meta("form_forms");
		
		if($form_forms) {
			foreach($form_forms as $element)
			{
				if( $element['type'] == 8 )
				{
					return explode(",", $element["placemarks"]);
				}
			}
		}
		
		return false;
	}
	function get_csv()
	{

		$upload_dir = wp_upload_dir();
		if(
			!file_exists($upload_dir['basedir']."/shmapper-by-teplitsa")
			&& !wp_mkdir_p($upload_dir['basedir']."/shmapper-by-teplitsa")
		) {
			echo '<pre>' . esc_html( print_r( 'FAIL', 1 ) ) . '</pre>';
			return false;
		}

		$points		= $this->get_points();
		$csv 		= [implode(SHM_CSV_STROKE_SEPARATOR, [ "#", __("Title", SHMAPPER), __("Description", SHMAPPER),  __("Location", SHMAPPER),  __("Longitude", SHMAPPER),  __("Latitude", SHMAPPER) ])];
		$i = 0;
		foreach($points as $point)
		{
			$p 		= ShmPoint::get_instance($point);
			$csv[]	= implode(SHM_CSV_STROKE_SEPARATOR, [
				($i++) . ". ",
				'"' . str_replace(';', ",", $p->get("post_title") )	  . '"', 
				'"' . str_replace(';', ",", wp_strip_all_tags( wp_specialchars_decode ($p->get("post_content"))))  . '"',
				'"' . str_replace(';', ",", $p->get_meta("location")) . '"',
				'"' . str_replace(';', ",", $p->get_meta("longitude")). '"',
				'"' . str_replace(';', ",", $p->get_meta("latitude")) . '"',
			]);
		}
		$csv_data 	= iconv ("UTF-8", "cp1251", implode( SHM_CSV_ROW_SEPARATOR, $csv));
		$path 		= $upload_dir['basedir'] . "/shmapper-by-teplitsa/shmap_" . $p->id . ".csv";
		$href		= $upload_dir['baseurl'] . "/shmapper-by-teplitsa/shmap_" . $p->id . ".csv";
		file_put_contents( $path, $csv_data );		
		return $href;

		if(class_exists("ZipArchive"))
		{
			$zip 		= new ZipArchive();
			$zip_name	= "shmap_" . $p->id . ".zip";
			if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
			{
				return $href;
			}
			$zip->addFile( $path );
			$zip->close();
			if(file_exists($zip_name))
				return $upload_dir['basedir'] . "/shmapper-by-teplitsa/" . $zip_name;
			else
				return $href;
		}
		else
			return $href;
	}
	function get_points_args()
	{
		global $wpdb;
		$points_ids = $wpdb->get_col( "SELECT point_id FROM {$wpdb->prefix}point_map WHERE map_id = $this->id" );
		return $points_ids;
	}
	function get_points()
	{
		$points = array();
		if ( $this->get_points_args() ) {
			$args = array(
				'post_type'   => SHM_POINT,
				'post_status' => 'publish',
				'numberposts' => -1,
				'post__in'    => $this->get_points_args(),
			);
			$points = get_posts( $args );
		}
		return $points;
	}
	
	function get_point_count()
	{
		$point_count = count( (array) $this->get_points_args() );
		return $point_count;
	}
	function get_delete_form( $href )
	{
		$html ="
		<div class='shm-row' shm_delete_map_id='" . $this->id . "' >
			<div class='shm-12 small shm-color-grey'>" . 
				__("What do with placemarks of deleting Map?", SHMAPPER) .
			"</div>
			<div class='shm-12'>
				<div class='spacer-10'></div>
				<input type='radio' class='radio' id='dd1' value='1'  name='shm_esc_points' checked />
				<label for='dd1'>" . __("Delete all Points", SHMAPPER) . "</label>
				<div class='spacer-10'></div>
			</div>
			<div class='shm-12'>
				<div class='spacer-10'></div>
				<input type='radio' class='radio' id='dd2' value='2' name='shm_esc_points' />
				<label for='dd2'>" . __("Escape all Points without Owner Map", SHMAPPER) . "</label>
				<div class='spacer-10'></div>
			</div>
			<div class='shm-12'>
				<div class='spacer-10'></div>
				<input type='radio' class='radio' id='dd3' value='3'  name='shm_esc_points' />
				<label for='dd3'>" . __("Switch all Points to anover Map", SHMAPPER) . "</label>
				<div class='spacer-10'></div>" .
					ShmMap::wp_dropdown([
						"class"		=> "shm-form",
						"id"		=> "shm_esc_points_id",
						"style"		=> "display:none;",
						"posts"     => ShmMap::get_all(),
						"exclude_post_id"   => $this->id,
					]) .
				"<div class='spacer-10'></div>
			</div>
		</div>
		<!--div class='shm-row'>
			<div class='shm-12'>
				<div class='spacer-10'></div>
				<a class='button' href='$href'>" . esc_html__( 'delete', 'shmapper-by-teplitsa' ) . "</a>
				<div class='spacer-10'></div>
			</div>
		</div-->";
		return $html;
	}
	function get_map_points()
	{
		$points = $this->get_points();
		$p = [];
		
		foreach($points as $point)
		{
			$pn = ShmPoint::get_instance($point);
			$types	= wp_get_object_terms($pn->id, SHM_POINT_TYPE);
			$type	= count( $types ) ? $types[0] : null;
			$pnt 	= new StdClass;
			$pnt->ID			= $pn->id;
			$pnt->post_title	= $pn->get("post_title");
			$pnt->post_content  = wpautop( $pn->get("post_content") );
			$pnt->latitude 		= $pn->get_meta("latitude");
			$pnt->longitude 	= $pn->get_meta("longitude");
			$pnt->location 		= $pn->get_meta("location");
			$pnt->color 		= $type ? get_term_meta($type->term_id, "color", true) : "";
			$pnt->height 		= $type ? get_term_meta($type->term_id, "height", true): 40;
			$pnt->height 		= $pnt->height 	? $pnt->height 	: 30;
			$pnt->width 		= $type ? get_term_meta($type->term_id, "width", true): 40;
			$pnt->width 		= $pnt->width 	? $pnt->width 	: 30;
			$pnt->type 			= $type ? $type->name : "";
			$pnt->term_id 		= $type ? $type->term_id: -1;

			$pnt_icon = '';
			if ( $type ) {
				$icon_id = get_term_meta( $type->term_id, 'icon', true );

				if ( $icon_id ) {
					$pnt_icon = wp_get_attachment_image_url( $icon_id, 'medium_large');
					$icon_src = wp_get_attachment_image_src( $icon_id, 'medium_large');
					$icon_width  = $icon_src['1'];
					$icon_height = $icon_src['2'];

					if ( ! get_term_meta( $type->term_id, 'height', true) || ! get_term_meta( $type->term_id, 'width', true) ) {
						if ( $icon_width > $icon_height ) {
							$icon_scale  = $icon_width / 40;
							$icon_width  = 40;
							$icon_height = $icon_height / $icon_scale;
						} else {
							$icon_scale  = $icon_height / 40;
							$icon_height = 40;
							$icon_width  = $icon_width / $icon_scale;
							
						}
						$pnt->width  = $icon_width;
						$pnt->height = $icon_height;
					}
				}
			}

			$pnt->icon = $pnt_icon;
			//$pnt->width 		= ShMapPointType::get_icon_src( $type->term_id )[2]/ShMapPointType::get_icon_src( $type->term_id )[1] * $pnt->height ;
			//$pnt->width 		= $pnt->width ? $pnt->width : $pnt->height;
			$p[] 	= $pnt;
		}
		return $p;
	}
	function draw($args=-1)
	{
		if(!is_array($args)) $args = [ "height" => 450, "id" => $this->id, "is_title" => 'true' ];
		require_once(SHM_REAL_PATH . "tpl/shmMap.php");
		return draw_shMap($this, $args);
	}
	
	/*
		final delete map and difference placemarks migration
	*/
	function shm_delete_map_hndl($data)
	{
		global $wpdb;
		$points = $this->get_points();
		switch($data['action'])
		{
			case 1:
				// search once usage points in deleted Map (only_once == 1)
				$query = "SELECT DISTINCT( p1.point_id ) AS point, COUNT(p1.map_id)=1 AS only_once, GROUP_CONCAT(p1.map_id) AS maps
				FROM " . $wpdb->prefix . "point_map AS p1
				LEFT JOIN " . $wpdb->prefix . "point_map AS p2 ON p1.point_id=p2.point_id
				WHERE p2.map_id=".$this->id."
				GROUP BY p1.point_id";
				$res = $wpdb->get_results($query);
				$i = 0;
				foreach($res as $point)
				{
					if($point->only_once == 1)
					{
						ShmPoint::delete($point->point);
						$i++;
					}
				}
				$message = sprintf(__("Succesfuly delete map width %s points", SHMAPPER), $i );
				break;
			case 2:
				$count = $wpdb->get_var("SELECT COUNT(point_id) FROM ".$wpdb->prefix."point_map WHERE map_id=".$this->id);	$query = "DELETE FROM " . $wpdb->prefix . "point_map WHERE map_id=".$this->id;
				$res = $wpdb->query($query);
				$message = sprintf(__("Succesfuly delete map and %s points are orphans now", SHMAPPER), $count );
				break;
			case 3:
				$count = $wpdb->get_var("SELECT COUNT(point_id) FROM ".$wpdb->prefix."point_map WHERE map_id=".$this->id);
				$query = "UPDATE " . $wpdb->prefix . "point_map SET map_id=".((int)sanitize_text_field($data['anover'])). " WHERE map_id=".$this->id;
				$res = $wpdb->query($query);
				$map2 = static::get_instance(sanitize_text_field($data['anover']));
				$message = sprintf(__("Succesfuly delete map and %s points migrates to %s", SHMAPPER), $count, $map2->get("post_title") );
				break;
		}
		static::delete( $this->id );
		return ["query" => $query, "res" => $res, "message" => $message];
	}
	static function the_content($content)
	{
		global $post;
		$t = '';
		if ( $post ) {
			$t = ($post->post_type == SHM_MAP && (is_single() || is_archive() )) ? '[shmMap id="' . esc_attr( $post->ID ) . '" map form ]'  : "";
		}
		return $t . $content;
	}
	static function get_type_radio($params=-1)
	{
		if(!is_array($params))
		{
			$params = [ 
				"id" 		=> "map_radio", 
				"name" 		=> "type_radio[" . ShMapper::$options['map_api'] . "][]", 
				'selected' 	=> [] 
			];
		}
		$html 	= "
		<div class='shm_type_radio shm-row'>
			<div class='shm-12'>";
		
		if( ShMapper::$options['map_api'] != 2 )
		{
			$html .= "<div class='shm-admin-block'>
					<h3 class='shm-admin-layers-heading'>" . esc_html__( 'Yandex Map', 'shmapper-by-teplitsa' ) . "</h3>";
			$i 		= 0;

			foreach(static::get_map_types()[ 1 ] as $type)
			{
				$selected = !empty($params['selected']) && !empty($params['selected'][1][0]) && $params[ 'selected' ][1][0] == $type ? " checked " : "";
				$name 	= $params[ 'name' ];
				$id 	= $params[ 'id' ];
				$html 	.= "
				<div>
					<input type='radio' name='".$name."[1][]' id='$id$i' $selected value='$type'/> 
					<label for='$id$i'>" . $type . "</label>
				</div>";
				$i++;
			}

			$html .= "
					</div>";
		}
		else
		{
			$html .= "<div class='shm-admin-block'>
					<h3 class='shm-admin-layers-heading'>" . esc_html__( 'Open Street Map', 'shmapper-by-teplitsa' ) . "</h3>";

			$i 		= 0;
			
			foreach(static::get_map_types()[ 2 ] as $type)
			{
				$selected = isset($params[ 'selected' ][2][0]) && $params[ 'selected' ][2][0] == $type  ? " checked " : "";
				$name 	= $params[ 'name' ];
				$id 	= $params[ 'id' ];
				$html 	.= "
				<div>
					<input type='radio' name='".$name."[2][]' id='$id$i' $selected value='$type'/> 
					<label for='$id$i'>" . $type . "</label>
				</div>";
				$i++;
			}
			
			$html .= "
					</div>";
		}
		$html .= "
			</div>
		</div>";
		return $html;
	}
}
