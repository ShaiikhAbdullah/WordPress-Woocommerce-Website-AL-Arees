<?php

namespace PSCWF\Inc;

defined( 'ABSPATH' ) || exit;

class Short_Code {
	protected static $instance = null;
	protected $suffix = '';
	protected $setting;

	private function __construct() {
		$this->setting = Data::get_instance();
		$this->suffix = WP_DEBUG ? '' : '.min';
		if ( $this->setting->get_params( 'enable' ) === "1" ) {
			add_shortcode( 'PSCW_SIZE_CHART', [ $this, 'short_code_content' ] );
		}
	}

	public static function instance() {
		return null === self::$instance ? self::$instance = new self() : self::$instance;
	}

	public function short_code_content( $atts ) {
		$atts = shortcode_atts( array( 'id' => '', 'signature' => '' ), $atts );
		if ( ! $atts['id'] ) {
			return '';
		}

		/* To determine size chart from customize */
		$signature = isset( $atts['signature'] ) ? esc_attr($atts['signature']) : '';

		$allowed_html = wp_kses_allowed_html( 'post' );

		$allowed_html['input']  = array(
			'class'       => array(),
			'id'          => array(),
			'name'        => array(),
			'value'       => array(),
			'type'        => array(),
			'placeholder' => array(),
			'style'       => array(),
		);


		wp_enqueue_style( 'pscw-shortcode', PSCW_CONST_F['css_url'] . 'shortcode' . $this->suffix . '.css', array(), PSCW_CONST_F['version'] );

		$interface = get_post_meta( $atts['id'], 'pscw_interface', true );
		if ( empty( $interface ) ) {
			return '';
		}

		if ( ! is_customize_preview() ) {
			wp_add_inline_style( 'pscw-shortcode', $this->pscw_generate_style( $interface['elementsById'] ) );
		}
		$layout = $interface['layout'];
		$html   = '';
		foreach ( $layout['children'] as $child_id ) {
			$html .= $this->pscw_render_interface( $child_id, $interface['elementsById'] );
		}
		ob_start();
		echo is_customize_preview() ? '<div class="pscw-container pscw-customizing '. $signature .' " id="pscw-container">' : '<div class="pscw-container" id="pscw-container">';
		echo wp_kses( $html, $allowed_html );
		echo is_customize_preview() ? '<ul id="pscw-preview-table-menu">
                <li data-action="add-row-below">' . esc_html__( 'Add row below', 'product-size-chart-for-woo' ) . '</li>
                <li data-action="add-col-right">' . esc_html__( 'Add col right', 'product-size-chart-for-woo' ) . '</li>
                <li data-action="delete-col">' . esc_html__( 'Delete col', 'product-size-chart-for-woo' ) . '</li>
                <li data-action="delete-row">' . esc_html__( 'Delete row', 'product-size-chart-for-woo' ) . '</li>
            </ul>' : '';
		echo '</div>';
		?>
		<?php
		return ob_get_clean();
	}

	public function pscw_render_interface( $element_id, $elements_by_id ) {
		if ( ! isset( $elements_by_id[ $element_id ] ) ) {
			return '';
		}
		$element = $elements_by_id[ $element_id ];
		$html    = '';
		switch ( $element['type'] ) {
			case 'row':
				$html .= '<div class="pscw-row" id="' . esc_attr( $element['id'] ) . '">';
				foreach ( $element['children'] as $child_id ) {
					$html .= $this->pscw_render_interface( $child_id, $elements_by_id );
				}
				$html .= '</div>';
				break;

			case 'column':
				$html .= '<div class="pscw-cols ' . esc_attr( $element['class'] ) . '" id="' . esc_attr( $element['id'] ) . '">';
				foreach ( $element['children'] as $child_id ) {
					$html .= $this->pscw_render_interface( $child_id, $elements_by_id );
				}
				$html .= '</div>';
				break;

			case 'table':
				$html .= '<div class="woo_sc_table_scroll woo_sc_table100" id="' . esc_attr( $element['id'] ) . '">';
				$html .= '<div class="woo_sc_table100-body">';
				$html .= '<div class="woo_sc_view_table">';
				$html .= '<table class="woo_sc_view_table">';
				$html .= '<thead class="woo_sc_table100-head "><tr class="woo_sc_row100 head">';
				foreach ( $element['columns'] as $column ) {
					if ( is_customize_preview() ) {
						$html .= '<th class="woo_sc_cell100"><input type="text" placeholder="..." style="width: ' . esc_attr( ( strlen( $column ) === 0 ? 3 : strlen( $column ) + 1 ) ) . 'ch;" value="' . esc_attr( esc_html( $column ) ) . '"></th>';
					} else {
						$html .= '<th class="woo_sc_cell100">' . esc_html( $column ) . '</th>';
					}
				}
				$html .= '</tr></thead>';
				$html .= '<tbody>';
				foreach ( $element['rows'] as $row ) {
					$html .= '<tr>';
					$i    = 0;
					foreach ( $row as $cell ) {
						if ( is_customize_preview() ) {
							$html .= '<td class="' . esc_attr( ( $i === 0 ) ? 'pscw-first-child' : '' ) . '"><input type="text" placeholder="..." style="width: ' . esc_attr( strlen( $cell ) === 0 ? 3 : strlen( $cell ) + 1 ) . 'ch;" value="' . esc_attr( $cell ) . '"></td>';
						} else {
							$html .= '<td>' . esc_html( $cell ) . '</td>';
						}
						$i ++;
					}
					$html .= '</tr>';
				}
				$html .= '</tbody>';
				$html .= '</table>';
				$html .= '</div>';
				$html .= '</div>';
				$html .= $this->pscw_render_element_editing();
				$html .= '</div>';
				break;

			case 'text':
				$html .= '<div class="pscw-text-editor-container" id="' . esc_attr( $element['id'] ) . '">';
				$html .= '<div class="pscw-text-editor">' . wp_kses_post( $element['value'] ) . '</div>';
				$html .= $this->pscw_render_element_editing();
				$html .= '</div>';
				break;

			case 'image':
				$html .= '<div class="pscw-image-container" id="' . esc_attr( $element['id'] ) . '">';
				$html .= '<img src="' . esc_url( $element['src'] ) . '" alt="' . esc_attr( $element['alt'] ) . '">';
				$html .= $this->pscw_render_element_editing();
				$html .= '</div>';
				break;
		}

		return $html;
	}

