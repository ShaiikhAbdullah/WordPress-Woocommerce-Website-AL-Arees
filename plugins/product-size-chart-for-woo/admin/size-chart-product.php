<?php

namespace PSCWF\Admin;

defined( 'ABSPATH' ) || exit;

class Size_Chart_Product {
	protected static $instance;

	private function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'pscw_product_tab' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'pscw_product_tab_content' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'pscw_save_product_data' ) );
		add_action( 'wp_ajax_pscw_search_size_chart', array( $this, 'pscw_ajax_search_size_chart' ) );
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self() : self::$instance;
	}

	public function pscw_product_tab( $tabs ) {
		$tabs['pscw_size_chart'] = array(
			'label'    => esc_html__( 'Size chart', 'product-size-chart-for-woo' ),
			'target'   => 'pscw_options',
			'class'    => array(),
			'priority' => 70,
		);

		return $tabs;
	}

	public function pscw_product_tab_content() {
		global $post;
		$pscw_mode                 = get_post_meta( $post->ID, 'pscw_mode', true );
		$pscw_override             = get_post_meta( $post->ID, 'pscw_override', true );
		$pscw_override_size_charts = array();
		if ( is_array( $pscw_override ) && count( $pscw_override ) ) {
			foreach ( $pscw_override as $override_id ) {
				$size_chart = get_post( $override_id );
				if ( $size_chart ) {
					$pscw_override_size_charts[ $override_id ] = $size_chart->post_title;
				}
			}
		}

		wp_nonce_field( 'pscw_search_nonce', 'pscw_nonce' );
		?>
        <div id="pscw_options" class="panel wc-metaboxes-wrapper">
            <div class="woocommerce_variable_attributes wc-metabox-content">
                <div class="wc-metabox data">
                    <a href="<?php echo esc_url( admin_url() . "/edit.php?post_type=pscw-size-chart" ); ?>"
                       target="_blank"><?php esc_html_e( 'Manage Size Charts', 'product-size-chart-for-woo' ); ?></a>
					<?php
					woocommerce_wp_radio( array(
						'id'          => 'pscw_mode',
						'name'        => 'pscw_mode',
						'label'       => esc_html__( 'Product Size Chart Mode', 'product-size-chart-for-woo' ),
						'description' => '',
						'desc_tip'    => false,
						'options'     => array(
							'global'   => esc_html__( 'Global', 'product-size-chart-for-woo' ),
							'disable'  => esc_html__( 'Disable', 'product-size-chart-for-woo' ),
							'override' => esc_html__( 'Override', 'product-size-chart-for-woo' ),
						),
						'value'       => ! empty( $pscw_mode ) ? sanitize_text_field( $pscw_mode ) : 'global',
					) );

					woocommerce_wp_select( array(
						'id'                => 'pscw_override',
						'name'              => 'pscw_override[]',
						'label'             => '',
						'desc_tip'          => false,
						'description'       => '',
						'options'           => ! empty( $pscw_override_size_charts ) ? wc_clean( wp_unslash( $pscw_override_size_charts ) ) : array(),
						'custom_attributes' => array( 'multiple' => 'multiple' ),
						'value'             => ! empty( $pscw_override ) ? wc_clean( wp_unslash( $pscw_override ) ) : array()
					) );
					?>
                </div>
            </div>
        </div>
		<?php
	}

	public function pscw_save_product_data( $product ) {
		if ( isset( $_POST['pscw_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['pscw_nonce'] ) ), 'pscw_search_nonce' ) ) {
			$pscw_mode     = isset( $_POST['pscw_mode'] ) ? wc_clean( wp_unslash( $_POST['pscw_mode'] ) ) : 'global';
			$pscw_override = isset( $_POST['pscw_override'] ) ? wc_clean( wp_unslash( $_POST['pscw_override'] ) ) : array();
			update_post_meta( $product->get_id(), 'pscw_mode', $pscw_mode );
			if ( $pscw_mode === 'override' ) {
				update_post_meta( $product->get_id(), 'pscw_override', $pscw_override );
			}
		}
	}

	public function pscw_ajax_search_size_chart() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'pscw_nonce' ) ) {
			$search_key = isset( $_POST['key_search'] ) ? sanitize_text_field( wp_unslash( $_POST['key_search'] ) ) : '';
			pscw_search_post( 'pscw-size-chart', $search_key );
		}
		die;
	}

}
