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
 * Analytics Customers Store
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
Ext.define('Shopware.apps.Analytics.store.navigation.Customers', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-customers',
    remoteSort: true,
    fields: [
        'week',
        'male',
        'female',
        'newCustomersOrders',
        'oldCustomersOrders',
        'amountNewCustomers',
        'amountOldCustomers',
        'maleAmount',
        'femaleAmount'
    ],
    proxy: {
        type: 'ajax',
        url: '{url controller=analytics action=getCustomers}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },

    constructor: function (config) {
        var me = this;
        config.fields = me.fields;

        if (config.shopStore) {
            config.shopStore.each(function (shop) {
                config.fields.push('amountNewCustomers' + shop.data.id);
                config.fields.push('amountOldCustomers' + shop.data.id);
                config.fields.push('maleAmount' + shop.data.id);
                config.fields.push('femaleAmount' + shop.data.id);
            });
        }

        me.callParent(arguments);
    }
});
