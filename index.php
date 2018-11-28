<?php

if ( file_exists( __DIR__ . '/staging.php' ) ) {
    include __DIR__ . '/staging.php';
}

include __DIR__ . '/live.php';