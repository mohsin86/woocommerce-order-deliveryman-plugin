<?php
/**
 * Created by PhpStorm.
 * User: mohammed.mohasin
 * Date: 13-Feb-17
 * Time: 5:20 PM
 */

class deliveryReports
{
    public function __construct()
    {
        // Register scripts
        add_action( 'admin_enqueue_scripts', array(__CLASS__,'load_custom_wp_admin_style' ));

        // Add Menu to the admin
        add_action('admin_menu', array(__CLASS__, 'admin_menu_submenu'));
    }

    // Add plugin menu pages to admin menu
    public function admin_menu_submenu() {

        // Set up the plugin admin menu
        add_submenu_page( 'woocommerce', 'Delivery Assign List', 'Delivery Assign List', 'manage_options', 'my_delivery_list_slug', array(__CLASS__,'my_delivery_page'));

    }
    function load_custom_wp_admin_style($hook) {
       //  Load only on ?page=mypluginname

        if($hook != 'woocommerce_page_my_delivery_list_slug') {
                return;
        }

        wp_enqueue_style( 'my_datatable_style_bootstrap','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', array(), '3.3.7', 'all' );
        wp_enqueue_style( 'my_datatable_style_datatalbe','https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css', array(), '1.10.13', 'all' );
        wp_enqueue_style( 'my_datatable_style_button','https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css', array(), '1.2.4', 'all');

        // date Range
        wp_enqueue_style( 'my_datatable_style_datepicker','//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css', array());


       wp_deregister_script( 'jquery' );
        wp_register_script('jquery',"https://code.jquery.com/jquery-1.12.4.js", false, null);
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'my_datable_script','https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js',array ( 'jquery' ),'1.10.13',true);
        wp_enqueue_script( 'my_datable_script_button','https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js',array ( 'jquery' ),'1.2.4',true);


        wp_enqueue_script( 'my_datable_script_pdfmake','https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js',array ('jquery'  ),'0.1.18',true);
        wp_enqueue_script( 'my_datable_script_vfs_fonts','https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js',array ( ),'0.1.18',true);
        wp_enqueue_script( 'my_datable_script_print', 'https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js',array ('jquery'  ),'1.2.4',true);
        wp_enqueue_script( 'my_datable_script_flash', '//cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js',array ('jquery'  ),'1.2.4',true);
        wp_enqueue_script( 'my_datable_script_jszip', ' //cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js',array ('jquery'  ),'2.5.0',true);
        wp_enqueue_script( 'my_datable_script_button.print', '//cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js',array ('jquery'  ),'1.2.4',true);


