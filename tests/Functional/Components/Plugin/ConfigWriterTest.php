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

namespace Shopware\Tests\Functional\Components\Plugin;

use DateTime;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Configuration\ReaderInterface;
use Shopware\Components\Plugin\Configuration\WriterInterface;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

class ConfigWriterTest extends TestCase
{
    const PLUGIN_NAME = 'swConfigWriterPluginTest';

    const NUMBER_CONFIGURATION_NAME = 'numberConfiguration';

    const ELEMENT_DEFAULT_VALUE = 1;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var int
     */
    private $configElementId;

    /**
     * @var int
     */
    private $installationShopId;

    /**
     * @var int
     */
    private $subShopId;

    /**
     * @var int
     */
    private $languageShopId;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReaderInterface
     */
    private $configReader;

    public function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
        $this->modelManager = Shopware()->Container()->get('models');

        // setup plugin
        $this->connection->insert('s_core_plugins', [
            'namespace' => 'Core',
            'name' => self::PLUGIN_NAME,
            'label' => 'This is a config reader test plugin',
            'source' => 'php unit',
            'active' => 0,
            'added' => new DateTime(),
            'version' => '1.0.0',
            'capability_update' => 0,
            'capability_install' => 0,
            'capability_enable' => 1,
            'capability_secure_uninstall' => 1,
        ]);
        /** @var Plugin $plugin */
        $plugin = $this->modelManager->find(Plugin::class, $this->connection->lastInsertId());

        // setup plugin configuration
        $parentFormId = $this->connection
            ->executeQuery('SELECT id FROM s_core_config_forms WHERE `name` = ?', ['Core'])
            ->fetchColumn();

        $this->connection->insert('s_core_config_forms', [
            'name' => self::PLUGIN_NAME,
            'label' => 'This is a config reader test plugin',
            'position' => 0,
            'plugin_id' => $plugin->getId(),
            'parent_id' => $parentFormId,
        ]);
        $formId = $this->connection->lastInsertId();

        $this->connection->insert('s_core_config_elements', [
            'form_id' => $formId,
            'name' => self::NUMBER_CONFIGURATION_NAME,
            'value' => serialize(self::ELEMENT_DEFAULT_VALUE),
            'type' => 'number',
            'required' => 0,
            'position' => 0,
            'scope' => 1,
        ]);
        $this->configElementId = $this->connection->lastInsertId();

        // setup shops
        // assume shop by id 1 exists
        $this->installationShopId = 1;

        $this->connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
        ]);
        $this->subShopId = $this->connection->lastInsertId();

        $this->connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
            'main_id' => $this->subShopId,
        ]);
        $this->languageShopId = $this->connection->lastInsertId();

        $this->configWriter = Shopware()->Container()->get('shopware.plugin.configuration.writer');
        $this->configReader = Shopware()->Container()->get('shopware.plugin.configuration.reader');
    }

    public function tearDown()
    {
        $this->connection->rollBack();
        $this->connection = null;
        $this->configReader = null;
    }

    public function testWriteValueForInstallation()
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->installationShopId
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME),
            [self::NUMBER_CONFIGURATION_NAME => 2]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId),
            [self::NUMBER_CONFIGURATION_NAME => 2]
        );
    }

    public function testWriteValueForSubShop()
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->subShopId
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId),
            [self::NUMBER_CONFIGURATION_NAME => 2]
        );
    }

    public function testWriteValueForLanguageShop()
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->languageShopId
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShopId),
            [self::NUMBER_CONFIGURATION_NAME => 2]
        );
    }

    public function testWriteDefaultValueForSubShop()
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->installationShopId
        );

        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->subShopId
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId),
            [self::NUMBER_CONFIGURATION_NAME => 2]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );
    }

    public function testWriteDefaultValueForLanguageShop()
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->subShopId
        );

        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->languageShopId
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId),
            [self::NUMBER_CONFIGURATION_NAME => 2]
        );

        $this->assertArraySubset(
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShopId),
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE]
        );
    }
}
