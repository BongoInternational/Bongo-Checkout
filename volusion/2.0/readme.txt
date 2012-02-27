Volusion Implementation Guide v2.0
---
NOTE: By default Volusion does not have FTP access enabled. To enable FTP access go to http://my.volusion.com. For detailed instructions on enabling this feature, please see http://support.volusion.com/article/setting-your-volusion-ftp-account
---

1. Edit bongocheckout.volusion.2.0.js and on line 102 enter your PARTNER_KEY.
2. Using the File Manager or via FTP, upload bongocheckout.volusion.2.0.js to your vspfiles folder.         
3. Under Design > Site Content, locate ‘ShoppingCart.asp’ > ‘Below_ShoppingCart’ and edit this area.
4. Copy the following code into the corresponding text area.




<form name="BongoCheckoutForm" action="YOUR-CHECKOUT-URL" method="post">
<script type="text/javascript" src="v/vspfiles/bongocheckout.volusion.2.0.js"></script>




5. Replace YOUR-CHECKOUT-URL in the above code with the corresponding information available through your Bongo Partner Portal or from your Product or Implementation Specialist.