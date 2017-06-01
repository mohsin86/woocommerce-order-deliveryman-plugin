<?php
/*
Plugin Name: Deliveryman Management with delivery report for Woocommerce
Plugin URI:  http://webloungeonlinebd.com
Description: This plugin manages Deliveryman for orders deliveries
Version:     0.1.0
Author:      Mohsin
Author URI:  http://dragonitbd.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Hush! Stay away please!' );

/**
 *
 * add custom field in admin order Page
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-main.php';

/**
 *
 * Load sms Setting Under woocommerce Menu
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-page.php';

/**
 *
 * check woocommerce plugin available or not
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    register_activation_hook(__FILE__, array('DeliveryManMetaBox', 'create_deliveryman_role'));
    register_deactivation_hook(__FILE__, array('DeliveryManMetaBox', 'delete_deliveryman_role'));

    $metaBox = new DeliveryManMetaBox();
    $deliveryReports = new deliveryReports();

    //WooCommerce plugin activation prompt tracker
    update_option('weblounge_woocommerce_prompt', 'false');
} else {
    //WooCommerce plugin activation prompt tracker
    update_option('weblounge_woocommerce_prompt', 'true');

    if( get_option('weblounge_woocommerce_prompt') == 'true' )
    {
        //Show prompt to user
        add_action('admin_notices', 'weblounge_woocommerce_activate_prompt');
        function weblounge_woocommerce_activate_prompt()
        {
            echo "<div class='updated'> <p>Please activate WooCommerce to use Woocommerce Deliveryman Management with delivery reports Plugin.</p> </div>";
        }
    }
}


