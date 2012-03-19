Volusion Implementation Guide v2.2
------------------------------------

Installation
------------------------------------
NOTE: By default Volusion does not have FTP access enabled. To enable FTP access go to http://my.volusion.com. For detailed instructions on enabling this feature, please see http://support.volusion.com/article/setting-your-volusion-ftp-account


1. Edit bongocheckout.volusion.2.2.js and on line 23 enter your PARTNER_KEY.
2. Using the File Manager or via FTP, upload bongocheckout.volusion.2.2.js to your vspfiles folder.         
3. Under Design > Site Content, locate ‘ShoppingCart.asp’ > ‘Below_ShoppingCart’ and edit this area.
4. Copy the following code into the corresponding text area.


<form name="BongoCheckoutForm" action="YOUR-CHECKOUT-URL" method="post"></form>
<script type="text/javascript" src="v/vspfiles/bongocheckout.volusion.2.2.js"></script>


5. Replace YOUR-CHECKOUT-URL in the above code with the corresponding information available through your Bongo Partner Portal or from your Product or Implementation Specialist.


Customization
------------------------------------
There are a variety of modifications that can be made to the look and feel of the Bongo Checkout module. Within the included JavaScript file, starting at line 22 there are various parameters that you can change:


- partner_key - Your Bongo Checkout partner key.

------------

- message_abovebutton - The HTML displayed above the Bongo Checkout button.

- message_abovebutton_border_color - The border color surrounding the message that appears above the Proceed to Checkout buttons.

- message_abovebutton_bg_color - The background for the message that appears above Proceed to Checkout buttons.

------------

- message_belowshipping - The HTML displayed below the shipping estimate.

- message_belowshipping_border_color - The border color for the message that appears above the shipping estimate.

- message_belowshipping_bg_color - The background color for the message that appears above the shipping estimate.

------------

- button_image - URL of the image to use for the button. Defaults to one provided by Bongo.

- per_item_shipping - A boolean value indicating whether to apply a flat shipping cost per item, or per order. Setting this to true will charge a shipping cost for each item, setting this to false will apply a single shipping cost to the entire order.

- shipping_cost - The shipping cost to be used on a per-item or per-order basis.


Understanding per_item_shipping
------------------------------------
- When set to true, the customer will be charged shipping_cost for each item in their cart.
	Example: 
	---
	shipping_cost = $5
	Total number of items = 10

	Total shipping cost = 5 x 10 = $50

- When set to false, they will be charged a flat rate for the overall shipment.
	Example: 
	---
	shipping_cost = $5
	Total # of items = 10
	
	Total shipping cost = $5