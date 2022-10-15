<?php

/**
 * Plugin Name: Affilo Integration Wordpress
 * Plugin URI: https://www.Affilo.ir/
 * Description: Affilo Integration Wordpress PLugin.
 * Version: 1.0
 * Author: DINGO
 * Author URI: http://Affilo.ir/
 * shift + option + F
 **/
if (!defined('ABSPATH')) {
    exit;
}
require __DIR__ . '/config.php';
require __DIR__ . '/client.php';
// define('WEB_STORE_ID', 5175616687319568587);
define('AFF_ID', "NzdhZGFiZTAtZGQxMC00YTA2LTgzNDItNTY0NzM0YTFkOThk");

/**
 * 
 * ACTIONS AFFILIO PLUGIN
 * 
 */
function call_after_order_update()
{
}

function call_after_order_cancel($order_id)
{
    log_me('This is a message for debugging purposes');
    log_me(array("This is a message" => 'xx', 'orderId' => $order_id));

    $body = array(array(
        "order_id" => $order_id,
        "basket_id" => "string",
        "web_store_code" => WEB_STORE_ID
    ));

    $params = array(
        'body'    => wp_json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(SYNC_ORDER_CANCEL_API), $params);

    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
    parse_str($response['body'], $response_);
}
add_action('woocommerce_order_status_cancelled', 'call_after_order_cancel');
// add_action( 'woocommerce_order_status_processing', 'call_after_order_cancel' );
// add_action( 'woocommerce_order_status_changed', 'call_after_order_cancel');

function call_after_new_customer_insert($user_id)
{
    log_me('This is a message for debugging purposes');
    log_me(array("This is a message" => 'xx', 'user_id' => $user_id));

    $body = array(array(
        "user_id" => $user_id,
        "web_store_code" => WEB_STORE_ID,
        "affiliate_id" => AFF_ID
    ));

    $params = array(
        'body'    => wp_json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(SYNC_ORDER_CANCEL_API), $params);

    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
    parse_str($response['body'], $response_);
}
add_action('woocommerce_order_status_cancelled', 'call_after_order_cancel');
add_action('user_register', 'call_after_new_customer_insert');

function auth_login()
{
    $body = array(
        'user_name' => "09360004748",
        'password' => "09360004748",
        'remember_me' => true,
    );

    $params = array(
        'body'    => wp_json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(AUTH_LOGIN), $params);
    echo "<div style='direction:ltr'><pre>";
    // parse_str($response['body'], $response_);
    // var_dump(json_decode($response['body'])->data);
    $GLOBALS['bearer'] = json_decode($response['body'])->data;
    // var_dump(esc_url_raw(AUTH_LOGIN));
    echo "</pre></div>";
}

function init_set_cookie()
{
    // auth_login();
    // https://salam.com/?utm_source=https%253A%252F%252Ftakhfifan.com&utm_medium=Affilio&utm_campaign=ohgv&utm_content=&affid=NzdhZGFiZTAtZGQxMC00YTA2LTgzNDItNTY0NzM0YTFkOThk&exp=2
    // $b64 = 'aHR0cHM6Ly93d3cuZGlnaWthbGEuY29tL3NlYXJjaC9jYXRlZ29yeS1ncmFwaGljcy1jYXJkLz9wcmljZVttaW5dPTEwMDAwMDAwMCZwcmljZVttYXhdPTE4MDAwMDAwMA==';
    // $extraParam = 'ehdiw';
    // $utms = 'graphiccard';
    // $publicAffId = '55854ffc-acb3-4c3a-a81f-c0100365671f'; // b
    // $privateAffId = '55854ffc-acb3-4c3a-a81f-c0100365671f';
    // // http://migmig.affilio.ir/api/v1/Click/b/55854ffc-acb3-4c3a-a81f-c0100365671f?b64=XXXX&extraParam=ehdiw&utms=graphiccard
    // var_dump($_GET);
    $utm_source = $_GET['utm_source'];
    $utm_medium = $_GET['utm_medium'];
    $utm_campaign = $_GET['utm_campaign'];
    $utm_content = $_GET['utm_content'];
    $aff_id = $_GET['affid'];
    $exp = $_GET['exp'];
    // echo $ppc;
    if (
        isset($utm_source) &&
        isset($utm_medium) && strtolower($utm_medium) === "affilio" &&
        isset($utm_campaign) &&
        isset($utm_content) &&
        isset($aff_id) &&
        isset($exp)
    ) {
        try {
            $expTime = time() + (86400 * $exp);
            setcookie("AFF_ID", $aff_id, $expTime);
        } catch (Exception $e) {
            var_dump($e);
        }
    }
}
add_action('init', 'init_set_cookie');

