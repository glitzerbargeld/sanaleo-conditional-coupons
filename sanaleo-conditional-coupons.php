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

function random_str_generator ($len_of_gen_str){
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $var_size = strlen($chars);
    $random_str = '';
    for( $x = 0; $x < $len_of_gen_str; $x++ ) {  
        $random_str= $random_str . $chars[ rand( 0, $var_size - 1 ) ];  
    }
    return $random_str;
}


$dir = plugin_dir_path( __FILE__ );
require_once($dir . 'vendor/autoload.php');

function run_mc($mailadress){
 try {
    $mailchimp = new MailchimpTransactional\ApiClient();
    $mailchimp->setApiKey('j6ZwO6da1H1a8OfzqLJB2w');
    $response = $mailchimp->users->ping();
    wp_mail( $mailadress, "mctest", $response );
    print_r($response);
    var_dump($response);

  } catch (Error $e) {
        echo 'Error: ',  $e->getMessage(), "\n";
  }
}


function run_message($message, $template_name, $discount_code, $date)
{
    try {
        $mailchimp = new MailchimpTransactional\ApiClient();
        $mailchimp->setApiKey('j6ZwO6da1H1a8OfzqLJB2w');

        $response = $mailchimp->messages->sendTemplate([
            "template_name" => $template_name,
            "message" => $message,
            "send_at" => $date,
            "template_content" => [["discount_code" => $discount_code,]],
        ]);

        print_r($response);
    } catch (Error $e) {
        echo 'Error: ', $e->getMessage(), "\n";
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
        $menge = $item->get_meta('pa_menge');
        $has_10g = FALSE;        

        if ( has_term( 'cbd-blueten', 'product_cat', $product_id ) && $menge = "10g") {

            $has_10g = TRUE; 
     
        }
    }

    if($has_10g) {

        $rand_str = random_str_generator(4); 
        $coupon_name = $menge . '-' . $rand_str . '-' . $first_name[0] . $last_name[0];
        $coupon = array(
            'post_title' => $coupon_name,
            'post_content' => '',
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

        $to = $customer_email;
        $two_minutes = time() + 120;
        $template = "rm-cs-bl-ten10gr";
        $subject = 'Dein Gutschein für 10g CBD Blüten' . $two_minutes;
        wp_mail( $to, $subject, $coupon_name );
        
        $message = [
            "from_email" => "info@sanaleo.com",
            "subject" => "Hello world",
            "to" => [
                [
                    "email" => "torben@sanaleo.com",
                    "type" => "to"
                ]
            ]
        ];

        run_message($message, $template, $coupon_name, $two_minutes);
    }
}

add_action( 'woocommerce_order_status_completed', 'create_conditional_coupon', 10, 1);





