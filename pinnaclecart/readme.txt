Bongo Checkout for Pinnacle Cart v1.0
-------------------------------


Installation:
-------------------------------
1. In the Pinnacle Cart admin area, select Cart Settings > Appearance Settings
2. In the sidebar select 'Activate Cart Designer'
3. While in cart designer mode, navigate to the shopping cart page.
4. Right click the main cart content area and select 'Edit Source Code'
5. Paste in the following code at the very bottom of the code displayed:

<!-- BONGO CHECKOUT FORM -->
<form name="BongoCheckoutForm" method="post" action="https://bongous.com/pay/d4122/index.php">
<input type="hidden" name="PARTNER_KEY" value="7625511ed3383939386558db5e52c27b">
{foreach from=$order_items item="order_item" name="bongo_loop"}
  <input type="hidden" name="PRODUCT_ID_{$smarty.foreach.bongo_loop.iteration}" value="{$order_item.product_id|htmlspecialchars}">
  <input type="hidden" name="PRODUCT_NAME_{$smarty.foreach.bongo_loop.iteration}" value="{$order_item.title|htmlspecialchars}">
  <input type="hidden" name="PRODUCT_PRICE_{$smarty.foreach.bongo_loop.iteration}" value="{$order_item.product_price}">
  <input type="hidden" name="PRODUCT_Q_{$smarty.foreach.bongo_loop.iteration}" value="{$order_item.quantity}">
  <input type="hidden" name="PRODUCT_SHIPPING_{$smarty.foreach.bongo_loop.iteration}" value="">
  <input type="hidden" name="PRODUCT_CUSTOM_1_{$smarty.foreach.bongo_loop.iteration}" value="{$order_item.options_clean|htmlspecialchars|nl2br}">
  <input type="hidden" name="PRODUCT_CUSTOM_2_{$smarty.foreach.bongo_loop.iteration}" value="">
  <input type="hidden" name="PRODUCT_CUSTOM_3_{$smarty.foreach.bongo_loop.iteration}" value="">
{/foreach}
</form>
<script type="text/javascript">
{literal}
$(document).ready(function() {
	$('#BongoCheckoutButton').unbind();
	$('#BongoCheckoutButton').click(function() {
		$('form[name="BongoCheckoutForm"]').submit();
	});
});
{/literal}
</script>
<!-- END BONGO CHECKOUT FORM -->


6. Change YOUR-PARTNER-KEY and YOUR-CHECKOUT-URL to the applicable information given to by your Product or Implementation Specialist.
7. Locate an area within your shopping cart template to insert the following code, which will place a button for customers to click that will transfer them to Bongo Checkout. You can insert this code as many times as you like if you want the button to appear in multiple places on the cart page.

<!-- BONGO CHECKOUT BUTTON -->
<input type="button" style="position:relative; float:right; clear:both; margin:10px 0; padding:5px;" id="BongoCheckoutButton" value="Are you outside the U.S. ?" />
<!-- END BONGO CHECKOUT BUTTON -->





