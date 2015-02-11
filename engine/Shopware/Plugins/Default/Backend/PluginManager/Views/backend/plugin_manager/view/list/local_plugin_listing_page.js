
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.list.LocalPluginListingPage', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.plugin-manager-local-plugin-listing',
    cls: 'plugin-manager-local-plugin-listing',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },
    viewConfig: {
        markDirty: false
    },

    configure: function() {
        return {
            addButton: false,
            pageSizeCombo: false,
            deleteButton: false,
            deleteColumn: false,
            editColumn: false,
            columns: {
                label: {
                    flex: 2,
                    header: '{s name="plugin_name"}{/s}',
                    renderer: this.nameRenderer,
                    editor: null
                },
                version: {
                    width: 30,
                    header: '{s name="version"}{/s}',
                    editor: null
                },
                installationDate: {
                    header: '{s name="installed_on"}{/s}',
                    renderer: this.dateRenderer,
                    editor: null
                },
                updateDate: {
                    header: '{s name="updated_on"}{/s}',
                    renderer: this.dateRenderer,
                    editor: null
                },
                licenceCheck: {
                    flex: 2,
                    sortable: false,
                    cls: 'licence-column',
                    header: '{s name="licence"}{/s}',
                    renderer: this.licenceRenderer,
                    editor: null
                },
                active: this.createActiveColumn,
                author: {
                    header: '{s name="from_producer"}{/s}',
                    renderer: this.authorRenderer,
                    editor: null
                }
            }
        };
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        Shopware.app.Application.on('plugin-reloaded', function(plugin) {
            me.store.each(function(record, index) {
                if (record && record.get('technicalName') == plugin.get('technicalName')) {
                    me.store.remove(record);
                }
            });

            if (plugin.get('id') > 0) {
                plugin.set('groupingState', null);
                plugin.dirty = false;
                try {
                    me.store.add(plugin);
                } catch (e) {
                    me.store.load();
                }
            }

            me.store.sort();
            me.store.group();
            me.reconfigure(me.store);
            me.hideLoadingMask();
        });
    },

    createActionColumn: function () {
        var me = this;

        var actionColumn = me.callParent(arguments);

        actionColumn.width = 120;
        return actionColumn;
    },

    createSelectionModel: function() { },

    createActiveColumn: function() {
        var me = this,
            items = [];

        items.push({
            tooltip: '{s name="activate_deactivate"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                if (record.allowActivate()) {
                    me.activatePluginEvent(record);

                } else if (record.allowDeactivate()) {
                    me.deactivatePluginEvent(record);
                }
            },
            getClass: function(value, metaData, record) {
                if (!record.allowActivate() && !record.allowDeactivate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }

                if (record.allowActivate()) {
                    return 'sprite-ui-check-box-uncheck';
                } else {
                    return 'sprite-ui-check-box';
                }
            }
        });

        return {
            xtype: 'actioncolumn',
            width: 60,
            align: 'center',
            header: '{s name="active"}{/s}',
            items: items
        };
    },


    searchEvent: function(field, value) {
        var me = this;

        me.store.clearFilter();

        value = value.toLowerCase();

        me.store.filterBy(function(record, id) {
            var description = record.get('description') + '';
            var name = record.get('label') + '';
            var technicalName = record.get('technicalName') + '';
            var producer = '';

            if (record['getProducerStore']) {
                producer = record['getProducerStore'].first().get('name') + '';
            }

            name = name.toLowerCase();
            technicalName = technicalName.toLowerCase();
            producer = producer.toLowerCase();
            description = description.toLowerCase();

            return (
                name.indexOf(value) > -1
                || description.indexOf(value) > -1
                || producer.indexOf(value) > -1
                || technicalName.indexOf(value) > -1
            );
        });
    },

    createFeatures: function() {
        var me = this,
            items = me.callParent(arguments);

        me.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: new Ext.XTemplate(
                '{literal}{name:this.formatName} ({rows.length} Plugins){/literal}',
                {
                    formatName: function(name) {
                        switch(name) {
                            case 2:
                                return '{s name="group_headline_installed"}{/s}';
                            case 1:
                                return '{s name="group_headline_deactivated"}{/s}';
                            case 0:
                                return '{s name="group_headline_uninstalled"}{/s}';
                        }
                    }
                }
            )
        });

        items.push(me.groupingFeature);
        return items;
    },

    nameRenderer: function(value, metaData, record) {
        var name = record.get('label');

        if (!record.get('localIcon') || record.get('localIcon') == 'false') {
            return name;
        }

        return '<div style="display: inline-block; position:relative; top: 2px; margin-right: 6px; width:16px; height:16px; background:url('+record.get('localIcon')+') no-repeat"></div>' + name;
    },

    authorRenderer: function(value, metaData, record) {
        if (!record || !record['getProducerStore']) {
            return value;
        }

        var producer = record['getProducerStore'].first();
        var website = producer.get('website');

        if (producer.get('name') == 'shopware AG' && !website) {
            website = 'http://www.shopware.com';
        }

        if (website && website.length > 0) {
            return '<a href="' + website + '" target="_blank">'+producer.get('name')+'</a>'
        } else {
            return producer.get('name');
        }
    },

    dateRenderer: function(value) {
        if (!value || !value.hasOwnProperty('date')) {
            return value;
        }
        var date = this.formatDate(value.date);
        return Ext.util.Format.date(date);
    },

    licenceRenderer: function(value, metaData, record) {
        if (!record || !record['getLicenceStore']) {
            return;
        }

        try {
            var licence = record['getLicenceStore'].first();
            var price = licence['getPriceStore'].first();
            var type = this.getTextForPriceType(price.get('type'));
            var expiration = licence.get('expirationDate');
            var result = type;
        } catch (e) {
            return result;
        }

        if (!expiration) {
            return result;
        }

        if (expiration) {
            result += '<br><span class="label">{s name="till"}{/s}: </span><span class="date">' + Ext.util.Format.date(expiration.date) + '</span>';
        }

        return result;
    },

    createToolbarItems: function() {
        var me = this,
            items = me.callParent(arguments);

        Ext.Array.insert(items, 0, [
            me.createUploadButton()
        ]);

        return items;
    },

    createUploadButton: function() {
        var me = this;

        me.uploadButton = Ext.create('Ext.button.Button', {
            text: '{s name="upload_plugin"}{/s}',
            iconCls: 'sprite-plus-circle',
            handler: function() {
                me.fireEvent('open-plugin-upload');
            }
        });

        return me.uploadButton;
    },

    createActionColumnItems: function() {
        var me = this, items = [];

        items.push({
            iconCls: 'sprite-pencil',
            tooltip: '{s name="open"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.displayPluginEvent(record);
            }
        });

        items.push({
            iconCls: 'sprite-plus-circle',
            tooltip: '{s name="install"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.updateDummyPluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowDummyUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-minus-circle',
            tooltip: '{s name="install_uninstall"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                if (record.allowInstall()) {
                    me.installPluginEvent(record);
                } else {
                    me.uninstallPluginEvent(record);
                }
            },
            getClass: function(value, metaData, record) {
                if (record.allowDummyUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }

                if (record.allowInstall()) {
                    return 'sprite-plus-circle';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-continue',
            tooltip: '{s name="reinstall"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.reinstallPluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowReinstall()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-circle-135',
            tooltip: '{s name="update_plugin"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.updatePluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-bin-metal-full',
            tooltip: '{s name="delete"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.deletePluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowDelete()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-circle-225-left',
            tooltip: '{s name="local_update"}{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.executePluginUpdateEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowLocalUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        return items;
    }
});