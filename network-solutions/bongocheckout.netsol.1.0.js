/*
	BONGO CHECKOUT for Network Solutions
	---
	v1.0
	
	By Elijah Boston
*/


// Strip out HTML Tags
String.prototype.stripHTML = function() { return this.replace(/(<([^>]+)>)/ig,''); }

var $b = jQuery.noConflict();

var BongoCheckout = {
	CheckoutURL: 'https://bongous.com/pay/d4122/index.php',
	PartnerKey: '7625511ed3383939386558db5e52c27b',
	ButtonImageURL: 'http://public.bongocheckout.com/Bongo_Checkout_Button.png',
	Init: function() {
		var country = $b('select[name="ctl00$pageContent$checkoutWizard$customerInformation$shippingAddress$ddlCountry"] option:selected').val();
		
		console.log('Shipping Country: ', country);
		if (country) {
			if (country != 'US') {
				// Append Form
				$b('form[name="aspnetForm"]').parent().append('<form name="BongoCheckoutForm" action="'+BongoCheckout.CheckoutURL+'" method="post"></form>');
				
				// Append Button
				$b('input[name="ctl00$pageContent$checkoutWizard$StartNavigationTemplateContainerID$btnNext"]').parent().append('<a class="BongoCheckoutButton" href="#" onclick="javascript:BongoCheckout.Submit(); return false;"><img src="'+BongoCheckout.ButtonImageURL+'" border="0"/></a>');
				
				BongoCheckout.Populate();
				BongoCheckout.Display('on');
			}
		}
	},
	Populate: function() {
		var x = 1; // MUST START AT 1
		
		// Append Partner Key
		$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PARTNER_KEY" value="'+BongoCheckout.PartnerKey+'">');
		
		$b('.checkout-cart-item').each(function() {
			var sku= $b('.checkout-cart-part-number',this).html().replace('Item Number:','').trim();
			var name= $b('.checkout-cart-prod-name',this).html().stripHTML().trim();
			var price = $b('.cart-item-attributes',this).parent().next('td').html().replace('$','').trim();
			var q = $b('.cart-item-attributes',this).parent().next('td').next('td').html().trim();
			
			//console.log(name,' >> ', sku, ' > Price: ', price, '  > Q: ', q);
			
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_ID_'+x+'" value="'+sku+'">');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_NAME_'+x+'" value="'+name+'">');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_PRICE_'+x+'" value="'+price+'">');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_Q_'+x+'" value="'+q+'">');
			$b('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+x+'" value="0">');
			
			x++;
		});
	},
	Submit: function() {
		$b('form[name="BongoCheckoutForm"]').submit();
	},
	Display: function(status) {
		if (status == 'on') {
			$b('.BongoCheckoutButton').show();
			$b('input[name="ctl00$pageContent$checkoutWizard$StartNavigationTemplateContainerID$btnNext"]').hide();
		} else {
			$b('.BongoCheckoutButton').hide();
			$b('input[name="ctl00$pageContent$checkoutWizard$StartNavigationTemplateContainerID$btnNext"]').show();
		}
	},
}

$b(document).ready(function() { BongoCheckout.Init(); });