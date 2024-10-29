<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class APPMAKER_WC_YARPP {


	public function __construct() {
		add_filter( 'appmaker_wc_related_products', array( $this, 'get_related_products_yarpp' ), 2, 2 );
	}

	public function get_related_products_yarpp( $related_products, $product ) {

		 /**
		 * @var $yarpp YARPP
		 */
		global $yarpp;

		$limit = $yarpp->get_option( 'limit' );
		$id    = $product->get_id();	

		$args = array( 'limit' => $limit );

		$related_posts    = $yarpp->get_related(
			$id,
			$args
		);
		$related_products = wp_list_pluck( $related_posts, 'ID' );

		return $related_products;

	}
}
new APPMAKER_WC_YARPP();
