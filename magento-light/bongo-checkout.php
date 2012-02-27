<!-- LOAD BONGO CHECKOUT -->
<!--bongo:start-->

<?php

/*
	Bongo Checkout for Magento (LIGHT) v1.5
	By Elijah Boston (elijah.boston@bongous.com)
*/

function getPerItemShippingCost($items, $shipping_cost) {

	$n_items = 0;
	foreach ($items as $item) { $n_items += $item->getQty(); }
	$per_item_shipping = $shipping_cost / $n_items;
	
	return $per_item_shipping;
}

$bill = Mage::getModel('checkout/cart')->getQuote()->getBillingAddress();
$partner_key = '7625511ed3383939386558db5e52c27b';
$checkout_url = 'https://bongous.com/pay/f87d1/index.php';

?>
<form action="<? echo $checkout_url; ?>" method="post" id="frmBongo" name="frmBongo" style="display:none;">
	
			<input type="hidden" name="PARTNER_KEY" value="<? echo $partner_key; ?>" />
			<input type="hidden" name="CUST_FIRST_NAME" id="CUST_FIRST_NAME" value="<? echo $bill->getFirstname();  ?>" />
			<input type="hidden" name="CUST_LAST_NAME" id="CUST_LAST_NAME" value="<? echo $bill->getLastname(); ?>" /> 
			<input type="hidden" name="CUST_COMPANY" id="CUST_COMPANY" value="<? echo $bill->getCompany(); ?>" /> 
			<input type="hidden" name="CUST_COUNTRY" id="CUST_COUNTRY" value="<? echo $bill->getCountry(); ?>" /> 
			<input type="hidden" name="CUST_ADDRESS_LINE_1" id="CUST_ADDRESS_LINE_1" value="<? echo $bill->getStreet(1); ?>" /> 
			<input type="hidden" name="CUST_ADDRESS_LINE_2" id="CUST_ADDRESS_LINE_2" value="<? echo $bill->getStreet(2); ?>" /> 
			<input type="hidden" name="CUST_CITY" id="CUST_CITY" value="<? echo $bill->getCity(); ?>" />   
			<input type="hidden" name="CUST_STATE" id="CUST_STATE" value="<? echo ($bill->getRegionCode() ? $bill->getRegionCode() : $bill->getRegion()); ?>" /> 	  
			<input type="hidden" name="CUST_ZIP" id="CUST_ZIP" value="<? echo $bill->getPostcode(); ?>" />   
			<input type="hidden" name="CUST_PHONE" id="CUST_PHONE" value="<? echo $bill->getTelephone(); ?>" /> 
			<input type="hidden" name="CUST_EMAIL" id="CUST_EMAIL" value="<? echo $bill->getEmail(); ?>" /> 

<?
			
			$items = Mage::getModel('checkout/cart')->getQuote()->getAllItems();
			$a = Mage::getModel('checkout/cart')->getQuote()->getShippingAddress();
			$per_item_shipping = getPerItemShippingCost($items, $a->getShippingAmount());
			
			$iCount = 1;
			
			foreach($items as $item) {

				echo '<input type="hidden" name="PRODUCT_CUSTOM_'.$iCount .'"  value="'.$item->getSku() .'"/>'; // Get FULL SKU (After customizations)
				echo '<input type="hidden" name="PRODUCT_ID_'.$iCount .'"  value="'.$item->product->sku.'"/>'; // Get BASE SKU
				echo '<input type="hidden" name="PRODUCT_NAME_'.$iCount .'"  value="'.$item->getName() .'"/>';
				echo '<input type="hidden" name="PRODUCT_PRICE_'.$iCount .'"  value="'.$item->getPrice() .'"/>';
				echo '<input type="hidden" name="PRODUCT_Q_'.$iCount .'"  value="'.$item->getQty() .'"/>';
				echo '<input type="hidden" name="PRODUCT_SHIPPING_'.$iCount .'"  value="'.$per_item_shipping.'"/>';
				
				$iCount ++;
			}
	
			$orderId = $this->getOrderId();
			$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
			echo '<input type="hidden" id="txtOrderId" name="txtOrderId" value="'.$orderId.'"/>';
?>
</form>

<!-- This is the button to send users to Bongo Checkout -->
<button type="submit" title="International Checkout" class="button btn-checkout" onclick="frmBongo.submit();">
	<span><span>Outside the U.S.?</span></span>
</button>

		
<!--bongo:end-->
<!-- END BONGO CHECKOUT -->