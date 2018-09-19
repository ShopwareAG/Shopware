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

namespace Shopware\Components\Plugin\Configuration\Layers;

use Doctrine\DBAL\Query\QueryBuilder;

class DefaultShopLayer extends AbstractShopConfigurationLayer
{
    /**
     * {@inheritdoc}
     */
    public function readValues($shopId, $pluginName)
    {
        return parent::readValues(1, $pluginName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureQuery(QueryBuilder $builder, $shopId, $pluginName)
    {
        $shopIdKey = 'shopId' . crc32($shopId);
        $pluginNameKey = 'pluginName' . crc32($pluginName);

        return $builder
            ->andWhere($builder->expr()->eq('corePlugins.name', ':' . $pluginNameKey))
            ->andWhere($builder->expr()->eq('coreConfigValues.shop_id', ':' . $shopIdKey))
            ->setParameter($pluginNameKey, $pluginName)
            ->setParameter($shopIdKey, $shopId)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function isLayerResponsibleForShopId($shopId)
    {
        return $shopId === 1 || is_null($shopId);
    }
}
