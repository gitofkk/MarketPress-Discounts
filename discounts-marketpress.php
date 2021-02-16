<?php
/*
Plugin Name: MarketPress Discounts
Description: Add discount to bulk of products on MarketPress for WordPress.
Version: 1.0
Author:       Kanikannan M
Author URI:   https://www.upwork.com/fl/kanikannanm
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( is_plugin_active( 'wordpress-ecommerce/marketpress.php' ) ) {
    //plugin is activated
    add_filter( 'bulk_actions-edit-product', 'mp_di_bulk_actions_edit_product' );
    add_action( 'admin_enqueue_scripts', 'mp_di_enqueue' );    
    add_action('admin_head', 'mp_di_custom_styles');
} 

function mp_di_bulk_actions_edit_product( $bulk_actions ) {
	$bulk_actions['mp_di_set_product_sale_price'] = __( 'Discount', 'wordpress' );
	return $bulk_actions;
}


function mp_di_enqueue($hook) {
    if ( 'edit.php' != $hook ) {
        return;
    }

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );  
    wp_enqueue_style( 'jquery-ui-datepicker' );   
    wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'jquery-ui.css' );

    wp_enqueue_script( 'mp_di_custom_script', plugin_dir_url( __FILE__ ) . 'discounts.js' );    
}



function mp_di_custom_styles() {
  echo '<style>
    .de-custom-bulk-actions-elements {
      padding: 5px !important;
    } 
  </style>';
}

function mp_di_handle_bulk_actions_edit_product( $redirect_to, $action, $post_ids ) {
    
	if ( $action !== 'mp_di_set_product_sale_price' ) {
		return $redirect_to;
	} else if ( ! isset( $_REQUEST['mp_di_bulk_product_discount_percent'] ) || empty ( $_REQUEST['mp_di_bulk_product_discount_percent'] ) ) {
		return $redirect_to;
	}

	$updated_post_ids = array();
	$discount_percent =  (float) $_REQUEST['mp_di_bulk_product_discount_percent'];
    
	foreach ( $post_ids as $post_id ) {
    	$product = get_post( $post_id );

    	if  ( $product->post_type == 'product' ) {

            $regular_price =  (float) get_post_meta( $post_id, "regular_price", true ); 
            $discount_price = $regular_price * $discount_percent / 100;

            $sale_price =  $regular_price - $discount_price;
            $start_date = date('Y-m-d', strtotime($_REQUEST['mp_di_bulk_product_discount_start']));
            $end_date = date('Y-m-d', strtotime($_REQUEST['mp_di_bulk_product_discount_end']));
            //$sale_price_array = array("sale_price_amount" => "24", "sale_price_percentage" => "20", "sale_price_start_date" => "2018-02-03", "sale_price_end_date" => "2018-02-23");

            update_post_meta( $post_id, 'has_sale', 1 );
            update_post_meta( $post_id, 'sale_price_amount', $sale_price );
            update_post_meta( $post_id, 'sale_price_percentage', sanitize_text_field($_REQUEST['mp_di_bulk_product_discount_percent']) ); 
            update_post_meta( $post_id, 'sale_price_start_date', $start_date );
            update_post_meta( $post_id, 'sale_price_end_date', $end_date );
            
            update_post_meta( $post_id, 'sort_price', $sale_price  );
            //update_post_meta( $post_id, 'sale_price', $sale_price_array  );

			$updated_post_ids[] = $post_id;
    	}
  	}

	$redirect_to = add_query_arg( 'mp_di_bulk_product_sale_price_update_results', count( $updated_post_ids ), $redirect_to );

	return $redirect_to;
}

add_filter( 'handle_bulk_actions-edit-product', 'mp_di_handle_bulk_actions_edit_product', 10, 3 );


function mp_di_bulk_action_edit_product_admin_notice() {
    if ( isset( $_REQUEST['mp_di_bulk_product_sale_price_update_results'] ) ) {
      $updated_products_count = intval( $_REQUEST['mp_di_bulk_product_sale_price_update_results'] );

      echo '<div id="message" class="' . ( $updated_products_count > 0 ? 'updated' : 'error' ) . '">';

      if ( $updated_products_count > 0 ) {
          echo '<p>' . __( 'Updated discount price for '. $updated_products_count .' '. _n( 'product', 'products', $updated_products_count, 'wordpress' ).'!', 'wordpress' ) . '</p>';
      } else {
          echo '<p>' . __( 'No products were updated!', 'wordpress' ) . '</p>';
      }

      echo '</div>';
    }
}

add_action( 'admin_notices', 'mp_di_bulk_action_edit_product_admin_notice' );