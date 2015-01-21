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

namespace   Shopware\Models\Emotion;
use         Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * @category  Shopware
 * @package   Shopware\Models\Emotion
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Repository extends ModelRepository
{

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param array $filter
     * @param array $filterBy
     * @param array $orderBy
     * @param integer $offset
     * @param integer $limit
     * @param integer $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $filterBy = null, $orderBy = null, $offset = null, $limit = null, $categoryId = null)
    {
        $builder = $this->getListQueryBuilder($filter, $filterBy, $orderBy, $categoryId);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param  array $filter
     * @param  array $filterBy
     * @param  array $orderBy
     * @param  int $categoryId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $filterBy = null, $orderBy = null, $categoryId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('emotions', 'categories'))
                ->from('Shopware\Models\Emotion\Emotion', 'emotions')
                ->leftJoin('emotions.categories', 'categories');

        // filter by search
        if (!empty($filter) && $filter[0]["property"] == "filter" && !empty($filter[0]["value"])) {
            $builder->andWhere('emotions.name LIKE ?1')
                ->setParameter(1, '%'.$filter[0]["value"].'%');
        }

        // filter by desktop devices
        if (isset($filterBy) && $filterBy == 'onlyDesktop') {
            $builder->andWhere("emotions.device LIKE '%0%'");
        }

        // filter by tablet landscape devices
        if (isset($filterBy) && $filterBy == 'onlyTabletLandscape') {
            $builder->andWhere("emotions.device LIKE '%1%'");
        }

        // filter by tablet devices
        if (isset($filterBy) && $filterBy == 'onlyTablet') {
            $builder->andWhere("emotions.device LIKE '%2%'");
        }

        // filter by mobile landscape devices
        if (isset($filterBy) && $filterBy == 'onlyMobileLandscape') {
            $builder->andWhere("emotions.device LIKE '%3%'");
        }

        // filter by mobile devices
        if (isset($filterBy) && $filterBy == 'onlyMobile') {
            $builder->andWhere("emotions.device LIKE '%4%'");
        }

        // filter by active emotion worlds
        if (isset($filterBy) && $filterBy == 'active') {
            $builder->andWhere('emotions.active = 1');
        }

        // filter by landingpages
        if (isset($filterBy) && $filterBy == 'onlyLandingpage') {
            $builder->andWhere('emotions.isLandingPage = 1');
        }

        // filter by shopping worlds
        if (isset($filterBy) && $filterBy == 'onlyWorld') {
            $builder->andWhere('emotions.isLandingPage = 0');
        }

        // filter by categoryId
        if (!empty($categoryId) && $categoryId != 'NaN') {
            $path = '%|' . $categoryId . '|%';

            $builder->andWhere('categories.path LIKE :category OR categories.id = :categoryId')
                ->setParameter('category', $path)
                ->setParameter('categoryId', $categoryId);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        } else {
            $builder->addOrderBy('emotions.id', 'ASC');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param null $filter
     * @param array $orderBy
     * @param integer $offset
     * @param integer $limit
     * @return \Doctrine\ORM\Query
     */
    public function getNameListQuery($filter = null, $orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getNameListQueryBuilder($filter, $orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getLandingPageListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param  array $filter
     * @param  array $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNameListQueryBuilder($filter = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('emotions.id', 'emotions.name'))
            ->from('Shopware\Models\Emotion\Emotion', 'emotions');

        if (!empty($filter)) {
            $builder->where('emotions.isLandingPage = :isLandingPage')
                ->setParameter('isLandingPage', $filter ? 1 : 0);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param integer $emotionId
     * @return \Doctrine\ORM\Query
     */
    public function getEmotionDetailQuery($emotionId)
    {
        $builder = $this->getEmotionDetailQueryBuilder($emotionId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getEmotionDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param integer $emotionId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmotionDetailQueryBuilder($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('emotions', 'elements', 'component', 'fields', 'attribute','categories', 'grid', 'template'))
                ->from('Shopware\Models\Emotion\Emotion', 'emotions')
                ->leftJoin('emotions.grid', 'grid')
                ->leftJoin('emotions.template', 'template')
                ->leftJoin('emotions.elements', 'elements')
                ->leftJoin('emotions.attribute', 'attribute')
                ->leftJoin('elements.component', 'component')
                ->leftJoin('component.fields', 'fields')
                ->leftJoin('emotions.categories', 'categories')
                ->where('emotions.id = ?1')
                ->setParameter(1, $emotionId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param integer $elementId
     * @param integer $componentId
     * @return \Doctrine\ORM\Query
     */
    public function getElementDataQuery($elementId, $componentId)
    {
        $builder = $this->getElementDataQueryBuilder($elementId, $componentId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getElementDataQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param integer $elementId
     * @param integer $componentId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getElementDataQueryBuilder($elementId, $componentId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('data.value', 'field.name', 'field.id', 'field.valueType'))
                ->from('Shopware\Models\Emotion\Data', 'data')
                ->join('data.field', 'field')
                ->leftJoin('field.component', 'component')
                ->where('component.id = ?1')
                ->andWhere('data.elementId = ?2')
                ->setParameter(1, $componentId)
                ->setParameter(2, $elementId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param integer $emotionId
     * @return \Doctrine\ORM\Query
     */
    public function getEmotionAttributesQuery($emotionId)
    {
        $builder = $this->getEmotionAttributesQueryBuilder($emotionId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getEmotionAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param integer $emotionId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmotionAttributesQueryBuilder($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\Emotion', 'attribute')
                      ->where('attribute.emotionId = ?1')
                      ->setParameter(1, $emotionId);

        return $builder;
    }

    /**
     * @param integer $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getCategoryEmotionsQuery($categoryId)
    {
        $builder = $this->getCategoryEmotionsQueryBuilder($categoryId);

        return $builder->getQuery();
    }

    /**
     * @param integer $categoryId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCategoryEmotionsQueryBuilder($categoryId)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions', 'grid', 'template'))
                ->leftJoin('emotions.grid', 'grid')
                ->leftJoin('emotions.template', 'template')
                ->innerJoin('emotions.categories','categories')
                ->where('categories.id = ?1')
                ->andWhere('(emotions.validFrom <= CURRENT_TIMESTAMP() OR emotions.validFrom IS NULL)')
                ->andWhere('(emotions.validTo >= CURRENT_TIMESTAMP() OR emotions.validTo IS NULL)')
                ->andWhere('emotions.isLandingPage = 0 ')
                ->andWhere('emotions.active = 1 ')
                ->setParameter(1, $categoryId);

        return $builder;
    }


    /**
     * This function selects all elements and components of the passed emotion id.
     * @param $emotionId
     * @return QueryBuilder
     */
    public function getEmotionElementsQuery($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('elements', 'component'));
        $builder->from('Shopware\Models\Emotion\Element', 'elements');
        $builder->leftJoin('elements.component', 'component');
        $builder->where('elements.emotionId = :emotionId');
        $builder->addOrderBy(array(array('property' => 'elements.startRow','direction' => 'ASC')));
        $builder->addOrderBy(array(array('property' => 'elements.startCol','direction' => 'ASC')));
        $builder->setParameters(array('emotionId' => $emotionId));
        return $builder;
    }

    /**
     * @param integer $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getCampaignByCategoryQuery($categoryId)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions'))
                ->innerJoin('emotions.categories','categories')
                ->where('categories.id = ?1')
                ->andWhere('(emotions.validFrom <= CURRENT_TIMESTAMP() OR emotions.validFrom IS NULL)')
                ->andWhere('(emotions.validTo >= CURRENT_TIMESTAMP() OR emotions.validTo IS NULL)')
                ->andWhere('emotions.isLandingPage = 1 ')
                ->andWhere('emotions.active = 1 ')
                ->setParameter(1, $categoryId);

        return $builder->getQuery();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     * @param $offset
     * @param $limit
     */
    public function getCampaigns($offset=null, $limit=null)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions','categories.id AS categoryId', 'attribute'))
                ->innerJoin('emotions.categories','categories')
                ->leftJoin('emotions.attribute','attribute')
                ->where('emotions.isLandingPage = 1 ')
                ->andWhere('emotions.active = 1');

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Returns the builder selecting only Campaigns of the given shop category
     * @param $categoryId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCampaignsByCategoryId($categoryId)
    {
        $builder = $this->getCampaigns();
        $builder->andWhere(
            $builder->expr()->orX(
                $builder->expr()->eq('categories.id', ':categoryId'), // = 3
                $builder->expr()->like('categories.path', ':categoryPath') //like '%|3|
            )
        )
            ->setParameter('categoryId', $categoryId)
            ->setParameter('categoryPath', '%|' . $categoryId . '|');

        return $builder;
    }

    /**
     * @param integer $id
     * @return \Doctrine\ORM\Query
     */
    public function getEmotionById($id)
    {

        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions', 'elements', 'component', 'grid', 'template'))
                ->leftJoin('emotions.elements', 'elements')
                ->leftJoin('elements.component', 'component')
                ->leftJoin('emotions.grid', 'grid')
                ->leftJoin('emotions.template', 'template')
                ->where('emotions.id = ?1')
                ->andWhere('(emotions.validFrom <= CURRENT_TIMESTAMP() OR emotions.validFrom IS NULL)')
                ->andWhere('(emotions.validTo >= CURRENT_TIMESTAMP() OR emotions.validTo IS NULL)')
                ->setParameter(1, $id);

        return $builder;
    }
}
