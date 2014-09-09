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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductConfigurationGateway implements Gateway\ProductConfigurationGatewayInterface
{
    /**
     * @var Hydrator\ConfiguratorHydrator
     */
    private $configuratorHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\ConfiguratorHydrator $configuratorHydrator
     */
    public function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\ConfiguratorHydrator $configuratorHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\ShopContextInterface $context)
    {
        $groups = $this->getList(array($product), $context);

        return array_shift($groups);
    }

    /**
     * @inheritdoc
     */
    public function getList($products, Struct\ShopContextInterface $context)
    {
        if (empty($products)) {
            return array();
        }

        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }
        $ids = array_unique($ids);

        $query = $this->getQuery()
            ->select('variants.ordernumber as number')
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields())
        ;

        $this->fieldHelper->addConfiguratorTranslation($query);
        $query->setParameter(':language', $context->getShop()->getId());

        $query->where('relations.article_id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $query->addOrderBy('configuratorGroup.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = array();
        foreach ($data as $key => $groups) {
            $result[$key] = $this->configuratorHydrator->hydrateGroups($groups);
        }

        return $result;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from('s_article_configurator_option_relations', 'relations');

        $query->innerJoin(
            'relations',
            's_articles_details',
            'variants',
            'variants.id = relations.article_id'
        );

        $query->innerJoin(
            'relations',
            's_article_configurator_options',
            'configuratorOption',
            'configuratorOption.id = relations.option_id'
        );

        $query->innerJoin(
            'configuratorOption',
            's_article_configurator_groups',
            'configuratorGroup',
            'configuratorGroup.id = configuratorOption.group_id'
        );

        return $query;
    }
}
