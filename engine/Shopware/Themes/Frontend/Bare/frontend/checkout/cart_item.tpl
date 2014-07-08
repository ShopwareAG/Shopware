{* Constants for the different basket item types *}
{$IS_PRODUCT = 0}
{$IS_PREMIUM_PRODUCT = 1}
{$IS_VOUCHER = 2}
{$IS_REBATE = 3}
{$IS_SURCHARGE_DISCOUNT = 4}

{if $sBasketItem.modus == $IS_PRODUCT}

    {* Product *}
    {block name='frontend_checkout_cart_item_product'}
        {include file="frontend/checkout/items/product.tpl"}
    {/block}
{elseif $sBasketItem.modus == $IS_PREMIUM_PRODUCT}

    {* Chosen premium products *}
    {block name='frontend_checkout_cart_item_product'}
        {include file="frontend/checkout/items/premium-product.tpl"}
    {/block}
{elseif $sBasketItem.modus == $IS_VOUCHER}

    {* Voucher *}
    {block name='frontend_checkout_cart_item_product'}
        {include file="frontend/checkout/items/voucher.tpl"}
    {/block}
{elseif $sBasketItem.modus == $IS_REBATE}

    {* Basket rebate *}
    {block name='frontend_checkout_cart_item_product'}
        {include file="frontend/checkout/items/rebate.tpl"}
    {/block}
{elseif $sBasketItem.modus == $IS_SURCHARGE_DISCOUNT}

    {* Surcharge / discount *}
    {block name='frontend_checkout_cart_item_product'}
        {include file="frontend/checkout/items/rebate.tpl"}
    {/block}
{else}

    {* Register your own mode selection *}
    {block name='frontend_checkout_cart_item_additional_type'}{/block}
{/if}