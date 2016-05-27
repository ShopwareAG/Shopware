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

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Kernel;
use Shopware\Models\Plugin\Plugin;
use Shopware\Components\Plugin\FormSynchronizer;
use Shopware\Components\Plugin\MenuSynchronizer;
use Shopware\Components\Plugin\XmlMenuReader;
use Shopware\Components\Plugin\XmlConfigDefinitionReader;
use Shopware\Components\Plugin\XmlPluginInfoReader;

class PluginInstaller implements PluginInstallerInterface
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ModelManager $em
     */
    public function __construct(ModelManager $em)
    {
        $this->em = $em;
        $this->connection = $this->em->getConnection();
    }

    /**
     * @inheritdoc
     */
    public function installPlugin(Plugin $plugin)
    {
        /** @var Kernel $kernel */
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        $this->em->transactional(function ($em) use ($pluginBootstrap, $plugin) {

            if (is_file($pluginBootstrap->getPath().'/config.xml')) {
                $this->installForm($plugin, $pluginBootstrap->getPath().'/config.xml');
            }

            if (is_file($pluginBootstrap->getPath().'/menu.xml')) {
                $this->installMenu($plugin, $pluginBootstrap->getPath().'/menu.xml');
            }

            $pluginBootstrap->install();

            $plugin->setInstalled(new \DateTime());
            $plugin->setUpdated(new \DateTime());

            $this->em->flush($plugin);
        });

        return true;
    }

    /**
     * @param Plugin $plugin
     * @param string $file
     */
    private function installForm(Plugin $plugin, $file)
    {
        $xmlConfigReader = new XmlConfigDefinitionReader();
        $config = $xmlConfigReader->read($file);

        $formSynchronizer = new FormSynchronizer($this->em);
        $formSynchronizer->synchronize($plugin, $config);
    }

    /**
     * @param Plugin $plugin
     * @param string $file
     */
    private function installMenu(Plugin $plugin, $file)
    {
        $menuReader = new XmlMenuReader();
        $menu = $menuReader->read($file);

        $menuSynchronizer = new MenuSynchronizer($this->em);
        $menuSynchronizer->synchronize($plugin, $menu);
    }

    /**
     * @inheritdoc
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true)
    {
        $plugin->setInstalled(null);
        $plugin->setActive(false);

        $this->em->flush($plugin);

        $pluginId = $plugin->getId();

        $this->removeEventSubscribers($pluginId);
        $this->removeCrontabEntries($pluginId);
        $this->removeMenuEntries($pluginId);
        $this->removeTemplates($pluginId);
        $this->removeFormsAndElements($pluginId);
        $this->removeEmotionComponents($pluginId);
    }

    /**
     * @inheritdoc
     */
    public function updatePlugin(Plugin $plugin)
    {
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        $this->em->transactional(function ($em) use ($pluginBootstrap, $plugin) {
            $currentVersion = $plugin->getVersion();
            $updateVersion  = $plugin->getUpdateVersion();

            if (is_file($pluginBootstrap->getPath().'/config.xml')) {
                $this->installForm($plugin, $pluginBootstrap->getPath().'/config.xml');
            }

            if (is_file($pluginBootstrap->getPath().'/menu.xml')) {
                $this->installMenu($plugin, $pluginBootstrap->getPath().'/menu.xml');
            }

            $pluginBootstrap->update($currentVersion, $updateVersion);


            $plugin->setVersion($updateVersion);
            $plugin->setUpdateVersion(null);
            $plugin->setUpdateSource(null);
            $plugin->setUpdated(new \DateTime());

            $this->em->flush($plugin);
        });
    }

    /**
     * @inheritdoc
     */
    public function activatePlugin(Plugin $plugin)
    {
        $plugin->setActive(true);
        $this->em->flush($plugin);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deactivatePlugin(Plugin $plugin)
    {
        $plugin->setActive(false);
        $this->em->flush($plugin);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function refreshPluginList(\DateTimeInterface $refreshDate)
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');
        $plugins = $kernel->getPlugins();

        foreach ($plugins as $plugin) {
            if (!is_file($plugin->getPath().'/plugin.xml')) {
                continue;
            }

            $xmlConfigReader = new XmlPluginInfoReader();
            $updatePluginInfo = $xmlConfigReader->read($plugin->getPath().'/plugin.xml');

            $currentPluginInfo = $this->em->getConnection()->fetchAssoc(
                'SELECT * FROM s_core_plugins WHERE `name` LIKE ?',
                [$plugin->getName()]
            );

            $description = '';
            if (isset($updatePluginInfo['description'])) {
                foreach ($updatePluginInfo['description'] as $locale => $string) {
                    $description .= sprintf('<div lang="%s">%s</div>', $locale, $string);
                }
            }

            $data = [
                'namespace'          => 'ShopwarePlugins',
                'version'            => $updatePluginInfo['version'],
                'author'             => $updatePluginInfo['author'],
                'name'               => $plugin->getName(),
                'link'               => $updatePluginInfo['link'],
                'label'              => $updatePluginInfo['label']['en'],
                'description'        => $description,
                'capability_update'  => true,
                'capability_install' => true,
                'capability_enable'  => true,
                'refresh_date'       => $refreshDate
            ];

            if ($currentPluginInfo) {
                if ($this->hasInfoNewerVersion($updatePluginInfo['version'], $currentPluginInfo['version'])) {
                    $data['version']        = $currentPluginInfo['version'];
                    $data['update_version'] = $updatePluginInfo['version'];
                }

                $this->em->getConnection()->update(
                    's_core_plugins',
                    $data,
                    ['id' => $currentPluginInfo['id']],
                    ['refresh_date' => 'datetime']
                );
            } else {
                $data['added'] = $refreshDate;
                $this->em->getConnection()->insert(
                    's_core_plugins',
                    $data,
                    [
                        'added'        => 'datetime',
                        'refresh_date' => 'datetime',
                    ]
                );
            }
        }
    }

    /**
     * @param string $updateVersion
     * @param string $currentVersion
     * @return boolean
     */
    private function hasInfoNewerVersion($updateVersion, $currentVersion)
    {
        return version_compare($updateVersion, $currentVersion, '>');
    }

    /**
     * @param Plugin $plugin
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        /** @var Kernel $kernel */
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        return $pluginBootstrap->getPath();
    }

    /**
     * @param string $pluginName
     * @return \Shopware\Components\Plugin
     */
    private function getPluginByName($pluginName)
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');
        $plugins = $kernel->getPlugins();

        if (!isset($plugins[$pluginName])) {
            throw new \InvalidArgumentException(sprintf('Plugin by name "%s" not found.', $pluginName));
        }

        return $plugins[$pluginName];
    }

    /**
     * @param int $pluginId
     */
    private function removeEmotionComponents($pluginId)
    {
        // Remove emotion-components
        $sql = "DELETE s_emotion_element_value, s_emotion_element
                FROM s_emotion_element_value
                RIGHT JOIN s_emotion_element
                    ON s_emotion_element.id = s_emotion_element_value.elementID
                INNER JOIN s_library_component
                    ON s_library_component.id = s_emotion_element.componentID
                    AND s_library_component.pluginID = :pluginId";

        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);

        $sql = "DELETE s_library_component_field, s_library_component
                FROM s_library_component_field
                INNER JOIN s_library_component
                    ON s_library_component.id = s_library_component_field.componentID
                    AND s_library_component.pluginID = :pluginId";

        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     */
    private function removeFormsAndElements($pluginId)
    {
        $sql = <<<SQL
DELETE s_core_config_forms, s_core_config_form_translations, s_core_config_elements, s_core_config_element_translations, s_core_config_values
FROM s_core_config_forms
LEFT JOIN s_core_config_form_translations ON s_core_config_form_translations.form_id = s_core_config_forms.id
LEFT JOIN s_core_config_elements ON s_core_config_elements.form_id = s_core_config_forms.id
LEFT JOIN s_core_config_element_translations ON s_core_config_element_translations.element_id = s_core_config_elements.id
LEFT JOIN s_core_config_values ON s_core_config_values.element_id = s_core_config_elements.id
WHERE s_core_config_forms.plugin_id = :pluginId
SQL;
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeTemplates($pluginId)
    {
        $sql = 'DELETE FROM s_core_templates WHERE plugin_id = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeMenuEntries($pluginId)
    {
        $sql = 'DELETE FROM s_core_menu WHERE pluginID = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeCrontabEntries($pluginId)
    {
        $sql = 'DELETE FROM s_crontab WHERE pluginID = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeEventSubscribers($pluginId)
    {
        $sql = 'DELETE FROM s_core_subscribes WHERE pluginID = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }
}
