<?php
/*
Plugin Name: Better Random Redirect
Plugin URI: https://wordpress.org/plugins/better-random-redirect/
Description: Based on the original Random Redirect, this plugin enables efficent, easy random redirection to a post.
Author: Robert Peake
Version: 1.3.8
Author URI: http://www.robertpeake.com/
Text Domain: better_random_redirect
Domain Path: /languages/
*/
if ( !function_exists( 'add_action' ) ) {
    die();
}
function brr_autoload( $ClassName ) {
    if (!class_exists($ClassName)) {
        $file = plugin_dir_path( __FILE__ ) . 'classes/' . $ClassName . '.php';
        if (file_exists($file)) {
            require_once($file);
        }
    }
}
spl_autoload_register('brr_autoload');
BetterRandomRedirect::init();
