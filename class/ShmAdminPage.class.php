<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShmAdminPage {
	function __construct() {
	}

	function init_hooks() {
		add_filter( 'admin_footer_text', array($this, 'show_admin_footer_on_default_pages'), 20 );
	}

	function show_admin_footer_on_default_pages($old_footer_html='') {
		$screen = get_current_screen();

		if($screen->parent_base == 'shm_page') {
			return $this->get_admin_footer('', $old_footer_html);
		}

		return $old_footer_html;
	}

	function get_admin_footer($footer_class='', $old_footer_html='') {
		ob_start();
		?>
		<span class="shmapper-admin-footer <?php echo esc_attr( $footer_class ); ?>">
			<a href="https://t.me/shmapper" target="_blank"><?php _e('shMapper developers chat', SHMAPPER);?></a>
		</span>
		<?php
		return ob_get_clean() . $old_footer_html;
	}

}

$admin_page = new ShmAdminPage();
$admin_page->init_hooks();
