<?php
/*
 * Bootstrap Toggle implemented
 *  http://www.bootstraptoggle.com/
 */
global $wpdb;
$table_name = $wpdb->prefix . 'deliveryman_device_token';
$riders = [];

$all_delivery_man = $wpdb->get_results("SELECT userId, DeviceToken FROM $table_name", ARRAY_N);
if (!empty($all_delivery_man)) {
    foreach ($all_delivery_man as $rider) {
        $riders[$rider[0]] = $rider[1];
    }
}


?>
<div class="deliveryman-deshboard">
    <h1> Firebase Push Notification Settings for Rider </h1>
    <form action="<?php echo admin_url("options-general.php?page=" . $_GET["page"]); ?>" method="post">

        <div id="postbox-container-1" class="postbox-container">
            <div class="postbox">
                <div class="postbox-data">
                    <h2 class="hndle ui-sortable-handle"><span> FCM Server Api Key <sub><a
                                        href="<?php echo DELIVERYMAN_PLUGIN_PATH . 'assets/images/server_api_key.gif'; ?>">what is this ??</a></sub></span>
                    </h2>
                    <table>
                        <tbody>
                        <tr height="70">
                            <td><label for="activate_deliveryman_fcm_api"><?php echo __("Activate Push Notification", 'woocommerce'); ?></label> </td>
                            <td>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" <?php echo get_option('activate_deliveryman_fcm_api')==='on'? 'checked':''; ?>  name="activate_deliveryman_fcm_api"  data-toggle="toggle" data-on="Enabled" data-off="Disabled" data-onstyle="success" data-offstyle="danger">

                                        <?php echo get_option('activate_deliveryman_fcm_api')==='on'? 'FCM is enabled':'FCM is disable'; ?>
                                    </label>
                                </div>
                            </td>
                        </tr>

                        <tr height="70">
                            <td><label for="fcm_api"> <?php echo __("FCM API Key", 'woocommerce'); ?></label></td>
                            <td><input id="fcm_api" name="deliveryman_fcm_api" type="text"
                                       value="<?php echo get_option('deliveryman_fcm_api'); ?>" required="required"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="postbox-container-2" class="postbox-container">
            <div class="postbox">
                <div class="postbox-data">
                    <div class="header-box">
                        <h2 class="hndle ui-sortable-handle"> Rider Device Token Information</h2>
                        <p class="token-info">Enter Device Token For deliveryman</p>
                    </div>

                    <div class="content-box">

                        <table>
                            <thead>
                            <tr>
                                <td><b>User Name </b></td>
                                <td><b>Email </b></td>
                                <td><b>Device Token</b></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $blogusers = get_users('orderby=nicename&role=deliveryman');
                            // Array of WP_User objects.
                            foreach ($blogusers as $user) {
                                $token = isset($riders[$user->ID]) ? $riders[$user->ID] : '';
                                echo '<tr>';
                                echo '<td>' . $user->first_name . ' ' . esc_html($user->last_name) . '</td>';
                                echo '<td>' . esc_html($user->user_email) . '</td>';
                                echo '<td><input name="deviceTokenForUser_' . $user->ID . '" type="text" value="' . $token . '" /> </td>';
                                echo '</tr>';
                            }
                            ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        <?php submit_button(); ?>

    </form>

</div>
<script>
    $(function() {
        $('#toggle-two').bootstrapToggle({
            on: 'Enabled',
            off: 'Disabled'
        });
    })
</script>