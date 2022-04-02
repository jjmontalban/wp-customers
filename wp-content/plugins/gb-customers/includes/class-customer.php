<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Customer_Table extends WP_List_Table
{ 
    function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'customer',
            'plural'   => 'customers',
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_phone($item)
    {
        return '<em>' . $item['phone1'] . '</em>';
    }


    function column_name($item)
    {
        $actions = array(
            'view' => sprintf('<a href="?page=customer_view&id_customer=%s">%s</a>', $item['id_customer'], __('View', 'gbc')),
            'edit' => sprintf('<a href="?page=customer_form&id_customer=%s">%s</a>', $item['id_customer'], __('Edit', 'gbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id_customer=%s">%s</a>', $_REQUEST['page'], $item['id_customer'], __('Delete', 'gbc')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id_customer[]" value="%s" />',
            $item['id_customer']
        );
    }

    function get_columns()
    {
        $columns = array(
            'name'      => __('Name', 'gbc'),
            'lastname'  => __('Last Name', 'gbc'),
            'email'     => __('E-Mail', 'gbc'),
            'phone1'    => __('Phone 1', 'gbc'),
            'phone2'    => __('Phone 2', 'gbc'),
            'company'   => __('Company', 'gbc'),
            'address'   => __('Address', 'gbc'),  
            'notes'     => __('Notes', 'gbc'),   
        );
        return $columns;
    }

    //para hacer las columnas sortables
    /* function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'lastname'  => array('lastname', true),
            'email'     => array('email', true),
            'phone1'     => array('phone1', true),
            'company'   => array('company', true),
            'cif'       => array('cif', true),  
        );
        return $sortable_columns;
    } */

    function get_bulk_actions()
    {
        $actions = array( 'delete' => 'Delete' );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $customers = $wpdb->prefix . 'customers'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id_customer']) ? $_REQUEST['id_customer'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $customers WHERE id_customer IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $customers = $wpdb->prefix . 'customers'; 
        $per_page = 10; 
        $columns = $this->get_columns();
        $hidden = array();
        //$sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden/* , $sortable */);
        $this->process_bulk_action();
        
        $total_items = $wpdb->get_var("SELECT COUNT(id_customer) FROM $customers");
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        //$orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'lastname';
        //$order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $customers LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}


function gbc_validate_customer($customer)
{
    $messages = array();

    if (empty($customer['name'])) $messages[] = __('Name is required', 'gbc');
    if (empty($customer['lastname'])) $messages[] = __('Last Name is required', 'gbc');
    if (empty($customer['phone1'])) $messages[] = __('Phone is required', 'gbc');
    if (empty($customer['email'])) $messages[] = __('Email is required', 'gbc');
    if (empty($customer['adresss'])) $messages[] = __('Address is required', 'gbc');
    if (empty($customer['postcode'])) $messages[] = __('Postcode is required', 'gbc');
    if (empty($customer['city'])) $messages[] = __('City is required', 'gbc');
    if (empty($customer['state'])) $messages[] = __('State is required', 'gbc');
    if (empty($customer['country'])) $messages[] = __('Country is required', 'gbc');

    if (!empty($customer['email']) && !is_email($customer['email'])) $messages[] = __('E-Mail is in wrong format', 'gbc');
    if(!empty($customer['phone1']) && !preg_match('/[0-9]+/', $customer['phone1'])) $messages[] = __('Phone must be number');
    if(!empty($customer['phone2']) && !preg_match('/[0-9]+/', $customer['phone2'])) $messages[] = __('Phone must be number');
    if (empty($messages)) 
        return true;

    return implode('<br />', $messages);
}


function gbc_customer_form_meta_box($customer)
{
    ?>
    <form>
        <table class="form-table">
            <tr>
		        <th><?php _e('Customer Data', 'gbc')?></th>
                <td class="form-custom">
                    <div>
                        <label for="name"><?php _e('Name', 'gbc')?></label>
                        <input  id="name" name="name" type="text" value="<?php echo esc_attr($customer['name'])?>" required>
                    </div> 
                    <div>
                        <label for="name"><?php _e('Name', 'gbc')?></label>
                        <input  id="name" name="name" type="text" value="<?php echo esc_attr($customer['name'])?>" required>
                    </div>
                    <div>
                        <label for="lastname"><?php _e('Last Name', 'gbc')?></label>
                        <input  id="lastname" name="lastname" type="text" value="<?php echo esc_attr($customer['lastname'])?>" required>
                    </div>
                    <div>
                        <label for="company"><?php _e('Company', 'gbc')?></label>
                        <input  id="company" name="company" type="text" value="<?php echo esc_attr($customer['company'])?>" >
                    </div>
                    <div>
                        <label for="phone1"><?php _e('Phone 1', 'gbc')?></label>
                        <input  id="phone1" name="phone1" type="tel" value="<?php echo esc_attr($customer['phone1'])?>" required>
                    </div>
                    <div>                
                        <label for="phone2"><?php _e('Phone 2', 'gbc')?></label>
                        <input  id="phone2" name="phone2" type="tel" value="<?php echo esc_attr($customer['phone2'])?>">
                    </div>
                    <div>                
                        <label for="email"><?php _e('Email', 'gbc')?></label>
                        <input  id="email" name="email" type="email" value="<?php echo esc_attr($customer['email'])?>" required>
                    </div>
                    <div>
                        <label for="cif"><?php _e('CIF', 'gbc')?></label>
                        <input  id="cif" name="cif" type="tel" value="<?php echo esc_attr($customer['cif'])?>">
                    </div>
                    <div>                
                        <label for="vat"><?php _e('VAT', 'gbc')?></label> 
                        <input id="vat" name="vat" type="text" value="<?php echo esc_attr($customer['vat'])?>">
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <table class="form-table">
            
            <tr>
                <th><?php _e('Address Data', 'gbc')?></th>
                <td class="form-custom">
                    <div>                
                        <label for="address"><?php _e('Address', 'gbc')?></label>
                        <input  id="address" name="address" type="text" value="<?php echo esc_attr($customer['address'])?>" required>
                    </div>
                    <div>
                        <label for="postcode"><?php _e('Postcode', 'gbc')?></label>
                        <input  id="postcode" name="postcode" type="text" value="<?php echo esc_attr($customer['postcode'])?>" required>
                    </div>
                    <div>
                        <label for="city"><?php _e('City', 'gbc')?></label>
                        <input  id="city" name="city" type="text" value="<?php echo esc_attr($customer['city'])?>" required>
                    </div>
                    <div>
                        <label for="state"><?php _e('State', 'gbc')?></label>
                        <input  id="state" name="state" type="text" value="<?php echo esc_attr($customer['state'])?>" required>       
                    </div>
                    <div>
                        <label for="country"><?php _e('Country', 'gbc')?></label>
                        <input  id="country" name="country" type="text" value="<?php echo esc_attr($customer['country'])?>" required>
                    </div>
                    <div>
                        <label for="notes"><?php _e('Notes', 'gbc')?></label>
                        <input  id="notes" name="notes" type="text" value="<?php echo esc_attr($customer['notes'])?>" placeholder="<?php _e('Notes', 'gbc')?>">
                    </div>
                </td>
	        </tr>    
        </table>
    </form> 
    <?php
}



function gbc_customer_view_meta_box($customer)
{
    ?>
    <fieldset disabled="disabled">
        <table class="form-table">
            <tr>
		        <th><?php _e('Customer Data', 'gbc')?></th>
                <td class="form-custom">
                    <div>
                        <label for="name"><?php _e('Name', 'gbc')?></label>
                        <input  id="name" name="name" type="text" value="<?php echo esc_attr($customer['name'])?>" required>
                    </div> 
                    <div>
                        <label for="name"><?php _e('Name', 'gbc')?></label>
                        <input  id="name" name="name" type="text" value="<?php echo esc_attr($customer['name'])?>" required>
                    </div>
                    <div>
                        <label for="lastname"><?php _e('Last Name', 'gbc')?></label>
                        <input  id="lastname" name="lastname" type="text" value="<?php echo esc_attr($customer['lastname'])?>" required>
                    </div>
                    <div>
                        <label for="company"><?php _e('Company', 'gbc')?></label>
                        <input  id="company" name="company" type="text" value="<?php echo esc_attr($customer['company'])?>" >
                    </div>
                    <div>
                        <label for="phone1"><?php _e('Phone 1', 'gbc')?></label>
                        <input  id="phone1" name="phone1" type="tel" value="<?php echo esc_attr($customer['phone1'])?>" required>
                    </div>
                    <div>                
                        <label for="phone2"><?php _e('Phone 2', 'gbc')?></label>
                        <input  id="phone2" name="phone2" type="tel" value="<?php echo esc_attr($customer['phone2'])?>">
                    </div>
                    <div>                
                        <label for="email"><?php _e('Email', 'gbc')?></label>
                        <input  id="email" name="email" type="email" value="<?php echo esc_attr($customer['email'])?>" required>
                    </div>
                    <div>
                        <label for="cif"><?php _e('CIF', 'gbc')?></label>
                        <input  id="cif" name="cif" type="tel" value="<?php echo esc_attr($customer['cif'])?>">
                    </div>
                    <div>                
                        <label for="vat"><?php _e('VAT', 'gbc')?></label> 
                        <input id="vat" name="vat" type="text" value="<?php echo esc_attr($customer['vat'])?>">
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <table class="form-table">
            
            <tr>
                <th><?php _e('Address Data', 'gbc')?></th>
                <td class="form-custom">
                    <div>                
                        <label for="address"><?php _e('Address', 'gbc')?></label>
                        <input  id="address" name="address" type="text" value="<?php echo esc_attr($customer['address'])?>" required>
                    </div>
                    <div>
                        <label for="postcode"><?php _e('Postcode', 'gbc')?></label>
                        <input  id="postcode" name="postcode" type="text" value="<?php echo esc_attr($customer['postcode'])?>" required>
                    </div>
                    <div>
                        <label for="city"><?php _e('City', 'gbc')?></label>
                        <input  id="city" name="city" type="text" value="<?php echo esc_attr($customer['city'])?>" required>
                    </div>
                    <div>
                        <label for="state"><?php _e('State', 'gbc')?></label>
                        <input  id="state" name="state" type="text" value="<?php echo esc_attr($customer['state'])?>" required>       
                    </div>
                    <div>
                        <label for="country"><?php _e('Country', 'gbc')?></label>
                        <input  id="country" name="country" type="text" value="<?php echo esc_attr($customer['country'])?>" required>
                    </div>
                    <div>
                        <label for="notes"><?php _e('Notes', 'gbc')?></label>
                        <input  id="notes" name="notes" type="text" value="<?php echo esc_attr($customer['notes'])?>" placeholder="<?php _e('Notes', 'gbc')?>">
                    </div>
                </td>
	        </tr>    
        </table>
    </fieldset>     
    <?php
}