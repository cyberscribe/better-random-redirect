=== Plugin Name ===
Contributors: robertpeake
Tags: random,post,category
Requires at least: 3.0.0
Tested up to: 4.1.1
Stable tag: 1.3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Based on the original Random Redirect, this plugin enables efficient, easy random redirection to a post. Supports setting a category for all random redirects, shortcodes to generate URLs that can override the default category, and setting your own redirector URL. Designed to scale to handle high-traffic websites with thousands of posts by using a more efficient strategy than most other redirection plugins employ.

== Description ==

Based on the original Random Redirect, this plugin enables efficient, easy random redirection to a post. Supports setting a category for all random redirects, shortcodes to generate URLs that can override the default category, and setting your own redirector URL. Designed to scale to handle high-traffic websites with thousands of posts by using a more efficient strategy than most other redirection plugins employ.

Based on the original Random Redirect by Matt Mullenweg https://wordpress.org/plugins/random-redirect/

Special thanks to Tim Green for providing additional quality assurance testing on the popular rattle.com website.

== Installation ==

Install as normal for WordPress plugins.

== Frequently Asked Questions ==

= Another random post redirection script, really? =

Yep, really. So many of the ones currently out there are not suitable for large websites with lots of traffic. 

This is because many random redirect plugins rely on the <code>'orderby' => 'rand'</code> constraint in Wordpress, or directly use <code>'ORDER BY RAND()'</code> in their MySQL queries. This results in notoriously poor performance and can really cause problems with your MySQL server if this operation is heavily repeated on a website with lots of eligible posts.

This plugin uses a more efficient approach, including transient caching of all eligible posts by category and their post counts to minimise the time it takes to pick a true random post.

= How do I set the URL? =

Go to Settings > Better Random Redirect and change the URL slug from the default of "random" to whatever you want it to be.

= How do I make the randomiser use just one category for everything? =

Select the category you want to use in Settings > Better Random Redirect. This will become the default category used for all subsequent random requests. It can be overridden, however using the cat= shortcode attribute or query string as described below.

= How do I make the randomiser use just one post type for everything? =

Select the post type you want to use in Settings > Better Random Redirect. This will become the default post type used for all subsequent random requests. It can be overridden, however using the posttype= shortcode attribute or query string as described below.

= How do I tell the randomiser to override the default category for a single link? =

For random results in e.g. category 'foo', use the shortcode <code>[random-url cat="foo"]</code>. The generated link will select a random post from that category. 

Alternatively, use the URL you set up in the configuration above, and optionally append cat= as part of the URL query string.

= How do I tell the randomiser to override the default post type for a single link? =

For random results in e.g. post type 'page', use the shortcode <code>[random-url posttype="page"]</code>. The generated link will select a random post from that post type. 

Alternatively, use the URL you set up in the configuration above, and optionally append posttype= as part of the URL query string.

= How do I create buttons links to random posts? =

Use the shortcode <code>[random-url]</code> anywhere you want to place the URL for a link to the randomiser, such as in text links or buttons. You can also use the cat= attribute to create a link to a randomiser that will only select random posts from a specific category, or the posttype= attribute to create a link to a randomiser that will only select random posts from a specific post type.

Alternatively, simply use the URL you set up in the configuration above as the link for the link or button, and optionally append cat= and/or posttype= as part of the URL query string.

= How do I add these buttons or links to a sidebar or navigation menu item? =

You can use the URL you set up in the configuration, and optionally append cat= and/or posttype= as part of the URL query string.

Alternatively, for sidebar items, you can use the PHP Widget along with some php code like <code><?php echo do_sidebar('[random-url]'); ?></code> to resolve the shortcode to a link that includes the r= attribute to defeat URL-based caching.

= What is the r= parameter I see at the end of generated URLs? =

This is a random integer in the range of possible index values for the relevant category. It is appended to the generated URLs to defeat URL-based caching, and also to give deterministic routing of results (i.e. the same r value and category combination will lead to the same post each time). This helps with services like Facebook that cache the resulting 302 redirect, to make sure they are caching accurate metadata on a link-by-link basis.

== Screenshots ==

1. Configuration options screen

== Changelog ==

= 1.3.6 =

 * Bug fix (incomplete refactoring) 

= 1.3.5 =

 * Refactored namespaces into classes with static methods to improve compatability with php 5.2 and below

= 1.3.4 =

 * Improved queries related to qTranslate-X posts

= 1.3.3 =

 * Added support for qTranslate-X

= 1.3.2 =

 * Fixed bug related to Post Type settings (thanks to Naeem Noor)

= 1.3.1 =

* Display correct URL in admin on multisite
* Give example of posttype= parameters in admin

= 1.3 =

* Support for different post types

= 1.2.1 =

* More efficient routing
* Use of wpdb->prepare where applicable
* Expanded readme instructions and code comments

= 1.2 =

* Using mt_rand() instead of rand()

= 1.1 =

* Configurable global category
* Shortocde [random-url] with optional cat parameter for category override
* Deterministic routing and cache defeating using r= parameter on all generated URLs

= 1.0 =

* Initial release
