{if $sFilterDate && $sFilterDate|@count > 1}

	{* Filter by date *}
	{block name='frontend_blog_filter_date'}
		<div class="blog--filter blog--filter-date block">

			{block name="frontend_blog_filter_date_headline"}
				<h1 class="blog--filter-headline panel--title is--underline collapse--header" data-collapse-panel="true">{s name="BlogHeaderFilterDate"}{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
			{/block}

			{block name="frontend_blog_filter_date_content"}
				<div class="blog--filter-content panel--body is--wide collapse--content">
					<ul class="filter--list list--unstyled">
						{foreach name=filter from=$sFilterDate item=date}
							{if !$date.removeProperty}
								{if $smarty.get.sFilterDate==$date.dateFormatDate}
									{assign var=filterDateActive value=true}
									<li class="filter--entry is--active"><a href="{$date.link}" class="filter--entry-link is--active is--bold" title="{$date.dateFormatDate}">{$date.dateFormatDate|date:"DATE_SHORT"} ({$date.dateCount})</a></li>
								{else}
									<li class="filter--entry{if $smarty.foreach.filter.last} last{/if}"><a href="{$date.link}" class="filter--entry-link" title="{$date.dateFormatDate}">{$date.dateFormatDate|date:"DATE_SHORT"} ({$date.dateCount})</a></li>
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
		<div class="blog--filter blog--filter-author block">

			{block name="frontend_blog_filter_author_headline"}
				<h1 class="blog--filter-headline panel--title is--underline collapse--header" data-collapse-panel="true">{s name="BlogHeaderFilterAuthor"}{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
			{/block}

			{block name="frontend_blog_filter_author_content"}
				<div class="blog--filter-content panel--body is--wide collapse--content {if $filterAuthorActive}is--active{/if}">
					<ul class="filter--list list--unstyled">
						{foreach name=filterAuthor from=$sFilterAuthor item=author}
							{if !$author.removeProperty}
								{if $smarty.get.sFilterAuthor==$author.name|urlencode}
									{assign var=filterAuthorActive value=true}
									<li class="is--active"><a href="{$author.link}" title="{$author.name}" class="filter--entry-link is--active is--bold">{$author.name} ({$author.authorCount})</a></li>
								{else}
									<li class="filter--entry{if $smarty.foreach.filterAuthor.last} is--last{/if}"><a href="{$author.link}" class="filter--entry-link" title="{$author.name}">{$author.name} ({$author.authorCount})</a></li>
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
		<div class="blog--filter blog--filter-tags block">
			{block name="frontend_blog_filter_tags_headline"}
				<h1 class="blog--filter-headline panel--title is--underline collapse--header" data-collapse-panel="true">{s name="BlogHeaderFilterTags"}{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
			{/block}

			{block name="frontend_blog_filter_tags_content"}
				<div class="blog--filter-content panel--body is--wide collapse--content">
					<ul class="filter--list list--unstyled">
						{foreach name=filterTags from=$sFilterTags item=tag}
							{if !$tag.removeProperty}
								{if $smarty.get.sFilterTags==$tag.name|urlencode}
									{assign var=filterTagsActive value=true}
									<li class="filter--entry is--active"><a href="{$tag.link}" title="{$tag.name}" class="filter--entry-link is--active is--bold">{$tag.name} ({$tag.tagsCount})</a></li>
								{else}
									<li class="filter--entry{if $smarty.foreach.filterTags.last} is--last{/if}"><a href="{$tag.link}" class="filter--entry-link" title="{$tag.name}">{$tag.name} ({$tag.tagsCount})</a></li>
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