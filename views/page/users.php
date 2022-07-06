<?php
/**
 * The Template for displaying data from https://jsonplaceholder.typicode.com/users)
 */

use YivicTestInpsyde\Wp\Plugin\YivicTestInpsyde;

// phpcs:ignore PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** @noinspection PhpUnhandledExceptionInspection */
$viewService    = YivicTestInpsyde::getServiceView();
$textDomain     = $viewParams['textDomain'] ?? 'inpsyde';
$users          = $viewParams['users'] ?? [];
$errorMessage   = $viewParams['errorMessage'] ?? null;

get_header(); ?>

<?php do_action( 'before_user_listing' ); ?>

<main id="site-content" class="custom-page" role="main">
    <header class="entry-header custom-page__header">
        <div class="entry-header-inner">
            <h1 class="entry-title"><?php echo esc_html( __( 'Custom Inpsyde', $textDomain ) ) ?></h1>
        </div><!-- .archive-header-inner -->
    </header>
    <div class="post-inner custom-page__content">
        <div class="entry-content">

            <?php
            // phpcs:ignore PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
            if ( ! empty( $users ) ) { ?>
                <table class="custom-page__grid">
                    <tr class="custom-page__grid__row">
                        <th class="custom-page__grid__order-number"><?php echo '#' ?></th>
                        <th class="custom-page__grid__id"><?php echo esc_html( __( 'ID', $textDomain ) ) ?></th>
                        <th class="custom-page__grid__name"><?php echo esc_html( __( 'Name', $textDomain ) ) ?></th>
                        <th class="custom-page__grid__username"><?php echo esc_html( __( 'Username', $textDomain ) ) ?></th>
                    </tr>
                    <?php
                    foreach ( $users as $tmpIndex => $user ) {
                        $ajaxHtmlUrl = add_query_arg( [
                            'action'    => 'get_single_user',
                            'id'        => $user['id'],
                        ], admin_url( 'admin-ajax.php' ) );
                        $errorMessage = __( 'There is some error happening. Please try again later!', $textDomain );

                        // Apply filters for adjust data for user
                        $user = apply_filters('adjust_single_user', $user); ?>
                        <tr class="custom-page__grid__row">
                            <td class="custom-page__grid__order-number"><?php echo esc_html( $tmpIndex + 1 ) ?></td>
                            <td class="custom-page__grid__id"><a href="javascript:" data-ajax-html-enabled="true"
                                                                 data-ajax-html-url="<?php echo esc_attr( $ajaxHtmlUrl ) ?>"
                                                                 data-ajax-html-error-message="<?php echo esc_attr( $errorMessage ) ?>"><?php echo esc_html( $user['id'] ) ?></a>
                            </td>
                            <td class="custom-page__grid__name"><a href="javascript:" data-ajax-html-enabled="true"
                                                                   data-ajax-html-url="<?php echo esc_attr( $ajaxHtmlUrl ) ?>"
                                                                   data-ajax-html-error-message="<?php echo esc_attr( $errorMessage ) ?>"><?php echo esc_html( $user['name'] ) ?></a>
                            </td>
                            <td class="custom-page__grid__username"><a href="javascript:"
                                                                       data-ajax-html-enabled="true"
                                                                       data-ajax-html-url="<?php echo esc_attr( $ajaxHtmlUrl ) ?>"
                                                                       data-ajax-html-error-message="<?php echo esc_attr( $errorMessage ) ?>"><?php echo esc_html( $user['username'] ) ?></a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php } else {
                echo esc_html( $errorMessage );
            } ?>
        </div>
    </div>
</main>

<?php do_action('after_user_listing'); ?>

<?php get_footer(); ?>
