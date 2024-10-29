<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

use OTP\Handler\Forms\WooCommerceBilling;

class APPMAKER_WC_MINIORANGE
{

    public function __construct()
    {
        add_filter('appmaker_wc_dynamic_get_form_billing_address_submit', array($this, 'miniorange_billing_address'), 2);
    }

    public function miniorange_billing_address(){
        remove_filter('woocommerce_process_myaccount_field_billing_phone', array( WooCommerceBilling::instance(), '_wc_user_account_update'), 99, 1);
    }
}
new APPMAKER_WC_MINIORANGE();