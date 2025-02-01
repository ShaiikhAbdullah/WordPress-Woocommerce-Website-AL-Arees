<?php

namespace PSCWF\Admin;

use PSCWF\Inc\Data;

defined( 'ABSPATH' ) || exit;

class Settings {
	protected static $instance = null;
	protected static $setting;

	private function __construct() {
		self::$setting = Data::get_instance();
		add_action( 'admin_menu', array( $this, 'pswc_setting_page' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self() : self::$instance;
	}

	public function pswc_setting_page() {
		add_submenu_page( 'edit.php?post_type=pscw-size-chart',
			esc_html__( 'Size Chart', 'product-size-chart-for-woo' ),
			esc_html__( 'Settings', 'product-size-chart-for-woo' ),
			'manage_options',
			'pscw-size-chart-setting',
			array( $this, 'page_callback' ), 2 );

		add_submenu_page(
			'',
			esc_html__( 'Setup Wizard', 'product-size-chart-for-woo' ),
			esc_html__( 'Setup Wizard', 'product-size-chart-for-woo' ),
			'manage_options',
			'pscw-setup',
			'__return_false'
		);
	}

	public function save_settings() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ! isset( $_POST['woo_sc_setting_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woo_sc_setting_nonce'] ) ), 'woo_sc_check_setting_nonce' ) ) {
			return;
		}
		global $woopscw_settings;

		if ( isset( $_POST['woo_sc_save_setting'] ) ) {
			if ( ! empty( $_POST['enable'] ) ) {
				$save_option['enable'] = "1";
			} else {
				$save_option['enable'] = "0";
			}
			if ( ! empty( $_POST['woo_sc_multi_sc'] ) ) {
				$save_option['multi_sc'] = "1";
			} else {
				$save_option['multi_sc'] = "0";
			}
			if ( ! empty( $_POST['woo_cs_select_position'] ) ) {
				$save_option['position'] = sanitize_text_field( wp_unslash( $_POST['woo_cs_select_position'] ) );
			}
			if ( ! empty( $_POST['woo_sc_btn_horizontal'] ) ) {
				$save_option['btn_horizontal'] = sanitize_text_field( wp_unslash( $_POST['woo_sc_btn_horizontal'] ) );
			}
			if ( isset( $_POST['woo_sc_btn_vertical'] ) && $_POST['woo_sc_btn_vertical'] <= 95 && $_POST['woo_sc_btn_vertical'] >= 0 ) {
				$save_option['btn_vertical'] = sanitize_text_field( wp_unslash( $_POST['woo_sc_btn_vertical'] ) );
			}
			if ( isset( $_POST['woo_sc_name'] ) ) {
				$save_option['woo_sc_name'] = sanitize_text_field( wp_unslash( $_POST['woo_sc_name'] ) );
			}
			if ( ! empty( $_POST['woo_sc_button_type'] ) ) {
				$save_option['button_type'] = sanitize_text_field( wp_unslash( $_POST['woo_sc_button_type'] ) );
			}
			if ( ! empty( $_POST['woo_sc_btn_color'] ) ) {
				$save_option['btn_color'] = sanitize_text_field( wp_unslash( $_POST['woo_sc_btn_color'] ) );
			}
			if ( ! empty( $_POST['woo_sc_text_color'] ) ) {
				$save_option['text_color'] = sanitize_text_field( wp_unslash( $_POST['woo_sc_text_color'] ) );
			}
			if ( isset( $_POST['woo_sc_textarea'] ) ) {
				$save_option['custom_css'] = sanitize_textarea_field( wp_unslash( $_POST['woo_sc_textarea'] ) );
			}
			if ( ! empty( $_POST['pscw_icon'] ) ) {
				$save_option['pscw_icon'] = sanitize_textarea_field( wp_unslash( $_POST['pscw_icon'] ) );
			}

			$data_r = wp_parse_args( $save_option, self::$setting->get_params() );
			update_option( 'woo_sc_setting', $data_r );
			$woopscw_settings = $data_r;
			self::$setting    = Data::get_instance( true );
		}
	}

	public function page_callback() {
		$get_option = self::$setting->get_params();
		?>
        <div class="wrap woo_sc_space">
            <div class="woo_sc_title">
                <h2><?php esc_html_e( 'General settings', 'product-size-chart-for-woo' ) ?></h2>
            </div>
            <form method="post" class="vi-ui form">
                <div class="vi-ui segment woo_sc_setting_form">
					<?php wp_nonce_field( 'woo_sc_check_setting_nonce', 'woo_sc_setting_nonce' ); ?>
                    <table class="woo_sc_options_table form-table">
                        <tr>
                            <th>
                                <div>
									<?php esc_html_e( 'Enable', 'product-size-chart-for-woo' ) ?>
                                </div>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox"
                                           name="enable" <?php checked( $get_option['enable'] ); ?> >
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <div>
									<?php esc_html_e( 'Size chart type', 'product-size-chart-for-woo' ) ?>
                                </div>
                            </th>
                            <td>
                                <select name="woo_cs_select_position" id="woo_cs_select_position"
                                        class="vi-ui fluid dropdown setting_field">
                                    <option value="before_add_to_cart" <?php selected( $get_option['position'] == 'before_add_to_cart' ); ?>><?php esc_html_e( 'Before add to cart', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="after_add_to_cart" <?php selected( 'after_add_to_cart' == $get_option['position'] ); ?> ><?php esc_html_e( 'After add to cart', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="pop-up" <?php selected( 'pop-up' == $get_option['position'] ); ?> ><?php esc_html_e( 'Pop-up', 'product-size-chart-for-woo' ) ?></option>
                                    <option value="product_tabs" <?php selected( 'product_tabs' == $get_option['position'] ); ?>><?php esc_html_e( 'Product tab', 'product-size-chart-for-woo' ) ?></option>
                                    <option value="none" <?php selected( 'none' === $get_option['position'] ); ?>><?php esc_html_e( 'None', 'product-size-chart-for-woo' ) ?></option>
                                    <option value="before_atc_after_variations" disabled><?php esc_html_e( 'Before add to cart after variations (Premium)', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="top_des" disabled><?php esc_html_e( 'Top description (Premium)', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="bottom_des" disabled><?php esc_html_e( 'Bottom description (Premium)', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="after_title" disabled><?php esc_html_e( 'After title (Premium)', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="after_meta" disabled><?php esc_html_e( 'After the meta (Premium)', 'product-size-chart-for-woo' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="woo_sc_multi" style="display: none">
                            <th>
								<?php esc_html_e( 'Multi size charts', 'product-size-chart-for-woo' ) ?>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox"
                                           name="woo_sc_multi_sc" <?php checked( $get_option['multi_sc'] ); ?>>
                                    <label></label>
                                </div>
                                <div class="woo_sc_msg">
                                    <span><?php esc_html_e( 'When enabled, all size charts assigned to a product will be shown. If disabled, only the first size chart appears.', 'product-size-chart-for-woo' ) ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr class="woo_sc_btn_popup_position" style="display: none">
                            <th>
								<?php esc_html_e( 'Horizontal', 'product-size-chart-for-woo' ); ?>
                            </th>
                            <td>
                                <select name="woo_sc_btn_horizontal"
                                        class="vi-ui fluid dropdown selection setting_field ">
                                    <option value="right" <?php selected( "right" === $get_option['btn_horizontal'] ); ?>>
										<?php esc_html_e( 'Right', 'product-size-chart-for-woo' ) ?></option>
                                    <option value="left" <?php selected( "left" === $get_option['btn_horizontal'] ); ?>>
										<?php esc_html_e( 'Left', 'product-size-chart-for-woo' ) ?></option>
                                </select>
                                <div class="woo_sc_msg">
                                    <span> <?php esc_html_e( 'Popup button position in horizontal', 'product-size-chart-for-woo' ); ?> </span>
                                </div>

                            </td>
                        </tr>
                        <tr class="woo_sc_btn_popup_position" style="display: none">
                            <th>
								<?php esc_html_e( 'Vertical', 'product-size-chart-for-woo' ); ?>
                            </th>
                            <td>
                                <div class="vi-ui right labeled input">
                                    <input type="number" min="0" max="100" name="woo_sc_btn_vertical"
                                           id="woo_sc_btn_vertical"
                                           value="<?php echo esc_attr( $get_option['btn_vertical'] ); ?>">
                                    <div class="vi-ui basic label">
                                        %
                                    </div>
                                </div>
                                <div class="woo_sc_msg">
                                    <span><?php esc_html_e( 'Popup button position in vertical', 'product-size-chart-for-woo' ); ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr class="woo_sc_btn_type">
                            <th>
                                <div>
                                    <label><?php esc_html_e( 'Button type', 'product-size-chart-for-woo' ); ?></label>
                                </div>
                            </th>
                            <td>
                                <select name="woo_sc_button_type" class="vi-ui fluid dropdown selection setting_field"
                                        id="woo_sc_type_btn">
                                    <option value="text" <?php selected( "text" === $get_option['button_type'] ); ?>><?php esc_html_e( 'Text', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="icon" <?php selected( "icon" === $get_option['button_type'] ); ?>>
										<?php esc_html_e( 'Icon', 'product-size-chart-for-woo' ); ?></option>
                                    <option value="icon_text"><?php esc_attr_e( 'Icon & Text', 'product-size-chart-for-woo' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="woo_sc_sc_label" style="display: none">
                            <th>
                                <div>
									<?php esc_html_e( 'Size chart label', 'product-size-chart-for-woo' ) ?>
                                </div>
                            </th>
                            <td>
                                <input type="text" name="woo_sc_name" class="vi-ui setting_field"
                                       placeholder="<?php esc_html_e( 'Size Chart', 'product-size-chart-for-woo' ); ?>"
                                       value="<?php echo esc_attr( $get_option['woo_sc_name'] ); ?>">
                                <div class="woo_sc_msg">
                                    <span> <?php esc_html_e( 'Label for size chart on front end', 'product-size-chart-for-woo' ); ?> </span>
                                </div>
                            </td>
                        </tr>
                        <tr class="woo_sc_sc_icon" style="display: none">
                            <th>
                            </th>
                           <td colspan="2">
                                <a class="vi-ui button" target="_blank" href="https://1.envato.market/zN1kJe">Upgrade This Feature</a>
                           </td>
                        </tr>
                        <tr class="woo_sc_btn_color" style="display: none">
                            <th>
                                <div>
									<?php esc_html_e( 'Button background color', 'product-size-chart-for-woo' ); ?>
                                </div>
                            </th>
                            <td class="woo_sc_div_btn_color">
                                <input type="text" class="color-picker" id="woo_sc_btn_color"
                                       name="woo_sc_btn_color"
                                       autocomplete="off"
                                       value="<?php echo esc_attr( $get_option['btn_color'] ) ?>"
                                       style="background-color: <?php echo esc_attr( $get_option['btn_color'] ); ?>">
                            </td>
                        </tr>
                        <tr class="woo_sc_btn_color" style="display: none">
                            <th>
                                <div>
									<?php esc_html_e( 'Button color', 'product-size-chart-for-woo' ) ?>
                                </div>
                            </th>
                            <td>
                                <input type="text" class="color-picker" id="woo_sc_text_color"
                                       name="woo_sc_text_color"
                                       autocomplete="off"
                                       value="<?php echo esc_attr( $get_option['text_color'] ) ?>"
                                       style="background-color:<?php echo esc_attr( $get_option['text_color'] ) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>
								<?php esc_html_e( 'Custom CSS', 'product-size-chart-for-woo' ) ?>
                            </th>
                            <td>
                                    <textarea name="woo_sc_textarea" id="woo_sc_textarea" cols="30" rows="5"
                                              placeholder="<?php esc_html_e( 'Insert custom css here...', 'product-size-chart-for-woo' ); ?>"><?php if ( ! empty( $get_option['custom_css'] ) ) {
		                                    echo esc_attr( $get_option['custom_css'] );
	                                    } ?></textarea>
								<?php
								?>
                            </td>
                        </tr>
                    </table>
                    <div class="get_short_code" style="display: none">
                        <p><?php esc_html_e( 'Shortcode can be inserted to the content of page or post then converted to HTML on corresponding page or post', 'product-size-chart-for-woo' ) ?></p>
                        <p><?php esc_html_e( 'You can take the shortcode in "All size charts" section.', 'product-size-chart-for-woo' ) ?></p>
                    </div>
                    <p>
                        <button type="submit" name="woo_sc_save_setting" id="woo_sc_btn_save_setting"
                                class="vi-ui primary button">
                            <i class="icon send"></i><?php esc_attr_e( 'Save', 'product-size-chart-for-woo' ) ?>
                        </button>
                    </p>
                </div>
            </form>
			<?php
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			do_action( 'villatheme_support_product-size-chart-for-woocommerce' );
			?>
        </div>
		<?php


	}
}