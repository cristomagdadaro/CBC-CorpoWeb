<?php
wp_nav_menu(array(
	'theme_location' => 'primary',
	'walker'         => new GWT_Walker_Nav_Menu(),
	'menu_class'     => 'gwt-menu flex space-x-4',
));

