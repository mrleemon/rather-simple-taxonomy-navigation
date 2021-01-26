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

        add_action( 'woocommerce_before_shop_loop', array( $this, 'add_category_filter_links' ) );

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
     * Generates filter categories array
     *
     * @param $params
	 * 
	 * @return array
     */
	public function get_filter_categories( $params ) {
		
		$cat_id = 0;
		$top_category = '';
		$categories_groups = array();
		$parent_categories = array();
		$child_categories = array();
		
		if ( !empty( $params['category'] ) ) {	
			$top_category = get_term_by( 'slug', $params['category'], 'product_cat' );
			if ( isset( $top_category->term_id ) ) {
				$cat_id = $top_category->term_id;
			}
		}
		
		$args = array(
            'taxonomy'   => 'product_cat',
			'child_of'   => $cat_id,
            'orderby'    => $params['filter_order_by'],
            'hide_empty' => true,
		);
		
		$filter_categories = get_terms( $args );

		if ( !empty( $params['category'] ) ) {
			$child = array();
			$child['id'] = 0;
			$child['value'] = $filter_categories;
			$child_categories[] = $child;
			$categories_groups['child_categories'] = $child_categories;
			$categories_groups['parent_categories'] = $parent_categories;
		}
		else {
			foreach ( $filter_categories as $filter ) {
				if ( $filter->parent == 0 ) {
					$parent_categories[] = $filter;
				}
			}
			$categories_groups['parent_categories'] = $parent_categories;

			foreach ( $parent_categories as $parent ) {
				$args = array(
                    'taxonomy'   => 'product_cat',
					'child_of'   => $parent->term_id,
                    'order_by'   => $params['filter_order_by'],
                    'hide_empty' => true,
				);
				$child = array();
				$child['id'] = $parent->term_id;
				$child['value'] = get_terms( $args );
				$child_categories[] = $child;
			}
			$categories_groups['child_categories'] = $child_categories;
		}
		
		return $categories_groups;
		
	}

    /**
     * Generates product categories html based on id
     *
     * @param $params
     *
     * @return html
     */
	public function add_category_filter_links() {
        $params['filter_order_by'] = 'name';
        $filter_categories = Rather_Simple_Category_Filter::get_filter_categories( $params );
        ?>
        <div class="qodef-filter-holder-inner">
        <?php
        $rand_number = rand();
        $term_id = get_queried_object_id();
        if ( is_array( $filter_categories ) && is_array( $filter_categories['parent_categories'] ) && count( $filter_categories['parent_categories'] ) ) { ?>
			<ul class="qodef-filter-parent-categories clearfix" data-class="filter_<?php echo $rand_number; ?>">
				<?php
				$parent = array();
				foreach ( $filter_categories['parent_categories'] as $par ) {
					$parent[] = '.portfolio_category_' . $par->term_id;
				}
				$all_parent_array = implode( ', ', $parent );
                ?>
					<li data-class="filter_<?php echo $rand_number; ?>" class="parent-filter filter_<?php echo $rand_number; ?>" data-filter="<?php echo $all_parent_array; ?>" data-group-id="-1"><a href=""><?php esc_html_e( 'All', 'rather-simple-category-filter' ); ?></a></li>
				<?php
                foreach ( $filter_categories['parent_categories'] as $parent ) :
                    $is_term_active = ( $parent->term_id == $term_id ) ? ' current-term' : '';
                    ?>
					<li class="parent-filter filter_<?php echo $rand_number; ?><?php echo $is_term_active; ?>" data-filter=".portfolio_category_<?php echo $parent->term_id; ?>" data-class="filter_<?php echo $rand_number; ?>" data-group-id="<?php echo $parent->term_id; ?>"><a href="<?php echo get_term_link( $parent->term_id ); ?>"><?php echo $parent->name; ?></a></li>
                <?php
                endforeach;
                ?>
			</ul>
        <?php }

        if ( is_array( $filter_categories ) && is_array( $filter_categories['child_categories'] ) && count( $filter_categories['child_categories'] ) ) { ?>
            <div class="qodef-filter-child-categories-holder">
                <?php foreach ( $filter_categories['child_categories'] as $child_group ) {
                    $is_parent_active = ( $child_group['id'] == $term_id || term_is_ancestor_of( $child_group['id'], $term_id, 'product_cat' ) ) ? ' current-term' : '';
                    ?>
                    <ul class="qodef-filter-child-categories clearfix <?php echo esc_attr( $single_cat_class ); ?><?php echo $is_parent_active; ?>" data-parent-id="<?php echo $child_group['id']; ?>" data-class="filter_<?php echo $rand_number; ?>">
                        <?php
                        if ( is_array( $child_group['value'] ) && count( $child_group['value'] ) ) {
                            $children = array();
                            foreach ( $child_group['value'] as $child ) {
                                $children[] = '.portfolio_category_' . $child->term_id;
                            }
                            $children[] = '.portfolio_category_' . $child_group['id'];
                            $all_array = implode( ', ', $children );
                            ?>
                                <li data-class="filter_<?php echo $rand_number; ?>" class="filter_<?php echo $rand_number; ?>" data-filter="<?php echo $all_array; ?>"><a href=""><?php esc_html_e( 'All', 'rather-simple-category-filter' ); ?></a></li>
                            <?php
                            foreach ( $child_group['value'] as $child ) :
                                $is_term_active = ( $child->term_id == $term_id ) ? ' current-term' : '';
                                ?>
                                    <li data-class="filter_<?php echo $rand_number; ?>" class="filter_<?php echo $rand_number; ?><?php echo $is_term_active; ?>" data-filter=".portfolio_category_<?php echo $child->term_id; ?>">
                                        <a href="<?php echo get_term_link( $child->term_id ); ?>"><?php echo $child->name; ?></a>
                                    </li>
                                <?php
                            endforeach;
                        } ?>
                    </ul>
                <?php } ?>
            </div>
        <?php } ?>
        </div>
        <?php

    }

    /**
     * Generates product categories html based on id
     *
     * @param $params
     *
     * @return html
     */
	public function add_category_filter() {
        $params['filter_order_by'] = 'name';
        $filter_categories = Rather_Simple_Category_Filter::get_filter_categories( $params );
        ?>
        <div class="qodef-filter-holder-inner">
        <?php
        $rand_number = rand();
        if ( is_array( $filter_categories ) && is_array( $filter_categories['parent_categories'] ) && count( $filter_categories['parent_categories'] ) ) { ?>
			<ul class="qodef-filter-parent-categories clearfix" data-class="filter_<?php echo $rand_number; ?>">
				<?php
				$parent = array();
				foreach ( $filter_categories['parent_categories'] as $par ) {
					$parent[] = '.portfolio_category_' . $par->term_id;
				}
				$all_parent_array = implode( ', ', $parent );
                ?>
					<li data-class="filter_<?php echo $rand_number; ?>" class="parent-filter filter_<?php echo $rand_number; ?>" data-filter="<?php echo $all_parent_array; ?>" data-group-id="-1"><span><?php esc_html_e( 'All', 'rather-simple-category-filter' ); ?></span></li>
				<?php
				foreach ( $filter_categories['parent_categories'] as $parent ) : ?>
					<li class="parent-filter filter_<?php echo $rand_number; ?>" data-filter=".portfolio_category_<?php echo $parent->term_id; ?>" data-class="filter_<?php echo $rand_number; ?>" data-group-id="<?php echo $parent->term_id; ?>"><span><?php echo $parent->name; ?></span></li>
                    <?php
                endforeach;
                ?>
			</ul>
        <?php }

        if ( is_array( $filter_categories ) && is_array( $filter_categories['child_categories'] ) && count( $filter_categories['child_categories'] ) ) { ?>
            <div class="qodef-filter-child-categories-holder">
                <?php foreach ( $filter_categories['child_categories'] as $child_group ) { ?>
                    <ul class="qodef-filter-child-categories clearfix <?php echo esc_attr( $single_cat_class ); ?>" data-parent-id="<?php echo $child_group['id']; ?>" data-class="filter_<?php echo $rand_number; ?>">
                        <?php

                        if ( is_array( $child_group['value'] ) && count( $child_group['value'] ) ) {
                            $children = array();
                            foreach ( $child_group['value'] as $child ) {
                                $children[] = '.portfolio_category_' . $child->term_id;
                            }
                            $children[] = '.portfolio_category_' . $child_group['id'];
                            $all_array = implode( ', ', $children );
                            ?>
                                <li data-class="filter_<?php echo $rand_number; ?>" class="filter_<?php echo $rand_number; ?>" data-filter="<?php echo $all_array; ?>"><span><?php esc_html_e( 'All', 'rather-simple-category-filter' ); ?></span></li>
                            <?php
                            foreach ( $child_group['value'] as $child ) :
                                ?>
                                    <li data-class="filter_<?php echo $rand_number; ?>" class="filter_<?php echo $rand_number; ?>" data-filter=".portfolio_category_<?php echo $child->term_id; ?>">
                                        <span>
                                            <?php echo $child->name; ?>
                                        </span>
                                    </li>
                                <?php
                            endforeach;
                        } ?>
                    </ul>
                <?php } ?>
            </div>
        <?php } ?>
        </div>
        <?php

    }

    
}

add_action( 'plugins_loaded', array( Rather_Simple_Category_Filter::get_instance(), 'plugin_setup' ) );