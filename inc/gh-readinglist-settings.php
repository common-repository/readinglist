<?php

function gh_readinglist_admin_init()
{
    $postTypeArgs = array (
        'public'   => true,
        '_builtin' => true
    );

    $enabledPostTypes = get_post_types($postTypeArgs, 'names', 'and');
    $postTypeSelect   = [];
    foreach ($enabledPostTypes as $post_type) {
        $postTypeSelect[ $post_type ] = ucfirst($post_type);
    }

    $sections = array (
        array (
            'id'    => 'readinglist_settings',
            'title' => __('Readinglist settings', 'ghreadinglist')
        )
    );

    $fields = array (
        'readinglist_settings' => array (
            array (
                'name'    => 'base_color',
                'label'   => __('Base color', 'ghreadinglist'),
                'desc'    => __('Set the basic color (hex value) of the readinglist to fit your theme (example: #ffffff)'),
                'ghreadinglist',
                'type'    => 'text',
                'default' => '#888',
            ),
            array (
                'name'  => 'empty_text',
                'label' => __('Default text when list is empty', 'ghreadinglist'),
                'desc'  => __('Set the text which is displayed when the list is empty', 'ghreadinglist'),
                'type'  => 'textarea',
            ),
            array (
                'name'    => 'post_types',
                'label'   => __('Set for post types', 'ghreadinglist'),
                'desc'    => __('Select for which post type you want to enable the add to my readinglist button. This button will be placed above the post content', 'wedevs'),
                'type'    => 'multicheck',
                'default' => array ('post' => 'Post'),
                'options' => $postTypeSelect,
            ),
            array (
                'name'    => 'readinglist_btn_class',
                'label'   => __('Readinglist Btn class', 'ghreadinglist'),
                'desc'    => __('Add a class for the add-to readinglist button. Default is set to bootstrap btn btn-primary'),
                'ghreadinglist',
                'type'    => 'text',
                'default' => 'btn btn-primary',
            ),
            array (
                'name'    => 'enable_shortcode',
                'label'   => __('Use shortcode', 'ghreadinglist'),
                'desc'    => __('If the shortcode is enabled, the readingbutton is no longer displayed above each article. You can place the shortcode wherever you want the button to appear.', 'ghreadinglist'),
                'type'    => 'checkbox',
            ),
            array (
                'name'    => 'load_in_wp_footer',
                'label'   => __('Load in custom position', 'ghreadinglist'),
                'desc'    => __('If unchecked, this loads the Readinglist markup in the wp_footer() hook. If you want to execute the HTML somewhere else, uncheck this option and place the <code>gh_readinglist_container_html()</code> function in your theme where you want the markup to be rendered.', 'ghreadinglist'),
                'type'    => 'checkbox',
            ),
            array (
                'name'    => 'readinglist_overview_page',
                'label'   => __('Readinglist page', 'ghreadinglist'),
                'desc'    => __('This page contains all the articles on the readinglist (starting from version 1.3, the list contains only the last 15 items). You can use the shortcode [readinglist_total_list] to display it on the page'),
                'ghreadinglist',
                'type'    => 'text',
                'default' => '/my-readinglist',
            ),
        ),
    );

    $rlSettings = WeDevs_Settings_API::getInstance();
    $rlSettings->set_sections($sections);
    $rlSettings->set_fields($fields);
    $rlSettings->admin_init();
}

add_action('admin_init', 'gh_readinglist_admin_init');

/**
 * Register the plugin page
 */
function gh_readinglist_admin_menu()
{
    add_options_page('Readinglist Settings', 'Readinglist', 'delete_posts', 'readinglist_settings', 'gh_readinglist_plugin_page');
}

add_action('admin_menu', 'gh_readinglist_admin_menu');

/**
 * Display the plugin settings options page
 */
function gh_readinglist_plugin_page()
{
    $settings_api = WeDevs_Settings_API::getInstance();

    echo '<div class="wrap">';
    settings_errors();

    $settings_api->show_navigation();
    $settings_api->show_forms();
    echo '<hr><h3>How to use the shortcode</h3><p>You can add the readingbutton inside any article like so: [readinglist_button]</p>';

    echo '</div>';
}

/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 *
 * @return mixed
 */
function gh_readinglist_get_option($option, $section, $default = '')
{

    $options = get_option($section);

    if (isset($options[ $option ])) {
        return $options[ $option ];
    }

    return $default;
}
