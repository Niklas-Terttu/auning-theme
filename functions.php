<?php
if (!defined('ABSPATH')) exit;
function auning_setup(){
 add_theme_support('title-tag'); add_theme_support('post-thumbnails'); add_theme_support('custom-logo',array('height'=>100,'width'=>360,'flex-height'=>true,'flex-width'=>true));
 add_theme_support('html5',array('search-form','comment-form','comment-list','gallery','caption','style','script'));
 add_theme_support('woocommerce'); add_theme_support('wc-product-gallery-zoom'); add_theme_support('wc-product-gallery-lightbox'); add_theme_support('wc-product-gallery-slider');
 register_nav_menus(array('primary'=>'Hovedmenu'));
} add_action('after_setup_theme','auning_setup');
function auning_assets(){wp_enqueue_style('auning',get_stylesheet_uri(),array(),'1.0.0');wp_enqueue_script('auning',get_template_directory_uri().'/assets/theme.js',array(),'1.0.0',true);}add_action('wp_enqueue_scripts','auning_assets');
function auning_customizer($c){$c->add_section('auning_settings',array('title'=>'Auning: forside og kontakt'));
 $fields=array('phone'=>'Telefon','email'=>'E-mail','address'=>'Adresse','hours'=>'Åbningstider','hero_title'=>'Forsideoverskrift','hero_text'=>'Forsidetekst');
 foreach($fields as $key=>$label){$c->add_setting('auning_'.$key,array('default'=>'','sanitize_callback'=>$key==='email'?'sanitize_email':($key==='hero_text'?'sanitize_textarea_field':'sanitize_text_field')));$c->add_control('auning_'.$key,array('label'=>$label,'section'=>'auning_settings','type'=>$key==='hero_text'?'textarea':'text'));}
 $c->add_setting('auning_hero_image',array('sanitize_callback'=>'absint'));$c->add_control(new WP_Customize_Media_Control($c,'auning_hero_image',array('label'=>'Forsidebillede (maskine eller værksted)','section'=>'auning_settings','mime_type'=>'image')));
}add_action('customize_register','auning_customizer');
function auning_shop_url(){return function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/?s=');}
function auning_fallback_menu(){echo '<ul id="primary-menu">';foreach(array('Maskiner','Have & park','Reservedele') as $label){echo '<li><a href="'.esc_url(auning_shop_url()).'">'.esc_html($label).'</a></li>';}echo '<li><a href="'.esc_url(home_url('/#vaerksted')).'">Værksted & service</a></li><li><a href="'.esc_url(home_url('/#kontakt')).'">Kontakt</a></li></ul>';}
