<?php
/*
Plugin Name: Rather Simple Taxonomy Navigation
Plugin URI:
Update URI: false 
Description: Adds a taxonomy navigation
Version: 1.0
Requires at least: 4.9
Requires PHP: 7.0
WC tested up to: 4.9
Author: Oscar Ciutat
Author URI: http://oscarciutat.com/code/
Text Domain: rather-simple-taxonomy-navigation
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

class Rather_Simple_Taxonomy_Navigation {
    
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

        add_action( 'show_taxonomy_navigation', array( $this, 'show_taxonomy_navigation' ), 10, 3 );

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
        load_plugin_textdomain( 'rather-simple-taxonomy-navigation', '', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueues scripts and styles in the frontend.
     */
    function wp_enqueue_scripts(){
        wp_enqueue_style( 'rstn-style', plugins_url( 'style.css', __FILE__ ) );
    }

    /**
     * Show terms navigation
     *
     */
    public function show_taxonomy_navigation( $post_type = 'post', $taxonomy = 'category', $parent = 0 ) {
        $html = '';

        if ( $parent != 0 && ! term_exists( $parent, $taxonomy ) ) {
            // Check if parent term exists
            $parent = 0;
        }

        if ( is_taxonomy_hierarchical( $taxonomy ) ) {
            $html = Rather_Simple_Taxonomy_Navigation::get_taxonomy_hierarchy( $post_type, $taxonomy, $parent );
        }
        echo $html;
    }

    /**
     * Get taxonomy hierarchy
     *
     * @param $post_type
     * @param $taxonomy
     * @param $parent
     * @param $depth
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
            if ( $depth == 0 ) {
                $html .= '<nav class="taxonomy-navigation">';
            }
            $ul_classes = [];
            $ul_classes[] = 'terms';
            $html .= '<ul class="' . esc_attr( implode( ' ', array_filter( $ul_classes ) ) ) . '">';
            $all_link = ( $parent > 0 ) ? get_term_link( $parent ) : get_post_type_archive_link( $post_type );
            $html .= '<li><a href="' . esc_url( $all_link ) . '">' . esc_html__( 'All', 'rather-simple-taxonomy-navigation' ) . '</a></li>';
            foreach ( $terms as $term ) {
                $li_classes = [];
                $li_classes[] = 'term-item';
                $li_classes[] = ( $term->term_id == $current_term_id ) ? 'current-term' : null;
                $li_classes[] = ( term_is_ancestor_of( $term->term_id, $current_term_id, $taxonomy ) ) ? 'current-term-parent' : '';
                $html .= '<li class="' . esc_attr( implode( ' ', array_filter( $li_classes ) ) ) . '">';
                $html .= '<a href="' . esc_url( get_term_link( $term->term_id ) ) . '">' . $term->name . '</a>';
                $html .= Rather_Simple_Taxonomy_Navigation::get_taxonomy_hierarchy( $post_type, $taxonomy, $term->term_id, $depth + 1 );
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

add_action( 'plugins_loaded', array( Rather_Simple_Taxonomy_Navigation::get_instance(), 'plugin_setup' ) );