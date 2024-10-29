<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class APPMAKER_WC_PRODUCT_INPUT_FIELDS {

	public function __construct() {
		add_filter( 'appmaker_wc_product_fields', array( $this, 'product_fields' ), 2, 2 );
        add_filter('appmaker_wc_cart_items',array($this,'product_addon_cart'),1,1);
        add_filter('woocommerce_rest_prepare_shop_order', array($this, 'product_addon_order_detail'), 1, 3);
	}

    public function product_addon_order_detail($response,$post,$request){

       $order = wc_get_order(($response->data['id']));
         foreach ( $order->get_items() as $item ) {
             $product = $item->get_data();
             if($product['variation_id'] != 0){
                 $product_id = $product['variation_id'];
             }else
                $product_id = $product['product_id'];	
			 $scopes  = array( 'global' , 'local');
			 foreach ( $scopes as $scope ) {
                if(  $item->get_meta( '_' . ALG_WC_PIF_ID . '_' . $scope ) ) {
                    foreach( $item->get_meta( '_' . ALG_WC_PIF_ID . '_' . $scope ) as $meta_data ){
                        $value = ! empty ( $meta_data['_value'] ) ? $meta_data['_value'] :  $meta_data['default_value']; 
                        $product_name ="\n".$meta_data['title']." : ".strip_tags($value);
                        foreach($response->data['line_items'] as $key => $data ){
                            //echo $product_id;echo $item['product_id']."\n";
                            if($product['id'] == $data['id']) {
                                $response->data['line_items'][$key]['quantity'] .= $product_name;
                            }
    
                        }
                    } 	
                }
				 
			 }		 		 
 
         }
 
         return $response;         
     }

    public function product_addon_cart($return){
        // print_r($return['products']);exit;
        
        foreach($return['products'] as $key =>$product ) {
            if( isset( $product['alg_wc_pif_local'] ) && !empty( $product['alg_wc_pif_local'] ) ){

                foreach($product['alg_wc_pif_local'] as $addon => $addon_value){
                    $value = ! empty ( $addon_value['_value'] ) ? $addon_value['_value'] :  $addon_value['default_value'];
                    $addon_string = $addon_value['title'].' : '. $value."\n";
                    $return['products'][$key]['variation_string'] .= $addon_string;     
                }
            }  
            if( isset( $product['alg_wc_pif_global'] ) && !empty( $product['alg_wc_pif_global'] ) ){

                foreach($product['alg_wc_pif_global'] as $addon => $addon_value){
                    $value = ! empty ( $addon_value['_value'] ) ? $addon_value['_value'] :  $addon_value['default_value'];
                    $addon_string = $addon_value['title'].' : '. $value."\n";
                    $return['products'][$key]['variation_string'] .= $addon_string;                    
                }
            }            
        }         
         
         return $return;
     }

	/**
	 * @param array $fields
	 * @param WC_Product $product
	 *
	 * @return array|mixed|void
	 */
	public function product_fields( $fields, $product ) {
        $product_id          = $product->get_id();
		// $input_counts_local  = get_post_meta( $product_id, '_' . ALG_WC_PIF_ID . '_local_total_number', true );
		// $input_counts_global = get_option( 'alg_wc_pif_global_total_number', 0 );            
        $scopes = array( 'global', 'local' );
		foreach ( $scopes as $scope ) {
			if ( 'yes' === get_wc_pif_option( $scope . '_enabled', 'yes' ) ) {
				$total_number = apply_filters( 'alg_wc_product_input_fields', 1, ( 'local' === $scope ? 'per_product_total_fields' : 'all_products_total_fields' ), $product_id );
		        for ( $i = 1; $i <= $total_number; $i++ ) {
                    $product_input_field = alg_get_all_values( $scope, $i, $product_id );//print_r($product_input_field);
                    if( ! in_array( $product_input_field['type'],
                    array(
                        'select',
                        'radio',                        
                        'textarea',
                        'text',
                        'checkbox', 
                    ), true )
                    ) {
                        continue;
                    }
                    if ( 'yes' === $product_input_field['enabled'] ) {
                        $required = ( 'yes' === $product_input_field['required'] ) ? true : false;
                        if ( $product_input_field['type'] === 'select' || $product_input_field['type'] === 'radio' ) {
                            $key = 'alg_wc_pif_' . $scope . '_' . $i;
                            $field[ $key ]['required'] = $required;
                            $field[ $key ]['label'] = $product_input_field['title'];
                            $field[ $key ]['type'] = 'select';
                            $field[ $key ]['options'] = array();                          
                            $select_options     = alg_get_select_options($product_input_field['select_radio_option_type'], false );
                            $field[ $key ]['options'] = $select_options;
                            $field[ $key ]['default_value'] =  $product_input_field['default_value'];
                        }else {
                            $key = 'alg_wc_pif_' . $scope . '_' . $i;
                            $field[ $key ]['required'] = $required;
                            $field[ $key ]['label'] = $product_input_field['title'];
                            $field[ $key ]['type'] = $product_input_field['type'];  
                            $field[ $key ]['placeholder'] = $product_input_field['placeholder'];   
                            $field[ $key ]['default_value'] =  $product_input_field['default_value'];                 
                        }
                    }
                }
			}
		}
        
        if(!empty($fields) && !empty($field)) {
            $field = APPMAKER_WC_Dynamic_form::get_fields($field, 'product');
            $fields['items'] = array_merge($fields['items'], $field['items']);
            $fields['order'] = array_merge($fields['order'], $field['order']);
            $fields['dependencies'] = array_merge($fields['dependencies'], $field['dependencies']);
            return $fields;
        }else if(!empty($field)){
            $fields = APPMAKER_WC_Dynamic_form::get_fields($field, 'product');
            return $fields;
        }
        else{

            return $fields;
        }
	}

}

new APPMAKER_WC_PRODUCT_INPUT_FIELDS();
