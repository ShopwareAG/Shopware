{extends file="parent:frontend/listing/index.tpl"}

{namespace name="frontend/listing/listing"}

{block name="frontend_listing_index_layout_variables" append}
    {$countCtrlUrl = "{url module="widgets" controller="listing" action="listingCount" sSupplier=$manufacturer->getId() fullPath}"}
{/block}

{block name="frontend_listing_text"}
    <div class="vendor--info panel has--border">

        {* Vendor headline *}
        {block name="frontend_listing_list_filter_supplier_headline"}
            <h3 class="panel--title is--underline">
                {s name='ListingInfoFilterSupplier'}{/s} {$manufacturer->getName()}
            </h3>
        {/block}

        {* Vendor content e.g. description and logo *}
        {block name="frontend_listing_list_filter_supplier_content"}
            <div class="panel--body is--wide">

                {if $manufacturer->getCoverFile()}
                    <img class="vendor--image" src="{$manufacturer->getCoverFile()}" alt="{$manufacturer->getName()|escape}">
                {/if}

                {if $manufacturer->getDescription()}
                    <div class="vendor--text">
                        {$manufacturer->getDescription()}
                    </div>
                {/if}
            </div>
        {/block}
    </div>
{/block}