/**
 * 
 * SETUP AFFILIO PLUGIN
 * 
 */

function call_after_post_publish($post)
{
    // Code to be run after publishing the post
}
// add_action('wp_after_insert_post', 'call_after_post_publish', 10, 2);
// add_action('publish_post', 'call_after_post_publish', 10, 2);

function init_categories()
{
    // auth_login();
    global $wpdb;
    // Prepare Database
    // $table = $wpdb->prefix . "options";
    $table = $wpdb->options;

    $afi_sent_cats_name = 'afi_sent_cats';
    $results = $wpdb->get_results("SELECT * FROM {$table} WHERE option_name = '{$afi_sent_cats_name}'", OBJECT);

    $is_exists = count($results) > 0;
    $option_id = 0;
    if (!$is_exists) {
        $data = array('option_name' => 'afi_sent_cats', 'option_value' => 0);
        $format = array('%s', '%s');
        $wpdb->insert($table, $data, $format);
        $option_id = $wpdb->insert_id;
    } else {
        $option_id = $results[0]->option_id;
        $option = $results[0]->option_name;

        $data = array('option_value' => 126);
        $where = array('option_name' => $option);
        $result = $wpdb->update($wpdb->options, $data, $where);
        // if ( ! $result ) {
        //     return false;
        // }
    }
    $categories = get_categories(
        array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        )
    );

    $body = [];
    foreach ($categories as $cat) {
        $val = array(
            'web_store_id' => WEB_STORE_ID,
            'title' => $cat->cat_name,
            'category_id' => $cat->cat_ID,
            'parent_category_id' => $cat->category_parent,
            'is_active' => true,
            'is_deleted' => false,
        );

        array_push($body, $val);
    }

    $params = array(
        'body'    => json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            'Authorization'=> 'Bearer ' . $GLOBALS['bearer'],
        ),
    );

    $response = wp_safe_remote_post(esc_url_raw(SYNC_CATEGORY_API), $params);
    // get all categories of woo-commerce
    echo "<div style='direction:ltr'><pre>";
    echo esc_url_raw(SYNC_CATEGORY_API);
    print_r(json_encode($body, JSON_PRETTY_PRINT));
    // var_dump(wp_json_encode($body));
    print_r($response);
    echo "</pre></div>";

    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
    parse_str($response['body'], $response_);
}
// add_action('init', 'init_categories');

function init_products()
{
    // echo 'init_products Fired on the WordPress initialization';
    $args = array(
        'post_type'      => 'product',
        // 'posts_per_page' => 10,
        // 'product_cat'    => 'hoodies'
    );

    $loop = new WP_Query($args);
    $body = [];
    foreach ($loop->posts as $post) :
        $product = wc_get_product($post->ID);
        // echo "<div style='direction:ltr'><pre>";
        // var_dump($product);
        // echo "</pre></div>";
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');

        $val = array(
            'id' => $product->id,
            'title' => $product->name,
            'web_store_id' => WEB_STORE_ID,
            'category_id' => end($product->category_ids),
            'landing' => "",
            'description' => $product->description,
            'image' => reset($image),
            'alt_image' => "",
            'discount' => $product->regular_price - $product->price,
            'price' => $product->price,
            'code' => $product->sku,
            'is_incredible' => "",
            'is_promotion' => "",
            'is_available' => $post->post_status === 'publish',
            'product_score' => "",
            'price_tag' => $product->tag_ids,
        );
        array_push($body, $val);
    endforeach;
    // echo json_encode($val);

    // echo "<div style='direction:ltr'><pre>";
    // // print_r($body);
    // echo "</pre></div>";

    $params = array(
        'body'    => json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            'Authorization'=> 'Bearer ' . $GLOBALS['bearer'],
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(SYNC_PRODUCT_API), $params);
    // get all categories of woo-commerce
    echo "<div style='direction:ltr'><pre>";
    print_r($params);
    print_r($response);
    echo "</pre></div>";
    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
    parse_str($response['body'], $response_);
}
// add_action('init', 'init_products');

