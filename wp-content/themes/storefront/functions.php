<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
		require 'inc/nux/class-storefront-nux-starter-content.php';
	}
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */


add_action('woocommerce_before_order_notes', 'wps_add_select_checkout_field');
function wps_add_select_checkout_field( $checkout ) {
	$users = get_users();
	$userDropDown = array();
	$array_ = array();
	$user_names = array();
	$user_names['None'] = 'none';

	foreach ( $users as $user ) {
		$user_names[$user->user_login] = get_user_meta($user->ID, 'reg_first_name', true ) . " " . get_user_meta($user->ID, 'reg_last_name',true );//$user->user_login;
	}

	if( current_user_can('editor') || current_user_can('administrator')){
		woocommerce_form_field( 'upline', array(
			'type'          => 'select',
			'class'         => array( 'wps-drop' ),
			'label'         => __( 'Customer' ),
			'options'       => $user_names,
				  
		 ),
		 $checkout->get_value( 'upline' ));
	}
	else{
		$user_names = array();
		//$user_names[$current_user->user_login] = $current_user->user_login;
		woocommerce_form_field( 'upline', array(
			'type'          => 'select',
			'class'         => array( 'wps-drop' ),
			'label'         => __( 'Customer' ),
			'options'       => $user_names,
				  
		 ),
		 $checkout->get_value( 'upline' ));
	}
	 
}



add_action('woocommerce_checkout_update_order_meta', 'wps_select_checkout_field_update_order_meta');
function wps_select_checkout_field_update_order_meta( $order_id ) {
  if ($_POST['upline']) update_post_meta( $order_id, 'upline', esc_attr($_POST['upline']));
}

