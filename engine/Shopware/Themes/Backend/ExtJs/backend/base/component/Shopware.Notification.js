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
 * @package    Base
 * @subpackage Component
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Central Notification
 *
 * This class represents all used user response elements and notifications.
 *
 * The notifications are based on Twitter's bootstrap CSS toolkit (http://twitter.github.com/bootstrap/)
 * and are placed in the upper right corner of the user's viewport (except the block messages).
 */
Ext.define('Shopware.Notification', {
    extend: 'Ext.app.Controller',
    singleton: true,

    alternateClassName: [ 'Shopware.Messages', 'Shopware.Msg' ],

    requires: [ 'Ext.container.Container', 'Ext.panel.Panel', 'Ext.XTemplate' ],

    /**
     * Default type of the alert and block messages.
     *
     * Types include:
     * - notice: yellow message (default)
     * - info: blue message
     * - success: green message
     * - error: red message
     *
     * @string
     */
    baseMsgType: 'notice',

    /**
     * Duration after the alert and growl messages are hide (ms)
     *
     * @integer
     */
    hideDelay: 1800,

    /**
     * Used easing type for the fade in and fade out animation
     *
     * @string
     */
    easingType: 'easeIn',

    /**
     * Default animation speed for the alert and growl messages (ms)
     *
     * @integer
     */
    animationDuration: 200,

    /**
     * Default width of the alert messages (px)
     *
     * @integer
     */
    alertWidth: 350,

    /**
     * Default CSS class of the alert messsages.
     *
     * @string
     */
    alertMsgCls: 'alert-message',

    /**
     * Default CSS class of the block messages.
     *
     * @string
     */
    blockMsgCls: 'block-message',

    /**
     * Default CSS class of the growl messages.
     *
     * @string
     */
    growlMsgCls: 'growl-msg',

    /**
     * Collects the available growl message to display them among each other.
     *
     * @Ext.util.MixedCollection
     */
    growlMsgCollection: Ext.create('Ext.util.MixedCollection'),

    /**
     * Default offset for the growl message, usally set to the height of the menu bar.
     *
     * @integer
     */
    offsetTop: 50,

    /**
     * XTemplate for the alert message
     *
     * @array
     */
    alertMsgTpl: [
        '{literal}<tpl for=".">',
            '<div class="{type}">',
                '<tpl if="closeBtn">',
                    '<a href="#" class="close close-alert">x</a>',
                '</tpl>',
                '<p>',
                    '<tpl if="title">',
                        '<strong>{title}</strong>&nbsp;',
                    '</tpl>',
                    '{text}',
                '</p>',
            '</div>',
        '</tpl>{/literal}'
    ],

    /**
     * XTemplate for the block message
     *
     * @array
     */
    blockMsgTpl: [
        '{literal}<tpl for=".">',
            '<p>',
                '{text}',
            '</p>',
        '</tpl>{/literal}'
    ],

    /**
     * XTemplate for the growl messages
     *
     * @array
     */
    growlMsgTpl: [
        '{literal}<tpl for=".">',
            '<div class="growl-icon {iconCls}"></div>',
            '<div class="alert">',
                '<tpl if="title">',
                    '<div class="title">{title}</div>',
                '</tpl>',
                '<p class="text">{text}</p>',
            '</div>',
        '</tpl>{/literal}'
    ],

    /**
     * RegEx of the valid types for the alert and block messages
     *
     * @private
     * @string
     */
    _validTypes: /(notice|info|success|error)/i,

    closeText: 'Schließen',

    /**
     * Sets the default type of the alert and block message.
     *
     * @param [string] type
     * @return [boolean]
     */
    setBaseMsgType: function(type) {
        if(!this.validBaseMsgType(type)) {
            return false;
        }

        this.baseMsgType = type;
        return true;
    },

    /**
     * Returns the default type of the alert and block message
     *
     * @return [string]
     */
    getBaseMsgType: function() {
        return this.baseMsgType;
    },

    /**
     * Checks if the passed message type is allowed
     *
     * @param [string] type
     * @return [null|string]
     */
    validBaseMsgType: function(type) {
        return type.match(this._validTypes);
    },

    /**
     * Sets the CSS class which is used by the alert message
     *
     * @param [string] cls - CSS class which is used by the alert messages
     */
    setAlertMsgCls: function(cls) {
      this.alertMsgCls = cls;
    },

    /**
     * Returns the CSS class of the alert message
     *
     * @return [string]
     */
    getAlertMsgCls: function() {
        return this.alertMsgCls;
    },

    /**
     * Sets the CSS class which is used by the block message
     *
     * @param [string] cls - CSS class which is used by the block messages
     */
    setBlockMsgCls: function(cls) {
      this.blockMsgCls = cls;
    },

    /**
     * Returns the CSS class of the block message
     *
     * @return [string]
     */
    getBlockMsgCls: function() {
        return this.blockMsgCls;
    },

    /**
     * Sets the CSS class which is used by the growl message
     *
     * @param [string] cls - CSS class which is used by the growl like messages
     */
    setGrowlMsgCls: function(cls) {
        this.growlMsgCls = cls;
    },

    /**
     * Returns the CSS class of the growl message
     *
     * @return [string]
     */
    getGrowlMsgCls: function() {
        return this.growlMsgCls;
    },

    /**
     * Creates an alert message based on the passed parameter's
     * and returns it
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [string] type - type of the message (see baseMsgType)
     * @param [boolean] closeBtn - show or hide close button<
     * @return [object] - Instance of the alert message
     */
    createMessage: function(title, text, type, closeBtn) {
        var me = this, alertMsg, msgData;

        if(!me.validBaseMsgType(type)) {
            type = false;
        }

        // Collect message data
        msgData = {
            title: title || false,
            text: text,
            type: type || this.baseMsgType,
            closeBtn: closeBtn || false
        };

        // Create message box
        alertMsg = Ext.create('Ext.container.Container', {
            ui: [ 'default', 'shopware-ui' ],
            data: msgData,
            cls: me.alertMsgCls,
            tpl: me.alertMsgTpl,
            width: me.alertWidth,
            renderTo: Ext.getBody(),
            style: 'opacity: 0'
        });
        alertMsg.update(msgData);

        // Fade out the alert message after the given delay
        var task = new Ext.util.DelayedTask(function() {
            me.closeAlertMessage(alertMsg, me, null);
        });
        task.delay(this.hideDelay);

        // Add close event to the close button
        if(closeBtn) {
            Ext.getBody().on('click', function(event) {
                me.closeAlertMessage(this, me, task);
            }, alertMsg, {
                delegate: '.close-alert'
            });
        }

        // Show the alert message
        alertMsg.getEl().fadeIn({
            opacity: 1,
            easing: me.easingType,
            duration: me.animationDuration
        });

        return alertMsg;
    },

    /**
     * Creates an error message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createErrorMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'error', closeBtn);
    },

    /**
     * Creates a success message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createSuccessMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'success', closeBtn);
    },

    /**
     * Creates a notice message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createNoticeMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'notice', closeBtn);
    },

    /**
     * Creates an info message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createInfoMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'info', closeBtn);
    },

    /**
     * Fades out the passed alert message and removes it from the DOM if the animation
     * is complete.
     *
     * @param [object] alertMsg - Instance of the alert message
     * @param [object] scope - Shopware.app.Notification
     * @param [object] task - Ext.util.DelayedTask
     * @return [boolean]
     */
    closeAlertMessage: function(alertMsg, scope, task) {
        if(task && Ext.isObject(task)) {
            task.cancel();
        }
        alertMsg.getEl().fadeOut({
            remove: true,
            easing: scope.easingType,
            duration: scope.animationDuration
        });

        return true;
    },

    /**
     * Creates a block message based on the passed parameter's
     * and returns it
     *
     * @param [string] title - Title of the message
     * @param [string] text - Text of the message (HTML allowed)
     * @param [string] type - Type of the message (default: notice, possible values: info = blue, notice = yellow, success = green, error = red)
     * @param [boolean] closeBtn - show or hide the close button
     * @return [object] - Instance of the block message
     */
    createBlockMessage: function(text, type) {
        var me = this, pnl, msgData, innerPnl;

        if(!me.validBaseMsgType(type)) {
            type = me.baseMsgType;
        }

        msgData = {
            text: text,
            type: type || me.baseMsgType
        };

        innerPnl = Ext.create('Ext.container.Container', {
            cls: [ me.blockMsgCls + '-inner' , type || me.baseMsgType ] ,
            data: msgData,
            margin: 1,
            padding: 7,
            plain: true,
            tpl: me.blockMsgTpl
        });

        pnl = Ext.create('Ext.container.Container', {
            cls: me.blockMsgCls  ,
            ui: 'shopware-ui',
            bodyCls: type || me.baseMsgType,
            items: [ innerPnl ]
        });
        innerPnl.update(msgData);

        return pnl;
    },

    /**
     * Creates a growl like message based on the passed parameter's
     * and returns it
     *
     * @param [string] title - Title of the message
     * @param [string] text - Text of the message
	 * @param [string] caller - The module, which called this function
     * @param [string] iconCls - Used icon class (default growl)
     * @param [boolean] log - If the growlMessage should be logged
     */
    createGrowlMessage: function(title, text, caller, iconCls, log) {
        var me = this, msgData, growlMsg, id = Ext.id(), compTop = me.offsetTop

		if(log != false){
			Ext.Ajax.request({
				url: '{url controller="Log" action="createLog"}',
				params: {
					type: 'backend',
					key: caller,
					text: text,
					user: userName,
					value4: ''
				},
				scope:this
			});
		}

        // Collect message data
        msgData = {
            title: title || false,
            text: text,
            iconCls: iconCls || 'growl'
        };

        me.growlMsgCollection.each(function(growlEl) {
            compTop += growlEl.height + 6;
        });

        // Create message box
        growlMsg = Ext.create('Ext.panel.Panel', {
            ui: [ 'default', 'shopware-ui' ],
            data: msgData,
            id: id,
            unstyled: true,
            cls: me.growlMsgCls,
            tpl: me.growlMsgTpl,
            renderTo: Ext.getBody()
        });
        growlMsg.update(msgData);
        growlMsg.getEl().setStyle({
            'opacity': 1,
            'left': Ext.Element.getViewportWidth() - 308 + 'px',
            'top': compTop + 'px'
        });

        // Fade out the growl like message after the given delay
        var task = new Ext.util.DelayedTask(function() {
            me.closeGrowlMessage(growlMsg, me, task);
        });

        task.delay(this.hideDelay + (text.length * 35));

        me.growlMsgCollection.add(id, { el: growlMsg, height: growlMsg.getHeight(), sticky: false });
        return growlMsg;
    },

    /**
     * Creates a sticky growl like message. The note must be closed by the user. The messages
     * will be displayed among each other.
     *
     * @example
     * Shopware.Notification.createStickyGrowlMessage({
     *     title: 'Growl Sticky Test',
     *     text: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor.',
     *     log: false,
     *     btnDetail: {
     *         link: 'http://wiki.shopware.de'
     *     }
     * });
     *
     * @this Shopware.Notification
     * @param { Object }  opts - Configuration object (required)
     *           { String }   opts.title - Title of the message (required)
     *           { String }   opts.text - Text of the message (required)
     *           { Integer }  opts.width - Width of the message in pixel
     *           { Boolean }  opts.log - Log message to display it in the log module (default: "false")
     *           { Object }   opts.scope - Scope in which the callback will be fired (default: this)
     *           { Function } opts.callback - Callback method which should be called after the message was closed.
     *                        (default: Ext.emptyFn)
     *           { Object }   opts.btnDetail - Configuration object for the detail button
     *              { String }   opts.btnDetail.text - Button text (default: "Details aufrufen")
     *              { Boolean }  opts.btnDetail.autoClose - Close the message after the user clicks the detail button
     *                           (default: true)
     *              { String }   opts.btnDetail.link - URL which will be opened when the user clicks the link
     *              { String }   opts.btnDetail.target - Target for the link (default: "_target")
     *              { Function } opts.btnDetail.callback - Callback method which should be called after the user
     *                           links on the detail link (default: Ext.emptyFn)
     *              { Object }   opts.btnDetail.scope - Scope in which the callback will be fired (default: this)
     *        { Function } caller - Function which calls this method. Only necessary for the logging.
     *        { String }   iconCls - CSS class for the icon which should be displayed. This options is disabled.
     *        { Boolean }  log - Compability parameter. Please use `opts.log` instead of the parameter `log`
     *
     * @returns { Ext.panel.Panel } Generated container for the growl message
     */
    createStickyGrowlMessage: function(opts, caller, iconCls, log) {
        var me = this, msgData, growlMsg, growlContent, btnContent, closeCB, detailCB, autoClose, closeHandler,
            target = '_blank', width = 300, id = Ext.id(), compTop = me.offsetTop;

        log = log || false;
        target = (opts.btnDetail && opts.btnDetail.target) ? opts.btnDetail.target : target;
        width = opts.width || width;
        closeCB = opts.callback || Ext.emptyFn;
        detailCB = (opts.btnDetail && opts.btnDetail.callback) ? opts.btnDetail.callback : Ext.emptyFn;
        autoClose = (opts.btnDetail && opts.btnDetail.autoClose !== undefined) ? opts.btnDetail.autoClose : true;

        if(log !== false || opts.log !== false) {
            Ext.Ajax.request({
                url: '{url controller="Log" action="createLog"}',
                params: {
                    type: 'backend',
                    key: caller,
                    text: opts.text,
                    user: userName,
                    value4: ''
                },
                scope: this
            });
        }

        // Collect message data
        msgData = {
            title: opts.title || false,
            text: opts.text,
            iconCls: iconCls || 'growl'
        };

        btnContent = Ext.create('Ext.container.Container', {
            cls: me.growlMsgCls + '-btn-content',
            flex: 2,
            layout: {
                type: 'vbox',
                align: 'stretch',
                pack:'center'
            }
        });

        // Content area
        growlContent = Ext.create('Ext.container.Container', {
            data: msgData,
            cls: me.growlMsgCls + '-sticky-content',
            tpl: me.growlMsgTpl,
            maxHeight: 120,
            autoScroll: true,
            flex: 3
        });
        growlContent.update(msgData);

        // Global container
        growlMsg = Ext.create('Ext.panel.Panel', {
            unstyled: true,
            id: id,
            width: width,
            ui: [ 'default', 'shopware-ui' ],
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            cls: me.growlMsgCls + ' ' + me.growlMsgCls +  '-sticky-notification',
            renderTo: document.body,
            items: [ growlContent, btnContent ]
        });

        closeHandler = function() {
            me.closeGrowlMessage(growlMsg, me);
            closeCB.apply(opts.scope || me, [ growlMsg, msgData ]);
        };

        // Add detail button
        if(opts.btnDetail && opts.btnDetail.link) {
            btnContent.add({
                xtype: 'button',
                height: 22,
                ui: 'growl-sticky',
                text: opts.btnDetail.text || 'Details aufrufen',
                handler: function() {
                    window.open(opts.btnDetail.link, target);
                    detailCB.apply(opts.btnDetail.scope || me, [ growlMsg, msgData ]);

                    if(autoClose) {
                        closeHandler();
                    }
                }
            });
        }

        // Add close button
        btnContent.add({
            xtype: 'button',
            ui: 'growl-sticky',
            text: me.closeText,
            height: 22,
            handler: closeHandler
        });

        me.growlMsgCollection.each(function(growlEl) {
            compTop += growlEl.height + 6;
        });

        // Animate it
        growlMsg.getEl().setStyle({
            'opacity': 1,
            'left': Ext.Element.getViewportWidth() - (width + 8) + 'px',
            'top': compTop + 'px'
        });

        me.growlMsgCollection.add(id, { el: growlMsg, height: growlContent.getHeight() + 26, sticky: true });

        return growlMsg;
    },

    /**
     * Fades out the passed growl like message and removes
     * it from the DOM
     *
     * @param [object] msg - Instance of the growl message
     * @param [object] scope - Instance of Shopware.app.Notification
     * @param [object] task - Instance of Ext.util.DelayedTask
     * @return [boolean]
     */
    closeGrowlMessage: function(msg, scope, task) {
        var pos = -1;

        if(task && Ext.isObject(task)) {
            task.cancel();
        }

        msg.getEl().setStyle('opacity', 0);
        Ext.defer(function() {
            msg.destroy();
            scope.growlMsgCollection.removeAtKey(msg.id);
        }, 210);

        scope.growlMsgCollection.each(function(growlMsg, i) {
            if(growlMsg.el.id === msg.id) {
                pos = i;
            }

            if(pos > -1 && pos !== i) {
                var top = scope.growlMsgCollection.getAt(pos).height;
                top = top + ((scope.growlMsgCollection.items.length - 2) * 6);
                growlMsg.el.animate({
                    to: { top: growlMsg.el.getPosition()[1] - (top < 50 ? 50 : top) + 'px' }
                }, 50);
            }
        });

        return true;
    }
});