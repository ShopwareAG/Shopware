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

/**
 * Method to fast deploy a query to enlight
 *
 * The params array knows the following keys
 * - name : The name of the action to call
 * - params : optional params array for the specific action call
 *
 * @param                          $params
 * @param Enlight_Template_Default $template
 * @return string
 * @throws Exception
 */
function smarty_function_action($params, Enlight_Template_Default $template)
{
    $response = Shopware()->Front()->subRequest($params);

    if ($response === false) {
        return '';
    }

    return $response->getBody();
}
