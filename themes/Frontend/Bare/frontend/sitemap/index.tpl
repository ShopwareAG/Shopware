{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name="rontend_index_start" prepend}
    {$sBreadcrumb = [['name'=>"{s name='SitemapTitle'}Sitemap{/s}", 'link'=>{url controller=sitemap}]]}
{/block}

{block name="frontend_index_content"}
    <div class="sitemap--content content block">

	{block name="frontend_sitemap_headline"}
		<div class="sitemap--headline panel--body is--wide has--border is--rounded">

			{block name="frontend_sitemap_headline_title"}
				<h1 class="sitemap--title">{s name='SitemapTitle'}Sitemap{/s}</h1>
			{/block}

			{block name="frontend_sitemap_headline_text"}
				<div class="sitemap--text">
					<p class="sitemap--headline-text">{s name='SitemapSubHeader'}Alle Kategorien auf einen Blick{/s}</p>
				</div>
			{/block}
		</div>
	{/block}


	{block name="frontend_sitemap_content"}
		{foreach $sCategoryTree as $categoryTree}

			{if $categoryTree@index % 4 == 0}
				<div class="block-group">
			{/if}

			{block name="frontend_sitemap_category"}
				<div class="sitemap--category block is--rounded">

					{block name="frontend_sitemap_navigation"}
						<ul class="sitemap--navigation list--unstyled">

							{block name="frontend_sitemap_navigation_headline"}
								<li class="sitemap--navigation-head is--bold is--rounded">

									{if $categoryTree.name == 'SitemapStaticPages'}
										{block name="frontend_sitemap_navigation_staticpages"}
											<a href="{$categoryTree.link}" title="{"{s name='SitemapStaticPages'}Statische Seiten{/s}"|escape}" class="sitemap--navigation-head-link is--active">
												{s name='SitemapStaticPages'}Statische Seiten{/s}
											</a>
										{/block}
									{elseif $categoryTree.name == 'SitemapSupplierPages'}
										{block name="frontend_sitemap_navigation_supplierpages"}
											<a href="{$categoryTree.link}" title="{"{s name='SitemapSupplierPages'}Herstellerseiten{/s}"|escape}" class="sitemap--navigation-head-link is--active">
												{s name='SitemapSupplierPages'}Herstellerseiten{/s}
											</a>
										{/block}
									{elseif $categoryTree.name == 'SitemapLandingPages'}
										{block name="frontend_sitemap_navigation_landingpages"}
											<a href="{$categoryTree.link}" title="{"{s name='SitemapLandingPages'}Landingpages{/s}"|escape}" class="sitemap--navigation-head-link is--active">
												{s name='SitemapLandingPages'}Landingpages{/s}
											</a>
										{/block}
									{else}
										{block name="frontend_sitemap_navigation_defaultpages"}
											<a href="{$categoryTree.link}" title="{$categoryTree.name|escape}" class="sitemap--navigation-head-link is--active">
												{$categoryTree.name}
											</a>
										{/block}
									{/if}

								</li>
							{/block}

							{if $categoryTree.sub}
								{include file="frontend/sitemap/recurse.tpl" sCategoryTree=$categoryTree.sub depth=1}
							{/if}
						</ul>
					{/block}

				</div>
			{/block}

			{if $categoryTree@index % 4 == 3 || $categoryTree@last}
				</div>
			{/if}
		{/foreach}
	{/block}

	</div>
{/block}