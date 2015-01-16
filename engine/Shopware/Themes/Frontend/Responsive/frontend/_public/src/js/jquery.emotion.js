;(function($, window, document, undefined) {
    "use strict";

    /**
     * Local private variables.
     */
    var $window = $(window),
        $body = $('body'),
        $document = $(document);


    /**
     * Emotion Loader Plugin
     *
     * This plugin is called on emotion wrappers to load emotion worlds
     * for the specific device types dynamically via ajax.
     */
    $.plugin('emotionLoader', {

        defaults: {

            /**
             * The url of the controller to load the emotion world.
             *
             * @property controllerUrl
             * @type {string}
             */
            controllerUrl: null,

            /**
             * The names of the devices for which the emotion world is available.
             *
             * @property availableDevices
             * @type {string}
             */
            availableDevices: null,

            /**
             * Show or hide the listing on category pages.
             *
             * @property showListing
             * @type {boolean}
             */
            showListing: false,

            /**
             * Configuration object to map device types to IDs.
             *
             * @property deviceTypes
             * @type {object}
             */
            deviceTypes: {
                'xl': '0',
                'l' : '1',
                'm' : '2',
                's' : '3',
                'xs': '4'
            },

            /**
             * The DOM selector of the fallback content
             * if no emotion world is available.
             *
             * @property fallbackContentSelector
             * @type {string}
             */
            fallbackContentSelector: '.listing--wrapper',

            /**
             * The markup for the loading indicator.
             *
             * @property loadingIndicator
             * @type {string}
             */
            loadingIndicator: '<i class="icon--loading-indicator"></i>'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            if (me.opts.controllerUrl === null ||
                me.opts.availableDevices === null) {
                me.$el.remove();
                return;
            }

            me.$emotion = false;

            me.hasSiblings = !!me.$el.siblings().length;
            me.availableDevices = me.opts.availableDevices.split(',');

            me.$fallbackContent = $(me.opts.fallbackContentSelector);

            if (!me.opts.showListing) {
                me.hideFallbackContent();
            }

            me.loadEmotion();
            me.registerEvents();
        },

        /**
         * Registers all necessary event listner.
         */
        registerEvents: function() {
            var me = this;

            StateManager.on('resize', $.proxy(me.onDeviceChange, me));
        },

        /**
         * Called on resize event of the StateManager.
         */
        onDeviceChange: function() {
            this.loadEmotion();
        },

        /**
         * Loads an emotion world for a given device state.
         * If the emotion world for the state was already loaded
         * it will just be initialized again from local save.
         *
         * @param controllerUrl
         * @param deviceState
         */
        loadEmotion: function(controllerUrl, deviceState) {
            var me = this,
                devices = me.availableDevices,
                types = me.opts.deviceTypes,
                url = controllerUrl || me.opts.controllerUrl,
                state = deviceState || StateManager.getCurrentState();

            /**
             * If the emotion world is not defined for the current device,
             * hide the wrapper element and show the default content.
             */
            if (devices.indexOf(types[state]) === -1) {
                me.hideEmotion();
                if (!me.hasSiblings) me.showFallbackContent();
                return;
            }

            /**
             * If the plugin is not configured correctly show the default content.
             */
            if (!devices.length || !state.length || !url.length) {
                me.hideEmotion();
                me.showFallbackContent();
                return;
            }

            /**
             * If the emotion world was already loaded show it.
             */
            if (me.$emotion.length) {
                me.showEmotion();
                return;
            }

            /**
             * Show the loading indicator and load the emotion world.
             */
            me.$el.html(me.opts.loadingIndicator);
            me.showEmotion();

            $.ajax({
                url: url,
                method: 'GET',
                success: function (response) {

                    if (!response.length) {
                        me.hideEmotion();
                        me.showFallbackContent();
                        return;
                    }

                    me.initEmotion(response);
                }
            });
        },

        /**
         * Removes the content of the container by
         * the new emotion world markup and initializes it.
         *
         * @param html
         */
        initEmotion: function(html) {
            var me = this;

            me.$el.html(html);
            me.$emotion = me.$el.find('*[data-emotion="true"]');

            if (!me.$emotion.length) {
                me.showFallbackContent();
                return;
            }

            me.$emotion.emotion();
        },

        /**
         * Shows the emotion world.
         */
        showEmotion: function() {
            var me = this;

            me.$el.css('display', 'block');
        },

        /**
         * Hides the emotion world.
         */
        hideEmotion: function() {
            var me = this;

            me.$el.css('display', 'none');
        },

        /**
         * Shows the fallback content.
         */
        showFallbackContent: function() {
            var me = this;

            me.$fallbackContent.css('display', 'block');

            StateManager.updatePlugin('*[data-infinite-scrolling="true"]', 'infiniteScrolling');
        },

        /**
         * Hides the fallback content.
         */
        hideFallbackContent: function() {
            var me = this;

            me.$fallbackContent.css('display', 'none');

            StateManager.updatePlugin('*[data-infinite-scrolling="true"]', 'infiniteScrolling');
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me._destroy();
        }
    });


    /**
     * Emotion plugin
     *
     * This plugin is called on each single emotion world
     * for handling the grid sizing and all elements in it.
     */
    $.plugin('emotion', {

        defaults: {

            /**
             * The grid mode of the emotion grid.
             *
             * @property gridMode ( resize | masonry )
             * @type {string}
             */
            gridMode: 'resize',

            /**
             * The base width in px for dynamic measurement.
             * Used for resize mode to have a base orientation for scaling.
             * Number is based on the fixed container width in desktop mode.
             *
             * @property baseWidth
             * @type {number}
             */
            baseWidth: 1160,

            /**
             * Turn fullscreen mode on and off.#
             *
             * @property fullScreen
             * @type {boolean}
             */
            fullscreen: false,

            /**
             * The number of columns in the grid.
             *
             * @property columns
             * @type {number}
             */
            columns: 4,

            /**
             * The height of one grid cell in px.
             *
             * @property cellHeight
             * @type {number}
             */
            cellHeight: 185,

            /**
             * The space in px between the elements in the grid.
             *
             * @property cellSpacing
             * @type {number}
             */
            cellSpacing: 10,

            /**
             * The duration for the masonry animation.
             *
             * @property transitionDuration
             * @type {string}
             */
            transitionDuration: '0.25s',

            /**
             * The DOM selector for the emotion elements.
             *
             * @property elementSelector
             * @type {string}
             */
            elementSelector: '.emotion--element',

            /**
             * The DOM selector for the sizer element.
             *
             * @property elementSelector
             * @type {string}
             */
            gridSizerSelector: '.emotion--sizer',

            /**
             * The DOM selector for banner elements.
             *
             * @property bannerElSelector
             * @type {string}
             */
            bannerElSelector: '.emotion--banner',

            /**
             * The DOM selector for video elements.
             *
             * @property videoElSelector
             * @type {string}
             */
            videoElSelector: '.emotion--video'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.bufferedCall = false;

            me.$contentMain = $('.content-main');
            me.$container = me.$el.parents('.content--emotions');
            me.$wrapper = me.$el.parents('.emotion--wrapper');

            me.$elements = me.$el.find(me.opts.elementSelector);
            me.$gridSizer = me.$el.find(me.opts.gridSizerSelector);

            me.$bannerElements = me.$elements.find(me.opts.bannerElSelector);
            me.$videoElements = me.$elements.find(me.opts.videoElSelector);

            if (me.opts.fullscreen) {
                me.initFullscreen();
            }

            if (me.opts.gridMode === 'masonry') {
                me.initMasonryGrid();
            }

            if (me.opts.gridMode === 'resize') {
                me.initScaleGrid();
            }

            me.initElements();
            me.registerEvents();

            $.publish('plugin/emotion/init', me);
        },

        /**
         * Initializes special elements and their needed plugins.
         */
        initElements: function() {
            var me = this;

            $.each(me.$bannerElements, function(index, item) {
                $(item).emotionBanner();
            });

            $.each(me.$videoElements, function(index, item) {
                $(item).emotionVideo();
            });

            me.$el.find('*[data-product-slider="true"]').productSlider();
            me.$el.find('*[data-image-slider="true"]').imageSlider();

            window.picturefill();

            $.publish('plugin/emotion/initElements', me);
        },

        /**
         * Initializes the fullscreen mode.
         */
        initFullscreen: function() {
            var me = this;

            $body.addClass('is--no-sidebar');
            me.$contentMain.addClass('is--fullscreen');

            $.publish('plugin/emotion/initFullscreen', me);
        },

        /**
         * Initializes the grid for the masonry type.
         */
        initMasonryGrid: function() {
            var me = this,
                remSpacing = me.opts.cellSpacing / 16;

            me.$el.css({
                'margin-top': -remSpacing + 'rem',
                'margin-left': -remSpacing + 'rem'
            });

            me.$elements.css({
                'padding-top': remSpacing + 'rem',
                'padding-left': remSpacing + 'rem',
                'padding-right': 0,
                'padding-bottom': 0
            });

            me.$el.masonry({
                'gutter': 0, // Gutter space is managed via css
                'itemSelector': me.opts.itemSelector,
                'transitionDuration': me.opts.transitionDuration,
                'columnWidth': me.$gridSizer[0]
            });

            $.publish('plugin/emotion/initMasonryGrid', me);
        },

        /**
         * Initializes the grid for the resizing type.
         */
        initScaleGrid: function() {
            var me = this,
                remSpacing = me.opts.cellSpacing / 16;

            me.baseWidth = ~~me.opts.baseWidth;
            me.ratio = me.baseWidth / me.$el.outerHeight();

            me.$el.css({
                'width': me.baseWidth,
                'margin-top': -remSpacing + 'rem'
            });

            if (!me.opts.fullscreen) {
                me.$wrapper.css('max-width', me.baseWidth);
            }

            me.scale();

            $.publish('plugin/emotion/initScaleGrid', me);
        },

        /**
         * Registers all necessary event listener.
         */
        registerEvents: function() {
            var me = this;

            $window.on('resize', $.proxy(me.onResize, me));

            $.publish('plugin/emotion/registerEvents', me);
        },

        /**
         * Called by event listener on window resize.
         */
        onResize: function() {
            var me = this;

            if (me.opts.gridMode === 'resize') {
                me.scale();
            }

            me.$bannerElements.trigger('emotionResize');
            me.$videoElements.trigger('emotionResize');
        },

        /**
         * Scales the emotion grid via css3 transformation for resize mode.
         */
        scale: function() {
            var me = this,
                width = (me.opts.fullscreen) ? $window.outerWidth() : me.$wrapper.outerWidth(),
                factor = width / me.baseWidth,
                origin = (factor > 1) ? '50% 0px' : '0px 0px',
                wrapperHeight = width / me.ratio;

            me.$el.css({
                '-ms-transform-origin': origin,
                '-o-transform-origin': origin,
                '-moz-transform-origin': origin,
                '-webkit-transform-origin': origin,
                'transform-origin': origin,
                '-ms-transform': 'scale('+ factor +')',
                '-o-transform': 'scale('+ factor +')',
                '-moz-transform': 'scale('+ factor +')',
                '-webkit-transform': 'scale('+ factor +')',
                'transform': 'scale('+ factor +')'
            });

            me.$wrapper.css('height', wrapperHeight);
        },

        /**
         * Buffers the calling of a function.
         *
         * @param func
         * @param bufferTime
         */
        buffer: function(func, bufferTime) {
            var me = this;

            window.clearTimeout(me.bufferedCall);

            me.bufferedCall = window.setTimeout($.proxy(func, me), bufferTime)
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me._destroy();
        }
    });


    /**
     * Emotion Banner Element
     *
     * This plugin handles banner elements in an emotion world.
     */
    $.plugin('emotionBanner', {

        defaults: {

            /**
             * Turn banner mapping on and off.
             *
             * @property bannerMapping
             * @type {boolean}
             */
            bannerMapping: false,

            /**
             * The DOM selector for the banner container.
             *
             * @property containerSelector
             * @type {string}
             */
            containerSelector: '.banner--content',

            /**
             * The DOM selector for the banner mapping container.
             *
             * @property bannerMappingSelector
             * @type {string}
             */
            bannerMappingSelector: '.banner--mapping'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$container = me.$el.find(me.opts.containerSelector);

            me.imageWidth = parseInt(me.$el.attr('data-width'), 10);
            me.imageHeight = parseInt(me.$el.attr('data-height'), 10);
            me.imageRatio = me.imageWidth / me.imageHeight;

            if (me.opts.bannerMapping) {
                me.$mapping = me.$el.find(me.opts.bannerMappingSelector);
                me.resizeMapping();
            }

            me.registerEvents();
        },

        /**
         * Registers all necessary event listener.
         */
        registerEvents: function() {
            var me = this;

            if (me.opts.bannerMapping) me._on(me.$el, 'emotionResize', $.proxy(me.resizeMapping, me));
        },

        /**
         * Does the measuring for the banner mapping container
         * and sets it's new dimensions.
         */
        resizeMapping: function() {
            var me = this,
                containerWidth = me.$container.outerWidth(),
                containerHeight = me.$container.outerHeight(),
                containerRatio = containerWidth / containerHeight,
                orientation = me.imageRatio > containerRatio,
                mappingWidth = orientation ? containerHeight * me.imageRatio : '100%',
                mappingHeight = orientation ? '100%' : containerWidth / me.imageRatio;

            me.$mapping.css({
                'width': mappingWidth,
                'height': mappingHeight,
                'top': orientation ? 0 : '50%',
                'left': orientation ? '50%' : 0,
                'margin-top': orientation ? 0 : -(mappingHeight / 2),
                'margin-left': orientation ? -(mappingWidth / 2) : 0
            });
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me._destroy();
        }
    });


    /**
     * Emotion Video Element
     *
     * This plugin handles html5 video elements in an emotion world.
     */
    $.plugin('emotionVideo', {

        defaults: {

            /**
             * The sizing mode for the video.
             *
             * @property mode ( scale | cover | stretch )
             * @type {string}
             */
            mode: 'cover',

            /**
             * The X coordinate for the transform origin.
             *
             * @property scaleOriginX
             * @type {number}
             */
            scaleOriginX: 50,

            /**
             * The Y coordinate for the transform origin.
             *
             * @property scaleOriginX
             * @type {number}
             */
            scaleOriginY: 50,

            /**
             * The scale factor for the transforming.
             *
             * @property scale
             * @type {number}
             */
            scale: 1,

            /**
             * The css class for the play icon.
             *
             * @property playIconCls
             * @type {string}
             */
            playIconCls: 'icon--play',

            /**
             * The css class for the pause icon.
             *
             * @property pauseIconCls
             * @type {string}
             */
            pauseIconCls: 'icon--pause',

            /**
             * The DOM selector for the video element.
             *
             * @property videoSelector
             * @type {string}
             */
            videoSelector: '.video--element',

            /**
             * The DOM selector for the play button.
             *
             * @property playBtnSelector
             * @type {string}
             */
            playBtnSelector: '.video--play-btn',

            /**
             * The DOM selector for the play icon.
             *
             * @property playIconSelector
             * @type {string}
             */
            playIconSelector: '.video--play-icon'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$video = me.$el.find(me.opts.videoSelector);
            me.$playBtn = me.$el.find(me.opts.playBtnSelector);
            me.$playBtnIcon = me.$playBtn.find(me.opts.playIconSelector);

            me.player = me.$video.get(0);

            me.setScaleOrigin(me.opts.scaleOriginX, me.opts.scaleOriginY);

            me.registerEvents();
        },

        /**
         * Registers all necessary event listener.
         */
        registerEvents: function() {
            var me = this;

            me._on(me.$video, 'loadedmetadata', $.proxy(me.onLoadMeta, me));
            me._on(me.$video, 'canplay', $.proxy(me.onCanPlay, me));
            me._on(me.$video, 'ended', $.proxy(me.onVideoEnded, me));

            me._on(me.$el, 'emotionResize', $.proxy(me.resizeVideo, me));

            me._on(me.$playBtn, 'click', $.proxy(me.onPlayClick, me));
        },

        /**
         * Called on loaded meta data event.
         * Gets the video properties from the loaded video.
         */
        onLoadMeta: function() {
            var me = this;

            me.videoWidth = me.player.videoWidth;
            me.videoHeight = me.player.videoHeight;
            me.videoRatio = me.videoWidth / me.videoHeight;

            me.resizeVideo();
        },

        /**
         * Called on can play event.
         * Sets the correct play button icon.
         */
        onCanPlay: function() {
            var me = this;

            if(!me.player.paused || me.player.autoplay) {
                me.$playBtnIcon.addClass(me.opts.pauseIconCls).removeClass(me.opts.playIconCls);
            }
        },

        /**
         * Called on ended event.
         * Sets the correct play button icon.
         */
        onVideoEnded: function() {
            var me = this;

            me.$playBtnIcon.removeClass(me.opts.pauseIconCls).addClass(me.opts.playIconCls);
        },

        /**
         * Called on click event on the the play button.
         * Starts or pauses the video.
         */
        onPlayClick: function() {
            var me = this;

            (me.player.paused) ? me.playVideo() : me.stopVideo();
        },

        /**
         * Starts the video and sets the correct play button icon.
         */
        playVideo: function() {
            var me = this;

            me.$playBtnIcon.addClass(me.opts.pauseIconCls).removeClass(me.opts.playIconCls);
            me.player.play();
        },

        /**
         * Pauses the video and sets the correct play button icon.
         */
        stopVideo: function() {
            var me = this;

            me.$playBtnIcon.removeClass(me.opts.pauseIconCls).addClass(me.opts.playIconCls);
            me.player.pause();
        },

        /**
         * Measures the correct dimensions for the video
         * based on the transformation mode.
         */
        resizeVideo: function() {
            var me = this;

            /**
             * Do nothing because it is the standard browser behaviour.
             * The empty space will be filled by black bars.
             */
            if (me.opts.mode === 'scale') {
                return;
            }

            var containerWidth = me.$el.outerWidth(),
                containerHeight = me.$el.outerHeight(),
                containerRatio = containerWidth / containerHeight,
                orientation = me.videoRatio > containerRatio,
                positiveFactor = me.videoRatio / containerRatio,
                negativeFactor = containerRatio / me.videoRatio;

            /**
             * Stretches the video to fill the hole container
             * no matter what dimensions the container has.
             */
            if (me.opts.mode === 'stretch') {
                if (orientation) {
                    me.transformVideo('scaleY(' + positiveFactor * me.opts.scale + ')');
                } else {
                    me.transformVideo('scaleX(' + negativeFactor * me.opts.scale + ')');
                }
            }

            /**
             * Scales up the video to fill the hole container by
             * keeping the video dimensions but cutting overlapping content.
             */
            if (me.opts.mode === 'cover') {
                if (orientation) {
                    me.transformVideo('scaleX(' + positiveFactor * me.opts.scale + ') scaleY(' + positiveFactor * me.opts.scale + ')');
                } else {
                    me.transformVideo('scaleX(' + negativeFactor * me.opts.scale + ') scaleY(' + negativeFactor * me.opts.scale + ')');
                }
            }
        },

        /**
         * Sets the transform origin coordinates on the video element.
         *
         * @param originX
         * @param originY
         */
        setScaleOrigin: function(originX, originY) {
            var me = this,
                x = originX || me.opts.scaleOriginX,
                y = originY || me.opts.scaleOriginY,
                origin = x+'% '+y+'%';

            me.$video.css({
                '-ms-transform-origin': origin,
                '-o-transform-origin': origin,
                '-moz-transform-origin': origin,
                '-webkit-transform-origin': origin,
                'transform-origin': origin
            });
        },

        /**
         * Transforms the video by the given css3 transformation.
         *
         * @param transformation
         */
        transformVideo: function(transformation) {
            var me = this;

            me.$video.css({
                '-ms-transform': transformation,
                '-o-transform': transformation,
                '-moz-transform': transformation,
                '-webkit-transform': transformation,
                'transform': transformation
            });
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me._destroy();
        }
    });

})(jQuery, window, document);