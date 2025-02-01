<?php

namespace PSCWF\Inc;

defined( 'ABSPATH' ) || exit;

class Enqueue {
	protected static $instance = null;
	protected static $setting;
	protected $slug;
	protected $suffix = '';

	protected $lib_styles = [
		'button',
		'tab',
		'input',
		'segment',
		'image',
		'modal',
		'dimmer',
		'transition',
		'menu',
		'grid',
		'search',
		'message',
		'loader',
		'label',
		'select2',
		'header',
		'accordion',
		'dropdown',
		'checkbox',
		'form',
		'table',
		'slider',
		'icon',
		'step',
	];

	protected $lib_scripts = [
		'tab',
		'form',
		'checkbox',
		'slider',
		'address-1.6',
		'dropdown',
		'transition',
		'select2',
		'iris' => [ 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ],

	];

	private function __construct() {
		$this->slug    = PSCW_CONST_F['assets_slug'];
		self::$setting = Data::get_instance();
		$this->suffix  = WP_DEBUG ? '' : '.min';
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'client_enqueue_scripts' ) );
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self : self::$instance;
	}

	public function register_common_scripts() {
		//*************************************//
		$lib_styles = $this->lib_styles;

		foreach ( $lib_styles as $style ) {
			wp_register_style( $this->slug . $style, PSCW_CONST_F['libs_url'] . $style . '.min.css', '', PSCW_CONST_F['version'] );
		}

		//*************************************//
		$lib_scripts = $this->lib_scripts;

		foreach ( $lib_scripts as $script => $depend ) {
			if ( is_array( $depend ) ) {
				wp_register_script( $this->slug . $script, PSCW_CONST_F['libs_url'] . $script . '.min.js', $depend, PSCW_CONST_F['version'], false );
			} else {
				wp_register_script( $this->slug . $depend, PSCW_CONST_F['libs_url'] . $depend . '.min.js', [ 'jquery' ], PSCW_CONST_F['version'], false );
			}
		}
	}

	public function register_admin_scripts() {

		//*************************************//
		$admin_styles = [ 'settings', 'size-chart', 'product', 'setup-wizard' ];

		foreach ( $admin_styles as $style ) {
			wp_register_style( $this->slug . $style, PSCW_CONST_F['css_url'] . $style . $this->suffix . '.css', '', PSCW_CONST_F['version'] );
		}

		//*************************************//
		$admin_scripts = [
			'settings'       => [ 'jquery' ],
			'size-chart'     => [ 'jquery' ],
			'all-size-chart' => [ 'jquery' ],
			'product'        => [ 'jquery' ],
			'setup-wizard'   => [ 'jquery' ],
		];

		foreach ( $admin_scripts as $script => $depend ) {
			wp_register_script( $this->slug . $script, PSCW_CONST_F['js_url'] . $script . $this->suffix . '.js', $depend, PSCW_CONST_F['version'], true );
		}

	}

	public function admin_enqueue_scripts() {
		$screen_id = get_current_screen()->id;

		$this->register_common_scripts();
		$this->register_admin_scripts();

		$enqueue_scripts = $enqueue_styles = [];
		switch ( $screen_id ) {
			case 'pscw-size-chart_page_pscw-size-chart-setting':
				$enqueue_styles  = [
					'icon',
					'segment',
					'button',
					'menu',
					'form',
					'dropdown',
					'checkbox',
					'transition',
					'label',
					'input',
					'settings'
				];
				$enqueue_scripts = [
					'tab',
					'checkbox',
					'form',
					'dropdown',
					'transition',
					'iris',
					'settings'
				];
				break;
			case 'pscw-size-chart':
				$enqueue_styles  = [
					'form',
					'icon',
					'checkbox',
					'transition',
					'segment',
					'menu',
					'select2',
					'size-chart'
				];
				$enqueue_scripts = [
					'form',
					'checkbox',
					'transition',
					'select2',
					'size-chart',
				];
				break;
			case 'edit-pscw-size-chart':
				wp_register_style( 'pscw-dummy-handle', false, array(), PSCW_CONST_F['version'], false );
				wp_enqueue_style( 'pscw-dummy-handle' );
				wp_add_inline_style( 'pscw-dummy-handle', 'table.wp-list-table tr td .woo_sc_short_code {cursor: pointer;}.icon.input .woo_sc_copied {background-color: black;color: #fff;width: 120px;text-align: center;border-radius: 6px;padding: 5px 0;list-style: none;position: absolute;z-index: 9;top: 100%;left: 50%;margin-left: -60px;opacity: 0.7;visibility: hidden;} table.wp-list-table tr td .vi-ui.input{min-width:269px}' );
				$enqueue_styles  = [
					'input',
					'icon',
					'size-chart'
				];
				$enqueue_scripts = [
					'all-size-chart'
				];
				break;
			case 'product':
				$enqueue_styles  = [
					'select2',
					'product'
				];
				$enqueue_scripts = [
					'select2',
					'product'
				];
				break;
			case 'dashboard_page_pscw-setup':
				$enqueue_styles  = [ 'button', 'header', 'transition', 'dropdown', 'segment', 'step', 'checkbox', 'setup-wizard' ];
				$enqueue_scripts  = [ 'dropdown', 'transition', 'checkbox', 'setup-wizard' ];
				break;
		}

		foreach ( $enqueue_styles as $style ) {
			wp_enqueue_style( $this->slug . $style );
		}


		foreach ( $enqueue_scripts as $script ) {
			wp_enqueue_script( $this->slug . $script );
		}

		$params = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'pscw_nonce' ),
		);
		wp_localize_script( $this->slug . 'size-chart', 'VicPscwParams', $params );
		wp_localize_script( $this->slug . 'product', 'VicPscwParams', $params );
		wp_localize_script( $this->slug . 'setup-wizard', 'VicPscwParams', $params );
		wp_localize_script( $this->slug . 'all-size-chart', 'VicPscwParams', $params );

	}

	public function register_client_scripts() {
		$client_styles = [ 'frontend' ];

		foreach ( $client_styles as $style ) {
			wp_register_style( $this->slug . $style, PSCW_CONST_F['css_url'] . $style . $this->suffix . '.css', '', PSCW_CONST_F['version'] );
		}

		//*************************************//
		$scripts = [ 'frontend' => [ 'jquery' ] ];

		foreach ( $scripts as $script => $depend ) {
			wp_register_script( $this->slug . $script, PSCW_CONST_F['js_url'] . $script . $this->suffix . '.js', $depend, PSCW_CONST_F['version'], true );
		}
	}

	public function client_enqueue_scripts() {
		global $post;

		$this->register_client_scripts();
		$enqueue_styles  = [ 'frontend' ];
		$enqueue_scripts = [ 'frontend' ];
		$inline_style    = 'frontend';
		$css             = '';

		if ( is_product() && $this->get_prams( 'enable' ) ) {

			foreach ( $enqueue_styles as $style ) {
				wp_enqueue_style( $this->slug . $style );
			}

			foreach ( $enqueue_scripts as $script ) {
				wp_enqueue_script( $this->slug . $script );
			}

			$button_css  = "";
			$button_icon = "";
			if ( $this->get_prams( 'position' ) === 'pop-up' ) {
				$button_css = ".woo_sc_price_btn_popup.woo_sc_btn_popup {
				  position: fixed;
				  z-index: 999998;
				}";
			}

			$css .= "#woo_sc_show_popup{top:{$this->get_prams( 'btn_vertical' )}%;{$this->get_prams( 'btn_horizontal' )}:0%}
			    .woo_sc_data_content{clear:both; margin:15px auto;}
				{$button_icon}
				.woo_sc_call_popup{background-color:{$this->get_prams( 'btn_color' )};color:{$this->get_prams( 'text_color' )}
				}{$button_css}.woo_sc_size_icon svg {vertical-align: unset;fill: {$this->get_prams( 'text_color' )}}";

			$css .= self::$setting->get_params( 'custom_css' );
			$css = apply_filters( 'woo_sc_filter_style', $css );
			wp_add_inline_style( $this->slug . $inline_style, esc_html( $css ) );

			wp_localize_script( $this->slug . 'frontend', 'ViPscwFontParams', array(
				'isCustomize' => is_customize_preview(),
				'pscwSizeChartMode' => get_user_meta( get_current_user_id(), 'pscw_sizechart_mode', true ),
			) );

		}
	}

	public function get_prams( $param ) {
		return self::$setting->get_params( $param );
	}
}