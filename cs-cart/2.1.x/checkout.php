<?php
/***************************************************************************
*                                                                          *
*    Copyright (c) 2004 Simbirsk Technologies Ltd. All rights reserved.    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/


//
// $Id: checkout.php 11690 2011-01-24 12:02:30Z alexions $
//

if ( !defined('AREA') ) { die('Access denied'); }

fn_define('CHECKOUT', true);
fn_define('ORDERS_TIMEOUT', 60);

// Cart is empty, create it
if (empty($_SESSION['cart'])) {
	fn_clear_cart($_SESSION['cart']);
}

$cart = & $_SESSION['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$_suffix = '';

	//
	// Add product to cart
	//
	if ($mode == 'add') {
		if (empty($auth['user_id']) && Registry::get('settings.General.allow_anonymous_shopping') != 'Y') {
			return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode($_SERVER['HTTP_REFERER']));
		}

		// Add to cart button was pressed for single product on advanced list
		if (!empty($dispatch_extra)) {
			if (empty($_REQUEST['product_data'][$dispatch_extra]['amount'])) {
				$_REQUEST['product_data'][$dispatch_extra]['amount'] = 1;
			}
			foreach ($_REQUEST['product_data'] as $key => $data) {
				if ($key != $dispatch_extra && $key != 'custom_files') {
					unset($_REQUEST['product_data'][$key]);
				}
			}
		}

		$prev_cart_products = empty($cart['products']) ? array() : $cart['products'];

		fn_add_product_to_cart($_REQUEST['product_data'], $cart, $auth);
		fn_save_cart_content($cart, $auth['user_id']);

		$previous_state = md5(serialize($cart['products']));
		fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);

		if (md5(serialize($cart['products'])) != $previous_state) {
			$product_cnt = 0;
			$added_products = array();
			foreach ($cart['products'] as $key => $data) {
				if (empty($prev_cart_products[$key]) || !empty($prev_cart_products[$key]) && $prev_cart_products[$key]['amount'] != $data['amount']) {
					$added_products[$key] = $data;
					$added_products[$key]['product_option_data'] = fn_get_selected_product_options_info($data['product_options']);
					if (!empty($prev_cart_products[$key])) {
						$added_products[$key]['amount'] = $data['amount'] - $prev_cart_products[$key]['amount'];
					}
					$product_cnt += $added_products[$key]['amount'];
				}
			}

			if (!empty($added_products)) {
				$view->assign('added_products', $added_products);
				if (Registry::get('settings.DHTML.ajax_add_to_cart') != 'Y' && Registry::get('settings.General.redirect_to_cart') == 'Y') {
					$view->assign('continue_url', (!empty($_REQUEST['redirect_url']) && empty($_REQUEST['appearance']['details_page'])) ? $_REQUEST['redirect_url'] : $_SESSION['continue_url']);
				}
				$msg = $view->display('views/products/components/product_notification.tpl', false);
				fn_set_notification('P', fn_get_lang_var($product_cnt > 1 ? 'products_added_to_cart' : 'product_added_to_cart'), $msg, 'I');
				$cart['recalculate'] = true;
			} else {
				fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('product_in_cart'));
			}
		}

		if (defined('AJAX_REQUEST')) {
			$view->assign('cart_amount', $cart['amount']);
			$view->assign('cart_subtotal', $cart['display_subtotal']);
			$view->assign('force_items_deletion', true);

			// The redirection is made in order to update the page content to see changes made in the cart when adding a product to it from the 'view cart' or 'checkout' pages. 
			if (strpos($_SERVER['HTTP_REFERER'], 'dispatch=checkout.cart') || strpos($_SERVER['HTTP_REFERER'], 'dispatch=checkout.checkout') || strpos($_SERVER['HTTP_REFERER'], 'dispatch=checkout.summary')) {
				$ajax->assign('force_redirection', $_SERVER['HTTP_REFERER']);
			}

			$view->display('views/checkout/components/cart_status.tpl');
			exit;
		}

		$_suffix = '.cart';
		
		if (Registry::get('settings.DHTML.ajax_add_to_cart') != 'Y' && Registry::get('settings.General.redirect_to_cart') == 'Y') {
			if (!empty($_REQUEST['redirect_url']) && empty($_REQUEST['appearance']['details_page'])) {
				$_SESSION['continue_url'] = $_REQUEST['redirect_url'];
			}
			unset($_REQUEST['redirect_url']);
		}
	}

	//
	// Update products quantity in the cart
	//
	if ($mode == 'update') {
		if (!empty($_REQUEST['cart_products'])) {
			foreach ($_REQUEST['cart_products'] as $_key => $_data) {
				if (empty($_data['amount']) && !isset($cart['products'][$_key]['extra']['parent'])) {
					fn_delete_cart_product($cart, $_key);
				}
			}
			fn_add_product_to_cart($_REQUEST['cart_products'], $cart, $auth, true);
			fn_save_cart_content($cart, $auth['user_id']);
		}

		fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('text_products_updated_successfully'));

		// Recalculate cart when updating the products
		$cart['recalculate'] = true;

		$_suffix = ".$_REQUEST[redirect_mode]";
	}

	//
	// Estimate shipping cost
	//
	if ($mode == 'shipping_estimation') {

		$customer_location = empty($_REQUEST['customer_location']) ? array() : $_REQUEST['customer_location'];
		foreach ($customer_location as $k => $v) {
			$cart['user_data']['s_' . $k] = $v;
		}
		$_SESSION['customer_loc'] = $customer_location;

		$cart['recalculate'] = true;

		list ($cart_products, $_SESSION['shipping_rates']) = fn_calculate_cart_content($cart, $auth, 'A', true, 'F', true);

		$view->assign('shipping_rates', $_SESSION['shipping_rates']);
		$view->assign('cart', $cart);
		$view->assign('cart_products', array_reverse($cart_products, true));
		$view->assign('location', empty($_REQUEST['location']) ? 'cart' : $_REQUEST['location']);
		$view->assign('additional_id', empty($_REQUEST['additional_id']) ? '' : $_REQUEST['additional_id']);

		if (defined('AJAX_REQUEST')) {
			if (fn_is_empty($cart_products) && fn_is_empty($_SESSION['shipping_rates'])) {
				$ajax->assign_html('shipping_estimation_sidebox' . (empty($_REQUEST['additional_id']) ? '' : '_' . $_REQUEST['additional_id']), fn_get_lang_var('no_rates_for_empty_cart'));
			} else {
				$view->display(empty($_REQUEST['location']) ? 'views/checkout/components/checkout_totals.tpl' : 'views/checkout/components/shipping_estimation.tpl');
			}
			exit;
		}

		$_suffix = '.' . (empty($_REQUEST['current_mode']) ? 'cart' : $_REQUEST['current_mode']) . '?show_shippings=Y';
	}

	if ($mode == 'update_shipping') {
		if (!empty($_REQUEST['shipping_ids'])) {
			fn_checkout_update_shipping($cart, $_REQUEST['shipping_ids']);
		}

		$_suffix = ".$_REQUEST[redirect_mode]";
	}

	// Apply Discount Coupon
	if ($mode == 'apply_coupon') {

		$cart['pending_coupon'] = $_REQUEST['coupon_code'];

		$_suffix = ".$_REQUEST[redirect_mode]";
	}

	if ($mode == 'add_profile') {
	
		if (Registry::get('settings.Image_verification.use_for_register') == 'Y' && fn_image_verification('register', empty($_REQUEST['verification_answer']) ? '' : $_REQUEST['verification_answer']) == false) {
			fn_save_post_data();
			
			return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout?login_type=register");
		}

		
		if ($res = fn_update_user(0, $_REQUEST['user_data'], $auth, false, true)) {
			$profile_fields = fn_get_profile_fields('O');
			
			$step = 'step_two';
			if (empty($profile_fields['B']) && empty($profile_fields['S'])) {
				$step = 'step_three';
			}
			
			$suffix = '?edit_step=' . $step;
		} else {
			$suffix = '?login_type=register';
		}

		return array(CONTROLLER_STATUS_OK, "checkout.checkout" .  $suffix);
	}

	if ($mode == 'customer_info') {
		if (Registry::get('settings.General.disable_anonymous_checkout') == 'Y' && empty($cart['user_data']['email']) && Registry::get('settings.Image_verification.use_for_checkout') == 'Y' && fn_image_verification('checkout', empty($_REQUEST['verification_answer']) ? '' : $_REQUEST['verification_answer']) == false) {
			fn_save_post_data();

			return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout?login_type=guest");
		}

		$profile_fields = fn_get_profile_fields('O');
		$user_profile = array();

		if (!empty($_REQUEST['user_data'])) {
			if (empty($auth['user_id']) && !empty($_REQUEST['user_data']['email'])) { 
				$email_exists = db_get_field("SELECT email FROM ?:users WHERE email = ?s", $_REQUEST['user_data']['email']); 
				if (!empty($email_exists)) { 
					fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_user_exists')); 
					fn_save_post_data(); 

					return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout"); 
				} 
			} 

			$user_data = $_REQUEST['user_data'];
			!empty($_REQUEST['copy_address']) ? $_REQUEST['ship_to_another'] = '' : $_REQUEST['ship_to_another'] = 'Y';

			unset($user_data['user_type']);
			if (!empty($cart['user_data'])) {
				$cart['user_data'] = fn_array_merge($cart['user_data'], $user_data);
			} else {
				$cart['user_data'] = $user_data;
			}

			// Fill shipping info with billing if needed
			if (empty($_REQUEST['ship_to_another'])) {
				fn_fill_address($cart['user_data'], $profile_fields);
			}

			// Add descriptions for titles, countries and states
			fn_add_user_data_descriptions($cart['user_data']);

			// Update profile info (if user is logged in)
			$cart['profile_registration_attempt'] = false;
			$cart['ship_to_another'] = !empty($_REQUEST['ship_to_another']);

			if (!empty($auth['user_id'])) {
				// Check email
				$email_exists = db_get_field("SELECT email FROM ?:users WHERE email = ?s AND user_id != ?i", $cart['user_data']['email'], $auth['user_id']);
				if (!empty($email_exists)) {
					fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_user_exists'));
					$cart['user_data']['email'] = '';
				} else {
					db_query('UPDATE ?:users SET ?u WHERE user_id = ?i', $cart['user_data'], $auth['user_id']);

					if (!empty($cart['profile_id'])) {
						db_query('UPDATE ?:user_profiles SET ?u WHERE profile_id = ?i', $cart['user_data'], $cart['profile_id']);
					} else {
						$cart['profile_id'] = db_query('INSERT INTO ?:user_profiles ?e', $cart['user_data']);
					}

					fn_store_profile_fields($cart['user_data'], $cart['profile_id'], 'P');

					
				}

			} elseif (Registry::get('settings.General.disable_anonymous_checkout') == 'Y' || !empty($user_data['password1'])) {
				$cart['profile_registration_attempt'] = true;
				$user_profile = fn_update_user(0, $cart['user_data'], $auth, $cart['ship_to_another'], true);
				if ($user_profile === false) {
					unset($cart['user_data']['email'], $cart['user_data']['user_login']);
				} else {
					$cart['profile_id'] = $user_profile[1];
				}
			} else {
				$profile_fields = fn_get_profile_fields('O', $auth);
				if (count($profile_fields['C']) > 1) {
					return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout&edit_step=step_one"); 
				}
			}
		}

		$cart['recalculate'] = true;

		fn_save_cart_content($cart, $auth['user_id']);
		
		$step = 'step_two';
		if (empty($profile_fields['B']) && empty($profile_fields['S'])) {
			$step = 'step_three';
		}
		
		$_suffix = '.checkout?edit_step=' . $step;
	}

	if ($mode == 'order_info') {
		if (isset($_REQUEST['payment_id'])) {
			$payment_id = !empty($_REQUEST['payment_id']) ? (int) $_REQUEST['payment_id'] : 0;
			$cart['payment_id'] = $payment_id;
			$cart['payment_updated'] = true;
			fn_update_payment_surcharge($cart);
		}

		fn_save_cart_content($cart, $auth['user_id']);
		$_suffix = ".checkout";
	}

	if ($mode == 'place_order') {

		// Prevent unauthorized access
		if (empty($cart['user_data']['email'])) {
			return array(CONTROLLER_STATUS_DENIED);
		}

		// Prevent using disabled payment method by challenging HTTP data
		if (isset($cart['payment_id'])) {
			$payment_method_data = fn_get_payment_method_data($cart['payment_id']);
			if (!empty ($payment_method_data['status']) && $payment_method_data['status'] != 'A') {
				return array(CONTROLLER_STATUS_DENIED);
			}
		}

		// Remove previous failed order
		if (!empty($cart['failed_order_id']) || !empty($cart['processed_order_id'])) {
			$_order_ids = !empty($cart['failed_order_id']) ? $cart['failed_order_id'] : $cart['processed_order_id'];

			foreach ($_order_ids as $_order_id) {
				fn_delete_order($_order_id);
			}
			$cart['rewrite_order_id'] = $_order_ids;
			unset($cart['failed_order_id'], $cart['processed_order_id']);
		}

		// Clean up saved shipping rates
		unset($_SESSION['shipping_rates']);
		if (!empty($_REQUEST['customer_notes'])) {
			$cart['notes'] = $_REQUEST['customer_notes'];
		}

		if (!empty($_REQUEST['payment_info'])) {
			$cart['payment_info'] = $_REQUEST['payment_info'];
		}
		
		if (empty($_REQUEST['payment_info']) && !empty($cart['extra_payment_info'])) {
			$cart['payment_info'] = empty($cart['payment_info']) ? array() : $cart['payment_info'];
			$cart['payment_info'] = array_merge($cart['extra_payment_info'], $cart['payment_info']);
		}
		
		unset($cart['payment_info']['secure_card_number']);

		if (!empty($cart['products'])) {
			foreach ($cart['products'] as $k => $v) {
				$_is_edp = db_get_field("SELECT is_edp FROM ?:products WHERE product_id = ?i", $v['product_id']);
				if (fn_check_amount_in_stock($v['product_id'], $v['amount'], empty($v['product_options']) ? array() : $v['product_options'], $k, $_is_edp, 0, $cart) == false) {
					unset($cart['products'][$k]);
					return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");
				}
				$exceptions = fn_get_product_exceptions($v['product_id'], true);
				if (!isset($v['options_type']) || !isset($v['exceptions_type'])) {
					$v = array_merge($v, db_get_row('SELECT options_type, exceptions_type FROM ?:products WHERE product_id = ?i', $v['product_id']));
				}
				
				if (!fn_is_allowed_options_exceptions($exceptions, $v['product_options'], $v['options_type'], $v['exceptions_type'])) {
					fn_set_notification('E', fn_get_lang_var('notice'), str_replace('[product]', $v['product'], fn_get_lang_var('product_options_forbidden_combination')));
					unset($cart['products'][$k]);
					
					return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");
				}
			}
		}

		$_last_order_time = fn_get_session_data('last_order_time');

		/*if (!empty($_last_order_time) && ($_last_order_time + ORDERS_TIMEOUT > TIME)) {
			fn_set_notification('E', fn_get_lang_var('error'), str_replace('[minutes]', round(ORDERS_TIMEOUT / 60, 2), fn_get_lang_var('duplicate_order_warning')));
			if (!empty($auth['order_ids'])) {
				$_o_ids = $auth['order_ids'];
			}
			$last_order_id = empty($auth['user_id']) ? array_pop($_o_ids) : db_get_field("SELECT order_id FROM ?:orders WHERE user_id = ?i ORDER BY order_id DESC", $auth['user_id']);

			return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id=$last_order_id");
		}*/

		// Time of placing ordes is saved to avoid duplicate  orders.
		fn_set_session_data('last_order_time', TIME);

		list($order_id, $process_payment) = fn_place_order($cart, $auth);
		
		if (!empty($order_id)) {
			$view->assign('order_action', fn_get_lang_var('placing_order'));
			$view->display('views/orders/components/placing_order.tpl');
			fn_flush();

			if (empty($_REQUEST['skip_payment']) && $process_payment == true) { // administrator, logged in as customer can skip payment
				fn_start_payment($order_id);
			}
			
			fn_order_placement_routines($order_id);
		} else {
			return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");
		}
	}

	if ($mode == 'update_steps') {
		$user_data = !empty($_REQUEST['user_data']) ? $_REQUEST['user_data'] : array();
		$_suffix = ".checkout";
		unset($user_data['user_type']);

		if (!empty($auth['user_id'])) {
			if (isset($user_data['profile_id'])) {
				if (empty($user_data['profile_id'])) {
					$user_data['profile_type'] = 'S';
				}
				$profile_id = $user_data['profile_id'];

			} elseif (!empty($cart['profile_id'])) {
				$profile_id = $cart['profile_id'];

			} else {
				$profile_id = db_get_field("SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i AND profile_type = 'P'", $auth['user_id']);
			}

			$user_data['user_id'] = $auth['user_id'];
			$current_user_data = fn_get_user_info($auth['user_id'], true, $profile_id);
			if ($profile_id != NULL) {
				$cart['profile_id'] = $profile_id;
			}

			// Update contact information
			if ($_REQUEST['update_step'] == 'step_one') {
				// Check email
				$email_exists = db_get_field("SELECT email FROM ?:users WHERE email = ?s AND user_id != ?i", $user_data['email'], $auth['user_id']);
				if (!empty($email_exists)) {
					fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_user_exists'));
					$_suffix .= '?edit_step=step_one';
				} else {
					$user_data = fn_array_merge($current_user_data, $user_data);
					db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", $user_data, $auth['user_id']);
				}
			} elseif ($_REQUEST['update_step'] == 'step_two' && !empty($user_data)) {
				$user_data = fn_array_merge($current_user_data, $user_data);
				
				Registry::get('settings.General.address_position') == 'billing_first' ? $address_zone = 'b' : $address_zone = 's';
				if (!empty($user_data['firstname']) || !empty($user_data[$address_zone . '_firstname'])) {
					$user_data['firstname'] = empty($user_data['firstname']) && !empty($user_data[$address_zone . '_firstname']) ? $user_data[$address_zone . '_firstname'] : $user_data['firstname'];
				}
				if (!empty($user_data['lastname']) || !empty($user_data[$address_zone . '_lastname'])) {
					$user_data['lastname'] = empty($user_data['lastname']) && !empty($user_data[$address_zone . '_lastname']) ? $user_data[$address_zone . '_lastname'] : $user_data['lastname'];
				}
				if (!empty($user_data['phone']) || !empty($user_data[$address_zone . '_phone'])) {
					$user_data['phone'] = empty($user_data['phone']) && !empty($user_data[$address_zone . '_phone']) ? $user_data[$address_zone . '_phone'] : $user_data['phone'];
				}
				
				db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", $user_data, $auth['user_id']);
			}

			// Update billing/shipping information
			if ($_REQUEST['update_step'] == 'step_two') {
				$user_data = fn_array_merge($current_user_data, $user_data);
				!empty($_REQUEST['copy_address']) ? $_REQUEST['ship_to_another'] = '' : $_REQUEST['ship_to_another'] = 'Y';
				
				if (empty($_REQUEST['ship_to_another'])) {
					$profile_fields = fn_get_profile_fields('O');
					fn_fill_address($user_data, $profile_fields);
				}

				$cart['profile_id'] = $profile_id = db_query("REPLACE INTO ?:user_profiles ?e", $user_data);

				fn_set_hook('checkout_profile_update', $cart, $_REQUEST['update_step']);
			}

			// Add/Update additional fields
			if (!empty($user_data['fields'])) {
				fn_store_profile_fields($user_data, array('U' => $auth['user_id'], 'P' => $profile_id), 'UP'); // FIXME
			}

		} elseif (Registry::get('settings.General.disable_anonymous_checkout') != 'Y') {
			if (empty($auth['user_id']) && !empty($_REQUEST['user_data']['email'])) {
				$email_exists = db_get_field("SELECT email FROM ?:users WHERE email = ?s", $_REQUEST['user_data']['email']);
				if (!empty($email_exists)) {
					fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_user_exists'));
					return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout");
				}
			}
			
			if (isset($user_data['fields'])) {
				$fields = fn_array_merge(isset($cart['user_data']['fields']) ? $cart['user_data']['fields'] : array(), $user_data['fields']);
			}
			
			if ($_REQUEST['update_step'] == 'step_two' && !empty($user_data)) {
				Registry::get('settings.General.address_position') == 'billing_first' ? $address_zone = 'b' : $address_zone = 's';
				if (!empty($user_data['firstname']) || !empty($user_data[$address_zone . '_firstname'])) {
					$user_data['firstname'] = empty($user_data['firstname']) && !empty($user_data[$address_zone . '_firstname']) ? $user_data[$address_zone . '_firstname'] : $user_data['firstname'];
				}
				if (!empty($user_data['lastname']) || !empty($user_data[$address_zone . '_lastname'])) {
					$user_data['lastname'] = empty($user_data['lastname']) && !empty($user_data[$address_zone . '_lastname']) ? $user_data[$address_zone . '_lastname'] : $user_data['lastname'];
				}
				if (!empty($user_data['phone']) || !empty($user_data[$address_zone . '_phone'])) {
					$user_data['phone'] = empty($user_data['phone']) && !empty($user_data[$address_zone . '_phone']) ? $user_data[$address_zone . '_phone'] : $user_data['phone'];
				}
			}

			$cart['user_data'] = fn_array_merge($cart['user_data'], $user_data);
			!empty($_REQUEST['copy_address']) ? $_REQUEST['ship_to_another'] = '' : $_REQUEST['ship_to_another'] = 'Y';
			
			// Fill shipping info with billing if needed
			if (empty($_REQUEST['ship_to_another']) && $_REQUEST['update_step'] == 'step_two') {
				$profile_fields = fn_get_profile_fields('O');
				fn_fill_address($cart['user_data'] , $profile_fields);
			}
		}

		if (!empty($_REQUEST['next_step'])) {
			$_suffix .= '?edit_step=' . $_REQUEST['next_step'];
		}
	
		if (!empty($_REQUEST['shipping_ids'])) {
			fn_checkout_update_shipping($cart, $_REQUEST['shipping_ids']);
		}

		if (!empty($_REQUEST['payment_id'])) {
			$cart['payment_id'] = (int) $_REQUEST['payment_id'];
			if (!empty($_REQUEST['payment_info'])) {
				$cart['extra_payment_info'] = $_REQUEST['payment_info'];
				if (!empty($cart['extra_payment_info']['card_number'])) {
					$cart['extra_payment_info']['secure_card_number'] = preg_replace('/^(.+?)([0-9]{4})$/i', '***-$2', $cart['extra_payment_info']['card_number']);
				}
			} else {
				unset($cart['extra_payment_info']);
			}
			unset($cart['payment_updated']);
			fn_update_payment_surcharge($cart);

			fn_save_cart_content($cart, $auth['user_id']);
		}
		
		if (floatval($cart['total']) == 0) {
			unset($cart['payment_updated']);
		}
		
		// Recalculate the cart
		$cart['recalculate'] = true;
	}

	if ($mode == 'create_profile') {

		if (!empty($_REQUEST['order_id']) && !empty($auth['order_ids']) && in_array($_REQUEST['order_id'], $auth['order_ids'])) {

			$order_info = fn_get_order_info($_REQUEST['order_id']);
			$user_data = $_REQUEST['user_data'];

			fn_fill_user_fields($user_data);

			foreach ($user_data as $k => $v) {
				if (isset($order_info[$k])) {
					$user_data[$k] = $order_info[$k];
				}
			}

			if ($res = fn_update_user(0, $user_data, $auth, true, true)) {
				return array(CONTROLLER_STATUS_REDIRECT, "profiles.update");
			} else {
				$_suffix = '.complete?order_id=' . $_REQUEST['order_id'];
			}
		} else {
			return array(CONTROLLER_STATUS_DENIED);
		}
	}

	return array(CONTROLLER_STATUS_OK, "checkout$_suffix");
}

