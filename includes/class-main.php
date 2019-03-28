<?php
/**
 * Created by PhpStorm.
 * User: mohammed.mohasin
 * Date: 13-Feb-17
 * Time: 5:20 PM
 */
defined( 'ABSPATH' ) or die( 'Hush! Stay away please!' );

class DeliveryManMetaBox
{

    public function __construct()
    {
        //Display Deliveryman Select Metabox
        add_action( 'add_meta_boxes', array($this, 'deliveryman_add_meta_box') );

        //Save Deliveryman Field Value
        add_action( 'save_post', array($this, 'woo_add_custom_fields_save'), 10, 1 );

        /*** For Column *******/
        // ADDING COLUMN TITLES (Here 2 columns)
        add_filter( 'manage_edit-shop_order_columns', array($this, 'custom_shop_order_column'),11);
        // adding the data for each orders by column (example)
        add_action( 'manage_shop_order_posts_custom_column' , array($this, 'cbsp_credit_details'), 10, 2 );

        // add new order status for delivery Man
        add_filter( 'wc_order_statuses', array( $this,'add_delivery_man_to_order_statuses') );

        add_action( 'init', array( $this,'register_delivery_man_order_status') );

    }


    /**
     * Add Deliveryman user-role on plugin activation
     **/

    public function create_deliveryman_role()
    {
        add_role( 'deliveryman', 'Deliveryman', array( 'read' => true ) );
    }


    /**
     * Delete deliveryman user-role on plugin deactivation
     **/

    public function delete_deliveryman_role()
    {
        remove_role( 'deliveryman' );
    }

    /**
     * Display deliveryman Select Metabox
     **/

    public function deliveryman_add_meta_box()
    {
        add_meta_box(
                'weblounge-deliveryman-box', __( 'Select Deliveryman' ), array($this, 'deliveryman_meta_box_fields'), 'shop_order', 'side', 'default'
        );

    }

    /**
     * Register new status
     *
     **/
    function register_delivery_man_order_status() {
        register_post_status( 'wc-awaiting-shipment', array(
            'label'                     => 'Assign to Delivery Man',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Assign to Delivery Man <span class="count">(%s)</span>', 'Assign to Delivery Man <span class="count">(%s)</span>' )
        ) );
    }


// Add to list of Woocommerce Order statuses
    function add_delivery_man_to_order_statuses( $order_statuses ) {

        $new_order_statuses = array();

        // add new order status after processing
        foreach ( $order_statuses as $key => $status ) {

            $new_order_statuses[ $key ] = $status;

            if ( 'wc-processing' === $key ) {
                $new_order_statuses['wc-awaiting-shipment'] = 'Assign to Delivery Man';
            }
        }

        return $new_order_statuses;
    }

    /**
     * Add fields to the metabox
     **/

    public function deliveryman_meta_box_fields( $post )
    {

        $args = array('role' => 'deliveryman');
        $deliverymans = get_users( $args );

        $deliverymansArray = array();

        $deliverymansArray[0] = '---';
        $deliverStatus = 'new';

        foreach($deliverymans as $key=>$deliveryman)
        {
            $deliverymansArray[$deliveryman->ID] = $deliveryman->user_firstname.' '.$deliveryman->user_lastname;
        }

        woocommerce_wp_select(
            array(
                'id' => '_deliveryman_id',
                'label' => __('Deliveryman: ', 'woocommerce'),
                'options' => $deliverymansArray
            )
        );

        woocommerce_wp_select(
            array(
                'id' => '_delivery_status',
                'label' => __('Status:    ', 'woocommerce'),
                'options' => array('New'=>'New','Assigned'=>'Assigned','Delivered'=>'Delivered')
            )
        );


    }


    /**
     * Save Deliveryman Field Value
     **/

    public function woo_add_custom_fields_save( $product_id )
    {

        if( !empty( $_POST['_deliveryman_id'] ) )
            update_post_meta( $product_id, '_deliveryman_id', sanitize_text_field($_POST['_deliveryman_id']) );

        if( !empty( $_POST['_delivery_status'] ) )
            update_post_meta( $product_id, '_delivery_status', sanitize_text_field($_POST['_delivery_status']) );




        if( !empty( $_POST['_deliveryman_id']) && !empty( $_POST['_delivery_status'] )){
            $this->send_mail_to_deliveryman($_POST['_deliveryman_id'],$_POST['_delivery_status']);
        }



    }


    /*
     *
     * Get formated shipping address
     */
    public function formatted_shipping_address($order_obj)
    {
        $shipping_info =  $order_obj->get_address('shipping');
        $address =
            'Name :'. $shipping_info['first_name'] . ' ' . $shipping_info['last_name'] . ' ' . ',  ' .
            //  'Mobile : '.$shipping_info['address_1']      . ', ' .
            'Address 1 : '.$shipping_info['address_1']      . ', ' .
            'Address 2 : '. $shipping_info['address_2']    . ',  ' .
            'city : '.$shipping_info['city'] .',  ' .
            'state  : '.$shipping_info['state'] . ',  ' .
            'postcode  : '.$shipping_info['postcode'] ;

        return $address;

    }


    function custom_shop_order_column($columns)
    {
        //add columns
        $columns['order-deliveryman'] = __( 'Delivery Assign to','theme_slug');
        return $columns;
    }

    function cbsp_credit_details( $column )
    {
        global $post, $woocommerce, $the_order;

     //   if ( empty( $the_order ) || $the_order->id != $post->ID )
          //  $the_order = new WC_Order( $post->ID );

       //     $order_id = isset($the_order->id)? $the_order->id : '';
        $order_id = $post->ID;
        if($order_id){
            switch ( $column )
            {
                case 'order-deliveryman' :
                    $deliveryman = get_post_meta( $order_id, '_deliveryman_id', true );
                    $name = '';
                    if($deliveryman) {
                        $user_obj = get_user_by('id', $deliveryman);
                        $name = $user_obj->user_firstname.' '.$user_obj->user_lastname;
                    }
                    $Delivery_status = get_post_meta( $order_id, '_delivery_status', true );
                    $myVarOne = $name!=''?'Name: '.$name.'<br>' :'';
                    $myVarOne .= 'Status: '.$Delivery_status;
                    echo $myVarOne;
                    break;
            }
        }

    }

    public function send_mail_to_deliveryman($_deliveryman_id,$delivery_status){
        $user_obj = get_user_by('id', $_deliveryman_id)->data;


        global $post;
        $id = $post->ID;
        $order_obj = wc_get_order( $id );
        if($user_obj){
            $subject  = 'Delivery Status is changed for a order that you have assigned. Delivery Status: '.$delivery_status;
            $shipping_address  = $this->formatted_shipping_address($order_obj);
            $email = $user_obj->user_email;
            $display_name = $user_obj->display_name;
            $message = 'Hello '.$display_name. 'Delivery Details are: '.$shipping_address;
            wp_mail($email, $subject, $message );
        }

    }

}