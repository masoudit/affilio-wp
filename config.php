<?php 
// $BASE_API = "https://blackhole.affilio.ir/api/v1";
$BASE_API = "https://blackhole-stage.affilio.ir/api/v1";
define( 'BASE_API', $BASE_API );
define( 'AUTH_LOGIN', $BASE_API . "/Auth/Login" );
define( 'SYNC_ORDER_API', $BASE_API . "/sync/order/list" );
define( 'SYNC_PRODUCT_API', $BASE_API . "/sync/product/list");
define( 'SYNC_CATEGORY_API', $BASE_API . "/sync/category/list");
define( 'SYNC_ORDER_UPDATE_API', $BASE_API . "/sync/orderitem/update");
define( 'SYNC_ORDER_CANCEL_API', $BASE_API . "/sync/order/cancel");
define( 'SYNC_NEW_CUSTOMER_API', $BASE_API . "/sync/newCustomer/insert");