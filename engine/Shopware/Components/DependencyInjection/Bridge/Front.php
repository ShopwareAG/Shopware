<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\DependencyInjection\Bridge;

use Shopware\Components\DependencyInjection\ResourceLoader;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Front
{
    /**
     * Loads the Zend resource and initials the Enlight_Controller_Front class.
     * After the front resource is loaded, the controller path is added to the
     * front dispatcher. After the controller path is set to the dispatcher,
     * the plugin namespace of the front resource is set.
     *
     * @param ResourceLoader $resourceLoader
     * @param \Shopware_Bootstrap $bootstrap
     * @param array $options
     * @throws \Exception
     * @return \Enlight_Controller_Front
     */
    public function factory(
        ResourceLoader $resourceLoader,
        \Shopware_Bootstrap $bootstrap,
        \Enlight_Event_EventManager $eventManager,
        array $options
    ) {
        /** @var $front \Enlight_Controller_Front */
        $front = \Enlight_Class::Instance('Enlight_Controller_Front', array($eventManager));

        $front->Dispatcher()->addModuleDirectory(
            Shopware()->AppPath('Controllers')
        );

        $front->setParams($options);

        /** @var $plugins  \Enlight_Plugin_PluginManager */
        $plugins = $resourceLoader->get('Plugins');

        $plugins->registerNamespace($front->Plugins());

        $front->setParam('bootstrap', $bootstrap);

        if (!empty($config['throwExceptions'])) {
            $front->throwExceptions(true);
        }
        if (!empty($config['returnResponse'])) {
            $front->returnResponse(true);
        }

        try {
            $resourceLoader->load('Cache');
            $resourceLoader->load('Db');
            $resourceLoader->load('Plugins');
        } catch (\Exception $e) {
            if ($front->throwExceptions()) {
                throw $e;
            }
            $front->Response()->setException($e);
        }

        return $front;
    }
}
