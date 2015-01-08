{block name="frontend_note_item"}
	<div class="note--item panel--tr">

        {if $sBasketItem.sConfigurator}
            {$detailLink={url controller="detail" sArticle=$sBasketItem.articleID number=$sBasketItem.ordernumber}}
        {else}
            {$detailLink=$sBasketItem.linkDetails}
        {/if}

		{* Article information *}
		{block name="frontend_note_item_info"}
			<div class="note--info panel--td">

				{* Article picture *}
				{block name="frontend_note_item_image"}
					<div class="note--image-container">
						{if $sBasketItem.image.src.0}
							<a href="{$detailLink}" title="{$sBasketItem.articlename|escape}" class="note--image-link">
								<img src="{$sBasketItem.image.src.2}" alt="{$sBasketItem.articlename|escape}" class="note--image" />
							</a>
							{* Zoom picture *}
							{block name="frontend_note_item_image_zoom"}
								<a href="{$sBasketItem.image.src.5}" rel="lightbox" class="note--zoom" data-lightbox="true">
									{s name="NoteLinkZoom"}{/s}
								</a>
							{/block}
						{else}
							<a href="{$detailLink}" title="{$sBasketItem.articlename|escape}" class="note--image-link">
								<img src="{link file='frontend/_public/src/img/no-picture.jpg'}" alt="{$sBasketItem.articlename|escape}" class="note--image" />
							</a>
						{/if}
					</div>
				{/block}

				{* Article details *}
				{block name="frontend_note_item_details"}
					<div class="note--details">

						{* Article name *}
						{block name="frontend_note_item_details_name"}
							<a class="note--title" href="{$detailLink}" title="{$sBasketItem.articlename|escape}">
								{$sBasketItem.articlename|truncate:40}
							</a>
						{/block}

						{* Reviews *}
						{block name="frontend_note_item_rating"}
							{if !{config name=VoteDisable}}
                                {include file="frontend/_includes/rating.tpl" points=$sBasketItem.sVoteAverage.average type="aggregated"}
                            {/if}
						{/block}

						{* Supplier name *}
						{block name="frontend_note_item_details_supplier"}
							<div class="note--supplier">
								{s name="NoteInfoSupplier"}{/s} {$sBasketItem.supplierName}
							</div>
						{/block}

						{* Order number *}
						{block name="frontend_note_item_details_ordernumber"}
							<div class="note--ordernumber">
								{s name="NoteInfoId"}{/s} {$sBasketItem.ordernumber}
							</div>
						{/block}

						{* Date added *}
						{block name="frontend_note_item_date"}
							{if $sBasketItem.datum_add}
								<div class="note--date">
									{s name="NoteInfoDate"}Hinzugefügt am:{/s} {$sBasketItem.datum_add|date:DATE_MEDIUM}
								</div>
							{/if}
						{/block}

						{* Delivery information *}
						{block name="frontend_note_item_delivery"}
							{if {config name=BASKETSHIPPINGINFO}}
								<div class="note--delivery{if {config name=VoteDisable}} vote_disabled{/if}"  >
									{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sBasketItem}
								</div>
							{/if}
						{/block}

						{block name="frontend_note_index_items"}{/block}
					</div>
				{/block}
			</div>
		{/block}

		{block name="frontend_note_item_sale"}
			<div class="note--sale panel--td">

				{* Price *}
				{block name="frontend_note_item_price"}
					{if $sBasketItem.itemInfo}
						{$sBasketItem.itemInfo}
					{else}
						<div class="note--price">{if $sBasketItem.priceStartingFrom}{s namespace='frontend/listing/box_article' name='ListingBoxArticleStartsAt'}{/s} {/if}{$sBasketItem.price|currency}*</div>
					{/if}
				{/block}

				{* Price unit *}
				{block name="frontend_note_item_unitprice"}
					{if $sBasketItem.purchaseunit}
						<span class="note--price-unit">
                            <span class="is--strong">{s name="NoteUnitPriceContent"}{/s}:</span> {$sBasketItem.purchaseunit} {$sBasketItem.sUnit.description}
                            {if $sBasketItem.purchaseunit != $sBasketItem}
                                {if $sBasketItem.referenceunit}
                                    ({$sBasketItem.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sBasketItem.referenceunit} {$sBasketItem.sUnit.description})
                                {/if}
                            {/if}
						</span>
					{/if}
				{/block}

                {* Compare product *}
                {block name='frontend_note_item_actions_compare'}
                    <div class="note--compare">
                        <a href="{url controller='compare' action='add_article' articleID=$sBasketItem.articleID}"
                           data-product-compare-add="true"
                           class="compare--link"
                           title="{"{s name='ListingBoxLinkCompare'}{/s}"|escape}"
                           rel="nofollow">
                            <i class="icon--compare"></i> {s name='ListingBoxLinkCompare'}{/s}
                        </a>
                    </div>
                {/block}
			</div>
		{/block}

		{* Remove article *}
		{block name="frontend_note_item_delete"}
			<a href="{url controller='note' action='delete' sDelete=$sBasketItem.id}" title="{"{s name='NoteLinkDelete'}{/s}"|escape}" class="note--delete">
				<i class="icon--cross"></i>
			</a>
		{/block}
	</div>
{/block}