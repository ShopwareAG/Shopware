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
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Models_Category_ListQueryTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repo = null;

    protected $expected = array(
        1=> array ( 0 => array ( 'id' => 3, 'name' => 'Deutsch', 'position' => 1, 'parentId' => 1, 'childrenCount' => '31.0000', 'articleCount' => '305', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 39, 'name' => 'English', 'position' => NULL, 'parentId' => 1, 'childrenCount' => '29.0000', 'articleCount' => '115', 'emotions' => NULL, 'articles' => NULL, ), ),3=> array ( 0 => array ( 'id' => 5, 'name' => 'Genusswelten', 'position' => 0, 'parentId' => 3, 'childrenCount' => '5.0000', 'articleCount' => '56', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 9, 'name' => 'Freizeitwelten', 'position' => 1, 'parentId' => 3, 'childrenCount' => '2.0000', 'articleCount' => '27', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 8, 'name' => 'Wohnwelten', 'position' => 2, 'parentId' => 3, 'childrenCount' => '3.0000', 'articleCount' => '82', 'emotions' => NULL, 'articles' => NULL, ), 3 => array ( 'id' => 6, 'name' => 'Sommerwelten', 'position' => 3, 'parentId' => 3, 'childrenCount' => '4.0000', 'articleCount' => '103', 'emotions' => NULL, 'articles' => NULL, ), 4 => array ( 'id' => 10, 'name' => 'Beispiele', 'position' => 4, 'parentId' => 3, 'childrenCount' => '11.0000', 'articleCount' => '37', 'emotions' => NULL, 'articles' => NULL, ), 5 => array ( 'id' => 17, 'name' => 'Trends + News', 'position' => 3, 'parentId' => 3, 'childrenCount' => '0.0000', 'articleCount' => '0', 'emotions' => NULL, 'articles' => NULL, ), ),39=> array ( 0 => array ( 'id' => 42, 'name' => 'Trends + News', 'position' => 0, 'parentId' => 39, 'childrenCount' => '0.0000', 'articleCount' => '0', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'position' => 1, 'parentId' => 39, 'childrenCount' => '5.0000', 'articleCount' => '36', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 44, 'name' => 'Leisure worlds', 'position' => 2, 'parentId' => 39, 'childrenCount' => '2.0000', 'articleCount' => '11', 'emotions' => NULL, 'articles' => NULL, ), 3 => array ( 'id' => 45, 'name' => 'Home sweet home', 'position' => 3, 'parentId' => 39, 'childrenCount' => '3.0000', 'articleCount' => '16', 'emotions' => NULL, 'articles' => NULL, ), 4 => array ( 'id' => 46, 'name' => 'Summertime', 'position' => 4, 'parentId' => 39, 'childrenCount' => '4.0000', 'articleCount' => '19', 'emotions' => NULL, 'articles' => NULL, ), 5 => array ( 'id' => 61, 'name' => 'Examples', 'position' => NULL, 'parentId' => 39, 'childrenCount' => '9.0000', 'articleCount' => '33', 'emotions' => NULL, 'articles' => NULL, ), ),5=> array ( 0 => array ( 'id' => 11, 'name' => 'Tees und Zubehör', 'position' => 0, 'parentId' => 5, 'childrenCount' => '2.0000', 'articleCount' => '24', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 14, 'name' => 'Edelbrände', 'position' => 0, 'parentId' => 5, 'childrenCount' => '0.0000', 'articleCount' => '12', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 15, 'name' => 'Köstlichkeiten', 'position' => 0, 'parentId' => 5, 'childrenCount' => '0.0000', 'articleCount' => '9', 'emotions' => NULL, 'articles' => NULL, ), ),6=> array ( 0 => array ( 'id' => 34, 'name' => 'Beachwear', 'position' => 0, 'parentId' => 6, 'childrenCount' => '0.0000', 'articleCount' => '18', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 35, 'name' => 'Beauty & Care', 'position' => 0, 'parentId' => 6, 'childrenCount' => '0.0000', 'articleCount' => '12', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 36, 'name' => 'On World Tour', 'position' => 0, 'parentId' => 6, 'childrenCount' => '0.0000', 'articleCount' => '11', 'emotions' => NULL, 'articles' => NULL, ), 3 => array ( 'id' => 37, 'name' => 'Accessoires', 'position' => 0, 'parentId' => 6, 'childrenCount' => '0.0000', 'articleCount' => '11', 'emotions' => NULL, 'articles' => NULL, ), ),8=> array ( 0 => array ( 'id' => 38, 'name' => 'Dekoration', 'position' => NULL, 'parentId' => 8, 'childrenCount' => '0.0000', 'articleCount' => '14', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 32, 'name' => 'Möbel', 'position' => 0, 'parentId' => 8, 'childrenCount' => '0.0000', 'articleCount' => '16', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 33, 'name' => 'Küchenzubehör', 'position' => 0, 'parentId' => 8, 'childrenCount' => '0.0000', 'articleCount' => '18', 'emotions' => NULL, 'articles' => NULL, ), ),9=> array ( 0 => array ( 'id' => 31, 'name' => 'Vintage', 'position' => 0, 'parentId' => 9, 'childrenCount' => '0.0000', 'articleCount' => '10', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 16, 'name' => 'Entertainment', 'position' => 1, 'parentId' => 9, 'childrenCount' => '0.0000', 'articleCount' => '17', 'emotions' => NULL, 'articles' => NULL, ), ),10=> array ( 0 => array ( 'id' => 27, 'name' => 'In Kürze verfügbar', 'position' => 0, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '0', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 75, 'name' => 'Gutscheine', 'position' => 1, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '0', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 22, 'name' => 'Konfiguratorartikel', 'position' => 2, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), 3 => array ( 'id' => 30, 'name' => 'Kundenbindung', 'position' => 3, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '3', 'emotions' => NULL, 'articles' => NULL, ), 4 => array ( 'id' => 21, 'name' => 'Produktvergleiche & Filter', 'position' => 4, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '10', 'emotions' => NULL, 'articles' => NULL, ), 5 => array ( 'id' => 20, 'name' => 'Darstellung', 'position' => 5, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '6', 'emotions' => NULL, 'articles' => NULL, ), 6 => array ( 'id' => 19, 'name' => 'Crossselling', 'position' => 6, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '2', 'emotions' => NULL, 'articles' => NULL, ), 7 => array ( 'id' => 74, 'name' => 'Kundengruppen / B2B', 'position' => 7, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '1', 'emotions' => NULL, 'articles' => NULL, ), 8 => array ( 'id' => 25, 'name' => 'Zahlungsarten', 'position' => 8, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '3', 'emotions' => NULL, 'articles' => NULL, ), 9 => array ( 'id' => 24, 'name' => 'Versandkosten', 'position' => 7, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), 10 => array ( 'id' => 23, 'name' => 'Preisgestaltung', 'position' => 10, 'parentId' => 10, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), ),17=> array ( ),42=> array ( ),43=> array ( 0 => array ( 'id' => 47, 'name' => 'Teas and Accessories', 'position' => NULL, 'parentId' => 43, 'childrenCount' => '2.0000', 'articleCount' => '19', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 50, 'name' => 'Brandies', 'position' => NULL, 'parentId' => 43, 'childrenCount' => '0.0000', 'articleCount' => '12', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 51, 'name' => 'Delights', 'position' => NULL, 'parentId' => 43, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), ),44=> array ( 0 => array ( 'id' => 52, 'name' => 'Vintage', 'position' => NULL, 'parentId' => 44, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 53, 'name' => 'Entertainment', 'position' => NULL, 'parentId' => 44, 'childrenCount' => '0.0000', 'articleCount' => '6', 'emotions' => NULL, 'articles' => NULL, ), ),45=> array ( 0 => array ( 'id' => 54, 'name' => 'Furniture', 'position' => NULL, 'parentId' => 45, 'childrenCount' => '0.0000', 'articleCount' => '6', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 55, 'name' => 'Kitchen Accessories', 'position' => NULL, 'parentId' => 45, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 56, 'name' => 'Decoration', 'position' => NULL, 'parentId' => 45, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), ),46=> array ( 0 => array ( 'id' => 57, 'name' => 'Beachwear', 'position' => NULL, 'parentId' => 46, 'childrenCount' => '0.0000', 'articleCount' => '6', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 58, 'name' => 'Beauty & Care', 'position' => NULL, 'parentId' => 46, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 59, 'name' => 'Travel around the World', 'position' => NULL, 'parentId' => 46, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), 3 => array ( 'id' => 60, 'name' => 'Accessories', 'position' => NULL, 'parentId' => 46, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), ),61=> array ( 0 => array ( 'id' => 62, 'name' => 'Available soon', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '0', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 64, 'name' => 'Customer loyalty', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '3', 'emotions' => NULL, 'articles' => NULL, ), 2 => array ( 'id' => 65, 'name' => 'Configurator articles', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), 3 => array ( 'id' => 67, 'name' => 'Product comparison', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '6', 'emotions' => NULL, 'articles' => NULL, ), 4 => array ( 'id' => 68, 'name' => 'Presentation', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '6', 'emotions' => NULL, 'articles' => NULL, ), 5 => array ( 'id' => 69, 'name' => 'Crossselling', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '2', 'emotions' => NULL, 'articles' => NULL, ), 6 => array ( 'id' => 71, 'name' => 'Payment methods', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '3', 'emotions' => NULL, 'articles' => NULL, ), 7 => array ( 'id' => 72, 'name' => 'Shipping costs', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), 8 => array ( 'id' => 73, 'name' => 'Price strategies', 'position' => NULL, 'parentId' => 61, 'childrenCount' => '0.0000', 'articleCount' => '4', 'emotions' => NULL, 'articles' => NULL, ), ),11=> array ( 0 => array ( 'id' => 12, 'name' => 'Tees', 'position' => 0, 'parentId' => 11, 'childrenCount' => '0.0000', 'articleCount' => '14', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 13, 'name' => 'Tee-Zubehör', 'position' => 1, 'parentId' => 11, 'childrenCount' => '0.0000', 'articleCount' => '9', 'emotions' => NULL, 'articles' => NULL, ), ),14=> array ( ),15=> array ( ),16=> array ( ),19=> array ( ),20=> array ( ),21=> array ( ),22=> array ( ),23=> array ( ),24=> array ( ),25=> array ( ),27=> array ( ),30=> array ( ),31=> array ( ),32=> array ( ),33=> array ( ),34=> array ( ),35=> array ( ),36=> array ( ),37=> array ( ),38=> array ( ),47=> array ( 0 => array ( 'id' => 48, 'name' => 'Teas', 'position' => NULL, 'parentId' => 47, 'childrenCount' => '0.0000', 'articleCount' => '14', 'emotions' => NULL, 'articles' => NULL, ), 1 => array ( 'id' => 49, 'name' => 'Teas and accessories', 'position' => NULL, 'parentId' => 47, 'childrenCount' => '0.0000', 'articleCount' => '5', 'emotions' => NULL, 'articles' => NULL, ), ),50=> array ( ),51=> array ( ),52=> array ( ),53=> array ( ),54=> array ( ),55=> array ( ),56=> array ( ),57=> array ( ),58=> array ( ),59=> array ( ),60=> array ( ),62=> array ( ),64=> array ( ),65=> array ( ),67=> array ( ),68=> array ( ),69=> array ( ),71=> array ( ),72=> array ( ),73=> array ( ),74=> array ( ),75=> array ( ),12=> array ( ),13=> array ( ),48=> array ( ),49=> array ( ),
    );


    /**
     * @return Shopware\Models\Category\Repository
     */
    protected function getRepo() {
        if ($this->repo === null) {
            $this->repo = Shopware()->Models()->Category();
        }
        return $this->repo;
    }

    public function testQuery() {
        foreach($this->expected as $id => $expected) {
            $filter = array(array('property' => 'c.parentId', 'value' => $id));
            $query = $this->getRepo()->getListQuery($filter, array());
            $data = $this->removeDates($query->getArrayResult());
            $this->assertEquals($data, $expected);
        }
    }

    protected function removeDates($data) {
        foreach($data as &$subCategory) {
            unset($subCategory['changed']);
            unset($subCategory['cmsText']);
            unset($subCategory['added']);
            foreach($subCategory['emotions'] as &$emotion) {
                unset($emotion['createDate']);
                unset($emotion['modified']);
            }
            foreach($subCategory['articles'] as &$article) {
                unset($article['added']);
                unset($article['changed']);
                unset($article['mainDetail']['releaseDate']);
            }
        }
        return $data;
    }

}