{php}

$bongo_checkout_url = 'https://bongous.com/pay/d4122/index.php';
$bongo_partner_key = '7625511ed3383939386558db5e52c27b';

{/php}

<form name="BongoCheckoutForm" id="BongoCheckoutForm" method="post" action="{php} echo $bongo_checkout_url; {/php}">

<input type="hidden" name="PARTNER_KEY" value="{php} echo $bongo_partner_key; {/php}" />

{php} $k = 0; {/php}

{foreach from=$products item=product name=products}
	<input type="hidden" name="PRODUCT_ID_{php}echo $k + 1;{/php}" value="{$product.productcode}" />
	<input type="hidden" name="PRODUCT_NAME_{php}echo $k + 1;{/php}" value="{$product.product}" />
	<input type="hidden" name="PRODUCT_PRICE_{php}echo $k + 1;{/php}" value="{$product.display_price}" />
	<input type="hidden" name="PRODUCT_Q_{php}echo $k + 1;{/php}" value="{$product.amount}" />
	<input type="hidden" name="PRODUCT_SHIPPING_{php}echo $k + 1;{/php}" value="{$bongo_domestic_rate}" />
	<input type="hidden" name="PRODUCT_CUSTOM_1_{php}echo $k + 1;{/php}" value="{foreach from=$product.product_options item=option name=product} - {$option.class}: {$option.option_name}{/foreach} ">
	{php} $k++; {/php}
{/foreach}

</form>

