<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class APPMAKER_WC_Buy_One_Get_One_Free {

	/**
	 * Function __construct.
	 */
	public function __construct() {
	    add_filter( 'appmaker_wc_cart_items', array( $this, 'set_quantity_bundled_products' ), 10, 1 );
	}

	/**
	 * Function to fix quantity switcher for free products.
	 *
	 * @param array $return Return.
	 *
	 * @return mixed
	 */
	public function set_quantity_bundled_products( $return ) {
		if ( is_array ( $return['products'] ) ) {
			foreach( $return['products'] as $id => $product ) {
                if( isset ( $product['_bogof_free_item'] ) ) {                   
						$return['products'][$id]['qty_config']['display']   = false;
						//$return['products'][$id]['hide_delete_button'] = true;                   
                }              
            }
		}
		return $return;
	}

}

new APPMAKER_WC_Buy_One_Get_One_Free();
