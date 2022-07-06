<?php
namespace YivicTestInpsyde\Wp\Plugin\Services;

use YivicTestInpsyde\Wp\Plugin\YivicTestInpsyde;
use YivicTestInpsyde\Wp\Plugin\Traits\ConfigTrait;
use YivicTestInpsyde\Wp\Plugin\Traits\ServiceTrait;

/**
 * Class PageRendererService
 * @package TestInpsyde\Wp\Plugin\Services
 * @method YivicTestInpsyde getContainer()
 */
class PageRendererService {
    use ConfigTrait;
    use ServiceTrait;

    /**
     * @inheritDoc
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }

    /**
     * Enqueue JS and CSS
     */
    public function enqueueScripts() {
        // JS
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'test-inpsyde-plugin-main',
            $this->getContainer()->baseUrl.'/assets/dist/js/main.js',
            ['jquery'],
            $this->getContainer()->version,
            true
        );

        // CSS
        wp_enqueue_style(
            'test-inpsyde-plugin-main',
            $this->getContainer()->baseUrl.'/assets/dist/css/main.css',
            [],
            $this->getContainer()->version
        );
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Render custom page with corresponding pagename (slug)
     *
     * @param ViewService $viewService View service to use for rendering
     * @param string $pagename Name (slug) of page to be rendered
     * @param array $params Array of params to put to views
     *
     * @throws \Exception
     */
    public function render( $viewService, $pagename, $params = [] ) {
        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        echo $viewService->render( 'views/page/'.$pagename, $params );
        exit;
    }
}
