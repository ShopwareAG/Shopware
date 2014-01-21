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
 * Analytics Category Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/partner"}
Ext.define('Shopware.apps.Analytics.view.chart.PartnerRevenue', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-partner_revenue',

    legend: {
        position: 'right',
        autoScroll: true
    },
    mask: 'horizontal',

    initComponent: function () {
        var me = this;

        me.series = [
            {
                type: 'pie',
                field: 'revenue',
                showInLegend: true,
                label: {
                    field: 'partner',
                    display: 'rotate',
                    contrast: true,
                    font: '18px Arial'
                },
                tips: {
                    trackMouse: true,
                    width: 160,
                    height: 30,
                    autoScroll: true,
                    renderer: function (storeItem) {
                        var title = '{s name=chart/partner/title}Sales{/s}: ' + Ext.util.Format.number(storeItem.get('revenue'), '0.00');
                        this.setTitle(title);
                    }
                }
            }
        ];

        me.callParent(arguments);
    }
});
//{/block}
