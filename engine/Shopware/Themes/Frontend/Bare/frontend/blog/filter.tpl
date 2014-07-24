{if $sFilterDate && $sFilterDate|@count > 1}

	{* Filter by date *}
	{block name='frontend_blog_filter_date'}
		<div class="blog--filter blog--filter-date panel has--border is--rounded filter--group block">

			{* Filter headline *}
			{block name="frontend_blog_filter_date_headline"}
				<h1 class="blog--filter-headline panel--title is--underline collapse--header blog-filter--trigger">{s name="BlogHeaderFilterDate"}{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
			{/block}

			{* Filter content *}
			{block name="frontend_blog_filter_date_content"}
				<div class="blog--filter-content panel--body is--wide collapse--content">
					<ul class="filter--list list--unstyled">
						{foreach $sFilterDate as $date}
							{if !$date.removeProperty}
								{if $smarty.get.sFilterDate==$date.dateFormatDate}
									{$filterDateActive=true}
									<li class="filter--entry is--active"><a href="{$date.link}" class="filter--entry-link is--active is--bold" title="{$date.dateFormatDate}">{$date.dateFormatDate|date:"DATE_SHORT"} ({$date.dateCount})</a></li>
								{else}
									<li class="filter--entry{if $date@last} is--last{/if}"><a href="{$date.link}" class="filter--entry-link" title="{$date.dateFormatDate}">{$date.dateFormatDate|date:"DATE_SHORT"} ({$date.dateCount})</a></li>
								{/if}
							{elseif $filterDateActive}
								<li class="filter--entry close"><a href="{$date.link}" class="filter--entry-link" title="{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}">{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}</a></li>
							{/if}
						{/foreach}
					</ul>
				</div>
			{/block}
		</div>
	{/block}
{/if}

{if $sFilterAuthor && $sFilterAuthor|@count > 1}

	{* Filter by author *}
	{block name='frontend_blog_filter_author'}
		<div class="blog--filter blog--filter-author panel has--border is--rounded filter--group block">

			{* Filter headline *}
			{block name="frontend_blog_filter_author_headline"}
				<h1 class="blog--filter-headline panel--title is--underline collapse--header blog-filter--trigger">{s name="BlogHeaderFilterAuthor"}{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
			{/block}

			{* Filter content *}
			{block name="frontend_blog_filter_author_content"}
				<div class="blog--filter-content panel--body is--wide collapse--content {if $filterAuthorActive}is--active{/if}">
					<ul class="filter--list list--unstyled">
						{foreach $sFilterAuthor as $author}
							{if !$author.removeProperty}
								{if $smarty.get.sFilterAuthor==$author.name|urlencode}
									{$filterAuthorActive=true}
									<li class="is--active"><a href="{$author.link}" title="{$author.name}" class="filter--entry-link is--active is--bold">{$author.name} ({$author.authorCount})</a></li>
								{else}
									<li class="filter--entry{if $author@last} is--last{/if}"><a href="{$author.link}" class="filter--entry-link" title="{$author.name}">{$author.name} ({$author.authorCount})</a></li>
								{/if}
							{elseif $filterAuthorActive}
								<li class="filter--entry close"><a href="{$author.link}" class="filter--entry-link" title="{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}">{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}</a></li>
							{/if}
						{/foreach}
					</ul>
				</div>
			{/block}
		</div>
	{/block}
{/if}

{if $sFilterTags && $sFilterTags|@count > 1}

    {* Filter by tags *}
    {block name='frontend_blog_filter_tags'}
		<div class="blog--filter blog--filter-tags panel has--border is--rounded filter--group block">

			{* Filter headline *}
			{block name="frontend_blog_filter_tags_headline"}
				<h1 class="blog--filter-headline panel--title is--underline collapse--header blog-filter--trigger">{s name="BlogHeaderFilterTags"}{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
			{/block}

			{* Filter content *}
			{block name="frontend_blog_filter_tags_content"}
				<div class="blog--filter-content panel--body is--wide collapse--content">
					<ul class="filter--list list--unstyled">
						{foreach $sFilterTags as $tag}
							{if !$tag.removeProperty}
								{if $smarty.get.sFilterTags==$tag.name|urlencode}
									{$filterTagsActive=true}
									<li class="filter--entry is--active"><a href="{$tag.link}" title="{$tag.name}" class="filter--entry-link is--active is--bold">{$tag.name} ({$tag.tagsCount})</a></li>
								{else}
									<li class="filter--entry{if $tag@last} is--last{/if}"><a href="{$tag.link}" class="filter--entry-link" title="{$tag.name}">{$tag.name} ({$tag.tagsCount})</a></li>
								{/if}
							{elseif $filterTagsActive}
								<li class="filter--entry close"><a href="{$tag.link}" class="filter--entry-link" title="{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}">{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}</a></li>
							{/if}
						{/foreach}
					</ul>
				</div>
			{/block}
		</div>
    {/block}
{/if}