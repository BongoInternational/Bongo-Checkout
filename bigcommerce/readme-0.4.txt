Big Commerce Implementation Guide v0.4
Installation
1. Within your Big Commerce administration area, navigate to Users and select to Edit your account.
2. At the bottom of the account edit page, check the box labelled ‘Enable the API?’
3. Copy the API Token and save this for later.
4. Open bongocheckout.0.4.js in a text editor, and replace the following information, starting at line 31:
1. partnerKey = The Bongo partner key provided by your implementation specialist or obtained from your Bongo Partner Portal.
2. api_token = The API Token copied in step 3.
3. api_username = The username you use when logging into the administration area of Big Commerce (i.e. ‘admin’).


5. Upload bongocheckout.0.4.js to your Big Commerce content folder, via FTP.
6. Go to Design > Template Files > cart.html .Insert the code below BEFORE the closing div (MAKE SURE TO REPLACE "CHECKOUT URL" with the one we give you!)
        
                <form name="BongoCheckoutForm" method="post" action="CHECKOUT_URL"></form>
                <script type="text/javascript" src="content/bongocheckout.0.4.js"></script>
                <script type="text/javascript">BongoCheckout.Init();</script>


Below: Proper placement of the Bongo Checkout code snippet in the cart.html file.