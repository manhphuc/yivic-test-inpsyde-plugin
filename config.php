<?php

use YivicTestInpsyde\Wp\Plugin\Services\PageRendererService;
use YivicTestInpsyde\Wp\Plugin\Services\UserRemoteJsonService;
use YivicTestInpsyde\Wp\Plugin\Services\ViewService;

$textDomain = 'yivic_inpsyde';

return $config = [
    'version'               => YIVIC_TEST_INPSYDE_VERSION,
    'basePath'              => __DIR__,
    'baseUrl'               => plugins_url( null, __FILE__ ),
    'textDomain'            => $textDomain,
    'customEndpointName'    => get_option( 'yivic_custom_endpoint_name', 'custom-inpsyde' ),
    'services'              => [
        ViewService::class              => [

        ],
        PageRendererService::class      => [
            'textDomain' => $textDomain,
        ],
        UserRemoteJsonService::class    => [
            'baseUri'   => 'https://jsonplaceholder.typicode.com',
            'timeout'   => 7.7,
            'debug'     => false,
        ],
    ],
];