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
if (!defined('AFFILIO_LAST_ORDER')) {
    define('AFFILIO_LAST_ORDER', 'AFFILIO_LAST_ORDER');
}

/**
 * 
 * ACTIONS AFFILIO PLUGIN
 * 
 */

function init_set_cookie()
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
    $output = null;
    // Set secret keys
    $secret_key = 'nf8gf8a^3*s'; 
    $secret_iv = 's&&9"da4%:@';
    $key = hash('sha256',$secret_key);
    $iv = substr(hash('sha256',$secret_iv),0,16);
    // Check whether encryption or decryption
    if($encryptDecrypt == 'e'){
        // We are encrypting
        $output = base64_encode(openssl_encrypt($stringToHandle,"AES-256-CBC",$key,0,$iv));
    }else if($encryptDecrypt == 'd'){
        // We are decrypting
        $output = openssl_decrypt(base64_decode($stringToHandle),"AES-256-CBC",$key,0,$iv);
    }
    // Return the final value
    return $output;
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
    $resultLogin = new Affilio_Main();
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
