<?php
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart                                                                      |
| Copyright (c) 2001-2009 Ruslan R. Fazlyev <rrf@x-cart.com>                  |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS  AGREEMENT  EXPRESSES  THE  TERMS  AND CONDITIONS ON WHICH YOU MAY USE |
| THIS SOFTWARE   PROGRAM   AND  ASSOCIATED  DOCUMENTATION   THAT  RUSLAN  R. |
| FAZLIEV (hereinafter  referred to as "THE AUTHOR") IS FURNISHING  OR MAKING |
| AVAILABLE TO YOU WITH  THIS  AGREEMENT  (COLLECTIVELY,  THE  "SOFTWARE").   |
| PLEASE   REVIEW   THE  TERMS  AND   CONDITIONS  OF  THIS  LICENSE AGREEMENT |
| CAREFULLY   BEFORE   INSTALLING   OR  USING  THE  SOFTWARE.  BY INSTALLING, |
| COPYING   OR   OTHERWISE   USING   THE   SOFTWARE,  YOU  AND  YOUR  COMPANY |
| (COLLECTIVELY,  "YOU")  ARE  ACCEPTING  AND AGREEING  TO  THE TERMS OF THIS |
| LICENSE   AGREEMENT.   IF  YOU    ARE  NOT  WILLING   TO  BE  BOUND BY THIS |
| AGREEMENT, DO  NOT INSTALL OR USE THE SOFTWARE.  VARIOUS   COPYRIGHTS   AND |
| OTHER   INTELLECTUAL   PROPERTY   RIGHTS    PROTECT   THE   SOFTWARE.  THIS |
| AGREEMENT IS A LICENSE AGREEMENT THAT GIVES  YOU  LIMITED  RIGHTS   TO  USE |
| THE  SOFTWARE   AND  NOT  AN  AGREEMENT  FOR SALE OR FOR  TRANSFER OF TITLE.|
| THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY GRANTED BY THIS AGREEMENT.      |
|                                                                             |
| The Initial Developer of the Original Code is Ruslan R. Fazlyev             |
| Portions created by Ruslan R. Fazlyev are Copyright (C) 2001-2009           |
| Ruslan R. Fazlyev. All Rights Reserved.                                     |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

#
# $Id: shipping.php,v 1.61.2.1 2009/11/10 07:57:31 joy Exp $
#

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }


x_load('cart', 'pack');


