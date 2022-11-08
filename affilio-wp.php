<?php

/**
 * Plugin Name: Affilio Integration
 * Plugin URI: https://www.Affilio.ir/
 * Description: Affilio Integration Wordpress/Woocommerce PLugin.
 * Version: 1.0.0
 * Author: Masoud
 * Author URI: https://github.com/masoudit
 **/
if (!defined('ABSPATH')) {
    exit;
}
require __DIR__ . '/utils.php';
require __DIR__ . '/config.php';
// Check if WooCommerce is active
if (!affilio_is_plugin_active('woocommerce.php')) {
    affilio_admin_notice('error', 'جهت نصب افزونه افیلیو، پلاگین ووکامرس با حداقل ورژن ' . AFFILIO_MIN_WOOCOMMERCE_VERSION . ' باید نصب شده باشد!');
    return;
}

require __DIR__ . '/client.php';
require __DIR__ . '/order.php';
require __DIR__ . '/cron.php';

if (!defined('AFFILIO_LAST_ORDER')) {
    define('AFFILIO_LAST_ORDER', 'AFFILIO_LAST_ORDER');
}

/**
 * 
 * SET COOKIE
 * 
 */

function affilio_init_set_cookie()
{
    $utm_source = isset($_GET['utm_source']) && sanitize_text_field(['utm_source']);
    $utm_medium = isset($_GET['utm_medium']) && sanitize_text_field($_GET['utm_medium']);
    $utm_campaign = isset($_GET['utm_campaign']) && sanitize_text_field($_GET['utm_campaign']);
    $utm_content = isset($_GET['utm_content']) && sanitize_text_field($_GET['utm_content']);
    $aff_id = isset($_GET['affid']) && sanitize_text_field($_GET['affid']);
    $exp = isset($_GET['exp']) && sanitize_text_field($_GET['exp']);
    // echo $ppc;
    if (
        $utm_source &&
        $utm_medium && $utm_medium === "affilio" &&
        $utm_campaign &&
        $utm_content &&
        $aff_id &&
        $exp
    ) {
        try {
            $expTime = time() + (86400 * $exp);
            setcookie("AFF_ID", $aff_id, $expTime);
        } catch (Exception $e) {
            // var_dump($e);
        }
    }
}
add_action('init', 'affilio_init_set_cookie');


function affilio_add_script_style()
{
    // wp_enqueue_script('afi-js-file', plugin_dir_url(__FILE__) . '/script.js', '', time());
    // var_dump(plugin_dir_url(__FILE__) . 'assets/style.css');
    // var_dump(plugin_dir_url(__FILE__) . 'assets/style.css');
    // var_dump(plugin_dir(__FILE__) . 'assets/style.css');
    // wp_enqueue_style('afi-css-file', plugin_dir_url(__FILE__) . '/assets/style.css', array(), time(), 'all');
    // wp_enqueue_style( 'my-style', plugins_url( '/css/my-style.css', __FILE__ ), false, '1.0', 'all' ); // Inside a plugin
}
if (is_admin()) {
    // add_action('wp_head', 'add_script_style');
    affilio_add_script_style();
}
