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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductNumberSearch implements SearchBundle\ProductNumberSearchInterface
{
    /**
     * @var \Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * @var FacetHandlerInterface[]
     */
    private $facetHandlers;

    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param AttributeHydrator $attributeHydrator
     * @param \Enlight_Event_EventManager $eventManager
     * @param FacetHandlerInterface[] $facetHandlers
     */
    public function __construct(
        QueryBuilderFactory $queryBuilderFactory,
        AttributeHydrator $attributeHydrator,
        \Enlight_Event_EventManager $eventManager,
        $facetHandlers = array()
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->attributeHydrator = $attributeHydrator;
        $this->facetHandlers = $facetHandlers;
        $this->eventManager = $eventManager;
    }

    /**
     * Creates a product search result for the passed criteria object.
     * The criteria object contains different core conditions and plugin conditions.
     * This conditions has to be handled over the different condition handlers.
     *
     * The search gateway has to implement an event which plugin can be listened to,
     * to add their own handler classes.
     *
     * @param SearchBundle\Criteria $criteria
     * @param ShopContextInterface $context
     * @return SearchBundle\ProductNumberSearchResult
     */
    public function search(SearchBundle\Criteria $criteria, ShopContextInterface $context)
    {
        $this->facetHandlers = $this->registerFacetHandlers();

        $query = $this->getProductQuery($criteria, $context);

        $products = $this->getProducts($query);

        $total = $this->getTotalCount($query);

        $facets = $this->createFacets($criteria, $context);

        $result = new SearchBundle\ProductNumberSearchResult(
            $products,
            intval($total),
            $facets
        );

        return $result;
    }

    /**
     * @param QueryBuilder $query
     * @return array
     */
    private function getProducts(QueryBuilder $query)
    {
        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();

        foreach ($data as $row) {
            $product = new SearchBundle\SearchProduct();
            $product->setNumber($row['ordernumber']);

            unset($row['ordernumber']);

            if (!empty($row)) {
                $product->addAttribute(
                    'search',
                    $this->attributeHydrator->hydrate($row)
                );
            }
            $products[$product->getNumber()] = $product;
        }

        return $products;
    }


    /**
     * Calculated the total count of the whole search result.
     *
     * @param QueryBuilder $query
     * @return int
     */
    private function getTotalCount($query)
    {
        return $query->getConnection()->fetchColumn('SELECT FOUND_ROWS()');
    }

    /**
     * Executes the base query to select the products.
     *
     * @param SearchBundle\Criteria $criteria
     * @param ShopContextInterface $context
     * @return QueryBuilder
     */
    private function getProductQuery(SearchBundle\Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->queryBuilderFactory->createQueryWithSorting(
            $criteria,
            $context
        );

        $select = $query->getQueryPart('select');

        $query->select(array(
            'SQL_CALC_FOUND_ROWS variant.ordernumber'
        ));

        foreach ($select as $selection) {
            $query->addSelect($selection);
        }

        $query->addGroupBy('product.id');

        if ($criteria->getOffset()) {
            $query->setFirstResult($criteria->getOffset());
        }
        if ($criteria->getLimit()) {
            $query->setMaxResults($criteria->getLimit());
        }

        return $query;
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param ShopContextInterface $context
     * @return SearchBundle\FacetResultInterface[]
     * @throws \Exception
     */
    private function createFacets(SearchBundle\Criteria $criteria, ShopContextInterface $context)
    {
        $facets = array();

        foreach ($criteria->getFacets() as $facet) {
            $handler = $this->getFacetHandler($facet);

            $result = $handler->generateFacet($facet, $criteria, $context);

            if (!$result) {
                continue;
            }

            if (!is_array($result)) {
                $result = array($result);
            }

            $facets = array_merge($facets, $result);
        }

        return $facets;
    }

    /**
     * @return FacetHandlerInterface[]
     */
    private function registerFacetHandlers()
    {
        $facetHandlers = new ArrayCollection();
        $facetHandlers = $this->eventManager->collect(
            'Shopware_Search_Gateway_DBAL_Collect_Facet_Handlers',
            $facetHandlers
        );

        return array_merge($facetHandlers->toArray(), $this->facetHandlers);
    }

    /**
     * @param SearchBundle\FacetInterface $facet
     * @throws \Exception
     * @return FacetHandlerInterface
     */
    private function getFacetHandler(SearchBundle\FacetInterface $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Facet %s not supported", get_class($facet)));
    }
}