#
# This function creates the shipping methods/rates list
#
function func_get_bongo_shipping_methods_list($cart, $products, $userinfo, $return_all_available=false) {
	global $sql_tbl, $config, $active_modules, $single_mode, $smarty;
	global $xcart_dir;
	global $intershipper_recalc, $intershipper_rates;
	global $intershipper_error;
	global $real_time_rates;
	global $shipping_calc_service;
	global $current_carrier;
	global $login;
	global $arb_account_used;
	global $empty_other_carriers, $empty_ups_carrier;
	global $_carriers;

	if (empty($products) || $config['Shipping']['enable_shipping'] != 'Y')
		return;

	if (empty($login) && $config["General"]["apply_default_country"] != "Y" && $config["Shipping"]["enable_all_shippings"] == "Y") {
		$enable_all_shippings = true;
		$smarty->assign("force_delivery_dropdown_box", "Y");
	}

	#
	# If $enable_shipping then calculate shipping rates
	#
	$enable_shipping = ((!empty($userinfo) && !empty($login)) || $config["General"]["apply_default_country"] == "Y");

	#
	# Check if all products have free shipping
	#
	$all_products_free_shipping = true;
	if (!empty($cart["products"])) 
		foreach($cart["products"] as $k=>$product) 
			if ($product["free_shipping"] != "Y" && !$product["free_shipping_used"]) {
				$all_products_free_shipping = false;
				break;
			}

	#
	# Get the total products weight
	#
	$total_weight_shipping = func_weight_shipping_products($cart['products'],false, $all_products_free_shipping);

	#
	# Get the total products weight that is valid for rates calculation
	#
	$total_weight_shipping_valid = func_weight_shipping_products($cart['products'], true, $all_products_free_shipping);

	#
	# Get the max weight of products that is valid for rates calculation
	#
	$max_weight_shipping_valid = func_weight_shipping_products($cart['products'], true, $all_products_free_shipping, true);

	#
	# Collect products subtotal
	#
	$cart_subtotal = 0;
	if (!empty($cart["products"])) {
		foreach($cart["products"] as $k=>$product) {
			# for Advanced_Order_Management module
			if (@$product["deleted"])
				continue;

			if (!empty($active_modules["Egoods"]) && $product["distribution"] != "")
				continue;

			# Calculate total_cost and total_+weight for selection condition
			if ($product["free_shipping"] != "Y" || $config['Shipping']['free_shipping_weight_select'] == 'Y') {
				$cart_subtotal += $product["subtotal"];
			}
		}
	}

	#
	# The preparing to search the allowable shipping methods
	#
	$weight_condition = " AND weight_min<='$total_weight_shipping_valid' AND (weight_limit='0' OR weight_limit>='$max_weight_shipping_valid')";

	if (($enable_shipping || $config["Shipping"]["enable_all_shippings"] != "Y") && !$return_all_available) {
		$destination_condition = " AND destination=".(!empty($userinfo) && ((empty($userinfo["s_country"]) && $config["Company"]["location_country"] == $config["General"]["default_country"]) || $userinfo["s_country"] == $config["Company"]["location_country"]) ? "'L'" : "'I'");
	}

	if ($config["Shipping"]["realtime_shipping"] == "Y" && $enable_shipping && $intershipper_recalc != "N") {
		x_load('http');

		$default_seller_address = array(
			'city' => $config['Company']['location_city'],
			'state' => $config['Company']['location_state'],
			'country' => $config['Company']['location_country'],
			'zipcode' => $config['Company']['location_zipcode']
		);
		
		$config['General']['apply_default_country'] = 'Y';
		$config['General']['default_country'] = $userinfo['b_country'];
		$config['General']['default_zipcode'] = $userinfo['b_zipcode'];
		$config['General']['default_state'] = $userinfo['b_state'];
		$config['General']['default_city'] = $userinfo['b_city'];

		#
		# Prepare products list for packing
		#
		$items_for_packing = func_prepare_items_list($cart['products'], true, $all_products_free_shipping);

		if (!$single_mode) {
			$products_providers = func_get_products_providers($items_for_packing);
			$providers_data = array();
			if (is_array($products_providers)) {
				foreach ($products_providers as $_provider)
					$providers_data[$_provider]['seller_address'] = (func_empty_seller_address($_provider, 'N')) ? $default_seller_address : func_query_first("SELECT * FROM $sql_tbl[seller_addresses] WHERE login='$_provider'");

				foreach ($items_for_packing as $item)
					$providers_data[$item['provider']]['items'][] = $item;
			}
			
		}
		else {
			$providers_data['provider']['seller_address'] = $default_seller_address;
			$providers_data['provider']['items'] = $items_for_packing;
		}

		#
		# Get the real time shipping rates
		#
		if ($config["Shipping"]["use_intershipper"] == "Y") {
			include_once $xcart_dir."/shipping/intershipper.php";
		}
		else {
			include_once $xcart_dir."/shipping/myshipper.php";
		}

		func_https_ctl('IGNORE');

		$real_time_rates = array();
		foreach ($providers_data as $_provider=>$data) {
			$intershipper_rates = func_shipper($data['items'], $userinfo, $data['seller_address'], 'N', $cart);
			if (empty($intershipper_rates)) {
				$real_time_rates = array();
				break;
			}
			else
				$real_time_rates[$_provider] = $intershipper_rates;
		}

		# Intersect rates arrays to get list of methodid(s) available for all providers
		if (!empty($real_time_rates))
			$intershipper_rates = func_intersect_rates($real_time_rates);

		if ($empty_other_carriers == "Y") {
			$shipping = func_query("SELECT * FROM $sql_tbl[shipping] WHERE code='' AND active='Y' $destination_condition $weight_condition ORDER BY orderby");
			if (!empty($shipping)) {
				$tmp_shipping = array();
				foreach ($shipping as $k=>$v) {
					if ($v["code"]=="") {
						$v['allowed'] = $is_method_allowed = func_is_shipping_method_allowable($v["shippingid"], $userinfo, $products, $total_weight_shipping_valid, $cart_subtotal);

						if (!$return_all_available && !$is_method_allowed)
							continue;
					}

					$tmp_shipping[] = $v;
				}

				if (is_array($tmp_shipping)) {
					$tmp_cart = $cart;
					foreach ($tmp_shipping as $k=>$v) {
					#
					# Fetch shipping rate if it wasn't defined
					#
						if (!$v['allowed'])
							continue;

						$tmp_cart["shippingid"] = $v["shippingid"];
						$calc_result = func_calculate($tmp_cart, $products, $userinfo["login"], $userinfo["usertype"]);
						if (!empty($calc_result["display_shipping_cost"])) {
							$empty_other_carriers = "N";
						}
					}

					unset($tmp_cart);
				}

			}
			
		} 
		$smarty->assign("is_other_carriers_empty", $empty_other_carriers);
		$smarty->assign("is_ups_carrier_empty", $empty_ups_carrier);

		func_https_ctl('STORE');

		if (!empty($intershipper_error)){
			$smarty->assign("shipping_calc_service",$shipping_calc_service?$shipping_calc_service:"Intershipper");
			$smarty->assign("shipping_calc_error",$intershipper_error);

			$msg  = "Service: ".($shipping_calc_service?$shipping_calc_service:"Intershipper")."\n";
			$msg .= "Error: ".$intershipper_error."\n";
			$msg .= "Login: ".$login."\n";
			$msg .= "Shipping address: ".$userinfo['s_address']." ".$userinfo['s_address_2']."\n";
			$msg .= "Shipping city: ".$userinfo['s_city']."\n";
			$msg .= "Shipping state: ".$userinfo['s_statename']."\n";
			$msg .= "Shipping country: ".$userinfo['s_countryname']."\n";
			$msg .= "Shipping zipcode: ".$userinfo['s_zipcode'];
			x_log_add('SHIPPING', $msg);
		}

		$intershipper_recalc = "N";
	}

	if (!empty($active_modules["UPS_OnLine_Tools"]) && $config["Shipping"]["use_intershipper"] != "Y") {

		$condition = "";

		if ($enable_all_shippings) {
			global $ups_services;
			include $xcart_dir."/modules/UPS_OnLine_Tools/ups_shipping_methods.php";
		}

		$ups_condition = $condition;

		if ($config["Shipping"]["realtime_shipping"] == "Y" && $current_carrier == "UPS") {
			$ups_condition .= " AND $sql_tbl[shipping].code='UPS' AND $sql_tbl[shipping].service_code!=''";
		}

		if (!defined('ALL_CARRIERS'))
			$weight_condition .= $ups_condition;
	}

	$_carriers = array("UPS" => 0, "other" =>0);

	if (!empty($active_modules["UPS_OnLine_Tools"]) && $config["Shipping"]["realtime_shipping"] == "Y" && $config["Shipping"]["use_intershipper"] != "Y") {
		$_carriers["UPS"] = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping] WHERE code='UPS' AND service_code!='' AND weight_min<='$total_weight_shipping_valid' AND (weight_limit='0' OR weight_limit>='$max_weight_shipping_valid') AND active='Y'");
		$_carriers["other"] = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping] WHERE code<>'UPS' AND weight_min<='$total_weight_shipping_valid' AND (weight_limit='0' OR weight_limit>='$max_weight_shipping_valid') AND active='Y'");
		if ($_carriers["UPS"] == 0 || $_carriers["other"] == 0) {
			$current_carrier = ($_carriers["UPS"] == 0 ? "" : "UPS");
			x_session_save("current_carrier");
		}
		else {
			$smarty->assign("show_carriers_selector", "Y");
		}
	}

	if (!$enable_shipping || $config["Shipping"]["realtime_shipping"] != "Y") {
		#
		# Get ALL shipping methods according to the conditions (W/O real time)
		#
		$shipping = func_query("SELECT * FROM $sql_tbl[shipping] WHERE active='Y' $destination_condition $weight_condition ORDER BY orderby");
	}
	else {
		#
		# Gathering the defined shipping methods
		#
		$shipping = func_query ("SELECT * FROM $sql_tbl[shipping] WHERE code='' AND active='Y' $destination_condition $weight_condition ORDER BY orderby");

		if ($intershipper_rates) {
			#
			# Gathering the shipping methods from $intershipper_rates
			#
			foreach ($intershipper_rates as $intershipper_rate) {
				$ship_time = "";
				if (!empty($intershipper_rate["shipping_time"])) {
					if (is_numeric($intershipper_rate["shipping_time"]))
						$ship_time = $intershipper_rate["shipping_time"]." ".func_get_langvar_by_name("lbl_day_s", array(), false, false, true);
					else
						$ship_time = $intershipper_rate["shipping_time"];
				}

				if ($ship_time != "")
					$ship_time_column = "'".$ship_time."' AS shipping_time";
				else
					$ship_time_column = "shipping_time";

				$result = func_query_first("SELECT *, '$intershipper_rate[rate]' AS rate, '$intershipper_rate[warning]' AS warning, $ship_time_column FROM $sql_tbl[shipping] WHERE subcode='$intershipper_rate[methodid]' AND active='Y' $weight_condition ORDER BY orderby");

				if ($result) {
					$result['allowed'] = true;
					$shipping[] = $result;
				}
			}
		}

		if (is_array($shipping))
			usort($shipping, "usort_array_cmp_orderby");
	}

	if (!empty($shipping)) {
		#
		# Final preparing the shipping methods list
		#
		$tmp_shipping = array();

		if ((defined('GOOGLE_CHECKOUT_CALLBACK') || $return_all_available) && !empty($shipping)) {
			$shipping = func_rename_duplicate_shippings($shipping);
		}

		foreach ($shipping as $k=>$v) {
			if (($config["Shipping"]["realtime_shipping"]=="Y" && $v["code"]=="") || $config["Shipping"]["realtime_shipping"]!="Y") {
				#
				# Check accessibility only for defined shipping methods
				#

				$v['allowed'] = $is_method_allowed = func_is_shipping_method_allowable($v["shippingid"], $userinfo, $products, $total_weight_shipping_valid, $cart_subtotal);

				if (!$return_all_available && !$is_method_allowed)
					continue;

			}
			elseif ($config["Shipping"]["realtime_shipping"] == "Y" && $v["code"] != "" && !$enable_shipping)
				continue;

			$tmp_shipping[] = $v;
		}

		$shipping = $tmp_shipping;
		unset($tmp_shipping);

		if (is_array($shipping)) {
			$tmp_cart = $cart;
			foreach ($shipping as $k=>$v) {
				#
				# Fetch shipping rate if it wasn't defined
				#
				if (!$v['allowed'])
					continue;

				$tmp_cart["shippingid"] = $v["shippingid"];
				$calc_result = func_calculate($tmp_cart, $products, $userinfo["login"], $userinfo["usertype"]);
				$shipping[$k]["rate"] = $calc_result["display_shipping_cost"];
				$shipping[$k]["tax_cost"] = price_format($calc_result["tax_cost"]);
			}

			unset($tmp_cart);
		}

	}

	if ($arb_account_used && is_array($shipping)) {
		foreach ($shipping as $v) {
			if ($v["code"] == "ARB" && $v["shippingid"] == $cart["shippingid"]) {
				$smarty->assign("arb_account_used", true);
				break;
			}
		}
	}

	if ($shipping)
		return $shipping;
	else
		return;
}
?>