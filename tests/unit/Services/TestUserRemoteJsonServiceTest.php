<?php namespace YivicTestInpsyde\Wp\Plugin\Tests\Unit\Services;

use Brain\Monkey;
use Codeception\Stub;
use Exception;
use GuzzleHttp\Client;
use YivicTestInpsyde\Wp\Plugin\Tests\UnitTestCase;
use YivicTestInpsyde\Wp\Plugin\Services\UserRemoteJsonService;
use YivicTestInpsyde\Wp\Plugin\Services\UserRemoteJsonService as Testee;
use function Brain\Monkey\Functions\expect;

class TestUserRemoteJsonServiceTest extends UnitTestCase {
    /**
     * @var \YivicTestInpsyde\Wp\Plugin\Tests\UnitTester
     */
    protected $tester;

    protected $config;

    protected function _before() {
        Monkey\setUp();
        $this->config   = [
            'baseUri'   => 'https://jsonplaceholder.typicode.com',
            'timeout'   => 7.7,
            'debug'     => false,
        ];
    }

    protected function _after() {
        Monkey\tearDown();
    }

    /**
     * Test httpClient must be initialized after init function
     *
     * @throws \ReflectionException
     */
    public function testInit() {
        $testeeInstance = $this->make( Testee::class,
            array_merge( $this->config, [
                'httpClient' => null,
            ] )
         );

        /** @var Testee $testeeInstance */
        $testeeInstance->init();

        $this->assertInstanceOf(Client::class, $this->accessProtected( $testeeInstance, 'httpClient' ) );
    }

    /**
     * Test that remote data retrieved
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testData() {
        $testeeInstance = $this->make(Testee::class,
            $this->config
        );

        /** @var Testee $testeeInstance */
        $testeeInstance->init();

        expect( 'get_transient' );
        expect( 'set_transient' );
        $list = $testeeInstance->getList();
        $this->assertIsArray( $list );
        $this->assertNotEmpty( $list[0] );
        $this->assertArrayHasKey( 'id', $list[0] );

        expect( 'get_transient' );
        expect( 'set_transient' );
        $single = $testeeInstance->getSingle( $list[0]['id'] );
        $this->assertIsArray( $single );
        $this->assertArrayHasKey( 'name', $single );
        $this->assertArrayHasKey( 'phone', $single );

        // Expect an exception thrown because 404
        expect( 'get_transient' );
        $this->expectException( \GuzzleHttp\Exception\GuzzleException::class );
        $single = $testeeInstance->getSingle( 'qwerty' );
    }

    /**
     * Test when remote server failed to response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testFailedRemoteDataServer() {
        $testeeInstance = $this->make(Testee::class,
            array_merge( $this->config, [
                'baseUri' => 'https://jsonplaceholder.typicode.net',
            ] )
        );

        /** @var Testee $testeeInstance */
        $testeeInstance->init();

        expect( 'get_transient' );
        $this->expectException( \GuzzleHttp\Exception\GuzzleException::class );
        $list = $testeeInstance->getList();

        expect( 'get_transient' );
        $this->expectException( \GuzzleHttp\Exception\GuzzleException::class );
        $list = $testeeInstance->getSingle( 1 );
    }
}
