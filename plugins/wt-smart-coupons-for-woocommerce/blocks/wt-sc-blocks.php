<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WT_SC_FREE_BLOCKS_FILE' ) ) {
    define( 'WT_SC_FREE_BLOCKS_FILE', __FILE__ );
}

if ( ! defined( 'WT_SC_FREE_BLOCKS_MAIN_PATH' ) ) {
    define( 'WT_SC_FREE_BLOCKS_MAIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WT_SC_FREE_BLOCKS_URL' ) ) {
    define( 'WT_SC_FREE_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WT_SC_FREE_BLOCKS_VERSION' ) ) {
    define( 'WT_SC_FREE_BLOCKS_VERSION', '1.0.0' );
}

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

if ( ! class_exists( 'Wt_Sc_Free_Blocks' ) ) {
	
	class Wt_Sc_Free_Blocks {
		
		public $registered_blocks = array();
		private $block_post_fields = array();
		private $block_post_fields_schema = array();
		
		private $editor_script_handles = array();
		private $frontend_script_handles = array();
		private $frontend_script_data = array();

		public function __construct() {

			/**
			 * 	Init the blocks
			 */
			add_action( 'woocommerce_blocks_loaded', array( $this, 'init' ) );

			/**
			 * 	REST API POST data for modules.
			 * 	Priority must be less than 10
			 */
			add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'store_api_request_data' ), 9, 2); 

			/**
			 *  Save the data from checkout
			 */
			add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'save_data' ), 10, 2 );

		}

		/**
		 * 	Init the blocks
		 * 	1. Set the registered blocks data
		 * 	2. Include block integration class and hook integration.
		 * 	
		 * 	Hooked into: `woocommerce_blocks_loaded`
		 */
		public function init() {			
			
			$this->set_registered_blocks();

			// Include integration class file
			include_once WT_SC_FREE_BLOCKS_MAIN_PATH . 'includes/class-wt-sc-blocks-integration.php';       
	        add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'register_checkout_blocks' ) );
	        add_action( 'woocommerce_blocks_cart_block_registration', array( $this, 'register_checkout_blocks' ) );
	        
	        $this->register_api_endpoint_data();
		}


		/**
		 * 	Load registered blocks data.
		 * 	Modules can hook their blocks data
		 * 
		 */
		public function set_registered_blocks() {

			$registered_blocks = array(
				'main' => array(
	            	'block_dir' => 'main',
	            	'post_fields' => array( 'coupon_code' => '', 'coupon_message' => '' ),
	            	'post_fields_schema' => array(
	            		'coupon_code'  => array(
			 						'description' => __( 'coupon_code', 'wt-smart-coupons-for-woocommerce' ),
			 						'type'        => array( 'string', 'null' ),
			 						'readonly'    => true,
			 					),
	            		'coupon_message'  => array(
			 						'description' => __( 'Coupon message', 'wt-smart-coupons-for-woocommerce' ),
			 						'type'        => array( 'string', 'null' ),
			 						'readonly'    => true,
			 					),
	            	),
	            	'script_handles' => array( 'frontend-js' ),
	        	),
	        );
			/**
			 * 	Modules can register their blocks. This filter just enable the blocks. Blocks code must be present in the blocks directory.
			 * 	
			 * 	Sample block data structure:
			 * 		
			 * 		array(
			 *			'block_first' => array(
			 *				'block_dir' => 'block-first', // Do not use underscore
			 *				'post_fields' => array( 'field_a' => 'field_a_value', 'field_b' => '' ), // Field name and default values
			 *				'post_fields_schema' => array( 
			 *					'field_a'  => array(
			 *						'description' => __( 'Field A', 'text-domain' ),
			 *						'type'        => array( 'string', 'null' ),
			 *						'readonly'    => true,
			 *					),
			 *					'field_b'  => array(
			 *						'description' => __( 'Field B', 'text-domain' ),
			 *						'type'        => array( 'string', 'null' ),
			 *						'readonly'    => true,
			 *					)
			 *				),
			 *				'script_handles' => array( 'editor-js', 'editor-css', 'frontend-css', 'frontend-js' ), // Script handles, Only add the scripts and styles available for the block.
			 * 				'php_include' 	 => array( 'path/filename.php' ), // PHP files to include, file names with path after block_dir
			 *			),
			 *		);
			 * 		
			 * 
			 * 
			 * 	@param array 	$registered_blocks 		Blocks data array
			 */
			$this->registered_blocks = (array) apply_filters( 'wt_sc_blocks_register', $registered_blocks );


			// Prepare `block_post_fields` and `block_post_fields_schema`
			foreach ( $this->registered_blocks as $block_data ) {
							
				if ( is_array( $block_data ) && isset( $block_data['block_dir'] ) ) {
					
					// Post field
					if ( isset( $block_data['post_fields'] ) && is_array( $block_data['post_fields'] ) ) {
						$this->block_post_fields = array_merge( $this->block_post_fields, $block_data['post_fields'] );
					}

					// Field schema
					if ( isset( $block_data['post_fields_schema'] ) && is_array( $block_data['post_fields_schema'] ) ) {
						$this->block_post_fields_schema = array_merge( $this->block_post_fields_schema, $block_data['post_fields_schema'] );
					}

					// Script handles
					if (isset( $block_data['script_handles'] ) && is_array( $block_data['script_handles'] )) {
						
						// Editor
						if ( in_array( 'editor-js' , $block_data['script_handles'] ) ) {
							$this->editor_script_handles[] = 'wt-sc-blocks-' . $block_data['block_dir'] . '-editor';
						}

						// Frontend
						if ( in_array( 'frontend-js' , $block_data['script_handles'] ) ) {
							$this->frontend_script_handles[] = 'wt-sc-blocks-' . $block_data['block_dir'] . '-frontend';
						}
					}

					// Script data
					if ( isset( $block_data['script_data'] ) && is_array( $block_data['script_data'] ) ) {
						$this->frontend_script_data = array_merge( $this->frontend_script_data, $block_data['script_data'] );
					}

					// PHP files to include
					if ( isset( $block_data['php_include'] ) && is_array( $block_data['php_include'] ) ) {				
						foreach ( $block_data['php_include'] as $file ) {							
							$file_path = WT_SC_FREE_BLOCKS_MAIN_PATH . 'src/' . $block_data['block_dir'] . '/' . $file;
							if ( file_exists( $file_path ) ) {
								include_once $file_path;
							}
						}
					}
				}
			}
		}


		/**
	     * 	Register checkout blocks
	     * 	Hooked into: `woocommerce_blocks_checkout_block_registration`, `woocommerce_blocks_cart_block_registration`
	     */
		public function register_checkout_blocks( $integration_registry ) {
		    
		    if ( ! empty( $this->registered_blocks ) ) { // Blocks available
		        $wt_sc_blocks_integration = new Wt_Sc_Free_Blocks_Integration();
		        $wt_sc_blocks_integration->registered_blocks = $this->registered_blocks;
		        $wt_sc_blocks_integration->editor_script_handles = $this->editor_script_handles;
		        $wt_sc_blocks_integration->frontend_script_handles = $this->frontend_script_handles;
		        $wt_sc_blocks_integration->frontend_script_data = $this->frontend_script_data;
		        $integration_registry->register( $wt_sc_blocks_integration );
		    }
	    }


	    /**
	     * 	Register data to checkout end point
	     */
	    public function register_api_endpoint_data() {

	    	if ( ! empty( $this->block_post_fields ) && function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
	            woocommerce_store_api_register_endpoint_data(
	                array(
	                    'endpoint'        => CheckoutSchema::IDENTIFIER,
	                    'namespace'       => 'wt_sc_blocks',
	                    'data_callback'   => array( $this, 'data_callback' ),
	                    'schema_callback' => array( $this, 'schema_callback' ),
	                    'schema_type'     => ARRAY_A,
	                )
	            );

	            woocommerce_store_api_register_endpoint_data(
	                array(
	                    'endpoint'        => CartSchema::IDENTIFIER,
	                    'namespace'       => 'wt_sc_blocks',
	                    'data_callback'   => array( $this, 'data_callback' ),
	                    'schema_callback' => array( $this, 'schema_callback' ),
	                    'schema_type'     => ARRAY_A,
	                )
	            );
	        }


	        if ( function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
	        	woocommerce_store_api_register_update_callback(
				    array(
				      'namespace' => 'wbte-sc-blocks-update-checkout',
				      'callback'  => function( $data ) {},
				    )
			    );

			    woocommerce_store_api_register_update_callback(
				    array(
				      'namespace' => 'wbte-sc-blocks-update-cart',
				      'callback'  => function( $data ) {},
				    )
			    );
	        }
	    }


	    /**
		 * Callback function to register endpoint data for blocks.
		 *
		 * @return array
		 */
		public function data_callback() { 	
			/**
			 * 	Alter the blocks data based on the current cart condition.
			 * 	
			 * 	@param 	array 	Key/value pair associative array
			 */			
			return apply_filters( 'wbte_sc_alter_blocks_data', $this->block_post_fields );
		}


		/**
		 * Callback function to register schema for data.
		 *
		 * @return array
		 */
		public function schema_callback() {
			return $this->block_post_fields_schema;
		}


		/**
		 * 	REST API POST data for modules.
		 * 	Hooked into: woocommerce_store_api_checkout_update_order_from_request
		 * 	
		 * 	@param WC_order 	$order 		Order object
		 * 	@param array 		$request 	Array of request data
		 */
		public function store_api_request_data( $order, $request ) {
			
			$data_arr = isset( $request['extensions']['wt_sc_blocks'] ) && is_array( $request['extensions']['wt_sc_blocks'] ) ? $request['extensions']['wt_sc_blocks'] : array();

			if ( ! empty( $data_arr ) ) {
				
				/**
				 * 	Modules can hook and validate the data from checkout
				 * 
				 * 	@param array 		$data_arr 	Plugin data array
			 	 * 	@param WC_order 	$order 		Order object
		 	 	 * 	@param array 		$request 	Array of request data
				 */
				do_action( 'wt_sc_blocks_validate_checkout_data', $data_arr, $order, $request );
			}
		}


		/**
		 * 	Save data from checkout
		 * 	Hooked into: woocommerce_store_api_checkout_update_order_from_request
		 * 	
		 * 	@param WC_order 	$order 		Order object
		 * 	@param array 		$request 	Array of request data
		 */
		public function save_data( $order, $request ) {
			
			$data_arr = isset( $request['extensions']['wt_sc_blocks'] ) && is_array( $request['extensions']['wt_sc_blocks'] ) ? $request['extensions']['wt_sc_blocks'] : array();

			/**
			 * 	Modules can hook and process the data from checkout
			 * 
			 * 	@param array 		$data_arr 	Plugin data array
			 * 	@param WC_order 	$order 		Order object
		 	 * 	@param array 		$request 	Array of request data
			 */
			do_action( 'wt_sc_blocks_save_checkout_data', $data_arr, $order, $request );
		}

	}

	new Wt_Sc_Free_Blocks();
}	