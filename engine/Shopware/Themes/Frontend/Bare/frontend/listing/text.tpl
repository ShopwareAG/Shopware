{namespace name="frontend/listing/listing"}

{* Categorie headline *}
{block name="frontend_listing_text"}
	{if $sCategoryContent.cmsheadline && $sCategoryContent.cmstext}
		<div class="hero-unit category--teaser panel has--border">

			{* Headline *}
			{block name="frontend_listing_text_headline"}
				{if $sCategoryContent.cmsheadline}
					<h1 class="hero--headline panel--title">{$sCategoryContent.cmsheadline}</h1>
				{/if}
			{/block}

			{* Category text *}
			{block name="frontend_listing_text_content"}
				{if $sCategoryContent.cmstext}
					<div class="hero--text panel--body is--wide"
                     data-collapse-text="true"
                     data-lines="2"
					 data-readMoreText="{s name='ListingCategoryTeaserShowMore'}{/s}"
					 data-readLessText="{s name='ListingCategoryTeaserShowLess'}{/s}">
                    {$sCategoryContent.cmstext}
                </div>
            {/if}
        {/block}
	</div>
	{/if}
{/block}
