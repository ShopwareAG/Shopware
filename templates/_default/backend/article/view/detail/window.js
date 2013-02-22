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
 * @package    Article
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail window.
 * The detail window contains the definition of the base form and the element orientation within the form element.
 * The window passes the article record and the different stores to the form elements.
 *
 * shopware AG (c) 2012. All rights reserved.
 *
 * @link http://www.shopware.de/
 * @date 2012-02-20
 * @license http://www.shopware.de/license
 * @package Article
 * @subpackage Detail
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/window"}
Ext.override(Ext.container.DockingContainer, {
    dockedItems: []
});
Ext.define('Shopware.apps.Article.view.detail.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-detail-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-detail-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:'80%',
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:false,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-article-detail-window',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        titleNew: '{s name=window_title}Article details: new article{/s}',
        titleEdit:'{s name=window_title_edit}Article details : [0]{/s}',
        formTab:'{s name=base_data}Base data{/s}',
        categoryTab:'{s name=category_data}Categories{/s}',
        imageTab:'{s name=image_tab}Images{/s}',
        propertyTab:'{s name=property_tab}Properties{/s}',
        variantTab:'{s name=variant_tab}Variants{/s}',
        configuratorTab:'{s name=configurator_tab}Configurator{/s}',
        linkTab:'{s name=link_tab}Links{/s}',
        downloadTab:'{s name=download_tab}Downloads{/s}',
        crossSellingTab:'{s name=cross_selling_tab}Cross-Selling{/s}',
        esdTab:'{s name=esd_tab}ESD{/s}',
        statisticTab:'{s name=statistic_tab}Statistics{/s}',
        save:'{s name=save_button}Save article{/s}',
        cancel:'{s name=cancel_button}Cancel{/s}',
        categoryNotice:'{s name=category/category_notice}Please select the category to which the product <strong>[0]</strong> is supposed to be assigned.{/s}',
        categoryNoticeTitle:'{s name=category/category_assignment}Assign categories{/s}',
        descriptions: {
            title:'{s name=detail/description/title}Description{/s}',
            description: {
                label: '{s name=detail/description/description_label}Short description{/s}',
                support: '{s name=detail/description/description_support}Short description for search engines, exports and overviews{/s}'
            },
            keywords: {
                label: '{s name=detail/description/keywords_label}Keywords{/s}',
                support: '{s name=detail/description/keywords_support}Meta keywords for search engines and intelligent search{/s}'
            }
        },
        additional: {
            title:'{s name=detail/additional_fields/title}Additional fields{/s}',
            comment:'{s name=detail/additional_fields/comment}Comment{/s}',
            attribute1:'{s name=detail/additional_fields/free_text_1}Free text 1{/s}',
            attribute2:'{s name=detail/additional_fields/free_text_2}Free text 2{/s}'
        },
        basePrice: {
            title:'{s name=detail/base_price/title}Base price calculation{/s}',
            content:'{s name=detail/base_price/content}Content{/s}',
            unit:'{s name=detail/base_price/unit}Unit{/s}',
            basicUnit:'{s name=detail/base_price/basic_unit}Basic unit{/s}',
            packingUnit:'{s name=detail/base_price/packing_unit}Packing unit{/s}',
            empty:'{s name=empty}Please select...{/s}'
        },
        variant: {
            listing: '{s name=variant/listing_tab}Listing{/s}',
            configurator: '{s name=variant/configurator_tab}Configure variants{/s}',
            settings: '{s name=variant/settings_tab}Settings{/s}',
            button: {
                listing: '{s name=variant/save_button_listing}Save variants{/s}',
                settings: '{s name=variant/save_button_settings}Save settings{/s}',
                configurator: '{s name=variant/save_button_configurator}Generate variants{/s}'
            }
        },
        esd: {
            button: {
                save: '{s name=esd/save_button}Save ESD{/s}',
                back: '{s name=esd/back_button}Back to overview{/s}'
            }
        }
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.bbar = me.createToolbar();
        me.registerEvents();
        me.callParent(arguments);
        me.changeTitle();

        me.on('storesLoaded', me.onStoresLoaded, me)
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the save button.
             *
             * @event
             * @param [object] The detail window
             * @param [Ext.data.Model] The article record.
             */
            'saveArticle',
            /**
             * Event will be fired when the user clicks the cancel button.
             *
             * @event
             * @param [object] The detail window
             */
            'cancel',

            'storesLoaded'
        );
    },

    /**
     * Creates the main tab panel which displays the different tabs for the article sections.
     * To extend the tab panel this function can be override.
     *
     * @return Ext.tab.Panel
     */
    createMainTabPanel: function() {
        var me = this;

        me.categoryTab = Ext.create('Ext.container.Container', {
            title: me.snippets.categoryTab,
            disabled: true,
            layout: 'border',
            name: 'category'
        });

        me.imageTab = Ext.create('Ext.container.Container', {
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            title: me.snippets.imageTab,
            name: 'image',
            disabled: true,
            cls: Ext.baseCSSPrefix + 'image-tab-container'
        });

        me.variantTab = Ext.create('Ext.container.Container', {
            title: me.snippets.variantTab,
            disabled: true,
            layout: 'fit',
            name: 'variant-tab',
            disabled: true
        });

        me.esdTab = Ext.create('Ext.container.Container', {
            title: me.snippets.esdTab,
            disabled: true,
            name: 'esd-tab',
            layout: 'card',
            deferredRender: true,
            disabled: true
        });

        me.statisticTab = Ext.create('Ext.container.Container', {
            title: me.snippets.statisticTab,
            disabled: true,
            layout: {
                align: 'stretch',
                padding: 10,
                type: 'vbox'
            }
        });

        return me.mainTab = Ext.create('Ext.tab.Panel', {
            name: 'main-tab-panel',
            items: [
                me.createBaseTab(),
                me.categoryTab,
                me.imageTab,
                me.variantTab,
                me.esdTab,
                me.statisticTab
            ]
        });
    },


    /**
     * Changes the title of the article detail window header and the footer button.
     */
    changeTitle: function() {
        var me = this, title, footerButton;

        title = me.snippets.titleNew;
        if (me.article && me.article.get('id')>0) {
            title = Ext.String.format(me.snippets.titleEdit, me.article.get('name'));
        }
        me.setTitle(title);

        // Change the title of the footer button
        if(me._toolbarBtn) {
            footerButton = me._toolbarBtn;
            footerButton.setText(title);
        }
    },

    /**
     * Creates the tab panel for the base data. Contains a form panel which allows the user
     * to edit the selected record
     * @return Ext.container.Container
     */
    createBaseTab: function() {
        var me = this;

        me.detailForm = Ext.create('Ext.form.Panel', {
            region:'center',
            name: 'detail-form',
            bodyPadding: 10,
            autoScroll: true,
            defaults: {
                labelWidth: 155
            },
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'article',
                translationMerge: false,
                translationKey: null
            }],
            items: [
                me.createBaseFieldSet(),
                me.createPriceFieldSet(),
                me.createDescriptionFieldSet(),
                me.createBasePriceFieldSet(),
                me.createSettingsFieldSet(),
                me.createPropertiesFieldSet()
            ]
        });

        return me.detailContainer = Ext.create('Ext.container.Container', {
            layout: 'border',
            name: 'main',
            title: me.snippets.formTab,
            items: [
                me.detailForm,
                {
                    xtype: 'article-sidebar',
                    region: 'east',
                    article: me.article,
                    shopStore: me.shopStore
                }
            ]
        });
    },

    /**
     * Creates the field set for the article property configuration.
     */
    createPropertiesFieldSet: function() {
        return Ext.create('Shopware.apps.Article.view.detail.Properties');
    },

    /**
     * Creates the field set for the article setting configuration.
     */
    createSettingsFieldSet: function() {
        return Ext.create('Shopware.apps.Article.view.detail.Settings');
    },

    /**
     * Creates the base field set for the detail form.
     * @return Shopware.apps.Article.view.detail.Base
     */
    createBaseFieldSet: function() {
        return Ext.create('Shopware.apps.Article.view.detail.Base');
    },

    /**
     * Creates the field set for the article price configuration.
     */
    createPriceFieldSet: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.detail.Prices');
    },


    /**
     * Creates the field set for the article base price calculation.
     * @return Ext.form.FieldSet
     */
    createBasePriceFieldSet: function() {
        var me = this;

        me.unitComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'mainDetail[unitId]',
            queryMode: 'local',
            fieldLabel: me.snippets.basePrice.unit,
            emptyText: me.snippets.basePrice.empty,
            displayField: 'name',
            editable:false,
            valueField: 'id',
            labelWidth: 155,
            anchor: '100%',
            xtype: 'textfield'
        });

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'article-base-price-field-set',
            defaults: {
                labelWidth: 155,
                anchor: '100%',
                xtype: 'textfield'
            },
            title: me.snippets.basePrice.title,
            items: [
                {
                    xtype: 'combobox',

                }, {
                    xtype: 'numberfield',
                    submitLocaleSeparator: false,
                    decimalPrecision: 4,
                    name: 'mainDetail[purchaseUnit]',
                    fieldLabel: me.snippets.basePrice.content
                }, {
                    xtype: 'numberfield',
                    submitLocaleSeparator: false,
                    name: 'mainDetail[referenceUnit]',
                    decimalPrecision: 3,
                    fieldLabel: me.snippets.basePrice.basicUnit
                }, {
                    name: 'mainDetail[packUnit]',
                    translationName: 'packUnit',
                    translatable: true,
                    fieldLabel: me.snippets.basePrice.packingUnit
                }
            ]
        });
    },

    /**
     * Creates the description field set for the main form panel.
     * Contains the keywords, short and long description.
     * @return Ext.form.FieldSet
     */
    createDescriptionFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'article-description-field-set',
            defaults: {
                labelWidth: 155,
                anchor: '100%',
                translatable: true,
                xtype: 'textarea'
            },
            title: me.snippets.descriptions.title,
            items: [
                {
                    xtype: 'tinymce',
                    name: 'descriptionLong',
                    margin: '0 0 15',
                    cls: Ext.baseCSSPrefix + 'article-description-long',
                    height: 100
                }, {
                    name: 'description',
                    height: 100,
                    fieldLabel: me.snippets.descriptions.description.label,
                    supportText: me.snippets.descriptions.description.support
                }, {
                    name: 'keywords',
                    height: 100,
                    fieldLabel: me.snippets.descriptions.keywords.label,
                    supportText: me.snippets.descriptions.keywords.support
                }
            ]
        });
    },

    /**
     * Creates the tab panel tab for the category selection.
     * @return Array
     */
    createCategoryTab: function() {
        var me = this, rightContainer;

        var notice = Ext.String.format(me.snippets.categoryNotice, me.article.get('name'));

        me.categoryTree = Ext.create('Shopware.apps.Article.view.category.Tree', {
            store: me.categoryTreeStore,
            region: 'west'
        });

        me.categoryDropZone = Ext.create('Shopware.apps.Article.view.category.DropZone', {
            flex:1,
            autoScroll:true,
            margin: 10
        });

        me.categoryNotice = Ext.create('Ext.panel.Panel', {
            title: me.snippets.categoryNoticeTitle,
            bodyPadding: 10,
            height: 65,
            margin: 10,
            bodyStyle: 'background: #fff',
            items: [{
                xtype: 'container',
                cls: Ext.baseCSSPrefix + 'global-notice-text',
                html: notice
            }]
        });

        me.categoryList = Ext.create('Shopware.apps.Article.view.category.List', {
            article: me.article,
            flex: 1,
            autoScroll:true,
            margin: 10
        });

        rightContainer = Ext.create('Ext.container.Container', {
            region: 'center',
            bodyPadding: 10,
            name: 'category-tab',
            plain: true,
            autoScroll:true,
            layout: {
                align: 'stretch',
                type: 'vbox'
            },
            items: [
                me.categoryNotice, me.categoryDropZone, me.categoryList
            ]
        });

        return [ me.categoryTree, rightContainer ];
    },

    /**
     * Creates the image tab panel.
     * @return Array
     */
    createImageTab: function() {
        var me = this, leftContainer;

        me.imageList = Ext.create('Shopware.apps.Article.view.image.List', {
            article: me.article,
            margin: '0 10 10',
            flex: 1
        });
        me.imageUpload = Ext.create('Shopware.apps.Article.view.image.Upload', {
            article: me.article,
            margin: 10,
            flex: 1,
            autoScroll:true
        });
        me.imageInfo = Ext.create('Shopware.apps.Article.view.image.Info', {
            margin: 10,
            width: 390,
            configuratorGroupStore: me.configuratorGroupStore
        });

        leftContainer = Ext.create('Ext.container.Container', {
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                me.imageUpload,
                me.imageList
            ]
        });

        return [ leftContainer, me.imageInfo ];
    },

    /**
     * Creates the window toolbar which docked bottom and contains the cancel and save button.
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        //create the save button which fire the save event, the save event is handled in the detail controller.
        me.saveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            name: 'save-article-button',
            text: me.snippets.save,
            handler: function() {
                me.fireEvent('saveArticle', me, me.article);
            }
        });

        //creates the cancel button which fire the cancel event, the cancel event is handled in the detail controller.
        me.cancelButton = Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            name: 'cancel-button',
            cls: 'secondary',
            handler: function() {
                me.fireEvent('cancel', me, me.article);
            }
        });

        //creates the global save button for the configurator
        me.configuratorSaveButton = Ext.create('Ext.button.Button', {
            text: me.snippets.variant.button.configurator,
            cls: 'primary',
            hidden: true,
            name: 'configurator-save-button',
            handler: function() {
                me.variantListing.fireEvent('createVariants', me.article);
            }
        });

        //creates the global save button for the esd
        me.esdSaveButton = Ext.create('Ext.button.Button', {
            text: me.snippets.esd.button.save,
            cls: 'primary',
            hidden: true,
            name: 'esd-save-button',
            handler: function() {
                me.esdListing.fireEvent('saveEsd');
            }
        });

        //creates the global save button for the esd
        me.esdBackButton = Ext.create('Ext.button.Button', {
            text: me.snippets.esd.button.back,
            cls: 'secondary',
            hidden: true,
            name: 'esd-back-button',
            handler: function() {
                me.esdListing.fireEvent('backToList');
            }
        });

        //creates the toolbar with a spaces, the cancel and save button.
        return Ext.create('Ext.toolbar.Toolbar', {
            items: [
                { xtype: 'tbfill' },
                me.cancelButton,
				/*{if {acl_is_allowed privilege=save}}*/
                me.saveButton,
				/*{/if}*/
                me.configuratorSaveButton,
                me.esdBackButton,
                me.esdSaveButton
            ]
        });

    },

    /**
     * Creates the variant tab panel which contains the configuration elements for the article variants and configurator.
     * @return Ext.container.Container
     */
    createVariantTab: function() {
        var me = this, listing, configurator, settings, toolbar;
        listing = me.createVariantListingTab();
        configurator = me.createVariantConfiguratorTab();

        me.configuratorTab = Ext.create('Ext.tab.Panel', {
            name: 'configurator-tab',
            items: [
                listing,
                configurator
            ],
            margin: 10
        });

        return me.configuratorTab;
    },


    /**
     * Creates the listing component for the variant tab.
     * @return Ext.container.Container
     */
    createVariantListingTab: function() {
        var me = this;

        me.variantStore = Ext.create('Shopware.apps.Article.store.Variant');

        if (me.article) {
            me.variantStore.getProxy().extraParams.articleId = me.article.get('id');
        }

        me.variantListing = Ext.create('Shopware.apps.Article.view.variant.List', {
            border: false,
            //article: me.article,
            store: me.variantStore,
            //unitStore: me.unitStore,
            //configuratorGroupStore: me.configuratorGroupStore,
            //customerGroupStore: me.customerGroupStore
        });

        return Ext.create('Ext.container.Container', {
            items: [ me.variantListing ],
            layout: 'fit',
            name: 'listing',
            title: me.snippets.variant.listing
        });
    },

    /**
     * Creates the variant configurator for the variant tab panel.
     * @return Ext.container.Container.
     */
    createVariantConfiguratorTab: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.variant.Configurator', {
            title: me.snippets.variant.configurator,
            article: me.article,
            dependencyStore: me.dependencyStore,
            priceSurchargeStore: me.priceSurchargeStore,
            name: 'configurator',
            configuratorGroupStore: me.configuratorGroupStore,
            articleConfiguratorSet: me.articleConfiguratorSet
        });
    },

    /**
     * Creates the esd tab which contains the configuration for the esd options.
     * @return Ext.container.Container
     */
    createEsdTab: function() {
        var me = this;

        var esdStore      = Ext.create('Shopware.apps.Article.store.Esd');
        var filteredStore = Ext.create('Shopware.apps.Article.store.Esd');
        esdStore.addListener('beforeload', function(store, records) {
            filteredStore.load({
                params: {
                    filterCandidates: true
                }
            });
        });
        esdStore.getProxy().extraParams.articleId = me.article.get('id');
        filteredStore.getProxy().extraParams.articleId = me.article.get('id');

        me.esdListing = Ext.create('Shopware.apps.Article.view.esd.List', {
            esdStore: esdStore,
            filteredStore: filteredStore,
            article: me.article
        });

        return me.esdListing;
    },

    /**
     * Creates the statistic tab which contains a graph for the article sales.
     * @return Array
     */
    createStatisticTab: function() {
        var me = this;

        var statisticStore = Ext.create('Shopware.apps.Article.store.Statistic');
        var chartStore = Ext.create('Shopware.apps.Article.store.Statistic');

        statisticStore.getProxy().extraParams.articleId = me.article.get('id');
        chartStore.getProxy().extraParams.articleId = me.article.get('id');
        chartStore.getProxy().extraParams.chart = true;

        var list = Ext.create('Shopware.apps.Article.view.statistics.List', {
            flex: 1,
            article: me.article,
            store: statisticStore
        });

        var chart = Ext.create('Shopware.apps.Article.view.statistics.Chart', {
            height: 250,
            flex: 1,
            article: me.article,
            store: chartStore
        });

        return [ chart, list ];
    },

    onStoresLoaded: function(article, stores) {
        var me = this;

        me.detailForm.add(me.attributeFieldSet);

        me.unitComboBox.bindStore(stores['unit']);

        window.setTimeout(function() {
            me.detailForm.loadRecord(me.article);
        }, 10);

        me.categoryTab.add(me.createCategoryTab());
        me.categoryTab.setDisabled(false);

        me.imageTab.add(me.createImageTab());
        me.imageTab.setDisabled(false);

        me.variantTab.add(me.createVariantTab());
        me.variantTab.setDisabled((me.article.get('id') === null || me.article.get('isConfigurator') === false || me.article.get('configuratorSetId') === null))

        me.esdTab.add(me.createEsdTab());
        me.esdTab.setDisabled((me.article.get('id') === null));

        me.statisticTab.add(me.createStatisticTab());
        me.statisticTab.setDisabled(me.article.get('id') === null);
    }

});
//{/block}
