<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="woocommerce-order">

	<?php if ( $order ) : ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), $order ); ?></p>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php _e( 'Order number:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_order_number(); ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php _e( 'Date:', 'woocommerce' ); ?>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); ?></strong>
				</li>

				

				<li class="woocommerce-order-overview__total total">
					<?php _e( 'Total:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_formatted_order_total(); ?></strong>
				</li>

				
			</ul>

		<?php endif; ?>

		
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), null ); ?></p>

	<?php endif; ?>

	<?php
		global $wpdb;
		$servername = "localhost";
		$username = $wpdb->dbuser;
		$password = $wpdb->dbpassword;
		$dbname = $wpdb->dbname;
		
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		
		$orderID = $order->get_order_number();

		// $totalCommission = $order->get_total()*.03; 
		$totalCommission = 0; 
		$count = 0;
		// GET all Ordered Items
		$items = $order->get_items();
		foreach ( $items as  $item_id => $item ) {
			$product = $item->get_product();
			$terms   = get_the_terms( $product->get_id(), 'product_cat' );

			foreach ($terms as $key => $term) {
				$count += 1;
				if(strtolower($term->name == "bundle")) {
					$totalCommission += $item->get_quantity() * 500;
				} else {
					$totalCommission += ($item->get_quantity() * $product->price) * 0.03;
				}
			}
		}

		


		$upline = get_post_meta( $order->id, 'upline', true );
		$date = $order->order_date;

		$user_id;

		$sql = "SELECT ID FROM wp_users WHERE user_login='$upline'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			// output data of each row
			while($row = $result->fetch_assoc()) {
				$user_id = $row["ID"];
			}
		}
		else
			$user_id = -1;

		if($user_id != -1)
		{
			$sql = "SELECT meta_value FROM wp_usermeta WHERE meta_key='user_upline' AND user_id= $user_id";
			$my_upline;
			
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				// output data of each row
				while($row = $result->fetch_assoc()) {
					$my_upline = $row["meta_value"];
				}
			}
			else
				$my_upline = "None";

			if($my_upline != "None" && $my_upline != "none")
			{
				$sql = "INSERT INTO wp_commissions (comm_orderID, comm_amount, comm_upline, comm_timestamp) VALUES ($orderID, $totalCommission, '$my_upline', '$date')";
		
				if ($conn->query($sql) === TRUE) {
				} else {
				}
			}
		}
		
		
			$sql = "SELECT ID, user_email FROM wp_users WHERE user_login ='" . $upline . "'";
			$result = $conn->query($sql);
			$ID;
			$user_email;
			if ($result->num_rows > 0) {
				// output data of each row
				while($row = $result->fetch_assoc()) {
					$ID = $row["ID"];
					$user_email = $row["user_email"];
					//echo "$ID:$user_email";
				}

			}
			
					$sql = "UPDATE wp_postmeta SET meta_value = $ID WHERE post_id=$order->id AND meta_key='_customer_user'";
					if ($conn->query($sql) === TRUE) {
					} else {
						echo $sql;
						echo "Here";
						var_dump($conn);
					}
				
		

		//$sql = "UPDATE wp_wc_order_stats SET customer_id = wp_commissions (comm_orderID, comm_amount, comm_upline, comm_timestamp) VALUES ($orderID, $totalCommission, '$upline', '$date')";

		$conn->close();
		
		
		//global $wpdb;
		//$table = $wpdb->prefix.'wp_commissions';
		//$data = array('comm_orderId' => $orderID, 'comm_amount' => $totalCommission, 'comm_created' => getDate(), 'comm_updated' => getDate());
		//$format = array('%s','%d');
		//$wpdb->insert($table,$data,$format);
		//$my_id = $wpdb->insert_id;
		//echo $my_id;
		//$wpdb->query("INSERT INTO wp_commissions (comm_orderId, comm_amount) 
		//	VALUES ('$orderId', $totalCommission)"  );

	?>
</div>
