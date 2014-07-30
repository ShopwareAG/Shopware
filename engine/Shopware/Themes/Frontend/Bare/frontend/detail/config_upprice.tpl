<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="configurator--form upprice--form">

	{foreach $sArticle.sConfigurator as $sConfigurator}
		
		{* Group name *}
		{block name='frontend_detail_group_name'}
			<p class="configurator--label">{$sConfigurator.groupname}:</p>
		{/block}
		
		{* Group description *}
		{if $sConfigurator.groupdescription}
			{block name='frontend_detail_group_description'}
				<p class="configurator--description">{$sConfigurator.groupdescription}</p>
			{/block}
		{/if}

		{* Configurator drop down *}
		{block name='frontend_detail_group_selection'}
			<div class="field--select">
				<span class="arrow"></span>
				<select name="group[{$sConfigurator.groupID}]" data-auto-submit-form="true">
					{foreach $sConfigurator.values as $configValue}
						<option{if $configValue.selected} selected="selected"{/if} value="{$configValue.optionID}">
							{$configValue.optionname}{if $configValue.upprice} {if $configValue.upprice > 0}{/if}{/if}
						</option>
					{/foreach}
				</select>
			</div>
		{/block}
	{/foreach}

	{block name='frontend_detail_configurator_noscript_action'}
		<noscript>
			<input name="recalc" type="submit" value="{s name='DetailConfigActionSubmit'}{/s}" />
		</noscript>
	{/block}
</form>