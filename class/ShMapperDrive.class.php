<?php
class ShMapperDrive
{
	public static function activate()
	{
		global $wpdb;
		init_textdomain_shmapper();
		$options = get_option(SHMAPPERD);
		if(!is_array($options))
		{
			$options = [];
		}
		update_option(SHMAPPERD, $options);
	}
	
	public static function deactivate()
	{
		
	}
	static $options;
	static $instance;
	static function get_instance()
	{
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}
	static function update_options()
	{
		update_option( SHMAPPERD, static::$options );
		static::$options = get_option(SHMAPPERD);
	}
	function __construct()
	{	
		static::$options = get_option(SHMAPPERD);

		$defaults = array(
			'skip_first_rows',
			'is_google_post_date',
			'point_type',
			'google_geo_lat',
			'google_geo_lon',
			'is_google_point_type',
			'google_unique',
		);

		foreach ( $defaults as $default ) {
			if ( ! isset( static::$options[ $default ] ) ) {
				static::$options[ $default ] = '';
			}
		}

		add_action( "init", 						[__CLASS__, "add_shortcodes"], 82);
		add_action( 'wp_enqueue_scripts', 			[__CLASS__, 'add_frons_js_script'], 99 );
		add_action( 'admin_enqueue_scripts', 		[__CLASS__, 'add_admin_js_script'], 99 );
		add_filter("shmapper_admin",				[__CLASS__, "shmapper_admin"]);
		add_filter( "smc_add_post_types",	 		[__CLASS__, "init_obj"], 15);
	}
	
	static function add_shortcodes()
	{		
		require_once( SHM_REAL_PATH . 'shortcode/shmMapFeed.shortcode.php' );
		add_shortcode( 'shmMapFeed',		'shmMapFeed'); 
	}
	
	
	static function add_admin_js_script()
	{	
		//css
		wp_register_style("ShMapperDrive", SHM_URLPATH . 'assets/css/ShMapperDrive.css', array() );
		wp_enqueue_style( "ShMapperDrive");
		//js
		wp_register_script("ShMapperDrive.admin", plugins_url( '../assets/js/ShMapperDrive.admin.js', __FILE__ ), array());
		wp_enqueue_script("ShMapperDrive.admin");
	}
	static function add_frons_js_script()
	{	
		
		//css
		wp_register_style("ShMapperDrive", SHM_URLPATH . 'assets/css/ShMapperDrive.css', array());
		wp_enqueue_style( "ShMapperDrive");
		
		wp_register_script("ShMapperDrive.front", plugins_url( '../assets/js/ShMapperDrive.front.js', __FILE__ ), array());
		wp_enqueue_script("ShMapperDrive.front");
	}
	
	
	
	static function init_obj($init_object)
	{
		$point				= [];
		$point['t']			= ['type' => 'post'];	
		$point[SHM_POINT]	= [
			'type' => 'post', 
			"object" => SHM_POINT,
			"class" => "ShmPoint", 
			"color"=> "#5880a2", 
			"name" => __("Point", SHMAPPER)
		];
		$point['google_table_id']		= ['type'=>'string', "name" => __("Google Table ID source", SHMAPPER )];
		$init_object[SHMAPPER_POINT_MESSAGE] = $point;
		
		$init_object[SHM_POINT]['google_table_id']	= [
			'type'=>'string', 
			"name" => __("Google Table ID source", SHMAPPER )
		];		
		return $init_object;
	}
	
