//{block name="backend/application/grid/controller"}

/**
 * The Shopware.grid.Controller contains the Shopware default controls
 * for a full featured backend listing.
 * Each Shopware.grid.Panel requires an own instance of this controller.
 * The grid panel creates as default an own Shopware.grid.Controller instance.
 * If you want to handle the listing controls in your own controller you
 * can use the following source as example:
 * @example
 *
 * Ext.define('Shopware.apps.Product.controller.Listing', {
 *     extend: 'Shopware.grid.Controller',
 *     displayConfig: {
 *         gridClass: 'Shopware.apps.Product.view.list.Product',
 *         eventAlias: 'product'
 *     }
 * });
 *
 *
 * Ext.define('Shopware.apps.Product.view.list.Product', {
 *    extend: 'Shopware.grid.Panel',
 *    alias:  'widget.product-listing-grid',
 *    displayConfig: {
 *        detailWindow: 'Shopware.apps.Product.view.detail.Window',
 *        eventAlias: 'product'
 *    }
 * });
 *
 * This component fires the following custom events:
 *  @event 'eventAlias-before-open-delete-window'
 *  @event 'eventAlias-batch-delete-exception'
 *  @event 'eventAlias-batch-delete-success'
 *  @event 'eventAlias-after-selection-changed'
 *  @event 'eventAlias-before-add-item'
 *  @event 'eventAlias-before-search'
 *  @event 'eventAlias-before-page-size-changed'
 *  @event 'eventAlias-before-edit-item'
 *  @event 'eventAlias-before-create-detail-window'
 *  @event 'eventAlias-after-create-detail-window'
 *  @event 'eventAlias-after-init'
 *
 * The event parameter are documented in the { @link #registerEvents } function.
 */
