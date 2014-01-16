/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Analytics
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/controller/referrer_visitors"}
Ext.define('Shopware.apps.Analytics.controller.ReferrerVisitors', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend:'Enlight.app.Controller',

    /**
     * References to specific elements in the module
     * @array
     */
    refs:[
        { ref:'panel', selector:'analytics-panel' }
    ],

    /**
     * Creates the necessary event listener for this specific controller
     * to control the switch from the referrer listing and the search term table
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'analytics-table-referrer_visitors':{
                viewSearchTerms: me.onViewSearchTerms,
                viewSearchUrl: me.onViewSearchUrl
            }
        });
    },

    /**
     * Switches the view to a tables which shows all search terms of the referrer
     * @param grid
     * @param rowIndex
     * @param colIndex
     */
    onViewSearchTerms: function(grid, rowIndex, colIndex){

    },

    /**
     * Switches the view to a table that shows all referrer urls where visitors are coming from
     * @param grid
     * @param rowIndex
     * @param colIndex
     */
    onViewSearchUrl: function(grid, rowIndex, colIndex){

    }
});
//{/block}