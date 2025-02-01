<?php

namespace PSCWF\Inc\Customizer;

use _WP_Editors;
use PSCWF\Inc\Data;
use PSCWF\Inc\I18n;

defined( 'ABSPATH' ) || exit;

class Customizer {
	protected static $instance = null;
	protected $suffix = '';
	protected $setting;

	private function __construct() {
		$this->suffix = WP_DEBUG ? '' : '.min';
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['pscw_id'] ) ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			update_user_meta( get_current_user_id(), 'pscw_current_editing_sc', sanitize_text_field( wp_unslash( $_REQUEST['pscw_id'] ) ) );
		}
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['pscw_sizechart_mode'] ) ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			update_user_meta( get_current_user_id(), 'pscw_sizechart_mode', sanitize_text_field( wp_unslash( $_REQUEST['pscw_sizechart_mode'] ) ) );
		}
		$this->setting = Data::get_instance();
		add_action( 'customize_register', array( $this, 'pscw_customizer' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ), 30 );
		add_action( 'wp_ajax_pscw_save_size_chart_data', array( $this, 'pscw_save_size_chart_data' ) );
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self() : self::$instance;
	}

	public function pscw_customizer( $wp_customize ) {

		$wp_customize->add_panel( 'pscw_size_chart_customizer', array(
			'title'       => esc_html__( 'WooCommerce Size Chart Design', 'product-size-chart-for-woo' ),
			'description' => '',
			'priority'    => 200,
		) );

		$this->add_section_design( $wp_customize );
		$this->add_section_table( $wp_customize );
		$this->add_section_tab( $wp_customize );
		$this->add_section_text( $wp_customize );
		$this->add_section_image( $wp_customize );
		$this->add_section_divider( $wp_customize );
		$this->add_section_accordion( $wp_customize );
	}

	public function add_section_design( $wp_customize ) {
		$wp_customize->add_section( 'pscw_customizer_design', array(
			'title'          => esc_html__( 'Design', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_design]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_design]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_design',
		) ) );

	}

	public function add_section_table( $wp_customize ) {

		$wp_customize->add_section( 'pscw_customizer_table', array(
			'title'          => esc_html__( 'Table', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_table]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_table]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_table',
		) ) );
	}

	public function add_section_tab( $wp_customize ) {

		$wp_customize->add_section( 'pscw_customizer_tab', array(
			'title'          => esc_html__( 'Tab', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_tab]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_tab]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_tab',
		) ) );
	}

	public function add_section_text( $wp_customize ) {

		$wp_customize->add_section( 'pscw_customizer_text', array(
			'title'          => esc_html__( 'Text Editor', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_text]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_text]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_text',
		) ) );
	}

	public function add_section_image( $wp_customize ) {

		$wp_customize->add_section( 'pscw_customizer_image', array(
			'title'          => esc_html__( 'Image', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_image]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_image]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_image',
		) ) );
	}

	public function add_section_divider( $wp_customize ) {

		$wp_customize->add_section( 'pscw_customizer_divider', array(
			'title'          => esc_html__( 'Divider', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_divider]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_divider]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_divider',
		) ) );
	}

	public function add_section_accordion( $wp_customize ) {

		$wp_customize->add_section( 'pscw_customizer_accordion', array(
			'title'          => esc_html__( 'Accordion', 'product-size-chart-for-woo' ),
			'priority'       => 10,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'panel'          => 'pscw_size_chart_customizer',
		) );


		$wp_customize->add_setting( 'woo_sc_setting[cus_accordion]', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'woo_sc_setting[cus_accordion]', array(
			'label'   => '',
			'type'    => 'checkbox',
			'section' => 'pscw_customizer_accordion',
		) ) );
	}

	public function customize_preview_init() {
		$current_sc_id = get_user_meta( get_current_user_id(), 'pscw_current_editing_sc', true );

		$args = array(
			'currentScId' => $current_sc_id,
			'shortCode'   => do_shortcode( '[PSCW_SIZE_CHART id="' . $current_sc_id . '" signature="pscw_signature" ]' ),
			'position'    => $this->setting->get_params( 'position' ),
		);

		wp_enqueue_style( 'pscw-customize-preview', PSCW_CONST_F['css_url'] . 'customize-preview' . $this->suffix . '.css', array(), PSCW_CONST_F['version'] );
		wp_enqueue_script( 'pscw-customize-preview', PSCW_CONST_F['js_url'] . 'customize-preview' . $this->suffix . '.js', array( 'jquery' ), PSCW_CONST_F['version'], true );
		wp_localize_script( 'pscw-customize-preview', 'ViPscwCusParams', $args );
		if ( empty( $current_sc_id ) ) {
			wp_add_inline_style( 'pscw-customize-preview', '#tab-title-size_chart_tab,.woo_sc_frontend_btn{display:none !important;}' );
		}
	}

	public function customize_controls_enqueue_scripts() {
		$size_chart_ids = [];

		$current_sc_id       = get_user_meta( get_current_user_id(), 'pscw_current_editing_sc', true );
		$current_sc_title    = '';
		$pscw_sizechart_mode = get_user_meta( get_current_user_id(), 'pscw_sizechart_mode', true );
		if ( empty( $pscw_sizechart_mode ) ) {
			$size_charts_editing = get_option( 'pscw_size_charts_editing' );
			if ( ! empty( $size_charts_editing ) ) {
				$size_charts_editing = ( $this->setting->get_params( 'multi_sc' ) == '0' ) ? [ $size_charts_editing[0] ] : $size_charts_editing;
				$size_charts         = get_posts( array(
					'include'     => $size_charts_editing,
					'post_type'   => 'pscw-size-chart',
					'post_status' => 'publish'
				) );
				foreach ( $size_charts as $sc ) {
					$size_chart_ids[ $sc->ID ] = $sc->post_title . " (ID:{$sc->ID})";
				}
			}

		} else {
			$size_charts = get_posts( array(
				'post_type'   => 'pscw-size-chart',
				'post_status' => 'publish',
				'numberposts' => - 1
			) );
			foreach ( $size_charts as $sc ) {
				$size_chart_ids[ $sc->ID ] = $sc->post_title . " (ID:{$sc->ID})";
				if ( $sc->ID == $current_sc_id ) {
					$current_sc_title = $sc->post_title;
				}
			}
		}

		$interface    = get_post_meta( $current_sc_id, 'pscw_interface', true );
		$pscw_data    = get_post_meta( $current_sc_id, 'pscw_data', true );
		$list_product = get_post_meta( $current_sc_id, 'pscw_list_product' );

		$pscw_assign        = $pscw_data['assign'] ?? 'none';
		$pscw_condition     = $pscw_data['condition'] ?? [];
		$pscw_assign_values = [];

		if ( $pscw_assign != 'none' ) {
			foreach ( $pscw_condition as $id ) {
				$value = pscw_get_value_combined( $pscw_assign, $id );
				if ( count( $value ) === 2 ) {
					$pscw_assign_values[] = $value;
				}
			}
		}

		$assign_options = array(
			'none'               => esc_html__( 'None', 'product-size-chart-for-woo' ),
			'all'                => esc_html__( 'All Products', 'product-size-chart-for-woo' ),
			'products'           => esc_html__( 'Products', 'product-size-chart-for-woo' ),
			'product_cat'        => esc_html__( 'Product Categories', 'product-size-chart-for-woo' ),
			'combined'           => esc_html__( 'Combined (Premium)', 'product-size-chart-for-woo' ),
			'product_type'       => esc_html__( 'Product Type (Premium)', 'product-size-chart-for-woo' ),
			'product_visibility' => esc_html__( 'Product Visibility (Premium)', 'product-size-chart-for-woo' ),
			'product_tag'        => esc_html__( 'Product Tags (Premium)', 'product-size-chart-for-woo' ),
			'shipping_class'     => esc_html__( 'Product Shipping Class (Premium)', 'product-size-chart-for-woo' ),
		);

		if ( empty( $interface ) ) {
			wp_register_style( 'pscw-dummy-handle', false, array(), PSCW_CONST_F['version'], false );
			wp_enqueue_style( 'pscw-dummy-handle' );
			wp_add_inline_style( 'pscw-dummy-handle', "#accordion-panel-pscw_size_chart_customizer{display:none !important}" );

			return;
		}
		if ( ! class_exists( '_WP_Editors', false ) ) {
			require ABSPATH . WPINC . '/class-wp-editor.php';
		}
		_WP_Editors::print_tinymce_scripts();
		wp_enqueue_media();
		wp_enqueue_editor();
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'pscw-range', PSCW_CONST_F['css_url'] . 'range' . $this->suffix . '.css', array(), PSCW_CONST_F['version'] );
		wp_enqueue_script( 'pscw-range', PSCW_CONST_F['js_url'] . 'range' . $this->suffix . '.js', array( 'jquery' ), PSCW_CONST_F['version'], true );
		wp_enqueue_style( 'pscw-select2', PSCW_CONST_F['libs_url'] . 'select2.min.css', array(), PSCW_CONST_F['version'] );
		wp_enqueue_script( 'pscw-select2', PSCW_CONST_F['libs_url'] . 'select2.min.js', array( 'jquery' ), PSCW_CONST_F['version'], true );
		wp_enqueue_style( 'pscw-customize-setting', PSCW_CONST_F['css_url'] . 'customize-setting' . $this->suffix . '.css', array(), PSCW_CONST_F['version'] );
		wp_enqueue_script( 'pscw-customize-setting', PSCW_CONST_F['js_url'] . 'customize-setting' . $this->suffix . '.js', array(
			'jquery',
			'wp-color-picker',
			'jquery-ui-sortable',
			'wp-editor'
		), PSCW_CONST_F['version'], true );
		wp_localize_script( 'pscw-customize-setting', 'VicPscwParams', array(
			'imgUrl'           => PSCW_CONST_F['img_url'],
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'pscw_nonce' ),
			'scTitle'          => $current_sc_title,
			'interface'        => wp_json_encode( $interface, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ),
			'listProduct'      => wp_json_encode( $list_product ),
			'placeholderImage' => wc_placeholder_img_src( 'full' ),
			'assignOptions'    => wp_json_encode( $assign_options ),
			'assignTag'        => $pscw_assign,
			'assignValues'     => $pscw_assign_values,
			'sizeCharts'       => $size_chart_ids,
			'currentSizeChart' => $current_sc_id,
			'customizeMode'    => $pscw_sizechart_mode,
			'i18n'             => I18n::init(),
		) );

	}

	public function pscw_save_size_chart_data() {
		if ( isset( $_POST['nonce'], $_POST['pscwId'], $_POST['value'], $_POST['pscwData'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pscw_nonce' ) ) {
			$pscw_id   = sanitize_text_field( wp_unslash( $_POST['pscwId'] ) );
			$pscw_data = json_decode( sanitize_text_field( wp_unslash( $_POST['pscwData'] ) ), true );
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_update_post( array(
				'ID'         => $pscw_id,
				'post_title' => $pscw_data['title'],
			) );
			unset( $pscw_data['title'] );
			update_post_meta( $pscw_id, 'pscw_data', $pscw_data );
			update_post_meta( $pscw_id, 'pscw_interface', json_decode( wp_unslash( $_POST['value'] ), true ) );

			wp_send_json_success();
		}
		wp_die();
	}

}