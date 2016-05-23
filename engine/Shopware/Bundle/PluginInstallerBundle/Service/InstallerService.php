<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Components\Plugin\ConfigWriter;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

class InstallerService
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    private $pluginRepository;

    /**
     * @var \Shopware\Models\Shop\Repository
     */
    private $shopRepository;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var LegacyPluginInstaller
     */
    private $legacyPluginInstaller;

    /**
     * @var PluginInstaller
     */
    private $pluginInstaller;

    /**
     * @param ModelManager $em
     * @param PluginInstaller $pluginInstaller
     * @param LegacyPluginInstaller $legacyPluginInstaller
     * @param ConfigWriter $configWriter
     * @param ConfigReader $configReader
     */
    public function __construct(
        ModelManager $em,
        PluginInstaller $pluginInstaller,
        LegacyPluginInstaller $legacyPluginInstaller,
        ConfigWriter $configWriter,
        ConfigReader $configReader
    ) {
        $this->em = $em;
        $this->pluginInstaller = $pluginInstaller;
        $this->legacyPluginInstaller = $legacyPluginInstaller;
        $this->configWriter = $configWriter;
        $this->configReader = $configReader;
        $this->pluginRepository = $this->em->getRepository(Plugin::class);
        $this->shopRepository = $this->em->getRepository(Shop::class);
    }

    /**
     * @param string $pluginName
     * @return string
     * @throws \Exception
     */
    public function getPluginPath($pluginName)
    {
        $plugin = $this->getPluginByName($pluginName);

        if ($this->isNewPlugin($plugin)) {
            return $this->pluginInstaller->getPluginPath($plugin);
        } else {
            return $this->legacyPluginInstaller->getPluginPath($plugin);
        }
    }

    /**
     * @param string $pluginName
     * @throws \Exception
     * @return Plugin
     */
    public function getPluginByName($pluginName)
    {
        /** @var Plugin $plugin */
        $plugin = $this->pluginRepository->findOneBy(['name' => $pluginName, 'capabilityEnable' => 1]);

        if ($plugin === null) {
            throw new \Exception(sprintf('Unknown plugin: %s.', $pluginName));
        }

        return $plugin;
    }

    /**
     * Returns a certain plugin by plugin id.
     *
     * @param Plugin $plugin
     * @return \Shopware_Components_Plugin_Bootstrap|null
     */
    public function getPluginBootstrap(Plugin $plugin)
    {
        return $this->legacyPluginInstaller->getPluginBootstrap($plugin);
    }

    /**
     * @param Plugin $plugin
     * @return bool
     * @throws \Exception
     */
    public function installPlugin(Plugin $plugin)
    {
        if ($plugin->getInstalled()) {
            return true;
        }

        if ($this->isNewPlugin($plugin)) {
            return $this->pluginInstaller->installPlugin($plugin);
        } else {
            return $this->legacyPluginInstaller->installPlugin($plugin);
        }
    }

    /**
     * @param Plugin $plugin
     * @param bool $removeData
     * @return bool
     * @throws \Exception
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true)
    {
        if (!$plugin->getInstalled()) {
            return true;
        }

        if ($this->isNewPlugin($plugin)) {
            return $this->pluginInstaller->uninstallPlugin($plugin, $removeData);
        } else {
            return $this->legacyPluginInstaller->uninstallPlugin($plugin, $removeData);
        }
    }

    /**
     * @param Plugin $plugin
     * @return bool
     * @throws \Exception
     */
    public function updatePlugin(Plugin $plugin)
    {
        if (!$plugin->getUpdateVersion()) {
            return true;
        }

        if ($this->isNewPlugin($plugin)) {
            return $this->pluginInstaller->updatePlugin($plugin);
        } else {
            return $this->legacyPluginInstaller->updatePlugin($plugin);
        }
    }

    /**
     * @param Plugin $plugin
     * @return array|bool|void
     * @throws \Exception
     */
    public function activatePlugin(Plugin $plugin)
    {
        if ($plugin->getActive()) {
            return true;
        }

        if (!$plugin->getInstalled()) {
            throw new \Exception('Plugin has to be installed first.');
        }

        if ($this->isNewPlugin($plugin)) {
            return $this->pluginInstaller->activatePlugin($plugin);
        } else {
            return $this->legacyPluginInstaller->activatePlugin($plugin);
        }
    }

    /**
     * @param Plugin $plugin
     * @return array|bool|void
     * @throws \Exception
     */
    public function deactivatePlugin(Plugin $plugin)
    {
        if (!$plugin->getActive()) {
            return true;
        }

        if ($this->isNewPlugin($plugin)) {
            return $this->pluginInstaller->deactivatePlugin($plugin);
        } else {
            return $this->legacyPluginInstaller->deactivatePlugin($plugin);
        }
    }

    /**
     * @param Plugin $plugin
     * @param Shop $shop
     * @return array
     */
    public function getPluginConfig(Plugin $plugin, Shop $shop = null)
    {
        return $this->configReader->getByPluginName($plugin->getName(), $shop);
    }

    /**
     * @param Plugin $plugin
     * @param array $elements
     * @param Shop $shop
     */
    public function savePluginConfig(Plugin $plugin, $elements, Shop $shop = null)
    {
        if ($shop === null) {
            /** @var Shop $shop */
            $shop = $this->shopRepository->find($this->shopRepository->getActiveDefault()->getId());
        }

        $this->configWriter->savePluginConfig($plugin, $elements, $shop);
    }

    /**
     * @param Plugin $plugin
     * @param string $name
     * @param mixed $value
     * @param Shop $shop
     * @throws \Exception
     */
    public function saveConfigElement(Plugin $plugin, $name, $value, Shop $shop = null)
    {
        if ($shop === null) {
            /** @var Shop $shop */
            $shop = $this->shopRepository->find($this->shopRepository->getActiveDefault()->getId());
        }

        $this->configWriter->saveConfigElement($plugin, $name, $value, $shop);
    }

    public function refreshPluginList()
    {
        $refreshDate = new \DateTimeImmutable();

        $this->pluginInstaller->refreshPluginList($refreshDate);
        $this->legacyPluginInstaller->refreshPluginList($refreshDate);

        $this->cleanupPlugins($refreshDate);
    }

    /**
     * @param \DateTimeInterface $refreshDate
     */
    private function cleanupPlugins(\DateTimeInterface $refreshDate)
    {
        $sql = 'SELECT id FROM s_core_plugins WHERE refresh_date < ?';
        $pluginIds = $this->em->getConnection()->fetchAll($sql, [$refreshDate], ['datetime']);
        $pluginIds = array_column($pluginIds, 'id');
        foreach ($pluginIds as $pluginId) {
            $plugin = $this->pluginRepository->find($pluginId);
            $this->em->remove($plugin);
        }
        $this->em->flush();
    }

    /**
     * @param Plugin $plugin
     * @return bool
     */
    private function isNewPlugin(Plugin $plugin)
    {
        return $plugin->getNamespace() === "ShopwarePlugins";
    }
}