//
// Delete discount coupon
//
if ($mode == 'delete_coupon') {
	unset($cart['coupons'][$_REQUEST['coupon_code']], $cart['pending_coupon']);
	$cart['recalculate'] = true;

	return array(CONTROLLER_STATUS_OK);
}

if (empty($mode) || ($_SERVER['REQUEST_METHOD'] != 'POST' && in_array($mode, array('customer_info', 'summary')) && !defined('AJAX_REQUEST'))) {
	$redirect_mode = empty($_REQUEST['redirect_mode']) ? 'checkout' : $_REQUEST['redirect_mode'];
	return array(CONTROLLER_STATUS_REDIRECT, "checkout." . $redirect_mode);
}

$payment_methods = fn_prepare_checkout_payment_methods($cart, $auth);
if (((true == fn_cart_is_empty($cart) && !isset($force_redirection)) || empty($payment_methods)) && !in_array($mode, array('clear', 'delete', 'cart', 'update', 'apply_coupon', 'shipping_estimation', 'update_shipping', 'complete'))) {
	if (empty($payment_methods)) {
		fn_set_notification('W', fn_get_lang_var('notice'),  fn_get_lang_var('cannot_proccess_checkout_without_payment_methods'), 'K', 'no_payment_notification');
	} else {
		fn_set_notification('W', fn_get_lang_var('cart_is_empty'),  fn_get_lang_var('cannot_proccess_checkout'));
	}
	$force_redirection = "checkout.cart";
	if (defined('AJAX_REQUEST')) {
		Registry::get('ajax')->assign('force_redirection', $force_redirection);
		exit;
	} else {
		return array(CONTROLLER_STATUS_REDIRECT, $force_redirection);
	}
}

