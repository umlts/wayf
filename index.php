<?php

/**
 * Get cookie id from the return parameter
 */
function get_cookie_id( $return ) {

    if ( !preg_match( '/target=cookie:([0-9]+_([a-z0-9]+))/i', $return, $result ) ) {
        return false;
    }
    return $result[1];
}

/**
 * Replace token in string
 */
function token_replace( $key, $value, $string ) {

    $string = str_replace( '[[' . $key . ']]', $value, $string );
    $string = str_replace( '[[' . $key . '|urlencode]]', urlencode( $value ), $string );

    if ( function_exists( 'filter_var' ) ) {
        $string = str_replace( '[[' . $key . '|html]]', filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS ), $string );
    } else {
        $string = str_replace( '[[' . $key . '|html]]', htmlentities( $value, ENT_QUOTES ), $string );
    }

    return $string;
}

// Configuration

$config = array(

    'sso' => array(

        'url' => 'https://[[host]]/Shibboleth.sso/Login?SAMLDS=1&entityID=https://shib-idp.umsystem.edu/idp/shibboleth&target=cookie:[[cookie_id|urlencode]]'

    ),

    'merlin' => array(

        'host_replace' => array(

            array(
                'host' => 'merlin.lib.umsystem.edu',
                'replacement' => 'merlin.mobius.umsystem.edu',
            ),
            array(
                'host' => 'encore.lib.umsystem.edu',
                'replacement' => 'encore.mobius.umsystem.edu',
            ),
            array(
                'host' => 'mobius.lib.umsystem.edu',
                'replacement' => 'classic.searchmobius.org',
            ),
            array(
                'host' => 'mobius-encore.lib.umsystem.edu',
                'replacement' => 'encore.searchmobius.org',
            ),
            array(
                'host' => 'prospector-classic.lib.umsystem.edu',
                'replacement' => 'prospector.coalliance.org',
            ),
            array(
                'host' => 'prospector.lib.umsystem.edu',
                'replacement' => 'encore.coalliance.org',
            ),

        ),

    ),
);


// Get parameters

$entity = $_GET['entityID'];
$return = urldecode( $_GET['return'] );
$cookie_id = get_cookie_id( $return );
$url = $_COOKIE[ '_shibstate_' . $cookie_id ];


// Create URLs

$url_sso = token_replace( 'cookie_id', $cookie_id, $config['sso']['url'] );

foreach ( $config['merlin']['host_replace'] as $replace ) {
    if ( strpos( $url, $replace['host'] )!== FALSE ) {
        $url_merlin = str_replace( $replace['host'], $replace['replacement'], $url );
        $url_sso = token_replace( 'host', $replace['host'], $url_sso );
        break;
    }
}


// Rough check if everything needed is set

if ( empty( $entity ) ) { die( 'Error: No entity id given.' ); }
if ( empty( $url ) ) { die( 'Error: No URL found.' ); }

if ( empty( $url_merlin ) ) { die( 'Error: No Merlin login url.' ); }
if ( empty( $url_sso ) ) { die( 'Error: No SSO login url.' ); }


// Get template, replace placeholders and print it out

$template = file_get_contents( 'template.html' );
$template = token_replace( 'url_sso', $url_sso, $template );
$template = token_replace( 'url_merlin', $url_merlin, $template );

echo $template;
