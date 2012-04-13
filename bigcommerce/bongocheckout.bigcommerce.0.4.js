/*
----------------------------------------------------------------
Bongo Checkout for BigCommerce
----------------------------------------------------------------
Developed by Elijah Boston
elijah.boston@bongous.com
----------------------------------------------------------------
Copyright 2011 Bongo International, LLC

THIS CODE MAY NOT BE REDISTRIBUTED OR MODIFIED BY ANYONE
OTHER THAN PARTNERS LISCENSED BY BONGO INTERNATIONAL LLC.
----------------------------------------------------------------

CHANGELOG
---
0.4		-	Modified to use AJAX + BigCommerce API, no longer requires changing URL structure
		-	Fixed bug with price handling for items in the 1,000's
*/

String.prototype.stripHTML = function() {
	return this.replace(/(<([^>]+)>)/ig,'');
}

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,""); 
}

var BongoCheckout = {

	api_url: '/api/v2/products/', // BigCommerce (DO NOT CHANGE)
	partnerKey:  'YOUR_PARTNER_KEY', //Bongo
	api_token: 'API_TOKEN_FROM_BIGCOMMERCE', // BigCommerce
	api_username: 'ADMIN_USERNAME_FOR_BIGCOMMERCE', // BigCommerce
	
	prompt: 'Will this order require shipping outside of the United States? ',
	prompt2: 'International Order <br /><br /><font color="black">Shipping, duties, and taxes will be calculated on the next page.</font>',
	prompt3: 'Domestic U.S. Order <br /><br /><font color="black">Shipping, duties, and taxes will be calculated on the next page.</font>',
	image_url: 'content/Bongo_Checkout_Button.png',
	use_image: true,
	items: new Array(),
	
	debug: false,
	
	Init: function()
	{
		// Check if we're using HTTPS
		if (window.location.protocol != 'https:') {
			// Reload using https if not
			window.location.href = 'https://'+document.location.href.substr(6);
			return;
		}
		
		BongoCheckout.RenderForm();
		BongoCheckout.InsertHTML();
	},
	
	InsertHTML: function() {
		// Add hidden button for international customers
		if (this.use_image) 
		{
			$('div.ProceedToCheckout').prepend('<div id="BC_CheckoutButton" style="display:none;"><a href="#" onclick="BongoCheckout.Submit(); return false;" style="font-size:14px; font-weight:bold;"><img src="'+this.image_url+'" /></a></div>');
		} else {
			$('div.ProceedToCheckout').prepend('<div id="BC_CheckoutButton" style="display:none;"><a href="#" onclick="BongoCheckout.Submit(); return false;" style="font-size:14px; font-weight:bold;">Proceed to International Checkout</a></div>');
		}
		
		// Add prompt
		$('table.CartContents tfoot').append('<tr><td colspan=5><div id="BC_ConfirmDomesticOrder" style="font-size:14px; text-align:right; font-weight:bold; color:red; display:block;">'+this.prompt+'<br /><br /><a href="#" onclick="BongoCheckout.DomesticOrder(false); return false;" style="font-weight:bold;">Yes</a>   &nbsp;&nbsp;&nbsp;<a style="font-weight:bold;" href="#" onclick="BongoCheckout.DomesticOrder(true); return false;">No</a></div></td></tr>');		
		
		$('table.CartContents tfoot').append('<tr><td colspan=5><div id="BC_ConfirmDomesticOrder_Intl" style="font-size:14px; text-align:right; font-weight:bold; color:red; display:none;">'+this.prompt2+'<br /><a style="font-weight:bold; font-size:12px;" href="#" onclick="BongoCheckout.DomesticOrder(true); return false;">(Change)</a></div></td></tr>');		
		
		
		$('table.CartContents tfoot').append('<tr><td colspan=5><div id="BC_ConfirmDomesticOrder_Dom" style="font-size:14px; text-align:right; font-weight:bold; color:red; display:none;">'+this.prompt3+'<br /><a style="font-weight:bold; font-size:12px;" href="#" onclick="BongoCheckout.DomesticOrder(false); return false;">(Change)</a></div></td></tr>');	
	},

	Submit: function() 
	{
		$('form[name="BongoCheckoutForm"]').submit();
	},
	
	DomesticOrder: function(bool_yn)
	{
		//var shippingCountry = $('select[name="shippingZoneCountry"] option:selected').text();
		//console.log('ShippingCountry: ' + shippingCountry);
		
		if (bool_yn == true) {
			$('div.CheckoutButton').show();
	
			$('#BC_ConfirmDomesticOrder').hide();
			$('#BC_ConfirmDomesticOrder_Intl').hide();
			$('#BC_ConfirmDomesticOrder_Dom').show();
			
			$('#BC_CheckoutButton').hide();
		} else {
			$('#BC_CheckoutButton').show();
			
			
			$('#BC_ConfirmDomesticOrder').hide();
			$('#BC_ConfirmDomesticOrder_Intl').show();
			$('#BC_ConfirmDomesticOrder_Dom').hide();
			
			$('div.CheckoutButton').hide();
		}
	},

	
	RenderForm: function() {
		var name = '';
		var pid = '';
		var custom = '';
		var item_n = 0;
		
		$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PARTNER_KEY" value="'+this.partnerKey+'">');
		
		$('table.CartContents tbody tr').each(function() {
			//console.log(this);
			
			if ( !$(this).parent().parent().hasClass('productAttributes') ) {
			
				// Product ID
					pid = $('input.bongo-product-id', this).val();
					
				// Product Attributes
					try {
						custom = $('table.productAttributes', this).html().stripHTML().replace(/(\r\n|\n|\r|\s{1,})/gm,' ').replace(/[^a-zA-Z 0-9 . :]+/gm,'');
					} catch(err) {
						custom = '';
					}

					
				// Quantity
					quant = $('td.CartItemQuantity option:selected',this).text().trim();
					
					BongoCheckout.items[item_n] = {'quant': quant, 'custom': custom};
					
				BongoCheckout.GetProduct(pid, item_n);
				item_n++;
			}
		
			pid = '';
		});
	},
	
	AddItem: function(data, item_n) {
		if (BongoCheckout.debug) { console.log('Adding item...', data.name); }
		// Product Name
			var name = data.name;
		
		// SKU
			var sku = data.sku;

		// Price
			var price = data.price; // Remove dollar sign and commas
		
		// Quantity
			var quant = BongoCheckout.items[item_n].quant;
			
		// Custom info
			var custom = BongoCheckout.items[item_n].custom;
			
			var fixed_shipping = data.fixed_cost_shipping_price;
			
			var x = item_n+1;
			
			
			if (BongoCheckout.debug) { console.log('Name: ', name, '\nAttributes: ', custom, 'SKU: ', sku, '\nPrice: ', price, '\nQuantity: ', quant); }

			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_ID_'+x+'" value="'+sku+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_NAME_'+x+'" value="'+name+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_PRICE_'+x+'" value="'+price+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_Q_'+x+'" value="'+quant+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+x+'" value="'+fixed_shipping+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_CUSTOM_1_'+x+'" value="'+custom+'">');
			
		return;
	},
	
	GetProduct: function(pid, item_n) {
	
		var auth = Base64.encode(BongoCheckout.api_token + ':' + BongoCheckout.api_username);
		
		var resp = $.ajax({
			dataType: 'json',
			async: false,
			url: BongoCheckout.api_url+pid+'.json',
			headers: {
				'Authorization':'Basic '+auth,
				'Accept':'application/json',
				'Content-Type':'application/json',
			},
			success: function(data) {
			if (BongoCheckout.debug) { 
				console.log(data);
				console.log(data.sku);
			}
				BongoCheckout.AddItem(data, item_n);
				return true;
			},
		
		});
		
		return false;
	},
}


var Base64 = {
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			Base64._keyStr.charAt(enc1) + Base64._keyStr.charAt(enc2) +
			Base64._keyStr.charAt(enc3) + Base64._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = Base64._keyStr.indexOf(input.charAt(i++));
			enc2 = Base64._keyStr.indexOf(input.charAt(i++));
			enc3 = Base64._keyStr.indexOf(input.charAt(i++));
			enc4 = Base64._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}
		return string;
	}
}


$(document).ready(function() {
	BongoCheckout.Init();
});