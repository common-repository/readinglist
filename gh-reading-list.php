<?php
/**
 * Plugin Name: Readinglist
 * Description: A readinglist where registered users can add favorite articles
 * Version: 2.1
 * Author: Sander de Wijs
 * Author URI: https://www.degrinthorst.nl
 * License: GPL2
 */

define('GH_READINGLIST_PATH', plugin_dir_path(__FILE__));
/**
 *
 */
define('GH_READINGLIST_URL', plugin_dir_url(__FILE__));

require_once(GH_READINGLIST_PATH . 'vendor/autoload.php');
require_once(GH_READINGLIST_PATH . 'inc/gh-readinglist-settings.php');

add_action('plugins_loaded', 'ghreadinglist_load_textdomain');
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function ghreadinglist_load_textdomain()
{
    load_plugin_textdomain('ghreadinglist', false, basename(dirname(__FILE__)) . '/languages');
}

/**
 * Scripts & Styles
 */

add_action('wp_enqueue_scripts', 'gh_readinglist_assets');

/**
 *
 */
function gh_readinglist_assets()
{
    wp_enqueue_style('ghRlStyles', GH_READINGLIST_URL . 'assets/css/readinglist.css', false, '0.3');
    wp_enqueue_script('ghRlScript', GH_READINGLIST_URL . 'assets/js/gh-readinglist.js', ['jquery'], '0.4', true);

    /**
     * Only enable the readinglist for registered users
     */
    if (is_user_logged_in()) {
        wp_localize_script(
            'ghRlScript',
            'ghReadingList',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("reading_list_update"),
            )
        );
    }
}


add_action('wp_ajax_gh_readinglist_update_reading_list', 'gh_readinglist_update_reading_list');
add_action('wp_ajax_nopriv_gh_readinglist_update_reading_list', 'gh_readinglist_update_reading_list');

/**
 *
 */
function gh_readinglist_update_reading_list()
{
    if (check_ajax_referer('reading_list_update', 'reading_list_update')) {
        if ($_POST['listAction'] === 'add') {
            header('Content-Type: application/json');
            // Echo to permalink to be added
            $id = gh_readinglist_add_to_list($_POST);
            echo json_encode(array(
                'postLink' => get_permalink($id),
                'title' => get_the_title($id),
                'id' => $id,
            ));
        } else if ($_POST['listAction'] === 'drop') {
            // Echo the ID that should be removed
            header('Content-Type: application/json');
            $id = gh_readinglist_remove_from_list($_POST);
            echo json_encode(array(
                'id' => $id,
            ));
        }
    } else {
        echo 'Uhmm, nope!';
    }

    wp_die();
}

/**
 * @param $data
 *
 * @return int
 */
function gh_readinglist_add_to_list($data)
{

    $userId = get_current_user_id();
    $meta = get_user_meta($userId, 'reading_list', true);
    if (!$meta || empty($meta)) {
        $list = array(intval($data['articleID']));
        update_user_meta($userId, 'reading_list', $list);
    } else {
        $list = get_user_meta($userId, 'reading_list', true);
        $key = array_search($data['articleID'], $list);
        if ($key === false) {
            $list[] = intval($data['articleID']);
            update_user_meta($userId, 'reading_list', $list);
        }
    }

    return intval($data['articleID']);
}

/**
 * @param $data
 *
 * @return bool|int
 */
function gh_readinglist_remove_from_list($data)
{
    $userId = get_current_user_id();
    $list = get_user_meta($userId, 'reading_list', true);
    $key = array_search(intval($data['articleID']), $list);
    if ($key !== false) {
        unset($list[$key]);
        $list = array_values($list);
        update_user_meta($userId, 'reading_list', $list);
        return intval($data['articleID']);
    }

    return false;
}

if (!gh_readinglist_get_option('load_in_wp_footer', 'readinglist_settings', false)) {
    add_action('wp_footer', 'gh_readinglist_container_html');
}

/**
 *
 */
function gh_readinglist_container_html()
{
    if (!is_user_logged_in()) {
        return;
    }
    $items = get_user_meta(get_current_user_id(), 'reading_list', true);

    $output = '';
    $output .= '<div class="readinglist-wrapper"><div class="js-show-hide-readinglist"><span class="rl-icon-list"></span></div>';
    $output .= '<div class="js-reading-list"><div class="reading-list-header pb-1"><a href="" class="js-close-list"><div class="close-button"></div></a>';
    $output .= '<p><strong>' . __('My readinglist', 'ghreadinglist') . '</strong> / <span class="js-list-count">' . ghReadinglistGetReadingListCount() . '</span> ' . __('articles', 'ghreadinglist') . '</p></div>';
    $output .= '<ul class="js-readinglist-container">';

    if (empty($items)) {
        if (!gh_readinglist_get_option('empty_text', 'readinglist_settings', false)) {
            $output .= '<p style="color: #888;" class="js-empty-reading-list">You don\'t have any articles on your readlinglist yet. <br><br> Go ahead and simplyfy your life by adding some. You can find the add-to-readlinglist-button right above each article.</p>';
        } else {
            $output .= '<p style="color: #888;" class="js-empty-reading-list">' . gh_readinglist_get_option('empty_text', 'readinglist_settings', false) . '</p>';
        }
    } else {
        $output .= ghReadingListParseListItems($items);
    }

    $output .= '</ul>';
    $output .= '<div class="readinglist-footer"><hr /><a href="' . gh_readinglist_get_option('readinglist_overview_page', 'readinglist_settings', false) . '" class="readinglist-read-more-link">' . __('View all items', 'ghreadinglist') . '</a></div>';
    $output .= '</div></div>';

    echo $output;
}

