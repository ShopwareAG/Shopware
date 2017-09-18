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

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\DbAdapter;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Snippet_Manager extends Enlight_Components_Snippet_Manager
{
    /**
     * @var ModelManager the model manager
     */
    protected $modelManager;

    /**
     * @var array The config options provided in the global config.php file
     */
    protected $snippetConfig;

    /**
     * @var Shopware\Models\Shop\Locale
     */
    protected $locale;

    /**
     * @var Shopware\Models\Shop\Shop
     */
    protected $shop;

    /**
     * @var Enlight_Config_Adapter_File
     */
    protected $fileAdapter;

    /**
     * @var array
     */
    protected $extends = [];

    /**
     * @var array
     */
    private $pluginDirectories;

    /**
     * @param ModelManager $modelManager
     * @param array        $pluginDirectories
     * @param array        $snippetConfig
     */
    public function __construct(ModelManager $modelManager, array $pluginDirectories, array $snippetConfig)
    {
        $this->snippetConfig = $snippetConfig;
        $this->modelManager = $modelManager;
        $this->pluginDirectories = $pluginDirectories;

        if ($this->snippetConfig['readFromIni']) {
            $configDir = $this->getConfigDirs();
            $this->fileAdapter = new Enlight_Config_Adapter_File([
                'configDir' => $configDir,
                'allowWrites' => $snippetConfig['writeToIni'],
            ]);
        }

        $this->adapter = new DbAdapter([
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => ['shopID', 'localeID'],
            'allowWrites' => $snippetConfig['writeToDb'],
        ]);
    }

    /**
     * Returns a snippet model instance
     *
     * @param string $namespace
     *
     * @return Enlight_Components_Snippet_Namespace
     *
     * @deprecated  4.0 - 2012/04/01
     */
    public function getSnippet($namespace = null)
    {
        return parent::getNamespace($namespace);
    }

    /**
     * Returns a snippet model instance
     *
     * @param string $namespace
     *
     * @return Enlight_Components_Snippet_Namespace
     */
    public function getNamespace($namespace = null)
    {
        $key = $namespace === null ? '__ignore' : (string) $namespace;

        if (!isset($this->namespaces[$key])) {
            if ($this->snippetConfig['readFromDb']) {
                $this->namespaces[$key] = new $this->defaultNamespaceClass([
                    'adapter' => $this->adapter,
                    'name' => $namespace,
                    'section' => [
                        $this->shop ? $this->shop->getId() : 1,
                        $this->locale ? $this->locale->getId() : $this->getDefaultLocale()->getId(),
                    ],
                    'extends' => $this->extends,
                ]);
            }

            if ($this->snippetConfig['readFromIni'] && (!isset($this->namespaces[$key]) || count($this->namespaces[$key]) == 0) && isset($this->fileAdapter)) {
                /** @var \Enlight_Components_Snippet_Namespace $fullNamespace */
                $fullNamespace = new $this->defaultNamespaceClass([
                    'adapter' => $this->fileAdapter,
                    'name' => $namespace,
                    'section' => null,
                ]);

                $locale = $this->locale ? $this->locale->getLocale() : $this->getDefaultLocale()->getLocale();
                if (
                    !array_key_exists($locale, $fullNamespace->toArray())
                    && in_array($locale, ['en_GB', 'default'])
                    && count(array_keys($fullNamespace->toArray()))
                ) {
                    $locale = array_shift(array_diff(['en_GB', 'default'], [$locale]));
                }

                $fullNamespace->setSection($locale);
                $fullNamespace->setData($fullNamespace->get($locale));

                $this->namespaces[$key] = $fullNamespace;
            }
        }

        if (!isset($this->namespaces[$key])) {
            $this->namespaces[$key] = new $this->defaultNamespaceClass([
                'name' => $namespace,
            ]);
        }

        return $this->namespaces[$key];
    }

    /**
     * Set locale instance
     *
     * @param Locale $locale
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
        $this->namespaces = [];

        return $this;
    }

    /**
     * Set shop instance
     *
     * @param Shop $shop
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function setShop(Shop $shop)
    {
        $this->shop = $shop;
        $this->locale = $shop->getLocale();
        $this->namespaces = [];
        $this->initExtends();

        return $this;
    }

    /**
     * @param   $dir
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function addConfigDir($dir)
    {
        if (!$this->fileAdapter) {
            return $this;
        }

        $this->fileAdapter->addConfigDir($dir);

        return $this;
    }

    /**
     * @return Locale
     */
    protected function getDefaultLocale()
    {
        return $this->modelManager->getRepository(Shop::class)->getDefault()->getLocale();
    }

    /**
     * Defines the 'extends' logic for snippet loading, responsible for the cascading fallbacks
     * between snippet sets
     */
    protected function initExtends()
    {
        $extends = [];
        $shop = $this->shop;
        $locale = $this->locale;

        $main = $shop->getMain();
        if ($main !== null && $main->getId() === 1) {
            $main = null;
        }

        // fallback to parent shop, current locale
        if ($main !== null && $main->getId() !== 1) {
            $extends[] = [
                $main->getId(),
                $locale->getId(),
            ];
        }

        // fallback to default shop, current locale
        if ($shop && $shop->getId() !== 1) {
            $extends[] = [
                1,
                $locale->getId(),
            ];
        }

        /*
         * Fallback to parent shop, parent locale
         * If parent locale is the same as current locale, skip this step
         * as it was already added previously ("fallback to parent shop, current locale")
         **/
        if ($main !== null && $locale->getId() != $main->getLocale()->getId()) {
            $extends[] = [
                $main->getId(),
                $main->getLocale()->getId(),
            ];
        }

        // fallback to default shop, default language
        // this needs to be fixed, because its wrong for non-english installations
        if ($locale->getId() !== 1) {
            $extends[] = [
                1,
                1,
            ];
        }

        $this->extends = $extends;
    }

    /**
     * @return string[]
     */
    protected function getConfigDirs()
    {
        $configDir = [];
        if (file_exists(Shopware()->DocPath('snippets'))) {
            $configDir[] = Shopware()->DocPath('snippets');
        }

        /** @var \Shopware\Models\Plugin\Plugin[] $plugins */
        $plugins = $this->modelManager->getRepository(Plugin::class)->findBy(['active' => true]);
        foreach ($plugins as $plugin) {
            if (!$plugin->isLegacyPlugin()) {
                continue;
            }
            $pluginPath = $this->pluginDirectories[$plugin->getSource()] . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();

            // Add plugin snippets
            $pluginSnippetPath = $pluginPath . DIRECTORY_SEPARATOR . 'Snippets' . DIRECTORY_SEPARATOR;

            array_unshift($configDir, $pluginSnippetPath);

            $pluginThemePath = $pluginPath . DIRECTORY_SEPARATOR . 'Themes' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR;
            if (file_exists($pluginThemePath)) {
                // Add plugin theme snippets
                $directories = new \DirectoryIterator(
                    $pluginThemePath
                );

                /** @var $directory \DirectoryIterator */
                foreach ($directories as $directory) {
                    //check valid directory
                    if ($directory->isDot() || !$directory->isDir() || $directory->getFilename() === '_cache') {
                        continue;
                    }

                    $configDir['themes/' . strtolower($directory->getFilename()) . '/'] = $directory->getPathname() . '/_private/snippets/';
                }
            }
        }

        return $configDir;
    }
}