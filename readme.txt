=== Plugin Name ===
Contributors: SdeWijs
Donate link: https://www.mollie.com/pay/link/1006571/D2A4A1C0/2.5/Koffie%20voor%20de%20Grinthorst/a60be34ef573cefa17c1a00e90002f526b723683
Tags: favorites, readinglist, favorite articles
Requires at least: 3.0.1
Tested up to: 6.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a readinglist where registered users can add and delete favorite articles. The list is accessible from any page on the website.

== Description ==

This plugin adds a readinglist where registered users can add and delete favorite articles. The list is accessible from any page on the website.

The readinglist is a handy tool for websites with many articles like blogs, newssites or e-learning websites. It helps regular users to easily add
articles to their list of favorites so they can read them at a later time. You can choose to display the 'Add to readinglist' button above each post,
or you can use the shortcode [readinglist_button] to display the button anywhere you need it. It also works in widgets, as long as the widget is inside the Loop or the page content.

In the plugin settings you can select the base color of the readinglist to match the style of your theme. The button uses the Bootstrap button class by default
but you can override the class used for the button in the settings. The settings also offers an option to include or exclude the readinglist button for
posttypes.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly (reccommended).
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to the plugin settings (settings => Readinglist) to customize the appearance and default text when there are no items in the list

== Frequently Asked Questions ==

== Screenshots ==
1. Readinglist open button
2. Opened Readinglist with articles

== Changelog ==
= 2.2 =
* Bugfix fatal error when a non logged in user would access the my readinglist page

= 2.1 =
* change [readinglist] shortcode to [readinglist_total_list] to match description in plugin setting

= 2.0 =
* Starting from version 2, up to a maximum 15 items will be displayed in the list. This is to prevent long lists displaying off-page.
* In the bottom of the readinglist container, a link is added that links to a seperate page that contains the [readinglist_total_list] shortcode.
* Add new shortcode [readinglist_total_list] to display all the items that a user has added to their list.
* Add new setting to change the default readinglist page (defaults to /my-readinglist)
* If you want to use the default "My Readinglist" page, please create it manually and add the [readinglist_total_list] shortcode

= 1.2.2 =
* Add screenshots 

= 1.2.1 =
* Test for WP 5.5

= 1.2 =
* Prevent button text/shortcode from displaying in post exerpts

= 1.1 =
* Update description and installation instructions

= 1.0 =
* First stable version

== Upgrade Notice ==
