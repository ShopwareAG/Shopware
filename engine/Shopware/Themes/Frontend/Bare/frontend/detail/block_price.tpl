{block name='frontend_detail_data_block_prices_start'}
	<div class="block-prices--container{if $hidden && !$sArticle.selected} is--hidden{/if} block-price--{$sArticle.ordernumber}">

		{* @deprecated *}
		{block name='frontend_detail_data_block_prices_headline'}{/block}

		{block name="frontend_detail_data_block_prices_table"}
			<table class="block-prices--table">
                {block name="frontend_detail_data_block_prices_table_inner"}
                    {block name="frontend_detail_data_block_prices_table_head"}
                        <thead class="block-prices--head">
                            {block name="frontend_detail_data_block_prices_table_head_inner"}
                                <tr class="block-prices--row">
                                    {block name="frontend_detail_data_block_prices_table_head_row"}
                                        {block name="frontend_detail_data_block_prices_table_head_cell_quantity"}
                                            <th class="block-prices--cell">
                                                {s namespace="frontend/detail/data" name="DetailDataColumnQuantity"}{/s}
                                            </th>
                                        {/block}
                                        {block name="frontend_detail_data_block_prices_table_head_cell_price"}
                                            <th class="block-prices--cell">
                                                {s namespace="frontend/detail/data" name="DetailDataColumnPrice"}{/s}
                                            </th>
                                        {/block}
                                    {/block}
                                </tr>
                            {/block}
                        </thead>
                    {/block}

                    {block name="frontend_detail_data_block_prices_table_body"}
                        <tbody class="block-prices--body">
                            {block name="frontend_detail_data_block_prices_table_body_inner"}
                                {foreach $sArticle.sBlockPrices as $blockPrice}
                                    {block name='frontend_detail_data_block_prices'}
                                        <tr class="block-prices--row {cycle values="is--primary,is--secondary"}">
                                            {block name="frontend_detail_data_block_prices_table_body_row"}
                                                {block name="frontend_detail_data_block_prices_table_body_cell_quantity"}
                                                    <td class="block-prices--cell">
                                                        {if $blockPrice.from == 1}
                                                            {s namespace="frontend/detail/data" name="DetailDataInfoUntil"}{/s} {$blockPrice.to}
                                                        {else}
                                                            {s namespace="frontend/detail/data" name="DetailDataInfoFrom"}{/s} {$blockPrice.from}
                                                        {/if}
                                                    </td>
                                                {/block}
                                                {block name="frontend_detail_data_block_prices_table_body_cell_price"}
                                                    <td class="block-prices--cell">
                                                        {$blockPrice.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                                                    </td>
                                                {/block}
                                            {/block}
                                        </tr>
                                    {/block}
                                {/foreach}
                            {/block}
                        </tbody>
                    {/block}
                {/block}
			</table>
		{/block}
	</div>
{/block}