        // date Range
         wp_enqueue_script( 'my_datable_script_moment', '//cdn.jsdelivr.net/momentjs/latest/moment.min.js',array ('jquery'  ));
         wp_enqueue_script( 'my_datable_script_daterange', '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js',array ('jquery'  ),'2',true);

    }

    function my_delivery_page(){
          self::_deliver_list();
    }
    function _deliver_list(){
         $deliveryman_id = isset($_POST['_deliveryman_id'])? $_POST['_deliveryman_id']:'';
         $delivery_status = isset($_POST['_delivery_status'])? $_POST['_delivery_status']:'';
         $daterange = isset($_POST['daterange'])? $_POST['daterange']:'';

         $args = array(
                    'post_type' => 'shop_order',
                    'meta_key' =>'_deliveryman_id',
                    'orderby'   => 'meta_value',
                    'order' => 'DESC',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                     'meta_query'    => array(
                            'relation'  => 'AND',
                           0 =>  array(
                                'key'       => '_deliveryman_id',
                                'value'     => '',
                                'compare'   => '!='
                            ),
                           1 => array(
                                'key'       => '_deliveryman_id',
                                'value'     => '0',
                                'compare'   => '!='
                            )
                        )
                    );

              if($deliveryman_id){
                    $args['meta_query'][] =
                         array(
                            'key'       => '_deliveryman_id',
                                'value'     => $deliveryman_id,
                                'compare'   => '=='
                        );
              }

              if($delivery_status){
                   $args['meta_query'][] =
                        array(
                            'key'       => '_delivery_status',
                                'value'     => $delivery_status,
                                'compare'   => '=='
                        );
              }

              if($daterange){
                $dateBetween = explode('-',$daterange);
                $after = explode('/',$dateBetween[0]);
                $before = explode('/',$dateBetween[1]);
                         $args['date_query'] = array(
                            'relation'	=> 'AND',
                                 array(
                                    'after' => array(
                                          'year'	=> $after[0],
                                          'month' 	=> $after[1],
                                          'day'	=> $after[2]
                                    ),
                                    'inclusive' => true
                                ),
                                array(
                                    'before' => array(
                                            'year'	=> $before[0],
                                            'month' 	=> $before[1],
                                            'day'	=> $before[2]
                                    ),
                                    'inclusive' => true
                                )
                        );
              }
            $query1 = new WP_Query($args);
         ?>
                <div class="postbox" style='width: 100%; float:left; padding: 10px;'>
                    <h3 class="hndle ui-sortable-handle"><span>Filter: </span></h3>
                    <div class="inside">
                        <form class="alldetails" action="<?php echo admin_url('admin.php?page=my_delivery_list_slug'); ?>" method="post">
                            <div class="row">
                                <div class="col-md-4">
                                <div class="col-md-6 sor">
                                    <div class="form-group">
                                        <label>By :</label>
                                        <select  class="form-control" name="_deliveryman_id">
                                             <option value="">----</option>
                                            <?php
                                                 $args = array('role' => 'deliveryman');
                                                 $deliverymans = get_users( $args );
                                                 if(!empty($deliverymans)){
                                                    foreach($deliverymans as $key=>$deliveryman)
                                                    {
                                                       $selected = '';
                                                       if($deliveryman_id==$deliveryman->ID) $selected= 'selected="selected"';

                                                        echo '<option value="'.$deliveryman->ID.'" '.$selected.'>'.$deliveryman->user_firstname.' '.$deliveryman->user_lastname.'</option>';
                                                    }
                                                 }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 sor">
                                    <div class="form-group">
                                        <label>Status :</label>
                                        <select id="_delivery_status" name="_delivery_status" class="select short form-control" style="">
                                            <option value="">----</option>
                                            <option value="New" <?php echo $delivery_status=='New'? 'selected="selected"':''; ?> >New</option>
                                            <option value="Assigned" <?php echo $delivery_status=='Assigned'? 'selected="selected"':''; ?> >Assigned</option>
                                            <option value="Delivered" <?php echo $delivery_status=='Delivered'? 'selected="selected"':''; ?> >Delivered</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                                <div class="col-md-2">
                                <div class="form-inline">
                                    <div class="form-group">
                                      <label for="from">Order Date: </label> <br>
                                      <input name="daterange" class="datepick hasDatepicker" value="<?php echo $daterange; ?>"  type="text" style="width: 234px;">
                                    </div>
                                </div>
                            </div>
                                <div class="col-md-1">
                                <br>
                                <input value="Search" class="button-primary" type="submit">
                            </div>
                             </div>
                        </form>
                    </div>
                </div>
          <?php
            echo "
                <div class='postbox' style='width: 100%; float:left; padding: 10px;'>
                    <h3 class='hndle ui-sortable-handle'><span>Delivery List: </span></h3>
                    <table id='myTable' class='wp-list-table widefat fixed posts ufbl-table'  cellspacing='0' >
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Order </th>
                                 <th>Order Date </th>
                                <th>Delivery Man</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>";
                        $i = 1;
                        if ( $query1->have_posts() ) {
                            // The Loop
                            while ( $query1->have_posts() ) {
                                $query1->the_post();
                                $order_id =get_the_ID();
                                $order_obj = wc_get_order( $order_id );

                                 $deliveryman = get_post_meta( $order_id, '_deliveryman_id', true );
                                 $user_obj = get_user_by('id', $deliveryman);
                                 $name = $user_obj->user_firstname.' '.$user_obj->user_lastname;
                                 $Delivery_status = get_post_meta( $order_id, '_delivery_status', true );

                                ?>
                                    <tr>
                                        <td><?php echo $i++?> </td>

                                         <td>
                                         <a href="<?php echo home_url();?>/wp-admin/post.php?post=<?php echo $order_id; ?>&action=edit"> #<?php echo $order_id; ?> </a>
                                        </td>
                                         <td><?php echo $order_obj->order_date ; ?> </td>
                                        <td><?php echo $name; ?> </td>
                                        <td><?php echo $Delivery_status; ?></td>
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
              jQuery(document).ready(function() {
                jQuery('#myTable').DataTable( {
                    dom: 'Bfrtip',
                     text: 'somthng',
                    buttons: [
                           {
                                extend: 'copy',
                                text: 'copy',
                                title: 'Delivery List'
                           },
                           {
                                extend: 'csv',
                                text: 'csv',
                                title: 'Delivery List'
                           },
                           {
                                extend: 'excel',
                                text: 'Excel',
                                title: 'Delivery List'
                           },
                           {
                                extend: 'pdf',
                                text: 'pdf',
                                title: 'Delivery List'
                           },
                           {
                                extend: 'print',
                                text: 'print',
                                title:'Delivery List'
                           }
                        ]
                } );
                 jQuery('input[name="daterange"]').daterangepicker({
                        locale: {
                              format: 'YYYY/MM/DD'
                        }
                 });
            } );

            </script>
            <?php
    }
}