Bongo Checkout for CS-Cart v1.0 
Implementation Guide
---

REQUIRES: Cs-Cart v2.1.x

---

1. Unzip the files for the Bongo Checkout module for CS-Cart
2. On the server where CS-Cart is hosted, backup the following files:


<root> /skins/[current skin]/customer/views/checkout/components/steps/step_four.tpl
<root> /skins/[current skin]/customer/views/checkout/components/steps/step_three.tpl
<root>/controllers/customer/checkout.php


1. Upload the step_four.tpl and step_three.tpl files to <root> /skins/[current skin]/customer/views/checkout/components/steps/
2. Uploader the controllers folder on your computer to your root CS-Cart folder.
3. Open <root>/config.php and add the following code to the end of this file.


        define('PARTNER_KEY', 'YOUR PARTNER KEY');
        define('BONGOUS_ACTION', 'YOUR CHECKOUT URL');


1. Replace YOUR PARTNER KEY and YOUR CHECKOUT URL with your partner-specific information we provide.