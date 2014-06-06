{namespace name="frontend/account/order_item"}

<div id="order{$offerPosition.ordernumber}" class="order--details panel--table">

{block name="frontend_account_order_item_detail_table"}

	{block name="frontend_account_order_item_detail_id"}
		<input type="hidden" name="sAddAccessories" value="{$ordernumber|escape}" />
	{/block}

	{block name="frontend_account_order_item_detail_table_head"}
		<div class="orders--table-header panel--tr is--secondary">

			{block name="frontend_account_order_item_detail_table_head_name"}
				<div class="panel--th column--name">{s name="OrderItemColumnName"}{/s}</div>
			{/block}

			{block name="frontend_account_order_item_detail_table_head_quantity"}
				<div class="panel--th column--quantity is--align-center">{s name="OrderItemColumnQuantity"}{/s}</div>
			{/block}

			{block name="frontend_account_order_item_detail_table_head_price"}
				<div class="panel--th column--price is--align-right">{s name="OrderItemColumnPrice"}{/s}</div>
			{/block}

			{block name="frontend_account_order_item_detail_table_head_total"}
				<div class="panel--th column--total is--align-right">{s name="OrderItemColumnTotal"}{/s}</div>
			{/block}
		</div>
	{/block}

	{block name="frontend_account_order_item_detail_table_rows"}
		{foreach $offerPosition.details as $article}

			{block name="frontend_account_order_item_detail_table_row"}
				<div class="panel--tr">

					{block name="frontend_account_order_item_info"}
						<div class="panel--td order--info column--name">

							{* Name *}
							{block name="frontend_account_order_item_name"}
								<p class="order--name is--strong">
									{if $article.modus == 10}
										{s name="OrderItemInfoBundle"}{/s}
									{else}
										{$article.name}
									{/if}
								</p>
							{/block}

							{* Unit price *}
							{block name='frontend_account_order_item_unitprice'}
								{if $article.purchaseunit}
									<div class="order--price-unit">
										<p>{s name="OrderItemInfoContent"}{/s}: {$article.purchaseunit} {$article.sUnit.description}</p>
										{if $article.purchaseunit != $article.referenceunit}
											<p>
												{if $article.referenceunit}
													<span class="order--base-price">{s name="OrderItemInfoBaseprice"}{/s}:</span>
													{$article.referenceunit} {$article.sUnit.description} = {$article.referenceprice|currency}
													{s name="Star" namespace="frontend/listing/box_article"}{/s}
												{/if}
											</p>
										{/if}
									</div>
								{/if}
							{/block}

							{* Current price *}
							{block name='frontend_account_order_item_currentprice'}
								{if $article.currentPrice}
									<div class="order--current-price">
										<span>{s name="OrderItemInfoCurrentPrice"}{/s}:</span>
										<span class="is--soft">
											{$article.currentPrice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
										</span>
										{if $article.currentPseudoprice}
											<span class="order--pseudo-price is--italic is--soft is--line-through">
												{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s}
												{$article.currentPseudoprice|currency}
												{s name="Star" namespace="frontend/listing/box_article"}{/s}
											</span>
										{/if}
									</div>
								{/if}
							{/block}

							{* If ESD-Article *}
							{block name='frontend_account_order_item_downloadlink'}
								{if $article.esdarticle && $offerPosition.cleared|in_array:$sDownloadAvailablePaymentStatus}
									<div class="order--download is--strong">
										<a href="{$article.esdLink}" class="btn btn--grey is--small">
											{s name="OrderItemInfoInstantDownload"}{/s}
										</a>
									</div>
								{/if}
							{/block}
						</div>
					{/block}

					{* Order item quantity *}
					{block name='frontend_account_order_item_quantity'}
						<div class="panel--td order--quantity column--quantity">

							{block name='frontend_account_order_item_quantity_label'}
								<div class="column--label">{s name="OrderItemColumnQuantity"}{/s}</div>
							{/block}

							{block name='frontend_account_order_item_quantity_value'}
								<div class="column--value">{$article.quantity}</div>
							{/block}
						</div>
					{/block}

					{* Order item price *}
					{block name='frontend_account_order_item_price'}
						<div class="panel--td order--price column--price">

							{block name='frontend_account_order_item_price_label'}
								<div class="column--label">{s name="OrderItemColumnPrice"}{/s}</div>
							{/block}

							{block name='frontend_account_order_item_price_value'}
								<div class="column--value">
									{if $article.price}
										{$article.price} {$offerPosition.currency_html} *
									{else}
										{s name="OrderItemInfoFree"}{/s}
									{/if}
								</div>
							{/block}
						</div>
					{/block}

					{* Order item total amount *}
					{block name='frontend_account_order_item_amount'}
						<div class="panel--td order--amount column--total">

							{block name='frontend_account_order_item_amount_label'}
								<div class="column--label">{s name="OrderItemColumnTotal"}{/s}</div>
							{/block}

							{block name='frontend_account_order_item_amount_value'}
								<div class="column--value">
									{if $article.amount}
										{$article.amount} {$offerPosition.currency_html} *
									{else}
										{s name="OrderItemInfoFree"}{/s}
									{/if}
								</div>
							{/block}
						</div>
					{/block}
				</div>
			{/block}
		{/foreach}
	{/block}

	<div class="panel--tr is--odd">

		{block name="frontend_account_order_item_detail_info_labels"}
			<div class="panel--td column--info-labels">
				{* Order date label *}
				{block name="frontend_account_order_item_label_date"}
					<p class="is--strong">{s name="OrderItemColumnDate"}{/s}</p>
				{/block}

				{* Order number label *}
				{block name="frontend_account_order_item_label_ordernumber"}
					<p class="is--strong">{s name="OrderItemColumnId"}{/s}</p>
				{/block}

				{* Shipping method label  *}
				{block name="frontend_account_order_item_label_dispatch"}
					{if $offerPosition.dispatch}
						<p class="is--strong">{s name="OrderItemColumnDispatch"}{/s}</p>
					{/if}
				{/block}

				{* Package tracking code label *}
				{block name="frontend_account_order_item_label_trackingcode"}
					{if $offerPosition.trackingcode}
						<p class="is--strong">{s name="OrderItemColumnTracking"}{/s}</p>
					{/if}
				{/block}
			</div>
		{/block}

		{block name="frontend_account_order_item_detail_info_data"}
			<div class="panel--td column--info-data">
				{* Order date *}
				{block name='frontend_account_order_item_date'}
					<p>{$offerPosition.datum|date}</p>
				{/block}

				{* Order number *}
				{block name='frontend_account_order_item_ordernumber'}
					<p>{$offerPosition.ordernumber}</p>
				{/block}

				{* Shipping method *}
				{block name='frontend_account_order_item_dispatch'}
					{if $offerPosition.dispatch}
						<p>{$offerPosition.dispatch.name}</p>
					{/if}
				{/block}

				{* Package tracking code *}
				{block name='frontend_account_order_item_trackingcode'}
					{if $offerPosition.trackingcode}
						<p>
							{if $offerPosition.dispatch.status_link}
								{eval var=$offerPosition.dispatch.status_link}
							{else}
								{$offerPosition.trackingcode}
							{/if}
						</p>
					{/if}
				{/block}
			</div>
		{/block}

		{block name="frontend_account_order_item_detail_summary_labels"}
			<div class="panel--td column--summary-labels">

				{* Shopping costs label *}
				<p class="is--strong">{s name="OrderItemShippingcosts"}{/s}</p>

				{if $offerPosition.taxfree}
					<p class="is--strong">{s name="OrderItemNetTotal"}{/s}</p>
				{else}
					<p class="is--strong">{s name="OrderItemTotal"}{/s}</p>
				{/if}
			</div>
		{/block}

		{block name="frontend_account_order_item_detail_summary_data"}
			<div class="panel--td column--summary-data">

				{* Shopping costs *}
				{block name="frontend_account_order_item_shippingamount"}
					<p class="is--strong">{$offerPosition.invoice_shipping} {$offerPosition.currency_html}</p>
				{/block}

				{block name="frontend_acccount_order_item_amount"}
					{if $offerPosition.taxfree}
						<p class="is--bold">{$offerPosition.invoice_amount_net} {$offerPosition.currency_html}</p>
					{else}
						<p class="is--bold">{$offerPosition.invoice_amount} {$offerPosition.currency_html}</p>
					{/if}
				{/block}
			</div>
		{/block}
	</div>

	{* User comment *}
	{block name="frontend_account_order_item_user_comment"}
		{if $offerPosition.customercomment}
			<div class="order--user-comments panel">
				<div class="panel--title">{s name="OrderItemCustomerComment"}Ihr Kommentar{/s}</div>
				<div class="panel--body is--wide">
					<blockquote>{$offerPosition.customercomment}</blockquote>
				</div>
			</div>
		{/if}
	{/block}

	{* Shop comment *}
	{block name="frontend_account_order_item_shop_comment"}
		{if $offerPosition.comment}
			<div class="order--shop-comments panel">
				<div class="panel--title">{s name="OrderItemComment"}Unser Kommentar{/s}</div>
				<div class="panel--body is--wide">
					<blockquote>{$offerPosition.comment}</blockquote>
				</div>
			</div>
		{/if}
	{/block}

	{* Repeat order *}
	{block name="frontend_account_order_item_repeat_order"}
		<form method="post" action="{url controller='checkout' action='add_accessories'}">
			{foreach $offerPosition.details as $article}{if $article.modus == 0}
				<input name="sAddAccessories[]" type="hidden" value="{$article.articleordernumber|escape}" />
				<input name="sAddAccessoriesQuantity[]" type="hidden" value="{$article.quantity|escape}" />
			{/if}{/foreach}

			{* Repeat order button *}
			{block name="frontend_account_order_item_repeat_button"}
				{if $offerPosition.activeBuyButton}
					<div class="order--repeat">
						<input type="submit" class="btn btn--primary is--small" value="{s name='OrderLinkRepeat'}{/s}" />
					</div>
				{/if}
			{/block}
		</form>
	{/block}

{/block}
</div>