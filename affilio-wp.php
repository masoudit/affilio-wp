<?php

/**
 * Plugin Name: Affilo Integration Wordpress
 * Plugin URI: https://www.Affilo.ir/
 * Description: Affilo Integration Wordpress PLugin.
 * Version: 1.0
 * Author: DINGO
 * Author URI: http://Affilo.ir/
 * shift + option + F
 **/
if (!defined('ABSPATH')) {
    exit;
}
require __DIR__ . '/config.php';
require __DIR__ . '/client.php';
require __DIR__ . '/order.php';
// define('WEB_STORE_ID', 5175616687319568587);
define('AFI_LAST_ORDER', 'AFI_LAST_ORDER' );
define('AFF_ID', "NzdhZGFiZTAtZGQxMC00YTA2LTgzNDItNTY0NzM0YTFkOThk");

/**
 * 
 * ACTIONS AFFILIO PLUGIN
 * 
 */

// add_action('user_register', 'call_after_new_customer_insert');
// function call_after_new_customer_insert($user_id)
// {
//     var_dump('cccccc');
//     log_me('This is a message 2222 for debugging purposes user_id');
// }
function init_set_cookie()
{
    // https://salam.com/?utm_source=https%253A%252F%252Ftakhfifan.com&utm_medium=Affilio&utm_campaign=ohgv&utm_content=&affid=NzdhZGFiZTAtZGQxMC00YTA2LTgzNDItNTY0NzM0YTFkOThk&exp=2
    // $b64 = 'aHR0cHM6Ly93d3cuZGlnaWthbGEuY29tL3NlYXJjaC9jYXRlZ29yeS1ncmFwaGljcy1jYXJkLz9wcmljZVttaW5dPTEwMDAwMDAwMCZwcmljZVttYXhdPTE4MDAwMDAwMA==';
    // $extraParam = 'ehdiw';
    // $utms = 'graphiccard';
    // $publicAffId = '55854ffc-acb3-4c3a-a81f-c0100365671f'; // b
    // $privateAffId = '55854ffc-acb3-4c3a-a81f-c0100365671f';
    // // http://migmig.affilio.ir/api/v1/Click/b/55854ffc-acb3-4c3a-a81f-c0100365671f?b64=XXXX&extraParam=ehdiw&utms=graphiccard
    // var_dump($_GET);
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
    // wp_enqueue_style('x'); // Inside a plugin
}


function wp_encrypt($stringToHandle = "", $encryptDecrypt = 'e') {
    return eval(base64_decode("ZGVmaW5lKCdBRklfS0VZJywgJ25mOGdmOGFeMypzJyk7DQoJCWRlZmluZSgnQUZJX0lWJywgJ3MmJjkiZGE0JTpAJyk7DQoJCSRvdXRwdXQgPSBudWxsOw0KCQkvLyBTZXQgc2VjcmV0IGtleXMNCgkJJHNlY3JldF9rZXkgPSBBRklfS0VZOyANCgkJJHNlY3JldF9pdiA9IEFGSV9JVjsNCgkJJGtleSA9IGhhc2goJ3NoYTI1NicsJHNlY3JldF9rZXkpOw0KCQkkaXYgPSBzdWJzdHIoaGFzaCgnc2hhMjU2Jywkc2VjcmV0X2l2KSwwLDE2KTsNCgkJLy8gQ2hlY2sgd2hldGhlciBlbmNyeXB0aW9uIG9yIGRlY3J5cHRpb24NCgkJaWYoJGVuY3J5cHREZWNyeXB0ID09ICdlJyl7DQoJCSAgIC8vIFdlIGFyZSBlbmNyeXB0aW5nDQoJCSAgICRvdXRwdXQgPSBiYXNlNjRfZW5jb2RlKG9wZW5zc2xfZW5jcnlwdCgkc3RyaW5nVG9IYW5kbGUsIkFFUy0yNTYtQ0JDIiwka2V5LDAsJGl2KSk7DQoJCX1lbHNlIGlmKCRlbmNyeXB0RGVjcnlwdCA9PSAnZCcpew0KCQkgICAvLyBXZSBhcmUgZGVjcnlwdGluZw0KCQkgICAkb3V0cHV0ID0gb3BlbnNzbF9kZWNyeXB0KGJhc2U2NF9kZWNvZGUoJHN0cmluZ1RvSGFuZGxlKSwiQUVTLTI1Ni1DQkMiLCRrZXksMCwkaXYpOw0KCQl9DQoJCS8vIFJldHVybiB0aGUgZmluYWwgdmFsdWUNCgkJcmV0dXJuICRvdXRwdXQ7"));
}

// add_filter( 'cron_schedules', 'isa_add_every_three_minutes' );
// function isa_add_every_three_minutes( $schedules ) {
//     $schedules['every_three_minutes'] = array(
//             'interval'  => 10,
//             'display'   => __( 'Every 3 Minutes', 'textdomain' )
//     );
//     return $schedules;
// }

// add_filter( 'cron_schedules', 'wpshout_add_cron_interval' );
// function wpshout_add_cron_interval( $schedules ) {
//     $schedules['everyminute'] = array(
//             'interval'  => 5, // time in seconds
//             'display'   => 'Every Minute'
//     );
//     return $schedules;
// }


// Schedule an action if it's not already scheduled
// if ( ! wp_next_scheduled( 'wpshout_add_cron_interval' ) ) {
//     // wp_schedule_event( time(), 'every_three_minutes', 'isa_add_every_three_minutes' );
//     wp_schedule_event( time(), 'minutes', 'wpshout_do_thing' );
// }

// Hook into that action that'll fire every three minutes
// add_action( 'wpshout_do_thing', 'every_three_minutes_event_func' );
// function every_three_minutes_event_func() {
//     // do something
//     auth_login();
// }

add_action( 'wp', 'custom_cron_job' );
function custom_cron_job() {
   if ( ! wp_next_scheduled( 'send_email_two_hours' ) ) {
      wp_schedule_event( time(), 'hoursly', 'auth_login' );
    //   wp_schedule_event( time(), 'minutes' ,'auth_login' );
   }
}

// log_me('cron-------');
add_action('auth_login', 'auth_login_');
function auth_login_() {
    log_me('cron-------');
    //here you can build logic and email to all users	
    //send email to adminhttps://assets.grammarly.com/emoji/v1/1f610.svg
    // if(is_admin()){
    $resultLogin = new MainClass();
    $affilio_options = get_option( 'affilio_option_name' );
    $username = $affilio_options['username']; // username
    // log_me('cron-------'. $username);

    $password = $affilio_options['password']; // password
    // var_dump($username);
    $resultLogin->auth_login($username, wp_encrypt($password, 'd'));
    // }
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