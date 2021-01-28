=== Rather Simple Taxonomy Navigation ===
Contributors: leemon
Tags: taxonomy, terms, navigation, menu, filter
Requires at least: 4.0
Tested up to: 5.6
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a taxonomy navigation

== Description ==

Adds a taxonomy navigation

This plugin is experimental and is provided with no support or warranty. 

== Installation ==
1. Upload the extracted plugin folder and contained files to your /wp-content/plugins/ directory
2. Activate the plugin through the "Plugins" menu in WordPress

== Frequently Asked Questions ==
= How can I show the taxonomy navigation in a theme? =
You can use the `show_taxonomy_navigation` action:

`do_action( 'show_taxonomy_navigation', $post_type, $taxonomy, $parent )`;

where `$post_type` is the post type and `$taxonomy` is the taxonomy to display,
and $parent is an optional parameter that specifies the starting parent term
(0 by default, to show all terms in the taxonomy).

Example:

`do_action( 'show_taxonomy_navigation', 'product', 'product_cat' )`;

== Copyright ==

Rather Simple Taxonomy Navigation is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

== Changelog ==
= 1.0 =
* Initial release