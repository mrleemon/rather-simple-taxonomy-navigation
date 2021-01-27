<?php
/*
Plugin Name: Rather Simple Category Filter
Plugin URI: 
Description: Adds a category filter
Version: 1.0
WC tested up to: 4.4.1
Author: Oscar Ciutat
Author URI: http://oscarciutat.com/code/
Text Domain: rather-simple-category-filter
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

class Rather_Simple_Category_Filter {
    
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

        //add_action( 'woocommerce_before_shop_loop', array( $this, 'add_category_filter_links' ) );
        add_action( 'woocommerce_before_shop_loop', array( $this, 'show_terms_navigation' ) );

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
        load_plugin_textdomain( 'rather-simple-category-filter', '', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueues scripts and styles in the frontend.
     */
    function wp_enqueue_scripts(){
        wp_enqueue_style( 'rscf-style', plugins_url( 'style.css', __FILE__ ) );
    }

    /**
     * Get taxonomy hierarchy
     *
     * @param $taxonomy
     * @param $parent
	 * 
	 * @return string
     */
    public function get_taxonomy_hierarchy( $taxonomy, $parent = 0, $depth = 0 ) {
        $taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
        $html = '';
        $current_term_id = get_queried_object_id();
        $args = array(
            'taxonomy'   => 'product_cat',
            'parent'     => $parent,
            'order_by'   => 'name',
            'hide_empty' => true,
        );
        $terms = get_terms( $args );
        if ( $terms ) {
            $is_submenu = ( $depth > 0 ) ? ' sub-menu': '';
            $html .= '<ul class="term-nav' . $is_submenu . '">';
            $html .= '<li><a href="">' . esc_html( 'All', 'rather-simple-category-filter' ) . '</a></li>';
            foreach ( $terms as $term ) {
                $classes = [];
                $classes[] = 'term-item';
                $classes[] = ( $term->term_id == $current_term_id ) ? 'current-term' : '';
                $classes[] = ( term_is_ancestor_of( $term->term_id, $current_term_id, $taxonomy ) ) ? 'current-term-parent' : '';
                $html .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
                $html .= '<a href="' . get_term_link( $term->term_id ) . '">' . $term->name . '</a>';
                $html .= Rather_Simple_Category_Filter::get_taxonomy_hierarchy( $taxonomy, $term->term_id, $depth + 1 );
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * Show terms navigation
     *
     */
    public function show_terms_navigation() {
        $html = Rather_Simple_Category_Filter::get_taxonomy_hierarchy( 'product_cat' );
        echo $html;
    }
    
}

add_action( 'plugins_loaded', array( Rather_Simple_Category_Filter::get_instance(), 'plugin_setup' ) );