<?php
/*
Plugin Name: Rather Simple Term Filter
Plugin URI: 
Description: Adds a term filter
Version: 1.0
Requires at least: 4.9
Requires PHP: 7.0
WC tested up to: 4.9
Author: Oscar Ciutat
Author URI: http://oscarciutat.com/code/
Text Domain: rather-simple-term-filter
License: GPLv2 or later

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Rather_Simple_Term_Filter {
    
    /**
     * Plugin instance.
     *
     * @see get_instance()
     * @type object
     */
    protected static $instance = null;

    /**
     * Access this plugin’s working instance
     *
     * @wp-hook plugins_loaded
     * @return  object of this class
     */
    public static function get_instance() {
        
        if ( !self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;

    }
    
    /**
     * Used for regular plugin work.
     *
     * @wp-hook plugins_loaded
     * @return  void
     */
    public function plugin_setup() {
        
        // Init
        add_action( 'init', array( $this, 'load_language' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

        add_action( 'show_terms_navigation', array( $this, 'show_terms_navigation' ), 10, 2 );
        add_action( 'woocommerce_before_shop_loop', array( $this, 'test' ) );

    }
    
    /**
     * Constructor. Intentionally left empty and public.
     *
     * @see plugin_setup()
     */
    public function __construct() {}
    
    /*
     * load_language
     *
     * @since 1.0
     */
    function load_language() {
        load_plugin_textdomain( 'rather-simple-term-filter', '', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueues scripts and styles in the frontend.
     */
    function wp_enqueue_scripts(){
        wp_enqueue_style( 'rscf-style', plugins_url( 'style.css', __FILE__ ) );
    }

    /**
     * Test
     *
     */
    public function test() {
        do_action( 'show_terms_navigation', 'product', 'product_cat' );
    }

    /**
     * Show terms navigation
     *
     */
    public function show_terms_navigation( $post_type, $taxonomy ) {
        $html = Rather_Simple_Term_Filter::get_taxonomy_hierarchy( $post_type, $taxonomy );
        echo $html;
    }

    /**
     * Get taxonomy hierarchy
     *
     * @param $taxonomy
     * @param $parent
	 * 
	 * @return string
     */
    public function get_taxonomy_hierarchy( $post_type, $taxonomy, $parent = 0, $depth = 0 ) {
        $taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
        $html = '';
        $current_term_id = get_queried_object_id();
        $args = array(
            'taxonomy'   => $taxonomy,
            'parent'     => $parent,
            'order_by'   => 'name',
            'hide_empty' => true,
        );
        $terms = get_terms( $args );
        if ( $terms ) {
            $is_submenu = ( $depth > 0 ) ? ' sub-menu' : '';
            $all_link = ( $parent > 0 ) ? get_term_link( $parent ) : get_post_type_archive_link( $post_type );
            if ( $depth == 0 ) {
                $html .= '<nav class="terms-navigation">';
            }
            $html .= '<ul class="term-nav' . $is_submenu . '">';
            $html .= '<li><a href="' . $all_link . '">' . esc_html__( 'All', 'rather-simple-term-filter' ) . '</a></li>';
            foreach ( $terms as $term ) {
                $classes = [];
                $classes[] = 'term-item';
                $classes[] = ( $term->term_id == $current_term_id ) ? 'current-term' : '';
                $classes[] = ( term_is_ancestor_of( $term->term_id, $current_term_id, $taxonomy ) ) ? 'current-term-parent' : '';
                $html .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
                $html .= '<a href="' . get_term_link( $term->term_id ) . '">' . $term->name . '</a>';
                $html .= Rather_Simple_Term_Filter::get_taxonomy_hierarchy( $post_type, $taxonomy, $term->term_id, $depth + 1 );
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            if ( $depth == 0 ) {
                $html .= '</nav>';
            }
        }
        return $html;
    }
   
}

add_action( 'plugins_loaded', array( Rather_Simple_Term_Filter::get_instance(), 'plugin_setup' ) );