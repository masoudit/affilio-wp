<?php 
// $BASE_API = "https://blackhole.affilio.ir/api/v1";
$BASE_API = "https://blackhole-stage.affilio.ir/api/v1";
if(!defined('AFFILIO_BASE_API'))
    define( 'AFFILIO_BASE_API', $BASE_API );

if(!defined('AFFILIO_AUTH_LOGIN'))
    define( 'AFFILIO_AUTH_LOGIN', AFFILIO_BASE_API . "/Auth/Login" );

if(!defined('AFFILIO_SYNC_ORDER_API'))
    define( 'AFFILIO_SYNC_ORDER_API', AFFILIO_BASE_API . "/sync/order/list" );

if(!defined('AFFILIO_SYNC_PRODUCT_API'))
    define( 'AFFILIO_SYNC_PRODUCT_API', AFFILIO_BASE_API . "/sync/product/list");

if(!defined('AFFILIO_SYNC_CATEGORY_API'))
    define( 'AFFILIO_SYNC_CATEGORY_API', AFFILIO_BASE_API . "/sync/category/list");

if(!defined('AFFILIO_SYNC_ORDER_UPDATE_API'))
    define( 'AFFILIO_SYNC_ORDER_UPDATE_API', AFFILIO_BASE_API . "/sync/orderitem/update");

if(!defined('AFFILIO_SYNC_ORDER_CANCEL_API'))
    define( 'AFFILIO_SYNC_ORDER_CANCEL_API', AFFILIO_BASE_API . "/sync/order/cancel");

if(!defined('AFFILIO_SYNC_NEW_CUSTOMER_API'))
    define( 'AFFILIO_SYNC_NEW_CUSTOMER_API', AFFILIO_BASE_API . "/sync/newCustomer/insert");
    
if(!defined('AFFILIO_MIN_WOOCOMMERCE_VERSION'))
    define( 'AFFILIO_MIN_WOOCOMMERCE_VERSION', '3.9.4');