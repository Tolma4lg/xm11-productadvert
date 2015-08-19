<?php

/*
 *Adding widget with metabox information to page
*/
add_action('widgets_init', 'register_xm11_productadvert_widget');

function register_xm11_productadvert_widget() {
    register_widget('XM11_productadvert_widget');
}

/*
 * Main product advert widget class
*/
class XM11_productadvert_widget extends WP_Widget
{
    
    public function __construct() {
        
        $this->WP_Widget('XM11_productadvert_widget', 'Widget to show product advert');
    }
    
    public function widget($args, $instance) {
        global $post;
        
        // Extracting metainformation for post or page
        
        $post_id = $post->ID;
        $product_name = get_post_meta($post_id, '_product_name', true);
        $product_description = get_post_meta($post_id, '_product_description', true);
        $meta_image = get_post_meta($post_id, '_meta_image', true);
        $product_price = get_post_meta($post_id, '_product_price', true);
        $price_currency = get_post_meta($post_id, '_price_currency', true);
        
        // show product advert according to Product schema if metadata exists and not main page
        $mainpage = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
        $currentdir = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . 'index.php';
        $checkurl = $currentdir !== $mainpage;
        
        if ($product_name !== "" && $checkurl) {
            echo $args['before_widget'];
            echo $args['before_title'] . $args['after_title'];
            echo $args['after_widget'];
            
            echo '
            <!--Указывается схема Product.-->
<div itemscope style="text-align: center; font-size: 20px; margin: 10px;" "itemtype="http://schema.org/Product">

<!--В поле name указывается наименование товара.-->
  <h1 itemprop="name" >' . esc_attr($product_name) . '</h1>

<!--В поле description дается описание товара.-->
  <span itemprop="description" >' . esc_attr($product_description) . '</span>';
            
            //check if image is assigned and show if positive
            if ($meta_image !== "") {
                echo '
<!--В поле image указывается ссылка на картинку товара.-->
  <img src="' . esc_attr($meta_image) . '" itemprop="image">
  ';
            }
            
            //show product price accordig to Offer schema
            echo '
<!--Указывается схема Offer.-->
  <div itemprop="offers" style="text-align: right; font-size: 20px; margin: 10px;" itemscope itemtype="http://schema.org/Offer"> 

<!--В поле price указывается цена товара.-->
    <span itemprop="price"><strong>' . esc_attr($product_price) . '</strong></span>

<!--В поле priceCurrency указывается валюта.-->
    <span itemprop="priceCurrency" ><strong>' . esc_attr($price_currency) . '</strong></span>
  </div>
</div>
          ';
        }
    }
}

/*
 * End of main product advert widget class
*/

/*
 *End of widget section
*/
?>