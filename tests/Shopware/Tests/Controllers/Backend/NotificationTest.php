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
 */

/**
 * @group disable
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Backend_NotificationTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Returns the test DataSet
     * Because of this DataSet you can assert fix values
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Articles').'Notification.xml');
    }

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();
        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * test getList controller action
     */
    public function testGetArticleList()
    {
        $this->dispatch('backend/Notification/getArticleList');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        $this->assertNotEmpty($returnData);
        $this->assertEquals(2,count($returnData));
        $listingFirstEntry = $returnData[0];

        // cause of the DataSet you can assert fix values
        $this->assertEquals(2, $listingFirstEntry["registered"]);
        $this->assertEquals("SW2001", $listingFirstEntry["number"]);
        $this->assertEquals(1, $listingFirstEntry["notNotified"]);
    }

    /**
     * test getCustomerList controller action
     */
    public function testGetCustomerList()
    {
        $params["orderNumber"] = "SW2001";
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        $this->assertTrue($this->View()->success);

        $returnData = $this->View()->data;
        $this->assertEquals(2,count($returnData));
        $listingFirstEntry = $returnData[0];
        $listingSecondEntry = $returnData[1];

        // cause of the DataSet you can assert fix values
        $this->assertEquals("test@example.de", $listingFirstEntry["mail"]);
        $this->assertEquals(0, $listingFirstEntry["notified"]);

        $this->assertEquals("test@example.org", $listingSecondEntry["mail"]);
        $this->assertEquals(1, $listingSecondEntry["notified"]);

        $params["orderNumber"] = "SW2003";
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        $this->assertTrue($this->View()->success);

        $returnData = $this->View()->data;

        $this->assertArrayCount(1, $returnData);
        $this->assertEquals("test@example.com", $returnData[0]["mail"]);
        $this->assertNotEmpty($returnData[0]["name"]);
        $this->assertNotEmpty($returnData[0]["customerId"]);
    }
}
