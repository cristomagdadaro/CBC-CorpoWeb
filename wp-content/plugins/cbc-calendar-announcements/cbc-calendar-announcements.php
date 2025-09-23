<?php
/**
 * Plugin Name: CBC Calendar & Announcements
 * Description: Provides admin settings for Announcements (with optional image), Events & Trainings, Holidays, and Calendar display mode. Migrates existing theme options.
 * Version: 1.0.1
 * Author: Cristo Rey C. Magdadaro
 * License: GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Option name used by this plugin
const CBC_CA_OPT = 'cbc_calendar_options';

// Activation: migrate data from theme option if present
register_activation_hook( __FILE__, function(){
    $existing = get_option( CBC_CA_OPT );
    if ( ! is_array( $existing ) ) {
        $existing = array();
    }
    $theme = get_option( 'govph_options' );
    if ( is_array( $theme ) ) {
        $map = array(
            'govph_announcements'    => 'announcements',
            'govph_events'           => 'events',
            'govph_holidays'         => 'holidays',
            'govph_calendar_display' => 'calendar_display',
        );
        foreach ( $map as $src => $dst ) {
            if ( isset( $theme[$src] ) && ! isset( $existing[$dst] ) ) {
                $existing[$dst] = $theme[$src];
            }
        }
        update_option( CBC_CA_OPT, $existing, false );
    }
});

// Admin: settings page under Settings
add_action( 'admin_menu', function(){
    add_options_page(
        'CBC Calendar & Announcements',
        'CBC Calendar',
        'manage_options',
        'cbc-calendar-announcements',
        'cbc_ca_render_settings_page'
    );
});

add_action( 'admin_init', function(){
    register_setting( 'cbc_ca_group', CBC_CA_OPT, 'cbc_ca_sanitize' );

    add_settings_section( 'cbc_ca_main', '', '__return_null', 'cbc-calendar-announcements' );

    add_settings_field( 'cbc_ca_announcements', 'Announcements', 'cbc_ca_field_announcements', 'cbc-calendar-announcements', 'cbc_ca_main' );
    add_settings_field( 'cbc_ca_events', 'Events & Trainings', 'cbc_ca_field_events', 'cbc-calendar-announcements', 'cbc_ca_main' );
    add_settings_field( 'cbc_ca_holidays', 'Holidays', 'cbc_ca_field_holidays', 'cbc-calendar-announcements', 'cbc_ca_main' );
    add_settings_field( 'cbc_ca_calendar_display', 'Calendar Display', 'cbc_ca_field_calendar_display', 'cbc-calendar-announcements', 'cbc_ca_main' );
});

function cbc_ca_get_options(){
    $opts = get_option( CBC_CA_OPT );
    if ( ! is_array( $opts ) ) $opts = array();
    return $opts;
}

function cbc_ca_render_settings_page(){
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1>CBC Calendar & Announcements</h1>
        <form action="options.php" method="post">
            <?php settings_fields( 'cbc_ca_group' ); ?>
            <?php do_settings_sections( 'cbc-calendar-announcements' ); ?>
            <?php submit_button(); ?>
        </form>
        <hr />
        <p><strong>Format help</strong></p>
        <ul style="list-style:disc;margin-left:20px;">
            <li><strong>Announcements</strong>: one per line as <code>Message|URL|ImageURL</code>. ImageURL is optional.</li>
            <li><strong>Events & Trainings</strong>: <code>YYYY-MM-DD|Title|URL(optional)|Type(event/training)|Location(optional)</code></li>
            <li><strong>Holidays</strong>: <code>YYYY-MM-DD|Holiday name|Scope(optional e.g., PH/Intl)</code></li>
        </ul>
    </div>
    <?php
}

function cbc_ca_field_announcements(){
    $opts = cbc_ca_get_options();
    $val = isset($opts['announcements']) ? (string)$opts['announcements'] : '';
    echo '<textarea name="' . esc_attr(CBC_CA_OPT) . '[announcements]" rows="8" cols="100" style="max-width:100%;width:100%;">' . esc_textarea($val) . '</textarea>';
    echo '<p class="description">One per line: Message|URL|ImageURL (image is optional). Use Media Library to get an image URL.</p>';
}

function cbc_ca_field_events(){
    $opts = cbc_ca_get_options();
    $val = isset($opts['events']) ? (string)$opts['events'] : '';
    echo '<textarea name="' . esc_attr(CBC_CA_OPT) . '[events]" rows="8" cols="100" style="max-width:100%;width:100%;">' . esc_textarea($val) . '</textarea>';
    echo '<p class="description">'
        . 'YYYY-MM-DD|Title|URL(optional)|Type(event/training)|Location(optional)'
        . '<br/>Append <code>|announce</code> at the end to include this item in the announcements ticker.</p>';
}

function cbc_ca_field_holidays(){
    $opts = cbc_ca_get_options();
    $val = isset($opts['holidays']) ? (string)$opts['holidays'] : '';
    echo '<textarea name="' . esc_attr(CBC_CA_OPT) . '[holidays]" rows="6" cols="100" style="max-width:100%;width:100%;">' . esc_textarea($val) . '</textarea>';
    echo '<p class="description">'
        . 'YYYY-MM-DD|Holiday name|Scope(optional)'
        . '<br/>Append <code>|announce</code> at the end to include this holiday in the announcements ticker.</p>';
}

function cbc_ca_field_calendar_display(){
    $opts = cbc_ca_get_options();
    $val = isset($opts['calendar_display']) ? (string)$opts['calendar_display'] : 'grid';
    ?>
    <label><input type="radio" name="<?php echo esc_attr(CBC_CA_OPT); ?>[calendar_display]" value="grid" <?php checked($val,'grid'); ?>> Grid</label>
    <br />
    <label><input type="radio" name="<?php echo esc_attr(CBC_CA_OPT); ?>[calendar_display]" value="list" <?php checked($val,'list'); ?>> List</label>
    <?php
}

function cbc_ca_sanitize( $input ){
    if ( ! is_array( $input ) ) return array();
    $out = array();
    $out['announcements']    = isset($input['announcements']) ? (string)$input['announcements'] : '';
    $out['events']           = isset($input['events']) ? (string)$input['events'] : '';
    $out['holidays']         = isset($input['holidays']) ? (string)$input['holidays'] : '';
    $mode = isset($input['calendar_display']) ? (string)$input['calendar_display'] : 'grid';
    $out['calendar_display'] = in_array($mode, array('grid','list'), true) ? $mode : 'grid';
    return $out;
}

// Public helper for themes/plugins: get merged options with fallback
function cbc_ca_get_merged_options(){
    $p = cbc_ca_get_options();
    if ( ! empty($p) ) return $p;
    // Fallback to theme options
    $t = get_option('govph_options');
    if ( is_array($t) ) {
        return array(
            'announcements'    => isset($t['govph_announcements']) ? (string)$t['govph_announcements'] : '',
            'events'           => isset($t['govph_events']) ? (string)$t['govph_events'] : '',
            'holidays'         => isset($t['govph_holidays']) ? (string)$t['govph_holidays'] : '',
            'calendar_display' => isset($t['govph_calendar_display']) ? (string)$t['govph_calendar_display'] : 'grid',
        );
    }
    return array(
        'announcements' => '',
        'events' => '',
        'holidays' => '',
        'calendar_display' => 'grid',
    );
}

// Expose a filter for customization
add_filter('cbc_calendar_options', function($opts){ return cbc_ca_get_merged_options(); });
