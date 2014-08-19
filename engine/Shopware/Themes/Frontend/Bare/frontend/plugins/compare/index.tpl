{* Compare container *}
{block name='frontend_index_navigation_inline' append}
	{include file='frontend/compare/index.tpl'}
{/block}

{* Compare result *}
{block name='frontend_index_body_inline' append}
<div id="compare_bigbox"></div>
{/block}


{* Compare button *}
{block name='frontend_listing_box_article_actions_buy_now' prepend}
	<a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}"
	   rel="nofollow"
	   title="{"{s name='ListingBoxLinkCompare'}vergleichen{/s}"|escape}"
	   class="product--action action--compare btn btn--secondary">
		{se name='ListingBoxLinkCompare'}{/se}
		<i class="icon--arrow-right is--right is--small"></i>
	</a>
{/block}

{* Compare javascript *}
{block name='frontend_index_header_javascript_inline' prepend}
	var compareCount = '{$sComparisons|count}';
	var compareMaxCount = '{config name="MaxComparisons"}';
{literal}
	jQuery(document).ready(function() {
		jQuery.compare.setup();
	});
{/literal}
{/block}

{* Compare button 2 *}
{block name='frontend_detail_actions_notepad' prepend}
	<a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}" rel="nofollow" title="{"{s name='DetailActionLinkCompare'}Artikel vergleichen{/s}"|escape}" class="action--link action--compare">
		<i class="icon--compare"></i> {s name="DetailActionLinkCompare"}{/s}
	</a>
{/block}

{* Compare button note *}
{block name='frontend_note_item_actions_compare'}
	<a href="{url controller='compare' action='add_article' articleID=$sBasketItem.articleID}" class="product--action action--compare btn btn--secondary" title="{"{s name='ListingBoxLinkCompare'}{/s}"|escape}" rel="nofollow">
		{s name='ListingBoxLinkCompare'}{/s}
	</a>
{/block}


