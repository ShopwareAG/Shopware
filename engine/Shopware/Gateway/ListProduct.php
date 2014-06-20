<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
namespace Shopware\Gateway;

use Shopware\Struct;

/**
 * @package Shopware\Gateway
 */
interface ListProduct
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\ListProduct::get()
     *
     * @param array $numbers
     * @param Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number
     */
    public function getList(array $numbers, Struct\Context $context);

    /**
     * The \Shopware\Struct\ListProduct requires the following data:
     * - Product base data
     * - Variant data of the passed number
     * - Unit data of the variant
     * - Price group
     * - Tax data
     * - Manufacturer data
     * - Esd data of the variant
     * - Sales of the product
     * - Core attribute of the variant
     * - Core attribute of the manufacturer
     * - Core attribute of the esd
     *
     * Required translation in the provided context language:
     * - Product
     * - Variant
     * - Manufacturer
     * - Unit of the variant
     *
     * @param $number
     * @param Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context);
}