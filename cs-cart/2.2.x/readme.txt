Bongo Checkout for CS-Cart v1.1
Implementation Guide
---

REQUIRES: Cs-Cart v2.2.x

---

1. Unzip the files for the Bongo Checkout module for CS-Cart
2. On the server where CS-Cart is hosted, backup the following files:


<root> /skins/[current skin]/customer/views/checkout/components/steps/step_four.tpl
<root> /skins/[current skin]/customer/views/checkout/components/steps/step_three.tpl
<root>/controllers/customer/checkout.php


3. Upload the necessary files.

Upload the step_four.tpl and step_three.tpl files to:
<root> /skins/[current skin]/customer/views/checkout/components/steps/

Upload place_bongo_order.tpl to:
<root> /skins/[current skin]/customer/buttons/

Upload checkout.php to:
<root> /controllers/customer/

3. Open <root>/config.php and add the following code to the end of this file.


        define('PARTNER_KEY', 'YOUR PARTNER KEY');
        define('BONGOUS_ACTION', 'YOUR CHECKOUT URL');


1. Replace YOUR PARTNER KEY and YOUR CHECKOUT URL with your partner-specific information we provide.