if (($mode == 'customer_info' || $mode == 'checkout') && Registry::get('settings.General.min_order_amount_type') == "P" && Registry::get('settings.General.min_order_amount') > $cart['subtotal']) {
	$view->assign('value', Registry::get('settings.General.min_order_amount'));
	$min_amount = $view->display('common_templates/price.tpl', false);
	fn_set_notification('W', fn_get_lang_var('notice'), fn_get_lang_var('text_min_products_amount_required') . ' ' . $min_amount);
	return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");
}

if ($mode == 'customer_info' || $mode == 'checkout' || $mode == 'summary') {
	if (Registry::get('settings.General.checkout_redirect') == 'Y') {
		fn_check_redirect_to_cart();
	}
}

//Cart Items
if ($mode == 'cart') {

	list ($cart_products, $_SESSION['shipping_rates']) = fn_calculate_cart_content($cart, $auth, Registry::get('settings.General.estimate_shipping_cost') == 'Y' ? 'E' : 'S', true, 'F', true);

	fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => false));

	// [Breadcrumbs]
	fn_add_breadcrumb(fn_get_lang_var('cart_contents'));
	// [/Breadcrumbs]

	fn_update_payment_surcharge($cart);

	$cart_products = array_reverse($cart_products, true);
	$view->assign('cart_products', $cart_products);
	$view->assign('shipping_rates', $_SESSION['shipping_rates']);

	// Check if any outside checkout is enbaled
	if (fn_cart_is_empty($cart) != true) {
		$checkout_buttons = fn_get_checkout_payment_buttons($cart, $cart_products, $auth);
		if (!empty($checkout_buttons)) {
			$view->assign('checkout_add_buttons', $checkout_buttons, false);
		} elseif (empty($payment_methods) && !fn_notification_exists('E', 'no_payment_notification')) {
			fn_set_notification('W', fn_get_lang_var('notice'),  fn_get_lang_var('cannot_proccess_checkout_without_payment_methods'));
		}
	}

