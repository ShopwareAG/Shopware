;(function ($, window, Modernizr) {
    "use strict";

    /**
     * Off canvas menu plugin
     *
     * The plugin provides an lightweight way to use an off canvas pattern for all kind of content. The content
     * needs to be positioned off canvas using CSS3 `transform`. All the rest will be handled by the plugin.
     *
     * @example Simple usage
     * ```
     *     <a href="#" data-offcanvas="true">Menu</a>
     * ```
     *
     * @example Show the menu on the right side
     * ```
     *     <a href="#" data-offcanvas="true" data-direction="fromRight">Menu</a>
     * ```
     *
     * @ToDo: Implement swipe gesture control. The old swipe gesture was removed due to a scrolling bug.
     */
    var pluginName = 'offcanvasMenu',
        clickEvt = 'click',
        defaults = {

            /**
             * Selector for the content wrapper
             *
             * @property wrapSelector
             * @type {String}
             */
            'wrapSelector': '.page-wrap',

            /**
             * Selector of the off-canvas element
             *
             * @property offCanvasSelector
             * @type {String}
             */
            'offCanvasSelector': '.sidebar-main',

            /**
             * Selector for an additional button to close the menu
             *
             * @property closeButtonSelector
             * @type {String}
             */
            'closeButtonSelector': '.entry--close-off-canvas',

            /**
             * Animation direction, `fromLeft` (default) and `fromRight` are possible
             *
             * @property direction
             * @type {String}
             */
            'direction': 'fromLeft',

            /**
             * Container selector which should catch the swipe gesture
             *
             * @property swipeContainerSelector
             * @type {String}
             */
            'swipeContainerSelector': '.page-wrap',

            /**
             * Additional class for the off-canvas menu for necessary styling
             *
             * @property offCanvasElementCls
             * @type {String}
             */
            'offCanvasElementCls': 'off-canvas',

            /**
             * Class which should be added when the menu will be opened on the left side
             *
             * @property leftMenuCls
             * @type {String}
             */
            'leftMenuCls': 'is--left',

            /**
             * Class which should be added when the menu will be opened on the right side
             *
             * @property rightMenuCls
             * @type {String}
             */
            'rightMenuCls': 'is--right',

            /**
             * Class which indicates if the off-canvas menu is visible
             *
             * @property activeMenuCls
             * @type {String}
             */
            'activeMenuCls': 'is--active',

            /**
             * Flag whether to use transitions or not
             *
             * @property disableTransitions
             * @type {Boolean}
             */
            'disableTransitions': false,

            /**
             * Flag whether to show the offcanvas menu in full screen or not.
             *
             * @property fullscreen
             * @type {Boolean}
             */
            'fullscreen': false,

            /**
             * Class which sets the canvas to full screen
             *
             * @property fullscreenCls
             * @type {String}
             */
            'fullscreenCls': 'js--full-screen',

            /**
             * The mode in which the off canvas menu should be showing.
             *
             * 'local': The given 'offCanvasSelector' will be used as the off canvas menu.
             *
             * 'ajax': The given 'offCanvasSelector' will be used as an URL to
             *         load the content via AJAX.
             *
             * @type {String}
             */
            'mode': 'local',

            /**
             * The inactive class will be set on the body to disable scrolling
             * while the off canvas is opened.
             *
             * This will be also used to improve the performance of the mobile
             * scroll behaviour of the off canvas and off canvas sub navigation menu.
             *
             * @property inactiveClass
             * @type {String}
             */
            'inactiveClass': 'is--inactive',

            /**
             * This is the animation duration time in ms
             *
             * @porperty animationSpeed
             * @type {Number}
             */
            'animationSpeed': 400,

            /**
             * The animation easing for the menu open action
             *
             * @property easingIn
             * @type {String}
             */
            'easingIn': 'cubic-bezier(.16,.04,.14,1)',

            /**
             * The animation easing for the menu close action
             *
             * @property easingOut
             * @type {String}
             */
            'easingOut': 'cubic-bezier(.2,.76,.5,1)'
        };

    /**
     * Plugin constructor which merges the default settings with the user settings
     * and parses the `data`-attributes of the incoming `element`.
     *
     * @param {HTMLElement} element - Element which should be used in the plugin
     * @param {Object} userOpts - User settings for the plugin
     * @returns {Void}
     * @constructor
     */
    function Plugin(element, userOpts) {
        var me = this;

        me.$el = $(element);
        me.opts = $.extend({}, defaults, userOpts);

        // Get the settings which are defined by data attributes
        me.getDataConfig();

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

    /**
     * Loads config settings which are set via data attributes and
     * overrides the old setting with the data attribute of the
     * same name if defined.
     */
    Plugin.prototype.getDataConfig = function () {
        var me = this,
            attr;

        $.each(me.opts, function (key, value) {
            attr = me.$el.attr('data-' + key);
            if (attr !== undefined) {
                me.opts[key] = attr;
            }
        });
    };

    /**
     * Initializes the plugin, sets up event listeners and adds the necessary
     * classes to get the plugin up and running.
     *
     * @returns {Void}
     */
    Plugin.prototype.init = function () {
        var me = this,
            opts = me.opts;

        // Cache the necessary elements
        me.$pageWrap = $(opts.wrapSelector);
        me.$swipe = $(opts.swipeContainerSelector);
        me.$closeButton = $(opts.closeButtonSelector);
        me.$overlay = $(opts.wrapSelector + ':before');
        me.$body = $('body');
        me.fadeEffect = Modernizr.csstransitions && !opts.disableTransitions ? 'transition' : 'animate';

        me.opened = false;

        if (opts.mode === 'ajax') {
            me.$offCanvas = $('<div>', {
                'class': opts.offCanvasElementCls + ' ' + ((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
            }).appendTo(me.$body).css('display');
        } else {
            me.$offCanvas = $(opts.offCanvasSelector);
            me.$offCanvas.addClass(opts.offCanvasElementCls)
                .addClass((opts.direction === 'fromLeft') ? opts.leftMenuCls : opts.rightMenuCls)
                .removeAttr('style');
        }

        if (opts.fullscreen) {
            me.$offCanvas.addClass(opts.fullscreenCls);
        }

        me.offCanvasWidth = me.$offCanvas.width();

        if (!opts.fullscreen) {
            me.$offCanvas.css((opts.direction === 'fromLeft' ? 'left' : 'right'), me.offCanvasWidth * -1);
        }
        me.$offCanvas.addClass(opts.activeMenuCls);

        me.registerEventListeners();
    };

    /**
     * Registers all necessary event listeners for the plugin to proper operate. The
     * method contains the event callback methods as well due to the small amount of
     * code.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.registerEventListeners = function () {
        var me = this,
            opts = me.opts;

        // Button click
        me.$el.on(clickEvt + '.' + pluginName, function (event) {
            event.preventDefault();

            me.openMenu();
        });

        // Allow the user to close the off canvas menu
        me.$body.delegate(opts.closeButtonSelector, clickEvt + '.' + pluginName, function (event) {
            event.preventDefault();
            me.closeMenu();
        });

        return true;
    };

    /**
     * Opens the off-canvas menu based on the direction.
     * Also closes all other off-canvas menus.
     */
    Plugin.prototype.openMenu = function () {
        var me = this,
            opts = me.opts,
            fromLeft = opts.direction === 'fromLeft',
            plugin;

        if (me.opened) {
            return;
        }
        me.opened = true;

        // Close all other opened off-canvas menus
        $('.' + opts.offCanvasElementCls).each(function (i, el) {
            if (!(plugin = $(el).data('plugin_' + pluginName))) {
                return true;
            }

            plugin.closeMenu();
        });

        // Disable scrolling on body
        $('html').addClass(opts.inactiveClass);

        $.overlay.open({
            closeOnClick: true,
            onClick: $.proxy(me.closeMenu, me)
        });

        var css = {};
        css[fromLeft ? 'left' : 'right'] = 0;
        me.$offCanvas[me.fadeEffect](css, me.opts.animationSpeed, me.opts.easingIn);

        var left = (opts.fullscreen) ? (fromLeft ? '100%' : '-100%') : me.offCanvasWidth * (fromLeft ? 1 : -1);
        me.$pageWrap[me.fadeEffect]({'left': left}, me.opts.animationSpeed, me.opts.easingIn);

        if (opts.mode === 'ajax') {
            $.ajax({
                url: opts.offCanvasSelector,
                success: function (result) {
                    me.$offCanvas.html(result);
                }
            })
        }
    };

    /**
     * Closes the menu and slide the content wrapper
     * back to the normal position.
     */
    Plugin.prototype.closeMenu = function () {
        var me = this,
            opts = me.opts,
            fromLeft = opts.direction === 'fromLeft';

        if (!me.opened) {
            return;
        }
        me.opened = false;

        $.overlay.close();

        // Disable scrolling on body
        $('html').removeClass(opts.inactiveClass);

        var css = {};
        css[fromLeft ? 'left' : 'right'] = opts.fullscreen ? '-100%' : me.offCanvasWidth * -1;

        me.$offCanvas[me.fadeEffect](css, me.opts.animationSpeed, me.opts.easingOut);
        me.$pageWrap[me.fadeEffect]({'left': 0}, me.opts.animationSpeed, me.opts.easingOut);

        me.$pageWrap.off('scroll.' + pluginName);
        $.publish('plugin/offCanvasMenu/closeMenu');
    };

    Plugin.prototype.isOpened = function () {
        return this.opened;
    };

    /**
     * Destroyes the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function () {
        var me = this,
            opts = me.opts;


        // check if overlay exists
        if (me.opened) {
            $.overlay.close();
        }

        me.$offCanvas.removeClass(opts.offCanvasElementCls)
            .removeClass(opts.activeMenuCls)
            .removeAttr('style');

        me.$pageWrap.off(clickEvt + '.' + pluginName)
            .removeAttr('style');

        me.$closeButton.off(clickEvt + '.' + pluginName);

        me.$el.off(clickEvt + '.' + pluginName).removeData('plugin_' + pluginName);

        me.$body.undelegate('.' + pluginName);

        return true;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin(this, options));
            }
        });
    };
})(jQuery, window, Modernizr);