<?php
/**
 * A helper for mapping the new/old settings.
 *
 * @package WooCommerce\PayPalCommerce\Compat
 */

declare( strict_types = 1 );

namespace WooCommerce\PayPalCommerce\Compat;

use RuntimeException;

/**
 * A helper class to manage the transition between legacy and new settings.
 *
 * This utility provides mapping from old setting keys to new ones and retrieves
 * their corresponding values from the appropriate models. The class uses lazy
 * loading and caching to optimize performance during runtime.
 */
class SettingsMapHelper {

	/**
	 * A list of settings maps containing mapping definitions.
	 *
	 * @var SettingsMap[]
	 */
	protected array $settings_map;

	/**
	 * Indexed map for faster lookups, initialized lazily.
	 *
	 * @var array|null Associative array where old keys map to metadata.
	 */
	protected ?array $key_to_model = null;

	/**
	 * Cache for results of `to_array()` calls on models.
	 *
	 * @var array Associative array where keys are model IDs.
	 */
	protected array $model_cache = array();

	/**
	 * Constructor.
	 *
	 * @param SettingsMap[] $settings_map A list of settings maps containing key definitions.
	 * @throws RuntimeException When an old key has multiple mappings.
	 */
	public function __construct( array $settings_map ) {
		$this->validate_settings_map( $settings_map );
		$this->settings_map = $settings_map;
	}

	/**
	 * Validates the settings map for duplicate keys.
	 *
	 * @param SettingsMap[] $settings_map The settings map to validate.
	 * @throws RuntimeException When an old key has multiple mappings.
	 */
	protected function validate_settings_map( array $settings_map ) : void {
		$seen_keys = array();

		foreach ( $settings_map as $settings_map_instance ) {
			foreach ( $settings_map_instance->get_map() as $old_key => $new_key ) {
				if ( isset( $seen_keys[ $old_key ] ) ) {
					throw new RuntimeException( "Duplicate mapping for legacy key '$old_key'." );
				}
				$seen_keys[ $old_key ] = true;
			}
		}
	}

	/**
	 * Retrieves the value of a mapped key from the new settings.
	 *
	 * @param string $old_key The key from the legacy settings.
	 *
	 * @return mixed|null The value of the mapped setting, or null if not found.
	 */
	public function mapped_value( string $old_key ) {
		$this->ensure_map_initialized();

		if ( ! isset( $this->key_to_model[ $old_key ] ) ) {
			return null;
		}

		$mapping  = $this->key_to_model[ $old_key ];
		$model_id = spl_object_id( $mapping['model'] );

		return $this->get_cached_model_value( $model_id, $mapping['new_key'], $mapping['model'] );
	}

	/**
	 * Determines if a given legacy key exists in the new settings.
	 *
	 * @param string $old_key The key from the legacy settings.
	 *
	 * @return bool True if the key exists in the new settings, false otherwise.
	 */
	public function has_mapped_key( string $old_key ) : bool {
		$this->ensure_map_initialized();

		return isset( $this->key_to_model[ $old_key ] );
	}

	/**
	 * Retrieves a cached model value or caches it if not already cached.
	 *
	 * @param int    $model_id The unique identifier for the model object.
	 * @param string $new_key  The key in the new settings structure.
	 * @param object $model    The model object.
	 *
	 * @return mixed|null The value of the key in the model, or null if not found.
	 */
	protected function get_cached_model_value( int $model_id, string $new_key, object $model ) {
		if ( ! isset( $this->model_cache[ $model_id ] ) ) {
			$this->model_cache[ $model_id ] = $model->to_array();
		}

		return $this->model_cache[ $model_id ][ $new_key ] ?? null;
	}

	/**
	 * Ensures the map of old-to-new settings is initialized.
	 *
	 * This method initializes the `key_to_model` array lazily to improve performance.
	 *
	 * @return void
	 */
	protected function ensure_map_initialized() : void {
		if ( $this->key_to_model === null ) {
			$this->initialize_key_map();
		}
	}

	/**
	 * Initializes the indexed map of old-to-new settings keys.
	 *
	 * This method processes the provided settings maps and indexes the legacy
	 * keys to their corresponding metadata for efficient lookup.
	 *
	 * @return void
	 */
	protected function initialize_key_map() : void {
		$this->key_to_model = array();

		foreach ( $this->settings_map as $settings_map_instance ) {
			foreach ( $settings_map_instance->get_map() as $old_key => $new_key ) {
				$this->key_to_model[ $old_key ] = array(
					'new_key' => $new_key,
					'model'   => $settings_map_instance->get_model(),
				);
			}
		}
	}
}
