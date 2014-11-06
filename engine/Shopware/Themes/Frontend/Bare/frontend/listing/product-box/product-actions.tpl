{namespace name="frontend/listing/box_article"}

<div class="product--actions">

    {* Compare button *}
    {block name='frontend_listing_box_article_actions_compare'}
        <a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}"
           title="{s name='ListingBoxLinkCompare'}{/s}"
           class="product--action action--compare"
           data-product-compare-add="true"
           rel="nofollow">
            <i class="icon--compare"></i> {s name='ListingBoxLinkCompare'}{/s}
        </a>
    {/block}

    {* Note button *}
    {block name='frontend_listing_box_article_actions_save'}
        <a href="{url controller='note' action='add' ordernumber=$sArticle.ordernumber}"
           title="{"{s name='DetailLinkNotepad' namespace='frontend/detail/actions'}{/s}"|escape}"
           class="product--action action--note"
           rel="nofollow">
            <i class="icon--heart"></i> {s name="DetailLinkNotepadShort" namespace="frontend/detail/actions"}{/s}
        </a>
    {/block}

    {* @deprecated: block no longer in use *}
    {block name='frontend_listing_box_article_actions_more'}{/block}

    {* @deprecated: misleading name *}
    {block name="frontend_listing_box_article_actions_inline"}{/block}
</div>