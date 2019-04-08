<?php

class Woo_Delivery_My_Account_Endpoint
{

    /*
     * get User Data
     */
    private $get_current_user_data = [];

    /**
     * Custom endpoint name.
     *
     * @var string
     */
    public static $endpoint = 'product-delivery-schedule';


    /**
     * Plugin actions.
     */
    public function __construct()
    {

        add_action('init', array($this, 'get_current_user_data'));
        add_action('wp_enqueue_scripts', array($this, 'load_delivery_man_style_script'));

        // Actions used to insert a new endpoint in the WordPress.
        add_action('init', array($this, 'add_deliveryman_endpoints'));
        add_filter('query_vars', array($this, 'add_query_vars_deliveryman'), 0);

        // Change the My Accout page title.
        add_filter('the_title', array($this, 'endpoint_title_deliveryman'));

        // Insering your new tab/page into the My Account page.
        add_filter('woocommerce_account_menu_items', array($this, 'new_menu_items_deliveryman'));
        add_action('woocommerce_account_' . self::$endpoint . '_endpoint', array($this, 'deliveryman_endpoint_content'));
    }


    public function load_delivery_man_style_script()
    {

        if (array_key_exists('product-delivery-schedule', $_REQUEST)) {
            wp_enqueue_style('my_datatable_style_bootstrap', plugins_url('assets/css/bootstrap.min.css', dirname(__FILE__)), array(), '3.3.7', 'all');
            wp_enqueue_style('my_datatable_style_datatalbe', plugins_url('assets/css/jquery.dataTables.min.css', dirname(__FILE__)), array(), '1.10.13', 'all');
            wp_enqueue_style('my_datatable_style_button', plugins_url('assets/css/buttons.dataTables.min.css', dirname(__FILE__)), array(), '1.2.4', 'all');

            // For date Range
            wp_enqueue_style('my_datatable_style_datepicker', plugins_url('assets/css/daterangepicker.css', dirname(__FILE__)), array());


            wp_enqueue_script('jquery');
            wp_enqueue_script('my_datable_script', plugins_url('assets/js/jquery.dataTables.min.js', dirname(__FILE__)), array('jquery'), '1.10.13', true);
            wp_enqueue_script('my_datable_script_button', plugins_url('assets/js/dataTables.buttons.min.js', dirname(__FILE__)), array('jquery'), '1.2.4', true);


            wp_enqueue_script('my_datable_script_pdfmake', plugins_url('assets/js/pdfmake.min.js', dirname(__FILE__)), array('jquery'), '0.1.18', true);
            wp_enqueue_script('my_datable_script_vfs_fonts', plugins_url('assets/js/vfs_fonts.js', dirname(__FILE__)), array(), '0.1.18', true);
            wp_enqueue_script('my_datable_script_print', plugins_url('assets/js/buttons.html5.min.js', dirname(__FILE__)), array('jquery'), '1.2.4', true);
            wp_enqueue_script('my_datable_script_flash', plugins_url('assets/js/buttons.flash.min.js', dirname(__FILE__)), array('jquery'), '1.2.4', true);
            wp_enqueue_script('my_datable_script_jszip', plugins_url('assets/js/jszip.min.js', dirname(__FILE__)), array('jquery'), '2.5.0', true);
            wp_enqueue_script('my_datable_script_button.print', plugins_url('assets/js/buttons.print.min.js', dirname(__FILE__)), array('jquery'), '1.2.4', true);


            // date Range
            wp_enqueue_script('my_datable_script_moment', plugins_url('assets/js/moment.min.js', dirname(__FILE__)), array('jquery'));
            wp_enqueue_script('my_datable_script_daterange', plugins_url('assets/js/daterangepicker.js', dirname(__FILE__)), array('jquery'), '2', true);

        }


    }

    /*
     * get_current_user_data
     * $retunr user
     */

    public function get_current_user_data()
    {
        $current_user = wp_get_current_user();
        if (isset($current_user->roles) && in_array('deliveryman', $current_user->roles)) {
            $this->get_current_user_data = $current_user;
        }
    }

