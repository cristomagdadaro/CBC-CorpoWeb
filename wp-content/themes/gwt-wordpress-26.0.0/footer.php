<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package GWT
 * @since Government Website Template 2.0
 */
?>
            <!-- agency footer -->
            <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ||
                       is_active_sidebar( 'footer-4' ) ): ?>
                <div id="footer" class="anchor" name="agencyfooter">
                    <div id="supplementary">
                        <?php if ( is_active_sidebar( 'footer-1' ) ): ?>
                            <div class="<?php govph_displayoptions( 'govph_position_agency_footer' ); ?> p-0" role="complementary">
                                <?php do_action( 'before_sidebar' ); ?>
                                <?php dynamic_sidebar( 'footer-1' ) ?>
                            </div>
                        <?php endif; // if active footer-1 ?>

                        <?php if ( is_active_sidebar( 'footer-2' ) ): ?>
                            <div class="<?php govph_displayoptions( 'govph_position_agency_footer' ); ?> p-0" role="complementary">
                                <?php do_action( 'before_sidebar' ); ?>
                                <?php dynamic_sidebar( 'footer-2' ) ?>
                            </div>
                        <?php endif; // if active footer-2 ?>

                        <?php if ( is_active_sidebar( 'footer-3' ) ): ?>
                            <div class="<?php govph_displayoptions( 'govph_position_agency_footer' ); ?> p-0" role="complementary">
                                <?php do_action( 'before_sidebar' ); ?>
                                <?php dynamic_sidebar( 'footer-3' ) ?>
                            </div>
                        <?php endif; // if active footer-3 ?>

                        <?php if ( is_active_sidebar( 'footer-4' ) ): ?>
                            <div class="<?php govph_displayoptions( 'govph_position_agency_footer' ); ?> p-0" role="complementary">
                                <?php do_action( 'before_sidebar' ); ?>
                                <?php dynamic_sidebar( 'footer-4' ) ?>
                            </div>
                        <?php endif; // if active footer-4 ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-[#006837] w-full sm:py-0 py-2">
                <div class="row lg:p-4 p-1">
                    <div class="flex flex-col gap-1 sm:mb-5 mb-2">
                        <p class="lg:text-2xl md:text-xl text-lg text-white w-full border border-white sm:p-3 p-1 rounded text-center">Biotechnology Centers under DA-Biotechnology Program Office</p>
                        <div class="flex flex-row gap-1 items-center justify-center">
                            <a href="/" class="hover:scale-105 duration-200">
                                <img draggable="false" src="/wp-content/uploads/2024/06/DA-CBC-Logo-white-DA.png"
                                     alt="logo" class="w-48 h-auto">
                            </a>
                            <a draggable="false" href="https://livestockbiotech.ph/" target="_blank" class="hover:scale-105 duration-200">
                                <img src="/wp-content/uploads/2024/06/LBC-white-Logo.png"
                                     alt="logo" class="w-48 h-auto">
                            </a>
                            <a draggable="false" href="https://fbc.nfrdi.da.gov.ph/" target="_blank" class="hover:scale-105 duration-200">
                                <img src="/wp-content/uploads/2024/06/FBC-white-Logo.png"
                                     alt="logo" class="w-48 h-auto">
                            </a>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 grid-cols-1 text-white text-sm">
                        <div>
                            <h1 class="text-center border-b font-bold m-5 p-1">Quick Links</h1>
                            <ul class="list-none m-5">
                                <li><a href="/about-us">About Us</a></li>
                                <li><a href="/articles">Articles</a></li>
                                <li><a href="/faqs">FAQs</a></li>
                                <li><a href="/sitemap">Sitemap</a></li>
                            </ul>
                        </div>
                        <div>
                            <h1 class="text-center border-b font-bold m-5 p-1">Contact Us</h1>
                            <ul class="list-none m-5">
                                <li>Telephone: <a href="tel:+639088897135">(+63) 908 889 7135</a></li>
                                <li>Email:
                                    <a href="mailto:cropbiotechcenter@gmail.com">cropbiotechcenter@gmail.com</a>
                                </li>
                                <li>Facebook: <a href="https://www.facebook.com/DACropBiotechCenter" target="_blank">DA-Crop Biotechnology Center </a></li>
                                <li>Headquarter: PhilRice Compound, Brgy. Maligaya, Science City of Muñoz, Nueva Ecija 3119, Philippines</li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-center text-white py-1 text-xs">
                        <a href="http://philrice.gov.ph/" target="_blank">Powered by Philippine Rice Research Institute (PhilRice)</a>
                    </div>
                </div>
            </div>
            <!-- standard footer -->
            <div id="gwt-standard-footer" class="select-none"></div>
            <!-- end standard footer -->

        </div><!-- #off-canvass-content -->
    </div><!-- #off-canvass-wrapper inner -->