// Step 1/2: Customer information
} elseif ($mode == 'customer_info') {

	if (Registry::get('settings.General.approve_user_profiles') == 'Y' && Registry::get('settings.General.disable_anonymous_checkout') == 'Y' && empty($auth['user_id'])) {
		fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('text_anonymous_checkout'));

		return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");
	}

	$cart['profile_id'] = empty($cart['profile_id']) ? 0 : $cart['profile_id'];
	if (!empty($cart['user_data']['profile_id']) && $cart['profile_id'] != $cart['user_data']['profile_id']) {
		$cart['profile_id'] = $cart['user_data']['profile_id'];
	}
	$profile_fields = fn_get_profile_fields('O');

	//Get user profiles
	if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
		$user_profiles = fn_get_user_profiles($auth['user_id']);
		$view->assign('user_profiles', $user_profiles);
	}

	//Get countries and states
	$view->assign('countries', fn_get_countries(CART_LANGUAGE, true));
	$view->assign('states', fn_get_all_states());
	$view->assign('usergroups', fn_get_usergroups('C', CART_LANGUAGE));

	// CHECK ME!!!
	$_SESSION['saved_post_data'] = empty($_SESSION['saved_post_data']) ? array() : $_SESSION['saved_post_data'];
	$saved_post_data = & $_SESSION['saved_post_data'];
	unset($_SESSION['saved_post_data']);

	if (!empty($saved_post_data['user_data'])) {
		$view->assign('saved_user_data', $saved_post_data['user_data']);
		$view->assign('ship_to_another', !empty($saved_post_data['ship_to_another']));
	}

	if (!empty($_REQUEST['login_type'])) {
		$view->assign('login_type', $_REQUEST['login_type']);
	}

	// Change user profile
	if (!empty($auth['user_id']) && (empty($cart['user_data']) || (!empty($_REQUEST['profile_id']) && $cart['profile_id'] != $_REQUEST['profile_id']) || (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new'))) {
		if (!empty($_REQUEST['profile_id'])) {
			$cart['profile_id'] = $_REQUEST['profile_id'];
		}

		if (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
			$cart['profile_id'] = 0;
		}

		$cart['user_data'] = fn_get_user_info($auth['user_id'], empty($_REQUEST['profile']), $cart['profile_id']);
	}

	if (!empty($cart['user_data'])) {
		$cart['ship_to_another'] = fn_check_shipping_billing($cart['user_data'], $profile_fields);
	}

	$titles = fn_get_static_data_section('T', false, true);
	$view->assign('titles', $titles);

// Step 3: Shipping and payment methods
} elseif ($mode == 'checkout') {

	$profile_fields = fn_get_profile_fields('O');

	// Array notifying that one or another step is completed.
	$completed_steps = array();
	
	// Array responsible for what step has editing status
	$edit_step = !empty($_REQUEST['edit_step']) ? $_REQUEST['edit_step'] : (!empty($_SESSION['edit_step']) ? $_SESSION['edit_step'] : '');
	$cart['user_data'] = !empty($cart['user_data']) ? $cart['user_data'] : array();

	if (!empty($auth['user_id'])) {

		//if the error occurred during registration, but despite this, the registration was performed, then the variable should be cleared.
		unset($_SESSION['failed_registration']);

		if (!empty($_REQUEST['profile_id'])) {
			$cart['profile_id'] = $_REQUEST['profile_id'];
		
		} elseif (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
			$cart['profile_id'] = 0;
		
		} elseif (empty($cart['profile_id'])) {
			$cart['profile_id'] = db_get_field("SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i AND profile_type='P'", $auth['user_id']);
		}

		// Here check the previous and the current checksum of user_data - if they are different, recalculate the cart.
		$current_state = fn_crc32(serialize($cart['user_data']));

		$cart['user_data'] = fn_get_user_info($auth['user_id'], empty($_REQUEST['profile']), $cart['profile_id']);

		if ($current_state != fn_crc32(serialize($cart['user_data']))) {
			$cart['recalculate'] = true;
		}

	} else {
		if (!empty($_SESSION['saved_post_data']) && !empty($_SESSION['saved_post_data']['user_data'])) {
			$_SESSION['failed_registration'] = true;
			$_user_data = $_SESSION['saved_post_data']['user_data'];
			unset($_SESSION['saved_post_data']);
		} else {
			unset($_SESSION['failed_registration']);
		}

		$view->assign('login_type', empty($_REQUEST['login_type']) ? 'login' : $_REQUEST['login_type']);

		fn_add_user_data_descriptions($cart['user_data']);

		if (!empty($_REQUEST['action'])) {
			$view->assign('checkout_type', $_REQUEST['action']);
		}
	}

	fn_get_default_credit_card($cart, !empty($_user_data) ? $_user_data : $cart['user_data']);

	if (!empty($cart['extra_payment_info'])) {
		$cart['payment_info'] = empty($cart['payment_info']) ? array() : $cart['payment_info'];
		$cart['payment_info'] = array_merge($cart['payment_info'], $cart['extra_payment_info']);
	}

	$view->assign('user_data', !empty($_user_data) ? $_user_data : $cart['user_data']);
	$contact_info_population = fn_check_profile_fields_population($cart['user_data'], 'E', $profile_fields);
	$view->assign('contact_info_population', $contact_info_population);

	// Check fields population on first and second steps
	if ($contact_info_population == true && empty($_SESSION['failed_registration'])) {
		if ($edit_step != 'step_one' && !fn_check_profile_fields_population($cart['user_data'], 'C', $profile_fields)) {
			fn_set_notification('W', fn_get_lang_var('notice'), fn_get_lang_var('text_fill_the_mandatory_fields'));
			
			return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout?edit_step=step_one");
		}
		
		$completed_steps['step_one'] = true;

		// All mandatory Billing address data exist.
		$billing_population = fn_check_profile_fields_population($cart['user_data'], 'B', $profile_fields);
		$view->assign('billing_population', $billing_population);

		if ($billing_population == true || empty($profile_fields['B'])) {
			// All mandatory Shipping address data exist.
			$shipping_population = fn_check_profile_fields_population($cart['user_data'], 'S', $profile_fields);
			$view->assign('shipping_population', $shipping_population);

			if ($shipping_population == true || empty($profile_fields['S'])) {
				$completed_steps['step_two'] = true;
			}
		}
	}

	// Define the variable only if the profiles have not been changed and settings.General.user_multiple_profiles == Y.
	if (fn_need_shipping_recalculation($cart) == false && (!empty($_SESSION['shipping_rates']) && (Registry::get('settings.General.user_multiple_profiles') != "Y" || (Registry::get('settings.General.user_multiple_profiles') == "Y" && ((isset($user_data['profile_id']) && empty($user_data['profile_id'])) || (!empty($user_data['profile_id']) && $user_data['profile_id'] == $cart['profile_id'])))) || (empty($_SESSION['shipping_rates']) && Registry::get('settings.General.user_multiple_profiles') == "Y" && isset($user_data['profile_id']) && empty($user_data['profile_id'])))) {
		define('CACHED_SHIPPING_RATES', true);
	}

	if (!empty($_SESSION['shipping_rates'])) {
		$old_shipping_hash = md5(serialize($_SESSION['shipping_rates']));
	}

	list ($cart_products, $_SESSION['shipping_rates']) = fn_calculate_cart_content($cart, $auth, !empty($completed_steps['step_two']) ? 'A' : 'S', true, 'F');

	// if address step is completed, check if shipping step is completed
	if (!empty($completed_steps['step_two'])) {
		$completed_steps['step_three'] = true;
	
	}

	// If shipping step is completed, assume that payment step is completed too
	if (!empty($completed_steps['step_three']) && empty($cart['payment_updated'])) {
		$completed_steps['step_four'] = true;
	} elseif (!empty($completed_steps['step_three']) && $edit_step == 'step_four') {
		fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('payment_method_was_changed'));
	}

	if (!empty($cart['shipping_failed']) || !empty($cart['company_shipping_failed'])) {
		$completed_steps['step_four'] = false;
		fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('text_no_shipping_methods'));
	}

	// If shipping methods changed and shipping step is completed, display notification
	if (!empty($old_shipping_hash) && $old_shipping_hash != md5(serialize($_SESSION['shipping_rates'])) && !empty($completed_steps['step_three'])) {
		fn_set_notification('W', fn_get_lang_var('important'), fn_get_lang_var('text_shipping_rates_changed'));
	}

	fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => false, 'get_options' => true, 'get_discounts' => false));

	if (false !=($first_method = reset($payment_methods)) && empty($cart['payment_id']) && floatval($cart['total']) != 0) {
		$cart['payment_id'] = $first_method['payment_id'];
		$completed_steps['step_four'] = false;
	}
	if (floatval($cart['total']) == 0) {
		$cart['payment_id'] = 0;
	}

	if (!empty($cart['payment_id'])) {
		$payment_info = fn_get_payment_method_data($cart['payment_id']);
		$view->assign('payment_info', $payment_info);
	}

	$view->assign('shipping_rates', $_SESSION['shipping_rates']);
	$view->assign('payment_methods', $payment_methods = fn_prepare_checkout_payment_methods($cart, $auth));
	
	$cart['payment_surcharge'] = 0;
	if (!empty($cart['payment_id']) && !empty($payment_methods[$cart['payment_id']])) {
		$cart['payment_surcharge'] = $payment_methods[$cart['payment_id']]['surcharge_value'];
	}

	$view->assign('titles', fn_get_static_data_section('T'));
	$view->assign('usergroups', fn_get_usergroups('C', CART_LANGUAGE));
	$view->assign('countries', fn_get_countries(CART_LANGUAGE, true));
	$view->assign('states', fn_get_all_states());

	$cart['ship_to_another'] = fn_check_shipping_billing($cart['user_data'], $profile_fields);

	$view->assign('profile_fields', $profile_fields);

	if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
		$user_profiles = fn_get_user_profiles($auth['user_id']);
		$view->assign('user_profiles', $user_profiles);
	}

	fn_checkout_summary($cart);
	
	if ($edit_step == 'step_two' && !empty($completed_steps['step_one']) && empty($profile_fields['B']) && empty($profile_fields['S'])){
		$edit_step = 'step_four';
	}

	// If we're on shipping step and shipping is not required, switch to payment step
	//FIXME
	/*if ($edit_step == 'step_three' && $cart['shipping_required'] != true) {
		$edit_step = 'step_four';
	}*/

	if (empty($edit_step) || empty($completed_steps[$edit_step])) {
		// If we don't pass step to edit, open default (from settings)
		if (!empty($completed_steps['step_three'])) {
			$edit_step = 'step_three';
		} else {
			$edit_step = !empty($completed_steps['step_one']) ? 'step_two' : 'step_one';
		}
	}

	$_SESSION['edit_step'] = $edit_step;
	$view->assign('use_ajax', 'true');
	$view->assign('edit_step', $edit_step);
	$view->assign('completed_steps', $completed_steps);
	$view->assign('location', 'checkout');
	$view->assign('ship_country', trim($_SESSION['cart']['user_data']['s_country']));
	/* Code add by shryans 
	*  Bongous API Integration
	*  @ assign partner_key 
	*  @ assign bongous_url
	*  @ assign ship_country
	*/
	if($_SESSION['cart']['user_data']['s_country']!="US"){
		$view->assign('ship_country', trim($_SESSION['cart']['user_data']['s_country']));
		$view->assign('partner_key', PARTNER_KEY);
		$view->assign('bongous_url', BONGOUS_ACTION);
	}	
	if (defined('AJAX_REQUEST')) {

		$view->assign('cart', $cart);
		$view->assign('cart_products', array_reverse($cart_products, true));

		if (in_array('sign_io', Registry::get('ajax')->result_ids)) {
			$view->display('top.tpl');
		}
		if (in_array('cart_status', Registry::get('ajax')->result_ids)) {
			$view->display('views/checkout/components/cart_status.tpl');
		}
		if (in_array('checkout_totals', Registry::get('ajax')->result_ids)) {
			//$view->assign('location', 'checkout');
			$view->display('views/checkout/components/checkout_totals.tpl');
		}
		if (in_array('checkout_steps', Registry::get('ajax')->result_ids)) {
			/* Code add by shryans 
			*  Bongous API Integration
			*  @ assign partner_key 
			*  @ assign bongous_url
			*  @ assign ship_country
			*/
			if($_SESSION['cart']['user_data']['s_country']!="US"){
				$view->assign('ship_country', trim($_SESSION['cart']['user_data']['s_country']));
				$view->assign('cart_data' , $cart);
			}	
				$view->display('views/checkout/components/checkout_steps.tpl');
		}
		if (in_array('payments_summary', Registry::get('ajax')->result_ids)) {
			$view->display('views/checkout/components/payment_methods.tpl');
		}
		if (in_array('shipping_rates_list', Registry::get('ajax')->result_ids)) {
			$view->assign('shipping_rates', $_SESSION['shipping_rates']);
			$view->assign('display', 'radio');
			$view->display('views/checkout/components/shipping_rates.tpl');
		}

		exit;
	}

	$view->assign('cart_products', array_reverse($cart_products, true));

