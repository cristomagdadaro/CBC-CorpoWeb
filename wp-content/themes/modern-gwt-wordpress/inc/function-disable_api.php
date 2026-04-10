<?php
add_filter( 'rest_authentication_errors', function( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}

	$private_namespaces = apply_filters(
		'cbc_private_rest_namespaces',
		array(
			'/tailwind-manager/v1/',
			'/cbc-monitor/v1/',
			'/cbc-games/v1/',
			'/post-metrics/v1/',
		)
	);

	$route = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$route = $route ? wp_parse_url( $route, PHP_URL_PATH ) : '';
	$route = is_string( $route ) ? $route : '';
	$rest_prefix = '/' . trim( rest_get_url_prefix(), '/' );

	foreach ( $private_namespaces as $namespace ) {
		$namespace = '/' . trim( (string) $namespace, '/' ) . '/';
		$needle    = $rest_prefix . $namespace;

		if ( $route && 0 === strpos( trailingslashit( $route ), $needle ) && ! is_user_logged_in() ) {
			return new WP_Error(
				'restx_logged_out',
				__( 'Sorry, you must be logged in to make this request.', 'modern-gwt-wordpress' ),
				array( 'status' => 401 )
			);
		}
	}

	return $result;
});