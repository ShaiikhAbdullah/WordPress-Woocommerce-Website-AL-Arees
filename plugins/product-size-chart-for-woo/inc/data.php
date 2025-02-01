<?php

namespace PSCWF\Inc;

defined( 'ABSPATH' ) || exit;

class Data {
	private $params;
	private $default;
	protected static $instance = null;

	private function __construct() {
		global $woopscw_settings;

		if ( ! $woopscw_settings ) {
			$woopscw_settings = get_option( 'woo_sc_setting', array() );
		}

		$this->default = array(
			'enable'         => '1',
			'position'       => 'product_tabs',
			'woo_sc_name'    => '',
			'btn_horizontal' => 'right',
			'btn_vertical'   => '50',
			'multi_sc'       => '1',
			'button_type'    => 'text',
			'btn_color'      => '#2185d0',
			'text_color'     => '#ffffff',
			'pscw_icon'      => 'ruler-icon-2',
			'custom_css'     => '',
			/*Dummy option*/
			'cus_design'     => '',
			'cus_table'      => '',
			'cus_tab'        => '',
			'cus_text'       => '',
			'cus_image'      => '',
			'cus_divider'    => '',
			'cus_accordion'  => '',

		);

		$this->params = apply_filters( 'woo_sc_setting', wp_parse_args( $woopscw_settings, $this->default ) );
	}

	public function get_params( $name = "" ) {
		if ( ! $name ) {
			return $this->params;
		} elseif ( isset( $this->params[ $name ] ) ) {
			return apply_filters( 'woo_sc_setting_' . $name, $this->params[ $name ] );
		} else {
			return false;
		}
	}

	public static function get_instance( $new = false ) {
		if ( $new || null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}