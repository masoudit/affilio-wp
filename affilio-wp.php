<?php

/**
 * Plugin Name: Affilo Integration Wordpress
 * Plugin URI: https://www.Affilo.ir/
 * Description: Affilo Integration Wordpress PLugin.
 * Version: 1.0
 * Author: MASOUD
 * Author URI: http://Affilo.ir/
 **/
if (!defined('ABSPATH')) {
    exit;
}
require __DIR__ . '/config.php';
require __DIR__ . '/client.php';
require __DIR__ . '/order.php';
define('AFI_LAST_ORDER', 'AFI_LAST_ORDER' );

/**
 * 
 * ACTIONS AFFILIO PLUGIN
 * 
 */

function init_set_cookie()
{
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
}


function wp_encrypt($stringToHandle = "", $encryptDecrypt = 'e') {
    return eval(base64_decode("ZGVmaW5lKCdBRklfS0VZJywgJ25mOGdmOGFeMypzJyk7DQoJCWRlZmluZSgnQUZJX0lWJywgJ3MmJjkiZGE0JTpAJyk7DQoJCSRvdXRwdXQgPSBudWxsOw0KCQkvLyBTZXQgc2VjcmV0IGtleXMNCgkJJHNlY3JldF9rZXkgPSBBRklfS0VZOyANCgkJJHNlY3JldF9pdiA9IEFGSV9JVjsNCgkJJGtleSA9IGhhc2goJ3NoYTI1NicsJHNlY3JldF9rZXkpOw0KCQkkaXYgPSBzdWJzdHIoaGFzaCgnc2hhMjU2Jywkc2VjcmV0X2l2KSwwLDE2KTsNCgkJLy8gQ2hlY2sgd2hldGhlciBlbmNyeXB0aW9uIG9yIGRlY3J5cHRpb24NCgkJaWYoJGVuY3J5cHREZWNyeXB0ID09ICdlJyl7DQoJCSAgIC8vIFdlIGFyZSBlbmNyeXB0aW5nDQoJCSAgICRvdXRwdXQgPSBiYXNlNjRfZW5jb2RlKG9wZW5zc2xfZW5jcnlwdCgkc3RyaW5nVG9IYW5kbGUsIkFFUy0yNTYtQ0JDIiwka2V5LDAsJGl2KSk7DQoJCX1lbHNlIGlmKCRlbmNyeXB0RGVjcnlwdCA9PSAnZCcpew0KCQkgICAvLyBXZSBhcmUgZGVjcnlwdGluZw0KCQkgICAkb3V0cHV0ID0gb3BlbnNzbF9kZWNyeXB0KGJhc2U2NF9kZWNvZGUoJHN0cmluZ1RvSGFuZGxlKSwiQUVTLTI1Ni1DQkMiLCRrZXksMCwkaXYpOw0KCQl9DQoJCS8vIFJldHVybiB0aGUgZmluYWwgdmFsdWUNCgkJcmV0dXJuICRvdXRwdXQ7"));
}

add_action( 'wp', 'custom_cron_job' );
function custom_cron_job() {
   if ( ! wp_next_scheduled( 'send_email_two_hours' ) ) {
      wp_schedule_event( time(), 'hoursly', 'auth_login' );
    //   wp_schedule_event( time(), 'minutes' ,'auth_login' );
   }
}

add_action('auth_login', 'auth_login_');
function auth_login_() {
    $resultLogin = new MainClass();
    $affilio_options = get_option( 'affilio_option_name' );
    $username = $affilio_options['username']; // username
    $password = $affilio_options['password']; // password
    $resultLogin->auth_login($username, wp_encrypt($password, 'd'));
}

function set_option ($name, $value) { 
    $isAdded = add_option($name, $value);
    if(!$isAdded){
        $isAdded = update_option($name, $value);
    }
}

if ( is_admin() ){
    // add_action('wp_head', 'add_script_style');
    add_script_style();
}