add_action( 'rest_api_init', function ( $server ) {
    $server->register_route( 'generate', '/generate/startDate=(?P<startDate>[a-zA-Z0-9-]+)/endDate=(?P<endDate>[a-z0-9 .\-]+)/user=(?P<user>[a-z0-9 .\-_]+)/type=(?P<type>[a-z0-9 .\-]+)', array(
        'methods'  => 'GET',
        'callback' => function ($data) {
		/*
			mysql_connect('hostname', 'username', 'password');
			mysql_select_db('dbname');
			$qry = mysql_query("SELECT * FROM tablename");
			$data = "";
			while($row = mysql_fetch_array($qry)) {
			$data .= $row['field1'].",".$row['field2'].",".$row['field3'].",".$row['field4']."\n";
			}
*/			global $wpdb;

			$servername = "localhost";
			$username = $wpdb->dbuser;
			$password = $wpdb->dbpassword;
			$dbname = $wpdb->dbname;
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];

			// Create connection
			$con=mysqli_connect($servername,$username,$password,$dbname);

			if (mysqli_connect_errno())
			{
				echo "Failed to connect to MySQL: " . mysqli_connect_error();
			}

			if($data["type"] == "1")
			{
				if($data['user'] == "-1")
				{


					$sql = "SELECT comm_upline, comm_timestamp, SUM(comm_amount) AS comm_amount FROM wp_commissions 
						WHERE comm_timestamp >= '" . $startDate . "' AND comm_timestamp <= '" . $endDate . "' GROUP BY comm_upline;" ;
					$result = mysqli_query($con,$sql);

					$rows = array();
					while($r = mysqli_fetch_array($result)) {
						$rows[] = $r;
					}
					mysqli_close($con);
					return $rows;
					//return json_encode($rows);

				}
				
				else
				{

					$sql = "SELECT comm_upline, comm_timestamp, comm_amount FROM wp_commissions 
						WHERE comm_timestamp >= '" . $startDate . "' AND comm_timestamp <= '" . $endDate . "' AND comm_upline = '" . $data['user'] . "'" ;
					$result = mysqli_query($con,$sql);

					$rows = array();
					while($r = mysqli_fetch_array($result)) {
						$rows[] = $r;
					}
					mysqli_close($con);
					return $rows;
					//return json_encode($rows);

				}
			}
			else
			{
				if($data['user'] == "-1")
				{


					$sql = "Select DISTINCT(meta_value) FROM wp_postmeta, wp_posts WHERE wp_postmeta.post_id=wp_posts.ID AND wp_posts.post_date >= '$startDate' AND wp_posts.post_date <= '$endDate' AND wp_postmeta.meta_key='_customer_user'";
					$result = mysqli_query($con,$sql);
					$distinctUsers = array();
					while($r = mysqli_fetch_array($result)) {
						$distinctUsers[] = $r['meta_value'];
					}
					$response = array();

					foreach($distinctUsers as $distinctUser)
					{
						$sql = "Select post_id FROM wp_postmeta, wp_posts WHERE wp_postmeta.post_id=wp_posts.ID AND wp_posts.post_date >= '$startDate' AND wp_posts.post_date <= '$endDate' AND wp_postmeta.meta_key='_customer_user' AND wp_postmeta.meta_value=$distinctUser";
						$result = mysqli_query($con,$sql);

						$post_ids = array();
						while($r = mysqli_fetch_array($result)) {
							$post_ids[] = $r['post_id'];
						}

						$runningTotal = 0;
						foreach($post_ids as $post_id)
						{
							$sql = "Select meta_value FROM wp_postmeta WHERE meta_key='_order_total' AND post_id=$post_id";
							$result = mysqli_query($con,$sql);
							$total = mysqli_fetch_assoc($result);
							$runningTotal += $total['meta_value'];

						}
						$sql = "Select user_login FROM wp_users WHERE ID=$distinctUser";
						$result = mysqli_query($con,$sql);
						$userName = mysqli_fetch_assoc($result);

						$response[] = array('comm_upline' => $userName['user_login'], 'comm_timestamp' => 'N/A','comm_amount' => $runningTotal, 'comm_items' => 'N/A', 'comm_ids' => 'N/A');

						$runningTotal = 0;
					}
					
					
					mysqli_close($con);
					return $response;
		
				}
			
				else
				{
					$user = $data['user'];
					$sql = "Select post_id FROM wp_postmeta, wp_posts WHERE wp_postmeta.post_id=wp_posts.ID AND wp_posts.post_date >= '$startDate' AND wp_posts.post_date <= '$endDate' AND wp_postmeta.meta_key='_customer_user' AND wp_postmeta.meta_value=(Select ID FROM wp_users WHERE user_login='$user')";
					$result = mysqli_query($con,$sql);

					$post_ids = array();
					while($r = mysqli_fetch_array($result)) {
						$post_ids[] = $r['post_id'];
					}
					$response = array();
					foreach($post_ids as $post_id)
					{
						$sql = "Select meta_value FROM wp_postmeta WHERE meta_key='_order_total' AND post_id=$post_id";
						$result = mysqli_query($con,$sql);
						$total = mysqli_fetch_assoc($result);
						
						$sql = "Select post_date FROM wp_posts WHERE ID=$post_id";
						$result = mysqli_query($con,$sql);
						$date = mysqli_fetch_assoc($result);
						
						$sql = "Select order_item_name, meta_value FROM wp_woocommerce_order_items, wp_woocommerce_order_itemmeta WHERE 
							wp_woocommerce_order_items.order_item_id=wp_woocommerce_order_itemmeta.order_item_id AND
							wp_woocommerce_order_items.order_id=$post_id AND wp_woocommerce_order_itemmeta.meta_key='_qty'";
						$result = mysqli_query($con,$sql);
						$item_line = "";
						while($r = mysqli_fetch_array($result)) {
							$item_line .= $r['order_item_name']."(".$r['meta_value'].") ";
						}
						$response[] = array('comm_upline' => $data['user'], 'comm_timestamp' => $date['post_date'],'comm_amount' => $total['meta_value'], 'comm_items' => $item_line, 'comm_ids' => $post_id);
					}

					
					mysqli_close($con);
					return $response;
	
				}
			}
        },
    ) );
} );

