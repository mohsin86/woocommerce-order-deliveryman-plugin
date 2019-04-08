<?php
/*
 * Settings Page For Delivery Man
 *
 */


class options_page_for_delivery_man
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'deliveryman_dashboard_script'));
    }

    public function admin_menu()
    {
        add_options_page(
            'FCM For Delivery Man',
            'FCM Delivery Man',
            'manage_options',
            'deliveryman_options_page',
            array(
                $this,
                'settings_page'
            )
        );
    }

    public function settings_page()
    {

        self::deliveryman_add_edit_device_token();
        include(plugin_dir_path(__FILE__) . 'view/deshboard.php');

    }

    public function deliveryman_dashboard_script($hook)
    {


        if ($hook !== 'settings_page_deliveryman_options_page') return;

        wp_enqueue_style( 'my_datatable_style_bootstrap',plugins_url( 'assets/css/bootstrap.min.css', dirname(__FILE__) ), array(), '3.3.7', 'all' );
        wp_enqueue_style('bootstrap-toggle', 'https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css');
        wp_enqueue_style('my_datatable_style_button', plugins_url('assets/css/deliveryman-dashboard.css', dirname(__FILE__)), array(), '1.0.0', 'all');

        wp_enqueue_script('bootstrap-toogle-js', 'https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js');

    }

    /*
     * If form is submit
     * Check deliveryman_device_token exist or not
     * Insert Post data to deliveryman_device_token
     */
    public function deliveryman_add_edit_device_token()
    {
        $screen = get_current_screen();
        if (isset($screen) && $screen->id !== 'settings_page_deliveryman_options_page') return;

        if (isset($_POST['submit']) and $_POST['submit']=='Save Changes') {
            self::activate_fcm_for_deliveryman();

            self::set_server_key_for_fcm();

            self::add_device_token_to_table();

        }
    }

    public function activate_fcm_for_deliveryman(){

        if (isset($_POST['activate_deliveryman_fcm_api'])) {
            $deliveryman_fcm_api = sanitize_text_field(trim($_POST['activate_deliveryman_fcm_api']));
            update_option('activate_deliveryman_fcm_api', $deliveryman_fcm_api);
        }else{
            update_option('activate_deliveryman_fcm_api', 'off');
        }
    }

    public function set_server_key_for_fcm(){
        if (isset($_POST['deliveryman_fcm_api'])) {
            $deliveryman_fcm_api = sanitize_text_field(trim($_POST['deliveryman_fcm_api']));
            update_option('deliveryman_fcm_api', $deliveryman_fcm_api);
        }
    }

    public function add_device_token_to_table(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'deliveryman_device_token';

        //WP_User objects.
        $deliveryBoy = get_users('orderby=nicename&role=deliveryman');

        // Check if table exist;
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            self::deliveryman_create_db();
        }


        // aggregate data for insertion
        $insertions_data = '';
        foreach ($deliveryBoy as $user) {
            if (isset($_POST['deviceTokenForUser_' . $user->ID])) {
                $user_token = sanitize_text_field(trim($_POST['deviceTokenForUser_' . $user->ID]));
                $insertions_data .= "($user->ID, '$user->display_name', '$user_token', NOW()),";
            }
        }


        if($insertions_data){
            $insertions_data = rtrim($insertions_data,',') ;
            $query = "
                      REPLACE INTO $table_name(userId,userName,DeviceToken,time) 
                      VALUES $insertions_data";
            $wpdb->query($query);

        }
    }


    public function deliveryman_create_db()
    {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $deliveryman_db_version = '1.0.0';

        $table_name = $wpdb->prefix . 'deliveryman_device_token';

        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

            $sql = "CREATE TABLE $table_name (
                userId mediumint(9) NOT NULL,
                userName tinytext NOT NULL,
                deviceToken text NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (userId)
	      ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            add_option('deliveryman_plugin_version', $deliveryman_db_version);
            add_option('deliveryman_fcm_api', '');
            add_option('activate_deliveryman_fcm_api', 'off');

        }
    }
}

new options_page_for_delivery_man;
