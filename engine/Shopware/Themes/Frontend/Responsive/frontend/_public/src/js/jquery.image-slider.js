;(function ($, Modernizr, window, Math) {
    'use strict';

    /**
     * Image Slider Plugin.
     *
     * This plugin provides the functionality for an advanced responsive image slider.
     * It has support for thumbnails, arrow controls, touch controls and automatic sliding.
     *
     * Example DOM Structure:
     *
     * <div class="image-slider" data-image-slider="true">
     *      <div class="image-slider--container">
     *          <div class="image-slider--slide">
     *              <div class="image-slider--item"></div>
     *              <div class="image-slider--item"></div>
     *              <div class="image-slider--item"></div>
     *          </div>
     *      </div>
     *      <div class="image-slider--thumbnails">
     *          <div class="image-slider--thumbnails-slide">
     *              <a class="thumbnail--link"></a>
     *              <a class="thumbnail--link"></a>
     *              <a class="thumbnail--link"></a>
     *          </div>
     *      </div>
     * </div>
     */
    $.plugin('imageSlider', {

        defaults: {

            /**
             * Set the speed of the slide animation in ms.
             *
             * @property animationSpeed
             * @type {Number}
             */
            animationSpeed: 350,

            /**
             * Turn thumbnail support on and off.
             *
             * @property thumbnails
             * @type {Boolean}
             */
            thumbnails: true,

            /**
             * Turn support for a small dot navigation on and off.
             *
             * @property dotNavigation
             * @type {Boolean}
             */
            dotNavigation: true,

            /**
             * Turn arrow controls on and off.
             *
             * @property arrowControls
             * @type {Boolean}
             */
            arrowControls: true,

            /**
             * Turn touch controls on and off.
             *
             * @property touchControls
             * @type {Boolean}
             */
            touchControls: true,

            /**
             * Whether or not the automatic slide feature should be active.
             *
             * @property autoSlide
             * @type {Boolean}
             */
            autoSlide: false,

            /**
             * Whether or not the pinch to zoom feature should be active.
             *
             * @property pinchToZoom
             * @type {Boolean}
             */
            pinchToZoom: false,

            /**
             * Whether or not the swipe to slide feature should be active.
             *
             * @property swipeToSlide
             * @type {Boolean}
             */
            swipeToSlide: true,

            /**
             * Whether or not the double tap/click should be used to zoom in/out..
             *
             * @property doubleTap
             * @type {Boolean}
             */
            doubleTap: false,

            /**
             * The minimal zoom factor an image can have.
             *
             * @property minZoom
             * @type {Number}
             */
            minZoom: 1,

            /**
             * The maximal zoom factor an image can have.
             * Can either be a number or 'auto'.
             *
             * If set to 'auto', you can only zoom to the original image size.
             *
             * @property maxZoom
             * @type {Number|String}
             */
            maxZoom: 'auto',

            /**
             * The distance you have to travel to recognize a swipe in pixels.
             *
             * @property swipeTolerance
             * @type {Number}
             */
            swipeTolerance: 100,

            /**
             * The image index that will be set when the plugin gets initialized.
             *
             * @property startIndex
             * @type {Number}
             */
            startIndex: 0,

            /**
             * Set the speed for the automatic sliding in ms.
             *
             * @property autoSlideInterval
             * @type {Number}
             */
            autoSlideInterval: 5000,

            /**
             * This property indicates whether or not the slides are looped.
             * If this flag is active and the last slide is active, you can
             * slide to the next one and it will start from the beginning.
             *
             * @property loopSlides
             * @type {Boolean}
             */
            loopSlides: false,

            /**
             * The selector for the container element holding the actual image slider.
             *
             * @property imageContainerSelector
             * @type {String}
             */
            imageContainerSelector: '.image-slider--container',

            /**
             * The selector for the slide element which slides inside the image container.
             *
             * @property imageSlideSelector
             * @type {String}
             */
            imageSlideSelector: '.image-slider--slide',

            /**
             * The selector fot the container element holding the thumbnails.
             *
             * @property thumbnailContainerSelector
             * @type {String}
             */
            thumbnailContainerSelector: '.image-slider--thumbnails',

            /**
             * The selector for the element that slides inside the thumbnail container.
             * This element should be contained in the thumbnail container.
             *
             * @property thumbnailSlideSelector
             * @type {String}
             */
            thumbnailSlideSelector: '.image-slider--thumbnails-slide',

            /**
             * Selector of a single thumbnail.
             * Those thumbnails should be contained in the thumbnail slide.
             *
             * @property thumbnailSlideSelector
             * @type {String}
             */
            thumbnailSelector: '.thumbnail--link',

            /**
             * The selector for the dot navigation container.
             *
             * @property dotNavSelector
             * @type {String}
             */
            dotNavSelector: '.image-slider--dots',

            /**
             * The selector for each dot link in the dot navigation.
             *
             * @property dotLinkSelector
             * @type {String}
             */
            dotLinkSelector: '.dot--link',

            /**
             * Class that will be applied to both the previous and next arrow.
             *
             * @property thumbnailArrowCls
             * @type {String}
             */
            thumbnailArrowCls: 'thumbnails--arrow',

            /**
             * The css class for the left slider arrow.
             *
             * @property leftArrowCls
             * @type {String}
             */
            leftArrowCls: 'arrow is--left',

            /**
             * The css class for the right slider arrow.
             *
             * @property rightArrowCls
             * @type {String}
             */
            rightArrowCls: 'arrow is--right',

            /**
             * The css class for a top positioned thumbnail arrow.
             *
             * @property thumbnailArrowTopCls
             * @type {String}
             */
            thumbnailArrowTopCls: 'is--top',

            /**
             * The css class for a left positioned thumbnail arrow.
             *
             * @property thumbnailArrowLeftCls
             * @type {String}
             */
            thumbnailArrowLeftCls: 'is--left',

            /**
             * The css class for a right positioned thumbnail arrow.
             *
             * @property thumbnailArrowRightCls
             * @type {String}
             */
            thumbnailArrowRightCls: 'is--right',

            /**
             * The css class for a bottom positioned thumbnail arrow.
             *
             * @property thumbnailArrowBottomCls
             * @type {String}
             */
            thumbnailArrowBottomCls: 'is--bottom',

            /**
             * The css class for active states of the arrows.
             *
             * @property activeStateClass
             * @type {String}
             */
            activeStateClass: 'is--active',

            /**
             * Class that will be appended to the image container
             * when the user is grabbing an image
             *
             * @property grabClass
             * @type {String}
             */
            dragClass: 'is--dragging',

            /**
             * Class that will be appended to the thumbnail container
             * when no other thumbnails are available
             *
             * @property noThumbClass
             * @type {String}
             */
            noThumbClass: 'no--thumbnails',

            /**
             * Selector for the image elements in the slider.
             * Those images should be contained in the image slide element.
             *
             * @property imageSelector
             * @type {String}
             */
            imageSelector: '.image--element',

            /**
             * Selector for a single slide item.
             * Those elements should be contained in the image slide element.
             *
             * @property imageSelector
             * @type {String}
             */
            itemSelector: '.image-slider--item',

            /**
             * Class that will be appended when an element should not be shown.
             *
             * @property hiddenClass
             * @type {String}
             */
            hiddenClass: 'is--hidden'
        },

        /**
         * Method for the plugin initialisation.
         * Merges the passed options with the data attribute configurations.
         * Creates and references all needed elements and properties.
         * Calls the registerEvents method afterwards.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                opts = me.opts;

            // Merge the data attribute configurations with the default ones
            me.applyDataAttributes();

            /**
             * Container of the slide element.
             * Acts as a wrapper and container for additional
             * elements like arrows.
             *
             * @private
             * @property $slideContainer
             * @type {jQuery}
             */
            me.$slideContainer = me.$el.find(opts.imageContainerSelector);

            /**
             * Container of the slide element.
             * Acts as a wrapper and container for additional
             * elements like arrows.
             *
             * @private
             * @property $slide
             * @type {jQuery}
             */
            me.$slide = me.$slideContainer.find(opts.imageSlideSelector);

            /**
             * Current index of the active slide.
             * Will be used for correctly showing the active thumbnails / dot.
             *
             * @private
             * @property slideIndex
             * @type {Number}
             */
            me.slideIndex = opts.startIndex;

            /**
             * ID of the setTimeout that will be called if the
             * auto slide option is active.
             * Wil be used for removing / resetting the timer.
             *
             * @private
             * @property slideInterval
             * @type {Number}
             */
            me.slideInterval = 0;

            /**
             * References the currently active image.
             * This element is contained in a jQuery wrapper.
             *
             * @private
             * @property $currentImage
             * @type {jQuery}
             */
            me.$currentImage = null;

            opts.maxZoom = parseFloat(opts.maxZoom) || 'auto';

            if (opts.arrowControls) {
                me.createArrows();
            }

            if (opts.thumbnails) {
                me.$thumbnailContainer = me.$el.find(opts.thumbnailContainerSelector);
                me.$thumbnailSlide = me.$thumbnailContainer.find(opts.thumbnailSlideSelector);
                me.thumbnailOrientation = me.getThumbnailOrientation();
                me.thumbnailOffset = 0;
                me.createThumbnailArrows();
            }

            if (opts.dotNavigation) {
                me.$dotNav = me.$el.find(opts.dotNavSelector);
                me.$dots = me.$dotNav.find(opts.dotLinkSelector);
                me.setActiveDot(me.slideIndex);
            }

            me.trackItems();

            if (opts.thumbnails) {
                me.trackThumbnailControls();
                me.setActiveThumbnail(me.slideIndex);
            }

            me.setIndex(me.slideIndex);

            /**
             * Whether or not the user is grabbing the image with the mouse.
             *
             * @private
             * @property grabImage
             * @type {Boolean}
             */
            me.grabImage = false;

            /**
             * First touch point position from touchstart event.
             * Will be used to determine the swiping gesture.
             *
             * @private
             * @property startTouchPoint
             * @type {Vector}
             */
            me.startTouchPoint = new Vector(0, 0);

            /**
             * Translation (positioning) of the current image.
             *
             * @private
             * @property imageTranslation
             * @type {Vector}
             */
            me.imageTranslation = new Vector(0, 0);

            /**
             * Scaling (both X and Y equally) of the current image.
             *
             * @private
             * @property imageScale
             * @type {Number}
             */
            me.imageScale = 1;

            /**
             * Relative distance when pinching.
             * Will be used for the pinch to zoom gesture.
             *
             * @private
             * @property touchDistance
             * @type {Number}
             */
            me.touchDistance = 0;

            /**
             * Last time the current image was touched.
             * Used to determine double tapping.
             *
             * @private
             * @property lastTouchTime
             * @type {Number}
             */
            me.lastTouchTime = 0;

            me.registerEvents();
        },

        /**
         * Registers all necessary event listeners.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this,
                opts = me.opts,
                $slide = me.$slide;

            if (opts.touchControls) {
                me._on($slide, 'touchstart mousedown MSPointerDown', me.onTouchStart.bind(me));
                me._on($slide, 'touchmove mousemove MSPointerMove', me.onTouchMove.bind(me));
                me._on($slide, 'touchend mouseup MSPointerUp', me.onTouchEnd.bind(me));
                me._on($slide, 'mouseleave', me.onMouseLeave.bind(me));

                if (opts.pinchToZoom) {
                    me._on($slide, 'mousewheel', me.onScroll.bind(me));
                }

                if (opts.doubleTap) {
                    me._on($slide, 'dblclick', me.onDoubleClick.bind(me));
                }
            }

            if (opts.arrowControls) {
                me._on(me.$arrowLeft, 'click touchstart', $.proxy(me.onLeftArrowClick, me));
                me._on(me.$arrowRight, 'click touchstart', $.proxy(me.onRightArrowClick, me));
            }

            if (opts.thumbnails) {
                me.$thumbnails.each($.proxy(me.applyClickEventHandler, me));

                me._on(me.$thumbnailArrowPrev, 'click touchstart', $.proxy(me.onThumbnailPrevArrowClick, me));
                me._on(me.$thumbnailArrowNext, 'click touchstart', $.proxy(me.onThumbnailNextArrowClick, me));

                if (opts.touchControls) {
                    me._on(me.$thumbnailSlide, 'touchstart', $.proxy(me.onThumbnailSlideTouch, me));
                    me._on(me.$thumbnailSlide, 'touchmove', $.proxy(me.onThumbnailSlideMove, me));
                }

                StateManager.on('resize', me.onResize, me);
            }

            if (opts.dotNavigation && me.$dots) {
                me.$dots.each($.proxy(me.applyClickEventHandler, me));
            }

            if (opts.autoSlide) {
                me.startAutoSlide();

                me._on(me.$el, 'mouseenter', $.proxy(me.stopAutoSlide, me));
                me._on(me.$el, 'mouseleave', $.proxy(me.startAutoSlide, me));
            }
        },

        /**
         * Will be called when the user starts touching the image slider.
         * Checks if the user is double tapping the image.
         *
         * @event onTouchStart
         * @param {jQuery.Event} event
         */
        onTouchStart: function (event) {
            var me = this,
                pointers = me.getPointers(event),
                pointerA = pointers[0],
                currTime = Date.now(),
                startPoint = me.startTouchPoint,
                distance,
                deltaX,
                deltaY;

            startPoint.set(pointerA.clientX, pointerA.clientY);

            if (pointers.length === 1) {
                if (event.originalEvent instanceof MouseEvent) {
                    event.preventDefault();

                    me.grabImage = true;
                    me.$slideContainer.addClass(me.opts.dragClass);
                    return;
                }

                if (!me.opts.doubleTap) {
                    return;
                }

                deltaX = Math.abs(pointerA.clientX - startPoint.x);
                deltaY = Math.abs(pointerA.clientY - startPoint.y);

                distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

                if (currTime - me.lastTouchTime < 500 && distance < 30) {
                    me.onDoubleClick(event);
                }

                me.lastTouchTime = currTime;
            } else {
                event.preventDefault();
            }
        },

        /**
         * Will be called when the user is moving the finger while touching
         * the image slider.
         *
         * When only one finger is touching the screen
         * and the image was scaled, it will be translated (moved).
         *
         * If two fingers are available, the image will be zoomed (pinch to zoom).
         *
         * @event onTouchMove
         * @param {jQuery.Event} event
         */
        onTouchMove: function (event) {
            var me = this,
                touches = me.getPointers(event),
                touchA = touches[0],
                touchB = touches[1],
                scale = me.imageScale,
                distance,
                deltaX,
                deltaY;

            if (touches.length > 2) {
                return;
            }

            if (touches.length === 1 && scale > 1) {
                // If the image is zoomed, move it
                if (event.originalEvent instanceof MouseEvent && !me.grabImage) {
                    return;
                }

                deltaX = touchA.clientX - me.startTouchPoint.x;
                deltaY = touchA.clientY - me.startTouchPoint.y;

                me.startTouchPoint.set(touchA.clientX, touchA.clientY);

                me.translate(deltaX / scale, deltaY / scale);

                event.preventDefault();
                return;
            }

            if (!me.opts.pinchToZoom || !touchB) {
                return;
            }

            deltaX = Math.abs(touchA.clientX - touchB.clientX);
            deltaY = Math.abs(touchA.clientY - touchB.clientY);

            distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

            if (me.touchDistance === 0) {
                me.touchDistance = distance;
                return;
            }

            me.scale((distance - me.touchDistance) / 100);

            me.touchDistance = distance;
        },

        /**
         * Will be called when the user ends touching the image slider.
         * If the swipeToSlide option is active and the swipe tolerance is
         * exceeded, it will slide to the previous / next image.
         *
         * @event onTouchEnd
         * @param {jQuery.Event} event
         */
        onTouchEnd: function (event) {
            var me = this,
                touches = event.changedTouches,
                remaining = event.originalEvent.touches,
                touchA = (touches && touches[0]) || event.originalEvent,
                touchB = remaining && remaining[0],
                swipeTolerance = me.opts.swipeTolerance,
                deltaX,
                deltaY;

            me.touchDistance = 0;
            me.grabImage = false;
            me.$slideContainer.removeClass(me.opts.dragClass);

            if (touchB) {
                me.startTouchPoint.set(touchB.clientX, touchB.clientY);
                return;
            }

            if (!me.opts.swipeToSlide) {
                return;
            }

            deltaX = me.startTouchPoint.x - touchA.clientX;
            deltaY = me.startTouchPoint.y - touchA.clientY;

            if (Math.abs(deltaX) < swipeTolerance || Math.abs(deltaY) > swipeTolerance) {
                return;
            }

            event.preventDefault();

            if (deltaX < 0) {
                me.slidePrev();
                return;
            }

            me.slideNext();
        },

        /**
         * Will be called when the user scrolls the image by the mouse.
         * Zooms the image in/out by the factor 0.25.
         *
         * @event onScroll
         * @param {jQuery.Event} event
         */
        onScroll: function (event) {
            var me = this;

            if (event.originalEvent.deltaY < 0) {
                me.scale(0.25);
            } else {
                me.scale(-0.25);
            }

            event.preventDefault();
        },

        /**
         * Will be called when the user
         * double clicks or double taps on the image slider.
         * When the image was scaled, it will reset its scaling
         * otherwise it will zoom in by the factor of 1.
         *
         * @event onDoubleClick
         * @param {jQuery.Event} event
         */
        onDoubleClick: function (event) {
            var me = this;

            if (!me.opts.doubleTap) {
                return;
            }

            event.preventDefault();

            if (me.imageScale <= 1) {
                me.scale(1, true);
                return;
            }

            me.setScale(1, true);
        },

        /**
         * Is triggered when the left arrow
         * of the image slider is clicked or tapped.
         *
         * @event onLeftArrowClick
         * @param {jQuery.Event} event
         */
        onLeftArrowClick: function (event) {
            event.preventDefault();

            this.slidePrev();

            $.publish('plugin/imageSlider/onLeftArrowClick');
        },

        /**
         * Is triggered when the right arrow
         * of the image slider is clicked or tapped.
         *
         * @event onRightArrowClick
         * @param {jQuery.Event} event
         */
        onRightArrowClick: function (event) {
            event.preventDefault();

            this.slideNext();

            $.publish('plugin/imageSlider/onRightArrowClick');
        },

        /**
         * Slides the thumbnail slider one position backwards.
         *
         * @event onThumbnailPrevArrowClick
         * @param {jQuery.Event} event
         */
        onThumbnailPrevArrowClick: function (event) {
            event.preventDefault();

            var me = this,
                $container = me.$thumbnailContainer,
                size = me.thumbnailOrientation === 'horizontal' ? $container.innerWidth() : $container.innerHeight();

            me.setThumbnailSlidePosition(me.thumbnailOffset + (size / 2), true);
        },

        /**
         * Slides the thumbnail slider one position forward.
         *
         * @event onThumbnailNextArrowClick
         * @param {jQuery.Event} event
         */
        onThumbnailNextArrowClick: function (event) {
            event.preventDefault();

            var me = this,
                $container = me.$thumbnailContainer,
                size = me.thumbnailOrientation === 'horizontal' ? $container.innerWidth() : $container.innerHeight();

            me.setThumbnailSlidePosition(me.thumbnailOffset - (size / 2), true);
        },

        /**
         * Will be called when the user leaves the image slide with the mouse.
         * Resets the cursor grab indicator.
         *
         * @event onMouseLeave
         */
        onMouseLeave: function () {
            var me = this;

            me.grabImage = false;
            me.$slideContainer.removeClass(me.opts.dragClass);
        },

        /**
         * Will be called when the viewport has been resized.
         * When thumbnails are enabled, the trackThumbnailControls function
         * will be called.
         *
         * @event onResize
         */
        onResize: function () {
            if (this.opts.thumbnails) {
                this.trackThumbnailControls();
            }
        },

        /**
         * Will be called when the user starts touching the thumbnails slider.
         *
         * @event onThumbnailSlideTouch
         * @param {jQuery.Event} event
         */
        onThumbnailSlideTouch: function (event) {
            var me = this,
                pointers = me.getPointers(event),
                pointerA = pointers[0];

            me.startTouchPoint.set(pointerA.clientX, pointerA.clientY);
        },

        /**
         * Will be called when the user is moving the finger while touching
         * the thumbnail slider.
         * Slides the thumbnails slider to the left/right depending on the user.
         *
         * @event onThumbnailSlideMove
         * @param {jQuery.Event} event
         */
        onThumbnailSlideMove: function (event) {
            event.preventDefault();

            var me = this,
                pointers = me.getPointers(event),
                pointerA = pointers[0],
                startPoint = me.startTouchPoint,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                posA = isHorizontal ? pointerA.clientX : pointerA.clientY,
                posB = isHorizontal ? startPoint.x : startPoint.y,
                delta = posA - posB;

            startPoint.set(pointerA.clientX, pointerA.clientY);

            me.setThumbnailSlidePosition(me.thumbnailOffset + delta, false);

            me.trackThumbnailControls();
        },

        /**
         * Returns either an array of touches or a single mouse event.
         * This is a helper function to unify the touch/mouse gesture logic.
         *
         * @private
         * @method getPointers
         * @param {jQuery.Event} event
         */
        getPointers: function (event) {
            var origEvent = event.originalEvent || event;

            return origEvent.touches || [origEvent];
        },

        /**
         * Calculates the new x/y coordinates for the image based by the
         * given scale value.
         *
         * @private
         * @method getTransformedPosition
         * @param {Number} x
         * @param {Number} y
         * @param {Number} scale
         */
        getTransformedPosition: function (x, y, scale) {
            var me = this,
                image = me.$currentImage,
                width = image.width(),
                height = image.height(),
                scaledWidth = width * scale,
                scaledHeight = height * scale,
                minX = (scaledWidth - width) / scale / 2,
                minY = (scaledHeight - height) / scale / 2;

            return new Vector(
                Math.max(minX * -1, Math.min(minX, x)),
                Math.max(minY * -1, Math.min(minY, y))
            );
        },

        /**
         * Sets the translation (position) of the current image.
         *
         * @public
         * @method setTranslation
         * @param {Number} x
         * @param {Number} y
         */
        setTranslation: function (x, y) {
            var me = this,
                newPos = me.getTransformedPosition(x, y, me.imageScale);

            me.imageTranslation.set(newPos.x, newPos.y);

            me.updateTransform(false);
        },

        /**
         * Translates the current image relative to the current position.
         * The x/y values will be added together.
         *
         * @public
         * @method translate
         * @param {Number} x
         * @param {Number} y
         */
        translate: function (x, y) {
            var me = this,
                translation = me.imageTranslation;

            me.setTranslation(translation.x + x, translation.y + y);
        },

        /**
         * Scales the current image to the given scale value.
         * You can also pass the option if it should be animated
         * and if so, you can also pass a callback.
         *
         * @public
         * @method setScale
         * @param {Number|String} scale
         * @param {Boolean} animate
         * @param {Function} callback
         */
        setScale: function (scale, animate, callback) {
            var me = this,
                opts = me.opts,
                $currImage = me.$currentImage,
                img = $currImage[0],
                minZoom = opts.minZoom,
                maxZoom = opts.maxZoom,
                oldScale = me.imageScale;

            if (typeof maxZoom !== 'number') {
                maxZoom = Math.max(img.naturalWidth, img.naturalHeight) / Math.max($currImage.width(), $currImage.height());
            }

            me.imageScale = Math.max(minZoom, Math.min(maxZoom, scale));

            if (me.imageScale === oldScale) {
                if (typeof callback === 'function') {
                    callback.call(me);
                }
                return;
            }

            me.updateTransform(animate, callback);
        },

        /**
         * Scales the current image relative to the current scale value.
         * The factor value will be added to the current scale.
         *
         * @public
         * @method scale
         * @param {Number} factor
         * @param {Boolean} animate
         * @param {Function} callback
         */
        scale: function (factor, animate, callback) {
            this.setScale(this.imageScale + factor, animate, callback);
        },

        /**
         * Updates the transformation of the current image.
         * The scale and translation will be considered into this.
         * You can also decide if the update should be animated
         * and if so, you can provide a callback function
         *
         * @public
         * @method updateTransform
         * @param {Boolean} animate
         * @param {Function} callback
         */
        updateTransform: function (animate, callback) {
            var me = this,
                translation = me.imageTranslation,
                scale = me.imageScale,
                newPosition = me.getTransformedPosition(translation.x, translation.y, scale);

            translation.set(newPosition.x, newPosition.y);

            if (!animate || !Modernizr.csstransitions) {
                me.$currentImage.css('transform', 'scale(' + scale + ') translate(' + translation.x + 'px, ' + translation.y + 'px)');

                if (callback) {
                    callback.call(me);
                }
                return;
            }

            me.$currentImage.transition({
                'scale': scale,
                'x': translation.x,
                'y': translation.y
            }, me.opts.animationSpeed, 'cubic-bezier(.2,.76,.5,1)', callback);
        },

        /**
         * Applies a click event handler to the element
         * to slide the slider to the index of that element.
         *
         * @private
         * @method applyClickEventHandler
         * @param {Number} index
         * @param {HTMLElement} el
         */
        applyClickEventHandler: function (index, el) {
            var me = this,
                $el = $(el),
                i = index || $el.index();

            me._on($el, 'click', function (event) {
                event.preventDefault();
                me.slide(i);
            });
        },

        /**
         * Creates the arrow controls for the image slider.
         *
         * @private
         * @method createArrows
         */
        createArrows: function () {
            var me = this,
                opts = me.opts;

            me.$arrowLeft = $('<a>', {
                'class': opts.leftArrowCls + (!opts.loopSlides && (me.slideIndex <= 0) ? ' ' + opts.hiddenClass : '')
            }).appendTo(me.$slideContainer);

            me.$arrowRight = $('<a>', {
                'class': opts.rightArrowCls + (!opts.loopSlides && (me.slideIndex >= me.itemCount - 1) ? ' ' + opts.hiddenClass : '')
            }).appendTo(me.$slideContainer);
        },

        /**
         * Creates the thumbnail arrow controls for the thumbnail slider.
         *
         * @private
         * @method createThumbnailArrows
         */
        createThumbnailArrows: function () {
            var me = this,
                opts = me.opts,
                isHorizontal = (me.thumbnailOrientation === 'horizontal'),
                prevClass = isHorizontal ? opts.thumbnailArrowLeftCls : opts.thumbnailArrowTopCls,
                nextClass = isHorizontal ? opts.thumbnailArrowRightCls : opts.thumbnailArrowBottomCls;

            me.$thumbnailArrowPrev = $('<a>', {
                'class': opts.thumbnailArrowCls + ' ' + prevClass
            }).appendTo(me.$thumbnailContainer);

            me.$thumbnailArrowNext = $('<a>', {
                'class': opts.thumbnailArrowCls + ' ' + nextClass
            }).appendTo(me.$thumbnailContainer);
        },

        /**
         * Tracks and counts the image elements and the thumbnail elements.
         *
         * @private
         * @method trackItems
         */
        trackItems: function () {
            var me = this,
                opts = me.opts;

            me.$items = me.$slide.find(opts.itemSelector);
            me.$images = me.$slide.find(opts.imageSelector);

            if (opts.thumbnails) {
                me.$thumbnails = me.$thumbnailContainer.find(opts.thumbnailSelector);
                me.thumbnailCount = me.$thumbnails.length;

                if (me.thumbnailCount === 0) {
                    me.$el.addClass(opts.noThumbClass);
                    opts.thumbnails = false;
                }
            }

            me.itemCount = me.$items.length;

            if (me.itemCount <= 1) {
                opts.arrowControls = false;
            }
        },

        /**
         * Sets the position of the image slide to the given image index.
         *
         * @public
         * @method setIndex
         * @param {Number} index
         */
        setIndex: function (index) {
            var me = this,
                i = index || me.slideIndex;

            me.$slide.css('left', (i * 100 * -1) + '%');
            me.$currentImage = $(me.$images[index]);
        },

        /**
         * Returns the orientation of the thumbnail container.
         *
         * @private
         * @method getThumbnailOrientation
         * @returns {String}
         */
        getThumbnailOrientation: function () {
            var $container = this.$thumbnailContainer;

            return ($container.innerWidth() > $container.innerHeight()) ? 'horizontal' : 'vertical';
        },

        /**
         * Sets the active state for the thumbnail at the given index position.
         *
         * @public
         * @method setActiveThumbnail
         * @param {Number} index
         */
        setActiveThumbnail: function (index) {
            var me = this,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                orientation = isHorizontal ? 'left' : 'top',
                $thumbnail = me.$thumbnails.eq(index),
                $container = me.$thumbnailContainer,
                thumbnailPos = $thumbnail.position(),
                slidePos = me.$thumbnailSlide.position(),
                slideOffset = slidePos[orientation],
                posA = thumbnailPos[orientation] * -1,
                posB = thumbnailPos[orientation] + (isHorizontal ? $thumbnail.outerWidth() : $thumbnail.outerHeight()),
                containerSize = isHorizontal ? $container.width() : $container.height(),
                newPos;

            if (posA < slideOffset && posB * -1 < slideOffset + (containerSize * -1)) {
                newPos = containerSize - Math.max(posB, containerSize);
            } else {
                newPos = Math.max(posA, slideOffset);
            }

            me.$thumbnails.removeClass(me.opts.activeStateClass);
            $thumbnail.addClass(me.opts.activeStateClass);

            me.setThumbnailSlidePosition(newPos, true);
        },

        /**
         * Sets the active state for the dot at the given index position.
         *
         * @public
         * @method setActiveDot
         * @param {Number} index
         */
        setActiveDot: function (index) {
            var me = this;

            if (me.opts.dotNavigation && me.$dots) {
                me.$dots.removeClass(me.opts.activeStateClass);
                me.$dots.eq(index || me.slideIndex).addClass(me.opts.activeStateClass);
            }
        },

        /**
         * Sets the position of the thumbnails slider
         * If the offset exceeds the minimum/maximum position, it will be culled
         *
         * @public
         * @method setThumbnailSlidePosition
         * @param {Number} offset
         * @param {Boolean} animate
         */
        setThumbnailSlidePosition: function (offset, animate) {
            var me = this,
                $slide = me.$thumbnailSlide,
                $container = me.$thumbnailContainer,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                sizeA = isHorizontal ? $container.innerWidth() : $container.innerHeight(),
                sizeB = isHorizontal ? $slide.outerWidth(true) : $slide.outerHeight(true),
                min = Math.min(0, sizeA - sizeB),
                css = {};

            me.thumbnailOffset = Math.max(min, Math.min(0, offset));

            css[isHorizontal ? 'left' : 'top'] = me.thumbnailOffset;
            css[isHorizontal ? 'top' : 'left'] = 'auto';

            if (!animate) {
                $slide.css(css);
                return;
            }

            $slide[Modernizr.csstransitions ? 'transition' : 'animate'](css, me.animationSpeed, me.trackThumbnailControls.bind(me));
        },

        /**
         * Checks which thumbnail arrow controls have to be shown.
         *
         * @private
         * @method trackThumbnailControls
         */
        trackThumbnailControls: function () {
            var me = this,
                opts = me.opts,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                $container = me.$thumbnailContainer,
                $slide = me.$thumbnailSlide,
                $prevArr = me.$thumbnailArrowPrev,
                $nextArr = me.$thumbnailArrowNext,
                activeCls = me.opts.activeStateClass,
                pos = $slide.position(),
                orientation = me.getThumbnailOrientation();

            if (me.thumbnailOrientation !== orientation) {

                me.$thumbnailSlide.css({
                    'left': 0,
                    'top': 0
                });

                $prevArr
                    .toggleClass(opts.thumbnailArrowLeftCls, !isHorizontal)
                    .toggleClass(opts.thumbnailArrowTopCls, isHorizontal);

                $nextArr
                    .toggleClass(opts.thumbnailArrowRightCls, !isHorizontal)
                    .toggleClass(opts.thumbnailArrowBottomCls, isHorizontal);

                me.thumbnailOrientation = orientation;
            }

            if (me.thumbnailOrientation === 'horizontal') {
                $prevArr.toggleClass(activeCls, pos.left < 0);
                $nextArr.toggleClass(activeCls, ($slide.innerWidth() + pos.left) > $container.innerWidth());
                return;
            }

            $prevArr.toggleClass(activeCls, pos.top < 0);
            $nextArr.toggleClass(activeCls, ($slide.innerHeight() + pos.top) > $container.innerHeight());
        },

        /**
         * Starts the auto slide interval.
         *
         * @private
         * @method startAutoSlide
         */
        startAutoSlide: function () {
            var me = this;

            me.stopAutoSlide(me.slideInterval);

            me.slideInterval = window.setTimeout(me.slideNext.bind(me), me.opts.autoSlideInterval);
        },

        /**
         * Stops the auto slide interval.
         *
         * @private
         * @method stopAutoSlide
         */
        stopAutoSlide: function () {
            window.clearTimeout(this.slideInterval);
        },

        /**
         * Slides the image slider to the given index position.
         *
         * @public
         * @method slide
         * @param {Number} index
         * @param {Function} callback
         */
        slide: function (index, callback) {
            var me = this,
                opts = me.opts,
                newPosition = (index * 100 * -1) + '%',
                method = (Modernizr.csstransitions) ? 'transition' : 'animate';

            me.slideIndex = index;

            if (opts.thumbnails) {
                me.setActiveThumbnail(index);
                me.trackThumbnailControls();
            }

            if (opts.dotNavigation && me.$dots) {
                me.setActiveDot(index);
            }

            if (opts.autoSlide) {
                me.stopAutoSlide();
                me.startAutoSlide();
            }

            me.resetTransformation(true, function () {
                me.$slide[method]({
                    'left': newPosition,
                    'easing': 'cubic-bezier(.2,.89,.75,.99)'
                }, opts.animationSpeed, $.proxy(callback, me));
            });

            me.$currentImage = $(me.$images[index]);

            me.$arrowLeft.toggleClass(opts.hiddenClass, !opts.loopSlides && index <= 0);
            me.$arrowRight.toggleClass(opts.hiddenClass, !opts.loopSlides && index >= me.itemCount - 1);
        },

        /**
         * Resets the current image transformation (scale and translation).
         * Can also be animated.
         *
         * @public
         * @method resetTransformation
         * @param {Boolean} animate
         * @param {Function} callback
         */
        resetTransformation: function (animate, callback) {
            var me = this,
                translation = me.imageTranslation;

            me.touchDistance = 0;

            if (me.imageScale !== 1 || translation.x !== 0 || translation.y !== 0) {

                me.imageScale = 1;

                me.imageTranslation.set(0, 0);

                me.updateTransform(animate, callback);

            } else if (callback) {
                callback.call(me);
            }
        },

        /**
         * Slides the image slider one position forward.
         *
         * @public
         * @method slideNext
         */
        slideNext: function () {
            var me = this,
                newIndex = me.slideIndex + 1;

            if (newIndex >= me.itemCount) {
                if (!me.opts.loopSlides) {
                    return;
                }

                newIndex = 0;
            }

            me.slide(newIndex);

            $.publish('plugin/imageSlider/slideNext');
        },

        /**
         * Slides the image slider one position backwards.
         *
         * @public
         * @method slidePrev
         */
        slidePrev: function () {
            var me = this,
                newIndex = me.slideIndex - 1;

            if (newIndex < 0) {
                if (!me.opts.loopSlides) {
                    return;
                }

                newIndex = me.itemCount - 1;
            }

            me.slide(newIndex);

            $.publish('plugin/imageSlider/slidePrev');
        },

        /**
         * Destroys the plugin and removes
         * all elements created by the plugin.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                opts = me.opts;

            me.$slideContainer = null;
            me.$slides = null;
            me.$currentImage = null;

            if (opts.dotNavigation && me.$dots) {
                me.$dots.removeClass(me.opts.activeStateClass);
                me.$dotNav = null;
                me.$dots = null;
            }

            if (opts.arrowControls) {
                me.$arrowLeft.remove();
                me.$arrowRight.remove();
            }

            if (opts.thumbnails) {
                me.$thumbnailArrowPrev.remove();
                me.$thumbnailArrowNext.remove();

                me.$thumbnailContainer = null;
                me.$thumbnailSlide = null;

                me.$thumbnails.removeClass(me.opts.activeStateClass);
                me.$thumbnails = null;

                StateManager.off('resize', me.onResize, me);
            }

            if (opts.autoSlide) {
                me.stopAutoSlide();
            }

            me.resetTransformation(false);

            me._destroy();
        }
    });

    /**
     * Helper Class to manager coordinates of X and Y pair values.
     *
     * @class Vector
     * @constructor
     * @param {Number} x
     * @param {Number} y
     */
    function Vector(x, y) {
        var me = this;

        me.x = x || 0;
        me.y = y || 0;
    }

    /**
     * Sets the X and Y values.
     * If one of the passed parameter is not a number, it
     * will be ignored.
     *
     * @public
     * @method set
     * @param {Number} x
     * @param {Number} y
     */
    Vector.prototype.set = function (x, y) {
        var me = this;

        me.x = (typeof x === 'number') ? x : me.x;
        me.y = (typeof y === 'number') ? y : me.y;
    };
})(jQuery, Modernizr, window, Math);