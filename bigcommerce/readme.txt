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


====
INSTALLATION
====

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
4. Update the partnerKey and set per_item_shipping and shipping_cost in bongocheckout.js

===
OPTIONS
===

partnerKey - The unique identifier key assigned to your account.

prompt - The message displayed above the button.

image_url - URL of the image to use for the button

per_item_shipping - Setting this to true will charge the value of shipping_cost for EACH ITEM.

shipping_cost - Amount to charge for domestic shipping to the Bongo warehouse, on a per order or per item basis.

debug_mode - This can be left turned off. If you experience technical issues, our implementation specialist may ask you to set this to 'true' so they can better analyze the problem.
