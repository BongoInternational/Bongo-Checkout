Bongo Checkout for Shopify v1.0
-------------------------------


Installation:
-------------------------------

1. Select Themes > Theme Editor
2. Under the Snippets panel, create two new snippets named 'bongo-checkout-button' and 'bongo-checkout-form'.
3. In the bongo-checkout-button snippet paste in the following code:


<input class="btn btn-reversed" style="position:relative; float:right; margin:10px 0;" type="submit" id="outside-the-us" name="BongoFormSubmit" value="Are you outside the U.S. ?" onclick="javascript: document.forms['BongoCheckoutForm'].submit(); return false;">


4. In the bongo-checkout-form snippet paste in the following code:


<form name="BongoCheckoutForm" method="post" action="YOUR-CHECKOUT-URL">
<input type="hidden" name="PARTNER_KEY" value="YOUR-PARTNER-KEY">
{% for item in cart.items %}  
      <input type="hidden" name="PRODUCT_ID_{{ forloop.index }}" value="{{ item.sku }}">
      <input type="hidden" name="PRODUCT_NAME_{{ forloop.index }}" value="{{ item.title }}">
      <input type="hidden" name="PRODUCT_PRICE_{{ forloop.index }}" value="{{ item.price | money_without_currency }}">
      <input type="hidden" name="PRODUCT_Q_{{ forloop.index }}" value="{{ item.quantity }}">
      <input type="hidden" name="PRODUCT_SHIPPING_{{ forloop.index }}" value="">
      <input type="hidden" name="PRODUCT_CUSTOM_1_{{ forloop.index }}" value="{{ item.variant.title }}">
      <input type="hidden" name="PRODUCT_CUSTOM_2_{{ forloop.index }}" value="">
      <input type="hidden" name="PRODUCT_CUSTOM_3_{{ forloop.index }}" value="">

{% endfor %}
</form>

6. In the above code replace YOUR-PARTNER-KEY and YOUR-CHECKOUT-URL with the applicable information from your Product or Implementation Specialist.
5. Under Templates, open the 'cart' template.
6. At the very bottom of the cart template, insert the following:


{% include 'bongo-checkout-form' %}


7. Locate an area where you'd like to place the 'Outside the U.S.?' button, and insert the following code:


{% include 'bongo-checkout-button' %}