<?php

namespace PSCWF\Inc;

defined( 'ABSPATH' ) || exit;

class Setup_Wizard {
	protected static $instance = null;
	protected $default_template_posts_title;

	public function __construct() {
		global $title;
		$title                              = ''; // Fix PHP Deprecated:  strip_tags(): Passing null to parameter #1 ($string) of type string is deprecated
		$this->default_template_posts_title = array(
			'hoodie'       => esc_html__( "Hoodie Size Chart", 'product-size-chart-for-woo' ),
			'women_shoe'   => esc_html__( "Women's Shoe Size Chart", 'product-size-chart-for-woo' ),
			'men_shoe'     => esc_html__( "Men's Shoe Size Chart", 'product-size-chart-for-woo' ),
			'pet_clothing' => esc_html__( "Pet Clothing Size Chart", 'product-size-chart-for-woo' ),
			'dress'        => esc_html__( "Dress Size Chart", 'product-size-chart-for-woo' ),
			't_shirt'      => esc_html__( "T-Shirt Size Chart", 'product-size-chart-for-woo' ),
		);
		add_action( 'admin_head', [ $this, 'setup_wizard' ] );
		add_action( 'wp_ajax_pscw_setup_wizard', array( $this, 'pscw_ajax_setup_wizard' ) );
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self() : self::$instance;
	}

	public function setup_wizard() {
		$screen_id = get_current_screen()->id;
		if ( $screen_id !== 'dashboard_page_pscw-setup' ) {
			return;
		}
		delete_option( 'pscw_setup_wizard' );
		$size_charts = $this->default_template_posts_title;
		?>
        <div class="pscw-setup-wizard-container">
            <div>
                <h2 class="vi-ui header center aligned"><?php esc_html_e( 'PSCW - Product Size Chart For WooCommerce', 'product-size-chart-for-woo' ); ?></h2>
                <div class="vi-ui steps">
                    <div class="step active" data-step="1">
                        <i class="icon">1</i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Position Size Chart', 'product-size-chart-for-woo' ); ?></div>
                            <div class="description"><?php esc_html_e( 'Choose where to display the size chart on the product page', 'product-size-chart-for-woo' ); ?></div>
                        </div>
                    </div>
                    <div class="step" data-step="2">
                        <i class="icon">2</i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Size Chart', 'product-size-chart-for-woo' ); ?></div>
                            <div class="description"><?php esc_html_e( 'Select the size chart to display', 'product-size-chart-for-woo' ); ?></div>
                        </div>
                    </div>
                </div>

                <div class="vi-ui segment">
                    <div class="pscw-segment-container active">
                        <select name="" id="pscw-setup-select-position"
                                class="vi-ui fluid dropdown selection">
                            <option value="before_add_to_cart"><?php esc_html_e( 'Before add to cart', 'product-size-chart-for-woo' ); ?></option>
                            <option value="after_add_to_cart"><?php esc_html_e( 'After add to cart', 'product-size-chart-for-woo' ); ?></option>
                            <option value="pop-up"><?php esc_html_e( 'Pop-up', 'product-size-chart-for-woo' ) ?></option>
                            <option value="product_tabs" selected><?php esc_html_e( 'Product tab', 'product-size-chart-for-woo' ) ?></option>
                            <option value="none" ><?php esc_html_e( 'None', 'product-size-chart-for-woo' ) ?></option>
                           <option value="before_atc_after_variations" disabled><?php esc_html_e( 'Before add to cart after variations (Premium)', 'product-size-chart-for-woo' ); ?></option>
                            <option value="top_des" disabled><?php esc_html_e( 'Top description (Premium)', 'product-size-chart-for-woo' ); ?></option>
                            <option value="bottom_des" disabled><?php esc_html_e( 'Bottom description (Premium)', 'product-size-chart-for-woo' ); ?></option>
                            <option value="after_title" disabled><?php esc_html_e( 'After title (Premium)', 'product-size-chart-for-woo' ); ?></option>
                            <option value="after_meta" disabled><?php esc_html_e( 'After the meta (Premium)', 'product-size-chart-for-woo' ); ?></option>
                        </select>

                        <div class="vi-ui primary button"
                             id="pscw-setup-next"><?php esc_html_e( 'Next', 'product-size-chart-for-woo' ); ?></div>
                    </div>

                    <div class="pscw-segment-container">
                        <div class="pscw-setup-checkboxs">
							<?php foreach ( $size_charts as $sc_k => $sc_val ) : ?>
                                <div class="vi-ui checkbox">
                                    <input type="checkbox" class="hidden" name="pscw-selected-size-chart"
                                           value="<?php echo esc_attr( $sc_k ); ?>" checked>
                                    <label for="">
										<?php echo esc_html( $sc_val ); ?>
                                    </label>
                                </div>
							<?php endforeach; ?>
                        </div>
                        <div class="vi-ui primary button"
                             id="pscw-setup-finished"><?php esc_html_e( 'Finish', 'product-size-chart-for-woo' ); ?></div>
                    </div>

                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pscw-size-chart&page=pscw-size-chart-setting' ) ); ?>"
                       class="pscw-settings-link">
						<?php esc_html_e( 'Not now. Go to Settings page.', 'product-size-chart-for-woo' ) ?>
                    </a>
                </div>

            </div>
        </div>
		<?php
	}