function add_upline_register_field() {
	$users = get_users( array( 'fields' => array( 'ID', 'user_login' ) ) );
	$userDropDown = array();
	$array_ = array();
	$user_names = array();
	$user_names['None'] = 'none';
	foreach ( $users as $user ) {
		$user_names[$user->user_login] = "2019EUROASIA-$user->ID";
	}
	woocommerce_form_field( 'user_upline', array(
	    'type'          => 'select',
	    'class'         => array( 'wps-drop' ),
	    'label'         => 'Upline' ,
	    'options'       => $user_names,
	    
 	));
	
	
}
add_action( 'woocommerce_register_form_start', 'add_upline_register_field' );

function wooc_save_upline_register_field( $customer_id ) {
    if ( isset( $_POST['user_upline'] ) ) {
                 // Phone input filed which is used in WooCommerce
                 update_user_meta( $customer_id, 'user_upline', sanitize_text_field( $_POST['user_upline'] ) );
          }
      
}
add_action( 'woocommerce_created_customer', 'wooc_save_upline_register_field' );

function iconic_remove_password_strength() {
    wp_dequeue_script( 'wc-password-strength-meter' );
}
add_action( 'wp_print_scripts', 'iconic_remove_password_strength', 10 );


add_action( 'woocommerce_register_form_end', 'bbloomer_add_extras_woo_account_registration' );
 
