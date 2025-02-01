<?php

namespace PSCWF\Inc\Frontend;

use PSCWF\Inc\Data;

defined( 'ABSPATH' ) || exit;

class Front_End {

	protected static $instance = null;
	protected $settings;

	private function __construct() {
		$this->settings = Data::get_instance();
		if ( ! $this->settings->get_params( 'enable' ) ) {
			return;
		}
		$this->handle_sc_button_position();
		$this->handle_sc_show_popup();
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self() : self::$instance;
	}

	public function handle_sc_button_position() {
		switch ( $this->get_params( 'position' ) ) {
			case 'before_add_to_cart':
				add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'show_sc_button' ) );
				break;
			case 'after_add_to_cart':
				add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'show_sc_button' ) );
				break;
			case 'pop-up':
				add_action( 'wp_footer', array( $this, 'show_sc_button' ) );
				break;
			case 'product_tabs':
				add_filter( 'woocommerce_product_tabs', array( $this, 'custom_product_tabs' ) );
				break;
		}
	}

	public function pscw_helper_show_size_chart( $product_id, $update_option_editing = false ) {
		$size_charts    = get_posts( array(
			'post_type'   => 'pscw-size-chart',
			'post_status' => 'publish',
			'numberposts' => - 1,
			'fields'      => 'ids'
		) );

        $product_cate = get_the_terms( $product_id, 'product_cat');
        if ( $product_cate ) {
            $product_cate = array_map(function($term) {
                return $term->slug;
            }, $product_cate);
        }else {
            $product_cate = [];
        }

		$size_chart_all = [];
		

		foreach ( $size_charts as $sc_id ) {
			$pscw_data = get_post_meta( $sc_id, 'pscw_data', true );
			if ( isset( $pscw_data['assign'] ) && $pscw_data['assign'] === 'all' ) {
				$size_chart_all[] = $sc_id;
			}elseif ( isset($pscw_data['assign']) && $pscw_data['assign'] === 'product_cat') {
                if ( !empty( $pscw_data['condition']) && array_intersect( $product_cate, $pscw_data['condition'])) {
                    $size_chart_all[] = $sc_id;
                }
            }

		}
        
		$size_chart_id       = get_post_meta( $product_id, 'pscw_sizecharts', true );
		$size_chart_id       = empty( $size_chart_id ) ? [] : array_values( $size_chart_id );
		$size_chart_id       = array_merge( $size_chart_id, $size_chart_all );
		$product_sc_mode     = get_post_meta( $product_id, 'pscw_mode', true );
		$product_sc_override = get_post_meta( $product_id, 'pscw_override', true );
		$size_chart_id       = $product_sc_mode === 'override' ? array_values( $product_sc_override ) : $size_chart_id;

		if ( $update_option_editing ) {
            if ( is_array( $size_chart_id ) && count( $size_chart_id ) ) {
	            update_option( 'pscw_size_charts_editing', $size_chart_id );
	            update_user_meta( get_current_user_id(), 'pscw_current_editing_sc', $size_chart_id[0] );
            }else {
	            update_option( 'pscw_size_charts_editing', '' );
	            update_user_meta( get_current_user_id(), 'pscw_current_editing_sc', '' );
            }
			update_user_meta( get_current_user_id(), 'pscw_sizechart_mode', '' );
		}

		return array( $product_sc_mode, $size_chart_id );
	}

	public function handle_sc_show_popup() {
		$position = $this->get_params( 'position' );
		/* Exception for none position */
		$exception = $position === 'none' ?? false;
		if ( $exception || $position === 'before_add_to_cart' || $position === 'before_atc_after_variations' || $position === 'after_add_to_cart' || $position === 'pop-up' || $position === 'after_title' || $position === 'after_meta' ) {
			add_action( 'wp_footer', array( $this, 'pscw_size_chart_popup' ) );
		}
	}

	public function show_sc_button() {
		if ( ! is_customize_preview() ) {
			list( $pscw_mode, $size_chart_id ) = $this->pscw_helper_show_size_chart( get_the_ID() );
			if ( $pscw_mode === 'disable' || empty( $size_chart_id ) ) {
				return;
			}
		}
		?>
        <div class="woo_sc_frontend_btn">
            <div id="woo_sc_show_popup"
                 class="woo_sc_price_btn_popup woo_sc_btn_popup woo_sc_btn_span woo_sc_call_popup">
                <div class="woo_sc_text_icon">
					<?php
					$size_chart_name = ! empty( $this->get_params( 'woo_sc_name' ) ) ? $this->get_params( 'woo_sc_name' ) : esc_html__( 'Size Chart', 'product-size-chart-for-woo' );
					switch ( $this->get_params( 'button_type' ) ) {
						case 'icon':
							?>
                            <span class="woo_sc_size_icon">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="<?php echo esc_html( pscw_get_view_box( $this->get_params( 'pscw_icon' ) ) ); ?>"
                                 width="30" height="30">

                            <?php
                            $response = wp_remote_get( PSCW_CONST_F['img_url'] . 'svg/' . $this->get_params( 'pscw_icon' ) . '.svg' );
                            if ( is_wp_error( $response ) ) {
	                            $file_content = '';
                            } else {
	                            $file_content = wp_remote_retrieve_body( $response );
                            }
                            echo wp_kses( $file_content, pscw_get_allow_svg() )
                            ?>
                            </svg>
                            </span>
							<?php
							break;
						case 'text':
							?>
                            <span class="woo_sc_text"><?php echo esc_html( $size_chart_name ); ?></span>
							<?php
							break;
					}

					?>
                </div>
            </div>
        </div>
		<?php
	}

	public function custom_product_tabs( $tabs ) {
		global $product;
		if ( ! empty( $this->get_params( 'woo_sc_name' ) ) ) {
			$title = $this->get_params( 'woo_sc_name' );
		} else {
			$title = esc_html__( 'Size Chart', 'product-size-chart-for-woo' );
		}
        list( $product_sc_mode, $size_charts_allow_countries ) = $this->pscw_helper_show_size_chart( $product->get_id(), true );
		if ( is_customize_preview() ) {
			if ( 'product_tabs' === $this->get_params( 'position' ) ) {
				$tabs['size_chart_tab'] = array(
					'title'    => esc_html( $title ),
					'priority' => apply_filters('woo_sc_size_chart_tab_priority', 50),
				);
			}
		} else {
			if ( ( ! empty( $size_charts_allow_countries ) && $product_sc_mode !== 'disable' ) ) {
				if ( 'product_tabs' === $this->get_params( 'position' ) ) {
					$tabs['size_chart_tab'] = array(
						'title'    => esc_html( $title ),
						'priority' => apply_filters('woo_sc_size_chart_tab_priority', 50),
						'callback' => function () use ( $size_charts_allow_countries ) {
							$this->custom_product_tabs_content( $size_charts_allow_countries );
						}
					);
				}
			}
		}

		return $tabs;
	}

	public function custom_product_tabs_content( $size_chart_id ) {
		if ( is_customize_preview() ) {
			return;
		}
		$is_multiple = $this->get_params( 'multi_sc' );
		if ( $is_multiple ) {
			foreach ( $size_chart_id as $sc_id ) {
				echo do_shortcode( '[PSCW_SIZE_CHART id=' . $sc_id . ']' );
			}
		} else {
			echo do_shortcode( '[PSCW_SIZE_CHART id=' . $size_chart_id[0] . ']' );
		}
	}

	public function pscw_size_chart_popup() {
		$product_id     = get_the_ID();
		list( $product_sc_mode, $size_chart_id ) = $this->pscw_helper_show_size_chart( $product_id, true );
		if ( ! is_customize_preview() ) {
			/* Only display for customize*/
			$position = $this->get_params( 'position' );
			if ($position === 'none') return;

			if ( ! ( ( ! empty( $size_chart_id ) && $product_sc_mode !== 'disable' ) ) ) {
				return;
			}
		}
		if ( ! is_product() ) {
			return;
		}

		?>
        <div id="woo_sc_modal" class="woo_sc_modal">
            <div class="woo_sc_modal_content">
                <span class="woo_sc_modal_close">&times;</span>
                <div class="woo_sc_scroll_content">
					<?php
					if ( ! is_customize_preview() ) {
						if ( isset( $size_chart_id[0] ) ) {
							echo do_shortcode( '[PSCW_SIZE_CHART id=' . $size_chart_id[0] . ']' );
						}
					}
					?>
                </div>
            </div>
        </div>
		<?php
	}


	public function get_params( $param ) {
		return $this->settings->get_params( $param );
	}

}