	static function shmapper_admin( $text ) {
		$google_geo_adress = static::$options['google_geo_adress'] ? static::$options['google_geo_adress'] : 'D';
		$shmd_post_title   = static::$options['shmd_post_title'] ? static::$options['shmd_post_title'] : 'B';
		$shmd_post_date    = static::$options['post_date'] ? static::$options['post_date'] : 'E';
		$shmd_post_desc    = static::$options['shmd_post_desc'] ? static::$options['shmd_post_desc'] : 'F';

		return $text . "
				<li>
					<div class='shm-row' id='shm_vocabulary_cont'>
						<div class='shm-2 shm-color-grey sh-right sh-align-middle shm-title-4'>" .
							__("Export from Google Sheet", SHMAPPER ) .
						"</div>
						<div class='shm-9'>
							<div class='callout-danger mb-2'>
								<div class='shm-title'>".
									__("Attantion", SHMAPPER).
								"</div>
								<div>".
									__("Rules for Google snapshot structure.", SHMAPPER).
									"<ol>
										<li> " . __("Google-table must have access rights 'For everyone on the Internet' or 'For everyone who has a link'", SHMAPPER). "</lo>
										<li> " . __("In file must be only one screen.", SHMAPPER). "</lo>
										<li>" . __("First row must have only legends of columns.", SHMAPPER). "</lo>
										<li>" . __("If Points are have different Point types you must reserve one column for Point type and fill it ID of this types.", SHMAPPER). "</lo>
										<li>" . __("If you want to have geo position you must reserve one column for adress or 2 columns for longitude and latitude.", SHMAPPER). "</lo>
									</ol>
								</div>
							</div>
							<div>
								<small class='shm-color-grey '>".
									__("Google table document ID. For example: <b class='shm-color-danger'>1dQupQpiGjPqIbVHCTRvpybr-cmk5zs8U</b> in https://docs.google.com/spreadsheets/d/1dQupQpiGjPqIbVHCTRvpybr-cmk5zs8U/edit#gid=7101094", SHMAPPER) .
								"</small>
							</div>
							<div class='shm-row'>
								<div class='shm-12 d-flex'>
									<input 
										type='text' 
										class='shm-form' 
										name='google_table_id' 
										value='" . static::$options['google_table_id'] . "'
									/>
									<div class='button-2 button ml-1 button-reload' id='shm-google-reload' >
										<span class='dashicons dashicons-update'></span>
									</div>
								</div>
							</div>
							<div id='shm_google_params' >
								<div>
									<small class='shm-color-grey mt-2'>".
										__("Skip first rows count", SHMAPPER) .
									"</small>
								</div>
								<input 
									type='number' 
									class='shm-form shm_options' 
									name='skip_first_rows' 
									value='" . static::$options['skip_first_rows'] . "'
								/>
								
								<div>
									<small class='shm-color-grey mt-2'>".
										__("Execute next rows count. Empty for all.", SHMAPPER) .
									"</small>
								</div>
								<input type='number' class='shm-form shm_options' name='exec_rows' value=''/>
								
								<div>
									<small class='shm-color-grey mt-2'>".
										__("Map", SHMAPPER) .
									"</small>
								</div>" .
									ShmMap::wp_dropdown([
										"class"		=> "shm-form shm_options",
										"name"		=> "map_id",
										"selected"	=> static::$options['map_id'],
										"posts"		=> ShmMap::get_all( )
									]) .
								"

								<div>
									<small class='shm-color-grey mb-2'>".
										__("Column for unique id", SHMAPPER) .
									"</small>
								</div>" .
									googleColumnIdent_dropdown( [
										'name' 		=> "google_unique", 
										"class" 	=> "shm_options", 
										"selected" 	=> static::$options['google_unique']
									] ) . 
								"

								<div>
									<small class='shm-color-grey mt-2'>".
										__("Point title column", SHMAPPER) .
									"</small>
								</div>" .
								googleColumnIdent_dropdown( [
									'name'		=> 'shmd_post_title', 
									"class" 	=> "shm_options", 
									"selected" 	=> $shmd_post_title
								]). 
								"
								<div>
									<small class='shm-color-grey mt-2'>".
										__("Point description column", SHMAPPER) .
									"</small>
								</div>" .
								googleColumnIdent_dropdown( [
									'name'		=> 'shmd_post_desc', 
									"class" 	=> "shm_options", 
									"selected" 	=> $shmd_post_desc
								]) .
								"
								<div>
									<small class='shm-color-grey mt-2'>".
										__("Post date column", SHMAPPER) .
									"</small>
								</div>
								<div>
									<input 
										type='checkbox' 
										class='shm_options'
										id='is_google_post_date' 
										name='is_google_post_date' 
										value='1' ". 
										checked("1", static::$options['is_google_post_date'], 0).
									"/>
									<label for='is_google_post_date'>".
										__("Is fix post date?", SHMAPPER).
									"</label>
								</div>" .
								googleColumnIdent_dropdown( [
									'name'		=> 'post_date',
									"class" 	=> "shm_options",
									"selected" 	=> $shmd_post_date
								]). 
								"

								<div class=''>
									<small class='shm-color-grey mt-2'>".
										__("Select marker type", SHMAPPER).
									" <a href='" . admin_url( 'edit-tags.php?taxonomy=shm_point_type' ) . "'>".
										__("Add new marker", SHMAPPER).
									"</a> </small> 
									".
									ShMapPointType::get_ganre_swicher([
										'selected' 	=> static::$options['point_type'],
										'prefix'	=> "point_type",
										"class"		=> "shm_options",
										'col_width'	=> 6,
										"default_none" => false,
									], 'radio' ).
									"".
								"</div>							
								
								<div>
									<div class='spacer-10'></div>
									<small class='shm-color-grey mt-2'>".
										__("Select method and columns for generate Points's geo position.", SHMAPPER) .
									"</small>
									<div class='shm-row'>
										<div class='shm-4' >
											<input 
												type='radio' 
												class='shm_options'
												id='google_geo_position0' 
												name='google_geo_position' 
												value='0' ". 
												checked("0", static::$options['google_geo_position'], 0).
											"/>
											<label for='google_geo_position0'>".
												__("Latitude and Longitude", SHMAPPER).
											"</label>
										</div>
										<div class='shm-4' >".
											googleColumnIdent_dropdown( [
												'name'		=> 'google_geo_lat', 
												"class" 	=> "shm_options", 
												"selected" 	=> static::$options['google_geo_lat']
											]).
										"</div>
										<div class='shm-4' >".
											googleColumnIdent_dropdown( [
												'name'=>'google_geo_lon', 
												"class" => "shm_options", 
												"selected" => static::$options['google_geo_lon']
											]).
										"</div>
									</div>
									<div class='shm-row' >
										<div class='shm-4' >
											<input 
												type='radio' 
												class='shm_options'
												id='google_geo_position1' 
												name='google_geo_position' 
												value='1' ". 
												checked("1", static::$options['google_geo_position'], 0).
											"/>
											<label for='google_geo_position1'>".
												__("Adress", SHMAPPER).
											"</label>
										</div>
										<div class='shm-8' >".
											googleColumnIdent_dropdown([
												'name'     =>'google_geo_adress',
												"class"    => "shm_options", 
												"selected" => $google_geo_adress
											]).
										"</div>
									</div> 
								</div> 
								
								<div>
									<div class='spacer-10'></div>
									<small class='shm-color-grey mt-2'>".
										__("Select columns for choose Points's different types.", SHMAPPER) .
									"</small>
									<div class='shm-row'>
										<div class='shm-12' >
											<input 
												type='checkbox' 
												class='shm_options'
												id='is_google_point_type' 
												name='is_google_point_type' 
												value='1' ". 
												checked("1", static::$options['is_google_point_type'], 0).
											"/>
											<label for='is_google_point_type'>".
												__("Select column with marker type", SHMAPPER). // ion
											"</label>
										</div>
									</div>
									<div class='shm-row' > 
										<div class='shm-12' >".
											googleColumnIdent_dropdown([
												'name'=>'google_point_type',
												"class" => "shm_options", 
												"selected" => static::$options['google_point_type']
											]).
										"</div>
									</div> 
								</div> 
								<div class='hidden'> 
									<small class='shm-color-grey mb-2'>".
										__("List of columns in google table that need to parse to Point's description", SHMAPPER) .
									"</small>
								</div>
								<div class='shm-row hidden'>
									<div class='shm-1'>".
										__("Column", SHMAPPER) . 
									"</div>
									<div class='shm-1 border-left '>".
										__("Include?", SHMAPPER) .
										" <div class='shm-color-danger shm-title-2' title='". __("necessarily", SHMAPPER) ."'>
											*
										</div>" .
									"</div>
									<div class='shm-3 border-left'>".
										__("Field name by latin", SHMAPPER) .
										" <div class='shm-color-danger shm-title-2' title='". __("necessarily", SHMAPPER) ."'>
											*
										</div>".
									"</div>
									<div class='shm-5 border-left'>".
										__("Sub title for decription section", SHMAPPER) . 
									"</div>
									<div class='shm-2 border-left'>".
										__("Order", SHMAPPER) . 
									"</div>
								</div>
								<div class='hidden'>".							
									getGoogleRow(["n" => 0, 'include'=> 0, "id" => "google_null" ]) . 			
								"</div>
								<div id='google_row hidden'></div>

								<div class='spacer-10'></div>

								<div>
									<small class='shm-color-grey mb-2'>".
										__("If your Google spreadsheet has one text in the specified column", SHMAPPER) .
									"</small>
									
									<div class=' my-2'>
										<input 
											type='radio' 
											id='doubled_0' 
											class='shm_options'
											name='shm_doubled' 
											value='0' ".
											checked(0, static::$options['shm_doubled'], 0).
										"/>
										<label for='doubled_0'>".
											__("Use only first row for creation new Point or updating included Point and ignore over.", SHMAPPER).
										"</label>
									</div>
									<!--div class=' mb-2'>
										<input 
											type='radio' 
											id='doubled_1' 
											class='shm_options'
											name='shm_doubled' 
											value='1'".
											checked(1, static::$options['shm_doubled'], 0).
										"/>
										<label for='doubled_1'>".
											__("Use only last row for creation new Point or updating included Point and ignore over.", SHMAPPER).
										"</label>
									</div-->
									<div class=' mb-2' style='margin-bottom:10px;'>
										<input 
											type='radio' 
											id='doubled_2'
											class='shm_options' 
											name='shm_doubled' 
											value='2' ".
											checked(2, static::$options['shm_doubled'], 0).
										"/>
										<label for='doubled_2'>".
											__("Use only first row for creation new Point or updating included Point. Over some rows use for creation new Message or updating included Messages for Point", SHMAPPER).
										"</label>
									</div>
								</div>
							</div>
								<div 
									class='my-2 ".(static::$options['google_table_id'] != "" ? "" : " hidden ")."' 
									id='shmd_settings_wizzard' 
								>
									<div class='button' id='shmd_settings_open'>" . 
										__( "Settings" ) . 
									"</div>
									<div class='button' id='shmd_google_preview'>" . 
										__("Preview results", SHMAPPER) . 
									"</div>	
									<div class='button' id='shmd_google_update'>" . 
										__("Create or update Poins and Messages", SHMAPPER) .
									"</div>
									<span class='dashicons dashicons-update shmd-loader hidden'></span>
								</div>	
						</div>	
						<div class='shm-1'>
							
						</div>	
					</div>			
				</li>
		";
	}
}
function getGoogleRow($params)
{
	if ( ! isset( $params['title'] ) ) {
		$params['title'] = '';
	}
	return "
		<div class='shm-row ' id='".$params['id']."' shmd_google_row='".$params['n']."'>
			<div class='shm-1' nid='google-id'>".
				getSingleGoogleIdenter($params['n']).
				//googleColumnIdent_dropdown( ['name'=> 'ident', "selected" =>  getSingleGoogleIdenter($params['n']) ] ) .
			"</div>
			<div class='shm-1 border-left ' nid='google-include'>
				<input 
					type='checkbox' 
					class='checkbox shmd_row_check'
					name='include' 
					id='google_include' 
					value='1' " . 
					checked(1, $params['include'], 0) .
				"/>
				<label for='google_include'></label>
			</div>
			<div class='shm-3 border-left'  nid='google-meta'>
				<input type='text' class='shm-form shmd_row_input' name='meta' value='".getSingleGoogleIdenter($params['n'])."'/>
			</div>
			<div class='shm-5 border-left' nid='google-title'>
				<input type='text' class='shm-form shmd_row_input' name='title' value='".$params['title']."'/>
			</div>
			<div class='shm-2 border-left'  nid='google-order'>
				<input type='number' class='shm-form shmd_row_input' name='order' value='".$params['n']."'/>
			</div>
			
		</div>
	";
}
function getGoogleIdenters()
{
	return [ "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ"];
}
function getSingleGoogleIdenter($order)
{
	$a = getGoogleIdenters();
	return $a[ (int)$order ];
}
function getSingleGoogleOrder($identer)
{
	$a = getGoogleIdenters();
	$i = 0;
	foreach($a as $b)	
	{
		if($b == $identer)
			return $i;
		$i++;
	}
	return 0;
	
}
function googleColumnIdent_dropdown( $params )
{
	$a = getGoogleIdenters();
	$html = "<select class='shm-form ".$params['class']."' name='".$params['name']."'>";
	foreach($a as $b)
	{
		$html .= "<option value='$b' ".selected($b, $params['selected'], 0) . ">
			$b
		</options>";
		
	}
	$html .= "</select>";
	return $html;
}