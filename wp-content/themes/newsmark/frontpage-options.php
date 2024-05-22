<?php

/**
 * Option Panel
 *
 * @package Newsmark
 */


function newsmark_customize_register($wp_customize) {

$newsup_default = newsmark_get_default_theme_options();

$wp_customize->remove_control('newsup_select_slider_setting');
$wp_customize->remove_control('header_data_title');
$wp_customize->remove_control('header_data_enable');
$wp_customize->remove_control('header_time_enable');
$wp_customize->remove_control('newsup_date_time_show_type');
$wp_customize->remove_control('flash_news_title');
$wp_customize->remove_control('newsup_center_logo_title');
$wp_customize->remove_control('newsup_header_overlay_color');


$wp_customize->add_setting(
    'newsup_header_overlay_color', 
    array( 
        'sanitize_callback' => 'newsup_alpha_color_custom_sanitization_callback',
        'default' => '#eee',
    ) 
);
$wp_customize->add_control(new Newsup_Customize_Alpha_Color_Control( $wp_customize,'newsup_header_overlay_color', array(
        'label'      => __('Overlay Color', 'newsmark' ),
        'palette' => true,
        'section' => 'header_image',
        'active_callback' => 'newsup_header_iamge_overlay_status',
    )
) );

$wp_customize->add_setting('newsup_center_logo_title',
array(
    'default' => true,
    'sanitize_callback' => 'newsup_sanitize_checkbox',
)
);

$wp_customize->add_control('newsup_center_logo_title',
    array(
        'label' => esc_html__('Display Center Site Title and Tagline', 'newsmark'),
        'section' => 'title_tagline',
        'type' => 'checkbox',
        'priority' => 55,

    )
);
//section title
$wp_customize->add_setting('trending_post_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new newsup_Section_Title(
        $wp_customize,
        'trending_post_section_title',
        array(
            'label'             => esc_html__( 'Trending Post Section', 'newsmark' ),
            'section'           => 'frontpage_main_banner_section_settings',
            'priority'          => 100,
            'active_callback' => 'newsup_main_banner_section_status'
        )
    )
);


// Setting - drop down category for slider.
$wp_customize->add_setting('select_trending_news_category',
    array(
        'default' => $newsup_default['select_trending_news_category'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);


$wp_customize->add_control(new Newsup_Dropdown_Taxonomies_Control($wp_customize, 'select_trending_news_category',
    array(
        'label' => esc_html__('Category', 'newsmark'),
        'description' => esc_html__('Posts to be shown on trending 4 Post', 'newsmark'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'dropdown-taxonomies',
        'taxonomy' => 'category',
        'priority' => 100,
        'active_callback' => 'newsup_main_banner_section_status'
    )));

//section title
$wp_customize->add_setting('recent_post_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new newsup_Section_Title(
        $wp_customize,
        'recent_post_section_title',
        array(
            'label'             => esc_html__( 'Recent Post Section', 'newsmark' ),
            'section'           => 'frontpage_main_banner_section_settings',
            'priority'          => 100,
            'active_callback' => 'newsup_main_banner_section_status'
        )
    )
);

// Setting - drop down category for slider.
$wp_customize->add_setting('select_recent_news_category',
    array(
        'default' => $newsup_default['select_recent_news_category'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);
$wp_customize->add_control(new Newsup_Dropdown_Taxonomies_Control($wp_customize, 'select_recent_news_category',
    array(
        'label' => esc_html__('Category', 'newsmark'),
        'description' => esc_html__('Posts to be shown on recent 4 Post', 'newsmark'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'dropdown-taxonomies',
        'taxonomy' => 'category',
        'priority' => 100,
        'active_callback' => 'newsup_main_banner_section_status'
    )));

    // section title
    $wp_customize->add_setting('header_data_title',
        array(
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        new Newsup_Section_Title(
            $wp_customize,
            'header_data_title',
            array(
                'label' => esc_html__('Date', 'newsmark'),
                'priority' => 1,
                'section' => 'header_options',
            )
        )
    );
    $wp_customize->add_setting('header_data_enable',
    array(
        'default' => true,
        'sanitize_callback' => 'newsup_sanitize_checkbox',
    )
    );
    $wp_customize->add_control(new Newsup_Toggle_Control( $wp_customize, 'header_data_enable', 
        array(
            'label' => esc_html__('Hide / Show Date', 'newsmark'),
            'type' => 'toggle',
            'priority' => 1,
            'section' => 'header_options',
        )
    ));

    // date in header display type
    $wp_customize->add_setting( 'newsup_date_time_show_type', array(
        'default'           => 'newsup_default',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'newsup_sanitize_select',
    ) );

    $wp_customize->add_control( 'newsup_date_time_show_type', array(
        'type'     => 'radio',
        'priority' => 1,
        'label'    => esc_html__( 'Date in header display type:', 'newsmark' ),
        'choices'  => array(
            'newsup_default'          => esc_html__( 'Theme Default Setting', 'newsmark' ),
            'wordpress_date_setting' => esc_html__( 'From WordPress Setting', 'newsmark' ),
        ),
        'section'  => 'header_options',
        'settings' => 'newsup_date_time_show_type',
    ) );

    $wp_customize->add_setting('flash_news_title',
        array(
            'default' => $newsup_default['flash_news_title'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage'
        )
    );

    $wp_customize->add_control('flash_news_title',
        array(
            'label' => esc_html__('Latest Post Title', 'newsmark'),
            'section' => 'newsup_flash_posts_section_settings',
            'type' => 'text',
            'priority' => 23,
            'active_callback' => 'newsup_flash_posts_section_status'

        )
    );

}
add_action('customize_register', 'newsmark_customize_register');
