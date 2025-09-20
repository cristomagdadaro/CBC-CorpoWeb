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
		// register pm_buttons shortcode
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
		// Admin UI
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		// dashboard widget
		add_action( 'init', array( __CLASS__, 'register_dashboard_widgets' ) );
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

		// support 'comment' as a distinct event that requires auth
		$allowed = array( 'view', 'like', 'share', 'engagement', 'comment', 'custom' );
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

		// Enforce authentication for sensitive actions: likes and comments require a logged-in user
		if ( in_array( $event, array( 'like', 'comment' ), true ) && ! is_user_logged_in() ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'auth_required' ), 401 );
		}

		// Reintroduce 'viewers' tracked via cookie (30 days)
		$meta = get_post_meta( $post_id, 'pm_metrics', true );
		if ( ! is_array( $meta ) ) {
			$meta = array( 'views' => 0, 'viewers' => 0, 'likes' => 0, 'shares' => 0, 'comments' => 0, 'engagements' => 0, 'custom' => array() );
		}

		// Determine if visitor cookie exists for this post
		$cookie_name = 'pm_viewed_' . $post_id;
		$has_cookie = isset( $_COOKIE[ $cookie_name ] ) && $_COOKIE[ $cookie_name ];

		switch ( $event ) {
			case 'view':
				// increment views (raw page views)
				$meta['views'] = intval( $meta['views'] ?? 0 ) + 1;
				// If visitor hasn't got the post cookie, count as a unique viewer and set cookie for 30 days
				if ( ! $has_cookie ) {
					$meta['viewers'] = intval( $meta['viewers'] ?? 0 ) + 1;
					// set cookie so subsequent views in the next 30 days won't be double-counted
					setcookie( $cookie_name, '1', time() + ( DAY_IN_SECONDS * 30 ), COOKIEPATH ? COOKIEPATH : '/' );
					// also set in PHP superglobal so subsequent logic in this request sees it
					$_COOKIE[ $cookie_name ] = '1';
					$has_cookie = true;
				}
				break;
			case 'like':
				$meta['likes'] = intval( $meta['likes'] ?? 0 ) + 1;
				break;
			case 'share':
				// allow anonymous shares
				$meta['shares'] = intval( $meta['shares'] ?? 0 ) + 1;
				break;
			case 'engagement':
				$meta['engagements'] = intval( $meta['engagements'] ?? 0 ) + 1;
				break;
			case 'comment':
				// comment event requires login (checked earlier)
				$meta['comments'] = intval( $meta['comments'] ?? 0 ) + 1;
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

		// Update monthly buckets (YYYY-MM)
		$month_key = date( 'Y-m' );
		$monthly = get_post_meta( $post_id, 'pm_metrics_monthly', true );
		if ( ! is_array( $monthly ) ) $monthly = array();
		if ( ! isset( $monthly[ $month_key ] ) ) {
			$monthly[ $month_key ] = array( 'views' => 0, 'likes' => 0, 'shares' => 0, 'comments' => 0, 'engagements' => 0, 'custom' => array() );
		}
		switch ( $event ) {
			case 'view':
				$monthly[ $month_key ]['views'] = intval( $monthly[ $month_key ]['views' ] ?? 0 ) + 1;
				$monthly[ $month_key ]['viewers'] = intval( $monthly[ $month_key ]['viewers' ] ?? 0 ) + 1;
				break;
			case 'like':
				$monthly[ $month_key ]['likes'] = intval( $monthly[ $month_key ]['likes' ] ?? 0 ) + 1;
				break;
			case 'share':
				$monthly[ $month_key ]['shares'] = intval( $monthly[ $month_key ]['shares' ] ?? 0 ) + 1;
				break;
			case 'engagement':
				$monthly[ $month_key ]['engagements'] = intval( $monthly[ $month_key ]['engagements' ] ?? 0 ) + 1;
				break;
			case 'comment':
				$monthly[ $month_key ]['comments'] = intval( $monthly[ $month_key ]['comments' ] ?? 0 ) + 1;
				break;
			default:
				if ( ! isset( $monthly[ $month_key ]['custom'][ $label ] ) ) {
					$monthly[ $month_key ]['custom'][ $label ] = 0;
				}
				$monthly[ $month_key ]['custom'][ $label ] = intval( $monthly[ $month_key ]['custom'][ $label ] ) + 1;
				break;
		}
		update_post_meta( $post_id, 'pm_metrics_monthly', $monthly );

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
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'is_logged_in' => is_user_logged_in(),
			'login_url' => wp_login_url( get_permalink( $post->ID ) ),
			'register_url' => function_exists( 'wp_registration_url' ) ? '/wp-login.php?action=register' : '',
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
		$comments = intval( $meta['comments'] ?? 0 );
		echo '<div class="pm-meta-box">';
		echo '<p><strong>' . esc_html__( 'Views', 'post-metrics' ) . ':</strong> ' . esc_html( $views ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Likes', 'post-metrics' ) . ':</strong> ' . esc_html( $likes ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Shares', 'post-metrics' ) . ':</strong> ' . esc_html( $shares ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Comments', 'post-metrics' ) . ':</strong> ' . esc_html( $comments ) . '</p>';
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
				case 'comments':
					$val = intval( $meta['comments'] ?? 0 );
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

	public static function register_shortcodes() {
		add_shortcode( 'pm_buttons', array( __CLASS__, 'shortcode_buttons' ) );
	}

	public static function shortcode_buttons( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0, 'show_counts' => '1' ), $atts, 'pm_buttons' );
		$post_id = intval( $atts['id'] ) ? intval( $atts['id'] ) : get_the_ID();
		$meta = get_post_meta( $post_id, 'pm_metrics', true );
		$likes = intval( $meta['likes'] ?? 0 );
		$shares = intval( $meta['shares'] ?? 0 );

		$like_btn = '<button class="pm-btn pm-like flex items-center gap-2" data-pm-event="like" hover:scale-105 data-pm-label="shortcode-like" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">
  <path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a10 10 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733q.086.18.138.363c.077.27.113.567.113.856s-.036.586-.113.856c-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.2 3.2 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.8 4.8 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/>
</svg><span class="pm-like-count">' . esc_html( $likes ) . '</span></button>';
		$share_btn = '<div class="pm-btn pm-share flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-share-fill" viewBox="0 0 16 16">
  <path d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5"/>
</svg><span class="pm-share-count">' . esc_html( $shares ) . '</span></div>';

		// Inline script: use server-side login state and require login only for like/comment; allow anonymous share
		$login_url_js = esc_js( wp_login_url( get_permalink( $post_id ) ) );
		$reg_url_js = esc_js( function_exists( 'wp_registration_url' ) ? wp_registration_url() : '/wp-login.php?action=register' );
		$script = "<script>(function(){\n";
		$script .= "var isLogged = " . ( is_user_logged_in() ? 'true' : 'false' ) . ";\n";
		$script .= "var loginUrl = '" . $login_url_js . "';\n";
		$script .= "var regUrl = '" . $reg_url_js . "';\n";
		$script .= "document.addEventListener('click', function(e){\n";
		$script .= " var el = e.target; while(el && el !== document){ if (el.dataset && el.dataset.pmEvent){ var evt = el.dataset.pmEvent; // only require login for like and comment\n";
		$script .= " if (!isLogged && (evt === 'like' || evt === 'comment')){ var goLogin = confirm('You must be logged in to perform this action. Press OK to go to Login, Cancel to go to Register.'); if (goLogin) { window.location.href = loginUrl; } else { window.location.href = regUrl; } return; }\n";
		$script .= " if (evt==='like'){ var c = el.querySelector('.pm-like-count') || document.querySelector('.pm-like-count'); if (c) c.textContent = (parseInt(c.textContent||'0',10)+1); }\n";
		$script .= " if (evt==='share'){ var c2 = el.querySelector('.pm-share-count') || document.querySelector('.pm-share-count'); if (c2) c2.textContent = (parseInt(c2.textContent||'0',10)+1); } break;} el = el.parentNode; } }, false);\n";
		$script .= "})();</script>";

		return '<div class="pm-button-wrap flex items-center gap-5">' . $like_btn . ' ' . $share_btn . '</div>' . $script;
	}

	// Dashboard widget: register and render
	public static function register_dashboard_widgets() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_dashboard_widgets' ) );
	}

	public static function add_dashboard_widgets() {
		wp_add_dashboard_widget( 'pm_dashboard_widget', __( 'Post Metrics', 'post-metrics' ), array( __CLASS__, 'render_dashboard_widget' ) );
	}

	public static function render_dashboard_widget() {
		// Compute month key
		$month_key = date( 'Y-m' );
		$total_views = 0;
		// Find top posts by monthly views
		$args = array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array( array( 'key' => 'pm_metrics_monthly' ) ),
		);
		$all = get_posts( $args );
		$rows = array();
		foreach ( $all as $p ) {
			$m = get_post_meta( $p->ID, 'pm_metrics_monthly', true );
			$month_val = 0;
			if ( is_array( $m ) && isset( $m[ $month_key ] ) ) {
				$month_val = intval( $m[ $month_key ]['views'] ?? 0 );
			}
			$meta = get_post_meta( $p->ID, 'pm_metrics', true );
			$cumulative = intval( $meta['views'] ?? 0 );
			$rows[] = array( 'id' => $p->ID, 'title' => get_the_title( $p ), 'month' => $month_val, 'cumulative' => $cumulative );
			$total_views += $month_val;
		}
		// If no monthly buckets (0 total), fall back to cumulative top
		if ( $total_views === 0 ) {
			foreach ( $rows as &$r ) { $r['month'] = $r['cumulative']; }
		}
		usort( $rows, function( $a, $b ) { return $b['month'] <=> $a['month']; } );
		$top = array_slice( $rows, 0, 5 );

		echo '<div class="pm-dashboard">';
		echo '<p><strong>' . esc_html__( 'Total views this month', 'post-metrics' ) . ':</strong> ' . esc_html( $total_views ) . '</p>';
		echo '<ol>';
		foreach ( $top as $r ) {
			echo '<li>' . esc_html( $r['title'] ) . ' — ' . esc_html( $r['month'] ) . '</li>';
		}
		echo '</ol>';
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=pm-post-metrics' ) ) . '">' . esc_html__( 'View full report', 'post-metrics' ) . '</a></p>';
		echo '</div>';
	}
}

PM_Post_Metrics::init();
