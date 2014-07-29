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

namespace Shopware\Components\MultiEdit\Resource\Product;

/**
 * The filter class will search for articles matching a given filter
 *
 * Class Filter
 */
class Filter
{
    /**
     * Reference to an instance of the DqlHelper
     *
     * @var DqlHelper
     */
    protected $dqlHelper;

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * @param $dqlHelper
     */
    public function __construct($dqlHelper)
    {
        $this->dqlHelper = $dqlHelper;
    }

    /**
     * Returns a string representation of a given filterArray
     *
     * @param $filterArray
     * @return string
     */
    public function filterArrayToString($filterArray)
    {
        return implode(' ', array_map(function ($filter) { return $filter['token']; }, $filterArray));
    }

    /**
     * Builds the actual query for the token list
     *
     * @param $tokens
     * @param $offset
     * @param $limit
     * @return \Doctrine\ORM\Query
     */
    public function getFilterQuery($tokens, $offset=null, $limit=null)
    {

        $builder = $this->getFilterQueryBuilder($tokens, $offset, $limit);
        if ($offset) {
            $builder->setFirstResult($offset);
        }
        if ($limit) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Returns the basic filter query builder, with all the rules (tokens) applied
     *
     * @param $tokens
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterQueryBuilder($tokens)
    {
        $joinEntities = $this->getDqlHelper()->getJoinColumns($tokens);

        $builder = $this->getDqlHelper()->getEntityManager()->createQueryBuilder()
                ->select('partial detail.{id}')
                ->from('Shopware\Models\Article\Detail', 'detail')
                // only articles with attributes are considered to be valid
                ->innerJoin('detail.attribute', 'attr')
                ->leftJoin('detail.article', 'article');

        foreach ($joinEntities as $entity) {
            $builder->leftJoin($this->getDqlHelper()->getAssociationForEntity($entity), $this->getDqlHelper()->getPrefixForEntity($entity));
        }

        list($dql, $params) = $this->getDqlHelper()->getDqlFromTokens($tokens);

        foreach ($params as $key => $value) {
            $builder->setParameter($key, $value);
        }

        $builder->andWhere($dql);

        $builder->orderBy('detail.id', 'DESC');

        return $builder;
    }

    /**
     * Query builder to select a product with its dependencies
     *
     * @param $detailId
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    public function getArticleQueryBuilder($detailId)
    {
        $builder = $this->getDqlHelper()->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'partial detail.{id, number}',
            'partial article.{id, name}',
        ))
        ->from('Shopware\Models\Article\Detail', 'detail')
        //~ ->leftJoin('detail.article', 'article')
        ->where('detail.id = ?1')
        ->setParameter(1, $detailId);

        return $builder;
    }

    /**
     * @param $query \Doctrine\ORM\Query
     * @return Array
     */
    public function getPaginatedResult($query)
    {
        $paginator = Shopware()->Models()->createPaginator($query);

        $totalCount = $paginator->count();

        $result = array_map(function ($item) {
                return $item->getId();
            },
            $paginator->getIterator()->getArrayCopy()
        );

        // Detach currently handled models in order to avoid invalid models later
        $this->getDqlHelper()->getEntityManager()->clear();

        return array($result, $totalCount);
    }

    /**
     * Will return products which match a given filter (tokens)
     *
     * @param $tokens
     * @param $offset
     * @param $limit
     * @return array
     */
    public function filter($tokens, $offset, $limit)
    {

        $query = $this->getFilterQuery($tokens, $offset, $limit);
        list($result, $totalCount) = $this->getPaginatedResult($query);

        $articles = array();
        foreach ($result as $detailId) {
            // Skip invalid articles like articles not having attributes
            if ($article = $this->getDqlHelper()->getProductForListing($detailId)) {
                $articles[] = $article;
            }

        }

        return array(
            'data'  => $articles,
            'total' => $totalCount
        );

    }

}
