<?php

/**
 * HELPERS
 */
function affilio_log_me($message)
{
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

function affilio_wp_encrypt($stringToHandle = "", $encryptDecrypt = 'e')
{
    $output = null;
    // Set secret keys
    $secret_key = 'nf8gf8a^3*s';
    $secret_iv = 's&&9"da4%:@';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    // Check whether encryption or decryption
    if ($encryptDecrypt == 'e') {
        // We are encrypting
        $output = base64_encode(openssl_encrypt($stringToHandle, "AES-256-CBC", $key, 0, $iv));
    } else if ($encryptDecrypt == 'd') {
        // We are decrypting
        $output = openssl_decrypt(base64_decode($stringToHandle), "AES-256-CBC", $key, 0, $iv);
    }
    // Return the final value
    return $output;
}

function affilio_set_option($name, $value)
{
    $isAdded = add_option($name, $value);
    if (!$isAdded) {
        $isAdded = update_option($name, $value);
    }
}

function affilio_admin_notice($type = 'success', $msg) {
    if($type !== 'success'){
        $class = 'notice notice-error is-dismissible';
        $message = __( $msg, 'affilio' );
    }else {
        $class = 'notice notice-success is-dismissible';
        $message = __( $msg, 'affilio' );
    }

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}