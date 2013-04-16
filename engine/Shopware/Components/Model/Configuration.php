<?php
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
 * @package    Shopware_Components_Model
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Components\Model;
use \Doctrine\ORM\Configuration as BaseConfiguration;

/**
 *
 */
class Configuration extends BaseConfiguration
{
    /**
     * Directory for generated attribute models
     *
     * @var string
     */
    protected $attributeDir;

    /**
     * Directory for cached anotations
     *
     * @var string
     */
    protected $fileCacheDir;

    /**
     * @param $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        // Specifies the FQCN of a subclass of the EntityRepository.
        // That will be available for all entities without a custom repository class.
        $this->setDefaultRepositoryClassName('Shopware\Components\Model\ModelRepository');


        $this->setProxyDir($options['proxyDir']);
        $this->setProxyNamespace($options['proxyNamespace']);
        $this->setAutoGenerateProxyClasses(!empty($options['autoGenerateProxyClasses']));

        $this->setAttributeDir($options['attributeDir']);
        $this->setFileCacheDir($options['fileCacheDir']);

        $this->addEntityNamespace('Shopware', 'Shopware\Models');
        $this->addEntityNamespace('Custom', 'Shopware\CustomModels');

        $this->addCustomStringFunction('DATE_FORMAT', 'Shopware\Components\Model\Query\Mysql\DateFormat');
        $this->addCustomStringFunction('IFNULL', 'Shopware\Components\Model\Query\Mysql\IfNull');

        if(isset($options['cacheProvider'])) {
            $this->setCacheProvider($options['cacheProvider']);
        }
    }

    public function setCacheProvider($provider)
    {
        if(!class_exists($provider, false)) {
            $provider = "Doctrine\\Common\\Cache\\{$provider}Cache";
        }
        if(!class_exists($provider)) {
            throw new \Exception('Doctrine cache provider "' . $provider. "' not found failure.");
        }
        $cache = new $provider();
        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
    }

    /**
     * @param \Zend_Cache_Core $cacheResource
     */
    public function setCacheResource(\Zend_Cache_Core $cacheResource)
    {
        // Check if native Doctrine ApcCache may be used
        if ($cacheResource->getBackend() instanceof \Zend_Cache_Backend_Apc) {
            $cache = new \Doctrine\Common\Cache\ApcCache();
        } else {
            $cache = new Cache($cacheResource);
        }

        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    public function getAnnotationsReader()
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();

        $cache = $this->getMetadataCacheImpl();
        if ($this->getMetadataCacheImpl() instanceof Cache) {
            $reader = new \Doctrine\Common\Annotations\FileCacheReader(
                $reader,
                $this->getFileCacheDir()
            );
        } else {
            $reader = new \Doctrine\Common\Annotations\CachedReader(
                $reader,
                $cache
            );
        }

        return $reader;
    }

    /**
     * @param null $hookManager
     */
    public function setHookManager($hookManager = null)
    {
        $this->_attributes['hookManager'] = $hookManager;
    }

    /**
     * @return null
     */
    public function getHookManager()
    {
        return isset($this->_attributes['hookManager']) ?
            $this->_attributes['hookManager'] : null;
    }

    /**
     * @param string $dir
     * @throws \InvalidArgumentException
     * @return Configuration
     */
    public function setAttributeDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $dir = rtrim(realpath($dir), '\\/') . DIRECTORY_SEPARATOR;

        $this->attributeDir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeDir()
    {
        return $this->attributeDir;
    }

    /**
     * @param string $dir
     * @throws \InvalidArgumentException
     * @return Configuration
     */
    public function setFileCacheDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $dir = rtrim(realpath($dir), '\\/') . DIRECTORY_SEPARATOR;

        $this->fileCacheDir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileCacheDir()
    {
        return $this->fileCacheDir;
    }

    /**
     * Sets the directory where Doctrine generates any necessary proxy class files.
     *
     * @param string $dir
     * @throws \InvalidArgumentException
     */
    public function setProxyDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $dir = rtrim(realpath($dir), '\\/') . DIRECTORY_SEPARATOR;

        parent::setProxyDir($dir);
    }
}