// Step 4: Summary
} elseif ($mode == 'summary') {

	if (!empty($_SESSION['shipping_rates'])) {
		define('CACHED_SHIPPING_RATES', true);
	}

	list($cart_products, $_SESSION['shipping_rates']) = fn_calculate_cart_content($cart, $auth, 'E', true, Registry::get('settings.General.one_page_checkout') == 'Y' ? 'F' : 'I'); // we need this for promotions only actually...

	$profile_fields = fn_get_profile_fields('O');

	if (empty($cart['payment_id']) && floatval($cart['total']) || !fn_allow_place_order($cart)) {
		return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout");
	}

	fn_checkout_summary($cart);

	fn_get_default_credit_card($cart, empty($cart['user_data']) ? array() : $cart['user_data']);

	$view->assign('shipping_rates', $_SESSION['shipping_rates']);

	if (defined('AJAX_REQUEST')) {

		fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => false, 'get_options' => true, 'get_discounts' => false));

	//	$view->assign('cart', $cart);
		$view->assign('cart_products', array_reverse($cart_products, true));
		$view->assign('location', 'checkout');
		$view->assign('profile_fields', $profile_fields);
		$view->assign('use_ajax', true);

		if (Registry::get('settings.General.one_page_checkout') == 'Y') {
			$view->assign('edit_step', 'step_four');
			$view->display('views/checkout/components/checkout_steps.tpl');
			$view->display('views/checkout/components/cart_items.tpl');
		} else {
			$view->display('views/checkout/checkout.tpl');
		}
		$view->display('views/checkout/components/checkout_totals.tpl');

		exit;
	}

