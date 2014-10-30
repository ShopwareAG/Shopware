<nav class="shop--navigation block-group">
    <ul class="navigation--list block-group" role="menubar">

        {* Menu (Off canvas left) trigger *}
        {block name='frontend_index_offcanvas_left_trigger'}
            <li class="navigation--entry entry--menu-left" role="menuitem">
                <a class="entry--link entry--trigger btn is--icon-left" href="#offcanvas--left" data-offcanvas="true" data-offCanvasSelector=".sidebar-main">
                    <i class="icon--menu"></i> {s name="IndexLinkMenu"}Menü{/s}
                </a>
            </li>
        {/block}

        {* Search form *}
        {block name='frontend_index_search'}
            <li class="navigation--entry entry--search" role="menuitem" data-search-dropdown="true" aria-haspopup="true"{if $theme.focusSearch && {controllerName} == 'index'} data-activeOnStart="true"{/if}>
                <a class="btn entry--link entry--trigger" href="#show-hide--search" title="{"{s name="IndexTitleSearchToggle"}Suche anzeigen / schließen{/s}"|escape}">
                    <i class="icon--search is--large"></i>

                    {block name='frontend_index_search_display'}
                        <span class="search--display">{s name="IndexSearchFieldSubmit"}Suchen{/s}</span>
                    {/block}
                </a>

                {* Include of the search form *}
                {block name='frontend_index_search_include'}
                    {include file="frontend/index/search.tpl"}
                {/block}
            </li>
        {/block}

		{* Notepad entry *}
		{block name="frontend_index_checkout_actions_notepad"}
		<li class="navigation--entry" role="menuitem">
			<a href="{url controller='note'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s}"|escape}" class="btn">
				<i class="icon--heart is--large"></i><span class="notes--quantity">{$sNotesQuantity}</span>
			</a>
		</li>
		{/block}

        {* My account entry *}
        {block name="frontend_index_checkout_actions_my_options"}
            <li class="navigation--entry entry--account" role="menuitem">
                {block name="frontend_index_checkout_actions_account"}
                    <a href="{url controller='account'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}"|escape}" class="btn is--icon-left entry--link account--link">
                        <i class="icon--account is--large"></i>
						<span class="account--display">
							{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
						</span>
                    </a>
                {/block}
            </li>
        {/block}

        {* Cart entry *}
        {block name='frontend_index_checkout_actions'}
            <li class="navigation--entry entry--cart" role="menuitem">

				{* Include of the cart *}
				{block name='frontend_index_checkout_actions_include'}
					{action module=widgets controller=checkout action=info}
				{/block}
            </li>
        {/block}
    </ul>
</nav>