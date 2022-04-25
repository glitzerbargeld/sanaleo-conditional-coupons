<?php

/**
 * Plugin Name: Sanaleo Conditional Coupons
 * Description: Adds Coupons based on Customers Purchases
 */



function create_conditional_coupon($order_id) {


    /**
     * Create a coupon programatically
    */

    $coupon_code = 'WP_TESTCODE'; // Code
    $amount = '10'; // Amount
    $discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product

    $coupon = array(
    'post_title' => $coupon_code,
    'post_content' => '',
    'post_status' => 'publish',
    'post_author' => 1,
    'post_type' => 'shop_coupon');

    $new_coupon_id = wp_insert_post( $coupon );

    // Add meta
    update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
    update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
    update_post_meta( $new_coupon_id, 'individual_use', 'no' );
    update_post_meta( $new_coupon_id, 'product_ids', '' );
    update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
    update_post_meta( $new_coupon_id, 'usage_limit', '' );
    update_post_meta( $new_coupon_id, 'expiry_date', '' );
    update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
    update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

    $order = wc_get_order($order_id);
    $customer_email = $order -> get_billing_email();
    $first_name = $order->get_billing_first_name();
    $last_name  = $order->get_billing_last_name();

    $items = $order->get_items();

    foreach ( $items as $item ) {      
        $product_id = $item->get_product_id();

        if($item->has_weight()){
            $product_weight = $item->get_weight();
        } 

        if ( has_term( 'cbd-blueten', 'product_cat', $product_id ) && $product_weight == "10") {
           $cat_in_order = true;
           break;
        }   
    }
}


add_action( 'woocommerce_order_status_completed', 'create_conditional_coupon', 10, 1);