// Delete product from the cart
} elseif ($mode == 'delete' && isset($_REQUEST['cart_id'])) {

	fn_delete_cart_product($cart, $_REQUEST['cart_id']);
	
	if (fn_cart_is_empty($cart) == true) {
		fn_clear_cart($cart);
	}

	fn_save_cart_content($cart, $auth['user_id']);

	$cart['recalculate'] = true;

	if (defined('AJAX_REQUEST')) {
		fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('text_product_has_been_deleted'));
		if ($action == 'from_status') {
			fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);
			$view->assign('force_items_deletion', true);
			$view->display('views/checkout/components/cart_status.tpl');
			exit;
		}
	}

	return array(CONTROLLER_STATUS_REDIRECT, "checkout." . $_REQUEST['redirect_mode']);
	
} elseif ($mode == 'get_custom_file' && isset($_REQUEST['cart_id']) && isset($_REQUEST['option_id']) && isset($_REQUEST['file'])) {
	if (isset($cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']])) {
		$file = $cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']];
		
		fn_get_file($file['path'], $file['name']);
	}

} elseif ($mode == 'delete_file' && isset($_REQUEST['cart_id'])) {

	if (isset($cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']])) {
		// Delete saved custom file
		$file = $cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']];
		
		@unlink($file['path']);
		@unlink($file['path'] . '_thumb');
		
		unset($cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']]);
	}
	
	fn_save_cart_content($cart, $auth['user_id']);

	$cart['recalculate'] = true;

	if (defined('AJAX_REQUEST')) {
		fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('text_product_file_has_been_deleted'));
		if ($action == 'from_status') {
			fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);
			$view->assign('force_items_deletion', true);
			$view->display('views/checkout/components/cart_status.tpl');
			exit;
		}
	}

	return array(CONTROLLER_STATUS_REDIRECT, "checkout." . $_REQUEST['redirect_mode']);

//Clear cart
} elseif ($mode == 'clear') {

	fn_clear_cart($cart);
	fn_save_cart_content($cart, $auth['user_id']);

	return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");

//Purge undeliverable products
} elseif ($mode == 'purge_undeliverable') {

	fn_purge_undeliverable_products($cart);
	fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('notice_undeliverable_products_removed'));

	return array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout");

} elseif ($mode == 'complete') {

	if (!empty($_REQUEST['order_id'])) {
		if (empty($auth['user_id'])) {
			if (empty($auth['order_ids'])) {
				return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode(Registry::get('config.current_url')));
			} else {
				$allowed_id = in_array($_REQUEST['order_id'], $auth['order_ids']);
			}
		} else {
			$allowed_id = db_get_field("SELECT user_id FROM ?:orders WHERE user_id = ?i AND order_id = ?i", $auth['user_id'], $_REQUEST['order_id']);
		}

		fn_set_hook('is_order_allowed', $_REQUEST['order_id'], $allowed_id); 

		if (empty($allowed_id)) { // Access denied
			return array(CONTROLLER_STATUS_DENIED);
		}
		
		$order_info = fn_get_order_info($_REQUEST['order_id']);
		
		if (!empty($order_info['is_parent_order']) && $order_info['is_parent_order'] == 'Y') {
			$order_info['child_ids'] = implode(',', db_get_fields("SELECT order_id FROM ?:orders WHERE parent_order_id = ?i", $_REQUEST['order_id']));
		}
		if (!empty($order_info)) {
			$view->assign('order_info', $order_info);
		}
	}
	fn_add_breadcrumb(fn_get_lang_var('landing_header'));
}

