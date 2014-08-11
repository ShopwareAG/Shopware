;jQuery(function ($) {
    'use strict';

    /**
     * Shopware Add Article Plugin
     *
     * @example Button Element (can be pretty much every element)
     *
     * HTML:
     *
     * <button data-add-article="true" data-addArticleUrl="{url controller='checkout' action='addArticle' sAdd=$sArticle.ordernumber}">
     *     Jetzt bestellen
     * </button>
     *
     * @example Form
     *
     * HTML:
     *
     * <form data-add-article="true" data-eventName="submit">
     *     <input type="hidden" name="sAdd" value="SW10165"> // Contains the ordernumber of the article
     *     <input type="hidden" name="sQuantity" value"10"> // Optional (Default: 1). Contains the amount of articles to be added (Can also be an select box)
     *
     *     <button>In den Warenkorb</button>
     * </form>
     *
     *
     * You can either add an article by giving a specific url to the property "addArticleUrl" (First example)
     * or you can add hidden input fields to the element with name "sAdd" and "sQuantity" (Second example).
     *
     * JS:
     *
     * $('*[data-add-article="true"]').addArticle();
     *
     */
    $.plugin('addArticle', {

        defaults: {
            /**
             * Event name that the plugin listens to.
             *
             * @type {String}
             */
            'eventName': 'click',

            /**
             * The ajax url that the request should be send to.
             *
             * Default: myShop.com/(Controller:)checkout/(Action:)addArticle
             *
             * @type {String}
             */
            'addArticleUrl': jQuery.controller['ajax_add_article'],

            /**
             * Object that maps different device types with their per-page amount.
             *
             * @type {Object}
             */
            'sliderPerPage': {
                'desktop': 3,
                'tabletLandscape': 3,
                'tablet': 2,
                'smartphone': 1
            },

            /**
             * Default value that is used for the per-page amount when the current device is not mapped.
             * An extra option because the mapping table can be accidentally overwritten.
             *
             * @type {Number}
             */
            'sliderPerPageDefault': 3
        },

        /**
         * Default plugin initialisation function.
         * Registers an event listener on the change event.
         * When it's triggered, the parent form will be submitted.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;

            // Applies HTML data attributes to the current options
            me.applyDataAttributes();

            // Will be automatically removed when destroy() is called.
            me._on(me.$el, me.opts.eventName, $.proxy(me.sendSerializedForm, me));
        },

        /**
         * Gets called when the element was triggered by the given event name.
         * Serializes the plugin element {@link $el} and sends it to the given url.
         * When the ajax request was successful, the {@link initModalSlider} will be called.
         *
         * @public
         * @event sendSerializedForm
         * @param {jQuery.Event} event
         */
        sendSerializedForm: function (event) {
            event.preventDefault();

            var me = this,
                $el = me.$el,
                ajaxData = $el.serialize();

            $.loadingIndicator.open({
                'closeOverlay': false
            });

            $.ajax({
                'data': ajaxData,
                'dataType': 'jsonp',
                'url': me.opts.addArticleUrl,
                'success': function (result) {
                    $.loadingIndicator.close(function () {
                        $.modal.open(result, {
                            width: 750,
                            sizing: 'content',
                            onClose: me.onCloseModal
                        });

                        picturefill();

                        me.initModalSlider();
                    });
                }
            });
        },

        /**
         * Gets called when the modal box is closing.
         * Destroys the product slider when its available.
         *
         * @public
         * @event onCloseModal
         */
        onCloseModal: function () {
            var $sliderEl = $('.js--modal').find('.product-slider'),
                slider;

            if (!$sliderEl || !$sliderEl.length) {
                return;
            }

            slider = $sliderEl.data('plugin_productSlider');

            if (slider) {
                slider.destroy();
            }
        },

        /**
         * When the modal content contains a product slider, it will be initialized.
         *
         * @public
         * @method initModalSlider
         */
        initModalSlider: function () {
            var me = this,
                perPageList = me.opts.sliderPerPage,
                perPage = me.opts.sliderPerPageDefault,
                $sliderEl = $('.js--modal').find('.product-slider'),
                slider;

            if (!$sliderEl || !$sliderEl.length) {
                return;
            }

            StateManager.registerListener({
                'type': '*',
                'enter': function (event) {
                    slider = $sliderEl.data('plugin_productSlider');
                    perPage = perPageList[event.entering] || perPage;

                    if (!slider) {
                        $sliderEl.productSlider({
                            'perPage': perPage,
                            'perSlide': 1,
                            'touchControl': true
                        });
                        return;
                    }

                    slider.opts.perPage = perPage;
                    slider.setSizes();
                }
            });
        }
    });
});