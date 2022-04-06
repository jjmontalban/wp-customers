<?php
class gbc_webservice
{			
	public function gbc_getProducts($shop_url, $decrypt_pass)
	{				
		$url = $shop_url . '/api/products/?display=[id,link_rewrite,name]&output_format=JSON';
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $decrypt_pass.':' )
	    )
		);
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);								
		return $array;
	}
}

function gbc_configuration()
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
            check_admin_referer('save_gbc_settings','gbc_save_settings');
            $gbc_options['option_url_ws']=sanitize_text_field($_POST['option_url_ws']);
            $gbc_options['option_pass_ws']=gbc_encryption::encrypt($_POST['option_pass_ws']);
            update_option( 'gbc_options', $gbc_options );						
        }								
        ?>

        <form method="post" action="admin.php?page=webservice">
            <?php wp_nonce_field('save_gbc_settings','gbc_save_settings');?>
            
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

function gbc_shortcode_products($atts) {		        

	$gbc_options = get_option('gbc_options');	
	$decrypt_pass = gbc_encryption::decrypt($gbc_options['option_pass_ws']);						
	$shop_url = esc_attr($gbc_options['option_url_ws']);	
	$webService = new gbc_webservice();							
	$xml = $webService->gbc_getProducts($shop_url, $decrypt_pass);		
	$resources = $xml['products'];						
	$content='<ul class="short-products">';

	if (isset($resources)){		
		foreach ($resources as $resource){		
			$name = $resource['name'][1]['value'];
			$link = $shop_url . '/' . $resource['link_rewrite'][1]['value'];		
			$content .= "<li>";					
			$content .= "<a title='".$name."' href='".$link."' target='_blank'>";																						
			$content .= $name;
			$content .= "</a><br>";																			
			$content .= "</li>";							
		}	
	}else{
		$content="";
	}			

	return $content;
}
add_shortcode('gbc_products', 'gbc_shortcode_products');


class gbc_encryption{	

	public static function encrypt($string){	
		global $wpdb;
		$key ='asasest&A2oeds3-asdwas23'.$wpdb->base_prefix.'Acunt#33ddasd_asextod2Dseprueba31';		
		$iv = '12as16as78as12as';			
		$encrypted = openssl_encrypt($string,'AES-256-CBC',$key,0,$iv); 
		
		return $encrypted; 
	} 

	public static function decrypt($string){  		
		global $wpdb;
		$key ='asasest&A2oeds3-asdwas23'.$wpdb->base_prefix.'Acunt#33ddasd_asextod2Dseprueba31';	
		$iv = '12as16as78as12as';	   	
	   	$decrypted = openssl_decrypt($string,'AES-256-CBC',$key,0,$iv); 	
		
		return $decrypted;
	}
}