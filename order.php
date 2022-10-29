<?php
$AF_ID = isset($_COOKIE['AFF_ID']) && sanitize_text_field($_COOKIE['AFF_ID']);
if ($AF_ID && !is_admin()) {
    add_action('user_register', 'affilio_call_after_new_customer_insert');
    add_action('woocommerce_order_status_changed', 'affilio_call_after_order_update', 10, 3);
    add_action('woocommerce_order_status_cancelled', 'affilio_call_after_order_cancel');
    $bearer = get_option("affilio_token");
    define('AFFILIO_BEARER', $bearer);
    
}

function affilio_call_after_new_customer_insert($user_id)
{
    $options = get_option('affilio_option_name');
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
            'Authorization' => 'Bearer ' . AFFILIO_BEARER,
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_NEW_CUSTOMER_API), $params);
    // affilio_log_me($response);
    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
}

function affilio_call_after_order_update($id, $pre, $next)
{
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

        $options = get_option('affilio_option_name');
        $webstore = $options['webstore'];

        $val = array(
            'basket_id' => $order->order_key,
            'order_id' => $order->id,
            'web_store_id' => $webstore,
            'affiliate_id' => $_COOKIE['AFF_ID'],
            'is_new_customer' => '',
            // 'order_status' => $order->status,
            'order_status' => afiilio_get_order_status($order->status),
            'shipping_cost' => $order->shipping_total,
            'discount' => $order->discount_total,
            'order_amount' => $order->total,
            'source' => '',
            'created_at' => afiilio_get_time($order_->date_created), //"2022-10-12 07:40:41.000000",
            'close_source' => '',
            'state' => $order->billing->city,
            'city' => $order->billing->city,
            'user_id' => $order->customer_id,
            'voucher_code' => '',
            'voucher_type' => '',
            'voucher_price' => $order->discount_total,
            'vat_price' => $order->total_tax,
            'voucher_percent' => '',
            // 'update_date' => $order->date_modified->date,
            'update_date' => afiilio_get_time($order_->date_modified), //"2022-10-12 07:40:41.000000",
            // 'delivery_date' => $order->date_completed->date,
            'delivery_date' => $order_->date_completed ? afiilio_get_time($order_->date_completed) : "", //"2022-10-12 07:40:41.000000",
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
            'Authorization' => 'Bearer ' . AFFILIO_BEARER,
        ),
    );


    if ($pre === 'pending' && $next === 'processing') {
        $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_ORDER_API), $params);
        $isSuccess = json_decode($response['body'])->success;
        // affilio_log_me($isSuccess);
        if ($isSuccess) {
            affilio_set_option(AFFILIO_LAST_ORDER, $id);
        }

        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
    }
    if ($next === 'canceled') {
        $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_ORDER_CANCEL_API), $params);
        $isSuccess = json_decode($response['body'])->success;
        // affilio_log_me($isSuccess);
        if ($isSuccess) {
            // affilio_set_option(AFFILIO_LAST_ORDER, $id);
        }

        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
    }
    if ($next) {
        $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_ORDER_UPDATE_API), $params);
        $isSuccess = json_decode($response['body'])->success;
        // affilio_log_me($isSuccess);

        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
    }
}

function affilio_call_after_order_cancel($order_id)
{
    affilio_log_me('This is a message for debugging purposes');
    affilio_log_me(array("This is a message" => 'xx', 'orderId' => $order_id));

    $body = array(array(
        "order_id" => $order_id,
        "basket_id" => "string",
        "web_store_code" => AFFILIO_WEB_STORE_ID
    ));

    $params = array(
        'body'    => wp_json_encode($body),
        // 'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            'Authorization' => 'Bearer ' . AFFILIO_BEARER,
        ),
    );
    $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_ORDER_CANCEL_API), $params);

    if (is_wp_error($response)) {
        return $response;
    } elseif (empty($response['body'])) {
        return new WP_Error('AFFILIO-api', 'Empty Response');
    }
    parse_str($response['body'], $response_);
}

function affilio_call_new_order_insert($order_id)
{
    echo "<div style='direction:ltr;'><pre>";
    echo "</pre></div>";
}


// TEST METHOD
// add_action( 'woocommerce_loaded', 'testfunctiontest' );
add_action('init', 'affilio_init', 10, 0);
function affilio_init()
{
    // $local_tz = new \DateTimeZone(wc_timezone_string());
    // $order_ = wc_get_order(absint(86));
}

function afiilio_get_time($time)
{
    return $time->date('Y-m-d H:i:s.z') . "Z";
}

function afiilio_get_order_status($status)
{
    // 'pending'    => 'https://schema.org/OrderPaymentDue',
    // 'processing' => 'https://schema.org/OrderProcessing',
    // 'on-hold'    => 'https://schema.org/OrderProblem',
    // 'completed'  => 'https://schema.org/OrderDelivered',
    // 'cancelled'  => 'https://schema.org/OrderCancelled',
    // 'refunded'   => 'https://schema.org/OrderReturned',
    // 'failed'     => 'https://schema.org/OrderProblem',
    $rtn = 1;
    $affilio_order_status = array(
        "New" => 1,
        "MerchantApproved" => 2,
        "Finalize" => 3,
        "Canceled" => 4,
    );

    switch ($status) {
        case 'completed':
            $rtn = $affilio_order_status["Finalize"];
            break;
        case 'pending':
        case 'on-hold':
        case 'processing':
            $rtn = $affilio_order_status["New"];
            break;
        case 'cancelled':
        case 'failed':
            $rtn = $affilio_order_status["Canceled"];
            break;
    }
    return $rtn;
}
