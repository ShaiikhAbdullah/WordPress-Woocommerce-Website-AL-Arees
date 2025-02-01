<?php
/**
 * Plugin Name: Product Size Chart for WooCommerce
 * Plugin URI: https://villatheme.com/extensions/woo-product-size-chart/
 * Description: WooCommerce Size Chart lets customize and design size charts for specific products or categories, enhancing customer convenience and boosting sales.
 * Version: 2.0.8
 * Author URI: http://villatheme.com
 * Author: VillaTheme
 * Copyright 2021-2024 VillaTheme.com. All rights reserved.
 * Text Domain: product-size-chart-for-woo
 * Requires Plugins: woocommerce
 * Tested up to: 6.7
 * WC requires at least: 7.0
 * WC tested up to: 9.4
 * Requires PHP: 7.0
 **/

namespace PSCWF;

use PSCWF\Admin\Size_Chart_Product;
use PSCWF\Inc\Customizer\Customizer;
use PSCWF\Inc\Enqueue;
use PSCWF\Inc\Data;
use PSCWF\Admin\Settings;
use PSCWF\Admin\Size_Chart;
use PSCWF\Inc\Frontend\Front_End;
use PSCWF\Inc\Setup_Wizard;
use PSCWF\Inc\Short_Code;

defined( 'ABSPATH' ) || exit;

//Compatible with High-Performance order storage (COT)
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce-product-size-chart/woocommerce-product-size-chart.php' ) ) {
	return;
}


require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

