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
class Shopware_RegressionTests_Ticket5394 extends Enlight_Components_Test_Controller_TestCase
{
    protected $articles = array(
        '202' => array(
            'url' => '/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22',
            'sConfigurator' => array(
                0 => array(
                    'groupID' => 6,
                    'groupname' => 'Farbe',
                    'groupnameOrig' => 'Farbe',
                    'groupdescription' => NULL,
                    'groupdescriptionOrig' => NULL,
                    'groupimage' => '',
                    'postion' => 8,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        15 => array(
                            'optionID' => 15,
                            'groupID' => 6,
                            'optionnameOrig' => 'blau',
                            'optionname' => 'blau',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        28 => array(
                            'optionID' => 28,
                            'groupID' => 6,
                            'optionnameOrig' => 'rot',
                            'optionname' => 'rot',
                            'optionposition' => 8,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        37 => array(
                            'optionID' => 37,
                            'groupID' => 6,
                            'optionnameOrig' => 'pink',
                            'optionname' => 'pink',
                            'optionposition' => 10,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
                1 => array(
                    'groupID' => 7,
                    'groupname' => 'Größe',
                    'groupnameOrig' => 'Größe',
                    'groupdescription' => '',
                    'groupdescriptionOrig' => '',
                    'groupimage' => '',
                    'postion' => 9,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        61 => array(
                            'optionID' => 61,
                            'groupID' => 7,
                            'optionnameOrig' => '36',
                            'optionname' => '36',
                            'optionposition' => 15,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        62 => array(
                            'optionID' => 62,
                            'groupID' => 7,
                            'optionnameOrig' => '37',
                            'optionname' => '37',
                            'optionposition' => 16,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        63 => array(
                            'optionID' => 63,
                            'groupID' => 7,
                            'optionnameOrig' => '38',
                            'optionname' => '38',
                            'optionposition' => 17,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        64 => array(
                            'optionID' => 64,
                            'groupID' => 7,
                            'optionnameOrig' => '39',
                            'optionname' => '39',
                            'optionposition' => 18,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        65 => array(
                            'optionID' => 65,
                            'groupID' => 7,
                            'optionnameOrig' => '40',
                            'optionname' => '40',
                            'optionposition' => 19,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        66 => array(
                            'optionID' => 66,
                            'groupID' => 7,
                            'optionnameOrig' => '41',
                            'optionname' => '41',
                            'optionposition' => 20,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        67 => array(
                            'optionID' => 67,
                            'groupID' => 7,
                            'optionnameOrig' => '42',
                            'optionname' => '42',
                            'optionposition' => 21,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
            ),
            'sConfiguratorSettings' => array(
                'articleID' => 202,
                'type' => 0,
                'template' => 'article_config_upprice.tpl',
                'instock' => true,
                'upprice' => 0,
            ),
        ),
        '203' => array(
            'url' => '/beispiele/konfiguratorartikel/203/artikel-mit-auswahlkonfigurator?c=22',
            'sConfigurator' => array(
                0 => array(
                    'groupID' => 6,
                    'groupname' => 'Farbe',
                    'groupnameOrig' => 'Farbe',
                    'groupdescription' => NULL,
                    'groupdescriptionOrig' => NULL,
                    'groupimage' => '',
                    'postion' => 8,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        36 => array(
                            'optionID' => 36,
                            'groupID' => 6,
                            'optionnameOrig' => 'Grün',
                            'optionname' => 'Grün',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 0, 
                            'selected' => 0,
                        ),
                        15 => array(
                            'optionID' => 15,
                            'groupID' => 6,
                            'optionnameOrig' => 'blau',
                            'optionname' => 'blau',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                    ),
                    'user_selected' => false,
                ),
                1 => array(
                    'groupID' => 7,
                    'groupname' => 'Größe',
                    'groupnameOrig' => 'Größe',
                    'groupdescription' => '',
                    'groupdescriptionOrig' => '',
                    'groupimage' => '',
                    'postion' => 9,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        60 => array(
                            'optionID' => 60,
                            'groupID' => 7,
                            'optionnameOrig' => '37/38',
                            'optionname' => '37/38',
                            'optionposition' => 5,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        21 => array(
                            'optionID' => 21,
                            'groupID' => 7,
                            'optionnameOrig' => '39/40',
                            'optionname' => '39/40',
                            'optionposition' => 6,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        22 => array(
                            'optionID' => 22,
                            'groupID' => 7,
                            'optionnameOrig' => '41/42',
                            'optionname' => '41/42',
                            'optionposition' => 7,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        23 => array(
                            'optionID' => 23,
                            'groupID' => 7,
                            'optionnameOrig' => '43/44',
                            'optionname' => '43/44',
                            'optionposition' => 9,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        24 => array(
                            'optionID' => 24,
                            'groupID' => 7,
                            'optionnameOrig' => '44/45',
                            'optionname' => '44/45',
                            'optionposition' => 10,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        26 => array(
                            'optionID' => 26,
                            'groupID' => 7,
                            'optionnameOrig' => '46/47',
                            'optionname' => '46/47',
                            'optionposition' => 13,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        27 => array(
                            'optionID' => 27,
                            'groupID' => 7,
                            'optionnameOrig' => '48/49',
                            'optionname' => '48/49',
                            'optionposition' => 14,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
            ),
            'sConfiguratorSettings' => array(
                'articleID' => 203,
                'type' => 1,
                'template' => 'article_config_step.tpl',
                'instock' => true,
                'upprice' => 0,
            ),
        ),
        '204' => array(
            'url' => '/beispiele/konfiguratorartikel/204/artikel-mit-tabellenkonfigurator?c=22',
            'sConfigurator' => array(
                0 => array(
                    'groupID' => 6,
                    'groupname' => 'Farbe',
                    'groupnameOrig' => 'Farbe',
                    'groupdescription' => NULL,
                    'groupdescriptionOrig' => NULL,
                    'groupimage' => '',
                    'postion' => 8,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        38 => array(
                            'optionID' => 38,
                            'groupID' => 6,
                            'optionnameOrig' => 'Gelb',
                            'optionname' => 'Gelb',
                            'optionposition' => 0,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        36 => array(
                            'optionID' => 36,
                            'groupID' => 6,
                            'optionnameOrig' => 'Grün',
                            'optionname' => 'Grün',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        15 => array(
                            'optionID' => 15,
                            'groupID' => 6,
                            'optionnameOrig' => 'blau',
                            'optionname' => 'blau',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        37 => array(
                            'optionID' => 37,
                            'groupID' => 6,
                            'optionnameOrig' => 'pink',
                            'optionname' => 'pink',
                            'optionposition' => 10,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
                1 => array(
                    'groupID' => 7,
                    'groupname' => 'Größe',
                    'groupnameOrig' => 'Größe',
                    'groupdescription' => '',
                    'groupdescriptionOrig' => '',
                    'groupimage' => '',
                    'postion' => 9,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        16 => array(
                            'optionID' => 16,
                            'groupID' => 7,
                            'optionnameOrig' => 'S',
                            'optionname' => 'S',
                            'optionposition' => 0,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        17 => array(
                            'optionID' => 17,
                            'groupID' => 7,
                            'optionnameOrig' => 'M',
                            'optionname' => 'M',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        18 => array(
                            'optionID' => 18,
                            'groupID' => 7,
                            'optionnameOrig' => 'L',
                            'optionname' => 'L',
                            'optionposition' => 3,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        19 => array(
                            'optionID' => 19,
                            'groupID' => 7,
                            'optionnameOrig' => 'XL',
                            'optionname' => 'XL',
                            'optionposition' => 4,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        78 => array(
                            'optionID' => 78,
                            'groupID' => 7,
                            'optionnameOrig' => 'XXL',
                            'optionname' => 'XXL',
                            'optionposition' => 22,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
            ),
            'sConfiguratorSettings' => array(
                'articleID' => 204,
                'type' => 2,
                'template' => 'article_config_table.tpl',
                'instock' => true,
                'upprice' => 0,
            ),
        ),
        '205' => array(
            'url' => '/beispiele/konfiguratorartikel/205/artikel-mit-aufpreiskonfigurator?c=22',
            'sConfigurator' => array(
                0 => array(
                    'groupID' => 12,
                    'groupname' => 'mit Ersatzteilen',
                    'groupnameOrig' => 'mit Ersatzteilen',
                    'groupdescription' => '',
                    'groupdescriptionOrig' => '',
                    'groupimage' => '',
                    'postion' => 14,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        80 => array(
                            'optionID' => 80,
                            'groupID' => 12,
                            'optionnameOrig' => 'ohne',
                            'optionname' => 'ohne',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        81 => array(
                            'optionID' => 81,
                            'groupID' => 12,
                            'optionnameOrig' => 'mit Figuren',
                            'optionname' => 'mit Figuren',
                            'optionposition' => 2,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        83 => array(
                            'optionID' => 83,
                            'groupID' => 12,
                            'optionnameOrig' => 'mit Figuren und Ball-Set',
                            'optionname' => 'mit Figuren und Ball-Set',
                            'optionposition' => 3,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                        84 => array(
                            'optionID' => 84,
                            'groupID' => 12,
                            'optionnameOrig' => 'mit Figuren, Ball-Set und Service Box',
                            'optionname' => 'mit Figuren, Ball-Set und Service Box',
                            'optionposition' => 4,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
                1 => array(
                    'groupID' => 13,
                    'groupname' => 'Garantieverlängerung',
                    'groupnameOrig' => 'Garantieverlängerung',
                    'groupdescription' => NULL,
                    'groupdescriptionOrig' => NULL,
                    'groupimage' => '',
                    'postion' => 15,
                    'selected_value' => NULL,
                    'selected' => false,
                    'values' => array(
                        85 => array(
                            'optionID' => 85,
                            'groupID' => 13,
                            'optionnameOrig' => '24 Monate',
                            'optionname' => '24 Monate',
                            'optionposition' => 1,
                            'optionactive' => 1,
                            'user_selected' => 1,
                            'selected' => 1,
                        ),
                        86 => array(
                            'optionID' => 86,
                            'groupID' => 13,
                            'optionnameOrig' => '36 Monate',
                            'optionname' => '36 Monate',
                            'optionposition' => 2,
                            'optionactive' => 1,
                            'user_selected' => 0,
                            'selected' => 0,
                        ),
                    ),
                    'user_selected' => false,
                ),
            ),
            'sConfiguratorSettings' => array(
                'articleID' => 205,
                'type' => 0,
                'template' => 'article_config_upprice.tpl',
                'instock' => false,
                'upprice' => 0,
            ),
        )
    );

    public function testConfigurators()
    {
        foreach ($this->articles as $expected) {
            $this->Request()->setMethod('POST');
            $this->dispatch($expected['url']);
            $article = $this->View()->getAssign('sArticle');

            foreach($expected['sConfigurator'] as $key => $expectedConfigurator) {
                $properties = array('groupID','groupname','groupnameOrig','groupdescription','groupdescriptionOrig','groupimage','selected_value','selected','user_selected');

                $actualConfigurator =  $article['sConfigurator'][$key];
                $this->checkArrayValues($expectedConfigurator, $actualConfigurator, $properties);

                foreach($expectedConfigurator['values'] as $optionKey => $expectedOption) {
                    $optionProperties = array('optionID','groupID','optionnameOrig','optionname','optionactive','user_selected','selected');
                    $actualOption = $actualConfigurator['values'][$optionKey];

                    $this->checkArrayValues($expectedOption, $actualOption, $optionProperties);
                }
            }
        }
    }

    protected function checkArrayValues($expected, $actual, $properties)
    {
        foreach($properties as $property) {
            $this->assertEquals($expected[$property], $actual[$property]);
        }
    }
}