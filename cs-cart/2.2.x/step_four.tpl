{* $Id$ *}

<div class="step-container{if $edit}-active{/if}" id="step_four">
	<h2 class="step-title{if $edit}-active{/if}">
		<span class="float-left">{if $profile_fields.B || $profile_fields.S}4{else}3{/if}.</span>
		
		<span class="title">
		<!-- Bongo -->
		{if $user_data.s_country == "US"}
		{$lang.review_and_place_order}
		{else}
		Review Order and Proceed to Payment
		{/if}
		</span>
		
		{if $edit && $cart|fn_allow_place_order && !$iframe_mode}
			<span class="float-right">
				<!-- Bongo -->
				{if $user_data.s_country == "US"}
					<div class="buttons-container right clear-both">
						{include file="buttons/place_order.tpl" but_onclick="$('#place_order').click();" but_role="big"}
					</div>
				{else}
					<div class="buttons-container right clear-both">
						{include file="buttons/place_bongo_order.tpl" but_onclick="$('#place_order').click();" but_role="big"}
					</div>
				{/if}				
			</span>
		{/if}
	</h2>

	<div id="step_four_body" class="step-body{if $edit}-active{/if} {if !$edit && !$complete}hidden{/if}">
		<div class="clear">
			{if $edit}
				{if $settings.General.checkout_style == "multi_page"}
					{* Display summary customer info (payment, shipping, etc...) *}
					{* Shipping/billing information *}
					{if $settings.General.address_position == "billing_first"}
						{assign var="first_section" value="B"}
						{assign var="first_section_text" value=$lang.billing_address}
						{assign var="sec_section" value="S"}
						{assign var="sec_section_text" value=$lang.shipping_address}
						{assign var="ship_to_another_text" value=$lang.text_ship_to_billing}
						{assign var="body_id" value="sa"}
					{else}
						{assign var="first_section" value="S"}
						{assign var="first_section_text" value=$lang.shipping_address}
						{assign var="sec_section" value="B"}
						{assign var="sec_section_text" value=$lang.billing_address}
						{assign var="ship_to_another_text" value=$lang.text_billing_same_with_shipping}
						{assign var="body_id" value="ba"}
					{/if}
					
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="margin-top">
					<tr valign="top">
					{if $profile_fields[$first_section]}
						<td width="45%">
							{include file="views/profiles/components/step_profile_fields.tpl" section=$first_section text=$first_section_text}
						</td>
					{/if}
					<td width="10%">&nbsp;</td>
						<td width="45%">
						{if $profile_fields[$sec_section]}
								{if $cart.ship_to_another}
									{include file="views/profiles/components/step_profile_fields.tpl" section=$sec_section text=$sec_section_text}
								{else}
									<div class="step-complete-wrapper clear">
										<strong class="float-left">{$sec_section_text}: &nbsp;</strong>
										<p class="no-padding overflow-hidden">{$ship_to_another_text}</p>
									</div>
								{/if}
						{/if}
						</td>
					</tr>
					</table>
					
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="cc-infobox">
					<tr valign="top">
						<td width="45%">
							{* Payment information *}
							<div class="step-complete-wrapper">
								{if $cart.payment_id}
									<strong>{$lang.payment_method}:</strong> &nbsp;{$payment_info.payment};
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
							{* Shipping information *}
							<div class="step-complete-wrapper">
								<strong>{$lang.shipping_method}: &nbsp;</strong>
								{if $cart.shipping_required == true}
									{include file="views/checkout/components/shipping_rates.tpl" no_form=true display="show"}
								{else}
									{$lang.free_shipping}
								{/if}
							</div>
						</td>
					</tr>
					</table>
				{/if}
			
				<table class="table review margin-top" width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<th colspan="2" class="left">{$lang.products_in_your_order}</th>
					</tr>
					{hook name="checkout:summary_products_row"}
					{foreach from=$cart_products key="key" item="product"}
						{if !$cart.products.$key.extra.parent}
						<tr {cycle values=",class=\"table-row\""}>
							<td width="77%">
								{hook name="checkout:summary_products"}
									<a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="product-title">{$product.product|unescape}</a>
									{if $product.product_code}<p class="sku">{$lang.code}: {$product.product_code}</p>{/if}
									{include file="common_templates/options_info.tpl" product_options=$product.product_options no_block=true}
								{/hook}
							</td>
							<td width="10%" class="center"><strong>{$product.amount}&nbsp;x&nbsp;{include file="common_templates/price.tpl" value=$product.display_price}</strong>&nbsp;=&nbsp;{include file="common_templates/price.tpl" value=$product.display_subtotal class="price"}</td>
						</tr>
						{/if}
					{/foreach}
					{/hook}
				</table>
				
				{if $cart|fn_allow_place_order}
					<!-- Bongo -->
                    {if $user_data.s_country != "US"}
	                    <form action="{$bongous_url}" method="post" name="bongous_form">
    	            {else} 
						<form action="{""|fn_url}" method="post" name="summary_form" id="summary_form">
					{/if}
					<div class="clear">
						{include file="views/checkout/components/checkout_totals_info.tpl"}
						{if !$iframe_mode}
							{include file="views/checkout/components/customer_notes.tpl"}
						{/if}
					</div>
					
					{if $cart_agreements || $settings.General.agree_terms_conditions == "Y"}
						<script type="text/javascript">
						//<![CDATA[
						lang.checkout_terms_n_conditions_alert = '{$lang.checkout_terms_n_conditions_alert|escape:javascript}';
						{literal}
						function fn_check_agreement(id)
						{
							if (!$('#' + id).attr('checked')) {
								return lang.checkout_terms_n_conditions_alert;
							}

							return true;
						}
						{/literal}
						
						{if $iframe_mode}
							{literal}
							function fn_check_agreements()
							{
								if ($('#summary_form input:checkbox:checked').length > 0 && $('#summary_form input:checkbox:checked').length == $('#summary_form input:checkbox').length) {
									$('#payment_method_iframe').addClass('hidden');
								} else {
									$('#payment_method_iframe').removeClass('hidden');
								}
							}
							{/literal}
						{/if}
						//]]>
						</script>

						<table width="100%" cellpadding="3" cellspacing="0" border="0">
						<tr valign="top">
							<td>
							{if $settings.General.agree_terms_conditions == "Y"}
							<div class="form-field margin-top">
								{hook name="checkout:terms_and_conditions"}
								
								<label for="id_accept_terms" class="valign cm-custom (check_agreement)"><input type="checkbox" id="id_accept_terms" name="accept_terms" value="Y" class="checkbox valign" {if $iframe_mode}onclick="fn_check_agreements();"{/if} />{$lang.checkout_terms_n_conditions}</label>
								{/hook}
							</div>
							{/if}
							{if $cart_agreements}
							<div class="form-field">
								{hook name="checkout:terms_and_conditions_downloadable"}
								
								<label for="product_agreements" class="valign cm-custom (check_agreement)"><input type="checkbox" id="product_agreements" name="agreements[]" value="Y" class="valign checkbox"  {if $iframe_mode}onclick="fn_check_agreements();"{/if}/>{$lang.checkout_edp_terms_n_conditions}</label>{include file="buttons/button.tpl" but_text=$lang.license_agreement but_role="text" but_id="sw_elm_agreements" but_meta="cm-combination"}
								{/hook}
								<div class="hidden" id="elm_agreements">
								{foreach from=$cart_agreements item="product_agreements"}
									{foreach from=$product_agreements item="agreement"}
									<p>{$agreement.license|unescape}</p>
									{/foreach}
								{/foreach}
								</div>
							</div>
							{/if}
							</td>
							<td valign="bottom">
						{/if}
						
						{if !$iframe_mode}
							{if $user_data.s_country == "US"}
								<div class="buttons-container right clear-both">
									{include file="buttons/place_order.tpl" but_name="dispatch[checkout.place_order]" but_role="big" but_id="place_order"}
								</div>
							{else}
								<div class="buttons-container right clear-both">
									{include file="buttons/place_bongo_order.tpl" but_name="dispatch[checkout.place_order]" but_role="big" but_id="place_order"}
								</div>
							{/if}
						{/if}
						
						{if $cart_agreements || $settings.General.agree_terms_conditions == "Y"}
							</td>
						</tr>
						</table>
					{/if}
                    	<input type="hidden" name="PARTNER_KEY" id="edit-PARTNER-KEY" value="{$partner_key}"  />
                        <input type="hidden" name="CUST_FIRST_NAME" id="CUST_FIRST_NAME" value="{$cart.user_data.s_firstname}"  />
                        <input type="hidden" name="CUST_LAST_NAME" id="CUST_LAST_NAME" value="{$cart.user_data.s_lastname}"  />
                        <input type="hidden" name="CUST_COMPANY" id="CUST_COMPANY" value="{$cart.user_data.company}"  />
                        <input type="hidden" name="CUST_COUNTRY" id="CUST_COUNTRY" value="{$cart.user_data.s_country}"  />
                        <input type="hidden" name="CUST_ADDRESS_LINE_1" id="CUST_ADDRESS_LINE_1" value="{$cart.user_data.s_address}"  />
                        <input type="hidden" name="CUST_ADDRESS_LINE_2" id="CUST_ADDRESS_LINE_2" value="{$cart.user_data.s_address_2}"  />
                        <input type="hidden" name="CUST_CITY" id="CUST_CITY" value="{$cart.user_data.s_city}"  />
                        <input type="hidden" name="CUST_STATE" id="CUST_STATE" value="{$cart.user_data.s_state}"  />
                        <input type="hidden" name="CUST_ZIP" id="CUST_ZIP" value="{$cart.user_data.s_zipcode}"  />
                        <input type="hidden" name="CUST_PHONE" id="CUST_PHONE" value="{$cart.user_data.s_phone}"  />
                        <input type='hidden' name='CUST_EMAIL' id='CUST_EMAIL' value='{$cart.user_data.email}'  />
                    {assign var="count" value=1}
					{foreach from=$cart_products key="key" item="product"}
							<input id='PRODUCT_ID_{$count}' type='hidden' value='{$product.product_code}' name='PRODUCT_ID_{$count}'>
                            <input id='PRODUCT_NAME_{$count}' type='hidden' value='{$product.product}' name='PRODUCT_NAME_{$count}'>
                            <input id='PRODUCT_PRICE_{$count}' type='hidden' value='{$product.display_price}'  name = 'PRODUCT_PRICE_{$count}'>
                            <input id='PRODUCT_Q_{$count}' type='hidden' value='{$product.amount}' name='PRODUCT_Q_{$count}'>                        
			                {if $count++}{/if}
					{/foreach}
					
					{if $auth.act_as_user}
						<div class="select-field">
							<input type="checkbox" id="skip_payment" name="skip_payment" value="Y" class="checkbox" />
							<label for="skip_payment">{$lang.skip_payment}</label>
						</div>
					{/if}
					</form>
					
					{if $iframe_mode}
						<div class="payment_method_iframe_box">
							<iframe width="100%" height="820" id="order_iframe_{$smarty.const.TIME}" src="{"checkout.process_payment"|fn_url:$smarty.const.AREA:'checkout'}" style="border: 0px" frameBorder="0" ></iframe>
							{if $cart_agreements || $settings.General.agree_terms_conditions == "Y"}
							<div id="payment_method_iframe" class="payment_method_iframe">
								<div class="payment_method_iframe_label">
									<div class="payment_method_iframe_text">{$lang.checkout_terms_n_conditions_alert}</div>
								</div>
							</div>
							{/if}
						</div>
					{/if}
				{else}
					{if $cart.shipping_failed}
					<p class="error-text center">{$lang.text_no_shipping_methods}</p>
					{/if}
					{if $cart.amount_failed}
					<p class="error-text center">{$lang.text_min_order_amount_required}&nbsp;<strong>{include file="common_templates/price.tpl" value=$settings.General.min_order_amount}</strong></p>
					{/if}

					{if $settings.General.checkout_style == "multi_page"}
					<div class="buttons-container center">
						{include file="buttons/continue_shopping.tpl" but_href=$continue_url|default:$index_script but_role="action"}
					</div>
					{/if}
				{/if}
			{/if}
		</div>
	</div>
<!--step_four--></div>