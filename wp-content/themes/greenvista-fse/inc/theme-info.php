<?php
/**
 * Add Theme info Page
 */

function greenvista_fse_menu() {
	add_theme_page( esc_html__( 'GreenVista FSE', 'greenvista-fse' ), esc_html__( 'About GreenVista FSE', 'greenvista-fse' ), 'edit_theme_options', 'about-greenvista-fse', 'greenvista_fse_theme_page_display' );
}
add_action( 'admin_menu', 'greenvista_fse_menu' );

function greenvista_fse_admin_theme_style() {
	wp_enqueue_style('greenvista-fse-custom-admin-style', esc_url(get_template_directory_uri()) . '/assets/css/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'greenvista_fse_admin_theme_style');

/**
 * Display About page
 */
function greenvista_fse_theme_page_display() {
	$theme = wp_get_theme();

	if ( is_child_theme() ) {
		$theme = wp_get_theme()->parent();
	} ?>

		<div class="Grace-wrapper">
			<div class="Grcae-info-holder">
				<div class="Grcae-info-holder-content">
					<div class="Grace-Welcome">
						<h1 class="welcomeTitle"><?php esc_html_e( 'About Theme Info', 'greenvista-fse' ); ?></h1>                        
						<div class="featureDesc">
							<?php echo esc_html__( 'The GreenVista FSE is a free lawnshaper WordPress theme for agriculture, environment, florist, garden, gardeners, gardening, landscape architects, landscaper, landscaping, lawn services.', 'greenvista-fse' ); ?>
						</div>
						
                        <h1 class="welcomeTitle"><?php esc_html_e( 'Theme Features', 'greenvista-fse' ); ?></h1>

                        <h2><?php esc_html_e( 'Block Compatibale', 'greenvista-fse' ); ?></h2>
                        <div class="featureDesc">
                            <?php echo esc_html__( 'The built-in customizer panel quickly change aspects of the design and display changes live before saving them.', 'greenvista-fse' ); ?>
                        </div>
                        
                        <h2><?php esc_html_e( 'Responsive Ready', 'greenvista-fse' ); ?></h2>
                        <div class="featureDesc">
                            <?php echo esc_html__( 'The themes layout will automatically adjust and fit on any screen resolution and looks great on any device. Fully optimized for iPhone and iPad.', 'greenvista-fse' ); ?>
                        </div>
                        
                        <h2><?php esc_html_e( 'Cross Browser Compatible', 'greenvista-fse' ); ?></h2>
                        <div class="featureDesc">
                            <?php echo esc_html__( 'Our themes are tested in all mordern web browsers and compatible with the latest version including Chrome,Firefox, Safari, Opera, IE11 and above.', 'greenvista-fse' ); ?>
                        </div>
                        
                        <h2><?php esc_html_e( 'E-commerce', 'greenvista-fse' ); ?></h2>
                        <div class="featureDesc">
                            <?php echo esc_html__( 'Fully compatible with WooCommerce plugin. Just install the plugin and turn your site into a full featured online shop and start selling products.', 'greenvista-fse' ); ?>
                        </div>

					</div> <!-- .Grace-Welcome -->
				</div> <!-- .Grcae-info-holder-content -->
				
				
				<div class="Grcae-info-holder-sidebar">
                        <div class="sidebarBX">
                            <h2 class="sidebarBX-title"><?php echo esc_html__( 'Get GreenVista PRO', 'greenvista-fse' ); ?></h2>
                            <p><?php echo esc_html__( 'More features availbale on Premium version', 'greenvista-fse' ); ?></p>
                            <a href="<?php echo esc_url( 'https://gracethemes.com/themes/gardener-wordpress-theme/' ); ?>" target="_blank" class="button"><?php esc_html_e( 'Get the PRO Version &rarr;', 'greenvista-fse' ); ?></a>
                        </div>


						<div class="sidebarBX">
							<h2 class="sidebarBX-title"><?php echo esc_html__( 'Important Links', 'greenvista-fse' ); ?></h2>

							<ul class="themeinfo-links">
                                <li>
									<a href="<?php echo esc_url( 'https://gracethemesdemo.com/greenvista/' ); ?>" target="_blank"><?php echo esc_html__( 'Demo Preview', 'greenvista-fse' ); ?></a>
								</li>                               
								<li>
									<a href="<?php echo esc_url( 'https://gracethemesdemo.com/documentation/greenvista/#homepage-lite' ); ?>" target="_blank"><?php echo esc_html__( 'Documentation', 'greenvista-fse' ); ?></a>
								</li>
								
								<li>
									<a href="<?php echo esc_url( 'https://gracethemes.com/wordpress-themes/' ); ?>" target="_blank"><?php echo esc_html__( 'View Our Premium Themes', 'greenvista-fse' ); ?></a>
								</li>
							</ul>
						</div>

						<div class="sidebarBX">
							<h2 class="sidebarBX-title"><?php echo esc_html__( 'Leave us a review', 'greenvista-fse' ); ?></h2>
							<p><?php echo esc_html__( 'If you are satisfied with GreenVista FSE, please give your feedback.', 'greenvista-fse' ); ?></p>
							<a href="https://wordpress.org/support/theme/greenvista-fse/reviews/" class="button" target="_blank"><?php esc_html_e( 'Submit a review', 'greenvista-fse' ); ?></a>
						</div>

				</div><!-- .Grcae-info-holder-sidebar -->	

			</div> <!-- .Grcae-info-holder -->
		</div><!-- .Grace-wrapper -->
<?php } ?>