if ($mode == 'checkout' || $mode == 'summary') {
	if (!empty($cart['failed_order_id']) || !empty($cart['processed_order_id'])) {
		$_ids = !empty($cart['failed_order_id']) ? $cart['failed_order_id'] : $cart['processed_order_id'];
		$_order_id = reset($_ids);
		$_payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'P'", $_order_id);
		if (!empty($_payment_info)) {
			$_payment_info = unserialize(fn_decrypt_text($_payment_info));
		}

		if (!empty($cart['failed_order_id'])) {
			$_msg = !empty($_payment_info['reason_text']) ? $_payment_info['reason_text'] : '';
			$_msg .= empty($_msg) ? fn_get_lang_var('text_order_placed_error') : '';
			fn_set_notification('O', '', $_msg);
			$cart['processed_order_id'] = $cart['failed_order_id'];
			unset($cart['failed_order_id']);
		}

		unset($_payment_info['card_number'], $_payment_info['cvv2'], $_payment_info['issue_number']);
		$cart['payment_info'] = $_payment_info;
		if (!empty($cart['extra_payment_info'])) {
			$cart['payment_info'] = array_merge($cart['payment_info'], $cart['extra_payment_info']);
		}
	}
}

if (!empty($profile_fields)) {
	$view->assign('profile_fields', $profile_fields);
}

