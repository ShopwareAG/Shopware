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
use Doctrine\DBAL\Connection;
use Shopware\Models\Article\Article;

/**
 * Backend search controller
 *
 * This controller provides the global search in the Shopware backend. The
 * search has the ability to provides search results from the different
 * areas starting from articles to orders
 */
class Shopware_Controllers_Backend_Search extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Generic search action for entities
     */
    public function searchAction()
    {
        $entity = $this->Request()->getParam('entity', null);
        $ids = $this->Request()->getParam('ids', []);
        $id = $this->Request()->getParam('id', null);
        $term = $this->Request()->getParam('query', null);
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);

        $builder = $this->createEntitySearchQuery($entity);

        if (!empty($ids)) {
            $ids = json_decode($ids, true);
            $this->addIdsCondition($builder, $ids);
        } elseif (!empty($id)) {
            $this->addIdsCondition($builder, [$id]);
        } else {
            if (!empty($term)) {
                $this->addSearchTermCondition($entity, $builder, $term);
            }

            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $pagination = $this->getPaginator($builder);
        $data = $pagination->getIterator()->getArrayCopy();

        $data = $this->hydrateSearchResult($entity, $data);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => $pagination->count(),
        ]);
    }

    /**
     * Sanitizes the passed term and queries the different areas of the search
     *
     * @return mixed
     */
    public function indexAction()
    {
        if (!$this->Request()->isPost()) {
            return;
        }

        // Sanitize and clean up the search parameter for later processing
        $term = $this->Request()->get('search');
        $term = strtolower($term);
        $term = trim($term);

        $term = preg_replace('/[^\\w0-9]+/u', ' ', $term);
        $term = trim(preg_replace('/\s+/', '%', $term), '%');

        if ($term === '') {
            return;
        }

        $search = $this->container->get('shopware.backend.global_search');
        $result = $search->search($term);

        $this->View()->assign('searchResult', $result);
    }

    /**
     * Queries the articles from the database based on the passed search term
     *
     * @param $search
     *
     * @return array
     */
    public function getArticles($search)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->container->get('dbal_connection')->createQueryBuilder();

        $query->select([
            'article.id',
            'article.name',
            'article.description_long',
            'article.description',
            'variant.ordernumber',
        ]);
        $query->from('s_articles', 'article');
        $query->innerJoin('article', 's_articles_details', 'variant', 'variant.articleID = article.id');
        $query->leftJoin('article', 's_articles_translations', 'translation', 'article.id= translation.articleID');
        $query->leftJoin('article', 's_articles_supplier', 'manufacturer', 'article.supplierID = manufacturer.id');

        $searchTerm = $this->get('events')->filter(
            'Shopware_Backend_Search_GetArticles_SearchTerms',
            [
                'article.name^3',
                'variant.ordernumber^2',
                'translation.name^1',
                'manufacturer.name^1',
            ]
        );

        /** @var \Shopware\Components\Model\SearchBuilder $builder */
        $builder = $this->container->get('shopware.model.search_builder');
        $builder->addSearchTerm(
            $query,
            $search,
            $searchTerm
        );

        $query->addGroupBy('article.id');
        $query->setFirstResult(0);
        $query->setMaxResults(5);

        // add additional table joins for conditions
        $query = $this->get('events')->filter('Shopware_Backend_Search_GetArticles_PreFetch', $query);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Queries the customers from the database based on the passed search term
     *
     * @param $search
     *
     * @return array
     */
    public function getCustomers($search)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->container->get('dbal_connection')->createQueryBuilder();

        $query->select([
            'user.id',
            'IF(address.company != "", address.company, CONCAT(address.firstname, " ", address.lastname)) as name',
            'CONCAT(address.street, " ", address.zipcode, " ", address.city) as description'
        ]);

        $query->from('s_user', 'user');
        $query->innerJoin('user', 's_user_addresses', 'address', 'address.user_id = user.id');

        $searchTerm = $this->get('events')->filter(
            'Shopware_Backend_Search_GetCustomers_SearchTerms',
            [
                'user.email^3',
                'user.customernumber^4',
                'TRIM(CONCAT(address.company, \' \', address.department))^1',
                'TRIM(CONCAT(address.firstname, \' \', address.lastname))^1',
            ]
        );

        /** @var \Shopware\Components\Model\SearchBuilder $builder */
        $builder = $this->container->get('shopware.model.search_builder');
        $builder->addSearchTerm(
            $query,
            $search,
            $searchTerm
        );

        $query->addGroupBy('user.id');
        $query->setFirstResult(0);
        $query->setMaxResults(5);

        // add additional table joins for conditions
        $query = $this->get('events')->filter('Shopware_Backend_Search_GetCustomers_PreFetch', $query);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Queries the orders from the database based on the passed search term
     *
     * @param $search
     *
     * @return array
     */
    public function getOrders($search)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->container->get('dbal_connection')->createQueryBuilder();

        $query->select([
            '`order`.id',
            '`order`.ordernumber as name',
            '`order`.userID',
            '`order`.invoice_amount as totalAmount',
            '`order`.transactionID',
            '`order`.status',
            '`order`.cleared',
            'doc.type',
            'doc.docID',
            'CONCAT(
                IF(address.company != "", address.company, CONCAT(address.firstname, " ", address.lastname)),
                ", ",
                payment.description
            ) as description'
        ]);

        $query->from('s_order', '`order`');
        $query->leftJoin('`order`', 's_order_documents', 'doc', '(doc.orderID = `order`.id AND doc.docID != 0)');
        $query->leftJoin('`order`', 's_order_billingaddress', 'address', 'address.orderID = `order`.id');
        $query->leftJoin('`order`', 's_core_paymentmeans', 'payment', 'payment.id = `order`.paymentID');

        $query->where('`order`.id != "0"');

        $searchTerm = $this->get('events')->filter(
            'Shopware_Backend_Search_GetOrders_SearchTerms',
            [
                '`order`.ordernumber^3',
                '`order`.transactionID^1',
                '`doc`.docID^3'
            ]
        );

        /** @var \Shopware\Components\Model\SearchBuilder $builder */
        $builder = $this->container->get('shopware.model.search_builder');
        $builder->addSearchTerm(
            $query,
            $search,
            $searchTerm
        );

        $query->addGroupBy('`order`.id');
        $query->orderBy('`order`.ordertime', 'DESC');
        $query->setFirstResult(0);
        $query->setMaxResults(5);

        // add additional table joins for conditions
        $query = $this->get('events')->filter('Shopware_Backend_Search_GetOrders_PreFetch', $query);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $entity
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createEntitySearchQuery($entity)
    {
        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $this->get('models')->createQueryBuilder();
        $query->select('entity')
            ->from($entity, 'entity');

        switch ($entity) {
            case 'Shopware\Models\Article\Article':
                $query->select(['entity.id', 'entity.name', 'mainDetail.number'])
                    ->innerJoin('entity.mainDetail', 'mainDetail')
                    ->leftJoin('entity.details', 'details');
                break;

            case 'Shopware\Models\Property\Value':
                if ($groupId = $this->Request()->getParam('groupId')) {
                    $query->andWhere('entity.optionId = :optionId')
                        ->setParameter(':optionId', $this->Request()->getParam('groupId'));
                }
                break;

            case 'Shopware\Models\Property\Option':
                if ($setId = $this->Request()->getParam('setId')) {
                    $query->innerJoin('entity.relations', 'relations', 'WITH', 'relations.groupId = :setId')
                        ->setParameter(':setId', $setId);
                }
                break;
            case 'Shopware\Models\Category\Category':
                $query->andWhere('entity.parent IS NOT NULL')
                    ->addOrderBy('entity.parentId')
                    ->addOrderBy('entity.position');
                break;
        }

        return $query;
    }

    /**
     * @param $builder \Doctrine\ORM\QueryBuilder
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function getPaginator($builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        /** @var \Shopware\Components\Model\ModelManager $entityManager */
        $entityManager = $this->get('models');

        return $entityManager->createPaginator($query);
    }

    /**
     * @param string  $entity
     * @param array[] $data
     *
     * @return array[]
     */
    private function hydrateSearchResult($entity, $data)
    {
        $data = array_map(function ($row) {
            if (array_key_exists('_score', $row) && array_key_exists(0, $row)) {
                return $row[0];
            }

            return $row;
        }, $data);

        switch ($entity) {
            case 'Shopware\Models\Category\Category':
                $data = $this->resolveCategoryPath($data);
                break;
        }

        return $data;
    }

    /**
     * @param string $entity
     *
     * @return string[]
     */
    private function getEntitySearchFields($entity)
    {
        /** @var \Shopware\Components\Model\ModelManager $entityManager */
        $entityManager = $this->get('models');
        $metaData = $entityManager->getClassMetadata($entity);

        $fields = array_filter(
            $metaData->getFieldNames(),
            function ($field) use ($metaData) {
                $type = $metaData->getTypeOfField($field);

                return in_array($type, ['string', 'text', 'date', 'datetime', 'decimal', 'float']);
            }
        );

        if (empty($fields)) {
            return $metaData->getFieldNames();
        }

        return $fields;
    }

    /**
     * @param string                     $entity
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param string                     $term
     */
    private function addSearchTermCondition($entity, $query, $term)
    {
        $fields = $this->getEntitySearchFields($entity);

        $builder = Shopware()->Container()->get('shopware.model.search_builder');

        $fields = array_map(function ($field) {
            return 'entity.' . $field;
        }, $fields);

        switch ($entity) {
            case Article::class:
                $fields[] = 'mainDetail.number';
                break;
        }

        $builder->addSearchTerm($query, $term, $fields);
    }

    /**
     * Creates a custom search term condition
     *
     * @param string $entity
     * @param string $column
     *
     * @return string
     */
    private function createCustomFieldToSearchTermCondition($entity, $column)
    {
        $field = $entity . '.' . $column;

        return $field . ' LIKE :search';
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param int[]                      $ids
     */
    private function addIdsCondition($query, $ids)
    {
        $query->andWhere('entity.id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
    }

    /**
     * @param $data
     *
     * @return array[]
     */
    private function resolveCategoryPath($data)
    {
        $ids = [];
        foreach ($data as $row) {
            $ids = array_merge($ids, explode('|', $row['path']));
            $ids[] = $row['id'];
        }
        $ids = array_values(array_unique(array_filter($ids)));
        $categories = $this->getCategories($ids);

        foreach ($data as &$row) {
            $parents = array_filter(explode('|', $row['path']));
            $parents = array_reverse($parents);
            $path = [];
            foreach ($parents as $parent) {
                $path[] = $categories[$parent];
            }
            $path[] = $row['name'];
            $row['name'] = implode('>', $path);
        }

        return $data;
    }

    /**
     * @param $ids
     */
    private function getCategories($ids)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select(['id', 'description'])
            ->from('s_categories', 'category')
            ->where('category.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
