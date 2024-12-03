<?php
/**
 * ShMapper Blocks Category
 *
 * @package teplitsa
 */

/**
 * Gutenberg Blocks Category
 *
 * @param array $block_categories Block categories.
 */
function shm_block_categories_all( $block_categories ) {

	array_push( $block_categories,
		array(
			'slug'  => 'shmapper',
			'title' => esc_html__( 'ShMapper', 'shmapper-by-teplitsa' ),
		)
	);

	return $block_categories;

}
add_filter( 'block_categories_all', 'shm_block_categories_all' );
