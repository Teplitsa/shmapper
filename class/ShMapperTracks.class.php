<?php 
class ShMapperTracks
{
    static function activate()
    {

    }
    static function deactivate()
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
		update_option( SHMAPPER_TRACKS, static::$options );
		static::$options = get_option(SHMAPPER_TRACKS);
	}
	function __construct()
	{	
		static::$options = get_option(SHMAPPER_TRACKS); 
		 
		add_action( "init", 						[__CLASS__, "add_shortcodes"], 90);
		add_filter( "smc_add_post_types",	 		[__CLASS__, "init_obj"], 11); 
		add_action( 'wp_enqueue_scripts', 			[__CLASS__, 'add_frons_js_script'], 99 );
		add_action( 'admin_enqueue_scripts', 		[__CLASS__, 'add_admin_js_script'], 99 ); 
		add_filter( "shm_voc",	 					[__CLASS__, "shm_voc"], 10, 1);
		add_filter( "shm_vocabulary",	 			[__CLASS__, "shm_vocabulary"], 10, 1);
		add_filter( "shm_shortcode_args",			[__CLASS__, "shm_shortcode_args"], 10, 2 );
		add_filter( "shm_after_front_map",			[__CLASS__, "shm_after_front_map"], 10, 2);
		//
		add_filter( 'upload_mimes', 				[__CLASS__, 'upload_allow_types'] );
	} 
	static function add_shortcodes()
	{		
		require_once(SHMTRACKS_REAL_PATH.'shortcode/shmMapTrack.shortcode.php');
		add_shortcode('shmMapTrack', 'shmMapTrack'); 
	}
	static function upload_allow_types( $mimes )
	{
		ShMaperTrack::get_dump($mimes);
		$mimes['gpx']  			= "application/xml";
		$mimes['application']  	= "application/octet-stream";
		return $mimes;
	}
	static function shm_shortcode_args($arr, $args)
	{ 
		
		$arr["track_list"] = $args["track_list"];
		if( (isset($args["track_list"]) && $args["track_list"] == "1") && (!isset($args["map"]) && !isset($args["form"]) ))
		{
			$arr["map"]  = false;
			$arr["form"] = false;
		}
		return $arr;
	}
	static function shm_after_front_map($html, $args)
	{
		$tracksList_enb	= $args["track_list"] || ( !$args["map"] && !$args["form"] && !isset($args["track_list"])) ? 1 : 0; 
		if($tracksList_enb)
		{
			$id		= $args['id'];
			$map	= ShmMap::get_instance($$id);
			$tracks = ShMaperTrack::get_all([SHM_MAP => $id]);
			$uniq 	= $args['uniq'] ? $args['uniq'] : substr( MD5(rand(0, 100000000)), 0, 8 );
			$tackList = "<div map_id='$id' form_id='ShmMap$id$uniq' >";
			foreach($tracks as $track)
			{
				$tackList .= "<div class='shm-justify-between shm-align-items-center shm-track-li'>
					<a href='#' class='shm-track-list-btn' track_id='" . $track->ID . "'>".
						$track->post_title . 
					"</a>
					<div class='shm-track-dnld-gpx-btn' shm-track-dnld-gpx='" . $track->ID . "'>".
						__("download gpx", SHMAPPER_TRACKS) . 
					"</div>
				</div>";
			}
			$tackList .= "</div>";
			$html .= "<div class='shm-padding-20'>
				<div class='shm-title-6 shm-map-title'>".
					ShMapper::$options[ "list_of_tracks" ].
				"</div>
				$tackList
			</div>";
		}
		return $html;
	}
	static function shm_voc($arr)
	{
		$arr = array_merge(
			$arr,
			[
				'Start draw new Track' => __( "Start draw new Track", SHMAPPER_TRACKS ),
				'Uncorrect gpx-file: ' => __( "Uncorrect gpx-file: ", SHMAPPER_TRACKS ),
				'Not correct gpx format' => __( "Not correct gpx format", SHMAPPER_TRACKS ),
				'Not exists track data' => __( "Not exists track data", SHMAPPER_TRACKS ),
				"Not exists correct track's segment data" => __( "Not exists correct track's segment data", SHMAPPER_TRACKS ),
				"Not exists correct track segment's data" => __( "Not exists correct track segment's data", SHMAPPER_TRACKS ),
				"Set range fliping of route's dots" => __( "Set range fliping of route's dots", SHMAPPER_TRACKS ),
				"edit" => __( "edit", SHMAPPER_TRACKS ),
				"update" => __( "update", SHMAPPER_TRACKS ),
				"List of Tracks" => __( "List of Tracks", SHMAPPER_TRACKS ),
				"Add marker" => __( "Add marker", SHMAPPER_TRACKS ),
				"Empty vertex" => __( "Empty vertex", SHMAPPER_TRACKS ),
				"Edit vertex" => __( "Edit vertex", SHMAPPER_TRACKS ),
				"Update vertex" => __( "Update vertex", SHMAPPER_TRACKS ),
				"Title" => __( "Title", "shmapper-by-teplitsa" ),
				"Content" => __( "Content", "shmapper-by-teplitsa" ),
				"Type" => __( "Type", "shmapper-by-teplitsa" ),
				"Update new track" => __( "Update new track", SHMAPPER_TRACKS ),
			]
		);
		return $arr;
	}
	static function shm_vocabulary($arr)
	{
		$arr = array_merge(
			$arr,
			[
				"list_of_tracks" => __( "List of Tracks", SHMAPPER_TRACKS ),  
			]
		);
		return $arr;
	}
	static function init_obj($init_object)
	{
		if(!is_array($init_object)) $init_object = [];
		$point						= [];
		$point['t']					= ['type' => 'post'];	
		$point['class']				= ['type' => 'ShMaperTrack'];
		$point['gpx']				= ['type' => 'gpx', 	"name" => __("GPX source", SHMAPPER_TRACKS), 			"thread" => false ]; 
		$point['track']				= ['type' => 'track', 	"name" => __("Track", SHMAPPER_TRACKS),					"thread" => true ]; 
		$point['shm_author']		= ['type' => 'string', 	"name" => __("Track author name", SHMAPPER_TRACKS), 	"thread" => true ]; 
		$point['shm_author_email']	= ['type' => 'string', 	"name" => __("Track author e-mail", SHMAPPER_TRACKS), 	"thread" => false ]; 
		$point[SHM_MAP]				= ['type' => 'id', 		"name" => __("Map", SHMAPPER),							"object" => "post"];
		$point[SHM_TRACK_TYPE]		= ['type' => 'id', 		"name" => __("Type", SHMAPPER),							"object" => "taxonomy"];
		$init_object[SHMAPPER_TRACKS_TRACK]		= $point;
		
		$marker								= [];
		$marker['t']						= ['type' => 'post'];
		$marker['class']					= ['type' => 'ShMapperTracksPoint'];	
		$marker[SHMAPPER_TRACKS_TRACK] 		= ['type' => 'id', 		"name" => __("Track", SHMAPPER_TRACKS),	"object" => "post"];
		$marker[SHM_POINT_TYPE]				= ['type' => 'id', 		"name" => __("Type", SHMAPPER),			"object" => "taxonomy"];
		$marker['location']					= ['type' => 'string', "name" => __("Location", SHMAPPER)];
		$marker['latitude']					= ['type'=>'string', "name" => __("Latitude", SHMAPPER)];
		$marker['longitude']				= ['type'=>'string', "name" => __("Longitude", SHMAPPER)];
		$init_object[SHMAPPER_TRACKS_POINT]	= $marker;
		
		
		$point						= [];
		$point['t']					= ['type' => 'taxonomy'];	
		$point['class']				= ['type' => 'ShMapTrackType']; 
		$point['color']				= ['type' => 'color', "name" => __("Color", SHMAPPER)];	
		$init_object[ SHM_TRACK_TYPE ]		= $point;
		
		return $init_object;
		
	}
	
	static function add_frons_js_script()
	{
		//css
		wp_register_style(SHMAPPER_TRACKS, SHM_URLPATH . 'assets/css/ShmapperTracks.css', array( 'dashicons' ), SHMAPPER_VERSION );
		wp_enqueue_style( SHMAPPER_TRACKS);
		wp_register_style("rangeSlider", SHM_URLPATH . 'assets/css/ion.rangeSlider.min.css', array( 'dashicons' ) );
		wp_enqueue_style( "rangeSlider");
		wp_register_script(SHMAPPER_TRACKS, SHM_URLPATH . 'assets/js/ShmapperTracks.js', array( 'jquery-ui-draggable', 'jquery-touch-punch'), SHMAPPER_VERSION);
		wp_enqueue_script(SHMAPPER_TRACKS);
		wp_register_script("rangeSlider", SHM_URLPATH . 'assets/js/ion.rangeSlider.min.js', array( ));
		wp_enqueue_script("rangeSlider");
		wp_localize_script(
			SHMAPPER_TRACKS,
			'shmapper_track',
			array(
				'url'         => SHM_URLPATH,
				'downloadGpx' => __( 'Download GPX', 'shmapper-by-teplitsa' )
			)
		);
	}
	static function add_admin_js_script()
	{
		//css
		wp_register_style(SHMAPPER_TRACKS, SHM_URLPATH . 'assets/css/ShmapperTracks.css', array( 'dashicons' ), SHMAPPER_VERSION );
		wp_enqueue_style( SHMAPPER_TRACKS);
		wp_register_style("rangeSlider", SHM_URLPATH . 'assets/css/ion.rangeSlider.min.css', array( 'dashicons' ) );
		wp_enqueue_style( "rangeSlider");
		wp_register_style("rangeSlider.skinNice", SHM_URLPATH . 'assets/css/ion.rangeSlider.skinNice.css', array( 'dashicons' ) );
		wp_enqueue_style( "rangeSlider.skinNice"); 
		wp_register_script("ShMapperTracks.admin", SHM_URLPATH . 'assets/js/admin.js', array(), SHMAPPER_VERSION);
		wp_enqueue_script("ShMapperTracks.admin");
		wp_register_script("rangeSlider", SHM_URLPATH . 'assets/js/ion.rangeSlider.min.js', array());
		wp_enqueue_script("rangeSlider");
		wp_localize_script(
			'ShMapperTracks.admin',
			'shmapper_track',
			array(
				'url' => SHM_URLPATH,
				'updatePlacemark' => __( 'Update Placemark', 'shmapper-by-teplitsa' ),
				'removePlacemark' => __( 'Remove Placemark', 'shmapper-by-teplitsa' )
			)
		);
	}
}
