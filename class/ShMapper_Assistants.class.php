<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShMapper_Assistants
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
		add_filter("shm_after_request_form", 	[ __CLASS__, "shm_after_request_form" ]);
		add_filter('shm_after_request_form',	[ __CLASS__, 'shm_after_request_form2']);
		add_filter( 'parse_query', 				[ __CLASS__,'ba_admin_posts_filter' ] );
		add_action( 'restrict_manage_posts', 	[ __CLASS__,'ba_admin_posts_filter_restrict_manage_posts' ] );
	} 
	
	static function ba_admin_posts_filter( $query )
	{
		global $pagenow, $map_dropdown;
		if ( is_admin() && $pagenow=='edit.php' && SHM_POINT == $query->get('post_type') ) 
		{
			if (isset($_GET['ADMIN_FILTER_FIELD']) && $_GET['ADMIN_FILTER_FIELD'] > 1)
			{
				$map = ShmMap::get_instance( $_GET['ADMIN_FILTER_FIELD'] );
				$query->query_vars['post__in'] 	= $map->get_points_args();
			}
		}
	}

	static function ba_admin_posts_filter_restrict_manage_posts()
	{
		global $wpdb, $post, $wp_list_table, $map_dropdown;
		$current = isset($_GET['ADMIN_FILTER_FIELD'])? $_GET['ADMIN_FILTER_FIELD']:'';
		if($post && $post->post_type == SHM_POINT)
		{
			$map_dropdown = $map_dropdown ? $map_dropdown : ShmMap::get_all();
			echo ShmMap::wp_dropdown([
				"posts"			=> $map_dropdown,
				"name" 			=> "ADMIN_FILTER_FIELD",
				"selected" 		=>  esc_attr( $current ),
				"style"			=> "width:120px;",
				"select_none" 	=> esc_html__("all maps", "shmapper-by-teplitsa")
			]);
			?>
			<input name="pagenum" 			type="hidden" value="<?php echo esc_attr( $wp_list_table->get_pagenum() ); ?>" />
			<input name="items_per_page" 	type="hidden" value="<?php echo esc_attr( $wp_list_table->get_items_per_page('per_page') ); ?>" />
			
			<?php
		}
	}
	
	
	static function shm_after_request_form( $text )
	{
		if( empty(ShMapper::$options['shm_settings_captcha']) ) {
			return $text;
		}

		//require_once( SHM_REAL_PATH .'assets/recaptcha-php-1.11/recaptchalib.php');
		// Register API keys at https://www.google.com/recaptcha/admin
		$siteKey = isset( ShMapper::$options['shm_captcha_siteKey'] ) ? ShMapper::$options['shm_captcha_siteKey'] : '';
		$secret = isset( ShMapper::$options['shm_captcha_secretKey'] ) ? ShMapper::$options['shm_captcha_secretKey'] : '';
		
		// reCAPTCHA supported 40+ languages listed here: https://developers.google.com/recaptcha/docs/language
		$html = '<div class="shm-form-element" id="grec">
			<div class="g-recaptcha" data-sitekey="'. esc_attr( $siteKey ) . '"></div>
			<script type="text/javascript"
				src="https://www.google.com/recaptcha/api.js?hl=ru">
			</script>
		</div>';
		return $text . $html;

	}
	static function get_recaptcha_form()
	{
		
	}
	static function shm_after_request_form2($text)
	{
		//SESSION if session-plugin active
		if(!shm_is_session() || !$_SESSION)	return $text;
		$shm_reqs = $_SESSION['shm_reqs'];
		if(!is_array($shm_reqs))
			$shm_reqs = [ ];
		$html = "<div class='shm-row'>
			<div class='shm-12'>
				<div class='shm-title-5'>" .
					esc_html__("Your requests to this Map ", "shmapper-by-teplitsa") .
				"</div>
			</div>
			<div class='shm-12'>".
				$shm_reqs[0] .
			"</div>
		</div>";
		return $text.$html;
	}
}