	public function pscw_render_element_editing() {
		return is_customize_preview() ? '<span class="pscw-customizing-edit">&#9998;</span>' : '';
	}

	public function pscw_generate_style( $elements ) {
		$css = [];
		foreach ( $elements as $element ) {
			switch ( $element['type'] ) {
				case 'table':
					$headerTextBold = $element['headerTextBold'] == 1 ? 'bold' : '400';
					switch ( $element['headerColumn'] ) {
						default:
							$css[] = '#' . $element['id'] . ' table.woo_sc_view_table th.woo_sc_cell100 {background: ' . $element['headerBackground'] . ';color: ' . $element['textHeader'] . ';font-weight: ' . $headerTextBold . ';font-size: ' . $element['headerTextSize'] . 'px}';
							if ( $element['columnsStyle'] ) {
								$css[] = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr td:nth-child(even) {background: ' . $element['evenBackground'] . ';color: ' . $element['evenText'] . '}';
								$css[] = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr td:nth-child(odd) {background: ' . $element['oddBackground'] . ';color: ' . $element['oddText'] . '}';
							} else {
								$css[] = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr:nth-child(odd) td {background: ' . $element['evenBackground'] . ';color: ' . $element['evenText'] . '}';
								$css[] = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr:nth-child(even) td {background: ' . $element['oddBackground'] . ';color: ' . $element['oddText'] . '}';
							}
							break;
					}

					$css[] = '#' . $element['id'] . ' table.woo_sc_view_table th.woo_sc_cell100, ' . '#' . $element['id'] . ' .woo_sc_view_table tbody td {border-color: ' . $element['borderColor'] . '; border-width: ' . $element['horizontalBorderWidth'] . 'px ' . $element['verticalBorderWidth'] . 'px; border-style: ' . $element['horizontalBorderStyle'] . ' ' . $element['verticalBorderStyle'] . ';}';
					$css[] = '#' . $element['id'] . ' table.woo_sc_view_table tbody td {font-size: ' . $element['cellTextSize'] . 'px}';

					$tableMargin = array_map( function ( $item ) {
						return $item . "px";
					}, $element['margin'] );

					$tableMargin = implode( " ", $tableMargin );

					$tableBorderRadius = array_map( function ( $item ) {
						return $item . "px";
					}, $element['borderRadius'] );

					$tableBorderRadiusText = implode( " ", $tableBorderRadius );

					$css[]                 = '#' . $element['id'] . ' {margin: ' . $tableMargin . '; border-radius: ' . $tableBorderRadiusText . ';}';

					if ( is_rtl() ) {
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table thead tr th:fist-child{ border-top-right-radius: ' . $tableBorderRadius[0] . '}';
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table thead tr th:last-child{ border-top-left-radius: ' . $tableBorderRadius[1] . '}';
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr:last-child td:first-child{ border-bottom-right-radius: ' . $tableBorderRadius[2] . '}';
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr:last-child td:last-child{ border-bottom-left-radius: ' . $tableBorderRadius[3] . ' }';
					}else {
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table thead tr th:fist-child{ border-top-left-radius: ' . $tableBorderRadius[0] . '}';
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table thead tr th:last-child{ border-top-right-radius: ' . $tableBorderRadius[1] . '}';
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr:last-child td:first-child{ border-bottom-left-radius: ' . $tableBorderRadius[2] . '}';
						$css[]                 = '#' . $element['id'] . ' table.woo_sc_view_table tbody tr:last-child td:last-child{ border-bottom-right-radius: ' . $tableBorderRadius[3] . ' }';
					}
					$tableMaxHeight = 535;
					$css[]                 = '#' . $element['id'] . ' .woo_sc_table100-body {max-height: ' . $tableMaxHeight . 'px;}';
					break;
				case 'image':
					$imageMargin = array_map( function ( $item ) {
						return $item . "px";
					}, $element['margin'] );

					$imageMargin = implode( " ", $imageMargin );

					$imagePadding = array_map( function ( $item ) {
						return $item . "px";
					}, $element['padding'] );

					$imagePadding = implode( " ", $imagePadding );
					$css[]        = '#' . $element['id'] . ' img {width: ' . $element['width'] . $element['widthUnit'] . '; height: ' . $element['height'] . $element['heightUnit'] . '; border-width: ' . $element['borderWidth'] . 'px; border-color: ' . $element['borderColor'] . '; border-style: ' . $element['borderStyle'] . '}';
					$css[]        = '#' . $element['id'] . ' {padding: ' . $imagePadding . '; margin: ' . $imageMargin . ';}';
					$css[]        = '#' . $element['id'] . ' img {object-fit: ' . $element['objectFit'] . ';}';
					break;
				case 'text':
					$textMargin = array_map( function ( $item ) {
						return $item . "px";
					}, $element['margin'] );

					$textMargin = implode( " ", $textMargin );
					$css[]      = '#' . $element['id'] . ' {margin: ' . $textMargin . ';}';
					break;
			}
		}
		$css = implode( '', $css );

		return $css;
	}
}