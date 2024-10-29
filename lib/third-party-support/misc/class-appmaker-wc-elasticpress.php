<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class APPMAKER_WC_ElasticPress {

	public function __construct() {
		// disable elastic search
		add_filter( 'ep_elasticpress_enabled', '__return_false', 999 );
	}

}
new APPMAKER_WC_ElasticPress();
