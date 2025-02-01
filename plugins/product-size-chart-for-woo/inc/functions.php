<?php

if ( ! function_exists( 'pscw_get_value_combined' ) ) {
	function pscw_get_value_combined( $type, $key ) {
		$value   = array();
		$value[] = $key;
		switch ( $type ) {
			case 'products':
				$get_product = wc_get_product( $key );
				if ( $get_product ) {
					$value[] = $get_product->get_name();
				}
				break;
			case 'product_cat':
				$get_term = get_term_by( 'slug', $key, 'product_cat' );
				if ( $get_term ) {
					$value[] = $get_term->name;
				}
				break;
		}

		return $value;

	}
}

if ( ! function_exists( 'pscw_search_post' ) ) {
	function pscw_search_post( $post_type, $search_key ) {
		$args   = array(
			'post_type' => $post_type,
			'order'     => 'name',
			's'         => $search_key
		);
		$query  = new \WP_Query ( $args );
		$result = $query->get_posts();
		foreach ( $result as $val ) {
			$product_name_id[ $val->ID ] = $val->post_title;
		}
		if ( ! empty( $product_name_id ) ) {
			$results = array();
			foreach ( $product_name_id as $id => $name ) {
				$a['id']            = $id;
				$a['text']          = $name;
				$b[]                = $a;
				$results['results'] = $b;
			}
			wp_send_json( $results );
		}
	}
}

if ( ! function_exists( 'pscw_get_allow_svg' ) ) {
	function pscw_get_allow_svg() {
		$allowed_html = wp_kses_allowed_html( 'post' );

		$allowed_html['svg'] = array(
			'xmlns'       => true,
			'width'       => true,
			'height'      => true,
			'viewBox'     => true,
			'xmlns:xlink' => true,
			'version'     => true,
		);
		$allowed_html['g']   = array(
			'transform' => true,
			'data-name' => true,
			'id'        => true,
		);

		$allowed_html['rect'] = array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'fill'   => true,
		);

		$allowed_html['path']   = array(
			'd'    => true,
			'fill' => true,
		);
		$allowed_html['circle'] = array(
			'cx'   => true,
			'cy'   => true,
			'r'    => true,
			'fill' => true,
		);
		$allowed_html['title']  = array();
		$allowed_html['desc']   = array();

		return $allowed_html;
	}
}

if ( ! function_exists( 'pscw_get_view_box' ) ) {
	function pscw_get_view_box( $key ) {
		$view_box = array(
			'ruler-icon-2'   => '0 0 71 71.6',
		);

		return $view_box[ $key ];
	}
}

