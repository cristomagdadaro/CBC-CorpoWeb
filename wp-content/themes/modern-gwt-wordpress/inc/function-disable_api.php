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
		)
	);

	$route = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

	foreach ( $private_namespaces as $namespace ) {
		if ( $route && 0 === strpos( $route, $namespace ) && ! is_user_logged_in() ) {
			return new WP_Error(
				'restx_logged_out',
				__( 'Sorry, you must be logged in to make this request.', 'modern-gwt-wordpress' ),
				array( 'status' => 401 )
			);
		}
	}

	return $result;
});