</div><!-- #off-canvass-wrapper -->

<script>
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                //get one child element height
                var child = panel.children[0];
                var childHeight = child.scrollHeight;
                //get all child elements height
                var children = panel.children;
                var childrenHeight = 0;
                for (var i = 0; i < children.length; i++) {
                    childrenHeight += children[i].scrollHeight;
                }
                //set max height
                panel.style.maxHeight = (childHeight + childrenHeight) + "px";
            }
        });
    }
</script>

<!-- Scroll reveal (slide-up + fade-in) using Tailwind classes -->
<script>
    (function(){
        function initReveal() {
            var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Select all elements with reveal classes
            var targets = document.querySelectorAll('[class*="reveal-on-scroll"]');
            if (!targets.length) return;

            targets.forEach(function(el){
                if (prefersReduced) {
                    el.classList.add('opacity-100', 'translate-y-0');
                    el.classList.remove('opacity-0', 'translate-y-8');
                    return;
                }
                // initial state
                el.classList.add('opacity-0', 'translate-y-8', 'transition', 'duration-700', 'ease-out', 'will-change-transform');
            });

            if (prefersReduced) return;

            var io = new IntersectionObserver(function(entries, observer){
                entries.forEach(function(entry){
                    if (entry.isIntersecting) {
                        var t = entry.target;

                        // 🔍 Check if class has delay (e.g., reveal-on-scroll-300)
                        var match = t.className.match(/reveal-on-scroll-(\d+)/);
                        var delay = match ? parseInt(match[1], 10) : 0;

                        setTimeout(function(){
                            t.classList.remove('opacity-0', 'translate-y-8');
                            t.classList.add('opacity-100', 'translate-y-0');
                        }, delay);

                        observer.unobserve(t);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });

            targets.forEach(function(el){ io.observe(el); });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initReveal);
        } else {
            initReveal();
        }
    })();
</script>

<!-- end scroll reveal -->

<!-- standard footer script -->
<script type="text/javascript">
    (function (d, s, id) {
        var js, gjs = d.getElementById('gwt-standard-footer');

        js = d.createElement(s);
        js.id = id;
        js.src = "//gwhs.i.gov.ph/gwt-footer/footer.js";
        gjs.parentNode.insertBefore(js, gjs);
    }(document, 'script', 'gwt-footer-jsdk'));
</script>

<!-- philippine standard time script-->
<script type="text/javascript" id="gwt-pst">
    (function (d, eId) {
        var js, gjs = d.getElementById(eId);
        js = d.createElement('script');
        js.id = 'gwt-pst-jsdk';
        js.src = "//gwhs.i.gov.ph/pst/gwtpst.js?" + new Date().getTime();
        gjs.parentNode.insertBefore(js, gjs);
    }(document, 'gwt-pst'));

    var gwtpstReady = function () {
        var firstPst = new gwtpstTime('pst-time');
    }
</script>
<!-- end philippine standard time -->

<?php wp_footer(); ?>

<div><a href="#page" id="back-to-top" style="display: inline;"><i class="fa fa-arrow-circle-up fa-2x"></i></a></div>
</body>

</html>