    /**
     * Register new endpoint to use inside My Account page.
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
     */
    public function add_deliveryman_endpoints()
    {
        add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);
    }


    /**
     * Add new query var.
     *
     * @param array $vars
     * @return array
     */
    public function add_query_vars_deliveryman($vars)
    {
        $vars[] = self::$endpoint;

        return $vars;
    }

    /**
     * Set endpoint title.
     *
     * @param string $title
     * @return string
     */
    public function endpoint_title_deliveryman($title)
    {
        global $wp_query;

        $is_endpoint = isset($wp_query->query_vars[self::$endpoint]);

        if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            // New page title.
            $title = __('Product Delivery', 'deliveryman');

            remove_filter('the_title', array($this, 'endpoint_title_deliveryman'));
        }

        return $title;
    }

    /**
     * Insert the new endpoint into the My Account menu.
     *
     * @param array $items
     * @return array
     */
    public function new_menu_items_deliveryman($items)
    {
        if (!empty($this->get_current_user_data) && isset($this->get_current_user_data->ID) && $this->get_current_user_data->ID) {
            // Remove the logout menu item.
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);

            // Insert your custom endpoint.
            $items[self::$endpoint] = __('Product Delivery', 'deliveryman');

            // Insert back the logout item.
            $items['customer-logout'] = $logout;

            return $items;
        } else
            return $items;
    }

    public function formatted_shipping_address($order_obj)
    {
        $shipping_info = $order_obj->get_address('shipping');
        $address =
            'Name :' . $shipping_info['first_name'] . ' ' . $shipping_info['last_name'] . ' ' . ',  ' .
            //  'Mobile : '.$shipping_info['address_1']      . ', ' .
            'Address 1 : ' . $shipping_info['address_1'] . ', ' .
            'Address 2 : ' . $shipping_info['address_2'] . ',  ' .
            'city : ' . $shipping_info['city'] . ',  ' .
            'state  : ' . $shipping_info['state'] . ',  ' .
            'postcode  : ' . $shipping_info['postcode'];

        return $address;

    }

    /**
     * Endpoint HTML content.
     */
    public function deliveryman_endpoint_content()
    {
        $delivery_man_id = $this->get_current_user_data->ID;
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'meta_key' => '_deliveryman_id',
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_deliveryman_id',
                    'value' => $delivery_man_id,
                    'compare' => '!='
                )

            )

        );

        $delivery_query = new WP_Query($args);

        echo "
                <div class='postbox' style='width: 100%; float:left; padding: 10px;'>
                    <h3 class='hndle ui-sortable-handle'><span> <?php _e('Delivery List:','deliveryman'); ?> </span></h3>
                    <table id='myTable' class='wp-list-table widefat fixed posts ufbl-table'  cellspacing='0' >
                        <thead>
                            <tr>
                                <th>SL <?php _e('Delivery List:','deliveryman'); ?></th>
                                <th>Order <?php _e('Delivery List:','deliveryman'); ?></th>
                                 <th>Order Date <?php _e('Delivery List:','deliveryman'); ?> </th>
                                 <th>Shipping Address </th>
                               
                                <th>Status <?php _e('Delivery List:','deliveryman'); ?></th>
                            </tr>
                        </thead>

                        <tbody>";
        $i = 1;
        if ($delivery_query->have_posts()) {
            // The Loop
            while ($delivery_query->have_posts()) {
                $delivery_query->the_post();
                $order_id = get_the_ID();
                $order_obj = wc_get_order($order_id);

                $deliveryman = get_post_meta($order_id, '_deliveryman_id', true);

                $user_obj = get_user_by('id', $deliveryman);
                $name = $user_obj->user_firstname . ' ' . $user_obj->user_lastname;
                $Delivery_status = get_post_meta($order_id, '_delivery_status', true);

                // Delivery Text Accoring to delivery status

                if ($Delivery_status == 'New') {
                    $delivery_status_text = __("New", 'deliveryman');
                } else if ($Delivery_status == 'Assigned') {
                    $delivery_status_text = __("Assigned", 'deliveryman');
                } else if ($Delivery_status == 'Delivered') {
                    $delivery_status_text = __("Delivered", 'deliveryman');
                }

                ?>
                <tr>
                    <td><?php echo $i++ ?> </td>

                    <td>
                        <?php echo esc_html($order_id); ?>
                    </td>
                    <td><?php printf('<time datetime="%s">%s</time>', esc_attr($order_obj->get_date_created()->date('c')), esc_html($order_obj->get_date_created()->date_i18n(__('Y-m-d H:i:s', 'woocommerce')))); ?> </td>
                    <td>
                        <?php echo $this->formatted_shipping_address($order_obj); ?>
                    </td>
                    <td><?php echo esc_html($delivery_status_text); ?></td>
                </tr>
                <?php

            }
            wp_reset_postdata();
        }

        ?>

        </tbody>

        </table>
        </div>


        <script>
            jQuery(document).ready(function () {
                "use strict";
                jQuery('#myTable').DataTable({
                    dom: 'Bfrtip',
                    text: 'somthng',
                    buttons: [
                        {
                            extend: 'copy',
                            text: 'copy',
                            title: 'Delivery List For <?php echo esc_html($name); ?>'
                        },
                        {
                            extend: 'csv',
                            text: 'csv',
                            title: 'Delivery List For <?php echo esc_html($name); ?>'
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            title: 'Delivery List For <?php echo esc_html($name); ?>'
                        },
                        {
                            extend: 'pdf',
                            text: 'pdf',
                            title: 'Delivery List For <?php echo esc_html($name); ?>'
                        },
                        {
                            extend: 'print',
                            text: 'print',
                            title: 'Delivery List For <?php echo esc_html($name); ?>'
                        }
                    ]
                });
                jQuery('input[name="daterange"]').daterangepicker({
                    locale: {
                        format: 'YYYY/MM/DD'
                    }
                });
            });

        </script>
        <?php


    }

    /**
     * Plugin install action.
     * Flush rewrite rules to make our custom endpoint available.
     */
    public static function install()
    {
        flush_rewrite_rules();
    }
}


new Woo_Delivery_My_Account_Endpoint();
// Flush rewrite rules on plugin activation.
register_activation_hook(__FILE__, array('Woo_Delivery_My_Account_Endpoint', 'install'));






