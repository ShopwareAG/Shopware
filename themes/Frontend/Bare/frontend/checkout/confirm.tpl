{extends file="frontend/index/index.tpl"}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops' append}
    {if $theme.checkoutHeader}
        <a href="{url controller='index'}"
           class="btn is--small btn--back-top-shop is--icon-left"
           title="{"{s name='FinishButtonBackToShop' namespace='frontend/checkout/finish'}{/s}"|escape}"
           xmlns="http://www.w3.org/1999/html">
            <i class="icon--arrow-left"></i>
            {s name="FinishButtonBackToShop" namespace="frontend/checkout/finish"}{/s}
        </a>
    {/if}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
    {include file="frontend/register/steps.tpl" sStepActive="finished"}
{/block}

{* Hide top bar *}
{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Footer *}
{block name="frontend_index_footer"}
    {if !$theme.checkoutFooter}
        {$smarty.block.parent}
    {else}
        {block name='frontend_index_checkout_confirm_footer'}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="content confirm--content">

    {* Error messages *}
    {block name='frontend_checkout_confirm_error_messages'}
        {include file="frontend/checkout/error_messages.tpl"}
    {/block}

    {* AGB and Revocation *}
    {block name='frontend_checkout_confirm_tos_panel'}
        <div class="tos--panel panel has--border">

            {block name='frontend_checkout_confirm_tos_panel_headline'}
                <div class="panel--title primary is--underline">
                    {s name="ConfirmHeadlineAGBandRevocation"}{/s}
                </div>
            {/block}

            <div class="panel--body is--wide">

                {* Right of revocation notice *}
                {block name='frontend_checkout_confirm_tos_revocation_notice'}
                    {if {config name=revocationnotice}}
                        <div class="body--revocation" data-modalbox="true" data-targetSelector="a" data-mode="ajax" data-height="500" data-width="750">
                            {s name="ConfirmTextRightOfRevocationNew"}<p>Bitte beachten Sie bei Ihrer Bestellung auch unsere <a href="{url controller=custom sCustom=8 forceSecure}{/s}
                        </div>
                    {/if}
                {/block}

                <form id="confirm--form" method="post" action="{if $sPayment.embediframe || $sPayment.action}{url action='payment'}{else}{url action='finish'}{/if}">
                    {* Hidden field for the user comment *}
                    <textarea class="is--hidden user-comment--hidden" rows="1" cols="1" name="sComment">{$sComment|escape}</textarea>

                    <ul class="list--checkbox list--unstyled">

                        {* Terms of service *}
                        {block name='frontend_checkout_confirm_agb'}
                            <li class="block-group row--tos">
                                {* Terms of service  checkbox *}
                                {block name='frontend_checkout_confirm_agb_checkbox'}
                                    <div class="block column--checkbox">
                                        {if !{config name='IgnoreAGB'}}
                                            <input type="checkbox" required="required" aria-required="true" id="sAGB" name="sAGB"{if $sAGBChecked} checked="checked"{/if} />
                                        {/if}
                                    </div>
                                {/block}

                                {* AGB label *}
                                {block name='frontend_checkout_confirm_agb_label'}
                                    <div class="block column--label">
                                        <label for="sAGB"{if $sAGBError} class="has--error"{/if} data-modalbox="true" data-targetSelector="a" data-mode="ajax" data-height="500" data-width="750">{s name="ConfirmTerms"}{/s}</label>
                                    </div>
                                {/block}
                            </li>
                        {/block}

                        {* Service articles and ESD articles *}
                        {block name='frontend_checkout_confirm_service_esd'}
                            <li class="block-group row--tos">
                                {block name='frontend_checkout_confirm_service'}
                                    {if $hasServiceArticles}
                                        {block name='frontend_checkout_confirm_service_checkbox'}
                                            <div class="block column--checkbox">
                                                <input type="checkbox" required="required" aria-required="true" name="serviceAgreementChecked" id="serviceAgreementChecked"{if $serviceAgreementChecked} checked="checked"{/if} />
                                            </div>
                                        {/block}

                                        {block name='frontend_checkout_confirm_service_label'}
                                            <div class="block column--label">
                                                <label for="swagCRDServiceBox"{if $agreementErrors && $agreementErrors.serviceError} class="has--error"{/if}>
                                                    {s name="AcceptServiceMessage"}{/s}
                                                </label>
                                            </div>
                                        {/block}
                                    {/if}
                                {/block}

                                {block name='frontend_checkout_confirm_esd'}
                                    {if $hasEsdArticles}
                                        {block name='frontend_checkout_confirm_esd_checkbox'}
                                            <div class="block column--checkbox">
                                                <input type="checkbox" required="required" aria-required="true" name="esdAgreementChecked" id="esdAgreementChecked"{if $esdAgreementChecked} checked="checked"{/if} />
                                            </div>
                                        {/block}

                                        {block name='frontend_checkout_confirm_esd_label'}
                                            <div class="block column--label">
                                                <label for="esdAgreementChecked"{if $agreementErrors && $agreementErrors.esdError} class="has--error"{/if}>
                                                    {s name="AcceptEsdMessage"}{/s}
                                                </label>
                                            </div>
                                        {/block}
                                    {/if}
                                {/block}
                            </li>
                        {/block}

                        {* Newsletter sign up checkbox *}
                        {block name='frontend_checkout_confirm_newsletter'}
                            {if !$sUserData.additional.user.newsletter && {config name=newsletter}}
                                <li class="block-group row--newsletter">

                                    {* Newsletter checkbox *}
                                    {block name='frontend_checkout_confirm_newsletter_checkbox'}
                                        <div class="block column--checkbox">
                                            <input type="checkbox" name="sNewsletter" id="sNewsletter" value="1"{if $sNewsletter} checked="checked"{/if} />
                                        </div>
                                    {/block}

                                    {* Newsletter label *}
                                    {block name='frontend_checkout_confirm_newsletter_label'}
                                        <div class="block column--label">
                                            <label for="sNewsletter">
                                                {s name="ConfirmLabelNewsletter"}{/s}
                                            </label>
                                        </div>
                                    {/block}
                                </li>
                            {/if}
                        {/block}
                    </ul>
                </form>

                {* Additional custom text field which can be used to display the terms of services *}
                {block name="frontend_checkout_confirm_additional_free_text_display"}
                    {if {config name=additionalfreetext}}
                        <div class="notice--agb">
                            {s name="ConfirmTextOrderDefault"}{/s}
                        </div>
                    {/if}
                {/block}

                {* Additional notice - bank connection *}
                {block name="frontend_checkout_confirm_bank_connection_notice"}
                    {if {config name=bankConnection}}
                        <p class="notice--change-now">
                            {s name="ConfirmInfoChange"}{/s}
                        </p>

                        <p class="notice--payment-data">
                            {s name="ConfirmInfoPaymentData"}{/s}
                        </p>
                    {/if}
                {/block}
            </div>
        </div>
    {/block}

    <div class="panel--group block-group information--panel-wrapper">
        {* Billing address *}
        {block name='frontend_checkout_confirm_billing_address_panel'}
            <div class="panel has--border block information--panel billing--panel">

                {* Headline *}
                {block name='frontend_checkout_confirm_left_billing_address_headline'}
                    <div class="panel--title is--underline">
                        {s name="ConfirmHeaderBilling" namespace="frontend/checkout/confirm_left"}{/s}
                    </div>
                {/block}

                {* Content *}
                <div class="panel--body is--wide">
                    {block name='frontend_checkout_confirm_left_billing_address'}
                        {if $sUserData.billingaddress.company}
                            <strong>{$sUserData.billingaddress.company}{if $sUserData.billingaddress.department}<br />{$sUserData.billingaddress.department}{/if}</strong>
                            <br>
                        {/if}

                        {if $sUserData.billingaddress.salutation eq "mr"}
                            {s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm_left"}{/s}
                        {else}
                            {s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm_left"}{/s}
                        {/if}

                        {$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
                        {$sUserData.billingaddress.street}<br />
                        {if $sUserData.billingaddress.additional_address_line1}{$sUserData.billingaddress.additional_address_line1}<br />{/if}
                        {if $sUserData.billingaddress.additional_address_line2}{$sUserData.billingaddress.additional_address_line2}<br />{/if}
                        {if {config name=showZipBeforeCity}}{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}{else}{$sUserData.billingaddress.city} {$sUserData.billingaddress.zipcode}{/if}<br />
                        {if $sUserData.additional.state.statename}{$sUserData.additional.state.statename}<br />{/if}
                        {$sUserData.additional.country.countryname}
                    {/block}

                    {* Action buttons *}
                    {block name="frontend_checkout_confirm_left_billing_address_actions"}
                        <div class="panel--actions">
                            <a href="{url controller=account action=billing sTarget=checkout}" class="btn is--small">
                                {s name="ConfirmLinkChangeBilling" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                            <a href="{url controller=account action=selectBilling sTarget=checkout}" class="btn is--small">
                                {s name="ConfirmLinkSelectBilling" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                        </div>
                    {/block}
                </div>
            </div>
        {/block}

        {* Shipping address *}
        {block name='frontend_checkout_confirm_shipping_address_panel'}
            <div class="panel has--border block information--panel shipping--panel">
                {block name='frontend_checkout_confirm_left_shipping_address_headline'}
                    <div class="panel--title is--underline">
                        {s name="ConfirmHeaderShipping" namespace="frontend/checkout/confirm_left"}{/s}
                    </div>
                {/block}

                {* Content *}
                <div class="panel--body is--wide">
                    {block name='frontend_checkout_confirm_left_shipping_address'}
                        {if $sUserData.shippingaddress.company}
                            <strong>{$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department}<br />{$sUserData.shippingaddress.department}{/if}</strong>
                            <br>
                        {/if}

                        {if $sUserData.shippingaddress.salutation eq "mr"}
                            {s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm_left"}{/s}
                        {else}
                            {s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm_left"}{/s}
                        {/if}

                        {$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br/>
                        {$sUserData.shippingaddress.street}<br />
                        {if $sUserData.shippingaddress.additional_address_line1}{$sUserData.shippingaddress.additional_address_line1}<br />{/if}
                        {if $sUserData.shippingaddress.additional_address_line2}{$sUserData.shippingaddress.additional_address_line2}<br />{/if}
                        {if {config name=showZipBeforeCity}}{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}{else}{$sUserData.shippingaddress.city} {$sUserData.shippingaddress.zipcode}{/if}<br />
                        {if $sUserData.additional.stateShipping.statename}{$sUserData.additional.stateShipping.statename}<br />{/if}
                        {$sUserData.additional.countryShipping.countryname}
                    {/block}

                    {* Action buttons *}
                    {block name="frontend_checkout_confirm_left_shipping_address_actions"}
                        <div class="panel--actions">
                            <a href="{url controller=account action=shipping sTarget=checkout}" class="btn is--small">
                                {s name="ConfirmLinkChangeShipping" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>

                            <a href="{url controller=account action=selectShipping sTarget=checkout}" class="btn is--small">
                                {s name="ConfirmLinkSelectShipping" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                        </div>
                    {/block}
                </div>
            </div>
        {/block}

        {* Payment method *}
        {block name='frontend_checkout_confirm_payment_method_panel'}
            <div class="panel has--border block information--panel payment--panel">

                {block name='frontend_checkout_confirm_left_payment_method_headline'}
                    <div class="panel--title is--underline payment--title">
                        {s name="ConfirmHeaderPaymentShipping" namespace="frontend/checkout/confirm_left"}{/s}
                    </div>
                {/block}

                <div class="panel--body is--wide payment--content">
                    {block name='frontend_checkout_confirm_left_payment_method'}
                        <p class="payment--method-info">
                            <span class="payment--title is--bold">{s name="ConfirmInfoPaymentMethod" namespace="frontend/checkout/confirm_left"}{/s}</span>
                            <span class="payment--description">{$sUserData.additional.payment.description}</span>
                        </p>

                        {if !$sUserData.additional.payment.esdactive && {config name="showEsd"}}
                            <p class="payment--confirm-esd">{s name="ConfirmInfoInstantDownload" namespace="frontend/checkout/confirm_left"}{/s}</p>
                        {/if}
                    {/block}

                    {block name='frontend_checkout_confirm_left_shipping_method'}
                        <p class="shipping--method-info">
                            <span class="shipping--title is--bold">{s name="ConfirmHeadDispatch"}{/s}</span>
                            <span class="shipping--description" title="{$sDispatch.name}">{$sDispatch.name|truncate:25:"...":true}</span>
                        </p>
                    {/block}
                    {block name='frontend_checkout_confirm_left_payment_method_actions'}
                        {* Action buttons *}
                        <div class="panel--actions payment--actions">
                            <a href="{url controller=checkout action=shippingPayment sTarget=checkout}" class="btn is--small btn--change-payment">
                                {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                        </div>
                    {/block}
                </div>
            </div>
        {/block}
    </div>

    {* Additional feature which can be enabled / disabled in the base configuration *}
    {if {config name=commentvoucherarticle}||{config name=bonussystem} && {config name=bonus_system_active} && {config name=displaySlider}}
        {block name="frontend_checkout_confirm_additional_features"}
            <div class="panel has--border additional--features">
                {block name="frontend_checkout_confirm_additional_features_headline"}
                    <div class="panel--title is--underline">
                        {s name="ConfirmHeadlineAdditionalOptions"}{/s}
                    </div>
                {/block}

                {block name="frontend_checkout_confirm_additional_features_content"}
                    <div class="panel--body is--wide block-group">

                        {* Additional feature - Add voucher *}
                        {block name="frontend_checkout_confirm_additional_features_add_voucher"}
                            <div class="feature--group block">
                                <div class="feature--voucher">
                                    <form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}" class="table--add-voucher add-voucher--form">
                                        {block name='frontend_checkout_table_footer_left_add_voucher_agb'}
                                            {if !{config name='IgnoreAGB'}}
                                                <input type="hidden" class="agb-checkbox" name="sAGB"
                                                       value="{if $sAGBChecked}1{else}0{/if}"/>
                                            {/if}
                                        {/block}

                                        {block name='frontend_checkout_confirm_add_voucher_field'}
                                            <input type="text" class="add-voucher--field block" name="sVoucher" placeholder="{"{s name='CheckoutFooterAddVoucherLabelInline' namespace='frontend/checkout/cart_footer'}{/s}"|escape}" />
                                        {/block}

                                        {block name='frontend_checkout_confirm_add_voucher_button'}
                                            <button type="submit" class="add-voucher--button btn is--primary is--small block">
                                                <i class="icon--arrow-right"></i>
                                            </button>
                                        {/block}
                                    </form>
                                </div>


                                {* Additional feature - Add product using the sku *}
                                {block name="frontend_checkout_confirm_additional_features_add_product"}
                                    <div class="feature--add-product">
                                        <form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}" class="table--add-product add-product--form block-group">

                                            {block name='frontend_checkout_confirm_add_product_field'}
                                                <input name="sAdd" class="add-product--field block" type="text" placeholder="{s name='CheckoutFooterAddProductPlaceholder' namespace='frontend/checkout/cart_footer_left'}{/s}" />
                                            {/block}

                                            {block name='frontend_checkout_confirm_add_product_button'}
                                                <button type="submit" class="add-product--button btn is--primary is--small block">
                                                    <i class="icon--arrow-right"></i>
                                                </button>
                                            {/block}
                                        </form>
                                    </div>
                                {/block}
                            </div>
                        {/block}

                        {* Additional customer comment for the order *}
                        {block name='frontend_checkout_confirm_comment'}
                            <div class="feature--user-comment block">
                                <textarea class="user-comment--field" rows="5" cols="20" placeholder="{s name="ConfirmPlaceholderComment" namespace="frontend/checkout/confirm"}{/s}" data-pseudo-text="true" data-selector=".user-comment--hidden">{$sComment|escape}</textarea>
                            </div>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    {/if}

	{* Premiums articles *}
	{block name='frontend_checkout_confirm_premiums'}
		{if $sPremiums && {config name=premiumarticles}}
			{include file='frontend/checkout/premiums.tpl'}
		{/if}
	{/block}

    {block name='frontend_checkout_confirm_product_table'}
        <div class="product--table">
            <div class="panel has--border">
                <div class="panel--body is--rounded">

                    {* Product table header *}
                    {block name='frontend_checkout_confirm_confirm_head'}
                        {include file="frontend/checkout/confirm_header.tpl"}
                    {/block}

                    {block name='frontend_checkout_confirm_item_before'}{/block}

                    {* Basket items *}
                    {block name='frontend_checkout_confirm_item_outer'}
                        {foreach $sBasket.content as $sBasketItem}
                            {block name='frontend_checkout_confirm_item'}
                                {include file='frontend/checkout/confirm_item.tpl' isLast=$sBasketItem@last}
                            {/block}
                        {/foreach}
                    {/block}

                    {block name='frontend_checkout_confirm_item_after'}{/block}

                    {* Table footer *}
                    {block name='frontend_checkout_confirm_confirm_footer'}
                        {include file="frontend/checkout/confirm_footer.tpl"}
                    {/block}
                </div>
            </div>

            {* Table actions *}
            {block name='frontend_checkout_confirm_confirm_table_actions'}
                <div class="table--actions actions--bottom">
                    <div class="main--actions">
                        {if !$sLaststock.hideBasket}

                            {block name='frontend_checkout_confirm_submit'}
                                {* Submit order button *}
                                {if $sPayment.embediframe || $sPayment.action}
                                    <button type="submit" class="btn is--primary is--large right is--icon-right" form="confirm--form" data-form-polyfill="true" data-preloader-button="true">
                                        {s name='ConfirmDoPayment'}{/s}<i class="icon--arrow-right"></i>
                                    </button>
                                {else}
                                    <button type="submit" class="btn is--primary is--large right is--icon-right" form="confirm--form" data-form-polyfill="true" data-preloader-button="true">
                                        {s name='ConfirmActionSubmit'}{/s}<i class="icon--arrow-right"></i>
                                    </button>
                                {/if}
                            {/block}
                        {else}
                            {block name='frontend_checkout_confirm_stockinfo'}
                                {include file="frontend/_includes/messages.tpl" type="error" content="{s name='ConfirmErrorStock'}{/s}"}
                            {/block}
                        {/if}
                    </div>
                </div>
            {/block}
        </div>
    {/block}
    </div>
{/block}
