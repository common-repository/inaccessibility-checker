=== Inaccessibility Checker ===
Contributors: Vhati
Donate link: 
Tags: accessibility, filter, image, posts
Requires at least: 3.5.2
Tested up to: 4.7.0
Stable tag: 0.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Check your privilege and your tags. When previewing a post, images with no alt text or caption will be highlighted red, and a warning will be shown.

== Description ==

Check your privilege and your tags.

When previewing a post, images with no alt text or caption will be highlighted red, and a warning will be shown.

Screen reader software relies on alt text to describe images for visually impaired users.

== Installation ==

1. Upload and extract `inaccessibility-checker.zip` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. What a previewed post looks like.

== Changelog ==

= 0.0.3 =
* Replaced regex with DOM manipulation.
* Fixed exclusion when captioned (Sometime after WordPress 3.5.2, html structure changed div/p to figure/figcaption).
* Confirmed support for WordPress 4.7.

= 0.0.2 =
* Accepted captions when alt-text is absent.
* Formatted PHP to meet the [WordPress coding standards](http://make.wordpress.org/core/handbook/coding-standards/php/).

= 0.0.1 =
* Initial release.
