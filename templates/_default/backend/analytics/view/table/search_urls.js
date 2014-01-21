/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Analytics SearchUrls Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/search_urls"}
Ext.define('Shopware.apps.Analytics.view.table.SearchUrls', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-search-urls',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
            }
        };

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [
            {
                dataIndex: 'count',
                text: '{s name=table/referrer_visitors/count}Number of{/s}'
            },
            {
                dataIndex: 'referrer',
                text: '{s name=table/referrer_visitors/search_link}Search link{/s}'
            },
            {
                xtype: 'actioncolumn',
                text: '{s name=table/referrer_visitors/options}Options{/s}',
                items: [
                    {
                        action: 'viewSearchUrl',
                        iconCls: 'sprite-application',
                        tooltip: '{s name=table/referrer_visitors/open_link_tip}Open search link{/s}',
                        handler: function (grid, rowIndex, colIndex) {
                            var store = grid.store,
                                record = store.getAt(rowIndex);

                            window.open(record.get('referrer'), '_blank');
                        }
                    }
                ]
            }
        ];
    }
});
//{/block}