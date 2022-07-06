<?php

namespace YivicTestInpsyde\Wp\Plugin\Services;

use GuzzleHttp\Client;
use YivicTestInpsyde\Wp\Plugin\Traits\ConfigTrait;
use YivicTestInpsyde\Wp\Plugin\Traits\ServiceTrait;
use YivicTestInpsyde\Wp\Plugin\Traits\WPAttributeTrait;

class UserRemoteJsonService {
    use ConfigTrait;
    use WPAttributeTrait;
    use ServiceTrait;

    public $baseUri;
    public $timeout;
    public $debug;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @inheritDoc
     */
    public function init() {
        $this->httpClient = new Client( [
            // Base URI is used with relative requests
            'base_uri'  => $this->baseUri,
            'timeout'   => $this->timeout,
            'debug'     => $this->debug,
        ] );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get list of users
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getList(): mixed {
        return $this->getResponse( 'GET', '/users' );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get details of a single user
     *
     * @param $id
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSingle( $id ): mixed {
        return $this->getResponse( 'GET', sprintf( '/users/%s', $id ) );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get remote response with 120 seconds cache
     *
     * @param $method
     * @param string $uri
     * @param array $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResponse( $method, $uri = '', $options = [] ): mixed {
        $cacheKey = md5( json_encode( [
            'caller'    => 'getResponse',
            'baseUri'   => $this->baseUri,
            'method'    => $method,
            'uri'       => $uri,
            'options'   => $options,
        ] ) );
        $result = get_transient( $cacheKey );
        if ( empty( $result ) ) {
            $response   = $this->httpClient->request( $method, $uri, $options );
            $result     = null;
            if ( ( 200 === $response->getStatusCode() ) ) {
                $result = json_decode( $response->getBody()->getContents(), true );
                set_transient( $cacheKey, $result, 120 );
            }
        }

        return $result;
    }
}
