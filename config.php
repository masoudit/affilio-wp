<?php 
// $BASE_API = "https://blackhole.affilio.ir/api/v1";
$BASE_API = "https://blackhole-stage.affilio.ir/api/v1";
define( 'AFFILIO_BASE_API', $BASE_API );
define( 'AFFILIO_AUTH_LOGIN', AFFILIO_BASE_API . "/Auth/Login" );
define( 'AFFILIO_SYNC_ORDER_API', AFFILIO_BASE_API . "/sync/order/list" );
define( 'AFFILIO_SYNC_PRODUCT_API', AFFILIO_BASE_API . "/sync/product/list");
define( 'AFFILIO_SYNC_CATEGORY_API', AFFILIO_BASE_API . "/sync/category/list");
define( 'AFFILIO_SYNC_ORDER_UPDATE_API', AFFILIO_BASE_API . "/sync/orderitem/update");
define( 'AFFILIO_SYNC_ORDER_CANCEL_API', AFFILIO_BASE_API . "/sync/order/cancel");
define( 'AFFILIO_SYNC_NEW_CUSTOMER_API', AFFILIO_BASE_API . "/sync/newCustomer/insert");