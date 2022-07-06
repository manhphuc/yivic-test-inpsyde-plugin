<?php
use YivicTestInpsyde\Wp\Plugin\YivicTestInpsyde;
use YivicTestInpsyde\Wp\Plugin\Helpers\YivicTestInpsydeHelper;

$textDomain         = $viewParams['textDomain'] ?? 'yivic';
$customEndpointName = $viewParams['customEndpointName'] ?? 'custom-inpsyde'; ?>

<div class="wrap">
    <h2 class="title"><?php echo esc_html(__( 'Test Inpsyde Settings', $textDomain ) ) ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields( YivicTestInpsyde::OPTIONS_GROUP_NAME ); ?>
        <table class="form-table" role="presentation">

            <?php
            $goToPageUrl    = home_url( "/?pagename=" . $customEndpointName );
            $attr = [
                'id'        => 'yivic-title-id',
                'class'     => 'yivic-title-class',
                'style'     => 'width: 300px'
            ]; ?>
            <tr valign="top">
                <th scope="row"><label for="<?= YivicTestInpsyde::OPTION_KEY ?>">Label</label></th>
                <td>
                    <?php echo YivicTestInpsydeHelper::textbox( YivicTestInpsyde::OPTION_KEY, esc_attr( get_option( YivicTestInpsyde::OPTION_KEY ) ), $attr ); ?>
                    <a href="<?= $goToPageUrl; ?>" target="_blank" class="inpsyde-users-go-to-page"><?php echo esc_html__( "Go to Page", $textDomain ) ?></a>
                </td>
            </tr>
        </table>
        <p class="submit">
            <?php  $attr = [
                'id'        => 'submit_form',
                'class'     => 'button-primary'
            ]; ?>
            <?php echo YivicTestInpsydeHelper::button( 'submit_form', 'Save Changes', $attr ); ?>
        </p>
    </form>
</div>