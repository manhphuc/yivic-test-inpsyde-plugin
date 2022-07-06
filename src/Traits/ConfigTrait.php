<?php
namespace YivicTestInpsyde\Wp\Plugin\Traits;

trait ConfigTrait {
    /**
     * @param array $config
     */
    public function bindConfig( array $config ) {
        foreach ( $config as $attrName => $attrValue ) {
            if ( property_exists( $this, $attrName ) ) {
                $this->$attrName = $attrValue;
            }
        }
    }
}
