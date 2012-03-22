String.prototype.startsWith = function (str){ return this.indexOf(str) == 0; };
String.prototype.stripHTML = function() { return this.replace(/(<([^>]+)>)/ig,''); }
String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g,""); }

/*
	BONGO CHECKOUT
	for SHOPPING CARTS PLUS
	http://bongous.com
	
	by Elijah Boston (elijah.boston@bongous.com)

	ALL FOLLOWING CODE IS COPYRIGHT BONGO INTERNATIONAL, LLC AND MAY NOT BE USED OR REPRODUCED WITHOUT CONSENT.
*/

var $jb = jQuery.noConflict();

var BongoCheckout = {
	button_image_url: 'https://secure1411.hostgator.com/~bongo315/public/Bongo_Checkout_Button.png',
	debug: true,
	
	Initialize: function() {
		// Append form
		//$jb(document.body).append('<form name="BongoCheckoutForm" action="'+BongoCheckout.checkout_url+'" method="post"></form>');

		
		// Populate user info
		$jb('div.cart .subheading').each(function(index) {
			if ($jb(this).text() == 'Shipping Information') {
				//console.log('found Shipping Info area: ', $jb(this).next().html() );
				
				shipping_info = $jb(this).next().html().split('<br>');
				
				//console.log(shipping_info);
				//console.log(shipping_info.length);
				
				if (BongoCheckout._isInternational(shipping_info)) {
					if (BongoCheckout.debug) { console.log('International Customer'); }
					
					// Hide the shipping Error message
					$jb('.shippingError').html('We have partnered with Bongo International to provide international shipping. Please proceed to the next step to see our international shipping options and costs.');
					
					// Overide the 'Next' button
					$jb('input[name="button.submit"]').parent().append('<input type="image" class="BongoCheckoutButton" onclick="javascript:BongoCheckout.Submit(); return false;" name="BongoCheckoutButton.submit" src="'+BongoCheckout.button_image_url+'" />');
					$jb('input[name="button.submit"]').hide();
					
					// Populate Form
					BongoCheckout.PopulateCart();
					
					//BongoCheckout.PopulateCustomerProfile(shipping_info);
				} else {
					// Don't do anything if doemstic
					if (BongoCheckout.debug) { console.log('Domestic Customer'); }
					return;
				}
			}
		});
		

	},
	
	PopulateCart: function() {
	
		// Populate hidden form for transfer
		var sku = '';
		var custom = '';
		var item_name = '';
		var price = 0;
		var quant = 0;
		var x = 0;
		
		var shipping_info = null;
		var cust_info = null;
		
		// Populate product info
		$jb('table.items:first tr.item').each(function(index) {
			
			x = index + 1;
			
			sku = $jb('td:first',this).text().trim();
			custom = $jb('td:eq(1):not(a)', this).html().split('<br>').slice(1).join(' -- ').trim();
			item_name = $jb('td:eq(1) a', this).text();
			price = parseFloat( $jb('td:eq(2)', this).text().trim().slice(1) );
			quant = parseInt( $jb('td:eq(3)', this).text().trim() );
			
			if (BongoCheckout.debug) {
				console.log(item_name, ' -- ', sku, ' -- ', price , ' x ' , quant, '\nCUSTOM: ', custom);
			}
			
			$jb('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_ID_'+x+'" value="'+sku+'">');
			$jb('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_NAME_'+x+'" value="'+item_name+'">');
			$jb('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_PRICE_'+x+'" value="'+price+'">');
			$jb('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_Q_'+x+'" value="'+quant+'">');
			$jb('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_CUSTOM_'+x+'" value="'+custom+'">');
			$jb('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+x+'" value="0">');
		});
		
	},
	
	Submit: function() {
		// Submit to Bongo Checkout
		$jb('form[name="BongoCheckoutForm"]').submit();
	},
	
	_isInternational: function(data) {
		if ( $jb('.shippingError:visible').length > 0 ) { return true; }
		return false;
	},
};

jQuery(document).ready(function($) {
	BongoCheckout.Initialize();
});
