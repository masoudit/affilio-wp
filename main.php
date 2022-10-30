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
        $afi_sent_cats_name = 'afi_sent_cats';
        $last_sent_cat_id = get_option($afi_sent_cats_name);
        $categories = get_categories(
            array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'orderby'    => 'id',
                'order'      => 'DESC',
            )
        );
        $max_cat = reset($categories);
        if($max_cat->term_id == $last_sent_cat_id ){
            // return true;
            affilio_log_me($last_sent_cat_id);
        }

        $body = [];
        foreach ($categories as $cat) {
            $val = $this->get_category_object($cat);
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
            affilio_set_option($afi_sent_cats_name, $max_cat->term_id);
            return true;
        }
    }

    function init_products()
    {
        // echo 'init_products Fired on the WordPress initialization';
        $afi_sent_products = 'afi_sent_products';
        $last_sent_product_id = get_option($afi_sent_products);

        $args = array(
            'post_type'      => 'product',
            'posts_per_page'   => -1,
            // 'product_cat'    => 'hoodies'
        );

        $loop = new WP_Query($args);
        $body = [];
        foreach ($loop->posts as $post) :
            $val = $this->get_post_object($post);
            array_push($body, $val);
        endforeach;

        $max_post = reset($loop->posts);
        // affilio_log_me($loop->posts);
        if($max_post->ID == $last_sent_product_id ){
            // return true;
            affilio_log_me($last_sent_product_id);
        }
        affilio_log_me($max_post->ID);

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
            affilio_set_option($afi_sent_products, $max_post->ID);
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

    private function get_post_object($post)
    {
        $product = wc_get_product($post->ID);
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
        $cats = $product->get_category_ids();
        $cat = end($cats);
        $val = array(
            'id' => $product->get_id(),
            'title' => $product->get_name(),
            'web_store_id' => AFFILIO_WEB_STORE_ID,
            'category_id' => $cat,
            'landing' => "",
            'description' => $product->get_description(),
            'image' => is_array($image) ? reset($image) : null,
            'alt_image' => "",
            'discount' => $product->get_regular_price() ? $product->get_regular_price() - $product->get_price() : null,
            'price' => $product->get_price(),
            'code' => $product->get_sku(),
            'is_incredible' => "",
            'is_promotion' => "",
            'is_available' => $product->get_status() === 'publish',
            'product_score' => "",
            'price_tag' => $product->get_tag_ids(),
        );
        // affilio_log_me($val);
        return $val;
    }

    private function get_category_object($cat)
    {
        if ($cat->term_id) {
            $val = array(
                'web_store_id' => AFFILIO_WEB_STORE_ID,
                'title' => $cat->name,
                'category_id' => $cat->term_id,
                'parent_category_id' => $cat->parent,
                'is_active' => true,
                'is_deleted' => false,
            );

            return $val;
        } else {
            $val = array(
                'web_store_id' => AFFILIO_WEB_STORE_ID,
                'title' => $cat->cat_name,
                'category_id' => $cat->cat_ID,
                'parent_category_id' => $cat->category_parent,
                'is_active' => true,
                'is_deleted' => false,
            );

            return $val;
        }
    }

    function sync_new_product($productId)
    {
        $product = wc_get_product($productId);
        $body = [];
        $val = $this->get_post_object($product);
        array_push($body, $val);

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

    function sync_new_category($catId)
    {
        $cat = get_term($catId);
        $body = [];
        $val = $this->get_category_object($cat);
        array_push($body, $val);

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
}
