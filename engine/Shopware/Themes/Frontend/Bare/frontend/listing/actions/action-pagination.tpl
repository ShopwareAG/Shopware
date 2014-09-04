{* Paging which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{if $sNumberPages && $sNumberPages > 1}
    <div class="listing--paging panel--paging">

        {* Pagination label *}
        {block name='frontend_listing_actions_paging_label'}
            <label class="paging--label action--label">{s name='ListingPaging'}{/s}</label>
        {/block}

        {* Pagination - Previous page *}
        {block name='frontend_listing_actions_paging_previous'}
            {if $sPages.previous}
                <a href="{$sPages.previous|rewrite:$sCategoryInfo.name}" title="{"{s name='ListingLinkPrevious'}{/s}"|escape}" class="pagination--link paging--prev"><i class="icon--arrow-left"></i></a>
            {/if}
        {/block}

        {* Pagination numbers *}
        {block name='frontend_listing_actions_paging_numbers'}
            {foreach $sPages.numbers as $page}
                {if $page.value<$sPage+4 AND $page.value>$sPage-4}
                    {if $page.markup AND (!$sOffers OR $sPage)}
                        <a title="{$sCategoryInfo.name|escape}" class="pagination--link is--active">{$page.value}</a>
                    {else}
                        <a href="{$page.link|rewrite:$sCategoryInfo.name}" class="pagination--link">{$page.value}</a>
                    {/if}
                {elseif $page.value==$sPage+4 OR $page.value==$sPage-4}
                    <span class="pagination--link pagination--more">...</span>
                {/if}
            {/foreach}
        {/block}

        {* Pagination - Next page *}
        {block name='frontend_listing_actions_paging_next'}
            {if $sPages.next}
                <a href="{$sPages.next|rewrite:$sCategoryInfo.name}" title="{"{s name='ListingLinkNext'}{/s}"|escape}" class="pagination--link paging--next"><i class="icon--arrow-right"></i></a>
            {/if}
        {/block}

        {* Page counter *}
        {block name='frontend_listing_actions_count'}
            <div class="pagination--display">
                {s name="ListingTextSite"}Seite{/s} <strong>{$sPage}</strong> {s name="ListingTextFrom"}von{/s} <strong>{$sNumberPages}</strong>
            </div>
        {/block}
    </div>
{/if}