$view->assign('cart', $cart);
$view->assign('continue_url', empty($_SESSION['continue_url']) ? '' : $_SESSION['continue_url']);
$view->assign('mode', $mode);
$view->assign('payment_methods', $payment_methods);

// Remember mode for the check shipping rates
$_SESSION['checkout_mode'] = $mode;

function fn_prepare_checkout_payment_methods(&$cart, &$auth)
{
	static $payment_methods;

	//Get payment methods
	if (empty($payment_methods)) {
		$payment_methods = fn_get_payment_methods($auth);
	}

	// Check if payment method has surcharge rates
	foreach ($payment_methods as $k => $v) {
		$payment_methods[$k]['surcharge_value'] = 0;
		if (floatval($v['a_surcharge'])) {
			$payment_methods[$k]['surcharge_value'] += $v['a_surcharge'];
		}
		if (floatval($v['p_surcharge']) && !empty($cart['total'])) {
			$payment_methods[$k]['surcharge_value'] += fn_format_price($cart['total'] * $v['p_surcharge'] / 100);
		}
	}

	fn_set_hook('prepare_checkout_payment_methods', $cart, $auth, $payment_methods);

	return $payment_methods;
}

function fn_checkout_summary(&$cart)
{
	if (fn_cart_is_empty($cart) == true) {
		return false;
	}

	fn_set_hook('checkout_summary', $cart);

	//Get payment methods
	$payment_data = fn_get_payment_method_data($cart['payment_id']);

	Registry::get('view')->assign('payment_method', $payment_data);
	Registry::get('view')->assign('credit_cards', fn_get_static_data_section('C', true, 'credit_card'));

	// Downlodable files agreements
	$agreements = array();
	foreach ($cart['products'] as $item) {
		if ($item['is_edp'] == 'Y') {
			if ($_agreement = fn_get_edp_agreements($item['product_id'], true)) {
				$agreements[$item['product_id']] = $_agreement;
			}
		}
	}
	if (!empty($agreements)) {
		Registry::get('view')->assign('cart_agreements', $agreements);
	}
}

function fn_need_shipping_recalculation(&$cart)
{
	if ($cart['recalculate'] == true) {
		return true;
	}

	$recalculate_shipping = false;
	if (!empty($_SESSION['customer_loc'])) {
		foreach ($_SESSION['customer_loc'] as $k => $v) {
			if (!empty($v) && empty($cart['user_data'][$k])) {
				$recalculate_shipping = true;
				break;
			}
		}
	}

	if ($recalculate_shipping == false && !empty($_SESSION['checkout_mode']) && ($_SESSION['checkout_mode'] == 'cart' && MODE == 'checkout')) {
		$recalculate_shipping = true;
	}

	unset($_SESSION['customer_loc']);

	return $recalculate_shipping;

}

function fn_get_checkout_payment_buttons(&$cart, &$cart_products, &$auth)
{
	$checkout_buttons = array();

	$ug_condition = 'AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'b.usergroup_ids', true) . ')';
	$checkout_payments = db_get_fields("SELECT b.payment_id FROM ?:payment_processors as a LEFT JOIN ?:payments as b ON a.processor_id = b.processor_id WHERE a.type != 'P' AND b.status = 'A' ?p", $ug_condition);

	if (!empty($checkout_payments)) {
		foreach ($checkout_payments as $_payment_id) {
			$processor_data = fn_get_processor_data($_payment_id);
			if (!empty($processor_data['processor_script']) && file_exists(DIR_PAYMENT_FILES . $processor_data['processor_script'])) {
				include(DIR_PAYMENT_FILES . $processor_data['processor_script']);
			}
		}
	}

	return $checkout_buttons;
}

function fn_checkout_update_shipping(&$cart, $shipping_ids)
{
	$cart['shipping'] = array();
	$parsed_data = array();
	foreach ($shipping_ids as $k => $shipping_id) {
		if (strpos($k, ',') !== false) {
			$parsed_data = fn_array_merge($parsed_data, fn_array_combine(fn_explode(',', $k), $shipping_id));
		} else {
			$parsed_data[$k] = $shipping_id;
		}
	}

	foreach ($parsed_data as $k => $shipping_id) {
		if (empty($cart['shipping'][$shipping_id])) {
			$cart['shipping'][$shipping_id] = array(
				'shipping' => $_SESSION['shipping_rates'][$shipping_id]['name'],
			);
		}

		$cart['shipping'][$shipping_id]['rates'][$k] = $_SESSION['shipping_rates'][$shipping_id]['rates'][$k];
	}

	return true;
}

function fn_get_default_credit_card(&$cart, $user_data) 
{
	if (!empty($user_data['credit_cards'])) {
		$cards = unserialize(fn_decrypt_text($user_data['credit_cards']));
		foreach ((array)$cards as $cc) {
			if ($cc['default']) {
				$cart['payment_info'] = $cc;
				break;
			}
		}
	} elseif (isset($cart['payment_info'])) {
		unset($cart['payment_info']);
	}
}

function fn_update_payment_surcharge(&$cart)
{
	$cart['payment_surcharge'] = 0;
	if (!empty($cart['payment_id'])) {
		$_data = db_get_row("SELECT a_surcharge, p_surcharge FROM ?:payments WHERE payment_id = ?i", $cart['payment_id']);
		if (floatval($_data['a_surcharge'])) {
			$cart['payment_surcharge'] += $_data['a_surcharge'];
		}
		if (floatval($_data['p_surcharge'])) {
			$cart['payment_surcharge'] += fn_format_price($cart['total'] * $_data['p_surcharge'] / 100);
		}
	}
}

function fn_check_redirect_to_cart()
{
	if (!defined('AJAX_REQUEST') && (!empty($_SERVER['HTTP_REFERER']) && ((stripos($_SERVER['HTTP_REFERER'], Registry::get('config.http_location')) !== false || stripos($_SERVER['HTTP_REFERER'], Registry::get('config.https_location')) !== false) && strpos(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY ), 'checkout') === false))) {
		fn_redirect('checkout.cart', true);
	}
}

?>
