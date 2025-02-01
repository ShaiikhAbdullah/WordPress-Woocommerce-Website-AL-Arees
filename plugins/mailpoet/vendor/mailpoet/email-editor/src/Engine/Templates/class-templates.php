<?php
declare(strict_types = 1);
namespace MailPoet\EmailEditor\Engine\Templates;
if (!defined('ABSPATH')) exit;
use MailPoet\EmailEditor\Validator\Builder;
use WP_Block_Template;
class Templates {
 private string $template_prefix = 'mailpoet';
 private array $post_types = array();
 private string $template_directory = __DIR__ . DIRECTORY_SEPARATOR;
 public function initialize( array $post_types ): void {
 $this->post_types = $post_types;
 add_filter( 'theme_templates', array( $this, 'add_theme_templates' ), 10, 4 ); // Workaround needed when saving post – template association.
 $this->register_templates();
 $this->register_post_types_to_api();
 }
 public function get_block_template( $template_slug ) {
 // Template id is always prefixed by active theme and get_stylesheet returns the active theme slug.
 $template_id = get_stylesheet() . '//' . $template_slug;
 return get_block_template( $template_id );
 }
 private function register_templates(): void {
 // The function was added in WordPress 6.7. We can remove this check after we drop support for WordPress 6.6.
 if ( ! function_exists( 'register_block_template' ) ) {
 return;
 }
 // Register basic blank template.
 $general_email = array(
 'title' => __( 'General Email', 'mailpoet' ),
 'description' => __( 'A general template for emails.', 'mailpoet' ),
 'slug' => 'email-general',
 );
 $template_filename = $general_email['slug'] . '.html';
 $template_name = $this->template_prefix . '//' . $general_email['slug'];
 if ( ! \WP_Block_Templates_Registry::get_instance()->is_registered( $template_name ) ) {
 // skip registration if the template was already registered.
 register_block_template(
 $template_name,
 array(
 'title' => $general_email['title'],
 'description' => $general_email['description'],
 'content' => (string) file_get_contents( $this->template_directory . $template_filename ),
 'post_types' => $this->post_types,
 )
 );
 }
 do_action( 'mailpoet_email_editor_register_templates' );
 }
 public function register_post_types_to_api(): void {
 $controller = new \WP_REST_Templates_Controller( 'wp_template' );
 $schema = $controller->get_item_schema();
 // Future compatibility check if the post_types property is already registered.
 if ( isset( $schema['properties']['post_types'] ) ) {
 return;
 }
 register_rest_field(
 'wp_template',
 'post_types',
 array(
 'get_callback' => array( $this, 'get_post_types' ),
 'update_callback' => null,
 'schema' => Builder::string()->to_array(),
 )
 );
 }
 public function get_post_types( $response_object ): array {
 if ( isset( $response_object['plugin'] ) && $response_object['plugin'] === $this->template_prefix ) {
 return $this->post_types;
 }
 return $response_object['post_types'] ?? array();
 }
 public function add_theme_templates( $templates, $theme, $post, $post_type ) {
 if ( $post_type && ! in_array( $post_type, $this->post_types, true ) ) {
 return $templates;
 }
 $block_templates = get_block_templates();
 foreach ( $block_templates as $block_template ) {
 // Ideally we could check for supported post_types but there seems to be a bug and once a template has some edits and is stored in DB
 // the core returns null for post_types.
 if ( $block_template->plugin !== $this->template_prefix ) {
 continue;
 }
 $templates[ $block_template->slug ] = $block_template;
 }
 return $templates;
 }
}