	public function pscw_ajax_setup_wizard() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'pscw_nonce' ) ) {
			$selected_position    = isset( $_POST['selected_position'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_position'] ) ) : 'product_tabs';
			$selected_size_charts = isset( $_POST['selected_size_charts'] ) ? wc_clean( wp_unslash( $_POST['selected_size_charts'] ) ) : [];

			$sc_option = get_option( 'woo_sc_setting', array() );
			if ( isset( $sc_option['position'] ) ) {
				$sc_option['position'] = $selected_position;
				$sc_option['multi_sc'] = '1';
				update_option( 'woo_sc_setting', $sc_option );
			}

			$this->default_template( $selected_size_charts );

			$random_product      = wc_get_products( array(
				'type'    => array( 'simple', 'variable' ),
				'orderby' => 'rand',
				'return'  => 'ids',
				'limit'   => 1
			) );
			$data['productLink'] = get_permalink( $random_product[0] );
			wp_send_json_success( $data );
		}
		wp_die();
	}

	public function default_template( $selected_size_charts ) {
		if ( empty( $default_template_option ) ) {
			$t_shirt_img = PSCW_CONST_F['img_url'] . 'template/t-shirt.png';
			$hoodie_img  = PSCW_CONST_F['img_url'] . 'template/hoodie.png';
			$foot_img    = PSCW_CONST_F['img_url'] . 'template/foot.png';
			$pet_img     = PSCW_CONST_F['img_url'] . 'template/dog-3.png';
			$dress_img   = PSCW_CONST_F['img_url'] . 'template/dress.png';


			$default_template_interface = array(
				'hoodie'       => '{"layout":{"type":"container","children":["pscw-row-ID_1724125267262"]},"elementsById":{"pscw-col-ID_1724125267263":{"id":"pscw-col-ID_1724125267263","class":"pscw-col-l-12","type":"column","parent":"pscw-row-ID_1724125267262","children":["pscw-image-ID_1724125344627","pscw-table-ID_1726654859042"],"settings":{"class":"pscw-customize-col-12"}},"pscw-row-ID_1724125267262":{"children":["pscw-col-ID_1724125267263"],"id":"pscw-row-ID_1724125267262","type":"row"},"pscw-image-ID_1724125344627":{"id":"pscw-image-ID_1724125344627","type":"image","parent":"pscw-col-ID_1724125267263","alt":"","borderColor":"#000000","borderStyle":"solid","borderWidth":0,"height":350,"heightUnit":"px","width":100,"widthUnit":"%","src":"' . $hoodie_img . '","padding":[0,0,0,0],"margin":[0,0,0,0],"objectFit":"contain"},"pscw-table-ID_1726654859042":{"id":"pscw-table-ID_1726654859042","type":"table","parent":"pscw-col-ID_1724125267263","columns":["SIZE (US)","WIDTH","LENGTH","SLEEVE LENGTH","SIZE TOLERANCE"],"rows":[["S","20.08","27.17","33.50","1.50"],["M","22.05","27.95","34.50","1.50"],["L","24.02","29.13","35.50","1.50"],["XL","25.98","29.92","36.50","1.50"],["2XL","27.99","31.10","37.50","1.50"],["3XL","29.92","31.89","38.50","1.50"],["4XL","31.89","33.07","39.50","1.50"],["5XL","33.86","33.86","40.50","1.50"]],"headerColumn":"row","headerBackground":"#ff801f","textHeader":"#ffffff","headerTextBold":true,"headerTextSize":14,"columnsStyle":false,"evenBackground":"#fff9f4","evenText":"#494949","oddBackground":"#fff2e8","oddText":"#494949","borderColor":"#ff801f","cellTextSize":14,"horizontalBorderWidth":"0","horizontalBorderStyle":"solid","verticalBorderWidth":"0","verticalBorderStyle":"solid","margin":[0,0,0,0],"borderRadius":[0,0,0,0],"tableMaxHeight":535}}}',
				'women_shoe'   => '{"layout":{"type":"container","children":["pscw-row-ID_1726715272085"]},"elementsById":{"pscw-col-ID_1726715272086":{"id":"pscw-col-ID_1726715272086","class":"pscw-col-l-12","type":"column","parent":"pscw-row-ID_1726715272085","children":["pscw-image-ID_1726715274119","pscw-table-ID_1726715277461"],"settings":{"class":"pscw-customize-col-12"}},"pscw-row-ID_1726715272085":{"children":["pscw-col-ID_1726715272086"],"id":"pscw-row-ID_1726715272085","type":"row"},"pscw-image-ID_1726715274119":{"id":"pscw-image-ID_1726715274119","type":"image","parent":"pscw-col-ID_1726715272086","alt":"","borderColor":"#000000","borderStyle":"solid","borderWidth":0,"height":333,"heightUnit":"px","width":100,"widthUnit":"%","src":"' . $foot_img . '","padding":[0,0,0,0],"margin":["0","0","16","0"],"objectFit":"contain"},"pscw-table-ID_1726715277461":{"id":"pscw-table-ID_1726715277461","type":"table","parent":"pscw-col-ID_1726715272086","columns":["US Size","EU Size","UK Size","Foot Length (inches)","Foot Length (cm)"],"rows":[["5","35","2.5","8.5","21.6"],["5.5","35.5","3","8.75","22.2"],["6","36","3.5","8.875","22.5"],["6.5","37","4","9","23"],["7","37.5","4.5","9.25","23.5"],["7.5","38","5","9.375","23.8"],["8","38.5","5.5","9.5","24.1"],["8.5","39","6","9.75","24.6"],["9","40","6.5","9.875","25"],["9.5","40.5","7","10","25.4"],["10","41","7.5","10.25","25.9"],["10.5","42","8","10.375","26.2"],["11","42.5","8.5","10.5","26.7"],["11.5","43","9","10.75","27.3"],["12","44","9.5","10.875","27.6"]],"headerColumn":"row","headerBackground":"#456e68","textHeader":"#ffffff","headerTextBold":true,"headerTextSize":14,"columnsStyle":false,"evenBackground":"#eafbf8","evenText":"#494949","oddBackground":"#d2f1ed","oddText":"#494949","borderColor":"#456e68","cellTextSize":14,"horizontalBorderWidth":1,"horizontalBorderStyle":"solid","verticalBorderWidth":1,"verticalBorderStyle":"solid","margin":[0,0,0,0],"borderRadius":[0,0,0,0],"tableMaxHeight":"700"}}}',
				'men_shoe'     => '{"layout":{"type":"container","children":["pscw-row-ID_1726710772479"]},"elementsById":{"pscw-col-ID_1726710772480":{"id":"pscw-col-ID_1726710772480","class":"pscw-col-l-12","type":"column","parent":"pscw-row-ID_1726710772479","children":["pscw-image-ID_1726710780260","pscw-table-ID_1726710783136"],"settings":{"class":"pscw-customize-col-12"}},"pscw-row-ID_1726710772479":{"children":["pscw-col-ID_1726710772480"],"id":"pscw-row-ID_1726710772479","type":"row"},"pscw-image-ID_1726710780260":{"id":"pscw-image-ID_1726710780260","type":"image","parent":"pscw-col-ID_1726710772480","alt":"","borderColor":"#000000","borderStyle":"solid","borderWidth":0,"height":333,"heightUnit":"px","width":100,"widthUnit":"%","src":"' . $foot_img . '","padding":[0,0,0,0],"margin":["0","0","16","0"],"objectFit":"contain"},"pscw-table-ID_1726710783136":{"id":"pscw-table-ID_1726710783136","type":"table","parent":"pscw-col-ID_1726710772480","columns":["U.S. Size","U.K. Size","EU Size","Foot Length (Inches)","Foot Length (cm)"],"rows":[["6","5.5","39","9.25","23.5"],["6.5","6","39.5","9.5","24.1"],["7","6.5","40","9.625","24.4"],["7.5","7","40.5","9.75","24.8"],["8","7.5","41","9.9375","25.4"],["8.5","8","42","10.125","25.7"],["9","8.5","42.5","10.25","26.0"],["9.5","9","43","10.4375","26.7"],["10","9.5","44","10.5625","27.0"],["10.5","10","44.5","10.75","27.3"],["11","10.5","45","10.9375","27.9"],["11.5","11","45.5","11.125","28.3"],["12","11.5","46","11.25","28.6"],["13","12.5","47","11.5625","29.4"]],"headerColumn":"row","headerBackground":"#323232","textHeader":"#ffffff","headerTextBold":true,"headerTextSize":14,"columnsStyle":false,"evenBackground":"#f8f8f8","evenText":"#494949","oddBackground":"#eaeaea","oddText":"#494949","borderColor":"#323232","cellTextSize":14,"horizontalBorderWidth":1,"horizontalBorderStyle":"solid","verticalBorderWidth":1,"verticalBorderStyle":"solid","margin":[0,0,0,0],"borderRadius":[0,0,0,0],"tableMaxHeight":"656"}}}',
				'pet_clothing' => '{"layout":{"type":"container","children":["pscw-row-ID_1726711033581"]},"elementsById":{"pscw-col-ID_1726711033582":{"id":"pscw-col-ID_1726711033582","class":"pscw-col-l-12","type":"column","parent":"pscw-row-ID_1726711033581","children":["pscw-image-ID_1726711035983","pscw-table-ID_1726711091293"],"settings":{"class":"pscw-customize-col-12"}},"pscw-row-ID_1726711033581":{"children":["pscw-col-ID_1726711033582"],"id":"pscw-row-ID_1726711033581","type":"row"},"pscw-image-ID_1726711035983":{"id":"pscw-image-ID_1726711035983","type":"image","parent":"pscw-col-ID_1726711033582","alt":"","borderColor":"#000000","borderStyle":"solid","borderWidth":0,"height":333,"heightUnit":"px","width":100,"widthUnit":"%","src":"' . $pet_img . '","padding":[0,0,0,0],"margin":["0","0","16","0"],"objectFit":"contain"},"pscw-table-ID_1726711091293":{"id":"pscw-table-ID_1726711091293","type":"table","parent":"pscw-col-ID_1726711033582","columns":["SIZE","LENGTH","HEIGHT","COLLAR WIDTH"],"rows":[["M","10.00","7.24","5.37"],["L","12.00","8.23","6.50"],["XL","15.50","9.25","9.00"]],"headerColumn":"row","headerBackground":"#ffca0a","textHeader":"#ffffff","headerTextBold":true,"headerTextSize":14,"columnsStyle":false,"evenBackground":"#fffcf2","evenText":"#494949","oddBackground":"#fff9e3","oddText":"#494949","borderColor":"#ffca0a","cellTextSize":14,"horizontalBorderWidth":1,"horizontalBorderStyle":"solid","verticalBorderWidth":1,"verticalBorderStyle":"solid","margin":[0,0,0,0],"borderRadius":[0,0,0,0],"tableMaxHeight":535}}}',
				'dress'        => '{"layout":{"type":"container","children":["pscw-row-ID_1726711278920"]},"elementsById":{"pscw-col-ID_1726711278921":{"id":"pscw-col-ID_1726711278921","class":"pscw-col-l-12","type":"column","parent":"pscw-row-ID_1726711278920","children":["pscw-image-ID_1726711281860","pscw-table-ID_1726711283739"],"settings":{"class":"pscw-customize-col-12"}},"pscw-row-ID_1726711278920":{"children":["pscw-col-ID_1726711278921"],"id":"pscw-row-ID_1726711278920","type":"row"},"pscw-image-ID_1726711281860":{"id":"pscw-image-ID_1726711281860","type":"image","parent":"pscw-col-ID_1726711278921","alt":"","borderColor":"#000000","borderStyle":"solid","borderWidth":0,"height":333,"heightUnit":"px","width":100,"widthUnit":"%","src":"' . $dress_img . '","padding":[0,0,0,0],"margin":["0","0","16","16"],"objectFit":"contain"},"pscw-table-ID_1726711283739":{"id":"pscw-table-ID_1726711283739","type":"table","parent":"pscw-col-ID_1726711278921","columns":["SIZE","CHEST WIDTH","LENGTH"],"rows":[["XS","15.00","35.00"],["S","16.00","36.0"],["M","17.50","36.50"],["L","18.50","36.50"],["XL","20.50","36.50"],["2XL","22.50","37.00"]],"headerColumn":"row","headerBackground":"#f3a5ad","textHeader":"#ffffff","headerTextBold":true,"headerTextSize":14,"columnsStyle":false,"evenBackground":"#fffcfc","evenText":"#494949","oddBackground":"#fff0f0","oddText":"#494949","borderColor":"#ffffff","cellTextSize":14,"horizontalBorderWidth":"0","horizontalBorderStyle":"solid","verticalBorderWidth":"0","verticalBorderStyle":"solid","margin":[0,0,0,0],"borderRadius":[0,0,0,0],"tableMaxHeight":535}}}',
				't_shirt'      => '{"layout":{"type":"container","children":["pscw-row-ID_1724316618201"]},"elementsById":{"pscw-col-ID_1724316618202":{"id":"pscw-col-ID_1724316618202","class":"pscw-col-l-12","type":"column","parent":"pscw-row-ID_1724316618201","children":["pscw-image-ID_1726472854134","pscw-table-ID_1724316630077"],"settings":{"class":"pscw-customize-col-12"}},"pscw-row-ID_1724316618201":{"children":["pscw-col-ID_1724316618202"],"id":"pscw-row-ID_1724316618201","type":"row"},"pscw-table-ID_1724316630077":{"id":"pscw-table-ID_1724316630077","type":"table","parent":"pscw-col-ID_1724316618202","columns":["SIZE (US)","WIDTH","LENGTH","SLEEVE LENGTH"],"rows":[["S","18.0","28.0","15.1"],["M","20.0","29.0","16.5"],["L","22.0","30.0","18.0"],["XL","24.0","31.0","19.5"],["2XL","26.0","32.0","21.0"],["3XL","28.0","33.0","22.4"],["4XL","30.0","34.0","23.7"],["5XL","32.0","35.0","25.0"]],"headerColumn":false,"headerBackground":"#f6f6f6","textHeader":"#0a0a0a","headerTextBold":true,"headerTextSize":14,"columnsStyle":false,"evenBackground":"#ffffff","evenText":"#000000","oddBackground":"#f6f6f6","oddText":"#000000","borderColor":"#cccccc","cellTextSize":14,"horizontalBorderWidth":"0","horizontalBorderStyle":"solid","verticalBorderWidth":"0","verticalBorderStyle":"solid","margin":[0,0,0,0],"borderRadius":[0,0,0,0]},"pscw-image-ID_1726472854134":{"id":"pscw-image-ID_1726472854134","type":"image","parent":"pscw-col-ID_1724316618202","alt":"","borderColor":"#000000","borderStyle":"solid","borderWidth":0,"height":360,"heightUnit":"px","width":100,"widthUnit":"%","src":"' . $t_shirt_img . '","padding":[0,0,0,0],"margin":[0,0,0,0],"objectFit":"contain"}}}',
			);

			$user_id = get_current_user_id();
			foreach ( $this->default_template_posts_title as $default_template_posts_key => $default_template_posts_value ) {
				if ( in_array( $default_template_posts_key, $selected_size_charts ) ) {
					$post_args = array(
						'post_author' => $user_id,
						'post_type'   => 'pscw-size-chart',
						'post_status' => 'publish',
						'post_title'  => $default_template_posts_value,
					);
					$post_id   = wp_insert_post( $post_args );
					if ( 0 !== $post_id ) {
						update_post_meta( $post_id, 'pscw_data', array(
							'assign'          => 'all',
							'allow_countries' => [],
							'condition'       => []
						) );
						update_post_meta( $post_id, 'pscw_list_product', array() );
						update_post_meta( $post_id, 'pscw_interface', json_decode( wp_unslash( $default_template_interface[ $default_template_posts_key ] ), true ) );
					}
				}
			}

		}
	}
}