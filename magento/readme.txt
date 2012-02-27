Magento Implementation Guide v1.2
Requirements
1. Magento v1.5+
2. A shipping method for international customers (see notes).
Installation


1. Copy the entire app folder into the root directory of where you installed Magento. No files will be replaced.
2. After all files have copied over, log into your Magento Administration area, and navigate to System > Configuration.
3. From the left-hand menu, choose Payment Methods.
4. If the installed worked, you will see Bongo Checkout listed as a payment method. Select it and enter your Partner Key and Checkout URL, then save.
Notes


Excluding Countries
If you wish to exclude certain countries from using Bongo Checkout (i.e. Canada), enter the 2-letter country code next to Excluded Countries. Separate multiple countries with commas.


Applying Domestic Shipping Charges
The Checkout module will carry over whatever shipping charges would apply with your current settings. It is recommended you set a flat rate that applies for any international customers. This can be done through Magento. For information on setting up Flat Rate shipping check here: http://www.magentocommerce.com/knowledge-base/entry/how-do-i-set-up-flat-rate-shipping-per-item-or-per-order