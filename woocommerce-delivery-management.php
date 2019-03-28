<?php
/*
Plugin Name: Deliveryman Management with delivery report for Woocommerce
Plugin URI:  http://dragonitbd.com
Description: This plugin manages Deliveryman for orders deliveries
Version:     1.1
Author:      Mohsin
Author URI:  http://dragonitbd.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Hush! Stay away please!' );

/**
 * a. Load Common Function
 * b. Load Language File
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/common.php';

/**
 *
 * a. Add custom field in admin order Page.
 * b. Add DeliveryMan Role in User.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-main.php';

/**
 *
 * a. Load Page Menu "Delivery Assign List" Under Woocommerce Menu.
 * b. Filter by Deliver Man, Delivery Status, Order Date.
 * c. Print, Export Delivery Data.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-page.php';

/**
 *
 * a. a page for delivery man
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/delivery-man-page.php';



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


