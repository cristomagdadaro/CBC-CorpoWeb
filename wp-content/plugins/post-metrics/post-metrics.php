<?php
/**
 * Plugin Name: Post Metrics (Lightweight)
 * Description: Tracks post/page views and engagement events (views, likes, shares, custom). Provides a REST endpoint and frontend tracker script plus a shortcode to display counts.
 * Version: 0.1
 * Author: Automated Assistant
 */

defined( 'ABSPATH' ) || exit;

class PM_Post_Metrics {
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_shortcode( 'post_metrics', array( __CLASS__, 'shortcode_metrics' ) );
		// Admin UI
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	public static function register_routes() {
		register_rest_route( 'post-metrics/v1', '/track', array(
			'methods'  => 'POST',
			'callback' => array( __CLASS__, 'handle_track' ),
			'permission_callback' => '__return_true',
		) );
	}

	public static function handle_track( $request ) {
		$params = $request->get_json_params();
		$post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
		$event = isset( $params['event'] ) ? sanitize_key( $params['event'] ) : 'view';
		$label = isset( $params['label'] ) ? sanitize_text_field( $params['label'] ) : '';

		$allowed = array( 'view', 'like', 'share', 'engagement', 'custom' );
		if ( ! $post_id || ! get_post_status( $post_id ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'invalid post_id' ), 400 );
		}
		if ( ! in_array( $event, $allowed, true ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'invalid event' ), 400 );
		}

		// Simple server-side rate limiting by IP for short intervals to avoid spammy repeated hits.
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		$transient_key = 'pm_track_' . md5( $event . '_' . $post_id . '_' . $ip );
		if ( get_transient( $transient_key ) ) {
			return new WP_REST_Response( array( 'success' => true, 'skipped' => true ), 200 );
		}
		// lock for 30 seconds
		set_transient( $transient_key, 1, 30 );

		$meta = get_post_meta( $post_id, 'pm_metrics', true );
		if ( ! is_array( $meta ) ) {
			$meta = array( 'views' => 0, 'likes' => 0, 'shares' => 0, 'engagements' => 0, 'custom' => array() );
		}

		switch ( $event ) {
			case 'view':
				$meta['views'] = intval( $meta['views'] ?? 0 ) + 1;
				break;
			case 'like':
				$meta['likes'] = intval( $meta['likes'] ?? 0 ) + 1;
				break;
			case 'share':
				$meta['shares'] = intval( $meta['shares'] ?? 0 ) + 1;
				break;
			case 'engagement':
				$meta['engagements'] = intval( $meta['engagements'] ?? 0 ) + 1;
				break;
			default:
				// custom event aggregated by label
				if ( ! isset( $meta['custom'][ $label ] ) ) {
					$meta['custom'][ $label ] = 0;
				}
				$meta['custom'][ $label ] = intval( $meta['custom'][ $label ] ) + 1;
				break;
		}

		update_post_meta( $post_id, 'pm_metrics', $meta );

		return new WP_REST_Response( array( 'success' => true, 'metrics' => $meta ), 200 );
	}

	public static function enqueue_scripts() {
		if ( ! is_singular() ) {
			return;
		}
		global $post;
		wp_register_script( 'pm-tracker', plugin_dir_url( __FILE__ ) . 'js/pm-tracker.js', array(), '0.1', true );
		wp_localize_script( 'pm-tracker', 'PM_TRACKER', array(
			'rest_url' => esc_url_raw( rest_url( 'post-metrics/v1/track' ) ),
			'post_id'  => intval( $post->ID ),
		) );
		wp_enqueue_script( 'pm-tracker' );
	}

	// Admin metabox registration
	public static function register_meta_box() {
		add_meta_box( 'pm_metrics', __( 'Post Metrics', 'post-metrics' ), array( __CLASS__, 'render_meta_box' ), array( 'post', 'page' ), 'side', 'high' );
	}

	public static function render_meta_box( $post ) {
		$meta = get_post_meta( $post->ID, 'pm_metrics', true );
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}
		$views = intval( $meta['views'] ?? 0 );
		$likes = intval( $meta['likes'] ?? 0 );
		$shares = intval( $meta['shares'] ?? 0 );
		$eng = intval( $meta['engagements'] ?? 0 );
		echo '<div class="pm-meta-box">';
		echo '<p><strong>' . esc_html__( 'Views', 'post-metrics' ) . ':</strong> ' . esc_html( $views ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Likes', 'post-metrics' ) . ':</strong> ' . esc_html( $likes ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Shares', 'post-metrics' ) . ':</strong> ' . esc_html( $shares ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Engagements', 'post-metrics' ) . ':</strong> ' . esc_html( $eng ) . '</p>';
		if ( ! empty( $meta['custom'] ) && is_array( $meta['custom'] ) ) {
			echo '<hr /><p><strong>' . esc_html__( 'Custom events', 'post-metrics' ) . '</strong></p><ul>';
			foreach ( $meta['custom'] as $k => $v ) {
				echo '<li>' . esc_html( $k ) . ': ' . intval( $v ) . '</li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}

	// Admin menu and page
	public static function admin_menu() {
		add_menu_page( esc_html__( 'Post Metrics', 'post-metrics' ), esc_html__( 'Post Metrics', 'post-metrics' ), 'manage_options', 'pm-post-metrics', array( __CLASS__, 'admin_page' ), 'dashicons-chart-bar', 26 );
	}

	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'post-metrics' ) );
		}

		// Export CSV when requested
		if ( isset( $_GET['export'] ) && $_GET['export'] ) {
			self::maybe_export_csv();
		}

		$show = isset( $_GET['number'] ) ? intval( $_GET['number'] ) : 50;
		if ( $show <= 0 ) $show = 50;

		// Fetch published posts/pages that have pm_metrics
		$args = array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array( 'key' => 'pm_metrics' )
			)
		);
		$all = get_posts( $args );

		// Determine pagination
		$per_page = $show; // max per page when viewing
		$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$offset = ( $paged - 1 ) * $per_page;

		// If exporting, fetch all rows; otherwise fetch limited page
		$q_args = $args;
		if ( isset( $_GET['export'] ) && $_GET['export'] ) {
			$q_args['posts_per_page'] = -1;
		} else {
			$q_args['posts_per_page'] = $per_page;
			$q_args['offset'] = $offset;
		}
		$all = get_posts( $q_args );

		// When not exporting we also need total count for pagination
		$total_count = 0;
		if ( ! ( isset( $_GET['export'] ) && $_GET['export'] ) ) {
			$count_q = $args;
			$count_q['posts_per_page'] = -1;
			$all_for_count = get_posts( $count_q );
			foreach ( $all_for_count as $p ) {
				$meta = get_post_meta( $p->ID, 'pm_metrics', true );
				if ( ! is_array( $meta ) ) $meta = array();
				if ( intval( $meta['views'] ?? 0 ) > 0 ) $total_count++;
			}
			$total_pages = max( 1, ceil( $total_count / $per_page ) );
		}

		$rows = array();
		foreach ( $all as $p ) {
			$meta = get_post_meta( $p->ID, 'pm_metrics', true );
			if ( ! is_array( $meta ) ) $meta = array();
			$views = intval( $meta['views'] ?? 0 );
			if ( $views === 0 ) continue; // skip zero-view items in listing
			$rows[] = array(
				'id' => $p->ID,
				'title' => get_the_title( $p ),
				'views' => $views,
				'likes' => intval( $meta['likes'] ?? 0 ),
				'shares' => intval( $meta['shares'] ?? 0 ),
				'engagements' => intval( $meta['engagements'] ?? 0 ),
				'url' => get_edit_post_link( $p ),
			);
		}

		// Sort by views desc
		usort( $rows, function( $a, $b ) { return $b['views'] <=> $a['views']; } );
		$top = $rows; // already limited by query

		echo '<div class="wrap"><h1>' . esc_html__( 'Post Metrics - Top Posts', 'post-metrics' ) . '</h1>';
		echo '<p>' . esc_html__( 'Showing top posts by views. Adjust count or export CSV.', 'post-metrics' ) . '</p>';
		echo '<p><a class="button" href="' . esc_url( add_query_arg( array( 'export' => '1' ) ) ) . '">' . esc_html__( 'Export CSV', 'post-metrics' ) . '</a> ';
		echo '<form style="display:inline-block;margin-left:12px;" method="get">';
		echo '<input type="hidden" name="page" value="pm-post-metrics" />';
		echo '<label>' . esc_html__( 'Show top', 'post-metrics' ) . ' <input name="number" type="number" value="' . esc_attr( $show ) . '" style="width:80px;"/></label> <input type="submit" class="button" value="' . esc_attr__( 'Apply', 'post-metrics' ) . '" />';
		echo '</form></p>';

		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Rank', 'post-metrics' ) . '</th><th>' . esc_html__( 'Title', 'post-metrics' ) . '</th><th>' . esc_html__( 'Views', 'post-metrics' ) . '</th><th>' . esc_html__( 'Likes', 'post-metrics' ) . '</th><th>' . esc_html__( 'Shares', 'post-metrics' ) . '</th><th>' . esc_html__( 'Engagements', 'post-metrics' ) . '</th></tr></thead><tbody>';
		$rank = 1;
		foreach ( $top as $row ) {
			echo '<tr>';
			echo '<td>' . esc_html( $rank ) . '</td>';
			echo '<td><a href="' . esc_url( $row['url'] ) . '">' . esc_html( $row['title'] ) . '</a></td>';
			echo '<td>' . esc_html( $row['views'] ) . '</td>';
			echo '<td>' . esc_html( $row['likes'] ) . '</td>';
			echo '<td>' . esc_html( $row['shares'] ) . '</td>';
			echo '<td>' . esc_html( $row['engagements'] ) . '</td>';
			echo '</tr>';
			$rank++;
		}
		echo '</tbody></table>';

		if ( ! ( isset( $_GET['export'] ) && $_GET['export'] ) ) {
			// Simple pagination links
			echo '<p class="tablenav"><span class="pagination-links">';
			if ( $paged > 1 ) {
				echo '<a class="button" href="' . esc_url( add_query_arg( array( 'paged' => $paged - 1 ) ) ) . '">&laquo; ' . esc_html__( 'Prev', 'post-metrics' ) . '</a> ';
			}
			if ( $paged < $total_pages ) {
				echo '<a class="button" href="' . esc_url( add_query_arg( array( 'paged' => $paged + 1 ) ) ) . '">' . esc_html__( 'Next', 'post-metrics' ) . ' &raquo;</a>';
			}
			echo '</span></p>';
		}

		echo '</div>';
	}

	public static function maybe_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		// Gather same rows as admin_page
		$args = array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array( 'key' => 'pm_metrics' )
			)
		);
		$all = get_posts( $args );
		$rows = array();
		foreach ( $all as $p ) {
			$meta = get_post_meta( $p->ID, 'pm_metrics', true );
			if ( ! is_array( $meta ) ) $meta = array();
			$rows[] = array( 'ID' => $p->ID, 'title' => get_the_title( $p ), 'views' => intval( $meta['views'] ?? 0 ), 'likes' => intval( $meta['likes'] ?? 0 ), 'shares' => intval( $meta['shares'] ?? 0 ), 'engagements' => intval( $meta['engagements'] ?? 0 ) );
		}
		usort( $rows, function( $a, $b ) { return $b['views'] <=> $a['views']; } );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=post-metrics.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'Title', 'Views', 'Likes', 'Shares', 'Engagements' ) );
		foreach ( $rows as $r ) {
			fputcsv( $out, array( $r['ID'], $r['title'], $r['views'], $r['likes'], $r['shares'], $r['engagements'] ) );
		}
		fclose( $out );
		exit;
	}

	public static function shortcode_metrics( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0, 'show' => 'views,likes' ), $atts, 'post_metrics' );
		$post_id = intval( $atts['id'] ) ? intval( $atts['id'] ) : get_the_ID();
		$meta = get_post_meta( $post_id, 'pm_metrics', true );
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}
		$parts = explode( ',', $atts['show'] );
		$out = '<div class="pm-metrics">';
		foreach ( $parts as $p ) {
			$p = trim( $p );
			$val = 0;
			switch ( $p ) {
				case 'views':
					$val = intval( $meta['views'] ?? 0 );
					break;
				case 'likes':
					$val = intval( $meta['likes'] ?? 0 );
					break;
				case 'shares':
					$val = intval( $meta['shares'] ?? 0 );
					break;
				case 'engagements':
					$val = intval( $meta['engagements'] ?? 0 );
					break;
				default:
					if ( isset( $meta['custom'][ $p ] ) ) {
						$val = intval( $meta['custom'][ $p ] );
					}
					break;
			}
			$out .= '<span class="pm-' . esc_attr( $p ) . '">' . esc_html( ucfirst( $p ) ) . ': <strong>' . esc_html( $val ) . '</strong></span> ';
		}
		$out .= '</div>';
		return $out;
	}
}

PM_Post_Metrics::init();
