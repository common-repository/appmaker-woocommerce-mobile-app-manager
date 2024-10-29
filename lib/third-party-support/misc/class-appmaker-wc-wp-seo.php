<?php
/**
 * Created by IntelliJ IDEA.
 * User: shifa
 * Date: 8/7/18
 * Time: 2:17 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class APPMAKER_WC_WPSEO {

	public function __construct() {

		add_filter( 'appmaker_wc_rest_prepare_product', array( $this, 'appmaker_remove_yoast_seo_fields' ), 2, 3 );
		add_filter( 'rest_prepare_post', array( $this, 'appmaker_remove_yoast_seo_fields_post' ), 2, 3 );

	}

	public function appmaker_remove_yoast_seo_fields_post( $data, $post, $request ) {

		if ( isset( $data->data ) && isset( $data->data['yoast_head'] ) ) {
			unset( $data->data['yoast_head'] );
		}
		if ( isset( $data->data ) && isset( $data->data['yoast_head_json'] ) ) {
			unset( $data->data['yoast_head_json'] );
		}

		return $data;
	}

	public function appmaker_remove_yoast_seo_fields( $data, $post, $request ) {

		// $product = wc_get_product( $post->ID );
		if ( isset( $data['yoast_head'] ) ) {
			unset( $data['yoast_head'] );
		}
		if ( isset( $data['yoast_head_json'] ) ) {
			unset( $data['yoast_head_json'] );
		}
		return $data;
	}


}
new APPMAKER_WC_WPSEO();
