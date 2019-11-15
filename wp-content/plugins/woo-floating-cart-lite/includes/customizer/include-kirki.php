<?php
/**
 * This file implements custom requirements for the Kirki plugin.
 * It can be used as-is in themes (drop-in).
 *
 * @package kirki-helpers
 */

if ( ! class_exists( 'Kirki' ) ) {

	if ( class_exists( 'WP_Customize_Section' ) && ! class_exists( 'Kirki_Installer_Control' ) ) {
		/**
		 * A simple control that will render the installer <iframe>.
		 * We'll apply some CSS in order to move the section to the top
		 * as well as style the section & the iframe.
		 */
		class Kirki_Installer_Control extends WP_Customize_Control {

			/**
			 * The control-type.
			 *
			 * @access public
			 * @var string
			 */
			public $type = 'kirki-installer';

			/**
			 * Renders the control.
			 *
			 * @access public
			 */
			public function render_content() {
				?>
				<style>
				li#accordion-section-kirki_installer { background:#f3f3f3; margin:-15px 0; }
				li#accordion-section-kirki_installer .accordion-section-title,li#accordion-section-kirki_installer .customize-section-title { display: none; }
				li#accordion-section-kirki_installer ul.accordion-section-content { display: block; position: relative; left: 0; margin-top: 0 !important; padding-top: 0; padding-bottom: 0; }
				#customize-controls li#accordion-section-kirki_installer .description { font-size: 1em; }
				</style>
				<?php $plugins   = get_plugins(); ?>
				<?php $installed = false; ?>
				<?php foreach ( $plugins as $plugin ) : ?>
					<?php if ('XT Customizer Kit' === $plugin['Name'] ) : ?>
						<?php $installed = true; ?>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ( ! $installed ) : ?>

					<?php
						$plugin_slug = 'kirki';

						$plugin_install_url = add_query_arg(
							array(
								'action' => 'install-plugin',
								'plugin' => $plugin_slug,
							),
							self_admin_url( 'update.php' )
						);

						$nonce_key = 'install-plugin_' . $plugin_slug;

						$plugin_install_url = wp_nonce_url( $plugin_install_url, $nonce_key );
					?>

					<a class="install-now button-primary button" data-slug="kirki" href="<?php echo esc_url( $plugin_install_url ); ?>" aria-label="Install Kirki Toolkit now" data-name="Kirki Toolkit"><?php esc_html_e( 'Install Now','woo-floating-cart' ); ?></a>

					<br/></br><!-- Added <br/> tags to fix the spacing -->

				<?php else : ?>
					<hr>
					<p><?php printf( __( 'The plugin is installed but not activated. Please <a href="%s">activate it</a>.', 'woo-floating-cart' ), esc_url_raw( admin_url( 'plugins.php' ) ) ); ?></p>
				<?php endif;
			}
		}

	}

	if ( ! function_exists( 'kirki_installer_register' ) ) {
		/**
		 * Registers the section, setting & control for the kirki installer.
		 *
		 * @param object $wp_customize The main customizer object.
		 */
		function kirki_installer_register( $wp_customize ) {
			// Add the section.
			// You can add your description here.
			// Please note that the title will not be displayed.
			$wp_customize->add_section( 'kirki_installer', array(
				'title'       => '',
				'description' => sprintf(esc_html__( 'If you wish to customize the look & feel of %sXT Woo Floating Cart%s, please install the required %sXT Customizer Kit%s plugin.', 'woo-floating-cart' ), '<strong>', '</strong>', '<strong>', '</strong>'),
				'priority'    => -10,
				'capability'  => 'install_plugins',
			) );
			// Add the setting. This is required by WordPress in order to add our control.
			$wp_customize->add_setting( 'kirki_installer', array(
				'type'              => 'theme_mod',
				'capability'        => 'install_plugins',
				'default'           => '',
				'sanitize_callback' => '__return_true',
			));
			// Add our control. This is required in order to show the section.
			$wp_customize->add_control( new Kirki_Installer_Control( $wp_customize, 'kirki_installer', array(
				'section' => 'kirki_installer',
			) ) );

		}
		add_action( 'customize_register', 'kirki_installer_register' );
	}
}
