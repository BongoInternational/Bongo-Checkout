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

	NOTE: This module requires you make changes to your shopping cart's URL structure. The change required will *not* affect your SEO and may actually improve it by including the item SKU in the URL. If you do not with to make this change for any reason please contact your product or implementation specialist.

	1. Upload bongocheckout.js to your content folder.
	
	2. Change the following settings from 'Settings' > 'Store Settings'
	
		1. Display > Display Settings:
		
			Next to 'Add to Cart' Action choose 'Take Them to Their Shopping Cart'

		2. URL Structure > Product URL Settings:
		
			Set to Custom and enter in: /%productname%/%sku%/
			Click 'Save'
			Click 'Update Product URLs...'
			
		3. Design > Template Files > cart.html
		
			Insert the code below BEFORE the closing div (MAKE SURE TO REPLACE "CHECKOUT URL" with the one we give you!)
		
		
			<form name="BongoCheckoutForm" method="post" action="CHECKOUT URL">
				
			</form>

			<script type="text/javascript" src="content/bongocheckout.js"></script>
			<script type="text/javascript">
				BongoCheckout.Init();
			</script>
			
	3. Remove or comment out any Proceed to Checkout buttons that appear elsewhere in the shopping cart.
	4. Update the partnerKey and set per_item_shipping and shipping_cost.
	
	Options:
	---
	
	partnerKey - The unique identifier key assigned to your account.
	prompt - The message displayed above the button.
	image_url - URL of the image to use for the button
	
	per_item_shipping - Setting this to true will charge the value of shipping_cost for EACH ITEM.
	shipping_cost - Amount to charge for domestic shipping to the Bongo warehouse, on a per order or per item basis.
	
	debug_mode - This can be left turned off. If you experience technical issues, our implementation specialist may ask you to set this to 'true' so they can better analyze the problem.

*/

var BongoCheckout = {
	partnerKey:  '7625511ed3383939386558db5e52c27b',
	prompt: 'International Customers - Click below to Checkout',
	image_url: 'https://bongous.com/partner/images/Bongo_Checkout_Button.png',

	per_item_shipping: false,
	shipping_cost: 5.00,
	
	debug_mode: false,
	
	
	
	trim : function(stringToTrim) { return stringToTrim.replace(/^\s+|\s+$/g,""); },
	
	Init: function()
	{
		
		// Add prompt
		$('.ProceedToCheckout').prepend('<div id="BongoCheckoutButton" style="text-align:right; font-weight:bold; color:red; display:block; float:right; position:relative; clear:both; padding:10px;"><p>'+this.prompt+'</p><a href="#" onclick="javascript:BongoCheckout.Submit(); return false;"><img src="'+this.image_url+'" border="0" /></a></div>');	
	
	},

	Submit: function() 
	{
		var product_skus = this.GetProductSKUs();
		var product_names = this.GetProductNames();
		var product_prices = this.GetProductPrices();
		var product_quantities = this.GetProductQuantities();
		
		var total_items = 0;
		var shipping_cost_breakdown = 0;
		
		for (i=0; i < product_quantities.length; i++) { total_items += parseInt(product_quantities[i]); }
		
		shipping_cost_breakdown = ( this.shipping_cost / total_items );
		
		if (this.debug_mode) { console.log('Total # of Items: ' + total_items ); }
		if (this.debug_mode) { console.log('Per Item Shipping Cost would be: ' + shipping_cost_breakdown ); }
		
		//console.log('Partner Key: ' + this.partnerKey);
		$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PARTNER_KEY" value="'+this.partnerKey+'">');
		
		for (var i=0; i < product_skus.length; i++)
		{
			//console.log('Product #' + i + ': ' + product_skus[i] + ' | ' + product_names[i] + ' | ' + product_prices[i] + ' x ' + product_quantities[i]);
			
			var x = i + 1;
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_ID_'+x+'" value="'+product_skus[i]+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_NAME_'+x+'" value="'+product_names[i]+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_PRICE_'+x+'" value="'+product_prices[i]+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_Q_'+x+'" value="'+product_quantities[i]+'">');
			$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+x+'" value="0">');
			
			if (this.per_item_shipping) {
			
				if (this.debug_mode) { console.log('Applying $'+this.shipping_cost+' shipping cost per ITEM'); }
				
				$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+(i+1)+'" value="'+this.shipping_cost.toString()+'"> ');
			} else {
				
				$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_SHIPPING_'+(i+1)+'" value="'+shipping_cost_breakdown+'"> '); // The value here needs to be non-zero
			}
		}
		if (!this.debug_mode) {
			$('form[name="BongoCheckoutForm"]').submit();
		} else {
			var conf = confirm('Proceed to Checkout?');
			if (conf) { 
				$('form[name="BongoCheckoutForm"]').submit(); 
			} else { return; }
		}
	},
	
	ExtractProductSKU: function(link)
	{
		var link_str = link.toString();
		var product_id = link_str.split('/')[4];
		return product_id;
	},

	
	GetProductSKUs: function()
	{
		var links = $('table.CartContents td.ProductName a');
		var product_skus = [];
		var j = 0;
		
		for (var i=0; i < links.length; i++)
		{
			product_sku = BongoCheckout.ExtractProductSKU(links[i]);
			if (product_sku) {
				product_skus[j] = product_sku;
				j++;
				//console.log('Product SKU: ' + product_sku);
			}
		}
		
		return product_skus;
	},
	
	GetProductPrices: function()
	{
		var i = 0;
		var prices = new Array();
		
		$('td.CartItemIndividualPrice').each(function()
		{
			var ptmp = BongoCheckout.trim( $(this).html() );
			prices[i] = ptmp.substr(1, $(this).html().length);
			//console.log('Price: ' + prices[i]);
			i++;
		});
		
		return prices;
	},
	
	GetProductQuantities: function()
	{
		var i = 0;
		var quant = new Array();
		
		$('select.quantityInput').each(function()
		{

			quant[i] = BongoCheckout.trim( $('option:selected',this).text() );
			i++;
		});
		
		return quant;
	},
	
	GetProductNames: function()
	{
		var i = 0;
		var names = new Array();
		
		$('table.CartContents td.ProductName').each(function()
		{
			names[i] = BongoCheckout.trim( $('a:first', this).text() );
			//console.log('Name: ' + names[i]);
			i++;
		});
		
		return names;	
	},
	
}
