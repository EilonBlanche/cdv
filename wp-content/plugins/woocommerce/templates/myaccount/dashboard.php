<script type="text/javascript">



var csv = [];

function export_to_csv()
{


	let csvContent = "data:text/csv;charset=utf-8," 
    + csv.map(e => e.join(",")).join("\n");
	var encodedUri = encodeURI(csvContent);
	var link = document.createElement("a");
	link.setAttribute("href", encodedUri);
	link.setAttribute("download", "reports.csv");
	document.body.appendChild(link); // Required for FF

	link.click();
}

function test() {
	csv = [];
	//alert("Hello");
	var oReq = new XMLHttpRequest();

	oReq.addEventListener("load", reqListener);
	var e = document.getElementById("userId");
	var strUser = e.options[e.selectedIndex].value;
	var rep = document.getElementById("reportType");
	var reportType = rep.options[rep.selectedIndex].value;

	oReq.open("GET", window.location.origin + "/EuroAsia/wp-json/generate/startDate=" + document.getElementById("startDate").value +
		"/endDate=" + document.getElementById("endDate").value + "/user=" + strUser + "/type=" + reportType);
	oReq.send();
	document.getElementById("cover").style.visibility = 'visible';
	return null;   // The function returns the product of p1 and p2
}

function reqListener () {
	document.getElementById("cover").style.visibility = 'hidden';

	console.log(this.responseText.replace(/\\/g, ""));
	var json = this.responseText.replace(/\\/g, "");
	//console.log(JSON.parse(JSON.parse(this.responseText).data));
	
	
	var rep = document.getElementById("reportType");
	var reportType = rep.options[rep.selectedIndex].value;
	
	var e = document.getElementById("userId");
	var strUser = e.options[e.selectedIndex].value;


	var table = document.getElementById("commissionsTable");
	for(var i = table.rows.length - 1; i > 0; i--)
	{
		table.deleteRow(i);
	}



	if(strUser == "-1")
	{
		document.getElementById('itemColumn').style.visibility = 'hidden';
		document.getElementById('dateColumn').style.visibility = 'hidden';

		var table = document.getElementById("commissionsTable");
		var body = table.createTBody();
		var loop = 0;
		csv.push(["USER", "AMOUNT"]);

		JSON.parse(json).forEach(
			function(element) 
			{
				//console.log(element.comm_upline); 
				var row = body.insertRow(loop);
				var cell = row.insertCell(0);
				cell.innerHTML = element.comm_upline;

				cell = row.insertCell(1);
				cell.innerHTML = element.comm_amount;
				csv.push([element.comm_upline, element.comm_amount]);

				
				loop++;
			})  
		
		
	}
	
	else
	{
		document.getElementById('itemColumn').style.visibility = 'visible';
		document.getElementById('dateColumn').style.visibility = 'visible';
		document.getElementById("itemColumn");
		csv.push(["USER", "AMOUNT", "DATE", "ITEMS"]);

		var table = document.getElementById("commissionsTable");
		var body = table.createTBody();
		var loop = 0;
		JSON.parse(json).forEach(
			function(element) 
			{
				
				//console.log(element.comm_upline); 
				var row = body.insertRow(loop);
				var cell = row.insertCell(0);
				cell.innerHTML = element.comm_upline;

				cell = row.insertCell(1);
				cell.innerHTML = element.comm_amount;

				cell = row.insertCell(2);
				cell.innerHTML = element.comm_timestamp;

				if(reportType != 1)
				{
					cell = row.insertCell(3);
					cell.innerHTML = element.comm_items;
				}	
				csv.push([element.comm_upline, element.comm_amount, element.comm_timestamp, element.comm_items]);

				loop++;
			})  
		
		
	}
	
}
</script>

<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
	add_settings_field( 'example_date_picker', 'Example Date Picker', 'pu_display_date_picker', 'pu_theme_options.php', 'pu_date_section', array() );

	add_action( 'admin_enqueue_scripts', 'enqueue_date_picker' );

}
?>


<p><?php
	/* translators: 1: user display name 2: logout url */
	printf(
		__( '<b>USER ID: 2019EUROASIA-' . $current_user->ID .
			'<br/>Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ),
		'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
		esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) )
	);
?></p>

<p><?php
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
	
	
	
	$sql = "SELECT SUM(comm_amount) AS amount FROM wp_commissions WHERE comm_upline = '$current_user->user_login'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "Commission: P" . $row["amount"];
		}
	} else {
	}
	
	$sql = "SELECT meta_value FROM wp_users, wp_usermeta WHERE wp_users.ID=wp_usermeta.user_id AND wp_usermeta.meta_key = 'user_upline' AND wp_users.user_login = '$current_user->user_login'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "<br/>My Upline: " . $row["meta_value"];
		}
	} else {
	}


	if( current_user_can('editor') || current_user_can('administrator')){

		echo '<br/>Generate Reports <br />';
		echo '<select id="reportType"><option value="0">Orders</option><option value="1">Commissions</option></select> &nbsp;';
		echo '<select id="userId"><option value="-1">All</option>';
		$users = get_users();


		foreach ( $users as $user ) {
			echo '<option value="'. $user->user_login . '">'.  get_user_meta($user->ID, 'reg_first_name', true ) . " " . get_user_meta($user->ID, 'reg_last_name',true ) .'</option>';
		}
		echo '</select> &nbsp;';
		echo 'Start Date: &nbsp;<input type="date" id="startDate" name="start[datepicker]" value="" class="example-datepicker" /> &nbsp;';
		echo 'End Date: &nbsp;<input type="date" id="endDate" name="end[datepicker]" value="" class="example-datepicker" />';
		echo '<br/><br/><button onclick="test()">Generate</button>&nbsp;<button onclick="export_to_csv()">CSV</button>';
		
	}


	$conn->close();
	
	function enqueue_date_picker(){
		wp_enqueue_script(
	'field-date-js', 
	'Field_Date.js', 
	array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
	time(),
	true
	);  

	wp_enqueue_style( 'jquery-ui-datepicker' );
}
?></p>
<div id="cover" style="position:absolute;background:url('') no-repeat scroll center center rgba(255, 255, 255, 0.5);color:#000000;visibility:hidden" >Loading...</div>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

echo '
<table id="commissionsTable" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">User</span></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">Amount</span></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number" id="dateColumn"><span class="nobr">Date</span></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number" id="itemColumn"><span class="nobr">Items</span></th>

			</tr>
		</thead>

		<tbody id="commissionsTableBody">
							
		</tbody>
	</table>';