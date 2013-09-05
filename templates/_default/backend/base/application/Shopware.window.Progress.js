
//{block name="backend/application/window/progress"}
Ext.define('Shopware.window.Progress', {
    extend: 'Ext.window.Window',
    title: 'Delete items',
    alias: 'widget.shopware-progress-window',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    width: 600,
    modal: true,
    bodyPadding: 20,
    height: 360,
    closable: false,

    /**
     * Internal flag which will be set when the user clicks on the
     * cancel button at the bottom of the window.
     *
     * @type { boolean }
     */
    cancelProcess: false,

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

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
         *      Ext.define('Shopware.apps.Product.view.batch.Window', {
         *          extend: 'Shopware.window.Progress',
         *          displayConfig: {
         *              infoText: 'Deletes all selected products in a batch mode ...',
         *              ...
         *          }
         *      });
         */
        displayConfig: {

            /**
             * Contains an info text which will be displayed at top of the window.
             * @type { String }
             */
            infoText: undefined,

            /**
             * List of tasks which will be executed.
             * @example
             *  records = [ recordA, recordB, ... ];
             *
             *  window = Ext.create('Shopware.window.Progress', {
             *      displayConfig: {
             *          infoText: 'Delete products in a batch mode. Each product will be deleted in a single request. This task requires some minutes, please wait ...',
             *          tasks: [
             *              {
             *                  text: 'Delete product [0] of [1]',
             *                  event: 'delete-product-item',
             *                  totalCount: records.length,
             *                  data: records
             *              }
             *          ]
             *      }
             *  });
             *
             * @type { Array }
             */
            tasks: [ ],

            /**
             * Array of fields which will be displayed in the result grid.
             *
             * @type { Array }
             */
            outputProperties: [ 'id', 'number', 'name' ],

            /**
             * Flag to hide or display the result grid of the batch window.
             *
             * @type { boolean }
             */
            displayResultGrid: true
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = { };

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({ }, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({ }, config, definition.configure());
            }
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
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },


    /**
     * Initialisation of this component.
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();

        me.fireEvent('before-init-component', me);

        //reset the cancel process flag
        me.cancelProcess = false;

        me.items = me.createItems();

        me.dockedItems = [ me.createToolbar() ];

        me.fireEvent('after-init-component', me);

        me.callParent(arguments);

        me.fireEvent('before-start-sequential-process', me);

        //starts the batch process.
        me.sequentialProcess(undefined, me.getConfig('tasks'));
    },

    /**
     * Register all required custom events of this component.
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Event fired at the beginning of the { @link #initComponent } function.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             */
            'before-init-component',

            /**
             * Event fired after the default shopware elements for this component created
             * and before the me.callParent(arguments) call in the { @link #initComponent } function
             * executed.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             */
            'after-init-component',

            /**
             * Event fired after the me.callParent(arguments) call in the { @link #initComponent } function
             * and before the batch process started in the { @link #sequentialProcess } function.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             */
            'before-start-sequential-process',

            /**
             * Event fired before the default shopware elements for this component will be created.
             * If the event listener function returns false, the function process will be canceled
             * and the items parameter will be set as function return value.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Array } items - An empty array at this point, which used as function return value.
             */
            'before-create-items',

            /**
             * Event fired after all default shopware items for this component created.
             * This event can be used to add additional component items.
             * The items parameter contains all created elements and will be set as items
             * array of this component.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Array } items - Contains all created items like the info text, task progress bars and the result field set.
             */
            'after-create-items',

            /**
             * Event fired before the default shopware toolbar will be created.
             * If the event listener function returns false, the function will be canceled and the
             * toolbar parameter will be set as function return value.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Null } toolbar - This parameter will be set as return value if the event listener returns false.
             */
            'before-create-toolbar',

            /**
             * Event fired after the toolbar element created.
             * This event can be used to modify the toolbar view. To add additional toolbar items
             * or to remove items, you can use the following events:
             *  - after-create-toolbar-items
             *  - before-create-toolbar-items
             *  - after-create-toolbar-fill-item
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Ext.toolbar.Toolbar } toolbar - The created bottom toolbar.
             */
            'after-create-toolbar',

            /**
             * Event fired
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Array } items - An empty array at this point, which will be set as function return value if the event listener returns false.
             */
            'before-create-toolbar-items',

            /**
             * Event fired after the tb fill element pushed in the toolbar items array.
             * This event can be used to add elements after the fill element but before
             * shopware adds the default toolbar items.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Array } items - Array which contains a tb fill element and will be set as function return value.
             */
            'after-create-toolbar-fill-item',

            /**
             * Event fired after the default shopware toolbar items created.
             * This event can be used to remove or add items in the items array.
             * The items parameter will be set as function return value.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Array } items - Contains all created toolbar items. This parameter will be set as function return value.
             */
            'after-create-toolbar-items',

            /**
             * Event fired at the beginning of the { @link #createResultFieldSet } function.
             * The event can be used to cancel the function process, by returning false in the event listener function.
             * If the event listener returns false, the fieldSet parameter will be set as function return value.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Null } fieldSet - This parameter will be set as return value if the event listener returns false.
             */
            'before-result-field-set-created',

            /**
             * Event fired after the result grid created.
             * The resultGrid parameter will be set into the items array of the result field set.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Ext.grid.Panel } resultGrid - Instance of the created result grid
             */
            'after-result-grid-created',

            /**
             * Event fired after the result field set was created in the { @link #createResultFieldSet }
             * function.
             * The resultFieldSet parameter will be set as function return value.
             * To modify the result grid or add some event listeners to the grid, use the
             * after-result-grid-created event.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Ext.form.FieldSet } resultFieldSet - This parameter is used as function return value.
             */
            'after-result-field-set-created',

            /**
             * Event fired after a task toolbar created. This event can be used to
             * add event listeners to the toolbar element or to modify the toolbar view.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Object } task - The task for which the toolbar will be created
             * @param { Ext.ProgressBar } progressbar - The created progress bar for the current task. This parameter will be set as return value.
             */
            'task-toolbar-created',

            /**
             * Event fired after all task processes done or if the user canceled the
             * sequential process over the cancel button.
             * If the process canceled over the button, the processCanceled parameter
             * is set to true.
             *
             * @param { Shopware.window.Progress } window - Instance of this component.
             * @param { Object } task - The last task which was executed
             * @param { Boolean } processCanceled - Flag if the process canceled over the cancel button.
             */
            'process-done'
        );
    },

    /**
     * Creates all required elements for this component.
     * Shopware create a info text container, result grid for the task results and
     * a progress bar foreach task definition.
     *
     * @returns { Array }
     */
    createItems: function () {
        var me = this, items = [], item;
        var tasks = me.getConfig('tasks');

        if (!me.fireEvent('before-create-items', me, items)) {
            return items;
        }

        if (me.getConfig('infoText')) {
            items.push(me.createInfoText());
        }

        Ext.each(tasks, function (task) {
            item = me.createTaskProgressBar(task);
            if (item) {
                items.push(item);
            }
        });

        if (me.getConfig('displayResultGrid')) {
            items.push(me.createResultFieldSet());
        }

        me.fireEvent('after-create-items', me, items);

        return items;
    },


    /**
     * Creates the bottom toolbar for the batch window.
     * The toolbar contains as default a cancel button to cancel the batch process
     * and a close window button to close the bath window.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this, toolbar = null;

        if (!me.fireEvent('before-create-toolbar', me, toolbar)) {
            return toolbar;
        }

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: me.createToolbarItems()
        });

        me.fireEvent('after-create-toolbar', me, me.toolbar);

        return me.toolbar;
    },

    /**
     * Creates the toolbar elements.
     * The first element is a toolbar fill element to display
     *
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this, items = [];

        if (!me.fireEvent('before-create-toolbar-items', me, items)) {
            return items;
        }

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: 'Cancel process',
            handler: function() {
                me.cancelProcess = true;
            }
        });

        me.closeButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: 'Close window',
            disabled: true,
            handler: function() { me.destroy() }
        });

        items.push('->');

        me.fireEvent('after-create-toolbar-fill-item', me, items);

        items.push(me.cancelButton);

        items.push(me.closeButton);

        me.fireEvent('after-create-toolbar-items', me, items);

        return items;
    },


    /**
     * Creates the result grid which displays each request and response of each single task.
     * The result grid will be displayed within a field set which can be collapsed.
     *
     * @returns { Ext.form.FieldSet }
     */
    createResultFieldSet: function () {
        var me = this, fieldSet = null;

        if (!me.fireEvent('before-result-field-set-created', me, fieldSet)) {
            return fieldSet;
        }
        
        me.resultStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.model.Error'
        });

        me.resultGrid = Ext.create('Ext.grid.Panel', {
            border: false,
            columns: [
                { xtype: 'rownumberer', width: 30 },
                { header: 'Success', dataIndex: 'success', width: 60, renderer: me.successRenderer },
                { header: 'Request', dataIndex: 'request', flex: 1, renderer: me.requestRenderer, scope: me },
                { header: 'Error message', dataIndex: 'error', flex: 1 }
            ],
            store: me.resultStore
        });

        me.fireEvent('after-result-grid-created', me, me.resultGrid);

        me.resultFieldSet = Ext.create('Ext.form.FieldSet', {
            items: [ me.resultGrid ],
            layout: 'fit',
            collapsible: true,
            collapsed: false,
            flex: 1,
            margin: '20 0 0',
            title: 'Request results'
        });

        me.fireEvent('after-result-field-set-created', me, me.resultFieldSet);

        return me.resultFieldSet;
    },


    /**
     * Creates an Ext.container.Container for the info text which will
     * be displayed at top of the window.
     *
     * @returns { Ext.container.Container }
     */
    createInfoText: function () {
        return Ext.create('Ext.container.Container', {
            html: this.getConfig('infoText'),
            style: 'line-height:20px;'
        });
    },


    /**
     * Creates a progress bar for the passed task object.
     * The task can contains a text which will be displayed within the
     * progress bar.
     *
     * @param { Object } task - The current task
     * @returns { Ext.ProgressBar }
     */
    createTaskProgressBar: function (task) {
        var me = this;

        task.progressBar = Ext.create('Ext.ProgressBar', {
            animate: true,
            text: Ext.String.format(task.text, 0, task.totalCount),
            value: 0,
            height: 20,
            margin: '15 0 0'
        });

        me.fireEvent('task-toolbar-created', me, task, task.progressBar);

        return task.progressBar;
    },

    /**
     * Recursive helper function which executes the different tasks and iterates
     * the task records.
     *
     * @param current
     * @param tasks
     * @returns { boolean }
     */
    sequentialProcess: function (current, tasks) {
        var me = this, record;

        //no current task passed? Take the first task in the tasks array
        if (current == undefined && tasks.length > 0) {
            current = tasks.shift();
        }

        //contains the current task no more record/data? Then get the next from the array
        if (current.data && current.data.length <= 0) {
            current = tasks.shift();
        }

        //no more current task configured? Or the process was canceled over the button?
        if (!current || me.cancelProcess) {

            //disabled cancel button and enable the close window button
            me.closeButton.enable();
            me.cancelButton.disable();

            //if the process canceled over the button, set an info text at which position the process canceled.
            if (me.cancelProcess) {
                me.updateProgressBar(current, 'Process canceled at position [0] of [1]');
            }
            me.fireEvent('process-done', me, current, me.cancelProcess);

            return false;
        }

        //get next record of the data array of the current task.
        record = current.data.shift();
        me.updateProgressBar(current, current.text);


        /**
         * The progress window don't know, how to handle the data operation. Delete, update, create, save?
         * Or an Ext.Ajax Request? The event can be defined for each task definition.
         *
         * IMPORTANT: The event listener has the "callback" parameter, you have to call the callback function manuel!
         * @example:
         *
         * onBatchProcess: function (task, record, callback) {
         *     record.destroy({
         *         success: function (result, operation) {
         *             callback(result, operation);
         *         }
         *     });
         * },
         *
         * If you have no request result or Ext.data.Operation, you can fake this object:
         * @example
         * onBatchProcess: function (task, record, callback) {
         *     var me = this;
         *
         *     operation = {
         *          wasSuccessful: function() { return true; },
         *          getError: function() { return 'No error ...' },
         *          request: { url: "MY_REQUEST_URL" }
         *     };
         *
         *     callback({  }, operation);
         * },
         *
         */
        me.fireEvent(current.event, current, record, function(result, operation) {

            if (me.getConfig('displayResultGrid')) {
                me.resultStore.add(
                    me.createResponseRecord(operation)
                );
                if (!operation.wasSuccessful()) {
                    me.resultFieldSet.expand();
                }
            }

            //recursive call!
            me.sequentialProcess(current, tasks);
        });

        return true;
    },

    /**
     * Helper function to update the progress bar text and process.
     *
     * @param { Object } task
     * @param { String } text
     */
    updateProgressBar: function(task, text) {
        var index = task.totalCount - task.data.length;

        task.progressBar.updateProgress(
            index / task.totalCount,
            Ext.String.format(text, index, task.totalCount),
            true
        );
    },


    /**
     * Creates a response record for the result grid.
     *
     * @param { Ext.data.Operation } operation - The request operation.
     * @returns { Shopware.model.Error }
     */
    createResponseRecord: function(operation) {
        return Ext.create('Shopware.model.Error', {
            success: operation.wasSuccessful(),
            error: operation.getError(),
            request: operation.request,
            operation: operation
        });
    },


    /**
     * Success renderer function of the result grid which displayed
     * an tick icon if the request was successfully and a red cross
     * if the request contains an exception.
     *
     * @param value
     * @param metaData
     * @returns { String }
     */
    successRenderer: function(value, metaData) {
        metaData.tdAttr = 'style="vertical-align: middle;"';
        var css = 'sprite-cross-small';
        if (value) {
            css = 'sprite-tick-small'
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + css + '"></span>';
    },


    /**
     * Grid column renderer function for the request column.
     * This function combines the request url and the request parameter to
     * a list which will be displayed in the grid.
     *
     * @param value
     * @param metaData
     * @param record
     * @returns { String }
     */
    requestRenderer: function(value, metaData, record) {
        var me = this, operation, propertyValue,
            params = [], requestRecord,
            properties = me.getConfig('outputProperties');

        operation = record.get('operation');
        requestRecord = operation.getRecords();
        requestRecord = requestRecord[0];

        params.push('<strong>url</strong> = ' + value.url);
        Ext.each(properties, function(property) {
            propertyValue = requestRecord.get(property);
            if (propertyValue) {
                params.push('<strong>' + property + '</strong> = ' + propertyValue);
            }
        });

        return params.join('<br>');
    }

});
//{/block}
