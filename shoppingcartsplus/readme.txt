ShoppingCartsPlus Implementation Guide
---

Installation:
---
1. From the administration area click Edit Store
2. In the left-hand menu click Shipping
3. Paste the following code into the ‘Shipping Restriction Message’ area:

(NOTE: DO NOT add any spaces or insert new lines)


<input type="hidden" name="BONGO_CHECKOUT_URL" value="YOUR_CHECKOUT_URL" /><input type="hidden" name="BONGO_PARTNER_KEY" value="YOUR_PARTNER_KEY" /><script type="text/javascript" src="https://secure1411.hostgator.com/~bongo315/public/bongocheckout.shoppingcartsplus.js"></script>

4. Replace YOUR PARTNER KEY and YOUR CHECKOUT URL with the applicable information provided by your implementation specialist or obtained from your Partner Portal.