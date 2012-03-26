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

====
INSTALLATION
====

1. Open bongocheckout.0.4.js in a text editor, and replace line 30 where it says YOUR_PARTNER_KEY with the partner key given to you, or obtained through your partner portal.

2. Upload bongocheckout.0.4.js to your content folder.

3. Go to Design > Template Files > cart.html .Insert the code below BEFORE the closing div (MAKE SURE TO REPLACE "CHECKOUT URL" with the one we give you!)
	
	
		<form name="BongoCheckoutForm" method="post" action="CHECKOUT_URL"></form>
		<script type="text/javascript" src="content/bongocheckout.0.4.js"></script>
		<script type="text/javascript">BongoCheckout.Init();</script>

===
OPTIONS
===

partnerKey - The unique identifier key assigned to your account.

prompt - The message displayed above the button.

image_url - URL of the image to use for the button

debug - This can be left turned off. If you experience technical issues, our implementation specialist may ask you to set this to 'true' so they can better analyze the problem.
