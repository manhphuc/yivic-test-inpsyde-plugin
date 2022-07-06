<?php

namespace YivicTestInpsyde\Wp\Plugin\Tests\Unit;

use Brain\Monkey;
use Codeception\Stub;
use Illuminate\Contracts\Container\BindingResolutionException;
use YivicTestInpsyde\Wp\Plugin\Services\PageRendererService;
use YivicTestInpsyde\Wp\Plugin\Services\ViewService;
use YivicTestInpsyde\Wp\Plugin\YivicTestInpsyde;
use YivicTestInpsyde\Wp\Plugin\YivicTestInpsyde as Testee;
use YivicTestInpsyde\Wp\Plugin\Tests\UnitTestCase;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Filters\expectAdded as expectFilterAdded;
use YivicTestInpsyde\Wp\Plugin\Tests\UnitTester;
use YivicTestInpsyde\Wp\Plugin\Services\UserRemoteJsonService;


class TestInpsydeTest extends UnitTestCase {
    /**
     * @var UnitTester
     */
    protected $tester;

    protected $config;

    protected function _before()
    {
        Monkey\setUp();
        $this->config = [
            'basePath'  => '/wp-content/plugins/yivic-test-inpsyde-plugin',
            'baseUrl'   => 'http://yivic-test-inpsyde-plugin.docker/wp-content/plugins/yivic-test-inpsyde-plugin',
            'services'  => [
                'A non-existing service',
                Stub::class,
            ],
            ViewService::class => [
            ],
            PageRendererService::class => [
                'textDomain' => 'qwerty',
            ],
            UserRemoteJsonService::class => [
                'baseUri'   => 'https://jsonplaceholder.typicode.com',
                'timeout'   => 7.7,
                'debug'     => false,
            ],
        ];
    }

    protected function _after() {
        Monkey\tearDown();
    }

    /**
     * Test Constructor and service registration
     *
     * @throws BindingResolutionException
     * @throws \Exception
     */
    public function testInstanceWithConfig() {
        $testeeInstance = $this->construct(Testee::class,
            [
                'config' => $this->config,
            ],
            [
            ]
        );

        /** @var Testee $testeeInstance */
        // Test the property `basePath`
        $this->assertEquals( '/wp-content/plugins/yivic-test-inpsyde-plugin',
            $testeeInstance->basePath );

        // Test a correct service to me initialized
        $tmpService = $testeeInstance->getService( ViewService::class );
        $this->assertInstanceOf( ViewService::class, $tmpService );

        // Test a non-existing service, an exception thrown
        $this->expectException( BindingResolutionException::class );
        $tmpService = $testeeInstance->getService( 'A non-existing service' );

        // Test a global instnce created
        YivicTestInpsyde::initInstanceWithConfig( $this->config );
        $this->assertInstanceOf( Testee::class, Testee::getInstance() );

        // Test global instance properties not changed after created
        $config['basePath'] = 'Another one';
        YivicTestInpsyde::initInstanceWithConfig( $this->config );
        $this->assertEquals( 'http://yivic-test-inpsyde-plugin.docker/wp-content/plugins/yivic-test-inpsyde-plugin',
            Testee::getInstance()->basePath );
    }

    // Test Init Plugin
    public function testInitPlugin() {
        $testeeInstance = $this->make( Testee::class,
            [
                'loadTextDomain' => function () {

                },
            ]
         );

        // Test action init with method `addCustomRewriteRules` added
        expect( 'add_action' )->atLeast()->once()->with( 'init', [ $testeeInstance, 'addCustomRewriteRules' ] );
        expect( 'add_action' )->atLeast()->once()->with( 'wp_loaded', [ $testeeInstance, 'renderCustomInpsydeResponse' ],
            77 );
        $testeeInstance->initPlugin();

    }

    /**
     * Test rewrite rules
     *
     * @throws \Exception
     */
    public function testAddCustomRewriteRules() {
        $testeeInstance = $this->make( Testee::class,
            [
            ]
         );
        /** @var Testee $testeeInstance */

        // We expect rewrite rules must be added in this method
        expect( 'add_rewrite_rule' )->atLeast()->once();
        $testeeInstance->addCustomRewriteRules();
    }

    /**
     * @throws \Exception
     */
    public function testActivateDeactivatePlugin() {
        $testeeInstance = $this->make( Testee::class,
            [
                'addCustomRewriteRules' => function () {

                },
            ]
         );

        /** @var Testee $testeeInstance */
        // We expect `flush_rewrite_rules` called when plugin activated
        expect( 'flush_rewrite_rules' )->atLeast()->once();
        $testeeInstance->activatePlugin();

        // We expect to use `delete_option` to flush `rewrite_rules`
        expect( 'delete_option' )->atLeast()->once()->with( 'rewrite_rules' );
        $testeeInstance->deactivatePlugin();
    }

    public function testRenderCustomInpsydeResponse() {
        YivicTestInpsyde::initInstanceWithConfig( $this->config );

        /** @var UserRemoteJsonService $userRemoteJsonService */
        $userRemoteJsonService = $this->make( UserRemoteJsonService::class,
            [
                'baseUri'   => 'https://jsonplaceholder.typicode.net',
                'timeout'   => 7.7,
                'debug'     => false,
            ]
         );
        $userRemoteJsonService->init();

        /** @var PageRendererService $pageRendererService */
        $pageRendererService = YivicTestInpsyde::getInstance()->getService( PageRendererService::class );

        /** @var ViewService $viewService */
        $viewService = YivicTestInpsyde::getInstance()->getService( ViewService::class );

        // We only expect Exception for view file not found, not exception on getting remote data
        expect( 'locate_template' );
        expect( 'get_transient' );
        expect( '__' );
        $this->expectExceptionMessageMatches('/(.*)(View file not working)(.*)/i');
        YivicTestInpsyde::getInstance()->renderListUsersResponse( $userRemoteJsonService, $viewService, $pageRendererService );

        expect('locate_template');
        expect('get_transient');
        expect('__');
        $this->expectExceptionMessageMatches('/(.*)(View file not working)(.*)/i');
        YivicTestInpsyde::getInstance()->renderSingleUserResponse( 1, $userRemoteJsonService, $viewService,
            $pageRendererService );
    }
}
