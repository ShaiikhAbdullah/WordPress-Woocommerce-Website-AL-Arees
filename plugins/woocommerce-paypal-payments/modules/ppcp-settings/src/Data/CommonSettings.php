<?php
/**
 * Common settings class
 *
 * @package WooCommerce\PayPalCommerce\Settings\Data
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Settings\Data;

use RuntimeException;

/**
 * Class CommonSettings
 *
 * This class serves as a container for managing the common settings that
 * are used and managed in various areas of the settings UI
 *
 * Those settings mainly describe connection details and are initially collected
 * in the onboarding wizard, and also appear in the settings screen.
 */
class CommonSettings extends AbstractDataModel {

	/**
	 * Option key where profile details are stored.
	 *
	 * @var string
	 */
	protected const OPTION_KEY = 'woocommerce-ppcp-data-common';

	/**
	 * List of customization flags, provided by the server (read-only).
	 *
	 * @var array
	 */
	protected array $woo_settings = array();

	/**
	 * Constructor.
	 *
	 * @param string $country  WooCommerce store country.
	 * @param string $currency WooCommerce store currency.
	 */
	public function __construct( string $country, string $currency ) {
		parent::__construct();
		$this->woo_settings['country']  = $country;
		$this->woo_settings['currency'] = $currency;
	}

	/**
	 * Get default values for the model.
	 *
	 * @return array
	 */
	protected function get_defaults() : array {
		return array(
			'use_sandbox'           => false,
			'use_manual_connection' => false,
			'client_id'             => '',
			'client_secret'         => '',

			// Details about connected merchant account.
			'merchant_connected'    => false,
			'sandbox_merchant'      => false,
			'merchant_id'           => '',
			'merchant_email'        => '',
		);
	}

	// -----

	/**
	 * Gets the 'use sandbox' setting.
	 *
	 * @return bool
	 */
	public function get_sandbox() : bool {
		return (bool) $this->data['use_sandbox'];
	}

	/**
	 * Sets the 'use sandbox' setting.
	 *
	 * @param bool $use_sandbox Whether to use sandbox mode.
	 */
	public function set_sandbox( bool $use_sandbox ) : void {
		$this->data['use_sandbox'] = $use_sandbox;
	}

	/**
	 * Gets the 'use manual connection' setting.
	 *
	 * @return bool
	 */
	public function get_manual_connection() : bool {
		return (bool) $this->data['use_manual_connection'];
	}

	/**
	 * Sets the 'use manual connection' setting.
	 *
	 * @param bool $use_manual_connection Whether to use manual connection.
	 */
	public function set_manual_connection( bool $use_manual_connection ) : void {
		$this->data['use_manual_connection'] = $use_manual_connection;
	}

	/**
	 * Gets the client ID.
	 *
	 * @return string
	 */
	public function get_client_id() : string {
		return $this->data['client_id'];
	}

	/**
	 * Sets the client ID.
	 *
	 * @param string $client_id The client ID.
	 */
	public function set_client_id( string $client_id ) : void {
		$this->data['client_id'] = sanitize_text_field( $client_id );
	}

	/**
	 * Gets the client secret.
	 *
	 * @return string
	 */
	public function get_client_secret() : string {
		return $this->data['client_secret'];
	}

	/**
	 * Sets the client secret.
	 *
	 * @param string $client_secret The client secret.
	 */
	public function set_client_secret( string $client_secret ) : void {
		$this->data['client_secret'] = sanitize_text_field( $client_secret );
	}

	/**
	 * Returns the list of read-only customization flags.
	 *
	 * @return array
	 */
	public function get_woo_settings() : array {
		return $this->woo_settings;
	}

	/**
	 * Setter to update details of the connected merchant account.
	 *
	 * Those details cannot be changed individually.
	 *
	 * @param bool   $is_sandbox     Whether the details are for a sandbox account.
	 * @param string $merchant_id    The merchant ID.
	 * @param string $merchant_email The merchant's email.
	 *
	 * @return void
	 */
	public function set_merchant_data( bool $is_sandbox, string $merchant_id, string $merchant_email ) : void {
		$this->data['sandbox_merchant']   = $is_sandbox;
		$this->data['merchant_id']        = sanitize_text_field( $merchant_id );
		$this->data['merchant_email']     = sanitize_email( $merchant_email );
		$this->data['merchant_connected'] = true;
	}

	/**
	 * Whether the currently connected merchant is a sandbox account.
	 *
	 * @return bool
	 */
	public function is_sandbox_merchant() : bool {
		return $this->data['sandbox_merchant'];
	}

	/**
	 * Whether the merchant successfully logged into their PayPal account.
	 *
	 * @return bool
	 */
	public function is_merchant_connected() : bool {
		return $this->data['merchant_connected'] && $this->data['merchant_id'] && $this->data['merchant_email'];
	}

	/**
	 * Gets the currently connected merchant ID.
	 *
	 * @return string
	 */
	public function get_merchant_id() : string {
		return $this->data['merchant_id'];
	}

	/**
	 * Gets the currently connected merchant's email.
	 *
	 * @return string
	 */
	public function get_merchant_email() : string {
		return $this->data['merchant_email'];
	}
}
