<?php
class gbc_webservice
{			
	public function gbc_getProducts($shop_url, $decrypt_pass)
	{				
		$url = $shop_url.'/api/products/?display=[id,link_rewrite,name]&output_format=JSON';
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