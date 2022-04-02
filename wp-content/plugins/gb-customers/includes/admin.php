<?php

function gbc_customers_list_page()
{
    $table = new Customer_Table();
    $table->prepare_items();
    $message = '';

    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>Customer deleted</p></div>';
    }
    
    ?>
    <div class="wrap">

        <h2>
            <?php _e('Customers', 'gbc')?> 
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=customer_form');?>"><?php _e('Add new', 'gbc')?></a>
        </h2>
        <?php echo $message; ?>

        <form id="customers-table" method="POST">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $table->display() ?>
        </form>

    </div>
    <?php
}


function gbc_customer_form_page()
{
    global $wpdb;
    $customers = $wpdb->prefix . 'customers'; 

    $message = '';
    $notice = '';

    $customer_table = array(
        'id_customer' => 0,
        'name'      => '',
        'lastname'  => '',
        'email'     => '',
        'phone1'     => '',
        'phone2'     => null,
        'company'   => '',
        'cif'       => '',  
        'vat' => '',
        'address'      => '',
        'postcode'  => '',
        'city'     => '',
        'state'     => '',
        'country'     => '',
        'notes'     => '',
    );

    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
                
        $customer = shortcode_atts($customer_table, $_REQUEST); 
        $customer_valid = gbc_validate_customer($customer);
        
        if ($customer_valid) {
            if ($customer['id_customer'] == 0) {
                $customer_result = $wpdb->insert($customers, $customer);
                $customer['id_customer'] = $wpdb->insert_id;
                
                if ($customer_result) {
                    $message = __('Customer was successfully saved', 'gbc');
                } else {
                    $notice = __('There was an error while saving customer', 'gbc');
                }
            } else {
                $customer_result = $wpdb->update($customers, $customer, array('id_customer' => $customer['id_customer']));

                if ($customer_result === false) {
                    $notice = __('There was an error while updating customer', 'gbc');
                   
                } else {
                    $message = __('Customer was successfully updated', 'gbc');
                }
            }
        } 
        else {        
            $notice = $customer_valid;
        }
    }
    else {   
        $customer = $customer_table;
        if (isset($_REQUEST['id_customer'])) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $customers WHERE id_customer = %d", $_REQUEST['id_customer']), ARRAY_A);
            
            if (!$customer) {
                $customer = $customer_table;
                $notice = __('Customer not found', 'gbc');
            }
        }
    }
    
    ?>
    <div class="wrap">
        <h2>
            <?php _e('Customer', 'gbc')?> 
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=customers');?>">
                <?php _e('back to list', 'gbc')?>
            </a>
        </h2>
        
        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif;?>
            
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif;?>
            
        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
            <input type="hidden" name="id_customer" value="<?php echo $customer['id_customer'] ?>"/>
            <!-- id="poststuff" -->
            <div class="metabox-holder">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php 
                        add_meta_box('address_form_meta_box', __('Customer Address', 'gbc'), 'gbc_address_form_meta_box', 'address', 'normal', 'default');  
                        add_meta_box('customer_form_meta_box', __('Customer Data', 'gbc'), 'gbc_customer_form_meta_box', 'customer', 'normal', 'default');  
                        do_meta_boxes('customer', 'normal', $customer);
                        ?>
                        <input type="submit" value="<?php _e('Save', 'gbc')?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
                
    </div>     
    <?php
}

function gbc_customer_view_page()
{
    global $wpdb;
    $customers = $wpdb->prefix . 'customers'; 

    $message = '';
    $notice = '';
   
    if (isset($_REQUEST['id_customer'])) {
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $customers WHERE id_customer = %d", $_REQUEST['id_customer']), ARRAY_A);
        if (!$customer) {
            $notice = __('Item not found', 'gbc');
        }
    }
    ?>

    <div class="wrap">

        <h2>
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=customers');?>">
                <?php _e('back to list', 'gbc')?>
            </a>
        </h2>
        
        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif;?>
            
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif;?>
            
        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
            <input type="hidden" name="id_customer" value="<?php echo $customer['id_customer'] ?>"/>
            <!-- id="poststuff" -->
            <div class="metabox-holder">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php 
                            add_meta_box('customer_view_meta_box', __('Customer Data', 'gbc'), 'gbc_customer_view_meta_box', 'customer', 'normal', 'default');  
                            do_meta_boxes('customer', 'normal', $customer);
                        ?>
                        <a href="<?php echo('admin.php?page=customer_form&id_customer=' . $customer['id_customer'])?>" class="button-primary">Editar</a>
                    </div>
                </div>
            </div>
        </form>
                
    </div>     
    <?php
}


function gbc_configuration_page()
{								
?>
    <script type="text/javascript">						
        jQuery(document).ready(function($) {
            $('.probar_conexion').click(function(){
                $('#probando_bd').show();
            });		
        });		
    </script>

    <div class="wrap">								
        <h2>
            <?php _e("Prestashop WebService configuration", "gbc");?>
        </h2>

        <?php 
        settings_fields('gbc_config_group');
        $gbc_options = get_option('gbc_options');
        
        if (isset($_POST['option_pass_ws'])){
            check_admin_referer('guardar_imac_settings','imacprestashop_guardar_settings');
            $gbc_options['option_url_ws']=sanitize_text_field($_POST['option_url_ws']);
            $gbc_options['option_pass_ws']=gbc_encryption::encrypt($_POST['option_pass_ws']);
            update_option( 'gbc_options', $gbc_options );						
        }								
        ?>

        <form method="post" action="options-general.php?page=webservice">
            <?php wp_nonce_field('guardar_imac_settings','imacprestashop_guardar_settings');?>
            
            <table class="form-table">																				
                <tr>
                    <th>
                        <?php _e("Prestashop Url:","gbc");?>
                    </th>
                    <td>							
                        <input type="text" name="option_url_ws" required value="<?php echo (isset($gbc_options['option_url_ws'])) ? esc_attr($gbc_options['option_url_ws']) : '';?>" />
                        <em><br><?php _e("Insert http or https without final '/' e.g: https://jjmontalban.github.io or http://jjmontalban.github.io", "gbc");?></em>						</td>
                    </td>
                </tr>	
                <tr>
                    <th>
                        <?php _e("Webservice key:", "gbc");?>
                    </th>
                    <td>
                        <input type="text" name="option_pass_ws" required value="<?php echo (isset($gbc_options['option_pass_ws'])) ? gbc_encryption::decrypt($gbc_options['option_pass_ws']) : '';?>" />			
                        <em><br><?php _e("You can make a webservice key from Prestashop -> Admin Information -> Web Service => Active webservice.", "gbc");?></em>					
                    </td>
                </tr>	 
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e("Save", "gbc");?>" /> 
                <a class="button-primary probar_conexion"><?php _e("test connection", "gbc");?></a>
            </p>				
            <div id="probando_bd" style="display:none;">
                <?php echo do_shortcode('[gbc_products]'); ?>
            </div>
        
        </form>
    </div>
<?php
}	