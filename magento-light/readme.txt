Magento (Light) Implementation Guide v1.5
---

The Light version of the Bongo Checkout module for Magento is simply a button that can be placed anywhere on the Checkout page to immediately re-direct the customer to Bongo Checkout. It will transfer over any customer information that has been entered.

Installation
---
1. Open bongo-checkout.php and modify lines 15 and 16 with your own Partner Key and Checkout URL.
2. Right-click and Copy the contents of bongo-checkout.php.
3. Open app/design/frontend/base/default/template/checkout/onepage/progress.phtml and Paste the code below just before the closing <div> tag in this file, just after line 110.


Note about Configurable Items:
---
For configurable items (i.e. DVD100-BLACK-SIZE1) , it will send over the base SKU (DVD100) as the PRODUCT_ID, and send over the configurable SKU (DVD100-BLACK-SIZE1) in the PRODUCT_CUSTOM field. When you receive the order information, you’ll just need to examine the PRODUCT_CUSTOM field to see what customization options the user has chosen.