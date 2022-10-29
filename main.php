<?php
class Affilio_Main
{
    public function __construct()
    {
        $affilio_options = get_option('affilio_option_name');
        $webId = $affilio_options['webstore']; // username
        if (!defined('AFFILIO_WEB_STORE_ID')) {
            define('AFFILIO_WEB_STORE_ID', $webId);
        }
    }

    public function auth_login($username, $password)
    {
        $body = array(
            'user_name' => $username,
            'password' => $password,
            'remember_me' => true,
        );

        $params = array(
            'body'    => wp_json_encode($body),
            // 'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json;charset=' . get_bloginfo('charset'),
            ),
        );
        try {
            $response = wp_safe_remote_post(esc_url_raw(AFFILIO_AUTH_LOGIN), $params);
            $hasError = null;
            if (!is_wp_error($response)) {
                $result = wp_remote_retrieve_body($response);
                $result = json_decode($result)->data;
                $hasError = isset($result->errors);
                if (!$hasError) {
                    if (strlen($result) > 150) {
                        $GLOBALS['bearer'] = $result;
                        add_option("affilio_connected", true);
                        add_option("affilio_token", $result);
                        update_option("affilio_connected", true);
                        update_option("affilio_token", $result);
                        return $result;
                    } else {
                        update_option("affilio_connected", false);
                    }
                }
            }
            if ($hasError && is_string($hasError)) {
                // $msg = '<div id="message" class="error notice is-dismissible"><p>' . $result . '</p></div>';
                // echo ($msg);
                affilio_admin_notice('error', $result);
                return;
            } else {
                // $msg = '<div id="message" class="error notice is-dismissible"><p>اطلاعات نامعتبر است</p></div>';
                // echo ($msg);
                affilio_admin_notice('error', 'اطلاعات نامعتبر است');
            }
        } catch (Exception $e) {
            // $msg = '<div id="message" class="error notice is-dismissible"><p>اطلاعات نامعتبر است</p></div>';
            // echo ($msg);
            affilio_admin_notice('error', 'اطلاعات نامعتبر است');
        }
    }

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
                'web_store_id' => AFFILIO_WEB_STORE_ID,
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
                'Authorization' => 'Bearer ' . $GLOBALS['bearer'],
            ),
        );

        $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_CATEGORY_API), $params);

        if (is_wp_error($response)) {
            $msg = '<div id="message" class="error notice is-dismissible"><p>خطای همگام سازی دسته بندی ها، لطفا مجددا تلاشی نمایید</p></div>';
            echo esc_html($msg);
            return $response;
        } elseif (empty($response['body'])) {
            $msg = '<div id="message" class="error notice is-dismissible"><p>خطای همگام سازی دسته بندی ها، لطفا مجددا تلاش نمایید</p></div>';
            echo esc_html($msg);
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
        $isSuccess = json_decode($response['body'])->success;
        if ($isSuccess) {
            return true;
        }
    }

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
                'web_store_id' => AFFILIO_WEB_STORE_ID,
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
                'Authorization' => 'Bearer ' . $GLOBALS['bearer'],
            ),
        );
        $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_PRODUCT_API), $params);
        if (is_wp_error($response)) {
            $msg = '<div id="message" class="error notice is-dismissible"><p>خطای همگام سازی محصولات، لطفا مجددا تلاش نمایید</p></div>';
            echo esc_html($msg);
            return $response;
        } elseif (empty($response['body'])) {
            $msg = '<div id="message" class="error notice is-dismissible"><p>خطای همگام سازی محصولات، لطفا مجددا تلاش نمایید</p></div>';
            echo esc_html($msg);
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
        $isSuccess = json_decode($response['body'])->success;
        if ($isSuccess) {
            return true;
        }
    }

    function init_orders()
    {
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
            $orderItems = [];
            foreach ($order->posts as $orderItem) :
                array_push($orderItems, $orderItem);
            endforeach;

            $val = array(
                'basket_id' => $order->order_key,
                'order_id' => $order->id,
                // 'web_store_id' => AFFILIO_WEB_STORE_ID,
                // 'affiliate_id' => AFF_ID,
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
                'Authorization' => 'Bearer ' . $GLOBALS['bearer'],
            ),
        );
        $response = wp_safe_remote_post(esc_url_raw(AFFILIO_SYNC_ORDER_API), $params);
        if (is_wp_error($response)) {
            return $response;
        } elseif (empty($response['body'])) {
            return new WP_Error('AFFILIO-api', 'Empty Response');
        }
        parse_str($response['body'], $response_);
    }
}