function init_orders()
{
    // echo 'init_orders Fired on the WordPress initialization';
    $args = array(
        'post_type' => 'shop_order',
        //    'posts_per_page' => '-1'
    );
    // $loop = new WP_Query($args);
    $orders = wc_get_orders(
        array(
            // 'limit'    => 1,
            // 'status'   => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
            // 'customer' => $user->ID,
        )
    );

    $body = [];
    foreach ($orders as $order) :
        // echo "<div style='direction:ltr'><pre>";
        // print_r(gettype($order));
        // print_r($order);
        // echo($order->date_created);
        // echo "</pre></div>";

        $orderItems = [];
        foreach ($order->posts as $orderItem) :
            array_push($orderItems, $orderItem);
        endforeach;
        
        // var_dump($order->date_created);
        // return;
        // echo "<br/>";

        $val = array(
            'basket_id' => $order->order_key,
            'order_id' => $order->id,
            'web_store_id' => WEB_STORE_ID,
            'affiliate_id' => AFF_ID,
            'is_new_customer' => '',
            // 'order_status' => $order->status,
            'order_status' => 1,
            'shipping_cost' => '',
            'discount' => '',
            'order_amount' => $order->total,
            'source' => '',
            'created_at' => "2022-10-12 07:40:41.000000",
            'close_source' => '',
            'state' => $order->status,
            'city' => $order->billing->city,
            'user_id' => $order->customer_id,
            'voucher_code' => '',
            'voucher_type' => '',
            'voucher_price' => $order->discount_total,
            'vat_price' => $order->total_tax,
            'voucher_percent' => '',
            // 'update_date' => $order->date_modified->date,
            'update_date' => "2022-10-12 07:40:41.000000",
            // 'delivery_date' => $order->date_completed->date,
            'delivery_date' => "2022-10-12 07:40:41.000000",
            'voucher_used_amount' => '',
            'order_items' => $orderItems
        );
        array_push($body, $val);
    endforeach;

    // echo "<div style='direction:ltr'><pre>";
    // print_r($body);
    // echo "</pre></div>";

    $params = array(
        'body'    => json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            'Authorization'=> 'Bearer ' . $GLOBALS['bearer'],
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(SYNC_ORDER_API), $params);
    // get all categories of woo-commerce
    echo "<div style='direction:ltr'><pre>";
    print_r($params);
    print_r($response);
    echo "</pre></div>";
    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
    parse_str($response['body'], $response_);
}
// add_action('init', 'init_orders');


/**
 * 
 * HELPERS
 * 
 */
function log_me($message)
{
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

function add_script_style() {
    // wp_enqueue_script('afi-js-file', plugin_dir_url(__FILE__) . '/script.js', '', time());
    // var_dump(plugin_dir_url(__FILE__) . 'assets/style.css');
    // var_dump(plugin_dir_url(__FILE__) . 'assets/style.css');
    // var_dump(plugin_dir(__FILE__) . 'assets/style.css');
    // wp_enqueue_style('afi-css-file', plugin_dir_url(__FILE__) . '/assets/style.css', array(), time(), 'all');
    // wp_enqueue_style( 'my-style', plugins_url( '/css/my-style.css', __FILE__ ), false, '1.0', 'all' ); // Inside a plugin
    // wp_enqueue_style('x'); // Inside a plugin

}
if ( is_admin() ){
    // add_action('wp_head', 'add_script_style');
    add_script_style();
}