<?php

function is_staging( $clients ) {

    $ip = $_SERVER['REMOTE_ADDR'];

    foreach ( $clients as $client ) {
        $client = preg_quote( $client );
        $client = str_replace( '\*', '\d{1,3}', $client );
        if ( preg_match( '/^' . $client . '$/', $ip ) ) {
            return true;
        }
    }
    return false;
}

if ( file_exists( __DIR__ . '/staging.php' ) ) {

    $staging_clients = [
        '127.*.*.*',
        '10.*.*.*',
        '128.206.162.187'
    ];

    if ( is_staging( $staging_clients ) ) {
        echo '<!-- Staging -->';
        include __DIR__ . '/staging.php';
        die( '<!-- /Staging -->' );
    }

}

include __DIR__ . '/live.php';
