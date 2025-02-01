<?php

namespace PSCWF\Admin;

defined( 'ABSPATH' ) || exit;

class Size_Chart {
	protected static $instance = null;

	private function __construct() {
		add_action( 'init', array( $this, 'pscw_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'pscw_meta_box' ) );
		add_action( 'save_post', array( $this, 'pscw_save_data_meta_box' ) );
		add_action( 'post_action_pscw_duplicate', array( $this, 'pscw_duplicate' ) );
		add_action( 'post_action_pscw_go_design', array( $this, 'pscw_go_design' ) );
		add_action( 'before_delete_post', array( $this, 'pscw_before_delete_size_chart' ), 99, 2 );
		add_filter( 'post_row_actions', array( $this, 'post_add_action' ), 20, 2 );
		add_filter( 'manage_pscw-size-chart_posts_columns', array( $this, 'custom_post_columns' ) );
		add_action( 'manage_pscw-size-chart_posts_custom_column', array( $this, 'show_custom_columns' ) );
		add_action( 'wp_ajax_pscw_search_product', array( $this, 'pscw_ajax_search_product' ) );
		add_action( 'wp_ajax_pscw_search_term', array( $this, 'pscw_ajax_search_term' ) );

		/* Button repair migrate data */
		add_action( 'manage_posts_extra_tablenav', array( $this, 'button_migrate_all_data' ) );
		add_action( 'wp_ajax_pscw_migrate_data', array( $this, 'migrate_size_chart' ) );
		add_action( 'admin_notices', array( $this, 'notice_migrate_size_chart'), 9999 );

		/* Go to Customize when add new size chart*/
		add_action( 'load-post-new.php', array( $this, 'add_new_size_chart' ) );
	}

	public function button_migrate_all_data( $type ) {
		global $typenow;

		if ( $type === 'top' && $typenow === 'pscw-size-chart' ) {
			printf( '<div class="button" id="pscw_migrate_all_data">%s</div>', esc_html__( 'Remigrate size chart', 'product-size-chart-for-woo' ) );
		}
	}

	public function migrate_size_chart() {
		if (isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'pscw_nonce' )) {
			/* Filter query size chart to migrate */
			$size_charts = get_posts(apply_filters('pscw_migrate_size_chart_query', array(
					'post_type'  => 'pscw-size-chart',
					'numberposts' => - 1,
					'meta_query' => array(
						array(
							'key'     => 'woo_sc_size_chart_data',
							'compare' => 'EXISTS',
						),
					)
				))
			);

			foreach( $size_charts as $size_chart ) {
				$woo_sc_size_chart_data = get_post_meta( $size_chart->ID, 'woo_sc_size_chart_data', true );
				pscw_migrate_size_chart_data($size_chart, $woo_sc_size_chart_data);
			}

			wp_send_json_success();
		}
		die;
	}

	public function notice_migrate_size_chart() {
		$screen = get_current_screen();
		if ( $screen->id === 'edit-pscw-size-chart' ) {
			?>
            <div class="notice notice-info">
                <p><?php _e( 'We’ve added a new option to easily re-migrate your Size Chart table from version 1.x to 2.x, improving data accuracy and consistency', 'product-size-chart-for-woo' ); ?></p>
            </div>
			<?php
		}
	}
	public function add_new_size_chart() {
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		if ( $post_type === 'pscw-size-chart' ) {
			$this->default_size_chart_interface();
			$user_id  = get_current_user_id();
			$new_post = array(
				'post_title'   => 'Size chart untitled',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'pscw-size-chart',
				'post_author'  => $user_id,
			);

			$post_id = wp_insert_post( $new_post );
			if ( 0 !== $post_id ) {
				update_post_meta( $post_id, 'pscw_data', array(
					'assign'          => 'all',
					'allow_countries' => [],
					'condition'       => []
				) );
				update_post_meta( $post_id, 'pscw_list_product', array() );
				update_post_meta( $post_id, 'pscw_interface', $this->default_size_chart_interface() );

				$random_product = $this->get_random_product();
				$url            = admin_url( 'customize.php' ) . '?url=' . esc_url( get_permalink( $random_product[0] ) ) . '&pscw_id=' . $post_id . '&autofocus[panel]=pscw_size_chart_customizer&autofocus[section]=pscw_customizer_design&pscw_sizechart_mode=1';
				wp_safe_redirect( $url );
			}
		}
	}

	public function default_size_chart_interface() {
		$row_id    = uniqid( 'pscw-row-ID_' );
        $col_id    = uniqid( 'pscw-col-ID_');
        $text_id   = uniqid('pscw-text-ID_' );
		$interface = [
			"layout"       => [
				"type"     => "container",
				"children" => [
					$row_id
				]
			],
			"elementsById" => [
				$col_id  => [
					"id"       => $col_id,
					"class"    => "pscw-col-l-12",
					"type"     => "column",
					"parent"   => $row_id,
					"children" => [
						$text_id
					],
					"settings" => [
						"class" => "pscw-customize-col-12"
					]
				],
				$row_id  => [
					"children" => [
						$col_id
					],
					"id"       => $row_id,
					"type"     => "row"
				],
				$text_id => [
					"id"     => $text_id,
					"type"   => "text",
					"parent" => $col_id,
					"value"  => "Enter your content",
					"margin" => [ 0, 0, 0, 0 ]
				]
			]
		];

		return $interface;
	}
	public function get_random_product() {
		$random_product = wc_get_products( array(
			'type'    => array( 'simple', 'variable' ),
			'status'  => 'publish',
			'catalog_visibility'=> 'visible',
			'orderby' => 'rand',
			'return'  => 'ids',
			'limit'   => 1
		) );

		if ( empty( $random_product ) ) {
			$product = new \WC_Product_Simple();
			$product->set_name( esc_html__( 'Product Size Chart Preview', 'product-size-chart-for-woo' ) );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_price( 0 );
			$product->set_regular_price( 0 );

			$product->save();
			$random_product[] = $product->get_id();
		}

		return $random_product;
	}


	public function pscw_post_type() {
		$icon_url = esc_url( PSCW_CONST_F['img_url'] . 'sc_logo.png' );
		$label    = array(
			'name'          => esc_html__( 'Size Chart', 'product-size-chart-for-woo' ),
			'singular_name' => esc_html__( 'Size Chart', 'product-size-chart-for-woo' ),
			'add_new'       => esc_html__( 'Add New', 'product-size-chart-for-woo' ),
			'all_items'     => esc_html__( 'All Size Charts', 'product-size-chart-for-woo' ),
			'add_new_item'  => esc_html__( 'Add Item', 'product-size-chart-for-woo' )
		);
		$args     = array(
			'labels'              => $label,
			'description'         => esc_html__( 'Product Size Chart', 'product-size-chart-for-woo' ),
			'supports'            => array(
				'title',
				'revisions',
			),
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => false,
			'menu_position'       => "5",
			'menu_icon'           => $icon_url,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post'

		);
		register_post_type( 'pscw-size-chart', $args );
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self : self::$instance;
	}

	public function pscw_meta_box() {
		$screen_id = get_current_screen()->id;
		if ( $screen_id === 'pscw-size-chart' ) {
			add_meta_box( 'configure_size_chart', esc_html__( 'Configure for the size chart', 'product-size-chart-for-woo' ), array(
				$this,
				'pscw_configure'
			), 'pscw-size-chart', 'normal', 'high' );
		}
	}

	public function pscw_configure( $post_id ) {
		wp_nonce_field( 'woo_sc_check_nonce', 'woo_sc_nonce' );
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
		$random_product = $this->get_random_product();
		$pscw_data      = get_post_meta( $post_id->ID, 'pscw_data', true );
		$pscw_assign    = $pscw_data['assign'] ?? 'none';
		$pscw_condition = $pscw_data['condition'] ?? [];
		?>
        <input type="hidden" name="prevent_delete_meta_movetotrash" id="prevent_delete_meta_movetotrash"
               value="<?php echo esc_attr( wp_create_nonce( plugin_basename( __FILE__ ) . $post_id->ID ) ); ?>">
        <div class="vi-ui segment" id="pscw_configure">
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Design', 'product-size-chart-for-woo' ); ?></th>
                    <td>
						<?php if ( $post_id->post_status !== 'auto-draft' ) : ?>
							<?php
							$url = admin_url( 'customize.php' ) . '?url=' . esc_url( get_permalink( $random_product[0] ) ) . '&pscw_id=' . $post_id->ID . '&autofocus[panel]=pscw_size_chart_customizer&autofocus[section]=pscw_customizer_design&pscw_sizechart_mode=1';
							?>
                            <a target="_blank"
                               href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Go to design', 'product-size-chart-for-woo' ) ?></a>
						<?php else: ?>
							<?php esc_html_e( 'Please publish size chart to see url design', 'product-size-chart-for-woo' ); ?>
						<?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Select countries to show', 'product-size-chart-for-woo' ); ?></th>
                    <td>
                        <a class="vi-ui pink button" target="_blank" href="https://1.envato.market/zN1kJe">Upgrade This
                            Feature</a>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Assign', 'product-size-chart-for-woo' ); ?></th>
                    <td>
                        <div class="pscw_assign_wrap">
                            <select name="pscw_assign" id="pscw_assign" class="pscw_assign">
								<?php foreach ( $assign_options as $key => $val ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key == $pscw_assign ); ?> <?php disabled( in_array( $key, [
										'combined',
										'product_type',
										'product_visibility',
										'product_tag',
										'shipping_class'
									] ) ) ?>><?php echo esc_html( $val ); ?></option>
								<?php endforeach; ?>

                            </select>
                            <div class="pscw_assign_pane <?php echo 'products' === $pscw_assign ? 'active' : '' ?>"
                                 data-option="products">
                                <select name="pscw_assign_products[]" id="pscw_assign_products" multiple>
									<?php
									if ( 'products' === $pscw_assign ) {
										foreach ( $pscw_condition as $val ) {
											$value = pscw_get_value_combined( 'products', $val );
											if ( count( $value ) === 2 ) {
												?>
                                                <option selected
                                                        value="<?php echo esc_attr( $value[0] ); ?>"><?php echo esc_html( $value[1] ); ?></option>
												<?php
											}
										}
									}

									?>
                                </select>
                            </div>
                            <div class="pscw_assign_pane <?php echo 'product_cat' === $pscw_assign ? 'active' : '' ?>"
                                 data-option="product_cat">
                                <select name="pscw_assign_product_cat[]" id="pscw_assign_product_cat"
                                        multiple>
									<?php
									if ( 'product_cat' === $pscw_assign ) {
										foreach ( $pscw_condition as $val ) {
											$value = pscw_get_value_combined( 'product_cat', $val );
											if ( count( $value ) === 2 ) {
												?>
                                                <option value="<?php echo esc_attr( $value[0] ); ?>"
                                                        selected><?php echo esc_html( $value[1] ); ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
		<?php
	}

	public function pscw_ajax_search_product() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'pscw_nonce' ) ) {
			$search_key = isset( $_POST['key_search'] ) ? sanitize_text_field( wp_unslash( $_POST['key_search'] ) ) : '';
			pscw_search_post( 'product', $search_key );
		}
		die;
	}

	function pscw_ajax_search_term() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'pscw_nonce' ) ) {
			$search_key = isset( $_POST['key_search'] ) ? sanitize_text_field( wp_unslash( $_POST['key_search'] ) ) : '';
			$taxonomy   = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
			$return     = [];

			$args = [
				'taxonomy'   => $taxonomy,
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => false,
				'fields'     => 'all',
				'name__like' => $search_key,
			];

			$terms = get_terms( $args );

			if ( is_array( $terms ) && count( $terms ) ) {
				foreach ( $terms as $term ) {
					$return['results'][] = [ 'id' => $term->slug, 'text' => $term->name ];
				}
			}

			wp_send_json( $return );
		}
		die;
	}

	public function pscw_save_data_meta_box( $post_id ) {
		if ( ! current_user_can( "edit_post", $post_id ) ) {
			return $post_id;
		}
		if ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ( ! isset( $_POST['woo_sc_nonce'] ) ) ||
		     ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woo_sc_nonce'] ) ), 'woo_sc_check_nonce' ) ) ) {
			return $post_id;
		}

		if (
			( ! isset( $_POST['prevent_delete_meta_movetotrash'] ) ) ||
			( ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['prevent_delete_meta_movetotrash'] ) ), plugin_basename( __FILE__ ) . $post_id ) ) )
		) {
			return $post_id;
		}
		$pscw_assign          = isset( $_POST['pscw_assign'] ) ? sanitize_text_field( wp_unslash( $_POST['pscw_assign'] ) ) : 'none';
		$pscw_allow_countries = isset( $_POST['pscw_allow_countries'] ) ? wc_clean( wp_unslash( $_POST['pscw_allow_countries'] ) ) : [];
		$pscw_condition       = [];
		$old_list_product     = get_post_meta( $post_id, 'pscw_list_product', true );
		$new_list_product     = [];

		switch ( $pscw_assign ) {
			case 'all':
				$new_list_product = [];
				break;
			case 'products':
				$pscw_condition   = isset( $_POST['pscw_assign_products'] ) ? wc_clean( wp_unslash( $_POST['pscw_assign_products'] ) ) : [];
				$new_list_product = $pscw_condition;
				break;
			case 'product_cat':
				$pscw_condition   = isset( $_POST['pscw_assign_product_cat'] ) ? wc_clean( wp_unslash( $_POST['pscw_assign_product_cat'] ) ) : [];
				$new_list_product = wc_get_products( array( 'category' => $pscw_condition, 'return' => 'ids' ) );
				break;
		}

		if ( is_array( $old_list_product ) && count( $old_list_product ) && is_array( $new_list_product ) ) {
			$deleted_products = array_diff( $old_list_product, $new_list_product );
			foreach ( $deleted_products as $deleted_product_id ) {
				$pscw_sizecharts = get_post_meta( $deleted_product_id, 'pscw_sizecharts', true );
				if ( is_array( $pscw_sizecharts ) && count( $pscw_sizecharts ) ) {
					$pscw_sizecharts = array_filter( $pscw_sizecharts, function ( $value ) use ( $post_id ) {
						return $value !== $post_id;
					} );
					update_post_meta( $deleted_product_id, 'pscw_sizecharts', $pscw_sizecharts );
				}
			}
		}

		if ( is_array( $new_list_product ) && count( $new_list_product ) ) {
			foreach ( $new_list_product as $product_id ) {
				$pscw_sizecharts = get_post_meta( $product_id, 'pscw_sizecharts', true );
				if ( is_array( $pscw_sizecharts ) && count( $pscw_sizecharts ) ) {
					if ( ! in_array( $post_id, $pscw_sizecharts ) ) {
						$pscw_sizecharts = array_merge( $pscw_sizecharts, [ $post_id ] );
						update_post_meta( $product_id, 'pscw_sizecharts', $pscw_sizecharts );
					}
				} else {
					update_post_meta( $product_id, 'pscw_sizecharts', [ $post_id ] );
				}
			}
		}

		$pscw_data = array(
			'assign'          => $pscw_assign,
			'allow_countries' => $pscw_allow_countries,
			'condition'       => $pscw_condition,
		);

		if ( empty( get_post_meta( $post_id, 'pscw_interface', true ) ) ) {
			update_post_meta( $post_id, 'pscw_interface', $this->default_size_chart_interface() );
		}
		update_post_meta( $post_id, "pscw_data", $pscw_data );
		update_post_meta( $post_id, "pscw_list_product", $new_list_product );

		return $post_id;
	}

	public function pscw_duplicate() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( isset( $_GET['pscw_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['pscw_nonce'] ) ), 'pscw_nonce' ) ) {
			$dup_id = ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
			if ( $dup_id ) {
				$current_post = get_post( $dup_id );

				$args             = [
					'post_title' => esc_html__( 'Copy of ', 'product-size-chart-for-woo' ) . $current_post->post_title,
					'post_type'  => $current_post->post_type,
				];
				$new_id           = wp_insert_post( $args );
				$dup_post_meta    = get_post_meta( $dup_id, 'pscw_data', true );
				$dup_list_product = get_post_meta( $dup_id, 'pscw_list_product', true );
				$dub_interface    = get_post_meta( $dup_id, 'pscw_interface', true );
				update_post_meta( $new_id, 'pscw_data', $dup_post_meta );
				update_post_meta( $new_id, 'pscw_list_product', $dup_list_product );
				update_post_meta( $new_id, 'pscw_interface', $dub_interface );
				wp_safe_redirect( admin_url( "post.php?post={$new_id}&action=edit" ) );
				exit;
			}
		}
	}

	public function pscw_go_design() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( isset( $_GET['pscw_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['pscw_nonce'] ) ), 'pscw_nonce' ) ) {
			$sc_id = ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
			if ( $sc_id ) {
				$random_product = $this->get_random_product();

				$url = admin_url( 'customize.php' ) . '?url=' . esc_url( get_permalink( $random_product[0] ) ) . '&pscw_id=' . $sc_id . '&autofocus[panel]=pscw_size_chart_customizer&autofocus[section]=pscw_customizer_design&pscw_sizechart_mode=1';

				wp_safe_redirect( $url );

				exit;
			}
		}
	}

	public function pscw_before_delete_size_chart( $postid, $post ) {
		if ( 'pscw-size-chart' !== $post->post_type ) {
			$list_product = get_post_meta( $postid, 'pscw_list_product', true );
			foreach ( $list_product as $pd ) {
				$size_charts = get_post_meta( $pd, 'pscw_sizecharts', true );
				$key         = array_search( $postid, $size_charts );
				if ( $key !== false ) {
					unset( $size_charts[ $key ] );
					$size_charts = array_values( $size_charts );
					update_post_meta( $pd, 'pscw_sizecharts', $size_charts );
				}
			}

			$args = array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'  => array(
					array(
						'key'     => 'pscw_override',
						'value'   => '"' . $postid . '"',
						'compare' => 'LIKE',
					),
				),
				'fields'      => 'ids',
			);

			$query = new \WP_Query( $args );


			if ( $query->have_posts() ) {
				$product_ids = $query->posts;
				foreach ( $product_ids as $pid ) {
					$size_charts_ov = get_post_meta( $pid, 'pscw_override', true );
					$key            = array_search( $postid, $size_charts_ov );
					if ( $key !== false ) {
						unset( $size_charts_ov[ $key ] );
						$size_charts_ov = array_values( $size_charts_ov );
						update_post_meta( $pid, 'pscw_override', $size_charts_ov );
					}
				}

			}
		}
	}

	public function post_add_action( $actions, $post ) {
		if ( "pscw-size-chart" == $post->post_type ) {
			$nonce                     = wp_create_nonce( 'pscw_nonce' );
			$href1                     = admin_url( "post.php?action=pscw_duplicate&id={$post->ID}&pscw_nonce={$nonce}" );
			$href2                     = admin_url( "post.php?action=pscw_go_design&id={$post->ID}&pscw_nonce={$nonce}" );
			$actions['pscw_duplicate'] = "<a href='{$href1}'>" . esc_html__( 'Duplicate', 'product-size-chart-for-woo' ) . "</a>";
			if ( $post->post_status !== 'auto-draft' ) {
				$actions['pscw_go_design'] = "<a href='{$href2}' target='_blank'> " . esc_html__( 'Design', 'product-size-chart-for-woo' ) . "</a>";
			}
		}

		return $actions;
	}

	public function custom_post_columns( $columns ) {
		$columns['short-code'] = esc_html__( 'Short Code', 'product-size-chart-for-woo' );
		unset( $columns['date'] );

		return $columns;
	}

	public function show_custom_columns( $name ) {
		global $post;
		switch ( $name ) {
			case 'short-code':
				?>
                <div class="vi-ui icon input">
                    <input type="text" class="woo_sc_short_code" readonly
                           value="[PSCW_SIZE_CHART ID=<?php echo "'" . esc_attr( $post->ID ) . "'"; ?>]">
                    <i class="copy icon"></i>
                    <span class="woo_sc_copied"><?php esc_html_e( 'Copied', 'product-size-chart-for-woo' ); ?></span>
                </div>
				<?php
				break;
		}
	}


}