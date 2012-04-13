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

	1. Upload bongocheckout.js to your content folder.
	
	2. Change the following settings from 'Settings' > 'Store Settings'
	
		1. Display > Display Settings:
		
			Next to 'Add to Cart' Action choose 'Take Them to Their Shopping Cart'

		2. URL Structure > Product URL Settings:
		
			Set to Custom and enter in: /%productname%/%sku%/
			Click 'Save'
			Click 'Update Product URLs...'
			
		3. Edit Template Files > cart.html
		
			Insert the code below BEFORE the closing div (MAKE SURE TO REPLACE "CHECKOUT URL" with the one we give you!)
		
		
			<form name="BongoCheckoutForm" method="post" action="CHECKOUT URL">
				
			</form>

			<script type="text/javascript" src="content/bongocheckout.js"></script>
			<script type="text/javascript">
				BongoCheckout.Init();
			</script>
			
	3. Remove or comment out any Proceed to Checkout buttons that appear elsewhere in the shopping cart.
	4. Update the partnerKey below.
*/

String.prototype.stripHTML = function() {
	return this.replace(/(<([^>]+)>)/ig,'');
}

var BongoCheckout = {
	partnerKey:  'YOUR PARTNER KEY',
	prompt: 'Will this order require shipping outside of the United States? ',
	prompt2: 'International Order <br /><br /><font color="black">Shipping, duties, and taxes will be calculated on the next page.</font>',
	prompt3: 'Domestic U.S. Order <br /><br /><font color="black">Shipping, duties, and taxes will be calculated on the next page.</font>',
	image_url: 'http://public.bongocheckout.com/Bongo_Checkout_Button.png',
	use_image: true,
	
	trim : function(stringToTrim) { return stringToTrim.replace(/^\s+|\s+$/g,""); },
	
	Init: function()
	{
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
		
		//$('div.CheckoutButton').hide();
		
		
	},

	Submit: function() 
	{
		var product_skus = this.GetProductSKUs();
		var product_names = this.GetProductNames();
		var product_prices = this.GetProductPrices();
		var product_quantities = this.GetProductQuantities();
		var product_custom = this.GetProductCustom();
		
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
			
			if (product_custom[i]) {
				$('form[name="BongoCheckoutForm"]').append('<input type="hidden" name="PRODUCT_CUSTOM_1_'+x+'" value="'+product_custom[i]+'">');
			}
		}
		
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
	
	ExtractProductSKU: function(link)
	{
		var link_str = link.toString();
		var product_id = link_str.split('/')[4];
		return product_id;
	},
	
	GetProductCustom: function()
	{
		var data = [];
		var k = 0;
		$('table.productAttributes').each(function() {
			data[k] = $(this).html().stripHTML().replace(/(\r\n|\n|\r|\s{1,})/gm,' ').replace(/[^a-zA-Z 0-9 . :]+/gm,'');
			//console.log('Attr: ', data[k]);
		});
		
		return data;
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
			prices[i] = ptmp.substr(1, $(this).html().length).replace(',','');
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
