<?php

/*
  Plugin Name: پارسی سخن
  Plugin URI:http://www.alivazirinia.ir/blog
  Description:  نمایش جملات و سخنان بزرگان
  Version: 1.0
  Author: علی وزیری - کیوان علی محمدی 
  Author URI:http://www.alivazirinia.ir
  License:GPL 2.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
define( 'SB4WP_DIR', plugin_dir_path( __FILE__ ) );
define( 'SB4WP_INC_DIR', trailingslashit( SB4WP_DIR . 'inc' ) );
define( 'SB4WP_URL', plugin_dir_url( __FILE__ ) );
define( 'SB4WP_CSS_URL', trailingslashit( SB4WP_URL . 'css' ) );
define( 'SB4WP_JS_URL', trailingslashit( SB4WP_URL . 'js' ) );
//includes
require_once (SB4WP_INC_DIR.'ajx_response.php');
require_once (SB4WP_INC_DIR.'front-end.php');
require_once (SB4WP_INC_DIR.'widget.php');
require_once (SB4WP_INC_DIR.'sb_shortcode.php');
//hooks
register_activation_hook(__FILE__,'parsisokhan_init');
add_action('admin_menu', 'admin_pages_parsisokhan');
add_action('admin_print_styles', 'sb_styles_parsisokhan');
add_action('admin_print_scripts','sb_scripts_parsisokhan');
//Functions
function admin_pages_parsisokhan() {
    add_menu_page("پارسی سخن", "پارسی سخن", 'manage_options', 'sb_page', 'parsisokhan_page_content' ,plugins_url( 'parsi-sokhan/img/icon.png' ), 99);
}
function parsisokhan_init(){
    global $wpdb;
    $query='CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'sb` (
            `id` int(20) NOT NULL AUTO_INCREMENT,
            `teller` text COLLATE utf8_bin NOT NULL,
            `content` text COLLATE utf8_bin NOT NULL,
            `status` tinyint(1) NOT NULL,
            `date` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ';
    
    $wpdb->query($query);
    add_option('sb_paginate_count',15);
}
function sb_scripts_parsisokhan() {
    wp_register_script('sb_scripts_parsisokhan',SB4WP_JS_URL.'sb_admin_script.js');
    wp_localize_script('sb_scripts_parsisokhan', 'sb_ajax', array('ajaxurl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http'))));
    wp_enqueue_script('sb_scripts_parsisokhan');
}
function sb_styles_parsisokhan(){
    wp_register_style('sb_admin_styles',SB4WP_CSS_URL.'sb_admin_style.css');
    wp_enqueue_style('sb_admin_styles');
}


