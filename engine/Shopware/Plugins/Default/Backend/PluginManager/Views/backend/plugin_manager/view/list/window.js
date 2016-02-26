
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.list.Window', {
    extend: 'Enlight.app.Window',

    layout: 'border',
    cls: 'plugin-manager-window listing-window',
    alias: 'widget.plugin-manager-listing-window',

    height: '90%',
    width: 1283,

    title: '{s name="title"}Plugin Manager{/s}',

    initComponent: function() {
        var me = this;

        me.items = [
            me.createNavigation(),
            me.createCenterPanel()
        ];

        me.callParent(arguments);

        me.on('afterrender', function() {
            me.fireEvent('plugin-manager-loaded');
        });
    },

    createCenterPanel: function() {
        var me = this;

        me.cards = [
            me.createHomePage(),
            me.createLocalPluginPage(),
            me.createPluginUpdatesPage(),
            me.createListingPage(),
            me.createAccountPage(),
            me.createLicencePage(),
            me.createPremiumPluginsPage()
        ];

        me.centerPanel = Ext.create('Ext.container.Container', {
            name: 'card-container',
            region: 'center',
            layout: 'card',
            items: me.cards
        });

        return me.centerPanel;
    },

    createNavigation: function() {
        this.navigation = Ext.create('Shopware.apps.PluginManager.view.list.Navigation', {
            region: 'west',
            width: 255
        });

        return this.navigation;
    },

    createHomePage: function() {
        this.homePage = Ext.create('Shopware.apps.PluginManager.view.list.HomePage', {
            cardIndex: 0
        });
        return this.homePage;
    },

    createLocalPluginPage: function() {
        var me = this;

        me.localPluginStore = Ext.create('Shopware.apps.PluginManager.store.LocalPlugin');

        me.localPluginListing = Ext.create('Shopware.apps.PluginManager.view.list.LocalPluginListingPage', {
            store: me.localPluginStore,
            subApp: this.subApp,
            flex: 1
        });

        this.localPluginPage = Ext.create('Ext.container.Container', {
            layout: { type: 'hbox', align: 'stretch' },
            items: [ me.localPluginListing ],
            cardIndex: 1,
            hideContent: function() {
                me.localPluginListing.hide();
            },
            displayContent: function() {
                me.localPluginListing.show();
            }
        });

        return this.localPluginPage;
    },

    createPluginUpdatesPage: function() {
        var me = this;

        me.updateListing = Ext.create('Shopware.apps.PluginManager.view.list.UpdatePage');

        me.pluginUpdatesPage = Ext.create('Ext.container.Container', {
            layout: { type: 'hbox', align: 'stretch' },
            items: [ me.updateListing ],
            cardIndex: 2
        });

        return me.pluginUpdatesPage;
    },

    createListingPage: function() {
        this.listingPage = Ext.create('Shopware.apps.PluginManager.view.list.StoreListingPage', {
            cardIndex: 3
        });
        return this.listingPage;
    },

    createAccountPage: function() {
        this.accountPage = Ext.create('Ext.container.Container', {
            html: 'AccountPage',
            cardIndex: 4
        });
        return this.accountPage;
    },

    createLicencePage: function() {

        this.licenceStore = Ext.create('Shopware.apps.PluginManager.store.Licence');

        this.licencePage = Ext.create('Shopware.apps.PluginManager.view.list.LicencePage', {
            cardIndex: 5,
            store: this.licenceStore
        });

        return this.licencePage;
    },

    createPremiumPluginsPage: function() {
        this.createPremiumPluginPage = Ext.create('Shopware.apps.PluginManager.view.list.PremiumPluginsPage', {
            cardIndex: 6
        });

        return this.createPremiumPluginPage;
    }
});