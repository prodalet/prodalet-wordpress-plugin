<?php
/**
 * Plugin Name: Prodalet
 * Author: Prodalet
 * Version: 0.9.1
 * License: GPLv2
 * Text Domain: prodalet.ru
 * Domain Path: /languages
 */

class Prodalet_Plugin {
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
	}

	function init() {
		$this->options = array_merge( array(
			'counter-code' => '',
		), (array) get_option( 'prodalet', array() ) );

		load_plugin_textdomain( 'prodalet', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	function admin_init() {
		register_setting( 'prodalet', 'prodalet', array( $this, 'sanitize' ) );
		add_settings_section( 'general', '', '', 'prodalet' );
		add_settings_field( 'install-code', __( 'Install code', 'prodalet' ), array( $this, 'field_install_code' ), 'prodalet', 'general' );
	}

	function sanitize( $input ) {
		$output = array();

		if ( isset( $input['install-codee'] ) )
			$output['install-code'] = ( current_user_can( 'unfiltered_html' ) ) ? $input['install-code'] : wp_kses_post( $input['install-code'] );

		return $output;
	}

	function field_counter_code() {
		?>
		<textarea name="prodalet[install-code]" class="code large-text" rows="10"><?php echo esc_textarea( $this->options['install-code'] ); ?></textarea>
		<p class="description"><?php _e( 'If you do not have a install code, you can <a href="http://prodalet.ru/">request one</a>.', 'prodalet' ); ?>
		<?php
	}

	function admin_menu() {
		add_options_page( __( 'ProdaLet', 'prodalet' ), __( 'ProdaLet', 'prodalet' ), 'manage_options', 'prodalet', array( $this, 'render_options' ) );
	}

	function render_options() {
		?>
		<div class="wrap">
	        <h2><?php _e( 'ProdaLet', 'prodalet' ); ?></h2>
	        <p><?php _e( 'Please enter your ProdaLet install code in the field below and click Save Changes.', 'prodalet' ); ?>
	        <form action="options.php" method="POST">
	            <?php settings_fields( 'prodalet' ); ?>
	            <?php do_settings_sections( 'prodalet' ); ?>
	            <?php submit_button(); ?>
	        </form>
	    </div>
		<?php
	}

	function wp_footer() {
		if ( ! empty( $this->options['install-code'] ) )
			echo $this->options['install-code'];
	}
}
$GLOBALS['prodalet_plugin'] = new Prodalet_Plugin;