if ( ! function_exists('pscw_migrate_size_chart_data') ) {
	function pscw_migrate_size_chart_data($size_chart, $woo_sc_size_chart_data) {
		$textElement  = [];
		$imageElement = [];
		$tableElement = [];
		$children     = [];

		$row_id = uniqid('pscw-row-ID_');
		$col_id = uniqid('pscw-col-ID_');

		/* Text content*/
		if ( ! empty( $size_chart->post_content ) ) {
			$text_id = uniqid('pscw-text-ID_');
			$children[]  = $text_id;
			$textElement = [
				"id"     => $text_id,
				"type"   => "text",
				"parent" => $col_id,
				"value"  => wp_kses_post( $size_chart->post_content ),
				"margin" => [
					0,
					0,
					0,
					0
				]
			];
		}
		/* Image */
		if ( isset( $woo_sc_size_chart_data['img_link'] ) && ! empty( $woo_sc_size_chart_data['img_link'] ) ) {
			$image_id  = uniqid('pscw-image-ID_');
			$children[] = $image_id;
			$imageElement = [
				'id'          => $image_id,
				'type'        => 'image',
				"parent"      => $col_id,
				"alt"         => "",
				"borderColor" => "#000000",
				"borderStyle" => "solid",
				"borderWidth" => 0,
				"height"      => 100,
				"heightUnit"  => "%",
				"width"       => isset( $woo_sc_size_chart_data['img_width'] ) ? $woo_sc_size_chart_data['img_width'] : 100,
				"widthUnit"   => "%",
				"src"         => $woo_sc_size_chart_data['img_link'],
				"padding"     => [
					0,
					0,
					0,
					0
				],
				"margin"      => [
					10,
					0,
					10,
					0
				],
				"objectFit"   => "unset"
			];
		}
		/* Table */
		if ( isset( $woo_sc_size_chart_data['table_array'] ) ) {
			$table_id   = uniqid('pscw-table-ID_');
			$children[] = $table_id;
			$columns    = [ "" ];
			$rows       = [ [ "" ] ];
			if ( ! empty( $woo_sc_size_chart_data['table_array'] ) ) {
				$table_array = json_decode( $woo_sc_size_chart_data['table_array'], true );
				$columns     = $table_array[0];
				unset( $table_array[0] );
				$rows = array_values( $table_array );
			}
			$tableElement = [
				'id'                    => $table_id,
				"type"                  => "table",
				"parent"                => $col_id,
				"columns"               => $columns,
				"rows"                  => $rows,
				"headerColumn"          => 'row',
				"headerBackground"      => isset( $woo_sc_size_chart_data['head_color'] ) ? $woo_sc_size_chart_data['head_color'] : "#ffffff",
				"textHeader"            => isset( $woo_sc_size_chart_data['text_head_color'] ) ? $woo_sc_size_chart_data['text_head_color'] : "#000000",
				"headerTextBold"        => false,
				"headerTextSize"        => 14,
				"columnsStyle"          => isset( $woo_sc_size_chart_data['woo_sc_cell_style'] ) && $woo_sc_size_chart_data['woo_sc_cell_style'] === 'columns',
				"evenBackground"        => isset( $woo_sc_size_chart_data['even_rows_color'] ) ? $woo_sc_size_chart_data['even_rows_color'] : "#ffffff",
				"evenText"              => isset( $woo_sc_size_chart_data['even_rows_text_color'] ) ? $woo_sc_size_chart_data['even_rows_text_color'] : "#494949",
				"oddBackground"         => isset( $woo_sc_size_chart_data['odd_rows_color'] ) ? $woo_sc_size_chart_data['odd_rows_color'] : "#ffffff",
				"oddText"               => isset( $woo_sc_size_chart_data['odd_rows_text_color'] ) ? $woo_sc_size_chart_data['odd_rows_text_color'] : "#494949",
				"borderColor"           => isset( $woo_sc_size_chart_data['border_color'] ) ? $woo_sc_size_chart_data['border_color'] : "#9D9D9D",
				"cellTextSize"          => 14,
				"horizontalBorderWidth" => isset( $woo_sc_size_chart_data['horizontal_width'] ) ? $woo_sc_size_chart_data['horizontal_width'] : 1,
				"horizontalBorderStyle" => isset( $woo_sc_size_chart_data['horizontal_border_style'] ) ? $woo_sc_size_chart_data['horizontal_border_style'] : "solid",
				"verticalBorderWidth"   => isset( $woo_sc_size_chart_data['vertical_width'] ) ? $woo_sc_size_chart_data['vertical_width'] : 1,
				"verticalBorderStyle"   => isset( $woo_sc_size_chart_data['vertical_border_style'] ) ? $woo_sc_size_chart_data['vertical_border_style'] : "solid",
				"margin"                => [
					0,
					0,
					0,
					0
				],
				"borderRadius"          => ( isset( $woo_sc_size_chart_data['table_template'] ) && $woo_sc_size_chart_data['table_template'] === 'table_template_v1' ) ? [
					20,
					20,
					20,
					20
				] : [
					0,
					0,
					0,
					0
				]
			];
		}
		/* Interface */
		$pscw_interface = array(
			'layout'       => [
				'type'     => "container",
				'children' => [
					$row_id
				]
			],
			'elementsById' => [
				$col_id => [
					'id'       => $col_id,
					'class'    => 'pscw-col-l-12',
					'type'     => 'column',
					'parent'   => $row_id,
					'children' => $children,
					'settings' => [
						'class' => 'pscw-customize-col-12',
					],
				],
				$row_id => [
					"children" => [
						$col_id
					],
					"id"       => $row_id,
					"type"     => "row"
				]
			]
		);

		if ( ! empty( $textElement ) ) {
			$pscw_interface['elementsById'][ $textElement['id'] ] = $textElement;
		}

		if ( ! empty( $imageElement ) ) {
			$pscw_interface['elementsById'][ $imageElement['id'] ] = $imageElement;
		}

		if ( ! empty( $tableElement ) ) {
			$pscw_interface['elementsById'][ $tableElement['id'] ] = $tableElement;
		}

		update_post_meta( $size_chart->ID, 'pscw_interface', $pscw_interface );
	}
}