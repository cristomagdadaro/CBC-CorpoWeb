<?php
/**
 * Footer template.
 *
 * @package ModernBiotechWordPress
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<footer class="mbw-footer" id="site-footer">
    <div class="mbw-shell">
        <div class="mbw-footer-grid">
            <section>
                <h2><?php bloginfo('name'); ?></h2>
                <p class="mbw-footer-lead">
                    Biotech for better crops and better lives through research, innovation, and public service.
                </p>
                <p>Email: <a href="mailto:cropbiotechcenter@gmail.com">cropbiotechcenter@gmail.com</a></p>
                <p>Phone: <a href="tel:+639088897135">(+63) 908 889 7135</a></p>
            </section>

            <section>
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#research">Research Portfolio</a></li>
                    <li><a href="#services">Laboratory Services</a></li>
                    <li><a href="#experts">Scientist Directory</a></li>
                    <li><a href="#calendar">Events Calendar</a></li>
                </ul>
            </section>

            <section>
                <h3>Partner Platforms</h3>
                <ul>
                    <li><a href="https://pin.philrice.gov.ph" target="_blank" rel="noopener noreferrer">PIN Database</a></li>
                    <li><a href="https://onecbc.philrice.gov.ph" target="_blank" rel="noopener noreferrer">OneCBC Portal</a></li>
                    <li><a href="https://www.philrice.gov.ph" target="_blank" rel="noopener noreferrer">DA-PhilRice</a></li>
                </ul>
            </section>
        </div>

        <div class="mbw-footer-bar">
            <p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
