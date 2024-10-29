<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class APPMAKER_WC_SMART_COUPONS
 */
class APPMAKER_WC_WPCLEVER {

	/**
	 * Function __construct.
	 */
	public function __construct() {
	    add_filter( 'appmaker_wc_cart_items', array( $this, 'handle_bundled_products' ), 10, 1 );
	}

    public function handle_bundled_products( $return ) {

        if ( is_array ( $return['products'] ) ) {
			foreach( $return['products'] as $id => $product ) {

                if ( isset( $product['woosb_ids'], $product['woosb_price'], $product['woosb_fixed_price'] ) && ! $product['woosb_fixed_price'] ) {
                    $return['products'][$id]['product_price_display'] =  APPMAKER_WC_Helper::get_display_price( ( $product['woosb_price'] ) );
                }
    
                if ( isset( $product['woosb_parent_id'], $product['woosb_price'], $product['woosb_fixed_price'] ) && $product['woosb_fixed_price'] ) {
                    $return['products'][$id]['product_price_display'] =  APPMAKER_WC_Helper::get_display_price( ( $product['woosb_price'] ) );
                }
                if ( isset( $product['woosb_parent_id']) ) {
                    $return['products'][$id]['qty_config']['display']   = false;
					$return['products'][$id]['hide_delete_button'] = true;

                }
            }
        }
        return $return;
    }

}
new APPMAKER_WC_WPCLEVER();