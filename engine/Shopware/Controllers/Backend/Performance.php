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
 * @package    Shopware_Controllers
 * @subpackage Article
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Performance Controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_Performance extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Stores a list of all needed config data
     * @var array
     */
    protected $configData = array();

	protected function initAcl()
	{
	}


    public function init()
    {
        echo "<pre>";
        print_r(Shopware()->Config()->get('LastArticles::show'));
        echo "</pre>";
        exit();
        
        $this->configData = $this->prepareConfigData();

        parent::init();
    }

    /**
     * Some methods for testing purpose
     */
    public function getTopSellerCountAction() { $this->View()->assign(array('success' => true, 'total' => 100000)); }
    public function initTopSellerAction() { sleep(1); $this->View()->assign(array('success' => true, 'total' => 100000)); }

    /**
     * This action creates/updates the configuration
     */
    public function saveConfigAction()
    {
        $data = $this->Request()->getParams();

		// Save the config
        $data = $this->prepareDataForSaving($data);
        $this->saveConfigData($data);

    	// Clear the config cache
        Shopware()->Cache()->clean();

		// Reload config, so that the actual config from the
		// db is returned
        $this->configData = $this->prepareConfigData();


        $this->View()->assign(array(
            'success' => true,
            'data' => $this->configData
        ));
    }

    /**
     * Iterates the given data array and persists all config variables
     * @param $data
     */
    public function saveConfigData($data)
    {
        foreach ($data as $values) {
            foreach ($values as $configKey => $value) {
                $this->saveConfig($configKey, $value);
            }
        }
    }

    /**
     * General helper method which triggers the prepare...ConfigForSaving methods
     *
     * @param $data
     * @return array
     */
    public function prepareDataForSaving($data)
    {
        $output = array();
        $output['httpCache'] = $this->prepareHttpCacheConfigForSaving($data['httpCache'][0]);
        $output['topSeller'] = $this->prepareForSavingDefault($data['topSeller'][0]);
        $output['seo']       = $this->prepareSeoConfigForSaving($data['seo'][0]);
        $output['search']    = $this->prepareForSavingDefault($data['search'][0]);
        $output['categories']= $this->prepareForSavingDefault($data['categories'][0]);
        $output['various']   = $this->prepareForSavingDefault($data['various'][0]);
        $output['customer']   = $this->prepareForSavingDefault($data['customer'][0]);

        return $output;
    }

    /**
     * Generic helper method which prepares a given array for saving
     * @param $data
     * @return Array
     */
    public function prepareForSavingDefault($data)
   	{
        unset($data['id']);

        return $data;
   	}

    /**
     * Prepare seo array for saving
     *
     * @param $data
     * @return Array
     */
    public function prepareSeoConfigForSaving($data)
	{
        unset($data['id']);

        $date = date_create($data['routerlastupdateDate'])->format('Y-d-m');
        $time = $data['routerlastupdateTime'];

        $datetime = $date . ' ' . $time;

		$data['routerlastupdate'] = $datetime;

        unset($data['routerlastupdateDate']);
        unset($data['routerlastupdateTime']);

        return $data;		
	}

    /**
     * Prepare the http config array so that it can easily be saved
     *
     * @param $data
     * @return Array
     */
    public function prepareHttpCacheConfigForSaving($data)
    {
        $lines = array();
        foreach ($data['cacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['cacheControllers'] = implode("\n", $lines);

        $lines = array();
        foreach ($data['noCacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['noCacheControllers'] = implode("\n", $lines);

        unset($data['id']);

        return $data;

    }

    /**
     * Helper method to persist a given config value
     */
    public function saveConfig($name, $value)
    {
        $shopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
        $formRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = array('name' => $name);
        if (isset($formName)) {
            $form = $formRepository->findOneBy(array('name' => $formName));
            $findBy['form'] = $form;
        }


        /** @var $element Shopware\Models\Config\Element */
        $element = $elementRepository->findOneBy($findBy);


        // If the element is empty, the given setting does not exists. This might be the case
        // for some plugins
        if (empty($element)) {
            return;
        }

        foreach ($element->getValues() as $valueModel) {
            Shopware()->Models()->remove($valueModel);
        }

        $values = array();
        // Do not save default value
        if ($value !== $element->getValue()) {
        	error_log("saving: ". $value . ": " . $name);
            $valueModel = new Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues($values);
        Shopware()->Models()->flush($element);
    }

    public function readConfig($configName, $defaultValue='')
    {
        // Simple getter for config items without scope
        if (strpos($configName, ':') === false) {
            return Shopware()->Config()->get($configName);
        }

        list($scope, $config) = explode(':', $configName, 2);

        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
        $formRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

        $form = $formRepository->findOneBy(array('name' => $scope));

        if(!$form) {
            return $defaultValue;
        }

        $element = $elementRepository->findOneBy(array('name' => $config, 'form' => $form));

        if(!$element) {
            return $defaultValue;
        }

        $values = $element->getValues();
        if (empty($values) || empty($values[0])) {
            return $element->getValue();
        }

        $firstValue = $values[0];
        return $firstValue->getValue();


    }

    /**
     * Reads all config data and prepares it for our models
     * @return array
     */
    protected function prepareConfigData()
    {
        return array(
            'httpCache' => $this->prepareHttpCacheConfig(),
            'topSeller' => $this->genericConfigLoader(
                array(
                    'topSellerActive',
                    'topSellerValidationTime',
                    'chartinterval',
                    'topSellerRefreshStrategy',
                    'topSellerPseudoSales'
                )
            ),
            'seo'       => $this->prepareSeoConfig(),
            'search'    => $this->genericConfigLoader(array('searchRefreshStrategy')),
            'categories' => $this->genericConfigLoader(
                array('articlesperpage', 'orderbydefault', 'showSupplierInCategories', 'propertySorting')
            ),
            'various' => $this->genericConfigLoader(
                array(
                    'disableShopwareStatistics',
                    'TagCloud:show',
                    'LastArticles:show',
                    'LastArticles:lastarticlestoshow',
                    'disableArticleNavigation'
                )
            ),
            'customer' => $this->genericConfigLoader(
                array('alsoBoughtShow', 'similarViewedShow', 'similarRefreshStrategy', 'similarRefreshStrategy')
            ),
        );
    }

    /**
     * Generic helper method to build an array of config which needs to be loaded
     * @param $config
     * @return array
     */
    protected function genericConfigLoader($config)
    {
        $data = array();

        foreach ($config as $configName) {
            $data[$configName] = $this->readConfig($configName);
        }

        return $data;
    }

    /**
     * Special treatment for SEO config needed
     *
     * @return array
     */
    protected function prepareSeoConfig()
    {
        $datetime = date_create(Shopware()->Config()->routerlastupdate);
        if ($datetime) {
            $date = $datetime ->format('Y-m-d');
            $time = $datetime ->format('H:i');
        } else {
            $date = null;
            $time = null;
        }

        return array(
            'routerurlcache'     => (int) Shopware()->Config()->routerurlcache,
            'routercache'        => (int) Shopware()->Config()->routercache,
            'routerlastupdateDate'   => $date,
            'routerlastupdateTime'   => $time,
            'seoRefreshStrategy' => Shopware()->Config()->seoRefreshStrategy
        );
    }

    /**
     * Special treatment for HTTPCache config needed
     *
     * @return array
     */
    protected function prepareHttpCacheConfig()
    {
        $controllers = Shopware()->Config()->cacheControllers;
        $cacheControllers = array();
        if(!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $cacheControllers[] = array('key' => $controller, 'value' => $cacheTime);
            }
        }

        $controllers = Shopware()->Config()->noCacheControllers;
        $noCacheControllers = array();
        if(!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $noCacheControllers[] = array('key' => $controller, 'value' => $cacheTime);
            }
        }

        return array(
            'cacheControllers' => $cacheControllers,
            'noCacheControllers' => $noCacheControllers,
            'HttpCache:proxyBan' => $this->readConfig('HttpCache:proxyBan'),
            'HttpCache:admin' => $this->readConfig('HttpCache:admin'),
            'HttpCache:proxy' => $this->readConfig('HttpCache:proxy')
        );
    }

    /**
     *
     */
    public function getConfigAction()
    {
        $this->View()->assign(array(
            'success' => true,
            'data' => $this->configData
        ));
    }
}
