<?php

/**
 * Plugin Name: Affilio Integration
 * Plugin URI: https://www.Affilio.ir/
 * Description: Affilio Integration Wordpress/Woocommerce PLugin.
 * Version: 1.0
 * Author: Masoud
 * Author URI: https://github.com/masoudit
 **/
if (!defined('ABSPATH')) {
    exit;
}
require __DIR__ . '/config.php';
require __DIR__ . '/client.php';
require __DIR__ . '/order.php';
if (!defined('AFI_LAST_ORDER')) {
    define('AFI_LAST_ORDER', 'AFI_LAST_ORDER');
}

/**
 * 
 * ACTIONS AFFILIO PLUGIN
 * 
 */

function init_set_cookie()
{
    $utm_source = isset($_GET['utm_source']) && $_GET['utm_source'];
    $utm_medium = isset($_GET['utm_medium']) && $_GET['utm_medium'];
    $utm_campaign = isset($_GET['utm_campaign']) && $_GET['utm_campaign'];
    $utm_content = isset($_GET['utm_content']) && $_GET['utm_content'];
    $aff_id = isset($_GET['affid']) && $_GET['affid'];
    $exp = isset($_GET['exp']) && $_GET['exp'];
    // echo $ppc;
    if (
        isset($utm_source ) &&
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

function add_script_style()
{
    // wp_enqueue_script('afi-js-file', plugin_dir_url(__FILE__) . '/script.js', '', time());
    // var_dump(plugin_dir_url(__FILE__) . 'assets/style.css');
    // var_dump(plugin_dir_url(__FILE__) . 'assets/style.css');
    // var_dump(plugin_dir(__FILE__) . 'assets/style.css');
    // wp_enqueue_style('afi-css-file', plugin_dir_url(__FILE__) . '/assets/style.css', array(), time(), 'all');
    // wp_enqueue_style( 'my-style', plugins_url( '/css/my-style.css', __FILE__ ), false, '1.0', 'all' ); // Inside a plugin
}


function wp_encrypt($stringToHandle = "", $encryptDecrypt = 'e')
{
    return eval(base64_decode("JG91dHB1dCA9IG51bGw7DQogICAgLy8gU2V0IHNlY3JldCBrZXlzDQogICAgJHNlY3JldF9rZXkgPSAnbmY4Z2Y4YV4zKnMnOyANCiAgICAkc2VjcmV0X2l2ID0gJ3MmJjkiZGE0JTpAJzsNCiAgICAka2V5ID0gaGFzaCgnc2hhMjU2Jywkc2VjcmV0X2tleSk7DQogICAgJGl2ID0gc3Vic3RyKGhhc2goJ3NoYTI1NicsJHNlY3JldF9pdiksMCwxNik7DQogICAgLy8gQ2hlY2sgd2hldGhlciBlbmNyeXB0aW9uIG9yIGRlY3J5cHRpb24NCiAgICBpZigkZW5jcnlwdERlY3J5cHQgPT0gJ2UnKXsNCiAgICAgICAgLy8gV2UgYXJlIGVuY3J5cHRpbmcNCiAgICAgICAgJG91dHB1dCA9IGJhc2U2NF9lbmNvZGUob3BlbnNzbF9lbmNyeXB0KCRzdHJpbmdUb0hhbmRsZSwiQUVTLTI1Ni1DQkMiLCRrZXksMCwkaXYpKTsNCiAgICB9ZWxzZSBpZigkZW5jcnlwdERlY3J5cHQgPT0gJ2QnKXsNCiAgICAgICAgLy8gV2UgYXJlIGRlY3J5cHRpbmcNCiAgICAgICAgJG91dHB1dCA9IG9wZW5zc2xfZGVjcnlwdChiYXNlNjRfZGVjb2RlKCRzdHJpbmdUb0hhbmRsZSksIkFFUy0yNTYtQ0JDIiwka2V5LDAsJGl2KTsNCiAgICB9DQogICAgLy8gUmV0dXJuIHRoZSBmaW5hbCB2YWx1ZQ0KICAgIHJldHVybiAkb3V0cHV0Ow=="));
}

add_action('wp', 'custom_cron_job');
function custom_cron_job()
{
    if (!wp_next_scheduled('send_email_two_hours')) {
        wp_schedule_event(time(), 'hoursly', 'auth_login');
        //   wp_schedule_event( time(), 'minutes' ,'auth_login' );
    }
}

add_action('auth_login', 'auth_login_');
function auth_login_()
{
    $resultLogin = new MainClass();
    $affilio_options = get_option('affilio_option_name');
    $username = $affilio_options['username']; // username
    $password = $affilio_options['password']; // password
    $resultLogin->auth_login($username, wp_encrypt($password, 'd'));
}

function set_option($name, $value)
{
    $isAdded = add_option($name, $value);
    if (!$isAdded) {
        $isAdded = update_option($name, $value);
    }
}

if (is_admin()) {
    // add_action('wp_head', 'add_script_style');
    add_script_style();
}
