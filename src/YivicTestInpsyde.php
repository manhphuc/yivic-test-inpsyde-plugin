<?php

namespace YivicTestInpsyde\Wp\Plugin;

use Exception;
use Illuminate\Container\Container;
use WP;
use YivicTestInpsyde\Wp\Plugin\Interfaces\WPPluginInterface;
use YivicTestInpsyde\Wp\Plugin\Services\PageRendererService;
use YivicTestInpsyde\Wp\Plugin\Services\UserRemoteJsonService;
use YivicTestInpsyde\Wp\Plugin\Services\ViewService;
use YivicTestInpsyde\Wp\Plugin\Traits\ServiceTrait;
use YivicTestInpsyde\Wp\Plugin\Traits\ConfigTrait;
use YivicTestInpsyde\Wp\Plugin\Traits\WPAttributeTrait;

/**
 * Class YivicTestInpsyde
 * @package YivicTestInpsyde\Wp\Plugin
 */
class YivicTestInpsyde extends Container implements WPPluginInterface {
    use ConfigTrait;
    use WPAttributeTrait;

    const
        OPTION_KEY          = 'yivic_custom_endpoint_name',
        OPTIONS_GROUP_NAME  = 'yivic_test_inpsyde_options_group';

    /**
     * @var string Version of this plugin
     */
    public $version;

    /** @noinspection PhpUnusedElementInspection */
    /**
     * @var string Base path to this plugin
     */
    public $basePath;

    /**
     * @var string Base url of the folder of this plugin
     */
    public $baseUrl;

    /**
     * @var string Name of the custom endpoint
     */
    public $customEndpointName;


    /**
     * Ppvp constructor.
     *
     * @param $config
     */
    public function __construct( $config ) {
        $this->bindConfig( $config );

        // phpcs:ignore PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
        if ( !empty( $services = $config['services'] ?? null ) ) {
            $this->registerServices( $services );
        }
    }

