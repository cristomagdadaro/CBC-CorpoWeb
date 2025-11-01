<?php
/**
 * Plugin Name: CBC Tailwind Manager
 * Description: Manage Tailwind CSS configuration (colors, dark mode, fonts) from WP Admin and enqueue compiled Tailwind CSS instead of the CDN script.
 * Version: 1.2.0
 * Author: Cristo Rey C. Magdadaro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TWM_Tailwind_Manager {
	const OPTION_KEY = 'twm_tailwind_settings';
	const CONFIG_FILE = 'tailwind-custom.json';

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'maybe_create_default_config' ] );
		add_action( 'admin_init', [ __CLASS__, 'ensure_config_files' ] );
        add_action( 'admin_init', [ __CLASS__, 'tailwind_manager_add_editor_styles'] );
        add_action( 'admin_post_twm_save', [ __CLASS__, 'handle_save' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_tailwind' ], 5 );
        add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'tailwind_manager_enqueue_block_editor_assets'] );
		add_action( 'admin_notices', [ __CLASS__, 'admin_notice_missing_build' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest' ] );
		register_activation_hook( __FILE__, [ __CLASS__, 'activate' ] );
		add_shortcode( 'tailwind_color', [ __CLASS__, 'shortcode_color' ] );
	}

    // Load Tailwind CSS inside the block editor
    public static function tailwind_manager_add_editor_styles(): void {
        add_editor_style( plugins_url( 'dist/tailwind.css', __FILE__ ) );
    }

    public static function tailwind_manager_enqueue_block_editor_assets(): void {
        wp_enqueue_style(
                'tailwind-manager-editor-styles',
                plugins_url( 'dist/tailwind.css', __FILE__ ),
                array(),
                filemtime( plugin_dir_path( __FILE__ ) . 'dist/tailwind.css' )
        );
    }


    public static function plugin_dir() { return plugin_dir_path( __FILE__ ); }
	public static function plugin_url() { return plugin_dir_url( __FILE__ ); }

	public static function activate() { self::maybe_create_default_config(); self::ensure_config_files(); }

	public static function default_settings() : array {
		return [
			'primary'   => '#1f5d2b',
			'secondary' => '#1d3b53',
			'accent'    => '#a2b917',
			'darkMode'  => 0,
			'fontFamily'=> 'Inter, system-ui, sans-serif'
		];
	}

	public static function get_settings() : array {
		$opt = get_option( self::OPTION_KEY, [] );
		return wp_parse_args( $opt, self::default_settings() );
	}

	public static function maybe_create_default_config() {
		$path = self::plugin_dir() . self::CONFIG_FILE;
		if ( ! file_exists( $path ) ) {
			self::write_config_file( self::get_settings() );
		}
	}

	protected static function write_config_file( array $settings ) : bool {
		$data = [
			'colors' => [
				'primary'   => $settings['primary'],
				'secondary' => $settings['secondary'],
				'accent'    => $settings['accent'],
			],
			'darkMode'  => (bool) $settings['darkMode'],
			'fontFamily'=> $settings['fontFamily'],
		];
		return (bool) file_put_contents( self::plugin_dir() . self::CONFIG_FILE, wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	public static function admin_menu() {
		add_menu_page( 'Tailwind Manager', 'Tailwind Manager', 'manage_options', 'tailwind-manager', [ __CLASS__, 'render_admin_page' ], 'dashicons-art', 58 );
	}

	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$settings = self::get_settings();
		$build_exists = file_exists( self::plugin_dir() . 'dist/tailwind.css' );
		?>
		<div class="wrap">
			<h1>Tailwind Manager</h1>
			<p>Adjust a subset of Tailwind theme tokens here. After saving, run <code>npm run build</code> inside this plugin directory to regenerate <code>dist/tailwind.css</code>.</p>
			<?php if ( ! $build_exists ) : ?>
			<div class="notice notice-warning"><p><strong>Tailwind build missing:</strong> dist/tailwind.css not found. Install dependencies & run a build.</p></div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'twm_save' ); ?>
				<input type="hidden" name="action" value="twm_save" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="twm_primary">Primary Color</label></th>
						<td><input type="text" id="twm_primary" name="primary" value="<?php echo esc_attr( $settings['primary'] ); ?>" class="regular-text" placeholder="#1f5d2b" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="twm_secondary">Secondary Color</label></th>
						<td><input type="text" id="twm_secondary" name="secondary" value="<?php echo esc_attr( $settings['secondary'] ); ?>" class="regular-text" placeholder="#1d3b53" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="twm_accent">Accent Color</label></th>
						<td><input type="text" id="twm_accent" name="accent" value="<?php echo esc_attr( $settings['accent'] ); ?>" class="regular-text" placeholder="#a2b917" /></td>
					</tr>
					<tr>
						<th scope="row">Dark Mode</th>
						<td><label><input type="checkbox" name="darkMode" value="1" <?php checked( 1, (int) $settings['darkMode'] ); ?> /> Enable (class strategy)</label></td>
					</tr>
					<tr>
						<th scope="row"><label for="twm_font">Custom Font Family</label></th>
						<td><input type="text" id="twm_font" name="fontFamily" value="<?php echo esc_attr( $settings['fontFamily'] ); ?>" class="regular-text" placeholder="Inter, system-ui, sans-serif" /></td>
					</tr>
				</table>
				<p class="submit"><button type="submit" class="button button-primary">Save Settings</button></p>
			</form>
			<h2>Build Instructions</h2>
			<ol>
				<li>Open a terminal in: <code><?php echo esc_html( self::plugin_dir() ); ?></code></li>
				<li>Run: <code>npm install</code></li>
				<li>Run production build: <code>npm run build</code> (outputs <code>dist/tailwind.css</code>)</li>
				<li>Remove any <code>cdn.tailwindcss.com</code> script tags from your theme.</li>
				<li>npm run dev (watch mode)</li>
				<li>npm run build (production + minify)</li>
				<li>npm run clean (rimraf output)</li>
			</ol>
			<p>For development with watch mode: <code>npm run dev</code></p>
		</div>
		<?php
	}

	public static function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
		check_admin_referer( 'twm_save' );
		$settings = [
			'primary'   => self::sanitize_color( $_POST['primary'] ?? '' ),
			'secondary' => self::sanitize_color( $_POST['secondary'] ?? '' ),
			'accent'    => self::sanitize_color( $_POST['accent'] ?? '' ),
			'darkMode'  => isset( $_POST['darkMode'] ) ? 1 : 0,
			'fontFamily'=> sanitize_text_field( $_POST['fontFamily'] ?? 'Inter, system-ui, sans-serif' )
		];
		update_option( self::OPTION_KEY, $settings );
		self::write_config_file( $settings );
		wp_safe_redirect( add_query_arg( [ 'page' => 'tailwind-manager', 'updated' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	protected static function sanitize_color( $val ) : string {
		$val = trim( $val );
		if ( preg_match( '/^#([0-9a-fA-F]{3}){1,2}$/', $val ) ) return $val;
		return '#000000';
	}

	public static function enqueue_tailwind() {
		$css = self::plugin_dir() . 'dist/tailwind.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style( 'tailwind-manager', self::plugin_url() . 'dist/tailwind.css', [], filemtime( $css ) );
			// Optionally remove known CDN handle if enqueued elsewhere (best effort)
			add_action( 'wp_print_scripts', function() {
				global $wp_scripts; if ( ! $wp_scripts ) return;
				foreach ( $wp_scripts->queue as $handle ) {
					$src = $wp_scripts->registered[ $handle ]->src ?? '';
					if ( strpos( $src, 'cdn.tailwindcss.com' ) !== false ) { wp_dequeue_script( $handle ); }
				}
			}, 1 );
		}
	}

	public static function admin_notice_missing_build() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->base !== 'toplevel_page_tailwind-manager' ) return;
		if ( ! file_exists( self::plugin_dir() . 'dist/tailwind.css' ) ) {
			echo '<div class="notice notice-error"><p><strong>Tailwind Manager:</strong> Compiled file <code>dist/tailwind.css</code> not found. Run <code>npm install && npm run build</code>.</p></div>';
		}
	}

	public static function register_rest() {
		register_rest_route( 'tailwind-manager/v1', '/settings', [
			'methods'  => 'GET',
			'callback' => function() { return rest_ensure_response( self::get_settings() ); },
			'permission_callback' => '__return_true'
		] );
	}

	// Helper to fetch color token safely
	public static function get_color( string $key, string $fallback = '' ) : string {
		$settings = self::get_settings();
		return $settings[ $key ] ?? $fallback;
	}

	public static function shortcode_color( $atts ) {
		$atts = shortcode_atts( [ 'name' => 'primary', 'default' => '' ], $atts, 'tailwind_color' );
		return esc_html( self::get_color( $atts['name'], $atts['default'] ) );
	}

	public static function ensure_config_files() {
		$base = self::plugin_dir();
		// tailwind.config.cjs
		$cfg = $base . 'tailwind.config.cjs';
		if ( ! file_exists( $cfg ) ) {
			$tpl = "const fs = require('fs');\nconst path = require('path');\nconst cfgPath = path.resolve(__dirname, '" . self::CONFIG_FILE . "');\nlet custom = {};\ntry { custom = JSON.parse(fs.readFileSync(cfgPath, 'utf8')); } catch (e) {}\nconst darkMode = custom.darkMode ? 'class' : 'media';\nmodule.exports = {\n  darkMode,\n  content: [\n    '../../themes/**/*.php',\n    '../../themes/**/*.twig',\n    '../../themes/**/*.js',\n    '../../themes/**/*.jsx',\n    '../../themes/**/*.ts',\n    '../../themes/**/*.tsx',\n    '../../themes/**/*.vue',\n    '../../themes/**/*.html',\n    './src/**/*.{js,ts,jsx,tsx,php,html}'\n  ],\n  theme: {\n    extend: {\n      colors: custom.colors || {},\n      fontFamily: {\n        sans: custom.fontFamily ? custom.fontFamily.split(',').map(s=>s.trim()) : ['Inter','system-ui','sans-serif']\n      }\n    }\n  },\n  plugins: []\n};\n";
			file_put_contents( $cfg, $tpl );
		}
		// postcss.config.cjs
		$postcss = $base . 'postcss.config.cjs';
		if ( ! file_exists( $postcss ) ) {
			file_put_contents( $postcss, "module.exports = { plugins: { tailwindcss: {}, autoprefixer: {} } };\n" );
		}
		// src/input.css
		$srcDir = $base . 'src'; if ( ! is_dir( $srcDir ) ) wp_mkdir_p( $srcDir );
		$input = $srcDir . '/input.css';
		if ( ! file_exists( $input ) ) {
			file_put_contents( $input, "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n\n/* Custom layer examples */\n@layer base {\n  :root {\n    --color-primary: " . self::get_settings()['primary'] . ";\n  }\n}\n" );
		}
		// dist dir placeholder
		$dist = $base . 'dist'; if ( ! is_dir( $dist ) ) wp_mkdir_p( $dist );
	}
}

TWM_Tailwind_Manager::init();
