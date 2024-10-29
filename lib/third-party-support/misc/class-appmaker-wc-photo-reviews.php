<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
class APPMAKER_WC_PHOTO_REVIEWS
{
    public function __construct()
    {        
       
        add_filter('appmaker_product_widget_webview_content', array($this, 'product_widget_photo_reviews'),10, 2 );
        add_filter( 'woocommerce_product_review_comment_form_args', array(
			$this,
			'add_comment_field'
		), PHP_INT_MAX, 1 );
               

       add_filter( 'appmaker_review_images', array( $this, 'reviews_widget_images' ), 2, 3 );     
       add_filter( 'appmaker_wc_product_tabs', array( $this, 'add_review_tab' ), 2, 1 );
       add_filter( 'appmaker_wc_product_widgets', array( $this, 'product_widgets' ), 2, 2 );     
    }

    public function add_review_tab( $tabs ) {

        global $product;
        
        if(!empty($product)){                      
 
              $tabs['add_review'] = array(
                  'title'    => __( 'Add review', 'appmaker-woocommerce-mobile-app-manager' ),
                  'priority' => 2,
                  'callback' => 'comments_template',
              );                     
  
          }
      
      return $tabs; 
    }

    public function reviews_widget_images( $return, $review , $product ) {

        if( $review && get_comment_meta( $review->comment_ID, 'reviews-images' ) ) {
            $image_post_ids = get_comment_meta( $review->comment_ID, 'reviews-images', true );
            foreach ( $image_post_ids as $image_post_id ) {
				if ( ! wc_is_valid_url( $image_post_id ) ) {
					$image_data = wp_get_attachment_metadata( $image_post_id );
					//$image      = get_post( $image_post_id );
                    $image = esc_url( wp_get_attachment_thumb_url( $image_post_id ) );
                }
                $images[] = $image;
            }
            $return = $images;
        }
        //print_r($return);
        return $return;
    
    }  
    
    public function product_widgets( $return, $product_local ) {
        global $product_obj,$product;
        $product_obj = $product_local;
        $product     = $product_local;
        $product_id = $product->get_id();

		$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
        $product_tabs = apply_filters( 'appmaker_wc_product_tabs', $product_tabs );
        $widgets_enabled_in_app = APPMAKER_WC::$api->get_settings( 'product_widgets_enabled', array() );            
        if ( ! empty( $widgets_enabled_in_app ) && is_array( $widgets_enabled_in_app ) ) {
            foreach($widgets_enabled_in_app as $id){
                if(array_key_exists($id,$product_tabs)){
                    $tabs[$id] = $product_tabs[$id];
                }
            }
        }else{
            $tabs = $product_tabs;
        }      
        $content = '';
  
        
        foreach($tabs as $key => $tab){

            
                //$title   = APPMAKER_WC::$api->get_settings( 'product_tab_field_title_'.$key );
                if ( 'add_review' === $key ) { 
                    $options            = get_option( 'appmaker_wc_settings' );
                    $api_key            = $options['api_key'];
                    $base_url           = site_url();   
                    $user_id            = get_current_user_id();
                    $access_token       = apply_filters( 'appmaker_wc_set_user_access_token', $user_id );                   
                    $product_id         = APPMAKER_WC_Helper::get_id($product_local);
                    $url                = $base_url . '/?rest_route=/' .'appmaker-wc/v1/products'. '/content/' . $key. '&id='. $product_id . '&api_key=' . $api_key .'&access_token='.$access_token.'&user_id='.$user_id ; 
                    $url                = add_query_arg( array( 'from_app' => true ), $url );
                    $return['add_review'] = array(
						'type'       => 'menu',
                        'expandable' => isset( $tab['expandable'] ) ? $tab['expandable'] && true : true,
                        'expanded'   => isset( $tab['expanded'] ) ? $tab['expanded'] && true : false,
                        'title'      => __( 'Add review', 'appmaker-woocommerce-mobile-app-manager' ),
                        'content'    => $content,
                        'action'     => array(
                            'type'   => 'OPEN_IN_WEB_VIEW',
                            'params' => array(
                                'url'  => $url,
                                'title' => __( 'Add review', 'appmaker-woocommerce-mobile-app-manager' ),
                            ),
                        ),
                    );
                } 
                
                if( 'reviews' == $key ){
                    $return['reviews']['allow_product_review'] = false;
                }
            
                
        }        
        
		return $return;

        
    }


    public function product_widget_photo_reviews( $content , $tab_id ) {
        if( 'add_review' == $tab_id ) {

            ob_start();                
            //  echo do_shortcode( '[wc_photo_reviews_shortcode]' );  
            //  echo do_shortcode( '[wc_photo_reviews_rating_html]' ); 
           //  echo do_shortcode( '[woocommerce_photo_reviews_form]' );   
            //echo do_shortcode('[wc_photo_reviews_shortcode comments_per_page="12" cols="3" cols_mobile="1" use_single_product="on" cols_gap="" products="" grid_bg_color="" grid_item_bg_color="" grid_item_border_color="" text_color="" star_color="#ffb600" product_cat="" order="" orderby="comment_date_gmt" show_product="on" filter="on" pagination="on" pagination_ajax="on" pagination_pre="" pagination_next="" loadmore_button="off" filter_default_image="off" filter_default_verified="off" filter_default_rating="" pagination_position="" conditional_tag="" custom_css="" ratings="" mobile="on" style="masonry" masonry_popup="review" enable_box_shadow="on" full_screen_mobile="on" overall_rating="on" rating_count="on" only_images="off" image_popup="below_thumb"]');   
            echo do_shortcode( '[woocommerce_photo_reviews_form product_id="" hide_product_details="" hide_product_price="" type="" button_position="center"]' );     
            $content = ob_get_contents(); 
            ob_end_clean();    
            
            if ( ! isset( $_COOKIE['from_app_cookie'] ) ) {
                $expire = time() + 60 * 60*24;
                wc_setcookie( 'from_app_cookie', 1, $expire, false );
            }
        }      

        return $content;
    }
    
    public function add_comment_field( $comment) {

       $comment['fields']['from_app'] = '<input type = "hidden" id="from_app" name="from_app"  value="1" >';
       
       return $comment;
    }
    
   
}
new APPMAKER_WC_PHOTO_REVIEWS();