    /**
     * Register service providers set in config
     *
     * @param $services
     */
    protected function registerServices( $services ) {
        foreach ( $services as $serviceClassname => $serviceConfig ) {
            $this->bind(
                $serviceClassname,
                function ( $container ) use ( $serviceClassname, $serviceConfig ) {
                    $serviceInstance = new $serviceClassname();

                    if ( in_array( ConfigTrait::class, class_uses( $serviceInstance ), true ) ) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $serviceInstance->bindConfig( $serviceConfig );
                    }

                    if ( in_array( ServiceTrait::class, class_uses( $serviceInstance ), true ) ) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $serviceInstance->setContainer( $container );
                        /** @noinspection PhpUndefinedMethodInspection */
                        $serviceInstance->init();
                    }

                    return $serviceInstance;
                }
            );
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @param $alias
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getService( $alias ): mixed {
        return $this->make( $alias );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get the `view` service
     *
     * @return ViewService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function getServiceView(): ViewService {
        return static::getInstance()->getService( ViewService::class );
    }

    /**
     * @param $config
     *
     * @throws Exception
     */
    public static function initInstanceWithConfig( $config ) {
        if ( is_null( static::$instance ) ) {
            static::setInstance( new static( $config ) );
        }

        if ( !static::getInstance() instanceof static ) {
            throw new Exception( 'No plugin initialized.' );
        }
    }

    /**
     * Get application locale
     *
     * @return string
     */
    public function getLocale(): string {
        return determine_locale();
    }

    /**
     * Load locale file to textDomain
     */
    protected function loadTextDomain() {
        $locale     = $this->getLocale();
        $mo_file    = $locale.'.mo';
        load_textdomain( $this->textDomain, $this->basePath.'/languages/'.$mo_file );
    }

    /**
     * Initialize all needed things for this plugin: hooks, assignments ...
     */
    public function initPlugin(): void {
        // Load Text Domain
        $this->loadTextDomain();

        add_action( 'init', [ $this, 'addCustomRewriteRules' ] );

        add_action('wp_ajax_get_single_user', [ $this, 'renderCustomInpsydeSingleUserResponse' ] );
        add_action('wp_ajax_nopriv_get_single_user', [ $this, 'renderCustomInpsydeSingleUserResponse'] );

        // We use `wp_loaded` hook for allowing widgets to be initialized, 77 for others to be loaded
        add_action('wp_loaded', [$this, 'renderCustomInpsydeResponse'], 77);

        // Hooks for admin
        add_action( 'admin_init', [ $this, 'registerSettingsGroup' ] );
        add_action( 'admin_menu', [ $this, 'registerSettingsPage' ] );

    }

    /**
     * Do some needed things when activate plugin
     */
    public function activatePlugin() {
        // Add rewrite rules
        $this->addCustomRewriteRules();

        // Flush rewrite rules when activate plugins
        flush_rewrite_rules( false );
    }

    /**
     * @noinspection PhpUnusedDeclarationInspection
     */
    public function deactivatePlugin() {
        // The problem with calling flush_rewrite_rules() is that the rules instantly get regenerated, while your plugin's hooks are still active.
        delete_option( 'rewrite_rules' );
    }

    /**
     * Add rewrite rule for Yivic IPN response page
     */
    public function addCustomRewriteRules() {
        add_rewrite_rule( 'custom-inpsyde/?$', 'index.php?pagename='.$this->customEndpointName, 'top' );
    }

    /**
     * Parse request URL to get `pagename` variable
     *
     * @return mixed|null
     */
    public function getRequestPagename(): mixed {
        // We need to parse request here to fetch our needed query_var because this method would be call before main query executed `$pagename = get_query_var( 'pagename' );`
        $the_wp = new WP();
        $the_wp->parse_request();

        return $the_wp->query_vars['pagename'] ?? null;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Handle response for custom endpoint
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function renderCustomInpsydeResponse() {
        $pagename = $this->getRequestPagename();
        if ( $this->customEndpointName === $pagename ) {
            /** @var UserRemoteJsonService $userRemoteJsonService */
            $userRemoteJsonService = $this->getService( UserRemoteJsonService::class );

            /** @var PageRendererService $pageRendererService */
            $pageRendererService = $this->getService( PageRendererService::class );

            /** @var ViewService $viewService */
            $viewService = $this->getService( ViewService::class );

            $this->renderListUsersResponse( $userRemoteJsonService, $viewService, $pageRendererService );
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Handle response for single user request
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function renderCustomInpsydeSingleUserResponse() {
        $userId = filter_input( INPUT_GET, 'id' );
        /** @var UserRemoteJsonService $userRemoteJsonService */
        $userRemoteJsonService = $this->getService( UserRemoteJsonService::class );

        /** @var PageRendererService $pageRendererService */
        $pageRendererService = $this->getService( PageRendererService::class );

        /** @var ViewService $viewService */
        $viewService = $this->getService( ViewService::class );

        $this->renderSingleUserResponse( $userId, $userRemoteJsonService, $viewService, $pageRendererService );

        // Be sure to have ajax call ending here
        wp_die();
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @param UserRemoteJsonService $userRemoteJsonService
     * @param ViewService $viewService
     * @param PageRendererService $pageRendererService
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function renderListUsersResponse(
        UserRemoteJsonService $userRemoteJsonService,
        ViewService $viewService,
        PageRendererService $pageRendererService
     ) {
        $users = $errorMessage = null;
        try {
            $users = $userRemoteJsonService->getList();
        } catch ( Exception $exception ) {
            $errorMessage = WP_DEBUG ? $exception->getMessage() :
                __( 'There is problem with remote data, please try again later!', $this->textDomain );
        }

        $pageRendererService->render( $viewService, 'users', [
            'users'         => $users,
            'textDomain'    => $this->textDomain,
            'errorMessage'  => $errorMessage,
        ] );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @param $userId
     * @param UserRemoteJsonService $userRemoteJsonService
     * @param ViewService $viewService
     * @param PageRendererService $pageRendererService
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function renderSingleUserResponse(
        $userId,
        UserRemoteJsonService $userRemoteJsonService,
        ViewService $viewService,
        PageRendererService $pageRendererService
    ) {

        $errorMessage = null;
        try {
            $user = $userRemoteJsonService->getSingle( $userId );
        } catch ( Exception $exception ) {
            $errorMessage = WP_DEBUG ? $exception->getMessage() :
                __( 'There is problem with remote data, please try again later!', $this->textDomain );
        }

        $pageRendererService->render( $viewService, '_view-user', [
            'user'          => $user,
            'textDomain'    => $this->textDomain,
            'errorMessage'  => $errorMessage,
        ] );
    }

    /**
     * Register a settings group for this plugin
     */
    public function registerSettingsGroup() {
        add_option( static::OPTION_KEY, 'custom-inpsyde' );
        register_setting( static::OPTIONS_GROUP_NAME, static::OPTION_KEY, [ $this, 'validateGroupSettings' ] );
    }

    public function validateGroupSettings( string $dataInput ): string {
        $dataInput = apply_filters( 'inpsyde_custom_url_validation', $dataInput );
        return sanitize_title( $dataInput );
    }

    /**
     * Register the Admin page for plugin settings
     */
    public function registerSettingsPage() {
        add_options_page(
            __( 'Test Inpsyde Settings', $this->textDomain ),
            __( 'Test Inpsyde Settings', $this->textDomain ),
            'manage_options',
            'test-inpsyde-settings',
            [ $this, 'displaySettingsPage' ]
        );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Showing content for options page
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function displaySettingsPage() {
        /** @var ViewService $viewService */
        $viewService = $this->getService( ViewService::class );

        // We ignore WordPress.XSS.EscapeOutput.OutputNotEscaped because we're gonna do this on the view side
        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        echo $viewService->render(
            'views/admin/yivic-test-inpsyde-settings',
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            [
                'textDomain'            => $this->textDomain,
                'customEndpointName'    => get_option( static::OPTION_KEY, 'custom-inpsyde' )
            ]
        );
        exit;
    }
}