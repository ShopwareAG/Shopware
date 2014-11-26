$(function () {
    StateManager.init([
        {
            state: 'xs',
            enter: 0,
            exit: 479
        },
        {
            state: 's',
            enter: 480,
            exit: 767
        },
        {
            state: 'm',
            enter: 768,
            exit: 1023
        },
        {
            state: 'l',
            enter: 1024,
            exit: 1259
        },
        {
            state: 'xl',
            enter: 1260,
            exit: 5160
        }
    ]);

    StateManager

        // OffCanvas menu
        .addPlugin('*[data-offcanvas="true"]', 'offcanvasMenu', ['xs', 's'])

        // Search field
        .addPlugin('*[data-search-dropdown="true"]', 'searchFieldDropDown', ['xs', 's', 'm', 'l'])

        // Scroll plugin
        .addPlugin('.btn--password, .btn--email', 'scroll', ['xs', 's'])

        // Collapse panel
        .addPlugin('.btn--password, .btn--email', 'collapsePanel', ['m', 'l', 'xl'])

        // Slide panel
        .addPlugin('*[data-slide-panel="true"]', 'slidePanel', ['xs', 's'])

        // Collapse panel
        .addPlugin('#new-customer-action', 'collapsePanel', ['xs', 's'])

        // Image slider
        .addPlugin('*[data-image-slider="true"]', 'imageSlider', { touchControls: true })
        .addPlugin('*[data-image-slider="true"]', 'imageSlider', { touchControls: false }, 'xl')

        // Image zoom
        .addPlugin('.product--image-zoom', 'imageZoom', 'xl')

        // Collapse panel
        .addPlugin('.blog-filter--trigger', 'collapsePanel', ['xs', 's', 'm', 'l'])

        // Collapse texr
        .addPlugin('.category--teaser .hero--text', 'collapseText', ['xs', 's'])

        // Default product slider
        .addPlugin('*[data-product-slider="true"]', 'productSlider', { itemsPerPage: 1 })
        .addPlugin('*[data-product-slider="true"]', 'productSlider', { itemsPerPage: 3 }, 'm')
        .addPlugin('*[data-product-slider="true"]', 'productSlider', { itemsPerPage: 4 }, 'l')
        .addPlugin('*[data-product-slider="true"]', 'productSlider', { itemsPerPage: 5 }, 'xl')

        // Product slider for premium items
        .addPlugin('.premium-product--content', 'productSlider', { itemsPerPage: 1 })
        .addPlugin('.premium-product--content', 'productSlider', { itemsPerPage: 2 }, 'm')
        .addPlugin('.premium-product--content', 'productSlider', { itemsPerPage: 3 }, 'l')
        .addPlugin('.premium-product--content', 'productSlider', { itemsPerPage: 4 }, 'xl')

        // Product slider for emotion items
        .addPlugin('.emotion--element .slider--article', 'productSlider', { itemsPerPage: 1 })
        .addPlugin('.emotion--element .slider--article', 'productSlider', { itemsPerPage: 3 }, ['m', 'l'])
        .addPlugin('.emotion--element .slider--article', 'productSlider', {
            itemsPerPage: 4,
            itemsPerSlide: 4
        }, 'xl')

        // Detail page tab menus
        .addPlugin('.product--rating-link, .link--publish-comment', 'scroll', {
            scrollTarget: '.tab-menu--product'
        })
        .addPlugin('.tab-menu--product', 'tabMenu', ['s', 'm', 'l', 'xl'])
        .addPlugin('.tab-menu--cross-selling', 'tabMenu', ['m', 'l', 'xl'])
        .addPlugin('.tab-menu--product .tab--container', 'offcanvasButton', {
            titleSelector: '.tab--title',
            previewSelector: '.tab--preview',
            contentSelector: '.tab--content'
        }, ['xs'])
        .addPlugin('.tab-menu--cross-selling .tab--header', 'collapsePanel', {
            'contentSiblingSelector': '.tab--content'
        }, ['xs', 's']);

    $('*[data-collapse-panel="true"]').collapsePanel();
    $('*[data-range-slider="true"]').rangeSlider();
    $('*[data-auto-submit="true"]').autoSubmit();
    $('*[data-drop-down-menu="true"]').dropdownMenu();
    $('*[data-newsletter="true"]').newsletter();
    $('*[data-pseudo-text="true"]').pseudoText();

    $('*[data-collapse-text="true"]').collapseText();
    $('*[data-filter-type]').filterComponent();
    $('*[data-listing-actions="true"]').listingActions();
    $('*[data-scroll="true"]').scroll();

    $('body').ajaxProductNavigation();
    $('*[data-emotion="true"]').emotion();
    $('input[data-form-polyfill="true"], button[data-form-polyfill="true"]').formPolyfill();

    $('select:not([data-no-fancy-select="true"])').selectboxReplacement();

    // Lightbox auto trigger
    $('*[data-lightbox="true"]').on('click.lightbox', function (event) {
        var $el = $(this),
            target = ($el.is('[data-lightbox-target]')) ? $el.attr('data-lightbox-target') : $el.attr('href');

        event.preventDefault();

        if (target.length) {
            $.lightbox.open(target);
        }
    });

    // Start up the placeholder polyfill, see ```jquery.ie-fixes.js```
    $('input, textarea').placeholder();

    // Deferred loading of the captcha
    $('div.captcha--placeholder[data-src]').captcha();

    $('*[data-modalbox="true"]').modalbox();

    $('.add-voucher--checkbox').on('change', function (event) {
        var method = (!$(this).is(':checked')) ? 'addClass' : 'removeClass';
        event.preventDefault();

        $('.add-voucher--panel')[method]('is--hidden');
    });

    $('.table--shipping-costs-trigger').on('click touchstart', function (event) {

        event.preventDefault();

        var $this = $(this),
            $next = $this.next(),
            method = ($next.hasClass('is--hidden')) ? 'removeClass' : 'addClass';

        $next[method]('is--hidden');
    });

    // Change the active tab to the customer reviews, if the url param sAction === rating is set.
    if ($('.is--ctl-detail').length) {
        var tabMenuProduct = $('.tab-menu--product').data('plugin_tabMenu'),
            tabMenuCrossSelling = $('.tab-menu--cross-selling').data('plugin_tabMenu');

        $('.product--rating-link, .link--publish-comment').on('click touchstart', function (event) {
            event.preventDefault();

            tabMenuProduct = $('.tab-menu--product').data('plugin_tabMenu');

            if (tabMenuProduct) {
                tabMenuProduct.changeTab(1);
            }
        });

        var param = decodeURI((RegExp('sAction' + '=' + '(.+?)(&|$)').exec(location.search) || [, null])[1]);
        if (param === 'rating' && tabMenuProduct) {
            tabMenuProduct.changeTab(1);
        }

        // Show the cross selling tabs if they have content when HTTP-Cache is active.
        if (tabMenuCrossSelling) {
            var activeContainerClass = tabMenuCrossSelling.opts.activeContainerClass;

            tabMenuCrossSelling.$contents.each(function (i, el) {
                var $el = $(el),
                    $con = $el.find('.tab--content');

                if ($con.html().length) {
                    $con.show();
                    $(tabMenuCrossSelling.$tabs.get(i)).css('display', 'inline-block');
                } else {
                    $el.addClass('no--content');

                    if ($el.hasClass(activeContainerClass)) {
                        $el.removeClass(activeContainerClass)
                        $(tabMenuCrossSelling.$contents.get(i + 1)).addClass(activeContainerClass);
                    }
                }
            });
        }
    }

    $('*[data-ajax-shipping-payment="true"]').shippingPayment();

    // Initialize the registration plugin
    $('div[data-register="true"]').register();

    $('*[data-live-search="true"]').liveSearch();

    $('*[data-last-seen-products="true"]').lastSeenProducts($.extend({}, lastSeenProductsConfig));

    $('*[data-add-article="true"]').addArticle();

    $('*[data-menu-scroller="true"]').menuScroller();

    $('*[data-collapse-cart="true"]').collapseCart();

    $('*[data-product-compare-add="true"]').productCompareAdd();

    $('*[data-product-compare-menu="true"]').productCompareMenu();

    $('*[data-infinite-scrolling="true"]').infiniteScrolling();

    // Ajax cart amount display
    function cartRefresh() {
        var ajaxCartRefresh = $.controller.ajax_cart_refresh,
            $cartAmount = $('.cart--amount'),
            $cartQuantity = $('.cart--quantity');

        if (!ajaxCartRefresh.length) {
            return;
        }

        $.getJSON(ajaxCartRefresh, function(cart) {

            if(!cart.amount || !cart.quantity) {
                return;
            }

            $cartAmount.html(cart.amount);
            $cartQuantity.html(cart.quantity).removeClass('is--hidden');

            if(cart.quantity == 0) {
                $cartQuantity.addClass('is--hidden');
            }
        });
    }

    $.subscribe('plugin/addArticle/onAddArticle', cartRefresh);
    $.subscribe('plugin/collapseCart/afterRemoveArticle', cartRefresh);

    StateManager.addPlugin('*[data-subcategory-nav="true"]', 'subCategoryNav', ['xs', 's']);
});
