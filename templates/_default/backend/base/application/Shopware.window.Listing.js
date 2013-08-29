
//{block name="backend/application/window/listing"}

Ext.define('Shopware.window.Listing', {
    extend: 'Enlight.app.Window',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    layout: 'border',

    width: 990,

    height: '50%',

    alias: 'widget.shopware-window-listing',

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can set the displayConfig directly
         * as property of the component:
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.list.Window', {
         *          extend: 'Shopware.window.Listing',
         *          displayConfig: {
         *              listingGrid: 'Shopware.apps.Product.view.list.Product',
         *              listingStore: 'Shopware.apps.Product.store.Product'
         *              ...
         *          }
         *      });
         */
        displayConfig: {
            /**
             * Class name of the grid which will be displayed in the center
             * region of this window.
             *
             * @type { String }
             * @optional
             */
            listingGrid: 'Shopware.grid.Panel',

            /**
             * Class name of the grid store. This store will be set in the
             * listingGrid instance as grid store.
             * The store will be loaded over this component so don't set the
             * autoLoad parameter of the store to true.
             *
             * @type { String }
             * @required
             */
            listingStore: undefined,

            /**
             * Alias for the fired events to prevent a duplicate event name
             * in different modules.
             *
             * @type { String }
             */
            eventAlias: undefined,

            extensions: [ ],
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

    /**
     * Initialisation of this component.
     * Creates all required elements for this component.
     */
    initComponent: function () {
        var me = this;

        me.listingStore = me.createListingStore();
        me.eventAlias = me.getConfig('eventAlias');
        if (!me.eventAlias) me.eventAlias = me.getEventAlias(me.listingStore.model.$className);

        me.registerEvents();

        me.fireEvent(me.eventAlias + '-before-init-component', me);

        me.items = me.createItems();

        me.fireEvent(me.eventAlias + '-after-init-component', me);

        me.callParent(arguments);
    },

    /**
     * Registers all required custom events of this component.
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(

            /**
             * Event fired before the window element will be create in the { @link #createItems } function.
             * The listing store is already created at this point and can be access over "window.listingStore".
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             */
            me.eventAlias + '-before-init-component',

            /**
             * Event fired before the shopware default items of the listing window will be created.
             * This event can be used to insert some elements at the beginning of the items array.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Array } items - Contains the create window elements.
             */
            me.eventAlias + '-before-create-items',

            /**
             * Event fired after the shopware default items of the listing window created.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Array } items - Contains the create window elements.
             */
            me.eventAlias + '-after-create-items',

            /**
             * Event fired after the default shopware elements for this component
             * created and all defined extensions loaded.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Array } items - Contains the created window elements and all defined extensions
             */
            me.eventAlias + '-after-extensions-loaded',

            /**
             * Event fired after the { @link Shopware.grid.Panel } created.
             * This event can be used to modify the grid view or to reposition the grid within the window.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             * @param { Shopware.grid.Panel } grid - Instance of the create { @link Shopware.grid.Panel }
             */
            me.eventAlias + '-after-create-grid-panel',

            /**
             * Event fired after the component was initialed. The event is fired before the me.callParent(arguments)
             * function called in the initComponent function.
             *
             * @param { Shopware.window.Listing } window - Instance of this component.
             */
            me.eventAlias + '-after-init-component'
        );
    },

    /**
     * Creates the listing store for the grid panel.
     *
     * @returns { Shopware.store.Listing }
     */
    createListingStore: function() {
        return Ext.create(this.getConfig('listingStore'));
    },

    /**
     * Creates all required elements for this component.
     *
     * @returns { Array }
     */
    createItems: function () {
        var me = this, items = [];

        me.fireEvent(me.eventAlias + '-before-create-items', me, items);

        items.push(me.createGridPanel());

        me.fireEvent(me.eventAlias + '-after-create-items', me, items);

        Ext.each(me.getConfig('extensions'), function(extension) {
            //extension isn't defined? Continue with next extension
            if (!extension) return true;

            //support for simple extension definition over strings
            if (Ext.isString(extension)) extension = { xtype: extension };

            extension.listingWindow = me;
            items.push(extension);
        });

        me.fireEvent(me.eventAlias + '-after-extensions-loaded', me, items);

        return items;
    },

    /**
     * Creates the grid panel for the listing window.
     * The grid panel requires the listing store which will be set as grid store.
     *
     * @returns { Shopware.grid.Panel }
     */
    createGridPanel: function () {
        var me = this;

        me.listingStore.load();

        me.gridPanel = Ext.create(me.getConfig('listingGrid'), {
            store: me.listingStore,
            flex: 1
        });

        me.fireEvent(me.eventAlias + '-after-create-grid-panel', me, me.gridPanel);

        return me.gridPanel;
    }

});
//{/block}