Ext.define('Shopware.grid.Controller', {
    extend: 'Ext.app.Controller',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    /**
     * The statics object contains the shopware default configuration for
     * this component.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig is the shopware default configuration for
         * this component.
         * To override this property you can use the controller.displayConfig object.
         *
         * @example
         * Ext.define('Shopware.apps.Product.controller.Product', {
         *     extend: 'Shopware.grid.Controller',
         *     displayConfig: {
         *         ...
         *     }
         * });
         */
        displayConfig: {
            /**
             * Final class of the Shopware.grid.Panel.
             * This class is required to get the alias of the component.
             *
             * @required
             * @type { string }
             */
            gridClass: undefined,

            /**
             * Suffix alias for the different component events.
             * This alias must the same alias of the { @link Shopware.grid.Panel:eventAlias }  component.
             * If you don't know the alias you can output the alias of the grid panel as follow:
             * console.log("alias", me.eventAlias);
             *
             * If you haven't configured a custom event alias, the { @link Shopware.grid.Panel } creates
             * the event alias over the configured model.
             * @example
             * If you passed a store with an model named: 'Shopware.apps.Product.model.Product'
             * the { @link Shopware.grid.Panel } use "product" as event alias.
             *
             * @required
             * @type { string }
             */
            eventAlias: undefined,

            /**
             * Title of the confirm message box.
             * The confirm box will be displayed when the user try to delete some grid items.
             *
             * @type { string }
             */
            deleteConfirmTitle: 'Delete items',

            /**
             * Message of the confirm message box.
             * The confirm box will be displayed when the user try to delete some grid items.
             *
             * @type { string }
             */
            deleteConfirmText: 'Are you sure you want to delete the selected items?',

            /**
             * Info text of the { @link Shopware.window.Progress }.
             * The { @link Shopware.window.Progress } window will be displayed when the user
             * try to delete some grid items.
             *
             * @type { string }
             */
            deleteInfoText: '<b>The records will be deleted.</b> <br>To cancel the process, you can use the <b><i>`Cancel process`</i></b> Button. Depending on the selected volume of data may take several seconds to complete this process.',

            /**
             * The progress bar text of the { @link Shopware.window.Progress }.
             * The snippet contains two placeholders which will be replaced at runtime.
             * The first placeholder will be replaced with the current index and
             * the second placeholder with the total count of records.
             *
             * @type { string }
             */
            deleteProgressBarText: 'Item [0] of [1]'
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

            config = Ext.apply({ }, userOpts.displayConfig, displayConfig);
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
     * The function calls the internal function createListingWindow to open
     * the listing window.
     * After the window created the function adds the event controls
     * over the createControls function.
     */
    init: function () {
        var me = this;

        if (me.getConfig('eventAlias')) {
            me.control(me.createControls());
            me.registerEvents();
        }

        me.fireEvent(me.getEventName('after-init'), me);

        me.callParent(arguments);
    },

    /**
     * Registers all required custom events of this component.
     */
    registerEvents: function() {
        var me = this;

        this.addEvents(
            /**
             * Event fired before the batch window opened to delete multiple grid items.
             * If you set false as return value in the even listener, the window won't be opened.
             * This allows you to implement your own delete process.
             * The last event parameter contains the selected records which has to be delete.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.window.Progress } window - Created instance of the Shopware.window.Progress.
             * @param { Shopware.grid.Panel } controller - Instance of the controlled Shopware.grid.Panel
             * @param { Shopware.data.Model[] } records - All selected records.
             */
            me.getEventName('before-open-delete-window'),

            /**
             * Event fired if an exception occurred on removing a single grid row over the batch delete
             * window.
             * The Ext.data.Operation contains the occurred error message in operation.getException().
             * The passed Shopware.data.Model is the record which was tried to delete.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.data.Model } record - The record which was trying to delete
             * @param { Object } task - The current task configuration which passed to the window constructor.
             * @param { Object } response - The Ext.data.Operation response object.
             * @param { Ext.data.Operation } operation - The operation which throws the exception.
             */
            me.getEventName('batch-delete-exception'),

            /**
             * Event fired after a single record was deleted successfully from the batch window.
             * The passed record, contains the data of the deleted record for additionally notifications or processes.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.data.Model } record - The deleted record.
             * @param { Object } task - The current task configuration which passed to the window constructor.
             * @param { Object } result - The result set of the data operation.
             * @param { Ext.data.Operation } operation - The destroy operation which was executed.
             */
            me.getEventName('batch-delete-success'),

            /**
             * Event fired after the user changed the grid selection over the grid selection model.
             * To cancel the selection change set the event listener return value to false.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.grid.Panel } grid - The controlled grid panel of the grid controller.
             * @param { Ext.selection.CheckboxModel } selModel - The grid selection model.
             * @param { Ext.data.Model[] } selection - The current selection of the grid.
             */
            me.getEventName('after-selection-changed'),

            /**
             * Event fired before a new record will be displayed in the detail window.
             * If the event listener returns false, the window won't be created.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.grid.Panel } grid - The controlled grid panel of the grid controller.
             * @param { Shopware.data.Model } record - The created record which will be displayed in the detail window
             */
            me.getEventName('before-add-item'),

            /**
             * Event fired before the inserted search value will be set as grid store filter value.
             * To cancel the search process set the return value of the event listener function to false.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.grid.Panel } grid - The controlled grid panel of the grid controller.
             * @param { Shopware.data.Store } store - The grid store.
             * @param { Ext.form.field.Text } searchField - The search field of the grid.
             * @param { String } value - The trimmed search value, which will be assigned to the store.filter function.
             */
            me.getEventName('before-search'),

            /**
             * Event is fired before the grid store page size changed and the store will be reloaded.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.grid.Panel } grid - The controlled grid panel of the grid controller.
             * @param { Ext.form.field.ComboBox } combo - The page size combo box.
             * @param { Ext.data.Model[] } selection - The combo box selection.
             */
            me.getEventName('before-page-size-changed'),

            /**
             * Event is fired before the detail window will be opened to edit a single grid row.
             * To cancel the window creation, set false as return value in the event listener.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.grid.Panel } grid - The controlled grid panel of the grid controller.
             * @param { Shopware.data.Model } record - The record which will be displayed in the detail window to edit.
             */
            me.getEventName('before-edit-item'),

            /**
             * General event which fired each time before the detail window will be created.
             * This event is even fired when a new record will be displayed or a grid record will be edited.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.data.Model } record - The record which will be displayed in the detail window.
             */
            me.getEventName('before-create-detail-window'),

            /**
             * General event which fired each time after a detail window was created.
             * This event is fired when the user creates a new record or edit an existing record.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             * @param { Shopware.data.Model } record - The record which will be displayed in the detail window to edit.
             * @param { Shopware.detail.Window } window - The created detail window.
             */
            me.getEventName('after-create-detail-window'),

            /**
             * Event fired after the controller was initialed, but before the me.callParent(arguments) was called.
             *
             * @param { Shopware.grid.Controller } controller - Instance of this component
             */
            me.getEventName('after-init')
        );
    },

    /**
     * Creates the control object which contains all event listener
     * definitions for this controller.
     *
     * This function requires the displayConfig.listingGrid parameter.
     * If this parameter isn't set, the function won't be called.
     *
     * @returns { Object }
     */
    createControls: function () {
        var me = this, alias, controls = {}, events = {};

        alias = Ext.ClassManager.getAliasesByName(me.getConfig('gridClass'));
        alias = alias[0];
        alias = alias.replace('widget.', '');
        controls[alias] = me.createListingWindowControls();

        events[me.getConfig('eventAlias') + '-batch-delete-item'] = me.onBatchDeleteItem;
        controls['shopware-progress-window'] = events;

        return controls;
    },

    /**
     * Creates the event controls for the configured listing grid.
     * Adds all shopware default events like addItem or editItem, etc.
     *
     * @returns { Object }
     */
    createListingWindowControls: function () {
        var me = this, events = {}, alias;

        alias = me.getConfig('eventAlias');

        events[alias + '-selection-changed'] = me.onSelectionChanged;
        events[alias + '-add-item'] = me.onAddItem;
        events[alias + '-delete-item'] = me.onDeleteItem;
        events[alias + '-delete-items'] = me.onDeleteItems;
        events[alias + '-edit-item'] = me.onEditItem;
        events[alias + '-search'] = me.onSearch;
        events[alias + '-change-page-size'] = me.onChangePageSize;

        return events;
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel } component.
     * This event is fired when the user uses the "delete items" button within the grid toolbar
     * to delete multiple items.
     *
     * The function creates an { @link Shopware.window.Progress } which deletes the items
     * in an batch mode.
     *
     * @param grid { Shopware.grid.Panel }
     * @param records { Array }
     * @param button { Ext.button.Button }
     */
    onDeleteItems: function (grid, records, button) {
        var me = this, window;

        Ext.MessageBox.confirm(me.getConfig('deleteConfirmTitle'), me.getConfig('deleteConfirmText'), function (response) {
            if (response !== 'yes') {
                return false;
            }

            if (!me.hasModelAction(records[0], 'destroy')) {
                grid.getStore().remove(records);
                return true;
            }

            window = Ext.create('Shopware.window.Progress', {
                displayConfig: {
                    infoText: me.getConfig('deleteInfoText'),
                    tasks: [
                        {
                            text: me.getConfig('deleteProgressBarText'),
                            event: me.getConfig('eventAlias') + '-batch-delete-item',
                            totalCount: records.length,
                            data: records
                        }
                    ]
                }
            });

            if (!Shopware.app.Application.fireEvent(me.getEventName('before-open-delete-window'), me, window, grid, records)) {
                return false;
            }

            window.show();
        });
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel.deleteColumn }.
     * This event is fired when the user clicks on the delete action column.
     *
     * The function calls the internal { @link #onDeleteItems }  function which displays an { @link Shopware.window.Progress }
     * window to delete all selected items.
     *
     * @param grid { Shopware.grid.Panel }
     * @param record { Shopware.data.Model }
     */
    onDeleteItem: function (grid, record) {
        var me = this;
        me.onDeleteItems(grid, [ record ], null);
    },



    /**
     * Event listener function of the { @link Shopware.window.Progress:sequentialProcess } function.
     * This event fired for each record which passed to the progress window.
     *
     * @param task { Object }
     * @param record { Ext.data.Model }
     * @param callback { Function }
     */
    onBatchDeleteItem: function (task, record, callback) {
        var me = this, proxy = record.getProxy(), data;
        callback = callback || Ext.emptyFn;

        proxy.on('exception', function (proxy, response, operation) {
            data = Ext.decode(response.responseText);
            operation.setException(data.error);

            if (!Shopware.app.Application.fireEvent(me.getEventName('batch-delete-exception'), me, record, task, response, operation)) {
                return false;
            }

            callback(response, operation);

        }, me, { single: true });

        record.destroy({
            success: function (result, operation) {
                if (!Shopware.app.Application.fireEvent(me.getEventName('batch-delete-success'), me, record, task, result, operation)) {
                    return false;
                }

                callback(result, operation);
            }
        });
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel:selectionModel } component.
     * Fired when the user change the selection over the checkbox selection model.
     *
     * @param grid { Shopware.grid.Panel }
     * @param selModel { Ext.selection.CheckboxModel }
     * @param selection { Array }
     * @returns { boolean }
     */
    onSelectionChanged: function (grid, selModel, selection) {
        var me = this;

        if (!(grid instanceof Ext.grid.Panel)) {
            return false;
        }
        if (!grid.deleteButton) {
            return false;
        }
        grid.deleteButton.setDisabled(selection.length <= 0);

        return Shopware.app.Application.fireEvent(me.getEventName('after-selection-changed'), me, grid, selModel, selection);
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel:addButton }.
     * Fired when the user clicks the add button.
     * Creates a new instance of the grid store model an displays it in a new detail window.
     *
     * @param listing { Shopware.grid.Panel }
     * @returns { Shopware.window.Detail|boolean }
     */
    onAddItem: function (listing) {
        var me = this, record, store = listing.getStore();

        record = Ext.create(store.model);

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-add-item'), me, listing, record)) {
            return false;
        }

        return me.createDetailWindow(
            record,
            listing.getConfig('detailWindow')
        );
    },

    /**
     * Event listener function of the { @link Shopware.grid.Panel:createSearchField }
     * The event is fired when the user insert a search string into the grid toolbar.
     * The search field can be enabled or disabled over the { @link Shopware.grid.Panel:searchField } property.
     *
     * @param grid { Shopware.grid.Panel }
     * @param searchField { Ext.form.field.Text }
     * @param value { String }
     * @returns { boolean }
     */
    onSearch: function (grid, searchField, value) {
        var me = this, store = grid.getStore();

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-search'), me, grid, store, searchField, value)) {
            return false;
        }

        if (value.length > 0) {
            store.filter({ property: 'search', value: value });
        } else {
            store.load();
        }

        return true;
    },

    /**
     * Event listener function of the { @link Shopware.grid.Panel:pageSizeCombo }.
     * The event is fired when the user change the combo box value to change the
     * grid store page size.
     *
     * @param grid { Shopware.grid.Panel }
     * @param combo { Ext.form.field.ComboBox }
     * @param records { Array }
     * @returns { boolean }
     */
    onChangePageSize: function (grid, combo, records) {
        var me = this,
            store = grid.getStore();

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-page-size-changed'), me, grid, combo, records)) {
            return false;
        }

        if (combo.getValue() > 0) {
            store.pageSize = combo.getValue();
            store.currentPage = 1;
            store.load();
        }

        return true;
    },


    /**
     * Event listener function of the { @link Shopware.grid.Panel:editColumn }.
     * The event is fired when the user clicks the action edit column.
     *
     * @param listing { Shopware.grid.Panel }
     * @param record { Shopware.data.Model }
     * @returns { boolean|Shopware.window.Detail }
     */
    onEditItem: function (listing, record) {
        var me = this;

        if (!(record instanceof Ext.data.Model)) {
            return false;
        }

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-edit-item'), me, listing, record)) {
            return false;
        }

        if (me.hasModelAction(record, 'detail')) {
            record.reload({
                callback: function (result) {
                    me.createDetailWindow(
                        result,
                        listing.getConfig('detailWindow')
                    );
                }
            });
            return true;
        } else {
            return me.createDetailWindow(
                record,
                listing.getConfig('detailWindow')
            );
        }
    },

    /**
     * Helper function which creates a detail window for the passed record.
     * The second parameter contains the detail window class name.
     *
     * @param record { Shopware.data.Model } - The record which will be displayed in the detail window
     * @param detailWindowClass { string } - Class name of the detail window
     */
    createDetailWindow: function (record, detailWindowClass) {
        var me = this, window;

        if (!detailWindowClass) {
            return false;
        }

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-create-detail-window'), me, record)) {
            return false;
        }

        window = me.getView(detailWindowClass).create({
            record: record
        });

        if (!Shopware.app.Application.fireEvent(me.getEventName('after-create-detail-window'), me, record, window)) {
            return false;
        }

        if (window) {
            window.show();
        }

        return window;
    },


    /**
     * Helper function to prefix the passed event name with the event alias.
     *
     * @param name
     * @returns { string }
     */
    getEventName: function (name) {
        return this.getConfig('eventAlias') + '-' + name;
    }

});
//{/block}
