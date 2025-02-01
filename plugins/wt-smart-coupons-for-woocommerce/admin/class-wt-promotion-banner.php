<?php 
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


if ( ! class_exists( 'Wt_Promotion_Banner' ) )  {

	class Wt_Promotion_Banner {
		
		private static $banner_state_option_name = 'wt_promotion_banner_state';
		private $banner_state = 1; // Banner state, 1: Show, 2: Closed by user, 3: Clicked the grab button, 4: Expired
		private static $show_banner = null;
		private static $ajax_action_name = 'wt_promotion_banner';
		private static $webtoffee_url = 'https://www.webtoffee.com/plugins/?utm_source=free_plugin_banner&utm_medium=basic_plugin&utm_campaign=BlackFriday_2023';

		public function __construct() {
			
			$this->banner_state = get_option( self::$banner_state_option_name ); // Current state of the banner
			$this->banner_state = absint( false === $this->banner_state ? 1 : $this->banner_state );

			// Show banner
			add_action( 'admin_notices', array( $this, 'show_banner' ) );

			// Add banner scripts and styles
			add_action( 'admin_print_footer_scripts', array( $this, 'add_banner_scripts' ) );

			// Ajax hook to save banner state
			add_action( 'wp_ajax_' . self::$ajax_action_name, array( $this, 'update_banner_state' ) ); 
		}

		public function show_banner() {

			if( $this->is_show_banner() ) { // Show the banner

				?>
				<div class="wt_promotion_banner notice-success notice is-dismissible">
					<div class="wt_promotion_banner_inner">
						<div class="wt_promotion_banner_left"></div>
						<div class="wt_promotion_banner_right">
							<h3 class="wt_promotion_banner_hd"><?php esc_html_e( 'Biggest sale of the year is here!' ); ?></h3>
							<p><?php esc_html_e( 'Black Friday & Cyber Monday sale is now live! Get any WebToffee plugins at an exclusive 30% discount.' ); ?></p>
							<button class="button button-primary"><?php esc_html_e( 'Grab this deal' ); ?> → </button>
						</div>
					</div>
				</div>
				<?php
			}
		}


		/**
    	 * 	Add banner scripts and styles
    	 * 	
    	 */
		public function add_banner_scripts() {
			
			if( $this->is_show_banner() ) { // Show the banner
				
				$ajax_url = admin_url( 'admin-ajax.php' );
        		$nonce = wp_create_nonce( 'wt_promotion_banner' );

				?>
		        <style type="text/css">
		            .wt_promotion_banner{ background:#fff url('<?php echo esc_url( plugin_dir_url( __FILE__ ) );?>images/banner/webtoffee_logo_transparent.svg') right no-repeat; display:block; clear:both; box-sizing:border-box; }
		            .wt_promotion_banner_inner{ display:inline-block; width:100%; padding:20px 15px; box-sizing:border-box; }
		            .wt_promotion_banner_left{ float:left; width:100px; height:90px; background:#F9F9F9 url('<?php echo esc_url( plugin_dir_url( __FILE__ ) );?>images/banner/webtoffee_logo.svg') center no-repeat; border-radius:4px;}
		            .wt_promotion_banner_right{ float:left; width: calc( 100% - 100px); padding-left:30px; box-sizing:border-box; }
		            h3.wt_promotion_banner_hd{ font-size:16px; color:#000; font-weight:600; margin:0px; } 

		            
		        </style>
		        <script type="text/javascript">
		        	(function($) {
		                "use strict";

		                /* Prepare ajax data object */
		                var data_obj = {
		                    _wpnonce: '<?php echo esc_html( $nonce ); ?>',
		                    action: '<?php echo esc_html( self::$ajax_action_name ); ?>',
		                    wt_promotion_banner_action_type: ''
		                };

		                $(document).on('click', '.wt_promotion_banner .button', function(e) {
		                    
		                    e.preventDefault(); 
		                    var elm = $(this);

		                    window.open('<?php echo esc_url(self::$webtoffee_url); ?>'); 
		                    elm.parents('.wt_promotion_banner').hide();

		                    data_obj['wt_promotion_banner_action_type'] = 3; // Clicked the button
		                    
		                    $.ajax({
		                        url: '<?php echo esc_url($ajax_url); ?>',
		                        data: data_obj,
		                        type: 'POST'
		                    });

		                }).on('click', '.wt_promotion_banner .notice-dismiss', function(e) {
		                    
		                    e.preventDefault();
		                    data_obj['wt_promotion_banner_action_type'] = 2; // Closed by user
		                    
		                    $.ajax({
		                        url: '<?php echo esc_url($ajax_url); ?>',
		                        data: data_obj,
		                        type: 'POST',
		                    });

		                });

	            })(jQuery)
		        </script>
		        <?php
			}
    	}


    	/**
    	 * 	Update banner state ajax hook
    	 * 
    	 */
    	public function update_banner_state() {

    		check_ajax_referer( 'wt_promotion_banner' );

    		if ( isset( $_POST['wt_promotion_banner_action_type'] ) ) {
	            
	            $action_type = absint( $_POST['wt_promotion_banner_action_type'] );

	            // Current action is allowed?
	            if ( in_array( $action_type, array( 2, 3 ) ) ) {
	                update_option( self::$banner_state_option_name, $action_type );
	            }
	        }

	        exit();
    	}
    	
    	
    	/**
    	 * 	Is show banner in the current page
    	 * 	
    	 * 	@return bool
    	 */
    	private function is_show_banner() {
    		
    		if ( ! is_null( self::$show_banner ) ) { // Already checked
    			return self::$show_banner;
    		}


    		/**
    		 * 	Check current banner state
    		 */
    		if ( 1 !== $this->banner_state ) {
    			self::$show_banner = false;
    			return self::$show_banner;
    		}

    		

    		/**
    		 * 	Check expiry date
    		 */
    		$target_date = new DateTime( '27-NOV-2023, 11:59 PM', new DateTimeZone( 'Asia/Kolkata' ) ); // Expiry date
    		$current_date = new DateTime( 'now', new DateTimeZone( 'Asia/Kolkata' ) ); // Current date
    		
    		if ( $current_date >= $target_date ) {
    			update_option( self::$banner_state_option_name, 4 ); // Set as expired.
    			self::$show_banner = false;
    			return self::$show_banner;
    		}



    		/**
    		 * 	Check screens
    		 */
    		$screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';

            /**
             *  Pages to show the promotional banner.
             * 	
             * 	@param 	string[] 	Default screen ids
             */
            $screens_to_show = (array) apply_filters( 'wt_promotion_banner_screens', array() );
            
            self::$show_banner = in_array( $screen_id, $screens_to_show );
            return self::$show_banner;
    	}
	}

	new Wt_Promotion_Banner();
}