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
	api_url: '/api/v2/products/',
	partnerKey:  '7625511ed3383939386558db5e52c27b',
	prompt: 'Will this order require shipping outside of the United States? ',
	prompt2: 'International Order <br /><br /><font color="black">Shipping, duties, and taxes will be calculated on the next page.</font>',
	prompt3: 'Domestic U.S. Order <br /><br /><font color="black">Shipping, duties, and taxes will be calculated on the next page.</font>',
	image_url: 'content/Bongo_Checkout_Button.png',
	use_image: true,
	items: new Array(),
	
	debug: false,
	
	Init: function()
	{
		//BongoCheckout.InsertHTML();
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
		
		var resp = $.ajax({
			dataType: 'json',
			async: false,
			url: BongoCheckout.api_url+pid+'.json',
			headers: {
				'Authorization':'Basic YWRtaW46NmJiNTEzM2I3OWNlOTk5MjNlOTYwMzFiMDdjMzExMTRkMzFlN2RjNw==',
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

$(document).ready(function() {
	BongoCheckout.Init();
});