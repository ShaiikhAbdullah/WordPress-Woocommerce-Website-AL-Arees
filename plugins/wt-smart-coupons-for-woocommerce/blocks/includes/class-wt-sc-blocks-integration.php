<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
class Wt_Sc_Free_Blocks_Integration implements IntegrationInterface {

	public $registered_blocks = array();
	public $editor_script_handles = array();
	public $frontend_script_handles = array();
	public $frontend_script_data = array();
	
	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wt_sc_blocks';
	}

	
	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$this->register_block_frontend_scripts();
		$this->register_block_editor_scripts();
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return $this->frontend_script_handles;
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return $this->editor_script_handles;
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return $this->frontend_script_data;
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return WT_SC_FREE_BLOCKS_VERSION;
	}


	/**
	 * Register scripts for block editor.
	 *
	 * @return void
	 */
	public function register_block_editor_scripts() {

		foreach ( $this->registered_blocks as $block_data ) {
			
			// Check and register editor scripts
			if ( is_array( $block_data ) 
				&& isset( $block_data['block_dir'] ) 
				&& isset( $block_data['script_handles'] ) 
				&& is_array( $block_data['script_handles'] ) 
			) {

				$handle = 'wt-sc-blocks-' . $block_data['block_dir'] . '-editor';

				// Scripts
				if ( in_array( 'editor-js' , $block_data['script_handles'] ) ) {
					$script_url        = WT_SC_FREE_BLOCKS_URL . 'build/' . $block_data['block_dir'] . '/index.js';
					$script_asset_path = WT_SC_FREE_BLOCKS_MAIN_PATH . 'build/' . $block_data['block_dir'] . '/index.asset.php';
					
					$script_asset      = file_exists( $script_asset_path )
						? require $script_asset_path
						: array(
							'dependencies' => array(),
							'version'      => $this->get_file_version( $script_asset_path ),
						);

					wp_register_script(
						$handle,
						$script_url,
						$script_asset['dependencies'],
						$script_asset['version'],
						true
					);
				}


				// Style
				if ( in_array( 'editor-css' , $block_data['script_handles'] ) ) {
					
					$style_url = WT_SC_FREE_BLOCKS_URL . 'build/' . $block_data['block_dir'] . '/index.css';
					$style_path = WT_SC_FREE_BLOCKS_MAIN_PATH . 'build/' . $block_data['block_dir'] . '/index.css';

					wp_enqueue_style(
						$handle,
						$style_url,
						array(),
						$this->get_file_version( $style_path )
					);
				}
			}
		}	
	}


	/**
	 * Register scripts for frontend block.
	 *
	 * @return void
	 */
	public function register_block_frontend_scripts() {
		
		foreach ( $this->registered_blocks as $block_data ) {
			
			// Check and register frontend scripts
			if ( is_array( $block_data ) 
				&& isset( $block_data['block_dir'] ) 
				&& isset( $block_data['script_handles'] ) 
				&& is_array( $block_data['script_handles'] ) 
				&& in_array( 'frontend-js' , $block_data['script_handles'] )
			) {

				$script_url        = WT_SC_FREE_BLOCKS_URL . 'build/' . $block_data['block_dir'] . '/frontend.js';
				$script_asset_path = WT_SC_FREE_BLOCKS_MAIN_PATH . 'build/' . $block_data['block_dir'] . '/frontend.asset.php';
				
				$script_asset      = file_exists( $script_asset_path )
					? require $script_asset_path
					: array(
						'dependencies' => array(),
						'version'      => $this->get_file_version( $script_asset_path ),
					);

				wp_register_script(
					'wt-sc-blocks-' . $block_data['block_dir'] . '-frontend',
					$script_url,
					$script_asset['dependencies'],
					$script_asset['version'],
					true
				);
			}
		}
	}	
}