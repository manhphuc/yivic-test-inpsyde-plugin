<?php
/**
 * Bootstrap file
 *
 * Bootstrap file of the plugin
 *
 * @package yivic-test-ipsyde-plugin
 *
 * Plugin Name: Yivic Test for Inpsyde
 * Description: A test plugin for getting remote json users
 * Author:      manhphucofficial@yahoo.com
 * Text Domain: yivic_inpsyde
 */

use YivicTestInpsyde\Wp\Plugin\YivicTestInpsyde;

defined( 'YIVIC_TEST_INPSYDE_VERSION' ) || define( 'YIVIC_TEST_INPSYDE_VERSION', '1.0.0' );

// Use autoload if it isn't loaded before.
if ( !class_exists( YivicTestInpsyde::class ) ) {
    require_once __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
}

$config = require( __DIR__ . DIRECTORY_SEPARATOR . 'config.php' );
$config = array_merge( $config, [
    'pluginFilename' => __FILE__,
] );

/** @noinspection PhpUnhandledExceptionInspection */
YivicTestInpsyde::initInstanceWithConfig( $config );

register_activation_hook( __FILE__, [ YivicTestInpsyde::getInstance(), 'activatePlugin' ] );
register_deactivation_hook( __FILE__, [ YivicTestInpsyde::getInstance(), 'deactivatePlugin' ] );

// We need to set up the main instance for the plugin.
// Use 'init' event but with low (<10) processing order to be able to execute before -> able to add other init.
add_action( 'init', [ YivicTestInpsyde::getInstance(), 'initPlugin' ], 7 );
