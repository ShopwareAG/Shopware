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
class Shopware_Tests_Models_Category_PathByIdTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repo = null;

    protected $expected1 = array(
        1=> array ( 1 => 'Root', ),3=> array ( 3 => 'Deutsch', ),39=> array ( 39 => 'English', ),5=> array ( 3 => 'Deutsch', 5 => 'Genusswelten', ),6=> array ( 3 => 'Deutsch', 6 => 'Sommerwelten', ),8=> array ( 3 => 'Deutsch', 8 => 'Wohnwelten', ),9=> array ( 3 => 'Deutsch', 9 => 'Freizeitwelten', ),10=> array ( 3 => 'Deutsch', 10 => 'Beispiele', ),17=> array ( 3 => 'Deutsch', 17 => 'Trends + News', ),42=> array ( 39 => 'English', 42 => 'Trends + News', ),43=> array ( 39 => 'English', 43 => 'Worlds of indulgence', ),44=> array ( 39 => 'English', 44 => 'Leisure worlds', ),45=> array ( 39 => 'English', 45 => 'Home sweet home', ),46=> array ( 39 => 'English', 46 => 'Summertime', ),61=> array ( 39 => 'English', 61 => 'Examples', ),11=> array ( 3 => 'Deutsch', 5 => 'Genusswelten', 11 => 'Tees und Zubehör', ),14=> array ( 3 => 'Deutsch', 5 => 'Genusswelten', 14 => 'Edelbrände', ),15=> array ( 3 => 'Deutsch', 5 => 'Genusswelten', 15 => 'Köstlichkeiten', ),16=> array ( 3 => 'Deutsch', 9 => 'Freizeitwelten', 16 => 'Entertainment', ),19=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 19 => 'Crossselling', ),20=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 20 => 'Darstellung', ),21=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 21 => 'Produktvergleiche & Filter', ),22=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 22 => 'Konfiguratorartikel', ),23=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 23 => 'Preisgestaltung', ),24=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 24 => 'Versandkosten', ),25=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 25 => 'Zahlungsarten', ),27=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 27 => 'In Kürze verfügbar', ),30=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 30 => 'Kundenbindung', ),31=> array ( 3 => 'Deutsch', 9 => 'Freizeitwelten', 31 => 'Vintage', ),32=> array ( 3 => 'Deutsch', 8 => 'Wohnwelten', 32 => 'Möbel', ),33=> array ( 3 => 'Deutsch', 8 => 'Wohnwelten', 33 => 'Küchenzubehör', ),34=> array ( 3 => 'Deutsch', 6 => 'Sommerwelten', 34 => 'Beachwear', ),35=> array ( 3 => 'Deutsch', 6 => 'Sommerwelten', 35 => 'Beauty & Care', ),36=> array ( 3 => 'Deutsch', 6 => 'Sommerwelten', 36 => 'On World Tour', ),37=> array ( 3 => 'Deutsch', 6 => 'Sommerwelten', 37 => 'Accessoires', ),38=> array ( 3 => 'Deutsch', 8 => 'Wohnwelten', 38 => 'Dekoration', ),47=> array ( 39 => 'English', 43 => 'Worlds of indulgence', 47 => 'Teas and Accessories', ),50=> array ( 39 => 'English', 43 => 'Worlds of indulgence', 50 => 'Brandies', ),51=> array ( 39 => 'English', 43 => 'Worlds of indulgence', 51 => 'Delights', ),52=> array ( 39 => 'English', 44 => 'Leisure worlds', 52 => 'Vintage', ),53=> array ( 39 => 'English', 44 => 'Leisure worlds', 53 => 'Entertainment', ),54=> array ( 39 => 'English', 45 => 'Home sweet home', 54 => 'Furniture', ),55=> array ( 39 => 'English', 45 => 'Home sweet home', 55 => 'Kitchen Accessories', ),56=> array ( 39 => 'English', 45 => 'Home sweet home', 56 => 'Decoration', ),57=> array ( 39 => 'English', 46 => 'Summertime', 57 => 'Beachwear', ),58=> array ( 39 => 'English', 46 => 'Summertime', 58 => 'Beauty & Care', ),59=> array ( 39 => 'English', 46 => 'Summertime', 59 => 'Travel around the World', ),60=> array ( 39 => 'English', 46 => 'Summertime', 60 => 'Accessories', ),62=> array ( 39 => 'English', 61 => 'Examples', 62 => 'Available soon', ),64=> array ( 39 => 'English', 61 => 'Examples', 64 => 'Customer loyalty', ),65=> array ( 39 => 'English', 61 => 'Examples', 65 => 'Configurator articles', ),67=> array ( 39 => 'English', 61 => 'Examples', 67 => 'Product comparison', ),68=> array ( 39 => 'English', 61 => 'Examples', 68 => 'Presentation', ),69=> array ( 39 => 'English', 61 => 'Examples', 69 => 'Crossselling', ),71=> array ( 39 => 'English', 61 => 'Examples', 71 => 'Payment methods', ),72=> array ( 39 => 'English', 61 => 'Examples', 72 => 'Shipping costs', ),73=> array ( 39 => 'English', 61 => 'Examples', 73 => 'Price strategies', ),74=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 74 => 'Kundengruppen / B2B', ),75=> array ( 3 => 'Deutsch', 10 => 'Beispiele', 75 => 'Gutscheine', ),12=> array ( 3 => 'Deutsch', 5 => 'Genusswelten', 11 => 'Tees und Zubehör', 12 => 'Tees', ),13=> array ( 3 => 'Deutsch', 5 => 'Genusswelten', 11 => 'Tees und Zubehör', 13 => 'Tee-Zubehör', ),48=> array ( 39 => 'English', 43 => 'Worlds of indulgence', 47 => 'Teas and Accessories', 48 => 'Teas', ),49=> array ( 39 => 'English', 43 => 'Worlds of indulgence', 47 => 'Teas and Accessories', 49 => 'Teas and accessories', ),
    );

    protected $expected2 = array(
        1=> array ( 1 => array ( 'id' => 1, 'name' => 'Root', 'blog' => false, ), ),3=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), ),39=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), ),5=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 5 => array ( 'id' => 5, 'name' => 'Genusswelten', 'blog' => false, ), ),6=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 6 => array ( 'id' => 6, 'name' => 'Sommerwelten', 'blog' => false, ), ),8=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 8 => array ( 'id' => 8, 'name' => 'Wohnwelten', 'blog' => false, ), ),9=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 9 => array ( 'id' => 9, 'name' => 'Freizeitwelten', 'blog' => false, ), ),10=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), ),17=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 17 => array ( 'id' => 17, 'name' => 'Trends + News', 'blog' => true, ), ),42=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 42 => array ( 'id' => 42, 'name' => 'Trends + News', 'blog' => true, ), ),43=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 43 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false, ), ),44=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 44 => array ( 'id' => 44, 'name' => 'Leisure worlds', 'blog' => false, ), ),45=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 45 => array ( 'id' => 45, 'name' => 'Home sweet home', 'blog' => false, ), ),46=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 46 => array ( 'id' => 46, 'name' => 'Summertime', 'blog' => false, ), ),61=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), ),11=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 5 => array ( 'id' => 5, 'name' => 'Genusswelten', 'blog' => false, ), 11 => array ( 'id' => 11, 'name' => 'Tees und Zubehör', 'blog' => false, ), ),14=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 5 => array ( 'id' => 5, 'name' => 'Genusswelten', 'blog' => false, ), 14 => array ( 'id' => 14, 'name' => 'Edelbrände', 'blog' => false, ), ),15=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 5 => array ( 'id' => 5, 'name' => 'Genusswelten', 'blog' => false, ), 15 => array ( 'id' => 15, 'name' => 'Köstlichkeiten', 'blog' => false, ), ),16=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 9 => array ( 'id' => 9, 'name' => 'Freizeitwelten', 'blog' => false, ), 16 => array ( 'id' => 16, 'name' => 'Entertainment', 'blog' => false, ), ),19=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 19 => array ( 'id' => 19, 'name' => 'Crossselling', 'blog' => false, ), ),20=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 20 => array ( 'id' => 20, 'name' => 'Darstellung', 'blog' => false, ), ),21=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 21 => array ( 'id' => 21, 'name' => 'Produktvergleiche & Filter', 'blog' => false, ), ),22=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 22 => array ( 'id' => 22, 'name' => 'Konfiguratorartikel', 'blog' => false, ), ),23=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 23 => array ( 'id' => 23, 'name' => 'Preisgestaltung', 'blog' => false, ), ),24=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 24 => array ( 'id' => 24, 'name' => 'Versandkosten', 'blog' => false, ), ),25=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 25 => array ( 'id' => 25, 'name' => 'Zahlungsarten', 'blog' => false, ), ),27=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 27 => array ( 'id' => 27, 'name' => 'In Kürze verfügbar', 'blog' => false, ), ),30=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 30 => array ( 'id' => 30, 'name' => 'Kundenbindung', 'blog' => false, ), ),31=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 9 => array ( 'id' => 9, 'name' => 'Freizeitwelten', 'blog' => false, ), 31 => array ( 'id' => 31, 'name' => 'Vintage', 'blog' => false, ), ),32=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 8 => array ( 'id' => 8, 'name' => 'Wohnwelten', 'blog' => false, ), 32 => array ( 'id' => 32, 'name' => 'Möbel', 'blog' => false, ), ),33=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 8 => array ( 'id' => 8, 'name' => 'Wohnwelten', 'blog' => false, ), 33 => array ( 'id' => 33, 'name' => 'Küchenzubehör', 'blog' => false, ), ),34=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 6 => array ( 'id' => 6, 'name' => 'Sommerwelten', 'blog' => false, ), 34 => array ( 'id' => 34, 'name' => 'Beachwear', 'blog' => false, ), ),35=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 6 => array ( 'id' => 6, 'name' => 'Sommerwelten', 'blog' => false, ), 35 => array ( 'id' => 35, 'name' => 'Beauty & Care', 'blog' => false, ), ),36=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 6 => array ( 'id' => 6, 'name' => 'Sommerwelten', 'blog' => false, ), 36 => array ( 'id' => 36, 'name' => 'On World Tour', 'blog' => false, ), ),37=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 6 => array ( 'id' => 6, 'name' => 'Sommerwelten', 'blog' => false, ), 37 => array ( 'id' => 37, 'name' => 'Accessoires', 'blog' => false, ), ),38=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 8 => array ( 'id' => 8, 'name' => 'Wohnwelten', 'blog' => false, ), 38 => array ( 'id' => 38, 'name' => 'Dekoration', 'blog' => false, ), ),47=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 43 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false, ), 47 => array ( 'id' => 47, 'name' => 'Teas and Accessories', 'blog' => false, ), ),50=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 43 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false, ), 50 => array ( 'id' => 50, 'name' => 'Brandies', 'blog' => false, ), ),51=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 43 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false, ), 51 => array ( 'id' => 51, 'name' => 'Delights', 'blog' => false, ), ),52=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 44 => array ( 'id' => 44, 'name' => 'Leisure worlds', 'blog' => false, ), 52 => array ( 'id' => 52, 'name' => 'Vintage', 'blog' => false, ), ),53=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 44 => array ( 'id' => 44, 'name' => 'Leisure worlds', 'blog' => false, ), 53 => array ( 'id' => 53, 'name' => 'Entertainment', 'blog' => false, ), ),54=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 45 => array ( 'id' => 45, 'name' => 'Home sweet home', 'blog' => false, ), 54 => array ( 'id' => 54, 'name' => 'Furniture', 'blog' => false, ), ),55=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 45 => array ( 'id' => 45, 'name' => 'Home sweet home', 'blog' => false, ), 55 => array ( 'id' => 55, 'name' => 'Kitchen Accessories', 'blog' => false, ), ),56=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 45 => array ( 'id' => 45, 'name' => 'Home sweet home', 'blog' => false, ), 56 => array ( 'id' => 56, 'name' => 'Decoration', 'blog' => false, ), ),57=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 46 => array ( 'id' => 46, 'name' => 'Summertime', 'blog' => false, ), 57 => array ( 'id' => 57, 'name' => 'Beachwear', 'blog' => false, ), ),58=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 46 => array ( 'id' => 46, 'name' => 'Summertime', 'blog' => false, ), 58 => array ( 'id' => 58, 'name' => 'Beauty & Care', 'blog' => false, ), ),59=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 46 => array ( 'id' => 46, 'name' => 'Summertime', 'blog' => false, ), 59 => array ( 'id' => 59, 'name' => 'Travel around the World', 'blog' => false, ), ),60=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 46 => array ( 'id' => 46, 'name' => 'Summertime', 'blog' => false, ), 60 => array ( 'id' => 60, 'name' => 'Accessories', 'blog' => false, ), ),62=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 62 => array ( 'id' => 62, 'name' => 'Available soon', 'blog' => false, ), ),64=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 64 => array ( 'id' => 64, 'name' => 'Customer loyalty', 'blog' => false, ), ),65=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 65 => array ( 'id' => 65, 'name' => 'Configurator articles', 'blog' => false, ), ),67=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 67 => array ( 'id' => 67, 'name' => 'Product comparison', 'blog' => false, ), ),68=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 68 => array ( 'id' => 68, 'name' => 'Presentation', 'blog' => false, ), ),69=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 69 => array ( 'id' => 69, 'name' => 'Crossselling', 'blog' => false, ), ),71=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 71 => array ( 'id' => 71, 'name' => 'Payment methods', 'blog' => false, ), ),72=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 72 => array ( 'id' => 72, 'name' => 'Shipping costs', 'blog' => false, ), ),73=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 61 => array ( 'id' => 61, 'name' => 'Examples', 'blog' => false, ), 73 => array ( 'id' => 73, 'name' => 'Price strategies', 'blog' => false, ), ),74=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 74 => array ( 'id' => 74, 'name' => 'Kundengruppen / B2B', 'blog' => false, ), ),75=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 10 => array ( 'id' => 10, 'name' => 'Beispiele', 'blog' => false, ), 75 => array ( 'id' => 75, 'name' => 'Gutscheine', 'blog' => false, ), ),12=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 5 => array ( 'id' => 5, 'name' => 'Genusswelten', 'blog' => false, ), 11 => array ( 'id' => 11, 'name' => 'Tees und Zubehör', 'blog' => false, ), 12 => array ( 'id' => 12, 'name' => 'Tees', 'blog' => false, ), ),13=> array ( 3 => array ( 'id' => 3, 'name' => 'Deutsch', 'blog' => false, ), 5 => array ( 'id' => 5, 'name' => 'Genusswelten', 'blog' => false, ), 11 => array ( 'id' => 11, 'name' => 'Tees und Zubehör', 'blog' => false, ), 13 => array ( 'id' => 13, 'name' => 'Tee-Zubehör', 'blog' => false, ), ),48=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 43 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false, ), 47 => array ( 'id' => 47, 'name' => 'Teas and Accessories', 'blog' => false, ), 48 => array ( 'id' => 48, 'name' => 'Teas', 'blog' => false, ), ),49=> array ( 39 => array ( 'id' => 39, 'name' => 'English', 'blog' => false, ), 43 => array ( 'id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false, ), 47 => array ( 'id' => 47, 'name' => 'Teas and Accessories', 'blog' => false, ), 49 => array ( 'id' => 49, 'name' => 'Teas and accessories', 'blog' => false, ), ),
    );

    protected $expected3 = array(
        1=> 'Root',3=> 'Deutsch',39=> 'English',5=> 'Deutsch > Genusswelten > Genusswelten',6=> 'Deutsch > Sommerwelten > Sommerwelten',8=> 'Deutsch > Wohnwelten > Wohnwelten',9=> 'Deutsch > Freizeitwelten > Freizeitwelten',10=> 'Deutsch > Beispiele > Beispiele',17=> 'Deutsch > Trends + News > Trends + News',42=> 'English > Trends + News > Trends + News',43=> 'English > Worlds of indulgence > Worlds of indulgence',44=> 'English > Leisure worlds > Leisure worlds',45=> 'English > Home sweet home > Home sweet home',46=> 'English > Summertime > Summertime',61=> 'English > Examples > Examples',11=> 'Deutsch > Genusswelten > Tees und Zubehör > Tees und Zubehör',14=> 'Deutsch > Genusswelten > Edelbrände > Edelbrände',15=> 'Deutsch > Genusswelten > Köstlichkeiten > Köstlichkeiten',16=> 'Deutsch > Freizeitwelten > Entertainment > Entertainment',19=> 'Deutsch > Beispiele > Crossselling > Crossselling',20=> 'Deutsch > Beispiele > Darstellung > Darstellung',21=> 'Deutsch > Beispiele > Produktvergleiche & Filter > Produktvergleiche & Filter',22=> 'Deutsch > Beispiele > Konfiguratorartikel > Konfiguratorartikel',23=> 'Deutsch > Beispiele > Preisgestaltung > Preisgestaltung',24=> 'Deutsch > Beispiele > Versandkosten > Versandkosten',25=> 'Deutsch > Beispiele > Zahlungsarten > Zahlungsarten',27=> 'Deutsch > Beispiele > In Kürze verfügbar > In Kürze verfügbar',30=> 'Deutsch > Beispiele > Kundenbindung > Kundenbindung',31=> 'Deutsch > Freizeitwelten > Vintage > Vintage',32=> 'Deutsch > Wohnwelten > Möbel > Möbel',33=> 'Deutsch > Wohnwelten > Küchenzubehör > Küchenzubehör',34=> 'Deutsch > Sommerwelten > Beachwear > Beachwear',35=> 'Deutsch > Sommerwelten > Beauty & Care > Beauty & Care',36=> 'Deutsch > Sommerwelten > On World Tour > On World Tour',37=> 'Deutsch > Sommerwelten > Accessoires > Accessoires',38=> 'Deutsch > Wohnwelten > Dekoration > Dekoration',47=> 'English > Worlds of indulgence > Teas and Accessories > Teas and Accessories',50=> 'English > Worlds of indulgence > Brandies > Brandies',51=> 'English > Worlds of indulgence > Delights > Delights',52=> 'English > Leisure worlds > Vintage > Vintage',53=> 'English > Leisure worlds > Entertainment > Entertainment',54=> 'English > Home sweet home > Furniture > Furniture',55=> 'English > Home sweet home > Kitchen Accessories > Kitchen Accessories',56=> 'English > Home sweet home > Decoration > Decoration',57=> 'English > Summertime > Beachwear > Beachwear',58=> 'English > Summertime > Beauty & Care > Beauty & Care',59=> 'English > Summertime > Travel around the World > Travel around the World',60=> 'English > Summertime > Accessories > Accessories',62=> 'English > Examples > Available soon > Available soon',64=> 'English > Examples > Customer loyalty > Customer loyalty',65=> 'English > Examples > Configurator articles > Configurator articles',67=> 'English > Examples > Product comparison > Product comparison',68=> 'English > Examples > Presentation > Presentation',69=> 'English > Examples > Crossselling > Crossselling',71=> 'English > Examples > Payment methods > Payment methods',72=> 'English > Examples > Shipping costs > Shipping costs',73=> 'English > Examples > Price strategies > Price strategies',74=> 'Deutsch > Beispiele > Kundengruppen / B2B > Kundengruppen / B2B',75=> 'Deutsch > Beispiele > Gutscheine > Gutscheine',12=> 'Deutsch > Genusswelten > Tees und Zubehör > Tees > Tees',13=> 'Deutsch > Genusswelten > Tees und Zubehör > Tee-Zubehör > Tee-Zubehör',48=> 'English > Worlds of indulgence > Teas and Accessories > Teas > Teas',49=> 'English > Worlds of indulgence > Teas and Accessories > Teas and accessories > Teas and accessories',
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

    public function testExpected1() {
        foreach($this->expected1 as $id => $expected) {
            $data = $this->getRepo()->getPathById($id);
            $this->assertEquals($expected, $data);
        }
    }

    public function testExpected2() {
        foreach($this->expected2 as $id => $expected) {
            $data = $this->getRepo()->getPathById($id, array('id', 'name', 'blog'));
            $this->assertEquals($expected, $data);
        }
    }

    public function testExpected3() {
        foreach($this->expected3 as $id => $expected) {
            $data = $this->getRepo()->getPathById($id, 'name', ' > ');
            $this->assertEquals($expected, $data);
        }
    }

}