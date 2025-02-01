<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 *  @since 1.4.5
 *  Compatability to WPML
 * 
 *  Webtoffee Smart Coupon Multi Language Class
 */


if( ! class_exists ( 'Wt_Smart_Coupon_Mulitlanguage' ) ) {
	class Wt_Smart_Coupon_Mulitlanguage {

        private static $instance;
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }
		/**
		 * is_multilanguage_plugin_active function.
		 *
		 * @access public
		 * @return void
		 */
		public function is_multilanguage_plugin_active()
		{
			$status = false;

			if (defined('ICL_LANGUAGE_CODE') || defined('POLYLANG_FILE')) {
				$status = true;
			}

			return $status;
        }

        /**
		 * Get all the id's of translation
		 *
		 * @access public
         * @param string $product_id
         * @param string $post_type
		 * @return Array
		 */
        public function get_all_translations( $product_id, $post_type = "post") {
            global $sitepress;
            $translated_products = array();
            if( !empty( $product_id ) ) {
                if ( $this->is_multilanguage_plugin_active() ) {
                    // Polylang
                    if( function_exists('icl_object_id') && $sitepress ) {
                        $trid = $sitepress->get_element_trid($product_id, $post_type);
                        $translations = $sitepress->get_element_translations($trid, $post_type);
                        foreach ($translations as $key => $translation ) {
                            $translated_products[] = $translation->element_id;
                        }
                    } else if( function_exists('pll_get_post_translations') ){
                    
                    }
                }
            }
            return $translated_products;
        }

	}
}