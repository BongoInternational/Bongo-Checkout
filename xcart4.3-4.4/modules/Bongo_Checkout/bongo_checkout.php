<?php


if (!defined('XCART_SESSION_START')) { header("Location: ../../"); die("Access denied"); }

x_load('user'); 

include_once $xcart_dir . "/shipping/shipping.php";


define ('X_SHOW_HTTP_ERRORS', 1);



# Skip if module is disabled
if (empty($active_modules["Bongo_Checkout"]))
	return;

# Skip if cart is empty
if (@$func_is_cart_empty)
	return;

# Skip if current mode is not checkout or cart
if ($smarty->get_template_vars("main") != "cart" && !in_array($mode, array("checkout", "auth")) && !($mode == "update" && $action == "cart"))
	return;


// set userinfo to Bongo's warehouse address
$bongo_userinfo = array();
$bongo_userinfo["s_country"] = 'US';
$bongo_userinfo["s_state"] = 'CT';
$bongo_userinfo["s_zipcode"] = '06607';
$bongo_userinfo["s_city"] = 'Bridgeport';
$bongo_userinfo["s_address"] = '315 Seaview Ave.';

// use the company address that's given in the store config
$orig_address = array(
	'address' => $config['Company']['location_address'],
	'city'    => $config['Company']['location_city'],
	'state'   => $config['Company']['location_state'],
	'country' => $config['Company']['location_country'],
	'zipcode' => $config['Company']['location_zipcode']
);

$config['General']['apply_default_country'] = 'Y';
$config['General']['default_address'] = '315 Seaview Ave.';
$config['General']['default_country'] = 'US';
$config['General']['default_zipcode'] = '06607';
$config['General']['default_state'] = 'CT';
$config['General']['default_city'] = 'Bridgeport';

$config['Shipping']['enable_all_shippings'] = 'N';
//$content = func_shipper($items, $userinfo, $orig_address, $debug="Y", $cart=false);

$shipping_methods = func_get_shipping_methods_list($cart, $products, $bongo_userinfo);

$content = "";
$max_shipping = 0;
$min_shipping = 9999;

if ($shipping_methods) {
	foreach ($shipping_methods as $ship_method) {
		//$content .= print_r($ship_method);
		$rate = floatval($ship_method['rate']);
		if ($rate > $max_shipping) { $max_shipping = $rate; }
		if ($rate < $min_shipping) { $min_shipping = $rate; }
	}
}

$total_items = 0;
$split_shipping_charge = 0;

foreach ($products as $product) { $total_items += $product['amount']; }

$split_shipping_charge = $min_shipping / $total_items;
//$content .= '<br />Rate: '.$rate.'<br />Max Shipping: '.$max_shipping.' Min Shipping: '.$min_shipping;

$smarty->assign("bongo_domestic_rate", $split_shipping_charge);

?>