<?php 
if(isset($_COOKIE['AFF_ID']) && !is_admin()){
    add_action('woocommerce_order_status_cancelled', 'call_after_order_cancel');
    // add_action('woocommerce_order_status_pending', 'call_after_order_insert');
    add_action('woocommerce_order_status_changed', 'call_after_order_insert', 10, 3);
    // add_action('woocommerce_order_status_processing', 'call_after_order_update');
    add_action('woocommerce_after_order_details', 'call_after_order_update');
    // add_action( 'woocommerce_order_status_changed', array( $this, 'track_order_status_change' ), 10, 3 );
    add_action('user_register', 'call_after_new_customer_insert');
    $bearer = get_option("affilio_token");
    // var_dump($bearer);
    define('BEARER', $bearer);
}

function call_after_new_customer_insert($user_id)
{
    $options = get_option( 'affilio_option_name' );
    $webstore = $options['webstore'];
    $body = array(array(
        "user_id" => $user_id,
        "web_store_code" => $webstore,
        "affiliate_id" => $_COOKIE['AFF_ID']
    ));

    $params = array(
        'body'    => wp_json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            'Authorization' => 'Bearer ' . BEARER,
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(SYNC_NEW_CUSTOMER_API), $params);
    // log_me($response);
    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
}

function call_after_order_insert($id, $pre, $next)
{
    // log_me($pre);
    $args = array(
        'post_type' => 'shop_order',
        //    'posts_per_page' => '-1'
    );
    $order_ = wc_get_order(
        $id
    );
    $orders = array($order_);

    $body = [];
    foreach ($orders as $order) :
        $orderItems = [];
        foreach ($order->posts as $orderItem) :
            array_push($orderItems, $orderItem);
        endforeach;

        $options = get_option( 'affilio_option_name' );
        $webstore = $options['webstore'];

        $val = array(
            'basket_id' => $order->order_key,
            'order_id' => $order->id,
            'web_store_id' => $webstore,
            'affiliate_id' => $_COOKIE['AFF_ID'],
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

    $params = array(
        'body'    => json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            'Authorization' => 'Bearer ' . BEARER,
        ),
    );


    if($pre === 'pending' && $next === 'processing'){
        $response = wp_safe_remote_post(esc_url_raw(SYNC_ORDER_API), $params);
        $isSuccess = json_decode($response['body'])->success;
        // log_me($isSuccess);
        if($isSuccess){
            set_option(AFI_LAST_ORDER, $id);
        }

        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
    }
    if($next === 'canceled'){
        $response = wp_safe_remote_post(esc_url_raw(SYNC_ORDER_CANCEL_API), $params);
        $isSuccess = json_decode($response['body'])->success;
        // log_me($isSuccess);
        if($isSuccess){
            // set_option(AFI_LAST_ORDER, $id);
        }

        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
    }
    if($next){
        $response = wp_safe_remote_post(esc_url_raw(SYNC_ORDER_UPDATE_API), $params);
        $isSuccess = json_decode($response['body'])->success;
        // log_me($isSuccess);

        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
    }
}

function call_after_order_update($id)
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
            'Authorization' => 'Bearer ' . BEARER,
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