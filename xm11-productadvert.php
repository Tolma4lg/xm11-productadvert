<?php

/*
Plugin Name: xm11-productadvert
Plugin URI: http://
Description: Plugin to show products you offer according to Products Scheme.
Version: 0.1
Author: Stefan Skliarov
*/

/*  Copyright 2015 Stefan Skliarov (email: stefan.skliarov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
  
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Loads the image management javascript
 */

defined('ABSPATH') or die('No script kiddies please!');

include ('/xm11-productadvert-widget.php');
function productadvert_image_enqueue() {
    global $typenow;
    if ($typenow == 'post') {
        wp_enqueue_media();
        
        // Registers and enqueues the required javascript.
        wp_register_script('meta-box-image', plugin_dir_url(__FILE__) . 'meta-box-image.js', array('jquery'));
        wp_localize_script('meta-box-image', 'meta_image', array('title' => __('Choose or Upload an Image', 'productadvert-textdomain'), 'button' => __('Use this image', 'productadvert-textdomain'),));
        wp_enqueue_script('meta-box-image');
    }
}

//adding hook for image load
add_action('admin_enqueue_scripts', 'productadvert_image_enqueue');

/*
 *Adding metabox to post page
*/

//main function for adding metabox to post and page
function productadvert_init() {
    
    add_meta_box('product_advert', 'ProductAdvert', 'productadvert_content', 'post', 'side', 'high');
    add_meta_box('product_advert', 'ProductAdvert', 'productadvert_content', 'page', 'side', 'high');
};

//input fields in metabox
function productadvert_content($post) {
    
    //Retrieve metabox data if exists
    $product_name = get_post_meta($post->ID, '_product_name', true);
    $product_description = get_post_meta($post->ID, '_product_description', true);
    $meta_image = get_post_meta($post->ID, '_meta_image', true);
    $product_price = get_post_meta($post->ID, '_product_price', true);
    $price_currency = get_post_meta($post->ID, '_price_currency', true);
    
    // Create a nonce field for verification
    wp_nonce_field('cf_submit_productadvert', 'cf_productadvert_check');
    
    //outputting input fields
    function imagelink() {
        if (isset($prfx_stored_meta['meta-image'])) echo $prfx_stored_meta['meta-image'][0];
    }
    function imagebutton() {
        _e('Choose or Upload an Image', 'productadvert-textdomain');
    }
    echo '
      <input placeholder="Product name" name="product_name" type="text" value ="' . esc_attr($product_name) . '" />
      <textarea placeholder="Product description" name="product_description" type="text" rows="4" >' . esc_attr($product_description) . '</textarea>
      <input placeholder="Path to image" type="text" name="meta_image" id="meta-image" value="' . esc_attr($meta_image) . '" >
      <input type="button" id="meta-image-button" class="button" value="Add/Change product image" />
      <input placeholder="Product price" name="product_price" type="text" value ="' . esc_attr($product_price) . '" />
      <input placeholder="Price currency" name="price_currency" type="text" value ="' . esc_attr($price_currency) . '" />
      ';
};

//adding hook for showing metabox
add_action('add_meta_boxes', 'productadvert_init');

/**
 *end adding metabox to page
 */

/*
 * Metabox save/update
*/

//ading hook to save data
add_action('save_post', 'xm11_productadvert_save');

// saving metabox data
function xm11_productadvert_save($post_id) {
    
    // Verify if this is an auto save routine.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    //Check permissions
    if (!current_user_can('publish_posts')) {
        
        // Check for capabilities, not role
        wp_die('Insufficient Privileges: Sorry, you do not have permissions for this action');
    }
    
    // Check nonce cf_productadvert_check
    // Verify this came from the our screen and with proper authorization
    if (!isset($_POST['cf_productadvert_check']) || !wp_verify_nonce($_POST['cf_productadvert_check'], 'cf_submit_productadvert')) {
        return;
    }
    
    //Now we check check if all fields are set and save data
    if (isset($_POST['product_name']) && isset($_POST['product_description']) && isset($_POST['product_price']) && isset($_POST['price_currency'])) {
        update_post_meta($post_id, '_product_name', strip_tags($_POST['product_name']));
        update_post_meta($post_id, '_product_description', strip_tags($_POST['product_description']));
        update_post_meta($post_id, '_meta_image', strip_tags($_POST['meta_image']));
        update_post_meta($post_id, '_product_price', strip_tags($_POST['product_price']));
        update_post_meta($post_id, '_price_currency', strip_tags($_POST['price_currency']));
    }
}

/*
 * End of metabox save/update
*/

/*
 * Product advert shortcode section
*/

//registering shortcode
add_shortcode('xm11_productadvert', 'xm11_productadvert_shortcode_handler');
//main function to handle shortcode
function xm11_productadvert_shortcode_handler($id) {
    
    //checking if post ID is set. Set to current otherwise.
    if ($id['post_id'] == "") {
        global $post;
        $post_id = $post->ID;
    } else {
        $post_id = $id['post_id'];
    }
    
    //retrieve metainformation for set post or page
    
    $product_name = get_post_meta($post_id, '_product_name', true);
    $product_description = get_post_meta($post_id, '_product_description', true);
    $meta_image = get_post_meta($post_id, '_meta_image', true);
    $product_price = get_post_meta($post_id, '_product_price', true);
    $price_currency = get_post_meta($post_id, '_price_currency', true);
    
    //outputting product advert information
    //adding Product Schema to output
    $shortcode_block = '
            <!--Указывается схема Product.-->
<div itemscope style="text-align: center; font-size: 20px; " itemtype="http://schema.org/Product">

<!--В поле name указывается наименование товара.-->
  <h1 itemprop="name" style="text-align: center; font-size: 20px; " >' . esc_attr($product_name) . '</h1>

<!--В поле description дается описание товара.-->
  <span itemprop="description" style="text-align: left; font-size: 20px; ">' . esc_attr($product_description) . '</span> ';
    
    //adding image to product advert block if exists
    if ($meta_image !== "") {
        $shortcode_block = $shortcode_block . '
        <!--В поле image указывается ссылка на картинку товара.-->
        <img src="' . esc_attr($meta_image) . '" itemprop="image">';}
      
        
        // adding Offer Schema to output
        $shortcode_block = $shortcode_block . '
<!--Указывается схема Offer.-->
  <div itemprop="offers" itemscope style="text-align: right; font-size: 20px; " itemtype="http://schema.org/Offer"> 

<!--В поле price указывается цена товара.-->
    <span itemprop="price" class="alignceter"><strong>' . esc_attr($product_price) . '</strong></span>

<!--В поле priceCurrency указывается валюта.-->
    <span itemprop="priceCurrency"><strong>' . esc_attr($price_currency) . '</strong></span>
  </div>
</div>
          ';
    
    return $shortcode_block;
}

/*
 *End of product advert shortcode section
*/
?>