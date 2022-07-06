<?php
namespace YivicTestInpsyde\Wp\Plugin\Helpers;

use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlButton;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlCheckbox;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlFileUpload;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlHidden;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlPassword;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlRadio;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlSelectBox;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlTextarea;
use YivicTestInpsyde\Wp\Plugin\Helpers\Html\HtmlTextbox;

class YivicTestInpsydeHelper {

    public function __construct($options = null){

    }

    static public function textbox( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlTextbox::create( $name, $value, $attr, $options );
    }

    static public function fileupload( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlFileupload::create( $name, $value, $attr, $options );
    }

    static public function password( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlPassword::create( $name, $value, $attr, $options );
    }

    static public function hidden( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlHidden::create( $name, $value, $attr, $options );
    }

    static public function button( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlButton::create( $name, $value, $attr, $options );
    }

    static public function textarea( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlTextarea::create( $name, $value, $attr, $options );
    }

    static public function radio( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlRadio::create( $name, $value, $attr, $options );
    }

    static public function checkbox( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlCheckbox::create( $name, $value, $attr, $options );
    }

    static public function selectbox( $name = '', $value = '', $attr = [], $options = null ): string {
        return HtmlSelectbox::create( $name, $value, $attr, $options );
    }
}
