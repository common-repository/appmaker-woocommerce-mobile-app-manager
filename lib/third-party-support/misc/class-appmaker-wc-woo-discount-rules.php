<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

use Wdr\App\Controllers\ManageDiscount;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Rule;
use Wdr\App\Helpers\Woocommerce;
use Wdr\App\Models\DBTable;
class APPMAKER_WC_WDR
{

    public function __construct()
    {
      add_filter('appmaker_wc_product_data',array($this,'wdr_single_product_price'), 2, 3 );
    }

    public function wdr_single_product_price( $data, $product, $expanded ){

        $product_id = (int) $product->is_type( 'variation' ) ? $product->get_variation_id() : APPMAKER_WC_Helper::get_id( $product );

        $product_obj = wc_get_product($product_id);

        
        $manageDiscount = new ManageDiscount();

        $original_prices_list = $sale_prices_lists = $discount_prices_lists = array();
        if( $product_obj->is_type( 'variable' ) ) {
            $woo_obj = new Woocommerce();
            $variations = $woo_obj->getProductChildren($product_obj);

            if (!empty($variations)) {
                $consider_out_of_stock_variants = apply_filters('advanced_woo_discount_rules_do_strikeout_for_out_of_stock_variants', false);

                foreach ($variations as $variation_id) {
                    if (empty($variation_id)) {
                        continue;
                    }
                    $variation = $woo_obj->getProduct($variation_id);
                    if(!$woo_obj->variationIsVisible($variation)){
                        continue;
                    }
                    if(!$woo_obj->isProductHasStock($variation) && !$consider_out_of_stock_variants) {
                        continue;
                    }
                    $prices =  $manageDiscount->calculateInitialAndDiscountedPrice($variation, 1);
                    if (!isset($prices['initial_price']) || !isset($prices['discounted_price'])) {
                        $original_prices_list[] = $woo_obj->getProductRegularPrice($variation);
                        $sale_prices_lists[] = $woo_obj->getProductPrice($variation);
                        continue;
                    }
                    $original_prices_list[] = $prices['initial_price'];
                    $discount_prices_lists[] = $prices['discounted_price'];
                }
            }
            $discount_prices_lists = array_unique($discount_prices_lists);//print_r($discount_prices_lists);exit;
            $original_prices_list = array_unique($original_prices_list);            
            $discount_prices_lists = array_merge($discount_prices_lists, $sale_prices_lists);
            $min_price = min($discount_prices_lists);
            $max_price = max($discount_prices_lists);
            $min_original_price = min($original_prices_list);
            $max_original_price = max($original_prices_list);
            $calculator =  $manageDiscount::$calculator;
            if(!empty($min_original_price)){
                $min_original_price = $calculator->mayHaveTax($product_obj, $min_original_price);
            }
            if(!empty($max_original_price)){
                $max_original_price = $calculator->mayHaveTax($product_obj, $max_original_price);
            }
            if(!empty($min_price)){
                $min_price = $calculator->mayHaveTax($product_obj, $min_price);
            }
            if(!empty($max_price)){
                $max_price = $calculator->mayHaveTax($product_obj, $max_price);
            }
            if( !empty( $min_price) && !empty( $max_price ) ){
                $data['on_sale'] = true;
                $data['price_display']    =  $data['sale_price_display'] = $min_price !== $max_price ? sprintf( _x( '%1$s-%2$s', '', 'woocommerce' ), APPMAKER_WC_Helper::get_display_price( $min_price ), APPMAKER_WC_Helper::get_display_price( $max_price ) ) : APPMAKER_WC_Helper::get_display_price( $min_price );
                $data['price']      = $data['sale_price'] = $min_price !== $max_price ? sprintf( _x( '%1$s-%2$s', '', 'woocommerce' ), $min_price ,  $max_price ) : $min_price ;
                $data['sale_percentage'] =  ( $product->is_on_sale() && 0 != $data['regular_price'] && ( $data['regular_price'] > $data['sale_price'] ) ) ? round( ( ( (float) $data['regular_price'] - (float) $data['sale_price'] ) / (float) $data['regular_price'] ) * 100 ).'%' : false;
            }
            
        } else {

            $prices =  $manageDiscount->calculateInitialAndDiscountedPrice($product_obj, 1 , false, false);//print_r($prices);
            if( $prices ) {
                $apply_as_cart_rule = isset($prices['apply_as_cart_rule']) ? $prices['apply_as_cart_rule'] : array('no');
    
                if(!empty($apply_as_cart_rule)){
                    if(!in_array('no', $apply_as_cart_rule)){
                        return $data;
                    }
                }
                $initial_price = isset($prices['initial_price']) ? $prices['initial_price'] : 0;
                $discounted_price = isset($prices['discounted_price']) ? $prices['discounted_price'] : 0;
    
                if( $discounted_price ) {
                    $data['on_sale'] = true;
                    $data['price'] =  $data['sale_price'] = $discounted_price;
                    $data['price_display'] =  $data['sale_price_display'] = APPMAKER_WC_Helper::get_display_price( $discounted_price );
                    $data['sale_percentage'] = ( $product->is_on_sale() && 0 != $data['regular_price'] && ( $data['regular_price'] > $data['sale_price'] ) ) ? round( ( ( (float) $data['regular_price'] - (float)$data['sale_price'] ) / (float)$data['regular_price'] ) * 100 ).'%': false;
                }
            }
        }         
    
        
        
        return $data;
    }


}
new APPMAKER_WC_WDR();