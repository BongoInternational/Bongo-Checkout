Yahoo! Small Business Implementation Guide
---

NOTE: Currently the Bongo Checkout module for Yahoo! Small Business only supports Single-Page Checkout. To enable Single-Page Checkout, open the Checkout & Registration Manager, select Global Settings, and choose Single-Page.


1. Open the provided file bongocheckout.yahoo.js and on line 157 enter your partner key.
2. From the Store Manager, open Store Editor
3. From the administration bar along the top of the screen, select Files. If you do not see this option, click the arrow to expand the menu.
4. Click Upload
5. Select the provided file bongocheckout.yahoo.js, set the filename to bongocheckout.yahoo.js. Ensure you have already edited this file and added your partner key.
6. Click Send
7. Click Publish All Staged Files
8. Right click the link to the uploaded file and select Copy Link Address, or click on the link and copy the URL from your address bar.
9. Go back to the Store Manager (from the Files area, click Index then select Manager)
10. Open the Checkout & Registration Manager
11. Click Page Configuration
12. In the Page Message area, add the code below:


        <form name="internationalForm" action="[CHECKOUT_PAGE_URL]" method="post"></form>


13. Replace [CHECKOUT_PAGE_URL] with the applicable URL given to you in your Partner Portal (http://bongous.com/partner).
14. Further down on the page in the HTML Head Section, add the following code (make sure to insert the location of your bongocheckout.yahoo.js file!):


	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
	<script type"text/javascript" src="[URL OF YOUR bongocheckout.yahoo.js FILE]"></script>


15. Click Save
16. From the Store Manage page, under Order Settings click Publish