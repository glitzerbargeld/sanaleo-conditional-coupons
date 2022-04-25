<?php

/**
 * Plugin Name: Sanaleo Conditional Coupons
 * Description: Adds Coupons based on Customers Purchases
 */

if (!function_exists('write_log')) {

    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}



function create_conditional_coupon($order_id) {


    /**
     * Create a coupon programatically
    */

    $order = wc_get_order($order_id);
    $customer_email = $order -> get_billing_email();
    $first_name = $order->get_billing_first_name();
    $last_name  = $order->get_billing_last_name();

    $items = $order->get_items();

    foreach ( $items as $item ) {      
        $product_id = $item->get_product_id();
        $prod_meta_data = $item->get_formatted_meta_data();

        if ( has_term( 'cbd-blueten', 'product_cat', $product_id )) {

            //$coupon_code = 'WP_TESTCODE_BLUETEN'; 
            $coupon = array(
                'post_title' => 'meta3',
                'post_content' => '',
                'post_excerpt' => $prod_meta_data,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon');
                
            $new_coupon_id = wp_insert_post( $coupon );

            update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
            update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
            update_post_meta( $new_coupon_id, 'individual_use', 'no' );
            update_post_meta( $new_coupon_id, 'product_ids', '' );
            update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
            update_post_meta( $new_coupon_id, 'usage_limit', '' );
            update_post_meta( $new_coupon_id, 'expiry_date', '' );
            update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
            update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

        }   
    }
}


add_action( 'woocommerce_order_status_completed', 'create_conditional_coupon', 10, 1);