function bbloomer_add_extras_woo_account_registration() {
	echo '<div class="cw_custom_class"><h3>'.__('For EAPI Use Only ').'</h3>';

	

	woocommerce_form_field( 'reg_eapi_date', array(
	    'type'          => 'date',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Date Received' ,
	    
	 ));

	
	woocommerce_form_field( 'reg_eapi_received_by', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Payment Accepted By' ,
	    
	 ));



	woocommerce_form_field( 'reg_eapi_csr', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Customer Service Representative' ,
	    
	 ));

	echo 'Area:';

	echo '<table><tr>';

	echo '<td>';
	woocommerce_form_field( 'reg_eapi_nl1', array(
        'type'          => 'checkbox',
        'label'         => __('NORTH LUZON 1'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_nl2', array(
        'type'          => 'checkbox',
        'label'         => __('NORTH LUZON 2'),
        'required'  => false,
	));

	woocommerce_form_field( 'reg_eapi_cl', array(
        'type'          => 'checkbox',
        'label'         => __('CENTRAL LUZON'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_sl1', array(
        'type'          => 'checkbox',
        'label'         => __('SOUTH LUZON 1'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_sl2', array(
        'type'          => 'checkbox',
        'label'         => __('SOUTH LUZON 2'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_sl3', array(
        'type'          => 'checkbox',
        'label'         => __('SOUTH LUZON 3'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_gma', array(
        'type'          => 'checkbox',
        'label'         => __('GMA NORTH'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_gmac', array(
        'type'          => 'checkbox',
        'label'         => __('GMA CENTRAL'),
        'required'  => false,
	));
	echo '</td>';


	echo '<td>';
	woocommerce_form_field( 'reg_eapi_gmac', array(
        'type'          => 'checkbox',
        'label'         => __('GMA SOUTH'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_gmae', array(
        'type'          => 'checkbox',
        'label'         => __('GMA EAST'),
        'required'  => false,
	));

	woocommerce_form_field( 'reg_eapi_ev', array(
        'type'          => 'checkbox',
        'label'         => __('EASTERN VISAYAS'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_cv', array(
        'type'          => 'checkbox',
        'label'         => __('CENTRAL VISAYAS'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_wv1', array(
        'type'          => 'checkbox',
        'label'         => __('WESTERN VISAYAS 1'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_wv2', array(
        'type'          => 'checkbox',
        'label'         => __('WESTERN VISAYAS 2'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_nm', array(
        'type'          => 'checkbox',
        'label'         => __('NORTHERN MINDANAO'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_eapi_sm', array(
        'type'          => 'checkbox',
        'label'         => __('SOUTHERN MINDANAO'),
        'required'  => false,
	));
	echo '</td>';

	echo '</tr></table>';

	woocommerce_form_field( 'reg_or', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'OR #' ,
	    
	 ));

	 woocommerce_form_field( 'reg_processed_by', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'PROCESSED BY' ,
	    
	 ));


	echo '</div>';


}


add_action( 'woocommerce_register_form_start', 'bbloomer_add_name_woo_account_registration' );
 
function bbloomer_add_name_woo_account_registration() {
	woocommerce_form_field( 'reg_first_name', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'First Name' ,
	    
	 ));
	 woocommerce_form_field( 'reg_last_name', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Last Name' ,
	    
 	));
	 woocommerce_form_field( 'reg_contact', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Telephone/s' ,
	    
	 ));
	 echo '<div class="cw_custom_class"><h3>'.__('Mode of Payment: ').'</h3>';
	 woocommerce_form_field( 'reg_deposit', array(
        'type'          => 'checkbox',
        'label'         => __('Bank Deposit'),
        'required'  => false,
	));
	woocommerce_form_field( 'reg_cash', array(
        'type'          => 'checkbox',
        'label'         => __('Cash'),
        'required'  => false,
	));

	woocommerce_form_field( 'reg_card', array(
        'type'          => 'checkbox',
        'label'         => __('Credit Card | Type: '),
        'required'  => false,
	));


	echo '<table><tr>';
	

	echo '<td>';
	woocommerce_form_field( 'reg_card_visa', array(
        'type'          => 'checkbox',
        'label'         => __('Visa'),
        'required'  => false,
	));
	echo '</td>';


	echo '<td>';
	woocommerce_form_field( 'reg_card_master', array(
        'type'          => 'checkbox',
        'label'         => __('Mastercard'),
        'required'  => false,
	));
	echo '</td>';
	echo '</tr><tr>';
	echo '<td>';

	woocommerce_form_field( 'reg_card_no', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Credit Card No.' ,
	    
	 ));

	echo '</td>';

	echo '<td>';

	woocommerce_form_field( 'reg_card_expiry', array(
	    'type'          => 'text',
	    'class'         => array( 'wps-input' ),
	    'label'         => 'Expiry Date' ,
	    
	 ));

	echo '</td>';

	echo '</tr></table>';


	echo '</div>';
}

function wooc_save_name_register_field( $customer_id ) {
    if ( isset( $_POST['reg_first_name']) && isset ($_POST['reg_last_name']) ) {
                 // Phone input filed which is used in WooCommerce
				 update_user_meta( $customer_id, 'reg_first_name', sanitize_text_field( $_POST['reg_first_name'] ) );
				 update_user_meta( $customer_id, 'reg_last_name', sanitize_text_field( $_POST['reg_last_name'] ) );
				 update_user_meta( $customer_id, 'reg_contact', sanitize_text_field( $_POST['reg_contact'] ) );
				 update_user_meta( $customer_id, 'reg_deposit', sanitize_text_field( $_POST['reg_deposit'] ) );
				 update_user_meta( $customer_id, 'reg_cash', sanitize_text_field( $_POST['reg_cash'] ) );
				 update_user_meta( $customer_id, 'reg_card', sanitize_text_field( $_POST['reg_card'] ) );
				 update_user_meta( $customer_id, 'reg_card_no', sanitize_text_field( $_POST['reg_card_no'] ) );
				 update_user_meta( $customer_id, 'reg_card_expiry', sanitize_text_field( $_POST['reg_card_expiry'] ) );



          }
}
add_action( 'woocommerce_created_customer', 'wooc_save_name_register_field' );


//add_filter( 'woocommerce_register_form_start' , 'custom_override_registration_fields' );

function custom_override_registration_fields( $fields ) {
	unset($fields['billing']['billing_first_name']);
	return $fields;
}


add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
 
function custom_override_checkout_fields( $fields ) {
    unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    unset($fields['order']['order_comments']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_city']);
    return $fields;
}
