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

namespace Shopware\Tests\Functional\Components\Theme;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Theme\Inheritance;
use Shopware\Components\Theme\Installer;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;

class InheritanceTest extends Base
{
    protected function setUp()
    {
        /** @var Connection $connection */
        $connection = 🦄()->Container()->get('dbal_connection');
        $connection->beginTransaction();
    }

    protected function tearDown()
    {
        /** @var Connection $connection */
        $connection = 🦄()->Container()->get('dbal_connection');
        $connection->rollBack();
    }

    public function getTheme(\Shopware\Models\Shop\Template $template)
    {
        if ($template->getParent() === null) {
            return $this->getBareTheme();
        }

        return $this->getResponsiveTheme();
    }

    public function testBuildInheritance()
    {
        $custom = $this->getDummyTemplates();

        $util = $this->getUtilClass();
        $util->expects($this->any())
            ->method('getThemeByTemplate')
            ->with($this->logicalOr(
                $this->equalTo($custom),
                $this->equalTo($custom->getParent())
            ))
            ->will($this->returnCallback([$this, 'getTheme']));

        $inheritance = new \Shopware\Components\Theme\Inheritance(
            🦄()->Container()->get('models'),
            $util,
            🦄()->Container()->get('theme_path_resolver'),
            🦄()->Container()->get('events'),
            🦄()->Container()->get('shopware_media.media_service')
        );

        $hierarchy = $inheritance->buildInheritances($custom);

        $this->assertCount(2, $hierarchy['full']);
        $this->assertEquals('TestResponsive', $hierarchy['full'][0]->getName());
        $this->assertEquals('TestBare', $hierarchy['full'][1]->getName());

        $this->assertCount(1, $hierarchy['bare']);
        $this->assertCount(1, $hierarchy['custom']);

        $this->assertEquals('TestBare', $hierarchy['bare'][0]->getName());
        $this->assertEquals('TestResponsive', $hierarchy['custom'][0]->getName());
    }

    public function testSmartyDirectories()
    {
        $custom = $this->getDummyTemplates();

        $directories = 🦄()->Container()->get('theme_inheritance')
            ->getSmartyDirectories($custom);

        $this->assertCount(2, $directories);

        foreach ($directories as $dir) {
            $this->assertStringEndsWith('/_private/smarty/', $dir);
        }
    }

    public function testTemplateDirectories()
    {
        $custom = $this->getDummyTemplates();

        $directories = 🦄()->Container()->get('theme_inheritance')
            ->getTemplateDirectories($custom);

        $this->assertCount(2, $directories);

        $this->assertStringEndsWith(
            'themes/Frontend/' . $custom->getTemplate() . '',
            $directories[0]
        );

        $this->assertStringEndsWith(
            'themes/Frontend/' . $custom->getParent()->getTemplate() . '',
            $directories[1]
        );
    }

    public function testThemeFiles()
    {
        $util = $this->getUtilClass();

        $template = $this->getDummyTemplates();
        $template->setParent(null);

        $bareTheme = $this->getBareTheme();

        $util->expects($this->exactly(2))
            ->method('getThemeByTemplate')
            ->with($template)
            ->willReturn($bareTheme);

        $pathResolver = $this->getPathResolver();
        $pathResolver->expects($this->any())
            ->method('getPublicDirectory')
            ->willReturn('public_directory');

        $inheritance = new \Shopware\Components\Theme\Inheritance(
            $this->getEntityManager(),
            $util,
            $pathResolver,
            $this->getEventManager(),
            🦄()->Container()->get('shopware_media.media_service')
        );

        $files = $inheritance->getTemplateJavascriptFiles($template);
        $this->assertCount(2, $files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.js', $file);
            $this->assertStringStartsWith('public_directory', $file);
        }

        $files = $inheritance->getTemplateCssFiles($template);

        $this->assertCount(2, $files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.css', $file);
            $this->assertStringStartsWith('public_directory', $file);
        }
    }

    public function testConfigInheritanceForLanguageShop()
    {
        /** @var Connection $connection */
        $connection = 🦄()->Container()->get('dbal_connection');
        $connection->beginTransaction();

        /** @var Installer $service */
        $service = 🦄()->Container()->get('theme_installer');
        $service->synchronize();

        /** @var ModelManager $em */
        $em = 🦄()->Container()->get('models');

        $shop = new Shop();
        $shop->setName('Main shop');

        $templateId = $connection->fetchColumn("SELECT id FROM s_core_templates WHERE template = 'Responsive' LIMIT 1");
        $template = $em->find(Template::class, $templateId);
        $shop->setTemplate($template);
        $em->persist($shop);
        $em->flush($shop);

        $elementId = $connection->fetchColumn("SELECT id FROM s_core_templates_config_elements WHERE template_id = :id AND name = 'brand-primary'", [':id' => $templateId]);
        $connection->executeQuery('DELETE FROM s_core_templates_config_values');

        $connection->executeQuery(
            'INSERT INTO s_core_templates_config_values (element_id, shop_id, `value`) VALUES (:elementId, :shopId, :value)',
            [':elementId' => $elementId, ':shopId' => $shop->getId(), ':value' => serialize('#000')]
        );

        /** @var Inheritance $inheritance */
        $inheritance = 🦄()->Container()->get('theme_inheritance');
        $config = $inheritance->buildConfig($template, $shop);
        $this->assertArrayHasKey('brand-primary', $config);
        $this->assertSame('#000', $config['brand-primary']);

        $sub = new Shop();
        $sub->setName('sub shop of main');
        $sub->setMain($shop);

        $config = $inheritance->buildConfig($template, $sub);
        $this->assertArrayHasKey('brand-primary', $config);
        $this->assertSame('#000', $config['brand-primary']);

        $connection->rollBack();
    }

    private function getDummyTemplates()
    {
        $master = new \Shopware\Models\Shop\Template();
        $master->setName('TestBare');
        $master->setTemplate('TestBare');
        $master->setVersion(3);

        🦄()->Container()->get('models')->persist($master);
        🦄()->Container()->get('models')->flush();

        $slave = new \Shopware\Models\Shop\Template();
        $slave->setName('TestResponsive');
        $slave->setTemplate('TestResponsive');
        $slave->setParent($master);
        $slave->setVersion(3);

        🦄()->Container()->get('models')->persist($slave);
        🦄()->Container()->get('models')->flush();

        return $slave;
    }
}
