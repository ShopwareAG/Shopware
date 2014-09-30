{block name="frontend_index_checkout_actions_cart"}
    <a class="cart--link" href="{url controller='checkout' action='cart'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}"|escape}">
        <span class="cart--display">{if $sUserLoggedIn}{s name='IndexLinkCheckout' namespace='frontend/index/checkout_actions'}{/s}{else}{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}{/if}</span>
        <span class="cart--quantity">({$sBasketQuantity})</span>
        <span class="cart--amount">{$sBasketAmount|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</span>

        {block name="frontend_index_checkout_actions_inner"}{/block}
    </a>
    <div class="ajax-loader">&nbsp;</div>
{/block}