/**
 * @return int|string
 */
function ghReadinglistGetReadingListCount()
{
    $items = get_user_meta(get_current_user_id(), 'reading_list', true);
    if (!$items) {
        return '0';
    } else {
        return count($items);
    }
}

/**
 * @param $data
 *
 * @return string
 */
function ghReadingListParseListItems($data)
{
    $output = '';
    $i = 0;
    foreach (array_reverse($data) as $item) {
        if ($i > 15) {
            break;
        }
        $post = get_post($item);
        $title = (strlen(htmlspecialchars_decode($post->post_title)) >= 40) ? substr(htmlspecialchars_decode($post->post_title), 0, 40) . '...' : htmlspecialchars_decode($post->post_title);
        $output .= '<li class="item mb-1" data-art-id="' . $post->ID . '">';
        $output .= '<a href="' . get_permalink($item) . '">' . $title . '</a>';
        $output .= '<span class="rl-icon-trash js-delete-item"></span>';
        $output .= '</li>';
        $i++;
    }
    return $output;
}

add_filter('the_content', 'renderAddToReadinglistBtn');
/**
 * @param $content
 *
 * @return string
 */
function renderAddToReadinglistBtn($content)
{
    $useShortcode = gh_readinglist_get_option('enable_shortcode', 'readinglist_settings', false);
    $posts = gh_readinglist_get_option('post_types', 'readinglist_settings', false);
    if (!in_array(get_post_type(), $posts) || $useShortcode === 'on' || !is_user_logged_in()) {
        return $content;
    }

    /**
     * Only return button content on single post pages
     */
    if (!is_single()) {
        return $content;
    }

    $id = get_the_ID();
    $buttonCssClass = gh_readinglist_get_option('readinglist_btn_class', 'readinglist_settings', false);
    $output = '';
    $output .= '<div class="readinglist-btn-wrapper">';
    $output .= '<a href="#" class="' . $buttonCssClass . ' js-add-to-list" data-art-id="' . $id . '"><span class="rl-icon-list"></span> &nbsp;' . __("Add to my readinglist", "ghreadinglist") . '</a></div>';

    return $output . $content;
}

add_shortcode('readinglist_button', 'readinglistButtonShortcode');
/**
 * @param $atts
 *
 * @return string
 */
function readinglistButtonShortcode($atts)
{
    $useShortcode = gh_readinglist_get_option('enable_shortcode', 'readinglist_settings', false);
    $posts = gh_readinglist_get_option('post_types', 'readinglist_settings', false);
    if ($useShortcode !== 'on' || !in_array(get_post_type(), $posts) || !is_user_logged_in()) {
        return '';
    }

    if (!get_the_ID()) {
        return 'You can only use this shortcode inside the contents of an article, or inside the Loop.';
    }
    $buttonCssClass = gh_readinglist_get_option('readinglist_btn_class', 'readinglist_settings', false);
    $output = '';
    $output .= '<div class="readinglist-btn-wrapper">';
    $output .= '<a href="#" class="' . $buttonCssClass . ' js-add-to-list" data-art-id="' . get_the_ID() . '"><span class="rl-icon-list"></span> &nbsp;' . __("Add to my readinglist", "ghreadinglist") . '</a></div>';
    return $output;
}

add_action('wp_head', 'gh_readinglist_custom_css');
function gh_readinglist_custom_css()
{
    $baseColor = gh_readinglist_get_option('base_color', 'readinglist_settings', false);
    if (!$baseColor) {
        return false;
    }
    ?>
    <style>
        .reading-list-header,
        .js-show-hide-readinglist  {
            background-color: <?php echo $baseColor; ?>!important;
        }

        .js-show-hide-readinglist::before {
            border-right: 25px solid <?php echo $baseColor; ?>!important;
        }
    </style>
    <?php
}

add_shortcode('readinglist_total_list', 'gh_readinglist_content');

function gh_readinglist_content($atts) {
	if (!is_user_logged_in()) {
		return '';
	}
    $content = ["<table class='table table-striped reading-list-page'><tbody>"];
	$items = get_user_meta(get_current_user_id(), 'reading_list', true);
	$posts = array_map(function($item) {
		return get_post($item);
	}, array_reverse($items));
	
	foreach ($posts as $post) {
	    $permalink = get_the_permalink($post);
	    $content[] = "
		        <tr class='list-item' data-art-id='{$post->ID}'>
                  <td><a href='{$permalink}'>{$post->post_title}</a></td>
                  <td class='text-left'>
                    <button class='btn btn-primary js-remove-page-item p-2'><i class='rl-icon-trash'></i></button>
                  </td>
                </tr>";
    }
	
	$content[] = "</tbody></table>";
 	
	return implode("", $content);
}
