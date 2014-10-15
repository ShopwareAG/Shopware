{* Paging which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{if $pages > 1}
    <div class="listing--paging panel--paging">

        {* Pagination label *}
        {block name='frontend_listing_actions_paging_label'}{/block}

		{* Pagination - Frist page *}
		{block name="frontend_listing_actions_paging_first"}
			{if $sPage > 1}
				<a href="#?p=1" title="{"{s name='ListingLinkFirst'}{/s}"|escape}" class="pagination--link paging--prev" data-action-link="true">
					<i class="icon--arrow-left"></i>
					<i class="icon--arrow-left"></i>
				</a>
			{/if}
		{/block}

        {* Pagination - Previous page *}
        {block name='frontend_listing_actions_paging_previous'}
            {if $sPage > 1}
                <a href="#?p={$sPage - 1}" title="{"{s name='ListingLinkPrevious'}{/s}"|escape}" class="pagination--link paging--prev" data-action-link="true">
					<i class="icon--arrow-left"></i>
				</a>
            {/if}
        {/block}

        {* Pagination - current page *}
        {block name='frontend_listing_actions_paging_numbers'}
			<a title="{$sCategoryInfo.name|escape}" class="pagination--link is--active">{$sPage}</a>
        {/block}

        {* Pagination - Next page *}
        {block name='frontend_listing_actions_paging_next'}
			{if $sPage < $pages}
				<a href="#?p={$sPage + 1}" title="{"{s name='ListingLinkNext'}{/s}"|escape}" class="pagination--link paging--next" data-action-link="true">
					<i class="icon--arrow-right"></i>
				</a>
			{/if}
        {/block}

		{* Pagination - Last page *}
		{block name="frontend_listing_actions_paging_last"}
			{if $sPage < $pages}
				<a href="#?p={$pages}" title="{"{s name='ListingLinkLast'}{/s}"|escape}" class="pagination--link paging--next" data-action-link="true">
					<i class="icon--arrow-right"></i>
					<i class="icon--arrow-right"></i>
				</a>
			{/if}
		{/block}

        {* Pagination - Number of pages *}
        {block name='frontend_listing_actions_count'}
            <span class="pagination--display">
                {s name="ListingTextFrom"}von{/s} <strong>{$pages}</strong>
            </span>
        {/block}
    </div>
{/if}
