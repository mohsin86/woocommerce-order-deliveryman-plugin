<?php
/**
 * Created by PhpStorm.
 * User: mohsin
 * Date: 3/24/2018
 * Time: 5:13 PM
 */
add_action('init', 'myplugin_load_textdomain');
function myplugin_load_textdomain()
{
    load_plugin_textdomain('deliveryman', false, plugin_dir_path(__FILE__) . 'lang');
}