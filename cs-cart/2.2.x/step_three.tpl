{if $user_data.s_country=="US"}
<div class="step-container{if $edit}-active{/if}" id="step_three">
	<h2 class="step-title{if $edit}-active{/if}">
		<span class="float-left">{if $profile_fields.B || $profile_fields.S}3{else}2{/if}.</span>

		{if $complete && !$edit}
			<img src="{$images_dir}/icons/icon_step_close.gif" width="19" height="17" border="0" alt="" class="float-right" />
		{/if}
		
		{hook name="checkout:edit_link_title"}
		<a class="title{if $complete && !$edit} cm-ajax{/if}" {if $complete && !$edit}href="{"checkout.checkout?edit_step=step_three&amp;from_step=`$edit_step`"|fn_url}" rev="checkout_steps"{/if}>{$lang.payment_and_shipping}</a>
		{/hook}
	</h2>

	<div id="step_three_body" class="step-body{if $edit}-active{/if} {if !$edit && !$complete}hidden{/if}">
		<div class="clear">
			{if $edit}
				<form name="step_three_payment_and_shipping" class="{$ajax_form} {$ajax_form_force}" action="{""|fn_url}" method="{if !$edit}get{else}post{/if}">
				<input type="hidden" name="update_step" value="step_three" />
				<input type="hidden" name="next_step" value="step_four" />
				<input type="hidden" name="result_ids" value="checkout_steps,checkout_cart" />
				
				<div class="clear">
					<div class="float-left">
						{if $cart.payment_id}
							{include file="common_templates/subheader.tpl" title=$lang.select_payment_method}
							{include file="views/checkout/components/payment_methods.tpl" no_mainbox="Y"}
						{else}
							{$lang.text_no_payments_needed}
						{/if}
					</div>
					<div class="float-right">
					{hook name="checkout:select_shipping"}
						{if !$cart.shipping_failed}
							{include file="common_templates/subheader.tpl" title=$lang.select_shipping_method}
							{include file="views/checkout/components/shipping_rates.tpl" no_form=true display="radio"}
						{else}
							<p class="error-text center">{$lang.text_no_shipping_methods}</p>
						{/if}
					{/hook}
					</div>
				</div>
				
				{if $edit}
					<div class="buttons-container hidden">
						{include file="buttons/button.tpl" but_name="dispatch[checkout.update_steps]" but_text=$but_text but_id="step_three_but"}
					</div>
				{/if}
				</form>
				
				{if $cart_products}
					{capture name="cart_promotions"}
						{if $cart.has_coupons}
							{include file="views/checkout/components/promotion_coupon.tpl" location=$location}
						{/if}
						
						{hook name="checkout:payment_extra"}
						{/hook}
					{/capture}
					{if $smarty.capture.cart_promotions|trim}
						<div class="coupon-code-container">
							{$smarty.capture.cart_promotions}
						</div>
					{/if}
				{/if}
				
				{if $edit}
					<div class="buttons-container">
						{include file="buttons/button.tpl" but_onclick="$('#step_three_but').click();" but_text=$but_text}
					</div>
				{/if}
			{else}
				{if $completed_steps.step_three}
				<table width="92%">
					<tr valign="top"><td width="45%">
						<div class="step-complete-wrapper">
							{if $cart.payment_id}
									<strong>{$lang.payment_method}: &nbsp;</strong>{$payment_info.payment};
									{if $cart.extra_payment_info.card_number}
										{foreach from=$credit_cards item="card"}
											{if $card.param == $cart.extra_payment_info.card}
												{$card.descr}:&nbsp;{$cart.extra_payment_info.secure_card_number}&nbsp;{$lang.exp}:&nbsp;{$cart.extra_payment_info.expiry_month}/{$cart.extra_payment_info.expiry_year}
											{/if}
										{/foreach}
									{/if}
							{else}
								{$lang.text_no_payments_needed}
							{/if}
						</div>
					</td>
					<td width="10%">&nbsp;</td>
					<td width="45%">
						<div class="step-complete-wrapper">
						{hook name="checkout:select_shipping_complete"}
							<strong>{$lang.shipping_method}: &nbsp;</strong>
							{if $cart.shipping_required == true}
								{include file="views/checkout/components/shipping_rates.tpl" no_form=true display="show"}
							{else}
								{$lang.free_shipping}
							{/if}
						{/hook}
						</div>
					</td></tr>
				</table>
				{/if}
			{/if}
		</div>

		{if $complete && !$edit}
			{hook name="checkout:edit_link"}
			<div class="right">
				{include file="buttons/button.tpl" but_meta="cm-ajax" but_href="checkout.checkout?edit_step=step_three&amp;from_step=$edit_step" but_rev="checkout_steps" but_text=$lang.change but_role="tool"}
			</div>
			{/hook}
		{/if}
	</div>
<!--step_three--></div>
{else}
<div class="step-container{if $edit}-active{/if}" id="step_three">
	<h2 class="step-title{if $edit}-active{/if}">
		<span class="float-left">{if $profile_fields.B || $profile_fields.S}3{else}2{/if}.</span>

		{if $complete && !$edit}
			<img src="{$images_dir}/icons/icon_step_close.gif" width="19" height="17" border="0" alt="" class="float-right" />
		{/if}
		
		<a class="title{if $complete && !$edit} cm-ajax{/if}" {if $complete && !$edit}href="{"checkout.checkout?edit_step=step_three&amp;from_step=`$edit_step`"|fn_url}" rev="checkout_steps"{/if}>{assign var="giftcoupon" value='International Order - Special Instructions'}{$giftcoupon}</a>
	</h2>

	<div id="step_three_body" class="step-body{if $edit}-active{/if} {if !$edit && !$complete}hidden{/if}">
		<div class="clear">
			{if $edit}
				<form name="step_three_payment_and_shipping" class="{$ajax_form} {$ajax_form_force}" action="{""|fn_url}" method="{if !$edit}get{else}post{/if}">
				<input type="hidden" name="update_step" value="step_three" />
				<input type="hidden" name="next_step" value="step_four" />
				<input type="hidden" name="result_ids" value="checkout_steps" />
				
				{assign var="international_txt" value="We have partnered with Bongo International to offer international shipping. Simply continue with the checkout process and on the next page you will be able to see the full international shipping costs for this order and enter your payment information. "}{$international_txt}
				{if $edit}
					<div class="buttons-container hidden">
						{include file="buttons/button.tpl" but_name="dispatch[checkout.update_steps]" but_text=$but_text but_id="step_three_but"}
					</div>
				{/if}
                
				
               	{if $edit}
                     <div class="buttons-container">
                        {include file="buttons/button.tpl" but_onclick="$('#step_three_but').click();" but_text=$but_text}
                     </div>
				{/if}
				</form>
				
				
				
			{/if}
            {if $complete && !$edit}
			<div class="right">
				{include file="buttons/button.tpl" but_meta="cm-ajax" but_href="checkout.checkout?edit_step=step_three&amp;from_step=$edit_step" but_rev="checkout_steps" but_text=$lang.change but_role="tool"}
			</div>
		{/if}
	</div>
<!--step_three--></div>
{/if}
