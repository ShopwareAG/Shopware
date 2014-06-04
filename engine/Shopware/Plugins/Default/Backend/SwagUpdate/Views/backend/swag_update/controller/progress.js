/**
 * Shopware 4
 * Copyright © shopware AG
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
 */

//{namespace name=backend/swag_update/main}
//{block name="backend/swag_update/controller/progress"}
Ext.define('Shopware.apps.SwagUpdate.controller.Progress', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;

        me.control({
            'update-main-progress': {
                startProcess: me.onStartProcess,
                cancelProcess: me.onCancelProcess,
                closeWindow: me.onCloseWindow
            }
        });

        me.callParent(arguments);
    },

    onStartProcess: function(win) {
        var me = this;

        var configs = [
            {
                url: '{url controller="SwagUpdate" action="download"}',
                formatFnct: function(offset, total) {
                    if (total > 0) {
                        return '{s name=progress/downloading}Downloading{/s} ' + (offset / total * 100).toFixed(0) + '%';
                    } else {
                        return '{s name=progress/downloading}Downloading{/s} 0%';
                    }
                }
            },
            {
                url: '{url controller="SwagUpdate" action="unpack"}',
                formatFnct: function(offset, total) {
                    if (total > 0) {
                        return '{s name=progress/unpacking}Unpacking{/s} ' + (offset / total * 100).toFixed(0) + '%';
                    } else {
                        return '{s name=progress/unpacking}Unpacking{/s} 0%';
                    }
                }
            }
        ];

        me.runRequest(0, win, configs.shift(), configs);
    },

    /**
     * This function sends a request to generate new thumbnails
     *
     * @param offset
     * @param win
     * @param config
     * @param configs
     */
    runRequest: function (offset, win, config, configs) {
        var me = this;

        if (offset == 0) {
            win.progressBar.updateProgress(
                0,
                config.formatFnct(0, 0)
            );
        }

        me.errors = me.errors || [];
        // if cancel button was pressed
        if (me.cancelOperation) {
            win.cancelButton.hide();
            win.closeButton.enable();
            return;
        }

        var params = {
            'offset': offset
        };

        // Sends a request to create new thumbnails according to the batch informations
        Ext.Ajax.request({
            url: config.url,
            method: 'GET',
            params: params,
            timeout: 4000000,
            success: function (response) {
                var operation = Ext.decode(response.responseText);
                if (operation.success !== true) {
                    alert("Some error occured");
                    return;
                }

                win.progressBar.updateProgress(
                    operation.offset / operation.total,
                    config.formatFnct(operation.offset, operation.total)
                );

                if (operation.valid === true) {
                    me.runRequest(operation.offset, win, config, configs);
                } else {
                    config = configs.shift();
                    if (config) {
                        win.progressBar.updateProgress(
                            0,
                            config.formatFnct(0, 0)
                        );
                        me.runRequest(0, win, config, configs);
                    } else {
                        win.progressBar.updateProgress(1, '{s name=progress/finish}Finished{/s}');
                        me.onProcessFinish(win);
                    }
                }
            },
            failure: function (response) {
                Shopware.Msg.createStickyGrowlMessage({
                    title: '{s name=progress/timeOutTitle}An error occured{/s}',
                    text: "{s name=progress/timeOut}The server could not handle the request. Please choose a smaller batch size.{/s}"
                });

                me.onProcessFailure(win);
            }
        });
    },

    /**
     * @param win
     */
    onProcessFailure: function (win) {
        var me = this;

        win.cancelButton.hide();
        win.closeButton.enable();
    },

    /**
     * @param win
     */
    onProcessFinish: function (win) {
        var me = this;

        win.cancelButton.hide();
        win.closeButton.enable();

        window.location.href = '{url controller="SwagUpdate" action="startUpdate"}';
    },

    /**
     * Sets cancelOperation to true which will be checked in the
     * next batch call and will stop.
     *
     * @param btn
     */
    onCancelProcess: function (btn) {
        var me = this;

        btn.disable();

        me.cancelOperation = true;
    },

    onCloseWindow: function(win) {
        win.destroy();
    }
});

//{/block}
