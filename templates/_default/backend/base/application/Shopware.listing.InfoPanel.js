
Ext.define('Shopware.listing.InfoPanel', {
    extend: 'Ext.panel.Panel',

    alias: 'widget.listing-info-panel',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    region: 'east',
    width: 300,
    cls: 'detail-view',
    collapsible: true,
    layout: 'fit',


    title: 'Detailed information',

    statics: {
        displayConfig: {
            model: undefined
        },
        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         *
         * @param userOpts Object
         * @param displayConfig Object
         * @returns Object
         */
        getDisplayConfig: function (userOpts, displayConfig) {
            var config;

            if (userOpts && userOpts.displayConfig) {
                config = Ext.apply({ }, userOpts.displayConfig);
            }

            config = Ext.apply({ }, config, displayConfig);
            config = Ext.apply({ }, config, this.displayConfig);
            return config;
        },

        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },

    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this.displayConfig);
        me.callParent(arguments);
    },


    /**
     * Helper function to get config access.
     *
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },


    initComponent: function() {
        var me = this;

        me.gridPanel = me.listingWindow.gridPanel;

        me.items = me.createItems();

        me.addEventListeners();

        me.callParent(arguments);
    },

    addEventListeners: function() {
        var me = this;

        me.gridPanel.on(me.gridPanel.eventAlias + '-selection-changed', function(grid, selModel, records) {
            var record = { };
            if (records.length > 0) {
                record = records.shift();
            }
            me.updateInfoView(record);
        });
    },

    createItems: function() {
        var me = this, items = [];

        items.push(me.createInfoView());

        return items;
    },

    createInfoView: function(){
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            tpl: me.createTemplate(),
            flex: 1,
            style: 'color: #6c818f;font-size:11px',
            emptyText: '<div style="font-size:13px; text-align: center;">No record selected.</div>',
            deferEmptyText: false,
            itemSelector: 'div.item',
            renderData: []
        });

        return me.infoView;
    },

    createTemplate: function() {
        var me = this, fields = [];

        if (me.getConfig('model')) {
            var model = Ext.create(me.getConfig('model'));
            Ext.each(model.fields.items, function(field) {
                fields.push('<p style="padding: 2px"><b>' + field.name +':</b> {literal}{' + field.name + '}{/literal}</p>')
            });
        }

        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="item" style="">',
                    fields.join(''),
                '</div>',
            '</tpl>'
        );
    },

    updateInfoView: function(record) {
        var me = this;

        if (record.data) {
            me.infoView.update(record.data);
        } else {
            me.infoView.update(me.infoView.emptyText);
        }

        return true;
    }
});