if ( ! class_exists( 'VillaTheme_Require_Environment' ) || ! class_exists( 'VillaTheme_Support' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'support/support.php';
}

if ( ! class_exists( 'Product_Size_Chart_F' ) ) {
	class Product_Size_Chart_F {
		public function __construct() {
			$this->define();

			register_activation_hook( __FILE__, array( $this, 'install' ) );
			add_action( 'activated_plugin', [ $this, 'after_activated' ] );
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			add_action( 'init', array( $this, 'load_text_domain' ) );
		}

		function define() {
			define( 'PSCW_CONST_F', [
				'version'     => '2.0.8',
				'plugin_name' => 'Product Size Chart for WooCommerce',
				'slug'        => 'pscw',
				'assets_slug' => 'pscw-',
				'file'        => __FILE__,
				'basename'    => plugin_basename( __FILE__ ),
				'plugin_dir'  => plugin_dir_path( __FILE__ ),
				'libs_url'    => plugins_url( 'assets/libs/', __FILE__ ),
				'css_url'     => plugins_url( 'assets/css/', __FILE__ ),
				'js_url'      => plugins_url( 'assets/js/', __FILE__ ),
				'img_url'     => plugins_url( 'assets/img/', __FILE__ ),
			] );
		}

		function plugins_loaded() {
			$environment = new \VillaTheme_Require_Environment( [
					'plugin_name'     => 'Product Size Chart for WooCommerce',
					'php_version'     => '7.0',
					'wp_version'      => '5.0',
					'wc_version'      => '7.0',
					'require_plugins' => [
						[
							'slug'    => 'woocommerce',
							'name'    => 'WooCommerce',
							'file'    => 'woocommerce/woocommerce.php',
							'version' => '7.0',
						],

					]
				]
			);

			if ( $environment->has_error() ) {
				return;
			}

			$this->init();
		}

		public function init() {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
			$this->load_classes();
			$this->migrate_data_from_free_to_pro();
		}

		public function load_text_domain() {
			load_plugin_textdomain( 'product-size-chart-for-woo', false, PSCW_CONST_F['basename'] . '/languages' );
		}

		function load_classes() {
			require_once PSCW_CONST_F['plugin_dir'] . 'inc/functions.php';

			Enqueue::instance();
			Setup_Wizard::instance();
			Settings::instance();
			Size_Chart::instance();
			Size_Chart_Product::instance();
			Short_Code::instance();
			Front_End::instance();
			Customizer::instance();

			if ( is_admin() && ! wp_doing_ajax() ) {
				$this->support();
			}
		}

		public function support() {
			new \VillaTheme_Support(
				array(
					'support'    => 'https://wordpress.org/support/plugin/product-size-chart-for-woo/',
					'docs'       => 'https://docs.villatheme.com/?item=woocommerce-product-size-chart',
					'review'     => 'https://wordpress.org/support/plugin/product-size-chart-for-woo/reviews/?rate=5#rate-response',
					'pro_url'    => 'https://1.envato.market/zN1kJe',
					'css'        => PSCW_CONST_F['css_url'],
					'image'      => PSCW_CONST_F['img_url'],
					'slug'       => 'product-size-chart-for-woo',
					'menu_slug'  => 'edit.php?post_type=pscw-size-chart',
					'version'    => PSCW_CONST_F['version'],
					'survey_url' => 'https://script.google.com/macros/s/AKfycbyu3hbv83J-U0p0RxhdqaTBKXlE2A7Vja6BC2XmaYq8bXymI4VDeDA2sFYgjTH-c3yXfw/exec'
				)
			);
		}

		public function settings_link( $links ) {
			return array_merge(
				[
					sprintf( "<a href='%1s' >%2s</a>", esc_url( admin_url( 'edit.php?post_type=pscw-size-chart&page=pscw-size-chart-setting' ) ),
						esc_html__( 'Settings', 'product-size-chart-for-woo' ) )
				],
				$links );
		}

		public function after_activated( $plugin ) {
			$args = array(
				'post_type'      => 'product',
				'orderby'        => 'asc',
				'posts_per_page' => 1,
			);

			$product_query = new \WP_Query( $args );
			$productIDs    = wp_list_pluck( $product_query->posts, 'ID' );

			if ( empty( $productIDs ) ) {

				$product_id = wp_insert_post( array(
					'post_title'   => esc_html__( 'Product Size Chart Preview', 'product-size-chart-for-woo' ),
					'post_type'    => 'product',
					'post_status'  => 'publish',
					'post_content' => '',
				) );


				update_post_meta( $product_id, '_regular_price', 0 );
				update_post_meta( $product_id, '_product_type', 'simple' );
				update_post_meta( $product_id, '_price', 0 );
				update_post_meta( $product_id, '_visibility', 'visible' );
			}

			$this->migrate_data_from_free_to_pro();
			if ( $plugin === plugin_basename( __FILE__ ) ) {
				$page = isset( $_GET['page'] ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( get_option( 'pscw_setup_wizard' ) ) {
					if ( $page !== 'pscw-setup' ) {
						$url = add_query_arg( [ 'page' => 'pscw-setup' ], admin_url() );
						wp_safe_redirect( $url );
						exit();
					}
				}
			}

		}

		public function install() {
			$check_active = get_option( 'woo_sc_setting', array() );
			if ( ! $check_active ) {
				$settings = Data::get_instance();
				$params   = $settings->get_params();
				update_option( 'woo_sc_setting', $params );
				update_option( 'pscw_setup_wizard', 1, 'no' );
			} else {
				if ( ! isset( $check_active['pscw_icon'] ) ) {
					$check_active['pscw_icon'] = 'ruler-icon-2';
					update_option( 'woo_sc_setting', $check_active );
				}
			}
		}

		public function migrate_data_from_free_to_pro() {
			$posts = get_posts( array(
					'post_type'   => 'pscw-size-chart',
					'numberposts' => - 1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'  => array(
						'relation' => 'AND',
						array(
							'key'     => 'woo_sc_size_chart_data',
							'compare' => 'EXISTS',
						),
						array(
							'key'     => 'pscw_data',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'pscw_list_product',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'pscw_interface',
							'compare' => 'NOT EXISTS',
						),
					)
				)
			);

			foreach ( $posts as $postt ) {
				$woo_sc_size_chart_data = get_post_meta( $postt->ID, 'woo_sc_size_chart_data', true );
				pscw_migrate_size_chart_data( $postt, $woo_sc_size_chart_data );
				$this->migrate_products( $postt, $woo_sc_size_chart_data );
			}
		}

		public function migrate_products( $postt, $woo_sc_size_chart_data ) {

			$pscw_data = array
			(
				'assign'          => 'none',
				'allow_countries' => array(),
				'condition'       => array(),
			);


			if ( isset( $woo_sc_size_chart_data['categories'] ) && isset( $woo_sc_size_chart_data['search_product'] ) ) {
				if ( ! empty( $woo_sc_size_chart_data['categories'] ) && ! empty( $woo_sc_size_chart_data['search_product'] ) ) {
					$new_list_product = [];
					if ( ( is_array( $woo_sc_size_chart_data['categories'] ) && count( $woo_sc_size_chart_data['categories'] ) && ( is_array( $woo_sc_size_chart_data['search_product'] ) && count( $woo_sc_size_chart_data['search_product'] ) ) ) ) {
						$pscw_data['assign'] = 'product_cat';


						foreach ( $woo_sc_size_chart_data['categories'] as $cat_id ) {
							$get_term                 = get_term_by( 'id', $cat_id, 'product_cat' );
							$new_list_product         = $new_list_product + wc_get_products( array(
									'category' => [ $cat_id ],
									'return'   => 'ids'
								) );
							$pscw_data['condition'][] = $get_term->slug;
						}
						foreach ( $new_list_product as $product_id ) {
							$pscw_sizecharts = get_post_meta( $product_id, 'pscw_sizecharts', true );
							if ( empty( get_post_meta( $product_id, 'pscw_mode', true ) ) ) {
								update_post_meta( $product_id, 'pscw_mode', 'global' );
							}
							if ( empty( $pscw_sizecharts ) ) {
								update_post_meta( $product_id, 'pscw_sizecharts', [ $postt->ID ] );
							} else {
								$pscw_sizecharts[] = $postt->ID;
								update_post_meta( $product_id, 'pscw_sizecharts', $pscw_sizecharts );
							}
						}

						update_post_meta( $postt->ID, 'pscw_data', $pscw_data );
						update_post_meta( $postt->ID, 'pscw_list_product', $new_list_product );

						foreach ( $woo_sc_size_chart_data['search_product'] as $search_product_id ) {
							if ( ! in_array( $search_product_id, $woo_sc_size_chart_data['categories'] ) ) {
								$pscw_override = get_post_meta( $search_product_id, 'pscw_override', true );
								if ( empty( $pscw_override ) ) {
									update_post_meta( $search_product_id, 'pscw_override', [ $postt->ID ] );
								} else {
									$pscw_override[] = $postt->ID;
									update_post_meta( $search_product_id, 'pscw_override', $pscw_override );
								}
								if ( empty( get_post_meta( $product_id, 'pscw_mode', true ) ) ) {
									update_post_meta( $product_id, 'pscw_mode', 'override' );
								}
							}
						}

					}

				} else if ( ! empty( $woo_sc_size_chart_data['categories'] ) && empty( $woo_sc_size_chart_data['search_product'] ) ) {
					$new_list_product = [];
					if ( is_array( $woo_sc_size_chart_data['categories'] ) && count( $woo_sc_size_chart_data['categories'] ) ) {
						$pscw_data['assign'] = 'product_cat';

						foreach ( $woo_sc_size_chart_data['categories'] as $cat_id ) {
							$get_term                 = get_term_by( 'id', $cat_id, 'product_cat' );
							$new_list_product         = $new_list_product + wc_get_products( array(
									'category' => $get_term->slug,
									'return'   => 'ids'
								) );
							$pscw_data['condition'][] = $get_term->slug;
						}
						foreach ( $new_list_product as $product_id ) {
							$pscw_sizecharts = get_post_meta( $product_id, 'pscw_sizecharts', true );
							if ( empty( get_post_meta( $product_id, 'pscw_mode', true ) ) ) {
								update_post_meta( $product_id, 'pscw_mode', 'global' );
							}
							if ( empty( $pscw_sizecharts ) ) {
								update_post_meta( $product_id, 'pscw_sizecharts', [ $postt->ID ] );
							} else {
								$pscw_sizecharts[] = $postt->ID;
								update_post_meta( $product_id, 'pscw_sizecharts', $pscw_sizecharts );
							}
						}

						update_post_meta( $postt->ID, 'pscw_data', $pscw_data );
						update_post_meta( $postt->ID, 'pscw_list_product', $new_list_product );
					}
				} else if ( empty( $woo_sc_size_chart_data['categories'] ) && ! empty( $woo_sc_size_chart_data['search_product'] ) ) {
					$pscw_data['assign']    = 'products';
					$pscw_data['condition'] = $woo_sc_size_chart_data['search_product'];
					foreach ( $woo_sc_size_chart_data['search_product'] as $product_id ) {
						$pscw_sizecharts = get_post_meta( $product_id, 'pscw_sizecharts', true );
						if ( empty( get_post_meta( $product_id, 'pscw_mode', true ) ) ) {
							update_post_meta( $product_id, 'pscw_mode', 'global' );
						}
						if ( empty( $pscw_sizecharts ) ) {
							update_post_meta( $product_id, 'pscw_sizecharts', [ $postt->ID ] );
						} else {
							$pscw_sizecharts[] = $postt->ID;
							update_post_meta( $product_id, 'pscw_sizecharts', $pscw_sizecharts );
						}
					}
					update_post_meta( $postt->ID, 'pscw_data', $pscw_data );
					update_post_meta( $postt->ID, 'pscw_list_product', $woo_sc_size_chart_data['search_product'] );
				} else {
					update_post_meta( $postt->ID, 'pscw_data', $pscw_data );
					update_post_meta( $postt->ID, 'pscw_list_product', [] );
				}
			}
		}
	}

	new Product_Size_Chart_F();
}
