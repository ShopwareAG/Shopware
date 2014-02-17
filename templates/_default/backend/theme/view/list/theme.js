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
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/list/theme"}

Ext.define('Shopware.apps.Theme.view.list.Theme', {
    alias: 'widget.theme-listing',
    region: 'center',
    autoScroll: true,
    extend: 'Ext.panel.Panel',

    initComponent: function () {
        var me = this;

        me.items = [
            me.createDropZone(),
            me.createInfoView()
        ];

        me.callParent(arguments);
    },

    createDropZone: function () {
        var me = this;

        me.dropZone = Ext.create('Shopware.app.FileUpload', {
            requestURL: '{url controller="Theme" action="upload"}',
            enablePreviewImage: false,
            showInput: false,
            dropZoneText: '{s name=drop_zone}Upload single theme using drag+drop (zip){/s}'
        });

        me.dropZone.snippets.messageTitle = '{s name=upload_title}Theme manager{/s}';
        me.dropZone.snippets.messageText = '{s name=upload_message}Theme uploaded successfully{/s}';

        return me.dropZone;
    },

    createInfoView: function () {
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            itemSelector: '.thumbnail',
            tpl: me.createTemplate(),
            store: me.store,
            cls: 'theme-listing'
        });

        return me.infoView;
    },

    createTemplate: function () {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',

            '<tpl if="enabled">',
                '<div class="thumbnail enabled">',
            '<tpl elseif="preview">',
                '<div class="thumbnail previewed">',
            '<tpl else>',
                '<div class="thumbnail">',
            '</tpl>',
                    '<tpl if="enabled">',
                        '<div class="hint enabled">',
                            '<span>{/literal}{s name=enabled}Enabled{/s}{literal}</span>',
                        '</div>',
                    '<tpl elseif="preview">',
                        '<div class="hint preview">',
                            '<span>{/literal}{s name=preview_hint}Preview{/s}{literal}</span>',
                        '</div>',
                    '</tpl>',

                    '<div class="thumb">',
                        '<div class="inner-thumb">',
                            '<tpl if="screen">',
                                '<img src="{screen}" title="{name}" />',
                            '</tpl>',
                        '</div>',
                        '<tpl if="hasConfig">',
                            '<div class="mapping-config">&nbsp;</div>',
                        '</tpl>',
                    '</div>',
                    '<span class="x-editable">{name}</span>',
                '</div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}'
        );
    }

});

//{/block}

